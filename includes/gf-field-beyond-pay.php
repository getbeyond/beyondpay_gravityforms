<?php
class GF_Field_Beyond_Pay extends GF_Field {
    public $type = 'beyond_pay';
    public $inputs = ['beyond_pay_token'];
    
    public function get_form_editor_field_icon() {
	return 'gform-icon--credit-card';
    }
    
    public function get_form_editor_field_title() {
	return esc_attr__( 'Credit Card', 'gravityforms' );
    }
    
    public function get_form_editor_button() {
	return array(
	    'group' => 'pricing_fields',
	    'text'  => $this->get_form_editor_field_title()
	);
    }
    public function get_form_editor_field_settings() {
	return [
	    'error_message_setting',
	    'force_ssl_field_setting',
	    'label_setting',
	    'admin_label_setting',
	    'description_setting'
	];
    }
    public function get_field_input($form, $value = '', $entry = NULL) {
	$bp = GFBeyondPay::get_instance();
	$is_custom_css = $bp->get_plugin_setting('use-custom-styling');
	$css = $is_custom_css ? 
		$bp->get_plugin_setting('styling') : 
		file_get_contents(dirname(__DIR__).'/assets/css/payment-styling.css');
	
	$out = 
	    (empty($css) ? '' : '<div style="display: none" id="beyondPayStyles">'.$css.'</div>')
	    . '<div class="ginput_container" id="gf_coupons_container_'.$form['id'].'">'
	    . '<div id="card"></div>'
	    . '<div id="errorMessage"></div>'
	    . '<input type="hidden" value="" id="beyond_pay_token" name="beyond_pay_token" />'
	    . '<script type="text/javascript">attachBeyondPay();</script>'
	    . '</div>';
	return $out;
    }
    
    public function get_field_label_class(){
	return 'gfield_label gfield_label_before_complex';
    }
    
    public function validate( $value, $form ) {
	if (empty(rgpost('beyond_pay_token'))) {
	    $this->failed_validation  = true;
	    $this->validation_message = 'Error submitting payment form. Please try again.';
	}
    }
}
