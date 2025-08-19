<?php

namespace Opencart\Catalog\Controller\Extension\TelrApplepayOc\Payment;

class TelrApplepay extends \Opencart\System\Engine\Controller {

	public function index(): string {
		header('Set-Cookie: ' . $this->config->get('session_name') . '=' . $this->session->getId() . '; HttpOnly ; SameSite=None; Secure');
		
		$this->load->language('extension/telr_applepay_oc/payment/telr_applepay');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$data['amount'] = $this->currency->format(
			$order_info['total'],
			$order_info['currency_code'],
			$order_info['currency_value'],
			false);
		$data['currency_code'] = $order_info['currency_code'];
		$data['country_code'] = $order_info['payment_iso_code_2'];
		$data['language'] = $this->config->get('config_language');
		$data['apple_mercahnt_id'] = $this->config->get('payment_telr_applepay_merchant_identifier');
		return $this->load->view('extension/telr_applepay_oc/payment/telr_applepay', $data);
	}
	
	public function certificatevalidation(){
		$url          = $this->request->post['url'];
        $domain       = $this->config->get('payment_telr_applepay_domain');
        $display_name = $this->config->get('payment_telr_applepay_domain_name');
        $merchant_id     = $this->config->get('payment_telr_applepay_merchant_identifier');
        $certificate     = $this->config->get('payment_telr_applepay_merchant_certificate');
        $certificate_key = $this->config->get('payment_telr_applepay_merchant_key');

        if (
            'https' === parse_url( $url, PHP_URL_SCHEME ) &&
            substr( parse_url( $url, PHP_URL_HOST ), - 10 ) === '.apple.com'
        ) {
            $ch = curl_init();
            $data =
                '{
                  "merchantIdentifier":"' . $merchant_id . '",
                  "domainName":"' . $domain . '",
                  "displayName":"' . $display_name . '"
              }';
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_SSLCERT, $certificate );
            curl_setopt( $ch, CURLOPT_SSLKEY, $certificate_key );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );

            if ( curl_exec( $ch ) === false ) {
                echo '{"curlError":"' . curl_error( $ch ) . '"}';
            }
            curl_close( $ch );            
        }
		exit();		
	}
	
	public function paymentprocess(){
		$objTransaction='';
		$objError='';

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$amount= $this->currency->format(
			$order_info['total'],
			$order_info['currency_code'],
			$order_info['currency_value'],
			false);
		$cart_desc=trim($this->config->get('payment_telr_applepay_purdesc'));
		if (empty($cart_desc)) {
			$cart_desc='Order ID {order} ';
		}
		$order_id = trim($order_info['order_id']);
		$cart_id = $order_id.'~'.(string)time();
		$cart_desc=str_replace('{order}', $order_id, $cart_desc);
		
		$post_data = Array(
			'ivp_method'	=> 'applepay',
			'ivp_authkey'		=> $this->config->get('payment_telr_applepay_authkey'),
			'ivp_store'		=> $this->config->get('payment_telr_applepay_store'),
			'ivp_cart'		=> $cart_id,
			'ivp_amount'	=> $amount,
			'ivp_currency'	=> trim($order_info['currency_code']),
			'ivp_test'		=> '0',
			'ivp_desc'		=> trim($cart_desc),
			'ivp_source'	=> trim('OpenCart '.VERSION),
			'ivp_trantype'  => 'sale',
			'ivp_tranclass' => "ecom"
		);
		
		//Billing details
		$post_data['bill_fname'] = trim($order_info['payment_firstname']);
		$post_data['bill_sname'] = trim($order_info['payment_lastname']);
		$post_data['bill_addr1'] = trim($order_info['payment_address_1']);
		$post_data['bill_addr2'] = trim($order_info['payment_address_2']);
		$post_data['bill_addr3'] = '';
		$post_data['bill_city'] = trim($order_info['payment_city']);
		$post_data['bill_region'] = trim($order_info['payment_zone']);
		$post_data['bill_zip'] = trim($order_info['payment_postcode']);
		$post_data['bill_country'] = trim($order_info['payment_iso_code_2']);
		$post_data['bill_email'] = trim($order_info['email']);
		$post_data['bill_phone1'] = trim($order_info['telephone']);
		
		//ApplePay details
		$post_data['applepay_enc_version'] = $this->request->post['applepayversion'];
		$post_data['applepay_enc_paydata'] = urlencode($this->request->post['applepaydata']);
		$post_data['applepay_enc_paysig'] = urlencode($this->request->post['applepaysignature']);
		$post_data['applepay_enc_pubkey'] = urlencode($this->request->post['applepaykey']);
		$post_data['applepay_enc_keyhash'] = $this->request->post['applepaykeyhash'];
		$post_data['applepay_tran_id'] = $this->request->post['applepaytransactionid'];
		$post_data['applepay_card_desc'] = $this->request->post['applepaytype'];
		$post_data['applepay_card_scheme'] = $this->request->post['applepaydisplayname'];
		$post_data['applepay_card_type'] = $this->request->post['applepaynetwork'];
		$post_data['applepay_tran_id2'] = $this->request->post['applepaytransactionidentifier'];

		$returnData = $this->_requestGateway($post_data);

		if (isset($returnData['transaction'])) { $objTransaction = $returnData['transaction']; }
		if (isset($returnData['error'])) { $objError = $returnData['error']; }
		if (is_array($objError)) {
			$json = [ 'redirect' => $this->url->link('checkout/failure') ];
		}else{
			$txStatus = $objTransaction['status'];
			$txRef = $objTransaction['ref'];
			if (($txStatus=='P') || ($txStatus=='H')) {
				// Transaction status of pending or held
				$this->payment_pending($order_id,$txRef);
			}
			if ($txStatus=='A') {
				// Transaction status = authorised
				$this->payment_authorised($order_id,$txRef);
			}
			$json = [ 'redirect' => $this->url->link('checkout/success') ];
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	private function _requestGateway($post_data)
	{
		$url='https://secure.telr.com/gateway/remote.json';
		$fields='';
		foreach ($post_data as $k => $v) {
			$fields.=$k .'='.$v . '&';
		}
		$fields = rtrim($fields, '&');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($fields)));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		/*curl_setopt($ch,CURLOPT_POST, count($fields));*/
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,10);
		curl_setopt($ch,CURLOPT_TIMEOUT, 30);
		$returnData = json_decode(curl_exec($ch),true);
		//$returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $returnData;
	}

	public function payment_authorised($order_id,$txref) {
		$this->load->model('checkout/order');
		$message='Payment Completed: '.$txref;
		$order_status = $cart_desc=trim($this->config->get('payment_telr_applepay_comp_status_id'));
		if (empty($order_status)) {
			$order_status='2'; // Order status 2 = Processing
		}
		$this->model_checkout_order->addHistory(
			$order_id,		// Order ID
			$order_status,		// New order status
			$message,		// Message text to add to order history
			true);			// Notify customer
	}

	public function payment_cancelled($order_id,$txref) {
		$this->load->model('checkout/order');
		$message='Payment Cancelled: '.$txref;
		$order_status = $cart_desc=trim($this->config->get('payment_telr_applepay_void_status_id'));
		if (empty($order_status)) {
			$order_status='7'; // Order status 2 = Cancelled
		}

		$pendingStatusId = $this->config->get('payment_telr_applepay_pend_status_id');
		$failedStatusId = $this->config->get('payment_telr_applepay_void_status_id');
		$currentStatusId = $this->get_order_status_id($order_id);

		if($currentStatusId == $pendingStatusId || $currentStatusId == $failedStatusId){
			$this->model_checkout_order->addHistory(
				$order_id,		// Order ID
				$order_status,		// New order status
				$message,		// Message text to add to order history
				true);			// Notify customer
		}
	}

	public function payment_pending($order_id,$txref) {
		$this->load->model('checkout/order');
		$message='Payment Pending: '.$txref;
		$order_status = $cart_desc=trim($this->config->get('payment_telr_applepay_pend_status_id'));
		if (empty($order_status)) {
			$order_status='1'; // Order status 1 = Pending
		}

		$pendingStatusId = $this->config->get('payment_telr_applepay_pend_status_id');
		$failedStatusId = $this->config->get('payment_telr_applepay_void_status_id');
		$currentStatusId = $this->get_order_status_id($order_id);

		if($currentStatusId == $pendingStatusId || $currentStatusId == $failedStatusId){
			$this->model_checkout_order->addHistory(
				$order_id,		// Order ID
				$order_status,	// New order status
				$message,		// Message text to add to order history
				true);			// Notify customer
		}
	}

	private function get_order_status_id($order_id){
		$query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order WHERE order_id = " . $order_id);
		$orders = $query->rows;
		if(count($orders) > 0){
			return $orders[0]['order_status_id'];
		}else{
			return 0;
		}
	}
	
}
?>
