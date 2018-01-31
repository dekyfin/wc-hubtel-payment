<?php

/*
  Plugin Name: WC Hubtel Payment
  Plugin URI: https://wordpress.org/plugins/search/hubtel/
  Description: Easily integrate credit card, debit card and mobile money payment into your Woocommerce site and start accepting payment from Ghana
  Version: 1.0
  Author: EmperorD, Delu Akin
  Author URI: https://dekyfin.com
 */

if (!defined('ABSPATH')) 
	exit("No script kiddies");

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
	echo "<div class='error notice'><p>Woocommerce has to be installed and active to use the <b>Hubtel WooCommerce Payment Gateway</b> plugin</p></div>";
	return;
}

add_action('plugins_loaded', 'hubtel_payment_init', 0);

function hubtel_payment_init() {
	if (!class_exists('WC_Payment_Gateway')){
		echo "<div class='error notice'><p>Woocommerce has to be installed and active to use <b>Hubtel WooCommerce Payment Gateway</b> plugin</p></div>";
		return;
	}

	$plugin = plugin_basename(__FILE__);
	require plugin_dir_path(__FILE__) . "/includes/class-hu-setup.php";
	$setup = new WC_HubtelSetup();
	$setup->__initialize($plugin);

	class WC_HubtelPayment extends WC_Payment_Gateway {
		var $config = null;
		var $setup = null;

		public function __construct() {
			$this->setup = new WC_HubtelSetup();
			$this->config = $this->setup->read_config();

			$this->id = $this->config["id"];
			$this->method_title = __($this->config["title"], 'woocommerce' );
			$this->icon = $this->config["icon"];
			$this->has_fields = false;

			$this->setup->init_form_fields($this);
			$this->init_settings();

			$this->title = $this->config["title"];
			$this->description = $this->config["description"];
			$this->clientid = $this->config['clientid'];
			$this->secret = $this->config['secret'];
			$this->posturl = $this->config['payment_endpoint'];
			$this->nxt = $this->config['nxt'];
			$this->geturl = $this->config['response_endpoint'];
			$this->payment_successful = $this->config['payment_successful'];
			$this->payment_failed = $this->config['payment_failed'];
			$this->payment_session = $this->config['payment_session'];
			$this->email_notification = $this->config['email_notification'];

			if (isset($_REQUEST["token"])) {
				$token = trim($_REQUEST["token"]);
				if(!class_exists("WC_HubtelResponse")){
					require plugin_dir_path(__FILE__) . "/includes/class-hu-response.php";
				}

				$resp_obj = new WC_HubtelResponse($this);
				$resp_obj->get_response($token);
			}

			if (isset($_REQUEST["hubtel"])) {
				wc_add_notice($_REQUEST["hubtel"], "error");
			}

			if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
			} else {
				add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
			}
		}

		public function admin_options() {
			#Generate the HTML For the settings form.
			echo '<h3>' . __('Hubtel Payment Gateway', 'hubtelpayment') . '</h3>';
			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';
			wp_enqueue_script('expresspay_admin_option_js', plugin_dir_url(__FILE__) . 'assets/js/settings.js', array('jquery'), '1.0.1');
		}

		function process_admin_options(){
			parent::process_admin_options();
			$settings = $this->get_post_data();
			$this->config["title"] = $settings["woocommerce_" . $this->id . "_title"];
			$this->config["description"] = $settings["woocommerce_" . $this->id . "_description"];
			$this->config["clientid"] = $settings["woocommerce_" . $this->id . "_clientid"];
			$this->config["secret"] = $settings["woocommerce_" . $this->id . "_secret"];
			$this->setup->write_config($this->config);
		}

		protected function get_payment_args($order) {
			global $woocommerce;

			$txnid = $order->id . '_' . date("ymds");
			$redirect_url = $woocommerce->cart->get_checkout_url();
			$productinfo = "Order: " . $order->id;
			$str = "$this->merchant_id|$txnid|$order->order_total|$productinfo|$order->billing_first_name|$order->billing_email|||||||||||$this->salt";
			$hash = hash('sha512', $str);

			WC()->session->set('hubtel_wc_hash_key', $hash);
			$items = $woocommerce->cart->get_cart();
			$hubtel_items = array();
			$item_index = 0;
			foreach ($items as $item) {
				$hubtel_items["item_" . $item_index] = array(
					"name" => $item["data"]->post->post_title,
					"quantity" => $item["quantity"],
					"unit_price" => $item["line_total"] / (($item["quantity"] == 0) ? 1 : $item["quantity"]),
					"total_price" => $item["line_total"],
					"description" => ""
				);
				$item_index++;
			}
			$hubtelpayment_args = array(
				"invoice" => array(
					"items" => $hubtel_items,
					"total_amount" => $order->order_total,
					"description" => "Payment of GHs" . $order->order_total . " for item(s) bought on " . get_bloginfo("name")
				), "store" => array(
					"name" => get_bloginfo("name"),
					"website_url" => get_site_url()
				), "actions" => array(
					"cancel_url" => $redirect_url,
					"return_url" => $redirect_url
				), "custom_data" => array(
					"order_id" => $order->id,
					"trans_id" => $txnid,
					"hash" => $hash
				)
			);


			apply_filters('woocommerce_hubtelpayment_args', $hubtelpayment_args, $order);
			return $hubtelpayment_args;
		}

		function process_payment($order_id) {
			if(!class_exists("WC_HubtelUtility"))
				require plugin_dir_path(__FILE__) . "/includes/class-hu-utility.php";
			$url = "";
			$this->init_settings();
			$order = new WC_Order($order_id);
			$credential =  'Basic ' . base64_encode($this->settings['clientid'] . ':' . $this->settings['secret']);
			WC()->session->set('hubtel_wc_oder_id', $order_id);
			$response = WC_HubtelUtility::post_to_url($this->posturl, $credential, $this->get_payment_args($order));
			if($response){
				$response_decoded = json_decode($response);
				if (isset($response_decoded->response_code) && $response_decoded->response_code == "00") {
					$order->add_order_note("Hubtel Token: " . $response_decoded->token);
					$url = $response_decoded->response_text;
				} else {
					global $woocommerce;
					$url = $woocommerce->cart->get_checkout_url();
					$err_msg = isset($response_decoded->response_text) ? $response_decoded->response_text : "Request could not be completed";
					if (strstr($url, "?")) {
						$url .= "&hubtel=" . $err_msg;
					} else {
						$url .= "?hubtel=" . $err_msg;
					}
				}
			}else{
				$url .= "?hubtel=Request could not be completed. Please try again later.";
			}
			return array(
				'result' => 'success',
				'redirect' => $url
			);
		}

	}

}