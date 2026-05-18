<?php

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA256KW;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Encryption\Serializer\CompactSerializer as EncryptionCompactSerializer;
use Jose\Component\Signature\Serializer\CompactSerializer as SignatureCompactSerializer;

if (!defined('ABSPATH')) {
    exit;
}

class MosingpassCryptoHelper
{
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function generateRandomId($bytes = 16)
    {
        return self::base64UrlEncode(random_bytes($bytes));
    }
    
    private static function publicJwkFromPrivateJwk($privateJwk)
    {
        unset($privateJwk['d']);

        return $privateJwk;
    }

    private static function getJwkByUse($jwks, $use)
    {
        if (is_string($jwks)) {
            $jwks = json_decode($jwks, true);
        }

        if (!is_array($jwks)) {
            throw new Exception('Invalid JWKS format');
        }

        $keys = isset($jwks['keys']) ? $jwks['keys'] : $jwks;

        foreach ($keys as $key) {
            if (isset($key['use']) && $key['use'] === $use) {
                return $key;
            }
        }

        throw new Exception('JWK not found for use: ' . $use);
    }

    public static function generatePkcePair()
    {
        $verifier = self::base64UrlEncode(random_bytes(32));
        $challenge = self::base64UrlEncode(hash('sha256', $verifier, true));

        return [
            'verifier' => $verifier,
            'challenge' => $challenge,
        ];
    }

    public static function generateDpopKeyPair()
    {
        $jwk = JWKFactory::createECKey('P-256');

        return [
            'private_jwk' => $jwk->all(),
            'public_jwk' => self::publicJwkFromPrivateJwk($jwk->all()),
        ];
    }

    public static function generateDpopJWT($method, $endpoint, $publicDpop, $privateDpop, $accessToken = null)
    {
        $now = time();

        // Build Claims
        $claims = [
            'htm' => strtoupper($method),
            'htu' => $endpoint,
            'iat' => $now,
            'exp' => $now + 120,
            'jti' => self::generateRandomId(16),
        ];

        // Add ath claim if access token is provided
        if ($accessToken !== null) {
            $claims['ath'] = self::base64UrlEncode(hash('sha256', $accessToken, true));
        }

        $jwsBuilder = new JWSBuilder(new AlgorithmManager([new ES256()]));

        // Build JWT header
        $header = [
            'alg' => 'ES256',
            'typ' => 'dpop+jwt',
            'jwk' => $publicDpop,
        ];

        // Create JWS
        $jws = $jwsBuilder
            ->create()
            ->withPayload(json_encode($claims, JSON_UNESCAPED_SLASHES))
            ->addSignature(JWKFactory::createFromValues($privateDpop), $header)
            ->build();


        // Serialize to compact form
        $signatureSerializer = new SignatureCompactSerializer();

        return $signatureSerializer->serialize($jws, 0);
    }

    public static function generateClientAssertion($clientId, $issuer, $privateJwks)
    {
        $privateSigJwk = self::getJwkByUse($privateJwks, 'sig');
        $sigKid = $privateSigJwk['kid'];

        $now = time();

        // Build JWT payload
        $payload = [
            'sub' => $clientId,
            'aud' => $issuer,
            'iss' => $clientId,
            'iat' => $now,
            'exp' => $now + 120,
            'jti' => self::generateRandomId(16),
        ];

        $jwsBuilder = new JWSBuilder(new AlgorithmManager([new ES256()]));

        // Build JWT header
        $headers = [
            'typ' => 'JWT',
            'alg' => 'ES256',
            'kid' => $sigKid,
        ];

        // Sign JWT
        $jws = $jwsBuilder
            ->create()
            ->withPayload(json_encode($payload, JSON_UNESCAPED_SLASHES))
            ->addSignature(JWKFactory::createFromValues($privateSigJwk), $headers)
            ->build();

        // Serialize to compact form
        $signatureSerializer = new SignatureCompactSerializer();

        return $signatureSerializer->serialize($jws, 0);
    }
 
    public static function verifyIdToken($idToken, $privateJwks, $singpassPublicSigningJwk, $issuer, $clientId, $nonce)
    {
        $publicSigJwk = JWKFactory::createFromValues(self::getJwkByUse($singpassPublicSigningJwk, 'sig'));
        $privateEncJwk = JWKFactory::createFromValues(self::getJwkByUse($privateJwks, 'enc'));
    
        // Decrypt the JWE
        $encryptionSerializer = new EncryptionCompactSerializer();
        $jwe = $encryptionSerializer->unserialize($idToken);

        $decrypter = new JWEDecrypter(
            new AlgorithmManager([new ECDHESA256KW()]),
            new AlgorithmManager([new A256CBCHS512()]),
            new CompressionMethodManager([])
        );

        if (!$decrypter->decryptUsingKey($jwe, $privateEncJwk, 0)) {
            throw new Exception('Failed to decrypt ID Token');
        }

        $payload = $jwe->getPayload();

        // Verify JWS
        $signatureSerializer = new SignatureCompactSerializer();
        $jws = $signatureSerializer->unserialize($payload);

        $jwsVerifier = new JWSVerifier(new AlgorithmManager([new ES256()]));

        if (!$jwsVerifier->verifyWithKey($jws, $publicSigJwk, 0)) {
            throw new Exception('Invalid ID Token signature');
        }

        // Verify Claims
        $claims = json_decode($jws->getPayload(), true);

        if ($claims['iss'] !== $issuer) {
            throw new Exception('Invalid issuer in ID Token');
        }

        if ($claims['aud'] !== $clientId) {
            throw new Exception('Invalid audience in ID Token');
        }

        if ($claims['exp'] < time()) {
            throw new Exception('ID Token has expired');
        }

        if ($claims['nonce'] !== $nonce) {
            throw new Exception('Invalid nonce in ID Token');
        }

        return $claims;
    }

    public static function decryptUserInfo($encryptedUserInfo, $privateJwks, $singpassPublicSigningJwk, $issuer, $clientId)
    {
        $privateEncJwk = JWKFactory::createFromValues(self::getJwkByUse($privateJwks, 'enc'));
        $publicSigJwk = JWKFactory::createFromValues(self::getJwkByUse($singpassPublicSigningJwk, 'sig'));

        $encryptionSerializer = new EncryptionCompactSerializer();
        $jwe = $encryptionSerializer->unserialize($encryptedUserInfo);

        $decrypter = new JWEDecrypter(
            new AlgorithmManager([new ECDHESA256KW()]),
            new AlgorithmManager([new A256GCM()]),
            new CompressionMethodManager([])
        );

        if (!$decrypter->decryptUsingKey($jwe, $privateEncJwk, 0)) {
            throw new Exception("Failed to decrypt UserInfo");
        }

        $decrypted = $jwe->getPayload();

        // CASE 1: it's a signed JWT (JWS)
        if (substr_count($decrypted, '.') === 2) {
            $signatureSerializer = new SignatureCompactSerializer();
            $jws = $signatureSerializer->unserialize($decrypted);

            $verifier = new JWSVerifier(new AlgorithmManager([new ES256()]));
            
            if (!$verifier->verifyWithKey($jws, $publicSigJwk, 0)) {
                throw new Exception("Invalid UserInfo signature");
            }

            $claims = json_decode($jws->getPayload(), true);

            if ($claims['iss'] !== $issuer) {
                throw new Exception("Invalid issuer");
            }

            if ($claims['aud'] !== $clientId) {
                throw new Exception("Invalid audience");
            }

            return $claims;
        }

        // CASE 2: plain JSON
        return json_decode($decrypted, true);
    }
}