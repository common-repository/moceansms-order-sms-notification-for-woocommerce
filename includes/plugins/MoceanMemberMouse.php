<?php

class MoceanMemberMouse implements Moceansms_PluginInterface, Moceansms_Register_Interface {
    /*
    Plugin Name: MemberMouse
    Plugin Link: https://membermouse.com/
    */

    public static $plugin_identifier = 'membermouse';
    private $plugin_name;
    private $plugin_medium;
    private $hook_action;
    private $log;
    private $option_id;

    public function __construct() {
        $this->log = new Moceansms_WooCoommerce_Logger();
        $this->option_id = "moceansms_{$this::$plugin_identifier}";
        $this->plugin_name = 'MemberMouse';
        $this->plugin_medium = 'wp_' . str_replace( ' ', '_', strtolower($this->plugin_name));
        $this->hook_action = "moceansms_send_reminder_{$this::$plugin_identifier}";

    }

    public static function plugin_activated() {
        return is_plugin_active("membermouse/index.php");
    }

    public function register() {
        add_action('mm_member_membership_change', array($this, 'send_sms_on_status_member_membership_change'));
        add_action('mm_member_status_change',     array($this, 'send_sms_on_status_member_status_change'));
        add_action('mm_bundles_add',              array($this, 'send_sms_on_status_bundles_add'));
        add_action('mm_bundles_status_change',    array($this, 'send_sms_on_status_bundles_status_change'));
        add_action('mm_payment_received',         array($this, 'send_sms_on_status_payment_received'));
        add_action('mm_payment_rebill',           array($this, 'send_sms_on_status_payment_rebill'));
        add_action('mm_payment_rebill_declined',  array($this, 'send_sms_on_status_payment_rebill_declined'));
        add_action('mm_refund_issued',            array($this, 'send_sms_on_status_refund_issued'));
        add_action( $this->hook_action,           array($this, 'send_sms_reminder'), 10, 2);
    }

    public function get_option_id()
    {
        return $this->option_id;
    }

    public function get_setting_section_data() {
        return array(
            'id'    => $this->get_option_id(),
            'title' => __( $this->plugin_name, MOCEANSMS_TEXT_DOMAIN ),
        );
    }

    public function get_setting_field_data() {
        $setting_fields = array(
			$this->get_enable_notification_fields(),
			$this->get_send_from_fields(),
			$this->get_send_on_fields(),
		);
        foreach($this->get_reminder_fields() as $reminder) {
            $setting_fields[] = $reminder;
        }
        foreach($this->get_sms_reminder_template_fields() as $sms_reminder) {
            $setting_fields[] = $sms_reminder;
        }
        foreach($this->get_sms_template_fields() as $sms_templates) {
            $setting_fields[] = $sms_templates;
        }

        return $setting_fields;
    }

