<?php
/**
 * Plugin Name: Beyond Pay for Gravity Forms
 * Plugin URI: https://developer.getbeyond.com
 * Description: Beyond Pay credit card payments with Gravity Forms
 * Author: Beyond
 * Author URI: https://getbeyond.com
 * Version: 1.1.3
 * Text Domain: beyond-pay-for-gravityforms
 * Tested up to: 6.0
 * 
 * Copyright (c) 2020 Above and Beyond Business Tools and Services for Entrepreneurs, Inc.
 *
 * Review the LICENSE file for licensing information.
 */

GFForms::include_payment_addon_framework();
 
add_action( 'gform_loaded', 'load_beyond_pay_gf', 5 );
 
function load_beyond_pay_gf() {
 
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }
	
	/** Check if the class wasn't loaded by a different plugin */
	if(!class_exists('BeyondPay\\BeyondPayRequest')) {
	    require( dirname(__FILE__) . '/includes/beyond-pay.php' );
	}
        require_once( dirname(__FILE__) . '/includes/gf-beyond-pay.php');
        require_once( dirname(__FILE__) . '/includes/gf-field-beyond-pay.php');
 
        GFBeyondPay::register( 'GFBeyondPay' );
	GF_Fields::register(new GF_Field_Beyond_Pay());

	add_action( 'gform_payment_details', function($form_id, $entry) {
	    $requires_capture = gform_get_meta($entry['id'], 'requires_capture');
	    $order_id = gform_get_meta($entry['id'], 'beyond_pay_order_id');
	    if(!empty($order_id)){ ?>
		<div id="gf_beyond_pay_invnum" class="gf_payment_detail">
			Beyond Pay Invoice Num:
			<span id='gform_beyond_pay_invnum'><?php echo esc_html($order_id); ?></span>
		</div><?php 
	    }
	    if($requires_capture && $entry['payment_status'] === 'Authorized'){
		echo "<input class=\"button\" type=\"button\" value=\"Capture Payment\" onclick=\"beyondPayCapturePayment(".$form_id.", ".$entry['id'].");\"/>";
	    }
	}, 10, 2 );
	add_action('wp_ajax_beyond_pay_capture', [gf_beyond_pay_addon(), 'capture_authorised_payment']);
}

function gf_beyond_pay_addon() {
    return GFBeyondPay::get_instance();
}