<?php

class WC_HubtelUtility {

	static function post_to_url($url, $credential, $data = false) {
		if($data)
			$json = json_encode($data);

		$response = wp_remote_post($url, array(
			'method' => 'POST',
			'headers' => array(
				"Authorization" => $credential,
				"Cache-Control" => "no-cache",
				"Content-Type" => "application/json"
			),
			'body' => $json
			)
		);

		if (!is_wp_error($response)) {
			$r = wp_remote_retrieve_body($response);
			return $r;
		}
		return false;
	}
	static function get_url($url, $credential, $data = false) {
		if($data)
			$json = json_encode($data);

		$response = wp_remote_get($url, array(
			'method' => 'GET',
			'headers' => array(
				"Authorization" => $credential,
				"Cache-Control" => "no-cache",
				"Content-Type" => "application/json"
			),
			'body' => $data
			)
		);

		if (!is_wp_error($response)) {
			$r = wp_remote_retrieve_body($response);
			return $r;
		}
		return false;
	}

	static function send_sms(){
		//get premium version
	}
}

?>