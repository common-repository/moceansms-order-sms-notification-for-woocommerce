<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 2/18/2019
 * Time: 5:46 PM.
 */

class Moceanapi_Multivendor_Wc_Vendors_Manager extends Abstract_Moceansms_Multivendor {
	public function __construct( Moceansms_WooCoommerce_Logger $log = null ) {
		parent::__construct( $log );
	}

	public function setup_mobile_number_setting_field( $user ) {
		?>
        <h3 class="heading">MoceanAPI Woocommerce</h3>
        <table class="form-table">
            <tr>
                <th><label for="moceansms_phone_field">Phone</label></th>
                <td>
                    <input type="text" class="input-text" id="moceansms_phone_field" name="moceansms_phone_field"
                           value="<?php echo esc_attr( get_the_author_meta( 'moceansms_phone', $user->ID ) ) ?>"/>
                    <p class="description">Fill this field to enable sms feature for vendor</p>
                </td>
            </tr>
        </table>
		<?php
	}

	public function save_mobile_number_setting( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$moceansms_phone_field = sanitize_text_field( $_POST['moceansms_phone_field'] );

		update_user_meta( $user_id, 'moceansms_phone', $moceansms_phone_field );
	}

	public function get_vendor_mobile_number_from_vendor_data( $vendor_data ) {
		return get_user_meta( $this->get_vendor_id_from_item( $vendor_data['item'] ), 'moceansms_phone', true );
	}

	public function get_vendor_country_from_vendor_data($vendor_data){
		$selected_country_code        = moceansms_get_options( 'moceansms_woocommerce_country_code', 'moceansms_setting', '' );//Get default country v1.1.17
		return $selected_country_code;
	}

	public function get_vendor_shop_name_from_vendor_data( $vendor_data ) {
		return WCV_Vendors::get_vendor_shop_name( $vendor_data['vendor_user_id'] );
	}

	public function get_vendor_id_from_item( WC_Order_Item $item ) {
		return WCV_Vendors::get_vendor_from_product( $item->get_product_id() );
	}

	public function get_vendor_profile_from_item( WC_Order_Item $item ) {
		return get_user_meta( $this->get_vendor_id_from_item( $item ) );
	}

	public function get_vendor_data_list_from_order( $order_id ) {
		$order = wc_get_order( $order_id );
		$items = $order->get_items();

		$vendor_data_list = array();

		foreach ( $items as $item ) {
			$vendor_data_list[] = array(
				'item'           => $item,
				'vendor_user_id' => $this->get_vendor_id_from_item( $item ),
				'vendor_profile' => $this->get_vendor_profile_from_item( $item )
			);
		}

		$this->log->add( 'MoceanSMS', 'Raw data: ' . json_encode( $vendor_data_list ) );

		return $this->perform_grouping( $vendor_data_list );
	}
}
