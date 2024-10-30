<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 2/25/2019
 * Time: 9:59 AM.
 */

class Moceansms_WooCommerce_Widget implements Moceansms_Register_Interface {
	protected $log;

	public function __construct( Moceansms_WooCoommerce_Logger $log = null ) {
		if ( $log === null ) {
			$log = new Moceansms_WooCoommerce_Logger();
		}

		$this->log = $log;
	}

	public function register() {
		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	public function register_widget() {
		wp_add_dashboard_widget( 'msmswc_dashboard_widget', 'MoceanSMS', array( $this, 'display_widget' ) );
	}

	public function display_widget() {
		$api_key        = moceansms_get_options( 'moceansms_woocommerce_api_key', 'moceansms_setting', '' );
		$api_secret     = moceansms_get_options( 'moceansms_woocommerce_api_secret', 'moceansms_setting', '' );
		$moceansms_rest = new MoceanSMS( $api_key, $api_secret );
		try {
			$balance = json_decode( $moceansms_rest->accountBalance() );

			if ( $api_key && $api_secret ) {
				?>

                <h3><?php echo $balance->status === 0 ? "Balance: $balance->value" : urldecode( $balance->err_msg ) ?></h3>

				<?php
			} else {
				?>

                <h3>
                    Please setup API Key and API Secret in
                    <a href="<?php echo admin_url( 'options-general.php?page=moceansms-woocoommerce-setting' ) ?>">
                        MoceanSMS settings
                    </a>
                </h3>

				<?php
			}
		} catch ( Exception $exception ) {
			//errors in curl
			$this->log->add( 'MoceanSMS', 'Failed get balance: ' . $exception->getMessage() );
			?>

            <h3>
                There's some problem while showing balance, please refresh this page and try again.
            </h3>

			<?php
		}
	}
}
