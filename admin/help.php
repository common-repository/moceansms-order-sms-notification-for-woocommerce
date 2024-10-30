<?php

class MoceanSMS_Help_View implements Moceansms_Register_Interface {

	private $settings_api;

	function __construct() {
		$this->settings_api = new WeDevs_Settings_API;
	}

	public function register() {
        add_filter( 'moceansms_setting_section',     array($this, 'set_help_setting_section' ) );
		add_filter( 'moceansms_setting_fields',      array($this, 'set_help_setting_field' ) );
        add_action( 'moceansms_setting_fields_custom_html', array($this, 'display_help_page'), 10, 1);
	}

	public function set_help_setting_section( $sections ) {
		$sections[] = array(
            'id'               => 'moceansms_help_setting',
            'title'            => __( 'Help', MOCEANSMS_TEXT_DOMAIN ),
            'submit_button'    => '',
		);

		return $sections;
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	public function set_help_setting_field( $setting_fields ) {
		return $setting_fields;
	}

    public function display_help_page($form_id) {
        if($form_id !== 'moceansms_help_setting') { return; }
    ?>
        <br>
        <h4>What is MoceanSMS Order Notification?</h4>
        <p><a href="https://moceanapi.com/" target="_blank">MoceanSMS Order Notification</a> is a cloud-based reliable interface for sending short text messages to 200+ networks around the world.</p>
        <h4>How to create an API key?</h4>
        <p>If you want to use the plugin for MoceanSMS, you need to create an API key. You can do this by creating an account <a href="https://dashboard.moceanapi.com/register?fr=wordpress_order_notification"><strong>here</strong></a>.  The account creation is free and 10 trial credit will be provided.</p>
        <h4>Have questions?</h4>
        <p>If you have any questions or feedbacks, you can send a message to our support team and we will get back to you as soon as possible at our <a href="https://moceanapi.com/#contact" target="_blank">page</a>.</p>
    <?php
    }


}

?>
