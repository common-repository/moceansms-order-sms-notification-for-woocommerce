<?php

use MoceanAPI_WC\Helpers\Utils;
use MoceanAPI_WC\Migrations\MigrateSendSMSPlugin;
use MoceanAPI_WC\Migrations\MigrateWoocommercePlugin;

class Moceansms_WooCommerce_Setting implements Moceansms_Register_Interface {

	private $settings_api;
    private $log;

	function __construct() {
		$this->settings_api = new WeDevs_Settings_API;
        $this->log = new Moceansms_WooCoommerce_Logger();
	}

	public function register() {
        // if ( class_exists( 'woocommerce' ) ) {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_action( 'admin_init', array( $this, 'initialise_default_recipient_setting' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'moceansms_setting_fields_custom_html', array( $this, 'moceansms_wc_not_activated' ), 10, 1 );

            add_action( 'init', array( $this, 'schedule_check_domain' ) );
            add_action( 'moceansms_check_domain', array( $this, 'check_domain_reachability' ) );

            add_filter( 'moceansms_setting_fields', array( $this, 'add_custom_order_status' ) );

        // } else {
        //     add_action( 'admin_menu', array( $this, 'woocommerce_not_activated_menu_view' ) );
        // }
	}

	function admin_init() {

		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings_api->admin_init();
	}

	function admin_menu() {
		add_options_page( 'MoceanSMS WooCommerce', 'MoceanAPI SMS Settings', 'manage_options', 'moceansms-woocoommerce-setting',
            array($this, 'plugin_page')
        );
	}

