<?php

/**
 * Title: OmniKassa
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Gateways_OmniKassa_Gateway extends Pronamic_Gateways_Gateway {
	public function __construct( $configuration, $data ) {
		parent::__construct(  );

		$this->set_method( Pronamic_Gateways_Gateway::METHOD_HTML_FORM );
		$this->set_require_issue_select( false );
		$this->set_amount_minimum( 0.01 );

		$this->data = $data;

		$this->client = new Pronamic_Gateways_OmniKassa_OmniKassa();
		
		$this->client->setPaymentServerUrl( $configuration->getPaymentServerUrl() );
		$this->client->setMerchantId( $configuration->getMerchantId() );
		$this->client->setKeyVersion( $configuration->getSubId() );
		$this->client->setSecretKey( $configuration->getHashKey() );
	}
	
	/////////////////////////////////////////////////

	public function start() {
		$this->transaction_id = md5( time() . $this->data->getOrderId() );
		$this->action_url     = $this->client->getPaymentServerUrl();

		$this->client->setCustomerLanguage( $this->data->getLanguageIso639Code() );
		$this->client->setCurrencyNumericCode( $this->data->getCurrencyNumericCode() );
		$this->client->setOrderId( $this->data->getOrderId() );
		$this->client->setNormalReturnUrl( site_url( '/' ) );
		$this->client->setAutomaticResponseUrl( site_url( '/' ) );
		$this->client->setAmount( $this->data->getAmount() );
		$this->client->setTransactionReference( $this->transaction_id );
	}
	
	/////////////////////////////////////////////////

	public function get_html_fields() {
		return $this->client->getHtmlFields();
	}
}
