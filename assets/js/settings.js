jQuery(document).on("ready", function() {
    if (jQuery("#woocommerce_wc_hubtelpayment_notify").is(":checked")) {
        jQuery("#woocommerce_mpowerpayment_sms_email_addresses").prop("visibility", "visible");
    } else {
        jQuery("#woocommerce_mpowerpayment_sms_email_addresses").prop("visibility", "hidden");
    }
    jQuery("#woocommerce_wc_hubtelpayment_notify").on("click", function() {
        if (jQuery(this).is(":checked")) {
            jQuery("#woocommerce_mpowerpayment_sms_email_addresses").prop("visibility", "visible");
        } else {
            jQuery("#woocommerce_mpowerpayment_sms_email_addresses").prop("visibility", "hidden");
        }
    });
});