<?php

class MosingpassPluginSettingsPage
{
    public const SLUG = "mosingpass";
    public const SETTINGS_COMMON_SECTION = "mosingpass_common_section";
    public const SETTINGS_SINGPASS_SECTION = "mosingpass_singpass_section";
    public const SETTINGS_LOCAL_SECTION = "mosingpass_local_section";
    public const SETTINGS_PAGE = "mosingpass-settings-page";

    public function __construct()
    {
        add_action('admin_post_mosingpass_clear_cache', array($this, 'clearCache'));
    }

    public function createSettings()
    {

        $this->addCommonSettingsSection();
        $this->addSingpassSettingsSection();
        $this->addLocalSettingsSection();

    }

    function checkboxHTML($args)
    { ?>
        <input class="form-control" type="checkbox"
               name="<?php echo $args['theName'] ?>"
               value="1"
            <?php checked(get_option($args['theName']), '1') ?>>
    <?php }

    function textHTML($args)
    { ?>
        <input class="form-control" type="text" size="75"
               name="<?php echo $args['theName']?>"
               value="<?php echo esc_attr(get_option($args['theName'])) ?>"
        >
    <?php }

    function textareaHTML($args)
    { ?>
        <textarea
                class="form-control" rows="8" cols="75"
                name="<?php echo $args['theName'] ?>"><?php echo get_option($args['theName']) ?></textarea>
    <?php }

    function singpassAppModeHTML($args)
    {
        $current = get_option($args['theName'], 'myinfo');
        ?>
        <fieldset>
            <label>
                <input type="radio"
                       name="<?php echo esc_attr($args['theName']); ?>"
                       value="myinfo"
                    <?php checked($current, 'myinfo'); ?>>
                MyInfo Retrieval
            </label>
            <br>
            <label>
                <input type="radio"
                       name="<?php echo esc_attr($args['theName']); ?>"
                       value="login"
                    <?php checked($current, 'login'); ?>>
                Singpass Login
            </label>
        </fieldset>
    <?php }

