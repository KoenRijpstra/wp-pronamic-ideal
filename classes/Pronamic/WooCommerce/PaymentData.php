<?php

/**
 * Title: WooCommerce payment data
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_WooCommerce_PaymentData extends Pronamic_WP_Pay_PaymentData {
	/**
	 * Order
	 * 
	 * @see http://plugins.trac.wordpress.org/browser/woocommerce/tags/1.5.2.1/classes/class-wc-order.php
	 * @var WC_Order
	 */
	private $order;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initializes an WooCommerce iDEAL data proxy
	 * 
	 * @param WC_Order $order
	 */
	public function __construct( $order ) {
		parent::__construct();

		$this->order = $order;
	}

	//////////////////////////////////////////////////

	/**
	 * Get source indicator
	 * 
	 * @see Pronamic_Pay_PaymentDataInterface::getSource()
	 * @return string
	 */
	public function getSource() {
		return 'woocommerce';
	}

	public function get_source_id() {
		return $this->order->id;
	}

	//////////////////////////////////////////////////

	public function get_title() {
		return sprintf( __( 'WooCommerce order %s', 'pronamic_ideal' ), $this->get_order_id() );
	}

	/**
	 * Get description
	 * 
	 * @see Pronamic_Pay_PaymentDataInterface::get_description()
	 * @return string
	 */
	public function get_description() {
		return sprintf( __( 'Order %s', 'pronamic_ideal' ), $this->get_order_id() );
	}

	/**
	 * Get order ID
	 * 
	 * @see Pronamic_Pay_PaymentDataInterface::get_order_id()
	 * @return string
	 */
	public function get_order_id() {
		// @see https://github.com/woothemes/woocommerce/blob/v1.6.5.2/classes/class-wc-order.php#L269
		$order_id = $this->order->get_order_number();

		/*
		 * An '#' charachter can result in the following iDEAL error:
		 * code             = SO1000
		 * message          = Failure in system
		 * detail           = System generating error: issuer
		 * consumer_message = Paying with iDEAL is not possible. Please try again later or pay another way.
		 * 
		 * Or in case of Sisow:
		 * <errorresponse xmlns="https://www.sisow.nl/Sisow/REST" version="1.0.0">
		 *     <error>
		 *         <errorcode>TA3230</errorcode>
		 *         <errormessage>No purchaseid</errormessage>
		 *     </error>
		 * </errorresponse>
		 * 
		 * @see http://wcdocs.woothemes.com/user-guide/extensions/functionality/sequential-order-numbers/#add-compatibility
		 * 
		 * @see page 30 http://pronamic.nl/wp-content/uploads/2012/09/iDEAL-Merchant-Integratie-Gids-NL.pdf
		 * 
		 * The use of characters that are not listed above will not lead to a refusal of a batch or post, but the 
		 * character will be changed by Equens (formerly Interpay) to a space, question mark or asterisk. The 
		 * same goes for diacritical characters (à, ç, ô, ü, ý etcetera).
		 */
		$order_id = str_replace( '#', '', $order_id );

		return $order_id;
	}

	/**
	 * Get items
	 * 
	 * @see Pronamic_Pay_PaymentDataInterface::getItems()
	 * @return Pronamic_IDeal_Items
	 */
	public function getItems() {
		// Items
		$items = new Pronamic_IDeal_Items();

		// Item
		// We only add one total item, because iDEAL cant work with negative price items (discount)
		$item = new Pronamic_IDeal_Item();
		$item->setNumber( $this->get_order_id() );
		$item->setDescription( $this->get_description() );
		// @see http://plugins.trac.wordpress.org/browser/woocommerce/tags/1.5.2.1/classes/class-wc-order.php#L50
		$item->setPrice( $this->order->order_total );
		$item->setQuantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	//////////////////////////////////////////////////
	// Currency
	//////////////////////////////////////////////////

	/**
	 * Get currency
	 * 
	 * @see Pronamic_Pay_PaymentDataInterface::get_currency_alphabetic_code()
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		// @see http://plugins.trac.wordpress.org/browser/woocommerce/tags/1.5.2.1/admin/woocommerce-admin-settings.php#L32
		return get_option( 'woocommerce_currency' );
	}

	//////////////////////////////////////////////////
	// Customer
	//////////////////////////////////////////////////

	public function get_email() {
		// @see http://plugins.trac.wordpress.org/browser/woocommerce/tags/1.5.2.1/classes/class-wc-order.php#L30
		return $this->order->billing_email;
	}

	public function getCustomerName() {
		// @see http://plugins.trac.wordpress.org/browser/woocommerce/tags/1.5.2.1/classes/class-wc-order.php#L21
		return $this->order->billing_first_name . ' ' . $this->order->billing_last_name;
	}

	public function getOwnerAddress() {
		// @see http://plugins.trac.wordpress.org/browser/woocommerce/tags/1.5.2.1/classes/class-wc-order.php#L24
		return $this->order->billing_address_1;
	}

	public function getOwnerCity() {
		return $this->order->billing_city;
	}

	public function getOwnerZip() {
		// http://plugins.trac.wordpress.org/browser/woocommerce/tags/1.5.2.1/classes/class-wc-order.php#L26
		return $this->order->billing_postcode;
	}

	//////////////////////////////////////////////////
	// URL's
	//////////////////////////////////////////////////

	/**
	 * Get normal return URL
	 * @see https://github.com/woothemes/woocommerce/blob/v2.0.10/classes/abstracts/abstract-wc-payment-gateway.php#L49
	 * 
	 * @see Pronamic_Pay_PaymentDataInterface::get_normal_return_url()
	 * @return string
	 */
	public function get_normal_return_url() { 
		$thanks_page_id = woocommerce_get_page_id( 'thanks' );

		// Base URL
		if ( $thanks_page_id ) {
			$return_url = get_permalink( $thanks_page_id );
		} else {
			$return_url = home_url();
		}

		// Add query arguments to URL
		$return_url = add_query_arg( array(
			'key'   => $this->order->order_key,
			'order' => $this->order->id
		), $return_url );

		// SSL check
		if ( is_ssl() || get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' ) {
			$return_url = str_replace( 'http:', 'https:', $return_url );
		}

		return apply_filters( 'woocommerce_get_return_url', $return_url );
	}
	
	public function get_cancel_url() {
		return $this->order->get_cancel_order_url();
	}
	
	public function get_success_url() {
		return $this->get_normal_return_url();
	}
	
	public function get_error_url() {
		return $this->order->get_checkout_payment_url();
	}
}
