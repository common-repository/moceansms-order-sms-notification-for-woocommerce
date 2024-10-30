<?php

/*
Plugin Name: MoceanAPI WooCommerce
Plugin URI:  https://dashboard.moceanapi.com
Description: MoceanAPI Order SMS Notification for WooCommerce
Version:     1.4.12
Author:      Micro Ocean Technologies
Author URI:  https://moceanapi.com
License:     GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: moceansms-woocommerce
*/

use MoceanAPI_WC\Loader;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'mocean_fs' ) ) {
    // Create a helper function for easy SDK access.
    function mocean_fs() {
        global $mocean_fs;

        if ( ! isset( $mocean_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/lib/freemius/start.php';

            $mocean_fs = fs_dynamic_init( array(
                'id'                  => '10109',
                'slug'                => 'moceansms-order-sms-notification-for-woocommerce',
                'type'                => 'plugin',
                'public_key'          => 'pk_e43ddd98007b9678c00aee2ee98a2',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'moceansms-woocoommerce-setting',
                    'override_exact' => true,
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'options-general.php',
                    ),
                ),
            ) );
        }

        return $mocean_fs;
    }

    // Init Freemius.
    mocean_fs();
    // Signal that SDK was initiated.
    do_action( 'mocean_fs_loaded' );

    function mocean_fs_settings_url() {
        return admin_url( 'options-general.php?page=moceansms-woocoommerce-setting' );
    }

    mocean_fs()->add_filter('connect_url', 'mocean_fs_settings_url');
    mocean_fs()->add_filter('after_skip_url', 'mocean_fs_settings_url');
    mocean_fs()->add_filter('after_connect_url', 'mocean_fs_settings_url');
    mocean_fs()->add_filter('after_pending_connect_url', 'mocean_fs_settings_url');
}

define("MOCEANSMS_PLUGIN_URL", plugin_dir_url(__FILE__));
define("MOCEANSMS_PLUGIN_DIR", plugin_dir_path(__FILE__));
define("MOCEANSMS_INC_DIR", MOCEANSMS_PLUGIN_DIR . "includes/");
define("MOCEANSMS_ADMIN_VIEW", MOCEANSMS_PLUGIN_DIR . "admin/");
define("MOCEANSMS_TEXT_DOMAIN", "moceansms-woocoommerce");
define("MOCEAN_DB_TABLE_NAME", "moceansms_wc_send_sms_outbox");

require_once MOCEANSMS_PLUGIN_DIR . 'lib/action-scheduler/action-scheduler.php';

add_action( 'plugins_loaded', 'moceansms_woocommerce_init', PHP_INT_MAX );

function moceansms_install() {

    include_once MOCEANSMS_PLUGIN_DIR . '/install.php';
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $create_sms_send );
}

register_activation_hook(__FILE__, 'moceansms_install');

function moceansms_cleanup() {
    delete_option("moceansms_plugin_version");
    delete_option("moceansms_domain_reachable");
}

register_deactivation_hook(__FILE__, 'moceansms_cleanup');

function moceansms_woocommerce_init() {
    require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
	require_once ABSPATH . '/wp-includes/pluggable.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'interfaces/Moceansms_PluginInterface.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/contracts/class-moceansms-register-interface.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-freemius.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-helper.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-woocommerce-frontend-scripts.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-woocommerce-hook.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-woocommerce-register.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-woocommerce-logger.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-woocommerce-notification.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-woocommerce-widget.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-download-log.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/class-moceansms-sendsms.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'includes/multivendor/class-moceansms-multivendor.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'lib/MoceanSMS.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'lib/class.settings-api.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'admin/class-moceansms-woocommerce-setting.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'admin/sendsms.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'admin/smsoutbox.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'admin/automation.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'admin/logs.php';
	require_once MOCEANSMS_PLUGIN_DIR . 'admin/help.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanS2Member.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanARMemberLite.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanARMemberPremium.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanMemberPress.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanMemberMouse.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanSimpleMembership.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanRestaurantReservation.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanQuickRestaurantReservation.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanBookIt.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanLatePoint.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanFATService.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanWpERP.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanJetpackCRM.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanFluentCRM.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanGroundhoggCRM.php';
    require_once MOCEANSMS_PLUGIN_DIR . 'includes/plugins/MoceanSupportedPlugin.php';

    // load all Forms integrations
    Loader::load();

	//create notification instance
	$moceansms_notification = new Moceansms_WooCommerce_Notification();

	//register hooks and settings
	$registerInstance = new Moceansms_WooCommerce_Register();
	$registerInstance->add( new MoceanSMS_Freemius() )
	                 ->add( new Moceansms_WooCommerce_Hook( $moceansms_notification ) )
	                 ->add( new Moceansms_WooCommerce_Setting() )
	                 ->add( new Moceansms_WooCommerce_Widget() )
	                 ->add( new Moceansms_WooCommerce_Frontend_Scripts() )
	                 ->add( new Moceansms_Multivendor() )
	                 ->add( new Moceansms_Download_log() )
	                 ->add( new MoceanSMS_SendSMS_View() )
	                 ->add( new MoceanSMS_Automation_View() )
	                 ->add( new MoceanSMS_SMSOutbox_View() )
	                 ->add( new MoceanSMS_Logs_View() )
	                 ->add( new MoceanSMS_Help_View() )
	                 ->load();
}

