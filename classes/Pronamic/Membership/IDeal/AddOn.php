<?php

/**
 * Title: WPMU Membership iDEAL add-on
 * Copyright: Pronamic (c) 2005 - 2013
 * Company: Pronamic
 * @author Leon Rowland <leon@rowland.nl>
 * @since 1.2.6
 */
class Pronamic_Membership_IDeal_AddOn {
	/**
	 * The slug of this addon
	 * 
	 * @var string
	 */
	const SLUG = 'membership';

	/**
	 * Indiactor for the config id options
	 * 
	 * @var string
	 */
	const OPTION_CONFIG_ID = 'pronamic_pay_membership_config_id';
	
	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
	}
	
	//////////////////////////////////////////////////

	/**
	 * Plugins loaded
	 */
	public static function plugins_loaded() {
		if ( Pronamic_Membership_Membership::is_active() ) {
			// Register the Membership iDEAL gateway
			M_register_gateway( 'pronamic_ideal', 'Pronamic_Membership_IDeal_IDealGateway' );

			$slug = self::SLUG;

			add_filter( "pronamic_payment_source_text_$slug",   array( __CLASS__, 'source_text' ), 10, 2 );
			
			if ( is_admin() ) {
				$admin = new Pronamic_WP_Pay_Membership_Admin();
			}
		}
	}

	//////////////////////////////////////////////////
	
	/**
	 * Source text
	 * 
	 * @param text $text
	 * @param Pronamic_Pay_Payment $payment
	 * @return string
	 */
	public static function source_text( $text, Pronamic_Pay_Payment $payment ) {
		$text  = '';

		$text .= __( 'Membership', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>', 
			add_query_arg( array(
				'page'    => 'membershipgateways',
				'action'  => 'transactions',
				'gateway' => 'pronamic_ideal'
			), admin_url( 'admin.php') ),
			sprintf( __( 'Transaction #%s', 'pronamic_ideal' ), $payment->get_id() )
		);

		return $text;
	}
}
