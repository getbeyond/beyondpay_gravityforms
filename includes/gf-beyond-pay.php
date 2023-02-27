<?php
class GFBeyondPay extends GFPaymentAddOn {
    protected $_version = "1.1.1";
    protected $_min_gravityforms_version = "2.4.19";
    protected $_slug = 'beyond-pay-gravity-forms';
    protected $_path = 'beyond-pay-gravity-forms/beyond-pay-gravity-forms.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Beyond Pay Gravity Forms Add-On';
    protected $_short_title = 'Beyond Pay';
    protected $_supports_callbacks = true;
    protected $_requires_credit_card = true;
    /**
     * @var object|null $_instance If available, contains an instance of this class.
     */
    private static $_instance = null;
    /**
     * Transaction response error
     */
    private $response_error = "";

    /**
     * Returns an instance of this class, and stores it in the $_instance property.
     *
     * @return object $_instance An instance of this class.
     */
    public static function get_instance() {
	if (self::$_instance == null) {
	    self::$_instance = new self();
	}

	return self::$_instance;
    }

    public function init() {
	parent::init();
	add_action('gform_post_payment_action', array($this, 'update_entry_payment_info'), 10, 2);
	add_filter('gform_validation_message', array($this, 'add_payment_error_description'), 10, 1 );
    }

    public function init_admin() {
	parent::init_admin();
	add_filter('gform_enable_credit_card_field', '__return_false');
    }

    public function feed_settings_fields(){
	$out = parent::feed_settings_fields();
	foreach($out as &$section){
	    if(!empty($section['fields'])){
		foreach($section['fields'] as &$field){
		    if($field['name'] === 'transactionType'){
			$field['choices'] = array_filter(
			    $field['choices'],
			    function ($v) {return $v['value']!=='subscription';}
			);
		    }
		}
	    }
	}
	return $out;
    }

    public function get_menu_icon() {
	return 'gform-icon--credit-card';
    }

    public function plugin_settings_fields() {
        return array(
            array(
                'label'  => esc_html__( 'Beyond Pay Settings', 'beyond_pay' ),
                'fields' => array(
		    array(
			'class' => 'enable-test-mode',
			'name' => 'testmode',
			'label' => 'Enable Test Mode',
			'type' => 'checkbox',
			'onchange' => 'onBeyondPayTestModeChanged(this)',
			'tooltip' => 'Place the payment gateway in test mode using test API keys.',
			'choices' => [['label'=>'Enable', 'name'=>'enable-test-mode', 'value' => 'yes']],
		    ),
		    array(
			'name'  => 'test_public_key',
			'label' => 'Test Public Key',
			'type' => 'text',
			'class' => 'beyond_pay_setting_test_only',
            'size' => '50',
		    ),
		    array(
			'name' => 'test_private_key',
			'label' => 'Test Private Key',
			'type' => 'text',
            'input_type' => 'password',
			'class' => 'beyond_pay_setting_test_only',
            'size' => '50',
		    ),
		    array(
			'name' => 'test_login',
			'label' => 'Test Username',
			'type' => 'text',
			'class' => 'beyond_pay_setting_test_only'
		    ),
		    array(
			'name'  => 'test_password',
			'label' => 'Test Password',
			'type' => 'text',
            'input_type' => 'password',
			'class' => 'beyond_pay_setting_test_only'
		    ),
		    array(
			'name' => 'public_key',
			'label' => 'Live Public Key',
			'type' => 'text',
			'class' => 'beyond_pay_setting_live_only',
            'size' => '50',
		    ),
		    array(
			'name'  => 'private_key',
			'label' => 'Live Private Key',
            'type' => 'text',
            'input_type' => 'password',
			'class' => 'beyond_pay_setting_live_only',
            'size' => '50',
		    ),
		    array(
			'name'  => 'login',
			'label' => 'Live Username',
			'type' => 'text',
			'class' => 'beyond_pay_setting_live_only'
		    ),
		    array(
			'name'  => 'password',
			'label' => 'Live Password',
			'type' => 'text',
			'input_type' => 'password',
			'class' => 'beyond_pay_setting_live_only'
		    ),
		    array(
			'name'  => 'merchant_code',
			'label' => 'Merchant Code',
			'type'  => 'text'
		    ),
		    array(
			'name'  => 'merchant_account_code',
			'label' => 'Merchant Account Code',
			'type'  => 'text'
		    ),
		    array(
			'name'  => 'transaction_mode',
			'label' => 'Transaction Mode',
			'type' => 'select',
			'choices' => [
			    ['label' => 'Sale', 'value' => 'sale'],
			    ['label' => 'Authorization', 'value' => 'authorization']
			],
			'tooltip' => 'Sale mode will capture the payment instantly, '
			. 'authorization will only authorize when order is placed and capture '
			. 'once the capture button is pressed on the entry detail page.'
		    ),
		    array(
			'name'  => 'additional_data',
			'label' => 'Level II/III Data',
			'type' => 'select',
			'choices' => [
			    ['label' => 'Do not send additional data', 'value' => 'off'],
			    ['label' => 'Send Level II Data', 'value' => 'level2'],
			    ['label' => 'Send Level II and Level III Data', 'value' => 'level3']
			],
			'tooltip' => 'Select the level of transaction data to '
			. 'be automatically sent. Level II includes reference '
			. 'number and tax amount, while Level III includes '
			. 'line-item details. Set to Level III to ensure you always '
			. 'qualify for the best rates on eligible corporate '
			. 'purchasing cards.'
		    ),
		    array(
			'name'  => 'use_custom_styling',
			'label' => 'Advanced Styling',
			'type' => 'checkbox',
			'onchange' => 'onBeyondPayStylingChanged(this)',
			'tooltip' => 'Enable to apply custom css rules to the '
			. 'payment fields.',
			'choices' => [['label'=>'Enable', 'name'=>'use-custom-styling', 'value' => 'yes']],
		    ),
		    array(
			'name'  => 'styling',
			'label' => 'Payment Fields styling',
			'type'  => 'textarea',
			'class' => 'large beyond_pay_styling',
			'tooltip' => 'You can set the CSS rules here which will '
			. 'apply to the payment fields.',
			'default_value' => file_get_contents(dirname(__DIR__).'/assets/css/payment-styling.css'),
			'allow_html' => true
		    ),
                )
            )
        );
    }

