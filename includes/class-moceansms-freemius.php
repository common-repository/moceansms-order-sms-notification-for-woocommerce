<?php

class MoceanSMS_Freemius implements Moceansms_Register_Interface {

    public function register()
    {
        mocean_fs()->add_filter('connect_message_on_update', array($this, 'mocean_fs_custom_connect_message_on_update'), 10, 6);
        mocean_fs()->add_filter('connect_message', array($this, 'mocean_fs_custom_connect_message_on_update'), 10, 6);
    }

    public function mocean_fs_custom_connect_message_on_update(
        $message,
        $user_first_name,
        $plugin_title,
        $user_login,
        $site_link,
        $freemius_link
    ) {

        return sprintf(
            __( 'Hey %1$s' ) . ',<br>' .
            __( 'Please help us improve %2$s! If you opt-in, we will collect some data about your usage of %2$s. If you skip this, that\'s okay! %2$s will still work just fine.', MOCEANSMS_TEXT_DOMAIN ),
            $user_first_name,
            '<b>' . $plugin_title . '</b>',
            '<b>' . $user_login . '</b>',
            $site_link,
            $freemius_link
        );
    }


}