	function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'moceansms_setting',
				'title' => __( 'MoceanAPI SMS Settings', MOCEANSMS_TEXT_DOMAIN )
			),
			array(
				'id'    => 'moceansms_admin_setting',
				'title' => __( 'Admin Settings', MOCEANSMS_TEXT_DOMAIN ),
                'submit_button' => class_exists("woocommerce") ? null : '',
			),
			array(
                'id'    => 'moceansms_customer_setting',
				'title' => __( 'Customer Settings', MOCEANSMS_TEXT_DOMAIN ),
                'submit_button' => class_exists("woocommerce") ? null : '',
			)
		);

		$sections = apply_filters( 'moceansms_setting_section', $sections );

		return $sections;
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	function get_settings_fields() {
		//WooCommerce Country
		global $woocommerce;
        // $countries_obj = $this->get_countries();
    	// $countries_obj   = new WC_Countries();
		// $countries   = $countries_obj->__get('countries');
        $countries =  $this->get_countries();

        // country
        $default_country_code = moceansms_get_options('moceansms_woocommerce_country_code', 'moceansms_setting');
        $country_code = '';
        if( empty($default_country_code) ) {
            $user_ip = $this->get_user_ip();
            if( empty($user_ip) ) { return; }

            $user_ip = $this->get_user_ip();
            $country_code = $this->get_country_code_from_ip($user_ip);
        }

		$additional_billing_fields = '';
		$additional_billing_fields_desc  = '';
		$additional_billing_fields_array = $this->get_additional_billing_fields();
		foreach ( $additional_billing_fields_array as $field ) {
			$additional_billing_fields .= ', [' . $field . ']';
		}
		if ( $additional_billing_fields ) {
			$additional_billing_fields_desc = '<br />Custom tags: ' . substr( $additional_billing_fields, 2 );
		}

		$settings_fields = array(
			'moceansms_setting' => array(
				array(
					'name'  => 'moceansms_woocommerce_account_balance',
					'label' => __( 'Account Balance', MOCEANSMS_TEXT_DOMAIN ),
					'desc'  => __( 'Your MoceanAPI account balance', MOCEANSMS_TEXT_DOMAIN ),
					'type'  => 'custom_html',
					'custom_html'  => array($this, "display_account_balance"),
				),
				array(
					'name'  => 'moceansms_woocommerce_api_key',
					'label' => __( 'API Key', MOCEANSMS_TEXT_DOMAIN ),
					'desc'  => __( 'Your MoceanAPI account key. Account can be registered <a href="https://dashboard.moceanapi.com/register?fr=wordpress_order_notification" target="blank">here</a>', MOCEANSMS_TEXT_DOMAIN ),
					'type'  => 'text',
				),
				array(
					'name'  => 'moceansms_woocommerce_api_secret',
					'label' => __( 'API Secret', MOCEANSMS_TEXT_DOMAIN ),
					'desc'  => __( 'Your MoceanAPI account secret', MOCEANSMS_TEXT_DOMAIN ),
					'type'  => 'password',
				),
				array(
					'name'  => 'moceansms_woocommerce_sms_from',
					'label' => __( 'Message From', MOCEANSMS_TEXT_DOMAIN ),
					'desc'  => __( 'Sender of the SMS when a message is received at a mobile phone', MOCEANSMS_TEXT_DOMAIN ),
					'type'  => 'text',
                    'sanitize_callback' => array($this, "validate_sender_id"),
				),
				array(//Get default country v1.1.17
					'name'    		=> 'moceansms_woocommerce_country_code',
					'label'   		=> __( 'Default country', MOCEANSMS_TEXT_DOMAIN ),
					'class'     	=> array('chzn-drop'),
					'placeholder'	=> __( 'Select a Country', MOCEANSMS_TEXT_DOMAIN),
					'desc'    		=> 'Selected country will be use as default country info for mobile number when country info is not provided. ',
					'type'    		=> 'select',
					'options' 		=> $countries,
                    'default'       => !empty($default_country_code) ? $default_country_code : $country_code,
				),
				array(
					'name'  => 'export_moceansms_log',
					'label' => 'Export Log',
					'desc'  => '<a href="' . admin_url( 'admin.php?page=moceansms-download-file&file=MoceanSMS' ) . '" class="button button-secondary">Export</a><div id="mocean_sms[keyword-modal]" class="modal"></div>',
					'type'  => 'html'
				)
			),
			'moceansms_admin_setting'     => array(
				array(
					'name'    => 'moceansms_woocommerce_admin_suborders_send_sms',
					'label'   => __( 'Enable Suborders SMS Notifications', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => ' ' . __( 'Enable', MOCEANSMS_TEXT_DOMAIN ),
					'type'    => 'checkbox',
					'default' => 'off'
				),
				array(
					'name'    => 'moceansms_woocommerce_admin_send_sms_on',
					'label'   => __( '	Send notification on', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => __( 'Choose when to send a status notification message to your admin <br> Set <strong>low stock threshold</strong> for each product under <strong>WooCommerce Product -> Product Data -> Inventory -> Low Stock Threshold</strong>', MOCEANSMS_TEXT_DOMAIN ),
					'type'    => 'multicheck',
					'default' => array(
						'on-hold'    => 'on-hold',
						'processing' => 'processing'
					),
					'options' => array(
						'pending'           => ' Pending',
						'on-hold'           => ' On-hold',
						'processing'        => ' Processing',
						'completed'         => ' Completed',
						'cancelled'         => ' Cancelled',
						'refunded'          => ' Refunded',
						'failed'            => ' Failed',
						'low_stock_product' => ' Low stock product ',
					)
				),
				array(
					'name'  => 'moceansms_woocommerce_admin_sms_recipients',
					'label' => __( 'Mobile Number', MOCEANSMS_TEXT_DOMAIN ),
					'desc'  => __( 'Mobile number to receive new order SMS notification. To send to multiple receivers, separate each entry with comma such as 0123456789, 0167888945', MOCEANSMS_TEXT_DOMAIN ),
					'type'  => 'text',
				),
				array(
					'name'    => 'moceansms_woocommerce_admin_sms_template',
					'label'   => __( 'Admin SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="admin" data-attr-target="moceansms_admin_setting[moceansms_woocommerce_admin_sms_template]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : You have a new order with order ID [order_id] and order amount [order_currency] [order_amount]. The order is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
                ),
				array(
					'name'    => 'moceansms_woocommerce_admin_sms_template_low_stock_product',
					'label'   => __( 'Low Stock Product Admin SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords-low-product-stock]" data-attr-type="admin" data-attr-target="moceansms_admin_setting[moceansms_woocommerce_admin_sms_template_low_stock_product]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Your product [product_name] has low stock. Current quantity: [product_stock_quantity]. Please restock soon.', MOCEANSMS_TEXT_DOMAIN )
                ),
			),
			'moceansms_customer_setting'  => array(
				array(
					'name'    => 'moceansms_woocommerce_suborders_send_sms',
					'label'   => __( 'Enable Suborders SMS Notifications', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => ' ' . __( 'Enable', MOCEANSMS_TEXT_DOMAIN ),
					'type'    => 'checkbox',
					'default' => 'off'
				),
				array(
					'name'    => 'moceansms_woocommerce_send_sms_to',
					'label'   => __( 'Send SMS to', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => ' ' . __( 'Choose who to send SMS to', MOCEANSMS_TEXT_DOMAIN ),
					'type'    => 'multicheck',
                    'default' => array(
						'billing-recipient'  => 'billing-recipient',
					),
					'options' => array(
                        'billing-recipient'  => 'Billing Recipient',
						'shipping-recipient' => 'Shipping Recipient',
					)
				),
				array(
					'name'    => 'moceansms_woocommerce_send_sms',
					'label'   => __( '	Send notification on', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => __( 'Choose when to send a status notification message to your customer', MOCEANSMS_TEXT_DOMAIN ),
					'type'    => 'multicheck',
                    'default' => array(
						'on-hold'    => 'on-hold',
						'processing' => 'processing',
						'completed'  => 'completed',
					),
					'options' => array(
						'pending'    => ' Pending',
						'on-hold'    => ' On-hold',
						'processing' => ' Processing',
						'completed'  => ' Completed',
						'cancelled'  => ' Cancelled',
						'refunded'   => ' Refunded',
						'failed'     => ' Failed'
					)
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_default',
					'label'   => __( 'Default Customer SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="default" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_default]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_pending',
					'label'   => __( 'Pending SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="pending" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_pending]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_on-hold',
					'label'   => __( 'On-hold SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="on_hold" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_on-hold]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_processing',
					'label'   => __( 'Processing SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="processing" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_processing]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_completed',
					'label'   => __( 'Completed SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="completed" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_completed]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_cancelled',
					'label'   => __( 'Cancelled SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="cancelled" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_cancelled]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_refunded',
					'label'   => __( 'Refunded SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="refunded" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_refunded]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				),
				array(
					'name'    => 'moceansms_woocommerce_sms_template_failed',
					'label'   => __( 'Failed SMS Message', MOCEANSMS_TEXT_DOMAIN ),
					'desc'    => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="failed" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_failed]" class="button button-secondary">Keywords</button>',
					'type'    => 'textarea',
					'rows'    => '8',
					'cols'    => '500',
					'css'     => 'min-width:350px;',
					'default' => __( '[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', MOCEANSMS_TEXT_DOMAIN )
				)
			)
		);

        if(!class_exists('woocommerce')) {
            unset($settings_fields['moceansms_admin_setting']);
            unset($settings_fields['moceansms_customer_setting']);
        }

		$settings_fields = apply_filters( 'moceansms_setting_fields', $settings_fields );

		return $settings_fields;
	}

    public function add_custom_order_status($setting_fields)
    {
        $log = new Moceansms_WooCoommerce_Logger();
        // $log->add("MoceanSMS", print_r($custom_wc_statuses, 1));
        $default_statuses = [
            'wc-pending',
            'wc-processing',
            'wc-on-hold',
            'wc-completed',
            'wc-cancelled',
            'wc-refunded',
            'wc-failed',
            'wc-checkout-draft'
        ];

        $fields_to_iterate = ['moceansms_admin_setting', 'moceansms_customer_setting', 'moceansms_multivendor_setting'];

        $all_wc_statuses = function_exists("wc_get_order_statuses") ? wc_get_order_statuses() : [];

        $custom_wc_statuses = array_diff_key($all_wc_statuses, array_flip($default_statuses));

        $processed_wc_statuses = [];

        foreach($custom_wc_statuses as $key => $value) {
            $trimmed_key = ltrim($key, 'wc-');
            $processed_wc_statuses[$trimmed_key] = $value;
        }

        foreach($fields_to_iterate as $field) {
            if(array_key_exists($field, $setting_fields)) {
                for( $i=0; $i<count($setting_fields[$field]); $i++ ) {
                    if(array_key_exists('options', $setting_fields[$field][$i])) {
                        foreach($processed_wc_statuses as $processed_key => $processed_value) {
                            if( ! array_key_exists($processed_key, $setting_fields[$field][$i]['options']) ) {
                                $setting_fields[$field][$i]['options'][$processed_key] = " {$processed_value}";
                                if($field == 'moceansms_customer_setting') {
                                    $setting_fields[$field][] = array(
                                        'name'    => "moceansms_woocommerce_sms_template_{$processed_key}",
                                        'label'   => __( "{$processed_value} Customer SMS Message", MOCEANSMS_TEXT_DOMAIN ),
                                        'desc'    => sprintf('Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="default" data-attr-target="moceansms_customer_setting[moceansms_woocommerce_sms_template_%s]" class="button button-secondary">Keywords</button>', $processed_key),
                                        'type'    => 'textarea',
                                        'rows'    => '8',
                                        'cols'    => '500',
                                        'css'     => 'min-width:350px;',
                                        'default' => __( "Your {$processed_value} SMS template", MOCEANSMS_TEXT_DOMAIN )
                                    );
                                }
                            }
                        }
                        break;
                    }
                }

                continue;
            }
        }

        return $setting_fields;
    }

	function plugin_page() {

		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();
		echo '<input type="hidden" value="' . join(",", $this->get_additional_billing_fields()) . '" id="moceansms_new_billing_field" />';

		echo '</div>';
	}

	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	function get_pages() {
		$pages         = get_pages();
		$pages_options = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		return $pages_options;
	}

    public function initialise_default_recipient_setting() {

        if( !get_option("moceansms_customer_setting") ) {
            // this is because new users.
            // no settings to anything.
            return;
        }

        $default_setting = [
            'billing-recipient' => 'billing-recipient'
        ];

        $option_setting = moceansms_get_options("moceansms_woocommerce_send_sms_to", "moceansms_customer_setting");

        // no settings, usually after update plugin.
        if(empty($option_setting)) {
            return moceansms_update_options("moceansms_woocommerce_send_sms_to", $default_setting, "moceansms_customer_setting");
        }

        $send_to_billing_recipient = "";
        $send_to_shipping_recipient = "";

        if( isset($option_setting['billing-recipient']) ) {
            $send_to_billing_recipient = $option_setting['billing-recipient'];
        }

        if( isset($option_setting['shipping-recipient']) ) {
            $send_to_shipping_recipient = $option_setting['shipping-recipient'];
        }

        // var_dump($option_setting);
        // var_dump($send_to_billing_recipient);
        // var_dump($send_to_shipping_recipient);
        if( empty($send_to_billing_recipient) && empty($send_to_shipping_recipient) ) {
            return moceansms_update_options("moceansms_woocommerce_send_sms_to", $default_setting, "moceansms_customer_setting");
        }
    }

    public function check_domain_reachability()
    {
        try {
            $this->log->add("MoceanSMS", "Running scheduled checking domain task.");
            $response_code = wp_remote_retrieve_response_code( wp_remote_get("https://rest.moceanapi.com/rest/2/account/balance") );
            // successfully reached our domain
            if($response_code === 400) {
                update_option("moceansms_domain_reachable", true);
                $this->log->add("MoceanSMS", "Domain is reachable. Will be using domain.");
            }
            else {
                $this->log->add("MoceanSMS", "Exception thrown. Domain not reachable.");
                throw new Exception("Domain not reachable.");
            }
        } catch (Exception $e) {
            $this->log->add("MoceanSMS", "Domain not reachable. Using IP address");
            $this->log->add("MoceanSMS", "err msg: {$e->getMessage()}");
            update_option("moceansms_domain_reachable", false);
        }
    }

    public function schedule_check_domain()
    {
        $latest_plugin_version = get_plugin_data(MOCEANSMS_PLUGIN_DIR . "moceansms-woocommerce.php")['Version'];
        $current_plugin_version = get_option("moceansms_plugin_version");

        if(!empty($current_plugin_version)) {
            // if cur < lat = -1
            // if cur === lat = 0
            // if cur > lat = 1
            if(version_compare( $current_plugin_version, $latest_plugin_version ) < 0) {
                $this->log->add("MoceanSMS", "current plugin version: {$current_plugin_version}.");
                $this->log->add("MoceanSMS", "latest plugin version: {$latest_plugin_version}.");
                as_unschedule_all_actions("moceansms_check_domain");
                $this->log->add("MoceanSMS", "Successfully unscheduled domain reachability for initialization.");
                update_option("moceansms_plugin_version", $latest_plugin_version);
            }
        } else {
            update_option("moceansms_plugin_version", '1.3.0');
            $this->schedule_check_domain();
        }
        if ( false === as_has_scheduled_action( 'moceansms_check_domain' ) ) {
            as_schedule_recurring_action( strtotime( 'now' ), DAY_IN_SECONDS, 'moceansms_check_domain' );
        }
    }

    public function display_account_balance()
    {
        $log = new Moceansms_WooCoommerce_Logger();
        try {
            $api_key = moceansms_get_options("moceansms_woocommerce_api_key", "moceansms_setting");
            $api_secret = moceansms_get_options("moceansms_woocommerce_api_secret", "moceansms_setting");

            $moceansms_rest = new MoceanSMS($api_key, $api_secret);
            $rest_response = $moceansms_rest->accountBalance();

            $rest_response = json_decode($rest_response);

            if($rest_response->{'status'} == 0){
                $acc_balance = $rest_response->{'value'};
            } else {
                $acc_balance = "Invalid API Credentials";
            }

        } catch (Exception $e) {
            $log->add("MoceanSMS", print_r($e->getMessage(), 1));
            $acc_balance = 'Failed to retrieve balance';
        }


        ?>
            <p><?php echo esc_html($acc_balance); ?></p>

            <?php
                if(strpos($acc_balance, 'Invalid') !== false) {
                $client_ip_address = $this->get_user_ip();

            ?>
                <p style="color: red;">If you are sure your API credentials is correct, please whitelist your own IP address
                    <a target="_blank" href="https://dashboard.moceanapi.com/user/apisetting">here</a>
                </p>
                <p>
                    Your server's IP address is: <b><?php echo $client_ip_address; ?></b>
                </p>
            <?php } ?>
        <?php
    }

	function get_additional_billing_fields() {
		$default_billing_fields   = array(
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_country',
			'billing_postcode',
			'billing_phone',
			'billing_email'
		);
		$additional_billing_field = array();
		$billing_fields           = array_filter( get_option( 'wc_fields_billing', array() ) );
		foreach ( $billing_fields as $field_key => $field_info ) {
			if ( ! in_array( $field_key, $default_billing_fields ) && $field_info['enabled'] ) {
				array_push( $additional_billing_field, $field_key );
			}
		}

		return $additional_billing_field;
	}

    public function moceansms_wc_not_activated($form_id)
    {
        if(class_exists('woocommerce')) { return; }
        if(!($form_id === 'moceansms_admin_setting' || $form_id === 'moceansms_customer_setting')) { return; }
        ?>
        <div class="wrap">
            <h1>MoceanAPI Woocommerce Order Notification</h1>
            <p>This feature requires WooCommerce to be activated</p>
        </div>
        <?php
    }

    public function get_user_ip() {
        return Utils::curl_get_file_contents("https://ipecho.net/plain");
    }

    public function get_country_code_from_ip($ip_address)
    {
        $api_url = "https://www.iplocate.io/api/lookup/{$ip_address}";
        try {
            $c = curl_init();
            curl_setopt( $c , CURLOPT_URL , $api_url);
            curl_setopt( $c , CURLOPT_USERAGENT, "Mozilla/5.0 (Linux Centos 7;) Chrome/74.0.3729.169 Safari/537.36");
            curl_setopt( $c , CURLOPT_RETURNTRANSFER, true);
            curl_setopt( $c , CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt( $c , CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt( $c , CURLOPT_TIMEOUT, 10000); // 10 sec
            $response = json_decode(curl_exec($c), 1);
            curl_close($c);


            if(!empty($response['error'])) {
                $this->log->add("MoceanAPI", "Unable to get country code for IP address: {$ip_address}");
                $this->log->add("MoceanAPI", "Error from API request: {$response['error']}");
                return ''; // ''
            }

            $country_code = $response['country_code'];

            $this->log->add("MoceanAPI", "Resolved {$ip_address} to country code: {$country_code}");
            return $country_code;

        } catch (Exception $e) {
            $this->log->add("MoceanAPI", "Error occured. Failed to get country code from ip address: {$ip_address}");
            $this->log->add("MoceanAPI", print_r($e->getMessage(), 1));
            return '';
        }
    }

    public function get_countries()
    {
        return array(
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "IO" => "British Indian Ocean Territory",
            "BN" => "Brunei Darussalam",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos (Keeling) Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo",
            "CD" => "Congo, the Democratic Republic of the",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "CI" => "Cote D'Ivoire",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands (Malvinas)",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and Mcdonald Islands",
            "VA" => "Holy See (Vatican City State)",
            "HN" => "Honduras",
            "HK" => "Hong Kong",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran, Islamic Republic of",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Lao People's Democratic Republic",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libyan Arab Jamahiriya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macao",
            "MK" => "Macedonia, the Former Yugoslav Republic of",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "MX" => "Mexico",
            "FM" => "Micronesia, Federated States of",
            "MD" => "Moldova, Republic of",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territory, Occupied",
            "PA" => "Panama",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RE" => "Reunion",
            "RO" => "Romania",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "ST" => "Sao Tome and Principe",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "CS" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syrian Arab Republic",
            "TW" => "Taiwan, Province of China",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania, United Republic of",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "UM" => "United States Minor Outlying Islands",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VE" => "Venezuela",
            "VN" => "Viet Nam",
            "VG" => "Virgin Islands, British",
            "VI" => "Virgin Islands, U.s.",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe"
        );
    }
}

?>