    public function scripts() {
	$assets_dir = dirname($this->get_base_url()) . '/assets/js/';
	$test_mode = !empty($this->get_plugin_setting('enable-test-mode'));
	$public_key = $this->get_plugin_setting(($test_mode ? 'test_' : '').'public_key');

        $scripts = array(
	    array(
                'handle'  => 'tokenpay',
                'src'     => $assets_dir . 'token-pay.js',
                'version' => $this->_version
            ),
	    array(
                'handle'  => 'beyond_pay',
                'src'     => $assets_dir . 'beyond-pay.js',
                'version' => $this->_version,
		'deps'    => array( 'tokenpay' ),
                'enqueue' => array(
                    array(
			'admin_page' => array( 'form_editor' )
                    )
                ),
		'strings' => array(
                    'public_key'  => $public_key,
		    'is_test_mode' => $test_mode ? 'yes' : 'no'
                )
            ),
	    array(
                'handle'  => 'beyond_pay',
                'src'     => $assets_dir . 'beyond-pay.js',
                'version' => $this->_version,
		'deps'    => array( 'tokenpay' ),
                'enqueue' => array(
                    array(
                        'field_types' => array( 'beyond_pay' )
                    )
                ),
		'strings' => array(
                    'public_key'  => $public_key,
		    'is_test_mode' => $test_mode ? 'yes' : 'no'
                )
            ),
	    array(
                'handle'  => 'beyond_pay_admin_settings',
                'src'     => $assets_dir . 'admin-settings.js',
                'version' => $this->_version,
                'enqueue' => array(
                    array(
                        'admin_page' => array( 'plugin_settings' )
                    )
                )
            ),
	    array(
                'handle'  => 'beyond_pay_admin_entries',
                'src'     => $assets_dir . 'admin-entry.js',
                'version' => $this->_version,
                'enqueue' => array(
                    array(
                        'admin_page' => array( 'entry_detail', 'entry_view' )
                    )
                )
            )

        );

        return array_merge( parent::scripts(), $scripts );
    }