    public function get_plugin_settings($with_identifier = false) {
        $settings = array(
            "moceansms_automation_enable_notification"                     => moceansms_get_options("moceansms_automation_enable_notification", $this->get_option_id()),
            "moceansms_send_from"                                          => moceansms_get_options('moceansms_automation_send_from', $this->get_option_id()),
            "moceansms_automation_send_on"                                 => moceansms_get_options("moceansms_automation_send_on", $this->get_option_id()),
            "moceansms_automation_reminder"                                => moceansms_get_options("moceansms_automation_reminder", $this->get_option_id()),
            "moceansms_automation_reminder_custom_time"                    => moceansms_get_options("moceansms_automation_reminder_custom_time", $this->get_option_id()),
            "moceansms_automation_sms_template_rem_1"                      => moceansms_get_options("moceansms_automation_sms_template_rem_1", $this->get_option_id()),
            "moceansms_automation_sms_template_rem_2"                      => moceansms_get_options("moceansms_automation_sms_template_rem_2", $this->get_option_id()),
            "moceansms_automation_sms_template_rem_3"                      => moceansms_get_options("moceansms_automation_sms_template_rem_3", $this->get_option_id()),
            "moceansms_automation_sms_template_custom"                     => moceansms_get_options("moceansms_automation_sms_template_custom", $this->get_option_id()),
            "moceansms_automation_sms_template_member_membership_change"   => moceansms_get_options("moceansms_automation_sms_template_member_membership_change", $this->get_option_id()),
            "moceansms_automation_sms_template_member_status_change"       => moceansms_get_options("moceansms_automation_sms_template_member_status_change", $this->get_option_id()),
            "moceansms_automation_sms_template_bundles_added_to_member"    => moceansms_get_options("moceansms_automation_sms_template_bundles_added_to_member", $this->get_option_id()),
            "moceansms_automation_sms_template_bundles_status_change"      => moceansms_get_options("moceansms_automation_sms_template_bundles_status_change", $this->get_option_id()),
            "moceansms_automation_sms_template_payment_received"           => moceansms_get_options("moceansms_automation_sms_template_payment_received", $this->get_option_id()),
            "moceansms_automation_sms_template_payment_rebill"             => moceansms_get_options("moceansms_automation_sms_template_payment_rebill", $this->get_option_id()),
            "moceansms_automation_sms_template_payment_rebill_declined"    => moceansms_get_options("moceansms_automation_sms_template_payment_rebill_declined", $this->get_option_id()),
            "moceansms_automation_sms_template_refund_issued"              => moceansms_get_options("moceansms_automation_sms_template_refund_issued", $this->get_option_id()),
        );

        if ($with_identifier) {
            return array(
                self::$plugin_identifier => $settings,
            );
        }

        return $settings;
    }

    private function get_enable_notification_fields() {
        return array(
            'name'    => 'moceansms_automation_enable_notification',
            'label'   => __( 'Enable SMS notifications', MOCEANSMS_TEXT_DOMAIN ),
            'desc'    => ' ' . __( 'Enable', MOCEANSMS_TEXT_DOMAIN ),
            'type'    => 'checkbox',
            'default' => 'off'
        );
    }

    private function get_send_from_fields() {
        return array(
            'name'  => 'moceansms_automation_send_from',
            'label' => __( 'Send from', MOCEANSMS_TEXT_DOMAIN ),
            'desc'  => __( 'Sender of the SMS when a message is received at a mobile phone', MOCEANSMS_TEXT_DOMAIN ),
            'type'  => 'text',
        );
    }

    private function get_send_on_fields() {
        return array(
            'name'    => 'moceansms_automation_send_on',
            'label'   => __( 'Send notification on', MOCEANSMS_TEXT_DOMAIN ),
            'desc'    => __( 'Choose when to send a SMS notification message to your customer', MOCEANSMS_TEXT_DOMAIN ),
            'type'    => 'multicheck',
            'options' => array(
                'member_membership_change' => 'Member membership change',
                'member_status_change'     => 'Member status change',
                'bundles_added_to_member'  => 'Bundles added to member',
                'bundles_status_change'    => 'Bundles status change',
                'payment_received'         => 'Payment received',
                'payment_rebill'           => 'Payment rebill',
                'payment_rebill_declined'  => 'Payment rebill declined',
                'refund_issued'            => 'Refund issued',
            )
        );
    }

