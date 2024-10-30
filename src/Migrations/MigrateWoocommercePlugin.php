<?php

namespace MoceanAPI_WC\Migrations;

class MigrateWoocommercePlugin {
    public static function migrate()
    {
        $setting_ids_to_iterate = ["moceansms_setting", "moceansms_admin_setting", "moceansms_customer_setting", "moceansms_multivendor_setting"];

        foreach($setting_ids_to_iterate as $setting_id) {
            // check if order notifciation plugin setting is set
            $setting = get_option($setting_id);
            if(empty($setting)) {
                // check moceanapi-sendsms
                $sendsms_setting_id = preg_replace("/moceansms_/", "moceanapi_", $setting_id, 1);
                $sendsms_setting = get_option($sendsms_setting_id);
                if(!empty($sendsms_setting)) {
                    // if user have moceanapi-sendsms setting, we overwrite it to order notification
                    $new_option = [];
                    foreach($sendsms_setting as $key => $value) {
                        $new_key = preg_replace("/moceanapi_/", "moceansms_woocommerce_", $key, 1);
                        $new_option[$new_key] = $value;

                    }
                    update_option($setting_id, $new_option);
                }
            }
        }


    }
}