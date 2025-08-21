<?php
namespace Opencart\Admin\Controller\Extension\TelrApplepayOc\Payment;
class TelrApplepay extends \Opencart\System\Engine\Controller {
	
	private $error = array();
	private $path = '';

	public function index() {
		
		$this->load->language('extension/telr_applepay_oc/payment/telr_applepay');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

		if(!$this->version_ok()) {
			$this->error['warning'] = "This module is not supported on this version of OpenCart. Please upgrade to OpenCart 3.0.0 or later";
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->request->post['payment_telr_applepay_defaults']='set';
			$this->model_setting_setting->editSetting('payment_telr_applepay', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');

		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_authkey'] = $this->language->get('entry_authkey');
		$data['entry_callback'] = $this->language->get('entry_callback');
		$data['entry_purdesc'] = $this->language->get('entry_purdesc');
		$data['entry_total'] = $this->language->get('entry_total');		
		$data['entry_pend_status'] = $this->language->get('entry_pend_status');
		$data['entry_comp_status'] = $this->language->get('entry_comp_status');
		$data['entry_void_status'] = $this->language->get('entry_void_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_merchant_identifier'] = $this->language->get('entry_merchant_identifier');
		$data['entry_merchant_certificate'] = $this->language->get('entry_merchant_certificate');
		$data['entry_merchant_key'] = $this->language->get('entry_merchant_key');
		$data['entry_domain'] = $this->language->get('entry_domain');
		$data['entry_domain_name'] = $this->language->get('entry_domain_name');

		$data['help_store'] = $this->language->get('help_store');
		$data['help_authkey'] = $this->language->get('help_authkey');
		$data['help_callback'] = $this->language->get('help_callback');
		$data['help_purdesc'] = $this->language->get('help_purdesc');
		$data['help_total'] = $this->language->get('help_total');		
		$data['help_merchant_identifier'] = $this->language->get('help_merchant_identifier');
		$data['help_merchant_certificate'] = $this->language->get('help_merchant_certificate');
		$data['help_merchant_key'] = $this->language->get('help_merchant_key');
		$data['help_domain'] = $this->language->get('help_domain');
		$data['help_domain_name'] = $this->language->get('help_domain_name');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['store'])) {
			$data['error_store'] = $this->error['store'];
		} else {
			$data['error_store'] = '';
		}

		if (isset($this->error['authkey'])) {
			$data['error_authkey'] = $this->error['authkey'];
		} else {
			$data['error_authkey'] = '';
		}
		
		if (isset($this->error['purdesc'])) {
			$data['error_purdesc'] = $this->error['purdesc'];
		} else {
			$data['error_purdesc'] = '';
		}
		
		if (isset($this->error['merchant_identifier'])) {
			$data['error_merchant_identifier'] = $this->error['merchant_identifier'];
		} else {
			$data['error_merchant_identifier'] = '';
		}
		
		if (isset($this->error['certificate'])) {
			$data['error_certificate'] = $this->error['certificate'];
		} else {
			$data['error_certificate'] = '';
		}
		
		if (isset($this->error['certificate_key'])) {
			$data['error_certificate_key'] = $this->error['certificate_key'];
		} else {
			$data['error_certificate_key'] = '';
		}
		
		if (isset($this->error['domain'])) {
			$data['error_domain'] = $this->error['domain'];
		} else {
			$data['error_domain'] = '';
		}
		
		if (isset($this->error['domain_name'])) {
			$data['error_domain_name'] = $this->error['domain_name'];
		} else {
			$data['error_domain_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('text_home'),
			'href'	=> $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('text_payment'),
			'href'	=> $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('heading_title'),
			'href'	=> $this->url->link('extension/telr_applepay_oc/payment/telr_applepay', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
		);

		$data['action'] = $this->url->link('extension/telr_applepay_oc/payment/telr_applepay', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->request->post['payment_telr_applepay_store'])) {
			$data['payment_telr_applepay_store'] = $this->request->post['payment_telr_applepay_store'];
		} else {
			$data['payment_telr_applepay_store'] = $this->config->get('payment_telr_applepay_store');
		}

		if (isset($this->request->post['payment_telr_applepay_authkey'])) {
			$data['payment_telr_applepay_authkey'] = $this->request->post['payment_telr_applepay_authkey'];
		} else {
			$data['payment_telr_applepay_authkey'] = $this->config->get('payment_telr_applepay_authkey');
		}
		
		if (isset($this->request->post['payment_telr_applepay_purdesc'])) {
			$data['payment_telr_applepay_purdesc'] = $this->request->post['payment_telr_applepay_purdesc'];
		} else {
			$data['payment_telr_applepay_purdesc'] = $this->config->get('payment_telr_applepay_purdesc');
		}
		
		if (isset($this->request->post['payment_telr_applepay_merchant_identifier'])) {
			$data['payment_telr_applepay_merchant_identifier'] = $this->request->post['payment_telr_applepay_merchant_identifier'];
		} else {
			$data['payment_telr_applepay_merchant_identifier'] = $this->config->get('payment_telr_applepay_merchant_identifier');
		}
				
		if (isset($this->request->post['payment_telr_applepay_merchant_certificate'])) {
			$data['payment_telr_applepay_merchant_certificate'] = $this->request->post['payment_telr_applepay_merchant_certificate'];
		} else {
			$data['payment_telr_applepay_merchant_certificate'] = $this->config->get('payment_telr_applepay_merchant_certificate');
		}
				
		if (isset($this->request->post['payment_telr_applepay_merchant_key'])) {
			$data['payment_telr_applepay_merchant_key'] = $this->request->post['payment_telr_applepay_merchant_key'];
		} else {
			$data['payment_telr_applepay_merchant_key'] = $this->config->get('payment_telr_applepay_merchant_key');
		}
				
		if (isset($this->request->post['payment_telr_applepay_domain'])) {
			$data['payment_telr_applepay_domain'] = $this->request->post['payment_telr_applepay_domain'];
		} else {
			$data['payment_telr_applepay_domain'] = $this->config->get('payment_telr_applepay_domain');
		}
		
		if (isset($this->request->post['payment_telr_applepay_domain_name'])) {
			$data['payment_telr_applepay_domain_name'] = $this->request->post['payment_telr_applepay_domain_name'];
		} else {
			$data['payment_telr_applepay_domain_name'] = $this->config->get('payment_telr_applepay_domain_name');
		}

		$data['payment_telr_applepay_title']= $this->config->get('payment_telr_applepay_title');
		$data['applepay_merchant_key_name']= $this->config->get('payment_telr_applepay_merchant_key_name');
		$data['applepay_merchant_certificate_name']= $this->config->get('payment_telr_applepay_merchant_certificate_name');

		if (isset($this->request->post['payment_telr_applepay_total'])) {
			$data['payment_telr_applepay_total'] = $this->request->post['payment_telr_applepay_total'];
		} else {
			$data['payment_telr_applepay_total'] = $this->config->get('payment_telr_applepay_total');
		}

		if (isset($this->request->post['payment_telr_applepay_pend_status_id'])) {
			$data['payment_telr_applepay_pend_status_id'] = $this->request->post['payment_telr_applepay_pend_status_id'];
		} else {
			$data['payment_telr_applepay_pend_status_id'] = $this->config->get('payment_telr_applepay_pend_status_id');
			if (empty($data['payment_telr_applepay_pend_status_id'])) {
				$data['payment_telr_applepay_pend_status_id']='1';
			}
		}
		if (isset($this->request->post['payment_telr_applepay_comp_status_id'])) {
			$data['payment_telr_applepay_comp_status_id'] = $this->request->post['payment_telr_applepay_comp_status_id'];
		} else {
			$data['payment_telr_applepay_comp_status_id'] = $this->config->get('payment_telr_applepay_comp_status_id');
			if (empty($data['payment_telr_applepay_comp_status_id'])) {
				$data['payment_telr_applepay_comp_status_id']='2';
			}
		}
		if (isset($this->request->post['payment_telr_applepay_void_status_id'])) {
			$data['payment_telr_applepay_void_status_id'] = $this->request->post['payment_telr_applepay_void_status_id'];
		} else {
			$data['payment_telr_applepay_void_status_id'] = $this->config->get('payment_telr_applepay_void_status_id');
			if (empty($data['payment_telr_applepay_void_status_id'])) {
				$data['payment_telr_applepay_void_status_id']='7';
			}
		}

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_telr_applepay_geo_zone_id'])) {
			$data['payment_telr_applepay_geo_zone_id'] = $this->request->post['payment_telr_applepay_geo_zone_id'];
		} else {
			$data['payment_telr_applepay_geo_zone_id'] = $this->config->get('payment_telr_applepay_geo_zone_id');
		}
		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_telr_applepay_status'])) {
			$data['payment_telr_applepay_status'] = $this->request->post['payment_telr_applepay_status'];
		} else {
			$data['payment_telr_applepay_status'] = $this->config->get('payment_telr_applepay_status');
		}

