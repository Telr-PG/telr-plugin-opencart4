<?php 

namespace Opencart\Catalog\Model\Extension\TelrOc\Payment;

class Telr extends \Opencart\System\Engine\Model {
	
	// For OC version 4.0.2.0, 4.0.2.1
	public function getMethods(array $address = []): array {
		
		$total = $this->cart->getTotal(); 
		
		$this->load->language('extension/telr_oc/payment/telr');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_telr_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if ($this->config->get('payment_telr_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_telr_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	
		
		$method_data = array();
		$title=trim($this->config->get('payment_telr_title'));
		if (empty($title)) {
			$title=trim($this->language->get('text_title'));
			if (empty($title)) {
				$title="Credit Card / Debit Card (Telr)";
			}
		}
	
		if ($status) {
			$option_data['telr'] = [
				'code' => 'telr.telr',
				'name' => $title
			];
			$method_data = array( 
				'code'		=> 'telr',
				'name'		=> $title,
				'option'     => $option_data,
				'terms' => '',
				'sort_order'	=> $this->config->get('telr_sort_order')
			);
		}
		
		
		return $method_data;
	}
	
	// For OC version 4.0.0.0, 4.0.1.0, 4.0.1.1
	public function getMethod(array $address = []): array {
		
		$total = $this->cart->getTotal(); 
		
		$this->load->language('extension/telr_oc/payment/telr');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_telr_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if ($this->config->get('telr_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('telr_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	
		
		$method_data = array();
		$title=trim($this->config->get('telr_title'));
		if (empty($title)) {
			$title=trim($this->language->get('text_title'));
			if (empty($title)) {
				$title="Credit Card / Debit Card (Telr)";
			}
		}
	
		if ($status) {
			$option_data['telr'] = [
				'code' => 'telr.telr',
				'title' => $title
			];
			$method_data = array( 
				'code'		=> 'telr',
				'title'		=> $title,
				'option'     => $option_data,
				'terms' => '',
				'sort_order'	=> $this->config->get('telr_sort_order')
			);
		}
		
		
		return $method_data;
	}

}
?>