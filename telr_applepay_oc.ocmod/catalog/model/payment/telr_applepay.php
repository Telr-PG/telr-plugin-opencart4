<?php 

namespace Opencart\Catalog\Model\Extension\TelrApplepayOc\Payment;

class TelrApplepay extends \Opencart\System\Engine\Model {
	
	// For OC version 4.0.2.0, 4.0.2.1
	public function getMethods(array $address = []): array {

		$total = $this->cart->getTotal(); 
		
		$this->load->language('extension/telr_applepay_oc/payment/telr_applepay');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_telr_applepay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if ($this->config->get('payment_telr_applepay_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_telr_applepay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if (!$this->is_safari()) {
			$status = false;
		}	
		
		$method_data = array();
		$title=trim($this->config->get('payment_telr_applepay_title'));
		if (empty($title)) {
			$title=trim($this->language->get('text_title'));
			if (empty($title)) {
				$title="Credit Card / Debit Card (Telr)";
			}
		}
	
		if ($status) {
			$option_data['telr_applepay'] = [
				'code' => 'telr_applepay.telr_applepay',
				'name' => $title
			];
			$method_data = array( 
				'code'		=> 'telr_applepay',
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
		
		$this->load->language('extension/telr_applepay_oc/payment/telr_applepay');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_telr_applepay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if ($this->config->get('payment_telr_applepay_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_telr_applepay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	

		if (!is_safari()) {
                     $status = false;
                }

		$method_data = array();
		$title=trim($this->config->get('payment_telr_applepay_title'));
		if (empty($title)) {
			$title=trim($this->language->get('text_title'));
			if (empty($title)) {
				$title="Credit Card / Debit Card (Telr)";
			}
		}
	
		if ($status) {
			$option_data['telr_applepay'] = [
				'code' => 'telr_applepay.telr_applepay',
				'title' => $title
			];
			$method_data = array( 
				'code'		=> 'telr_applepay',
				'title'		=> $title,
				'option'     => $option_data,
				'terms' => '',
				'sort_order'	=> $this->config->get('telr_sort_order')
			);
		}
		
		
		return $method_data;
	}

	private function is_safari(){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		$is_safari = (
 		   strpos($user_agent, 'Safari') !== false &&
 		   strpos($user_agent, 'Version/') !== false &&
 		   strpos($user_agent, 'Chrome') === false &&
		   strpos($user_agent, 'Chromium') === false &&
  		   strpos($user_agent, 'Edg') === false &&
    		   strpos($user_agent, 'OPR') === false &&
    		   strpos($user_agent, 'Firefox') === false
		);

		return $is_safari;
	}

}
?>