		if (isset($this->request->post['payment_telr_sort_order'])) {
			$data['payment_telr_sort_order'] = $this->request->post['payment_telr_sort_order'];
		} else {
			$data['payment_telr_sort_order'] = $this->config->get('payment_telr_sort_order');
		}
		$defaults=$this->config->get('payment_telr_defaults');
		if (empty($defaults)) {
			$data['payment_telr_applepay_title'] = 'ApplePay';	// Module Title
			$data['payment_telr_applepay_pend_status_id'] = '1';			// Pending
			$data['payment_telr_applepay_comp_status_id'] = '2';			// Processing
			$data['payment_telr_applepay_void_status_id'] = '7';			// Cancelled
			$data['payment_telr_applepay_status'] = '1';				// Enabled
		}
	
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/telr_applepay_oc/payment/telr_applepay', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/telr_applepay_oc/payment/telr_applepay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$certificate_file = $this->request->files['payment_telr_applepay_merchant_certificate'];
		$key_file = $this->request->files['payment_telr_applepay_merchant_key'];
		
		$store = $this->request->post['payment_telr_applepay_store'];
		$store = intval(preg_replace('/[^0-9]+/', '', $store), 10);
		$this->request->post['payment_telr_applepay_store'] = (string)$store;

		if ($store<=0) {
			$this->error['store'] = $this->language->get('error_store');
		}