    public function authorize( $feed, $submission_data, $form, $entry ) {
	$amount = $submission_data['payment_amount'];
	$token = rgpost('beyond_pay_token');

	$request = new \BeyondPay\BeyondPayRequest();
	$request->RequestType = "004";
	$request->TransactionID = time();

	$test_mode = !empty($this->get_plugin_setting('enable-test-mode'));
	$request->PrivateKey = $this->get_plugin_setting(
	    ($test_mode ? 'test_' : '').'private_key'
	);
	$request->AuthenticationTokenId = $token;

	$request->requestMessage = new \BeyondPay\RequestMessage();
	$request->requestMessage->TransIndustryType = "EC";

	$transaction_mode = $this->get_plugin_setting('transaction_mode') === 'sale'
		? 'sale'
		: 'sale-auth';
	$request->requestMessage->TransactionType = $transaction_mode;
	$request->requestMessage->AcctType = "R";
	$request->requestMessage->Amount = round($amount * 100);
	$request->requestMessage->HolderType = "O";

	$request->requestMessage->AccountHolderName = trim($submission_data['name']);
	$request->requestMessage->AccountStreet = trim($submission_data['address']);
	if(!empty($submission_data['zip'])) {
	    $postcode = str_replace('-', '', $submission_data['zip']);
	    if(is_numeric($postcode) && strlen($postcode) === 5) {
		$request->requestMessage->AccountZip = $postcode;
	    }
	}
	if(!empty($submission_data['phone'])){
	    $submission_data['phone'] = str_replace([' ','-','#','+'], '', $submission_data['phone']);
	    while(strlen($submission_data['phone']) < 10){
		$submission_data['phone'] = '0'.$submission_data['phone'];
	    }
	    if(strlen($submission_data['phone']) < 12){
		$request->requestMessage->AccountPhone = trim($submission_data['phone']);
	    }
	}
	$additional_data = $this->get_plugin_setting('additional_data');
	$use_level_2_data = !empty($additional_data) && $additional_data !== 'off';
	$use_level_3_data = $additional_data == 'level3';
	$timestamp = time();
	$request->requestMessage->InvoiceNum = $timestamp;
	if($use_level_2_data){
	    $request->requestMessage->PONum = $timestamp;
	    $localTaxIndicator = 'N';
	    $request->requestMessage->LocalTaxIndicator = $localTaxIndicator;
	}
	if($use_level_3_data){
	    $item_count = 0;
	    $itemsParsed = [];
	    foreach($submission_data['line_items'] as $i){
		if(empty($i['unit_price'])){
		    continue;
		}
		$item_count += $i['quantity'];

		$itemParsed = new \BeyondPay\Item();
		$itemParsed->ItemCode = "1234";
		$itemParsed->ItemCommodityCode = "1234";
		$itemParsed->ItemDescription = substr($i['name'],0,35);
		$itemParsed->ItemQuantity = $i['quantity'];
		$itemParsed->ItemUnitMeasure = "EA";
		$itemParsed->ItemUnitCostAmt = round($i['unit_price']  * 100);
		$itemParsed->ItemTotalAmount = round((intval($i['quantity']) * $i['unit_price'])  * 100);
		$itemParsed->ItemTaxIndicator = 'N';
		array_push($itemsParsed, $itemParsed);
	    }
	    $request->requestMessage->ItemCount = $item_count === 0 ? 1 : $item_count;
	    $request->requestMessage->Item = $itemsParsed;
	}

	$conn = new \BeyondPay\BeyondPayConnection();

	$api_url = $test_mode ?
	    "https://api-test.getbeyondpay.com/paymentservice/requesthandler.svc" :
	    "https://api.getbeyondpay.com/PaymentService/RequestHandler.svc";
	try{
	    $response = $conn->processRequest($api_url, $request);
	} catch (exception $e) {
	    return $this->authorization_error($e->getMessage());
	}

	if ($response->ResponseCode == '00000') {
	    $message = $response->responseMessage;
	    $expiry = $message->ExpirationDate;
	    $pan = $message->Token;
	    if($transaction_mode === 'sale'){
		return [
		    'is_authorized' => true,
		    'captured_payment' => [
			'is_success'=>true,
			'transaction_id' => $message->GatewayTransID,
			'amount' => $amount,
			'payment_method' => $message->CardType,
			'beyond_pay_transaction_mode' => $transaction_mode,
			'beyond_pay_order_id' => $timestamp,
			'pan_token' => $pan,
			'cc_expiry' => $expiry,
			'card_type' => $message->CardType
		    ]
		];
	    } else {
		return [
		    'is_authorized' => true,
		    'error_message' => '',
		    'transaction_id' => $message->GatewayTransID,
		    'beyond_pay_transaction_mode' => $transaction_mode,
		    'beyond_pay_order_id' => $timestamp,
		    'pan_token' => $pan,
		    'cc_expiry' => $expiry,
		    'payment_method' => $message->CardType,
		    'card_type' => $message->CardType
		];
	    }
	} else {
	    $this->response_error = "Error processing payment: ".$response->ResponseDescription;
	    return $this->authorization_error($response->ResponseDescription);
	}
    }

    public function option_choices() {
	return [];
    }

    public function billing_info_fields() {

	$fields = array(
	    array( 'name' => 'name', 'label' => esc_html__( 'Name', 'gravityforms' ), 'required' => false ),
	    array( 'name' => 'phone', 'label' => esc_html__( 'Phone', 'gravityforms' ), 'required' => false ),
	    array( 'name' => 'address', 'label' => esc_html__( 'Street Address', 'gravityforms' ), 'required' => false ),
	    array( 'name' => 'zip', 'label' => esc_html__( 'Zip', 'gravityforms' ), 'required' => false )
	);

	return $fields;
    }

