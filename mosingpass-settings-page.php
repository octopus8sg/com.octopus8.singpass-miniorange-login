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
        </div>
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

        add_settings_field(MosingpassPlugin::REDIRECT_URI,
            'Redirect URI',
            array($this, 'textHTML'),
            $settings_page,
            $local_section,
            array('theName' => MosingpassPlugin::REDIRECT_URI));
        register_setting("$slug._settings", MosingpassPlugin::REDIRECT_URI,
            array('sanitize_callback' => 'sanitize_text_field',
                'default' => ''));

//        add_settings_field(MosingpassPlugin::JWKS_ENDPOINT,
//            'JWKS Endpoint',
//            array($this, 'textHTML'),
//            $settings_page,
//            $local_section,
//            array('theName' => MosingpassPlugin::JWKS_ENDPOINT));
//        register_setting("$slug._settings", MosingpassPlugin::JWKS_ENDPOINT,
//            array('sanitize_callback' => 'sanitize_text_field',
//                'default' => ''));

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

