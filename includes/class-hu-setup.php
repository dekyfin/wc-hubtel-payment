<?php 

class WC_HubtelSetup {
	
	var $config = null;

	function __construct(){
		
	}


	function read_config(){
		$title = wp_filter_nohtml_kses(get_option("hubtel_title", ""));
		$description = wp_filter_nohtml_kses(get_option("hubtel_description", ""));
		$clientid = get_option("hubtel_clientid", "");
		$merchantid = get_option("hubtel_merchantid", "");
		$secret = get_option("hubtel_secret", "");
		$enabled = get_option("hubtel_enabled", "0");
		$cconverter = get_option("hubtel_cconverter", "0");
		$emails = get_option("hubtel_emails", "");
		$icon = get_option("hubtel_icon", "");

		$this->config = include plugin_dir_path(__FILE__) . "settings.php";

		if(trim($title) <> "")
			$this->config["title"] = $title;
		if(trim($description) <> "")
			$this->config["description"] = $description;
		if(trim($clientid) <> "")
			$this->config["clientid"] = $clientid;
		if(trim($merchantid) <> "")
			$this->config["merchantid"] = $merchantid;
		if(trim($secret) <> "")
			$this->config["secret"] = $secret;
		if(trim($emails) <> "")
			$this->config["emails"] = $emails;

		if(trim($icon) <> "")
			$this->config["icon"] = $icon;

		$this->config["enabled"] = $enabled;
		$this->config["cconverter"] = $cconverter;

		return $this->config;
	} 

	function write_config($data){
		foreach ($data as $key => $value){
			$key = "hubtel_" . $key;
			update_option($key, $value, '', 'no');
		}
	} 

	function init_form_fields($obj) {
			$obj->form_fields = array(
				'enabled' => array(
					'title' => __('Enable/Disable', $this->config["id"]),
					'type' => 'checkbox',
					'label' => __('Enable Hubtel Payment Module.', $this->config["id"]),
					'default' => 'no'),
				'title' => array(
					'title' => __('Title', $this->config["id"]),
					'type' => 'text',
					'description' => __('This controls the title which the user sees during checkout.', $this->config["id"]),
					'default' => __($this->config["title"], $this->config["id"])),
				'description' => array(
					'title' => __('Description', $this->config["id"]),
					'type' => 'textarea',
					'description' => __('This controls the description which the user sees during checkout.', $this->config["id"]),
					'default' => __($this->config["description"], $this->config["id"])),
				'icon' => array(
					'title' => __('Icon URL', $this->config["id"]),
					'type' => 'text',
					'description' => __('The logo which the user sees during checkout.', $this->config["id"]),
					'default' => __($this->config["icon"], $this->config["id"])),
				'merchantid' => array(
					'title' => __('Merchant ID', $this->config["id"]),
					'type' => 'text',
					'description' => __("Your Hubtel account's ID. It will be in the format HMXXXXXXXX", $this->config["id"]),
					'default' => __($this->config["merchantid"], $this->config["id"])),
				'clientid' => array(
					'title' => __('Client Id', $this->config["id"]),
					'type' => 'text',
					'description' => __('', $this->config["id"]),
					'default' => __($this->config["clientid"], $this->config["id"])),
				'secret' => array(
					'title' => __('Secret', $this->config["id"]),
					'type' => 'text',
					'description' => __('', $this->config["id"]),
					'default' => __($this->config["secret"], $this->config["id"])),
				'cconverter' => array(
					'title' => __('Currency Conversion<br><small style="font-weight: 400;">Enable this option if your store/site uses a different currency apart from GHS</small>', $config["id"]),
					'type' => 'checkbox',
					'label' => __('Automatically convert to GHS before sending to hubtel', $config["id"]),
					'default' => "no"),
			);
	}

	function __initialize($plugin){
		add_filter('woocommerce_currencies', array(&$this, 'add_ghs_currency'));
		add_filter('woocommerce_currency_symbol', array(&$this, 'add_ghs_currency_symbol'), 10, 2);
		add_filter("plugin_action_links_$plugin", array(&$this, 'add_hubtel_settings_link'));
		add_filter('woocommerce_payment_gateways', array(&$this, 'add_woocommerce_hubtel_gateway'));
	}

	function add_ghs_currency_symbol($currency_symbol, $currency) {
		switch ($currency) {
			case 'GHS': 
				$currency_symbol = 'GHS ';
				break;
		}
		return $currency_symbol;
	}

	function add_woocommerce_hubtel_gateway($methods) {
		$methods[] = 'WC_HubtelPayment';
		return $methods;
	}

	function add_hubtel_settings_link($links) {
	 	$settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=wc_hubtelpayment">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	 }

	function add_ghs_currency($currencies) {
		$currencies['GHS'] = __('Ghana Cedi', 'woocommerce');
		return $currencies;
	}
}