    private function get_sms_template_fields() {
        return array(
            array(
                'name'    => 'moceansms_automation_sms_template_member_membership_change',
                'label'   => __( 'Member membership change', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_member_membership_change]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your current membership is [membership_level_name]', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_member_status_change',
                'label'   => __( 'Member status change', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_member_status_change]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your membership is [status_name]', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_bundles_added_to_member',
                'label'   => __( 'Bundles added to member', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_bundles_added_to_member]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], [bundle_name] has been added to your account successfully', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_bundles_status_change',
                'label'   => __( 'Bundles status changed', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_bundles_status_change]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your [bundle_name] is [bundle_status_name]', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_payment_received',
                'label'   => __( 'Payment received', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_payment_received]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your payment of [order_total] is successful', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_payment_rebill',
                'label'   => __( 'Payment rebill', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_payment_rebill]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], recurring payment of [order_total] is successful', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_payment_rebill_declined',
                'label'   => __( 'Payment rebill declined', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_payment_rebill_declined]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], recurring payment of [order_total] is unsuccessful, talk to our support', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_refund_issued',
                'label'   => __( 'Refund issued', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_refund_issued]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], we are sorry to see you go, we have refunded your payment of [order_total]', MOCEANSMS_TEXT_DOMAIN )
            ),
        );
    }

    private function get_reminder_fields() {
        return array(
            array(
                'name'    => 'moceansms_automation_reminder',
                'label'   => __( 'Send reminder to renew membership', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => __( '', MOCEANSMS_TEXT_DOMAIN ),
                'type'    => 'multicheck',
                'options' => array(
                    'rem_1'  => '1 day before membership expiry',
                    'rem_2'  => '2 days before membership expiry',
                    'rem_3'  => '3 days before membership expiry',
                    'custom' => 'Custom time before membership expiry',
                )
            ),
            array(
                'name'  => 'moceansms_automation_reminder_custom_time',
                'label' => __( '', MOCEANSMS_TEXT_DOMAIN ),
                'desc'  => __( 'Enter the custom time you want to remind your customer before membership expires in (minutes) <br> Choose when to send a SMS reminder message to your customer <br> Please set your timezone in <a href="' . admin_url('options-general.php') . '">settings</a> <br> You must setup cronjob <a href="https://github.com/MoceanAPI/wordpress">here</a> ', MOCEANSMS_TEXT_DOMAIN ),
                'type'  => 'number',
            ),
        );
    }

    private function get_sms_reminder_template_fields() {
        return array(
            array(
                'name'    => 'moceansms_automation_sms_template_rem_1',
                'label'   => __( '1 day reminder SMS message', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_rem_1]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your [membership_level_name] subscription will expire in 1 Day, renew now to keep access.', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_rem_2',
                'label'   => __( '2 days reminder SMS message', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_rem_2]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your [membership_level_name] subscription will expire in 2 Days, renew now to keep access.', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_rem_3',
                'label'   => __( '3 days reminder SMS message', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_rem_3]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your [membership_level_name] subscription will expire in 3 Days, renew now to keep access.', MOCEANSMS_TEXT_DOMAIN )
            ),
            array(
                'name'    => 'moceansms_automation_sms_template_custom',
                'label'   => __( 'Custom time reminder SMS message', MOCEANSMS_TEXT_DOMAIN ),
                'desc'    => sprintf('Customize your SMS with <button type="button" id="moceansms-open-keyword-%1$s-[dummy]" data-attr-type="pending" data-attr-target="%1$s[moceansms_automation_sms_template_custom]" class="button button-secondary">Keywords</button>', $this->get_option_id() ),
                'type'    => 'textarea',
                'rows'    => '8',
                'cols'    => '500',
                'css'     => 'min-width:350px;',
                'default' => __( 'Hi [first_name], your [membership_level_name] subscription will expire in [reminder_custom_time] Days, renew now to keep access. - custom', MOCEANSMS_TEXT_DOMAIN )
            ),
        );
    }

    public function get_keywords_field() {
        return array(
            'member' => array(
                'first_name',
                'last_name',
                'email',
                'phone',
                'member_id',
                'status_name',
                'membership_level_name',
                'billing_address',
                'billing_city',
                'billing_state',
                'billing_zip_code',
                'billing_country',
                'shipping_address',
                'shipping_city',
                'shipping_state',
                'shipping_zip_code',
                'shipping_country',
            ),
            'bundle' => array(
                'bundle_id',
                'bundle_name',
                'bundle_status_name',
                'bundle_date_added',
                'bundle_last_updated',
            ),
            'order' => array(
                'order_number',
                'order_transaction_id',
                'order_total',
                'order_subtotal',
                'order_discount',
                'order_shipping',
                'order_shipping_method',
                'order_billing_address',
                'order_billing_city',
                'order_billing_state',
                'order_billing_zip_code',
                'order_billing_country',
                'order_shipping_address',
                'order_shipping_city',
                'order_shipping_state',
                'order_shipping_zip_code',
                'order_shipping_country',
                'order_shipping_city',
                'order_shipping_city',
            ),
            'product' => array(
                'product_id',
                'product_name',
                'product_amount',
                'product_quantity',
                'product_total',
                'product_recurring_amount',
                'product_rebill_period',
                'product_rebill_frequency',
            ),
            'moceansms' => array(
                'reminder_custom_time',
            ),
        );

    }

    private function schedule_reminders($data, $status) {
        $send_custom_reminder_flag = true;
        $settings = $this->get_plugin_settings();
        $format = get_option("date_format");

        $mm_user = new MM_User($data['member_id']);
        $this->log->add("MoceanSMS", "schedule_reminders: successfully retrieved plugin settings");
        $this->log->add("MoceanSMS", "Member ID: {$mm_user->getId()}");

        if(strtolower($mm_user->getStatusName()) != 'active') {
            $this->log->add("MoceanSMS", "member status is not active. member status: {$mm_user->getStatusName()}");
            $this->log->add("MoceanSMS", "Aborting...");
            return;
        }

        $membership_expiry_date = $mm_user->getExpirationDate();
        $membership_expiry_timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $membership_expiry_date)->getTimestamp();

        if(empty($membership_expiry_timestamp) || is_null($membership_expiry_timestamp)) {
            // maybe is lifetime account
            $this->log->add("MoceanSMS", "membership expiry date is empty or null");
            return;
        }

        // do our reminder stuff
        $as_group = "{$this::$plugin_identifier}_{$mm_user->getId()}";

        // Create date from timestamp
        $reminder_booking_date_1 = DateTime::createFromFormat('U', $membership_expiry_timestamp);
        $reminder_booking_date_1->setTimezone(wp_timezone());

        $reminder_booking_date_2 = DateTime::createFromFormat('U', $membership_expiry_timestamp);
        $reminder_booking_date_2->setTimezone(wp_timezone());

        $reminder_booking_date_3 = DateTime::createFromFormat('U', $membership_expiry_timestamp);
        $reminder_booking_date_3->setTimezone(wp_timezone());

        $reminder_booking_date_custom = DateTime::createFromFormat('U', $membership_expiry_timestamp);
        $reminder_booking_date_custom->setTimezone(wp_timezone());

        // current local time
        $current_time = date_i18n('Y-m-d H:i:s O');
        $now_date = DateTime::createFromFormat('Y-m-d H:i:s O', $current_time, wp_timezone())->format($format);
        $now_timestamp = DateTime::createFromFormat('Y-m-d H:i:s O', $current_time, wp_timezone())->getTimestamp();
        // $now_timestamp = strtotime("+1 minute", $now_timestamp);

        $this->log->add("MoceanSMS", "Membership expiry date: {$membership_expiry_date}");
        $this->log->add("MoceanSMS", "Current Local Date: {$now_date}");
        $this->log->add("MoceanSMS", "Current Local Timestamp: {$now_timestamp}");

        $custom_reminder_time = $settings['moceansms_automation_reminder_custom_time'];
        if(!ctype_digit($custom_reminder_time)) {
            $this->log->add("MoceanSMS", "reminder time (in minutes) is not digit");
            $send_custom_reminder_flag = false;
        }

        $reminder_date_1 = $reminder_booking_date_1->modify("-1 day")->getTimestamp();
        $reminder_date_2 = $reminder_booking_date_2->modify("-2 days")->getTimestamp();
        $reminder_date_3 = $reminder_booking_date_3->modify("-3 days")->getTimestamp();

        $this->log->add("MoceanSMS", "1 Day Reminder timestamp: {$reminder_date_1}");
        $this->log->add("MoceanSMS", "2 Days Reminder timestamp: {$reminder_date_2}");
        $this->log->add("MoceanSMS", "3 Days Reminder timestamp: {$reminder_date_3}");

        $this->log->add("MoceanSMS", "Unscheduling all SMS reminders for Group: {$as_group}");
        as_unschedule_all_actions('', array(), $as_group);
        $subscription = (array) $subscription->rec;
        $action_id_15 = as_schedule_single_action($reminder_date_1, $this->hook_action, array($data, 'rem_1'), $as_group );
        $action_id_30 = as_schedule_single_action($reminder_date_2, $this->hook_action, array($data, 'rem_2'), $as_group );
        $action_id_60 = as_schedule_single_action($reminder_date_3, $this->hook_action, array($data, 'rem_3'), $as_group );
        $this->log->add("MoceanSMS", "Send SMS Reminder scheduled, action_id_15 = {$action_id_15}");
        $this->log->add("MoceanSMS", "Send SMS Reminder scheduled, action_id_30 = {$action_id_30}");
        $this->log->add("MoceanSMS", "Send SMS Reminder scheduled, action_id_60 = {$action_id_60}");

        if($send_custom_reminder_flag) {
            $reminder_date_custom = $reminder_booking_date_custom->modify("-{$custom_reminder_time} minutes")->getTimestamp();
            $this->log->add("MoceanSMS", "Custom Reminder timestamp: {$reminder_date_custom}");
            $action_id_custom = as_schedule_single_action($reminder_date_custom, $this->hook_action, array($data, 'custom'), $as_group );
            $this->log->add("MoceanSMS", "Send SMS Reminder scheduled, action_id_custom = {$action_id_custom}");
        }

    }

    public function send_sms_reminder($data, $status)
    {
        if( (! isset($data['member_id'])) || empty($data['member_id'])) {
            $this->log->add("MoceanSMS", '$data["member_id"] is not set or empty');
            return;
        }
        $mm_user = new MM_User($data['member_id']);
        $this->log->add("MoceanSMS", 'Converted $mm_user to an instance of MM_User');

        $this->log->add("MoceanSMS", "User ID: {$mm_user->getId()}");
        $this->log->add("MoceanSMS", "Status: {$status}");

        if(strtolower($mm_user->getStatusName()) != 'active') {
            $this->log->add("MoceanSMS", "member status is not active. member status: {$mm_user->getStatusName()}");
            $this->log->add("MoceanSMS", "Aborting send_sms_reminder");
            return;
        }

        // membership already expired
        $membership_expiry_date = DateTime::createFromFormat('Y-m-d H:i:s', $mm_user->getExpirationDate());
        $membership_expiry_timestamp = $membership_expiry_date->getTimestamp();

        $now_timestamp = current_datetime()->getTimestamp();

        // membership already expired
        if($now_timestamp >= $membership_expiry_timestamp) {
            $this->log->add("MoceanSMS", "membership expiry date is in the past");
            return;
        }

        $settings = $this->get_plugin_settings();

        $enable_notifications = $settings['moceansms_automation_enable_notification'];
        $reminder = $settings['moceansms_automation_reminder'];

        $this->log->add("MoceanSMS", "Successfully retrieved plugin settings");

        if($enable_notifications === "on"){
            $this->log->add("MoceanSMS", "enable_notifications: {$enable_notifications}");
            if(!empty($reminder) && is_array($reminder)) {
                if(array_key_exists($status, $reminder)) {
                    $this->log->add("MoceanSMS", "Sending reminder now");
                    $this->send_customer_notification($data, $status);
                }
            }
        }
    }

    public function send_sms_on($data, $status)
    {
        $plugin_settings = $this->get_plugin_settings();
        $enable_notifications = $plugin_settings['moceansms_automation_enable_notification'];
        $send_on = $plugin_settings['moceansms_automation_send_on'];

        if($enable_notifications === "on"){
            if(!empty($send_on) && is_array($send_on)) {
                if(array_key_exists($status, $send_on)) {
                    $this->send_customer_notification($data, $status);
                }
            }
        }
        return;
    }

    public function send_sms_on_status_member_membership_change($data) {
        $status = 'member_membership_change';
        $this->schedule_reminders($data, $status);
        $this->send_sms_on($data, $status);
	}

    public function send_sms_on_status_member_status_change($data) {
        $status = 'member_status_change';
        $this->send_sms_on( $data, $status);
	}

    public function send_sms_on_status_bundles_added_to_member($data) {
        $status = 'bundles_added_to_member';
        $this->send_sms_on( $data, $status);
	}

    public function send_sms_on_status_bundles_status_change($data) {
        $status = 'bundles_status_change';
        $this->send_sms_on( $data, $status);
	}
    public function send_sms_on_status_payment_received($data) {
        $status = 'payment_received';
        $this->send_sms_on( $data, $status);
	}
    public function send_sms_on_status_payment_rebill($data) {
        $status = 'payment_rebill';
        $this->send_sms_on( $data, $status);
	}
    public function send_sms_on_status_payment_rebill_declined($data) {
        $status = 'payment_rebill_declined';
        $this->send_sms_on( $data, $status);
	}
    public function send_sms_on_status_refund_issued($data) {
        $status = 'refund_issued';
        $this->send_sms_on( $data, $status);
	}

    public function send_customer_notification($data, $status)
    {
        $settings = $this->get_plugin_settings();
        $sms_from = $settings['moceansms_automation_send_from'];
        $phone_no = '';

        // get number from user
        // first check if the $user is an instance of WP_User object
        // else if it is a member's object.
        if(!empty($data['phone'])) {
            $phone_no = $data['phone'];
        }
        else {
            $user_ins = new WP_User($data['member_id']);

            if(empty($user_ins->phone)) { return; }

            $phone_no = $user_ins->phone;
        }

        // get message template from status
        $msg_template = $settings["moceansms_automation_sms_template_{$status}"];
        $message = $this->replace_keywords_with_value($data, $msg_template);
        MoceanSMS_SendSMS_Sms::send_sms($sms_from, $phone_no, $message, $this->plugin_medium);
    }

    /*
        returns the message with keywords replaced to original value it points to
        eg: [name] => 'customer name here'
    */
    protected function replace_keywords_with_value($data, $message)
    {
        // use regex to match all [stuff_inside]
        // return the message
        // $add_data is either gonna be $order or $bundle, cannot be both.

        $products = array();

        if(array_key_exists('order_number', $data)) {
            $products = json_decode(stripslashes($data['order_products']));
        }

        preg_match_all('/\[(.*?)\]/', $message, $keywords);

        if(!empty($keywords)) {
            foreach($keywords[1] as $keyword) {
                if(array_key_exists($keyword, $data)) {
                    $message = str_replace("[{$keyword}]", $data[$keyword], $message);
                }

                else if (!empty($products)
                        && substr($keyword, 0, strlen('product_')) === 'product_') {
                    $trimmed_keyword = str_replace('product_', '', $keyword);
                    $prods = array();
                    foreach ($products as $product) {
                        if(array_key_exists($trimmed_keyword, $product)) {
                            $prods[] = $product[$trimmed_keyword];
                        }
                        else {
                            $message = str_replace("[{$keyword}]", "", $message);
                        }
                    }

                    if(!empty($prods)) {
                        $combined_string = implode(', ', $prods);
                        $message = str_replace("[{$keyword}]", $combined_string, $message);
                    }

                }

                else if($keyword == 'reminder_custom_time') {
                    $settings = $this->get_plugin_settings();
                    $reminder_time = $settings['moceansms_automation_reminder_custom_time'];
                    $message = str_replace("[{$keyword}]", $this->seconds_to_days($reminder_time), $message);
                }

                // the keyword not exist in any of the array
                // so we just replace with empty string
                else {
                    $message = str_replace("[{$keyword}]", "", $message);
                }
            }
        }
        return $message;
    }

    private function seconds_to_days($seconds) {

        if(!ctype_digit($seconds)) {
            $this->log->add("MoceanSMS", 'seconds_to_days: $seconds is not a valid digit');
            return '';
        }

        $ret = "";

        $days = intval(intval($seconds) / (3600*24));
        if($days> 0)
        {
            $ret .= "{$days}";
        }

        return $ret;
    }

}