    function authenticationContextTypeHTML($args)
    {
        $current = get_option($args['theName'], 'APP_AUTHENTICATION_DEFAULT');
        $context_options = array(
            'Others' => array(
                'APP_AUTHENTICATION_DEFAULT' => 'General authentication',
                'APP_PAYMENT_DEFAULT' => 'Making a payment',
                'APP_ACCOUNT_PASSWORD_CHANGE_DEFAULT' => 'Password change',
                'APP_ACCOUNT_PASSWORD_RESET_DEFAULT' => 'Password reset',
                'APP_ACCOUNT_DETAILS_CHANGE_DEFAULT' => 'Account details change',
                'APP_ONBOARDING_DEFAULT' => 'App onboarding',
            ),
            'CPF transactions' => array(
                'CPF_CHANGE_PAYMENT_MODE' => 'Change payment mode',
                'CPF_CHANGE_DAILY_WITHDRAWAL_LIMIT' => 'Change daily withdrawal limits',
                'CPF_PROFILE_UPDATE' => 'Profile update',
                'CPF_LINK_BANK_ACCOUNT' => 'Link to bank account',
                'CPF_FUNDS_TRANSFER' => 'Funds transfer',
            ),
            'Banking' => array(
                'BANK_CASA_OPENING' => 'CASA opening',
                'BANK_CASA_INITIAL_USAGE' => 'CASA initial usage',
                'BANK_CARD_APPLICATION' => 'Debit/Credit card application',
                'BANK_CARD_INITIAL_USAGE' => 'Debit/Credit card initial usage',
                'BANK_LOAN_APPLICATION' => 'Loan application',
                'BANK_ADD_LOCAL_RECIPIENT' => 'Successful addition of local recipient',
                'BANK_ADD_OVERSEAS_RECIPIENT' => 'Successful addition of overseas recipient',
                'BANK_INCREASE_TRANSFER_LIMIT' => 'Increase transfer limit',
                'BANK_REPORT_FRAUD_SUSPICIOUS_ACTIVITY' => 'Report fraud or suspicious activity',
                'BANK_FUNDS_TRANSFER_LOCAL' => 'Funds transfer',
                'BANK_REMIT_MONEY_OVERSEAS' => 'Remit money overseas',
                'BANK_REPORT_LOST_CARD' => 'Report lost cards',
                'BANK_CHANGE_NOTIFICATION_METHOD' => 'Change of notification method',
                'BANK_INCREASE_CREDIT_CARD_LIMIT' => 'Increase credit card limit',
                'BANK_REQUEST_CASH_ADVANCE' => 'Cash advance',
                'BANK_INCREASE_INFLOW_OUTFLOW' => 'Increased inflow and outflow of funds transfer',
                'BANK_ACTIVATE_DORMANT_ACCOUNT' => 'Activation of a dormant account',
                'BANK_LOGIN_NEW_DEVICE' => 'Login using a new device',
                'BANK_LOGIN_UNFAMILIAR_IP' => 'Login from an unfamiliar IP',
                'BANK_UPDATE_USER_INFORMATION' => 'User information update',
                'BANK_NEW_DEVICE_REGISTRATION' => 'New device registration',
                'BANK_UNLOCK_MONEY_LOCK' => 'Unlock money lock',
                'BANK_GOOGLE_PAY_APPLE_PAY_CARD_ONBOARDING' => 'Google Pay / Apple Pay card onboarding',
            ),
            'Other Financial Institutions' => array(
                'FI_ACCOUNT_OPENING' => 'Account opening',
                'FI_LINK_BANK_ACCOUNT' => 'Link bank account',
                'FI_INCREASE_TRANSFER_LIMIT' => 'Increase transfer limit',
                'FI_INCREASE_WITHDRAWAL_LIMIT' => 'Increase withdrawal limit',
                'FI_INITIATE_DEPOSIT' => 'Initiate deposit',
            ),
            'Telcos' => array(
                'TELCO_SIM_CARD_APPLICATION' => 'SIM card application',
                'TELCO_SIM_CARD_ACTIVATION' => 'Activation of SIM card',
                'TELCO_CHANGE_ACCOUNT_DETAILS' => 'Change of account details',
                'TELCO_ACTIVATE_ROAMING' => 'Activate roaming',
                'TELCO_CHANGE_NOTIFICATION_METHOD' => 'Change of notification method',
            ),
        );
        ?>
        <select class="form-control" name="<?php echo esc_attr($args['theName']); ?>">
            <?php foreach ($context_options as $group_label => $options): ?>
                <optgroup label="<?php echo esc_attr($group_label); ?>">
                    <?php foreach ($options as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current, $value); ?>>
                            <?php echo esc_html($label . ' (' . $value . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modeInputs = document.querySelectorAll('input[name="<?php echo esc_js(MosingpassPlugin::SINGPASS_APP_MODE); ?>"]');
                const authContextSelect = document.querySelector('select[name="<?php echo esc_js($args['theName']); ?>"]');
                const authContextRow = authContextSelect ? authContextSelect.closest('tr') : null;
                const userInfoInput = document.querySelector('input[name="<?php echo esc_js(MosingpassPlugin::SINGPASS_USERINFO_ENDPOINT); ?>"]');
                const userInfoRow = userInfoInput ? userInfoInput.closest('tr') : null;

                function toggleAuthenticationContext() {
                    const selectedMode = document.querySelector('input[name="<?php echo esc_js(MosingpassPlugin::SINGPASS_APP_MODE); ?>"]:checked');
                    const isLogin = selectedMode && selectedMode.value === 'login';
                    if (authContextRow) {
                        authContextRow.style.display = isLogin ? '' : 'none';
                    }
                    if (userInfoRow) {
                        userInfoRow.style.display = isLogin ? 'none' : '';
                    }
                }

                modeInputs.forEach(function (input) {
                    input.addEventListener('change', toggleAuthenticationContext);
                });

                toggleAuthenticationContext();
            });
        </script>
    <?php }

    function clearCacheButtonHTML()
    { ?>
        <button type="submit" class="button" form="mosingpass-clear-cache-form">
            Clear Cache
        </button>
    <?php }

    function clearCache()
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to clear the cache.');
        }

        check_admin_referer('mosingpass_clear_cache');

        $deleted = delete_transient('mosingpass_jwks_cache');
        $redirect_url = add_query_arg(
            array(
                'page' => self::SETTINGS_PAGE,
                'mosingpass_cache_cleared' => $deleted ? '1' : '0',
            ),
            admin_url('options-general.php')
        );

        wp_safe_redirect($redirect_url);
        exit;
    }

    function writeCommonOptionsHTML()
    { ?>
        <div class="block">
            Common Options for the Plugin
        </div>
    <?php }

    function writeSingPassOptionsHTML()
    { ?>
        <div class="block">
            SingPass Endpoint Links Options.
        </div>
        <div class="block">
            Please refer to <a target="_blank" href="https://stg-id.singpass.gov.sg/docs/authorization/api#_staging_and_production_urls">Staging and Production URLs</a> for reference.
        </div>
    <?php }

    function writeLocalOptionsHTML()
    { ?>
        <div class="block">
            Local Settings And Options.
        </div>
    <?php }

    function addAdminSettings()
    {
        add_options_page('MO Singpass Options', //Page title
            'MO Singpass', //Text in options
            'manage_options', //user rights
            self::SETTINGS_PAGE, //visible link
            array($this, 'settingsHTML') //function to render option
        );
    }

    function settingsHTML()
    {
//        self::writeLog("Hello!", "settingsHTML");
        $slug = self::SLUG;
        if (isset($_GET['mosingpass_cache_cleared'])) {
            $cache_cleared = sanitize_text_field(wp_unslash($_GET['mosingpass_cache_cleared']));
            $notice_class = $cache_cleared === '1' ? 'notice-success' : 'notice-info';
            $notice_message = $cache_cleared === '1'
                ? 'SingPass JWKS cache cleared.'
                : 'SingPass JWKS cache was already empty.';
            ?>
            <div class="notice <?php echo esc_attr($notice_class); ?> is-dismissible">
                <p><?php echo esc_html($notice_message); ?></p>
            </div>
            <?php
        }
        ?>
        <div class="block">
            <h1>MO Singpass Settings</h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields("$slug._settings");
                do_settings_sections(self::SETTINGS_PAGE);
                submit_button();
                ?>
            </form>
            <form id="mosingpass-clear-cache-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="mosingpass_clear_cache">
                <?php wp_nonce_field('mosingpass_clear_cache'); ?>
            </form>
        </div>
        <?php
    }

    function redirectURIsHTML($args)
    {
        $option_name = $args['theName'];
        $values = get_option($option_name, []);
        if (!is_array($values)) $values = [];

        ?>
        <div id="redirect-uri-wrapper">
            <?php foreach ($values as $uri): ?>
                <div class="redirect-uri-group" style="margin-bottom: 8px;">
                    <input type="text" class="form-control" name="<?php echo $option_name; ?>[]" value="<?php echo esc_attr($uri); ?>" size="75" />
                    <button type="button" class="button remove-uri" onclick="this.parentElement.remove()">Remove</button>
                </div>
            <?php endforeach; ?>
            <?php if (empty($values)): ?>
                <div class="redirect-uri-group" style="margin-bottom: 8px;">
                    <input type="text" class="form-control" name="<?php echo $option_name; ?>[]" size="75" />
                    <button type="button" class="button remove-uri" onclick="this.parentElement.remove()">Remove</button>
                </div>
            <?php endif; ?>
        </div>
        <button type="button" class="button" onclick="addRedirectUriField()">Add Redirect URI</button>

        <script>
            function addRedirectUriField() {
                const container = document.getElementById('redirect-uri-wrapper');
                const field = document.createElement('div');
                field.className = 'redirect-uri-group';
                field.style.marginBottom = '8px';
                field.innerHTML = `
                    <input type="text" class="form-control" name="<?php echo $option_name; ?>[]" size="75" />
                    <button type="button" class="button remove-uri" onclick="this.parentElement.remove()">Remove</button>
                `;
                container.appendChild(field);
            }
        </script>
        <?php
    }



    /**
     * @param string $common_section
     * @param string $settings_page
     */
    protected function addCommonSettingsSection(): void
    {
        $common_section = self::SETTINGS_COMMON_SECTION;
        $settings_page = self::SETTINGS_PAGE;
        $slug = self::SLUG;

        add_settings_section($common_section,
            'Common Options',
            array($this, 'writeCommonOptionsHTML'),
            $settings_page);

        add_settings_field(MosingpassPlugin::WRITE_LOG,
            'Write Log',
            array($this, 'checkboxHTML'),
            $settings_page,
            $common_section,
            array('theName' => MosingpassPlugin::WRITE_LOG));
        register_setting("$slug._settings",
            MosingpassPlugin::WRITE_LOG,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => '0'));
        
        add_settings_field(MosingpassPlugin::CLEAR_CACHE,
            'Clear Cache',
            array($this, 'clearCacheButtonHTML'),
            $settings_page,
            $common_section);
    }

    /**
     * @param string $singpass_section
     * @param string $settings_page
     */
    protected function addSingpassSettingsSection(): void
    {
        $singpass_section = self::SETTINGS_SINGPASS_SECTION;
        $settings_page = self::SETTINGS_PAGE;
        $slug = self::SLUG;

        add_settings_section($singpass_section,
            'SingPass Server Options', array($this,
                'writeSingPassOptionsHTML'),
            $settings_page);

        add_settings_field(MosingpassPlugin::SINGPASS_APP_MODE,
            'SingPass Mode',
            array($this, 'singpassAppModeHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_APP_MODE));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_APP_MODE,
            array(
                'sanitize_callback' => function ($value) {
                    return in_array($value, array('myinfo', 'login'), true) ? $value : 'myinfo';
                },
                'default' => 'myinfo'
            ));

        add_settings_field(MosingpassPlugin::SINGPASS_AUTH_CONTEXT_TYPE,
            'Authentication Context Type',
            array($this, 'authenticationContextTypeHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_AUTH_CONTEXT_TYPE));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_AUTH_CONTEXT_TYPE,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => 'APP_AUTHENTICATION_DEFAULT'));

        add_settings_field(MosingpassPlugin::SINGPASS_PAR_ENDPOINT,
            'SingPass PAR Endpoint',
            array($this, 'textHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_PAR_ENDPOINT));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_PAR_ENDPOINT,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

        add_settings_field(MosingpassPlugin::SINGPASS_AUTH_ENDPOINT,
            'SingPass Auth Endpoint',
            array($this, 'textHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_AUTH_ENDPOINT));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_AUTH_ENDPOINT,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

        add_settings_field(MosingpassPlugin::SINGPASS_TOKEN_ENDPOINT,
            'SingPass Token Endpoint',
            array($this, 'textHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_TOKEN_ENDPOINT));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_TOKEN_ENDPOINT,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

        add_settings_field(MosingpassPlugin::SINGPASS_USERINFO_ENDPOINT,
            'SingPass Userinfo Endpoint',
            array($this, 'textHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_USERINFO_ENDPOINT));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_USERINFO_ENDPOINT,
        array('sanitize_callback' => 'sanitize_text_field',
            'default' => ''));
    
        add_settings_field(MosingpassPlugin::SINGPASS_JWKS_ENDPOINT,
            'SingPass JWKS Endpoint',
            array($this, 'textHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_JWKS_ENDPOINT));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_JWKS_ENDPOINT,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

        add_settings_field(MosingpassPlugin::SINGPASS_OPENID_ENDPOINT,
            'SingPass OpenID discovery Endpoint',
            array($this, 'textHTML'),
            $settings_page,
            $singpass_section,
            array('theName' => MosingpassPlugin::SINGPASS_OPENID_ENDPOINT));
        register_setting("$slug._settings", MosingpassPlugin::SINGPASS_OPENID_ENDPOINT,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

    }

    /**
     * @param string $local_section
     * @param string $settings_page
     */
    protected function addLocalSettingsSection(): void
    {
        $local_section = self::SETTINGS_LOCAL_SECTION;
        $settings_page = self::SETTINGS_PAGE;
        $slug = self::SLUG;

        add_settings_section($local_section,
            'Local Options',
            array($this, 'writeLocalOptionsHTML'),
            $settings_page);

        add_settings_field(MosingpassPlugin::APP_NAME,
            'App Name',
            array($this, 'textHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::APP_NAME));
        register_setting("$slug._settings", MosingpassPlugin::APP_NAME,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

        // add_settings_field(MosingpassPlugin::REDIRECT_URI,
        //     'Redirect URI',
        //     array($this, 'textHTML'),
        //     $settings_page,
        //     $local_section,
        //     array('theName' => MosingpassPlugin::REDIRECT_URI));
        // register_setting("$slug._settings", MosingpassPlugin::REDIRECT_URI,
        //     array('sanitize_callback' => 'sanitize_text_field',
        //         'default' => ''));
        add_settings_field(
            MosingpassPlugin::REDIRECT_URI,
            'Redirect URIs',
            array($this, 'redirectURIsHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::REDIRECT_URI)
        );
        register_setting(
            "$slug._settings",
            MosingpassPlugin::REDIRECT_URI,
            array(
                'sanitize_callback' => function ($value) {
                    return array_values(array_filter(array_map('sanitize_text_field', $value)));
                },
                'default' => array()
            )
        );



/*
 *     public const SHOW_QR = "mosp_show_qr";
    public const CREATE_NEW_USER = "mosp_create_new_user";
    public const ADD_NEW_USER_FORM = "mosp_add_new_user_form";
    public const AFTER_LOGIN_URL = "mosp_after_login_url";

 */

        add_settings_field(MosingpassPlugin::SHOW_QR,
            'Show QR',
            array($this, 'checkboxHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::SHOW_QR));
        register_setting("$slug._settings",
            MosingpassPlugin::SHOW_QR,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => '0'));


        add_settings_field(MosingpassPlugin::CREATE_NEW_USER,
            "Automatically Create New User",
            array($this, 'checkboxHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::CREATE_NEW_USER));
        register_setting("$slug._settings",
            MosingpassPlugin::CREATE_NEW_USER,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => '0'));

        add_settings_field(MosingpassPlugin::ADD_NEW_USER_FORM,
            'New User Register URL',
            array($this, 'textHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::ADD_NEW_USER_FORM));
        register_setting("$slug._settings", MosingpassPlugin::ADD_NEW_USER_FORM,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

        add_settings_field(MosingpassPlugin::AFTER_LOGIN_URL,
            'Page To Load After Login',
            array($this, 'textHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::AFTER_LOGIN_URL));
        register_setting("$slug._settings", MosingpassPlugin::AFTER_LOGIN_URL,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

        add_settings_field(MosingpassPlugin::PUBLIC_JWKS,
            'Public JWKS',
            array($this, 'textareaHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::PUBLIC_JWKS));
        register_setting("$slug._settings", MosingpassPlugin::PUBLIC_JWKS,
            array(
                'default' => ''));
        add_settings_field(MosingpassPlugin::PRIVATE_JWKS,
            'Private JWKS',
            array($this, 'textareaHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::PRIVATE_JWKS));
        register_setting("$slug._settings", MosingpassPlugin::PRIVATE_JWKS,
            array(
                'default' => ''));

    }
}
