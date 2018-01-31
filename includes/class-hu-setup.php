<?php 

class WC_HubtelSetup {
	
	var $config = null;

	function __construct(){
		
	}

	function read_config(){
		$this->config = include "settings.php";
		return $this->config;
	} 

	function write_config($data){
		$config = var_export($config, true);
		
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
                'logo' => array(
                    'title' => __('Logo', $this->config["id"]),
                    'type' => 'text',
                    'description' => __('The logo which the user sees during checkout.', $this->config["id"]),
                    'default' => __($this->config["logo"], $this->config["id"])),
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