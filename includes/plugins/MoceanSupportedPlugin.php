<?php
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

class MoceanSupportedPlugin {

    public function __construct() {}

    public static function get_activated_plugins()
    {
        $supported_plugins = array();
        if(MoceanS2Member::plugin_activated())
            $supported_plugins[] = MoceanS2Member::class;
        if(MoceanARMemberLite::plugin_activated())
            $supported_plugins[] = MoceanARMemberLite::class;
        if(MoceanARMemberPremium::plugin_activated())
            $supported_plugins[] = MoceanARMemberPremium::class;
        if(MoceanMemberPress::plugin_activated())
            $supported_plugins[] = MoceanMemberPress::class;
        if(MoceanMemberMouse::plugin_activated())
            $supported_plugins[] = MoceanMemberMouse::class;
        if(MoceanSimpleMembership::plugin_activated())
            $supported_plugins[] = MoceanSimpleMembership::class;

        if(MoceanRestaurantReservation::plugin_activated())
            $supported_plugins[] = MoceanRestaurantReservation::class;
        if(MoceanQuickRestaurantReservation::plugin_activated())
        $supported_plugins[] = MoceanQuickRestaurantReservation::class;
        if(MoceanBookIt::plugin_activated())
            $supported_plugins[] = MoceanBookIt::class;
        if(MoceanLatePoint::plugin_activated())
            $supported_plugins[] = MoceanLatePoint::class;
        if(MoceanFATService::plugin_activated())
            $supported_plugins[] = MoceanFATService::class;

        if(MoceanWpERP::plugin_activated())
            $supported_plugins[] = MoceanWpERP::class;
        if(MoceanJetpackCRM::plugin_activated())
            $supported_plugins[] = MoceanJetpackCRM::class;
        if(MoceanFluentCRM::plugin_activated())
            $supported_plugins[] = MoceanFluentCRM::class;
        if(MoceanGroundhoggCRM::plugin_activated())
            $supported_plugins[] = MoceanGroundhoggCRM::class;

        return $supported_plugins;
    }


}
