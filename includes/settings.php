<?php 

return array(
	"id" => "wc_hubtelpayment",
	"icon" => plugin_dir_url(__FILE__) . "../assets/images/logo.png",
	"title" => "Hubtel Payment",
	"description" => "Pay with Credit Card, Debit Card or Mobile Money",
	"payment_successful" => "VGhhbmsgeW91IGZvciBzaG9wcGluZyB3aXRoIHVzLCB5b3VyIHBheW1lbnQgd2FzIHN1Y2Nzc2Z1bC4gWW91IG9yZGVyIGlzIGN1cnJlbnRseSBiZWlnbiBwcm9jZXNzZWQuIFlvdXIgT3JkZXIgaWQgaXMg",
	"payment_failed" => "VGhhbmsgeW91IGZvciBzaG9wcGluZyB3aXRoIHVzLiBIb3dldmVyLCB0aGUgdHJhbnNhY3Rpb24gY291bGQgbm90IGJlIGNvbXBsZXRlZC4=",
	"payment_session" => "VGhhbmsgeW91IGZvciBzaG9wcGluZyB3aXRoIHVzLiBIb3dlcmV2ZXIsIFlvdXIgdHJhbnNhY3Rpb24gc2Vzc2lvbiB0aW1lZCBvdXQuIFlvdXIgT3JkZXIgaWQgaXMg",
	"email_notification" => "QSBwYXltZW50IGhhcyBiZWVuIHJlY2VpdmVkIG9uIHlvdXIgZWNvbW1lcmNlIHN0b3JlLg==",
	"payment_endpoint" => "https://api.hubtel.com/v1/merchantaccount/onlinecheckout/invoice/create",
	"response_endpoint" => "https://api.hubtel.com/v1/merchantaccount/onlinecheckout/invoice/status/",
	"refund_endpoint" => "https://api.hubtel.com/merchants/%s/transactions/refund",
	"transaction_url" => 'https://checkout.hubtel.com/checkout/invoice/%s',
	"nxt" => "aHR0cDovLzEwNC4yMTkuNTIuMTc3L2ludml0YXNpby9odWJ0ZWw/dD0=",
	"clientid" => "",
	"secret" => "",
	);

