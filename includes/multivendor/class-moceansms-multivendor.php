<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 4/10/2019
 * Time: 2:47 PM.
 */

class Moceansms_Multivendor implements Moceansms_Register_Interface {
	public function register() {
		$this->required_files();
		//create notification instance
		$moceansms_notification = new Moceansms_Multivendor_Notification( 'Wordpress-Woocommerce-Multivendor-Extension-' . Moceansms_Multivendor_Factory::$activatedPlugin );

		$registerInstance = new Moceansms_WooCommerce_Register();
		$registerInstance->add( new Moceansms_Multivendor_Hook( $moceansms_notification ) )
		                 ->add( new Moceansms_Multivendor_Setting() )
		                 ->load();
	}

	protected function required_files() {
		require_once __DIR__ . '/admin/class-moceansms-multivendor-setting.php';
		require_once __DIR__ . '/abstract/abstract-moceansms-multivendor.php';
		require_once __DIR__ . '/contracts/class-moceansms-multivendor-interface.php';
		require_once __DIR__ . '/class-moceansms-multivendor-factory.php';
		require_once __DIR__ . '/class-moceansms-multivendor-hook.php';
		require_once __DIR__ . '/class-moceansms-multivendor-notification.php';
	}
}