    public function get_credit_card_field( $form ) {
	$fields = GFAPI::get_fields_by_type( $form, array( 'beyond_pay' ) );
	return empty( $fields ) ? false : $fields[0];
    }

    public function update_entry_payment_info($entry, $payment_result = array()){
	if (
	    isset($payment_result['beyond_pay_transaction_mode']) &&
            (
		rgar($payment_result,'is_authorized') || rgar($payment_result,'is_success')
	    )
        ) {
	    $requires_capture = $payment_result['beyond_pay_transaction_mode'] === 'sale-auth';
	    gform_update_meta( $entry['id'], 'requires_capture', $requires_capture, $entry['form_id']);
	    gform_update_meta( $entry['id'], 'beyond_pay_order_id', $payment_result['beyond_pay_order_id'], $entry['form_id']);
	    $form = GFAPI::get_form( $entry['form_id'] );
            $ccField = $this->get_credit_card_field($form);
	    $cc_description =
		    $payment_result['card_type'].
		    ' ************'.substr($payment_result['pan_token'],-4).
		    ' Exp: '.substr($payment_result['cc_expiry'],0,2).'/'.substr($payment_result['cc_expiry'],-2);
	    $entry[$ccField->id] = $cc_description;
	    $entry['payment_amount'] = $payment_result['amount'];
	    $entry['payment_date'] = $payment_result['payment_date'];
	    $entry['payment_method'] = $payment_result['card_type'];
	    GFAPI::update_entry($entry);
        }
    }

    public function capture_authorised_payment(){
	$form_id = rgpost('form_id');
	$entry_id = rgpost('entry_id');
	if(gform_get_meta($entry_id, 'requires_capture') && $this->has_feed($form_id)){

	    $entry = GFAPI::get_entry($entry_id);
	    $feed = $this->get_payment_feed($entry);
	    if($feed['addon_slug']!==$this->_slug){
		return;
	    }
	    $form = GFAPI::get_form($form_id);
	    $sub_data = $this->get_submission_data($feed, $form, $entry);
	    $test_mode = !empty($this->get_plugin_setting('enable-test-mode'));
	    $api_url = $test_mode ?
		"https://api-test.getbeyondpay.com/paymentservice/requesthandler.svc" :
		"https://api.getbeyondpay.com/PaymentService/RequestHandler.svc";
	    $login = $this->get_plugin_setting(($test_mode ? 'test_' : '').'login');
	    $password = $this->get_plugin_setting(($test_mode ? 'test_' : '').'password');
	    $merchant_code = $this->get_plugin_setting('merchant_code');
	    $merchant_account_code = $this->get_plugin_setting('merchant_account_code');

	    $request = new \BeyondPay\BeyondPayRequest();
	    $request->RequestType = "019";
	    $request->TransactionID = time();

	    $request->User = $login;
	    $request->Password = $password;

	    $request->requestMessage = new \BeyondPay\RequestMessage();
	    $request->requestMessage->SoftwareVendor = 'GravityForms Beyond Pay Plugin';
	    $request->requestMessage->TransactionType = 'capture';
	    $request->requestMessage->Amount = round($sub_data['payment_amount']*100);
	    $request->requestMessage->MerchantCode = $merchant_code;
	    $request->requestMessage->MerchantAccountCode = $merchant_account_code;
	    $request->requestMessage->ReferenceNumber = $entry['transaction_id'];

	    $conn = new \BeyondPay\BeyondPayConnection();
	    $response = $conn->processRequest($api_url, $request);
	    if ($response->ResponseCode == '00000') {
		gform_update_meta( $entry['id'], 'requires_capture', false, $entry['form_id']);
		$this->complete_payment( $entry, [
		    'amount'   => $sub_data['payment_amount'],
		    'transaction_id'   => $entry['transaction_id']
		]);
		echo json_encode(['success'=>true]);
	    } else {
		echo json_encode(['success'=>false,'error'=> $response->ResponseDescription, 'response_code'=>$response->ResponseCode]);
	    }
	    die();
	}
    }

    /**
     * Renders and initializes a password field based on the $field array
     *
     * @param array $field - Field array containing the configuration options of this field
     * @param bool  $echo  = true - true to echo the output to the screen, false to simply return the contents as a string
     *
     * @return string The HTML for the field
     */
    public function settings_password($field, $echo = true) {

	$html = str_replace('type="text"','type="password"',$this->settings_text($field, false));

	if($echo){
	    echo esc_html($html);
	}
	return $html;
    }

    public function add_payment_error_description($message) {
	if(!empty($this->response_error)){
	    return "<div class='validation_error'>".$this->response_error."</div>";
	} else {
	    return $message;
	}
    }

}
