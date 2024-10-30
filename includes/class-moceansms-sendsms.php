<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class MoceanSMS_SendSMS_Sms {

	public static function send_sms($sms_from, $phone_no, $message, $medium='wordpress_order_notification') {
        if(empty($phone_no)) {
            return;
        }

        $medium='wordpress_order_notification';
	    $log = new Moceansms_WooCoommerce_Logger();

	    $api_key = moceansms_get_options('moceansms_woocommerce_api_key', 'moceansms_setting');
	    $api_secret = moceansms_get_options('moceansms_woocommerce_api_secret', 'moceansms_setting');
	    $sms_sender = moceansms_get_options('moceansms_woocommerce_sms_from', 'moceansms_setting');

	    if($api_key == '' || $api_key == '') return;
        $sms_from = !empty($sms_from) ? $sms_from : (!empty($sms_sender) ? $sms_sender : "MoceanSMS");

	    $log->add('MoceanSMS', 'Sending SMS to '.$phone_no.', message: '.$message);

	    try {
	        $moceansms_rest = new MoceanSMS($api_key, $api_secret);
	        $rest_response = $moceansms_rest->sendSMS($sms_from, $phone_no, $message, $medium);

	        self::insertToOutbox($sms_from, $phone_no, $message, "Message sent");

	        $log->add('MoceanSMS', 'SMS response from SMS gateway: ' .$rest_response);

	  		return 'true';

	    } catch (Exception $e) {
	        $log->add('MoceanSMS', 'Failed sent SMS: ' . $e->getMessage());
	    }

	}

	public static function moceanapi_get_account_balance($api_key, $api_secret){

	    $moceansms_rest = new MoceanSMS($api_key, $api_secret);
	    $rest_response = $moceansms_rest->accountBalance();

	    $rest_response = json_decode($rest_response);

	    if($rest_response->{'status'} == 0){
			return $rest_response->{'value'};
	    }
	}

	public static function getPhoneNumber($message_to, $customer, $phone, $country, $filters='', $criteria=''){
        // Validate phone numbers here

		switch($message_to) {
		    case "customer_all":
                $numbers = self::getValidatedPhoneNumbers(get_users());
		    	#$numbers = self::getAllUsersPhones();
		    	break;
		    case "customer":
		    	$numbers = self::getValidatedPhoneNumbers($customer);
		    	// $numbers = self::getSpecificCustomerPhones($customer);
		    	break;
		    case "spec_group_ppl":
		    	$numbers = self::getFilteredUsers($filters, $criteria);
		    	// $numbers = self::getSpecificCustomerPhones($customer);
		    	break;
		    case "phones":
		    	$numbers = self::getUsersPhones($phone);
		    	break;
		    default: break;
		}

		return $numbers;
	}

    public static function getFilteredUsers($filters, $criteria) {

        $filtered_users = array();

        // get all users
        // filter them using filters and criteria
        if($filters == 'roles') {

            $args = array(
                'role__in' => $criteria,
            );

            $filtered_users = get_users($args);

        }

        if($filters == 'country') {

            $args = array(
                'meta_key' => 'country',
                'meta_value' => $criteria,
            );

            $filtered_users = get_users($args);

        }

        if ($filters == 'status') {
            $args = array(
                'meta_key' => 'account_status',
                'meta_value' => $criteria,
            );

            $filtered_users = get_users($args);
        }

        if ($filters == 'membership_level') {
            global $wpdb;
            #$wpdb->prepare($sql_query, implode(', ', $criteria));
            $sql_query = ' SELECT user_id FROM wp_pmpro_memberships_users WHERE membership_id IN (%s) ';
            $results = $wpdb->get_results($wpdb->prepare($sql_query, implode(', ', $criteria)));

            foreach($results as $result) {
                $filtered_users[] = get_user_by("ID", $result->user_id);
            }

        }

        return self::getValidatedPhoneNumbers($filtered_users);
    }

    public static function getValidatedPhoneNumbers($users) {
        $validatedUsers = array();
        if($users) {
            if(is_array($users)) {
                foreach ($users as $user) {
                    if(!($user instanceof WP_User)) {
                        $user = get_user_by('ID', $user);
                    }

                    $phone = self::get_formatted_number($user->phone, $user->country);

                    if ($phone) {
                        $user->phone = $phone;
                        array_push($validatedUsers, $user);
                    }
                }
            }
            else {
                $phone = self::get_formatted_number($users->phone, $users->country);

                if($phone) {
                    $users->phone = $phone;
                    return $users;
                }
            }
        }

        return $validatedUsers;
    }

    public static function get_formatted_number($phone, $country = '') {
        $log = new Moceansms_WooCoommerce_Logger();
        $settings_country = !empty(moceansms_get_options('moceansms_woocommerce_country_code', 'moceansms_setting', '' )) ? moceansms_get_options('moceansms_woocommerce_country_code', 'moceansms_setting', '' ) : "US";
        $country = !empty($country) ? strtoupper($country) : strtoupper($settings_country);

        if(get_option("moceansms_domain_reachable")) {
            $request_url = "https://dashboard.moceanapi.com/public/mobileChecking?mobile_number={$phone}&country_code={$country}";
        }
        else {
            $request_url = "https://183.81.161.105:443/public/mobileChecking?mobile_number={$phone}&country_code={$country}";
        }

        $response = wp_remote_get($request_url, array( 'sslverify' => false ));
        $log->add("MoceanSMS", "request url: {$request_url}");
		if ( is_array( $response ) ) {
			$customer_phone_no = wp_remote_retrieve_body( $response );

			if ( ctype_digit( $customer_phone_no ) ) {
				return $customer_phone_no;
			}
            self::insertToOutbox("", $phone, '', "{$phone} is invalid format for country code ({$country})");
			$log->add( 'MoceanSMS', "check number api err response: {$customer_phone_no}" );

			return false;
		}

		$log->add( 'MoceanSMS', 'check number api failed' );

		return false;
    }

	private static function insertToOutbox($sender,$recipient,$message,$status){
		global $wpdb;

		$db = $wpdb;

		return $db->insert(
			MOCEAN_DB_TABLE_NAME,
			array(
				'sender'    => $sender,
				'message'   => $message,
				'recipient' => $recipient,
                'status'    => $status,
			)
		);
	}

	private static function getUsersPhones($phone_number)
	{
		$phone_number = explode(",", $phone_number);
		$phones = array();
		foreach ($phone_number as $phone) {
		 	$phones[] = $phone;
		}
		return $phones;
	}
}