		if (!$this->request->post['payment_telr_applepay_authkey']) {
			$this->error['authkey'] = $this->language->get('error_authkey');
		}
		
		if (!$this->request->post['payment_telr_applepay_purdesc'] || strlen($this->request->post['payment_telr_applepay_purdesc']) > 63) {
			$this->error['purdesc'] = $this->language->get('error_purdesc');
		}
		
		if (!$this->request->post['payment_telr_applepay_merchant_identifier']) {
			$this->error['merchant_identifier'] = $this->language->get('error_merchant_identifier');
		}
		
		if (!$this->request->post['payment_telr_applepay_domain']) {
			$this->error['domain'] = $this->language->get('error_domain');
		}
		
		if (!$this->request->post['payment_telr_applepay_domain_name']) {
			$this->error['domain_name'] = $this->language->get('error_domain_name');
		}
		
		if(!$this->validate_uploade_file($certificate_file) || (empty($this->config->get('payment_telr_applepay_merchant_certificate')) && empty($certificate_file['name']))) {
			$this->error['certificate'] = $this->language->get('error_certificate');
		}else if(!empty($certificate_file['name'])){
			$this->request->post['payment_telr_applepay_merchant_certificate'] = $this->path;
			$this->request->post['payment_telr_applepay_merchant_certificate_name'] = $certificate_file['name'];
		}else {
			$this->request->post['payment_telr_applepay_merchant_certificate'] = $this->config->get('payment_telr_applepay_merchant_certificate');
			$this->request->post['payment_telr_applepay_merchant_certificate_name'] = $this->config->get('payment_telr_applepay_merchant_certificate_name');
		}
		
		if(!$this->validate_uploade_file($key_file) || (empty($this->config->get('payment_telr_applepay_merchant_key')) && empty($key_file['name']))) {
			$this->error['certificate_key'] = $this->language->get('error_certificate_key');
		}else if(!empty($key_file['name'])){
			$this->request->post['payment_telr_applepay_merchant_key'] = $this->path;
			$this->request->post['payment_telr_applepay_merchant_key_name'] = $key_file['name'];
		}else {
			$this->request->post['payment_telr_applepay_merchant_key'] = $this->config->get('payment_telr_applepay_merchant_key');
			$this->request->post['payment_telr_applepay_merchant_key_name'] = $this->config->get('payment_telr_applepay_merchant_key_name');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	private function version_ok() {
		if (version_compare(VERSION, '3.0.0.0', '<')) {
			return false;
		}
		return true;
	}
	
	private function validate_uploade_file($file){
		$this->path = '';
		$allowed_types = ['application/octet-stream'];
		if (!empty($file['name']) && !in_array($file['type'], $allowed_types)) {
			return false;
		}else if(!empty($file['name'])){
			$filename = basename($file['name']);
            $destination = DIR_UPLOAD . $filename;
			if(move_uploaded_file($file['tmp_name'], $destination)) {
				$this->path = $destination;
            }else {
				return false;
			}
		}
		return true;
	}
}
?>
