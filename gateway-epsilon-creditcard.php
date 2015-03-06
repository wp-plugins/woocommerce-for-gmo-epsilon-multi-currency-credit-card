<?php

/**
 * Plugin Name: WooCommerce Payment Gateway Multi-currency Credit Card- GMO Epsilon
 * Plugin URI: http://www.wp-pay.com/
 * Description: Accept Multi-currency credit cards directly on your WooCommerce site in a seamless and secure checkout environment with GMO Epsilon Commerce.
 * Version: 1.0.0
 * Author: 職人工房
 * Author URI: http://wc.artws.info/
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
 * Requires at least: 3.8
 * Tested up to: 3.9
 *
 * Text Domain: wc-epsilon
 * Domain Path: /i18n/
 *
 * @package WordPress
 * @author Artisan Workshop
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'plugins_loaded', 'woocommerce_gmo_epsilon_mccc_creditcard_init', 0 );

function woocommerce_gmo_epsilon_mccc_creditcard_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
    return;
  };

  DEFINE ('PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );

	/**
	 * GMO Epsilon Gateway Class
	 */
		class WC_Epsilon_Mccc extends WC_Payment_Gateway {

			function __construct() {

        // Register plugin information
	      $this->id			    = 'epsilon_mccc';
	      $this->has_fields = true;
	      $this->supports   = array(
               'products',
               'subscriptions',
               'subscription_cancellation',
               'subscription_suspension',
               'subscription_reactivation',
               'subscription_date_changes',
               );

        // Create plugin fields and settings
				$this->init_form_fields();
				$this->init_settings();
				$this->init();
		$this->method_title       = __( 'GMO Epsilon Multi-currency Credit Card Payment Gateway', 'wc-epsilon' );
		$this->method_description = __( 'Allows payments by GMO Epsilon Multi-currency Credit Card in Japan.', 'wc-epsilon' );

				// Get setting values
				foreach ( $this->settings as $key => $val ) $this->$key = $val;

        // Load plugin checkout icon
	      $this->icon = PLUGIN_DIR . 'images/cards.png';

        // Add hooks
				add_action( 'admin_notices',                                            array( $this, 'epsilon_mccc_commerce_ssl_check' ) );
				add_action( 'woocommerce_receipt_epsilon_mccc',                              array( $this, 'receipt_page' ) );
				add_action( 'woocommerce_update_options_payment_gateways',              array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'wp_enqueue_scripts',                                       array( $this, 'add_epsilon_mccc_scripts' ) );
//				add_action( 'scheduled_subscription_payment_epsilon',                   array( $this, 'process_scheduled_subscription_payment'), 0, 3 );
		  }

	/**
	 * Init WooCommerce Payment Gateway Credit Card- GMO Epsilon when WordPress Initialises.
	 */
	public function init() {
		// Set up localisation
		$this->load_plugin_textdomain();
	}

	/*
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wc-epsilon' );
		// Global + Frontend Locale
		load_plugin_textdomain( 'wc-epsilon', false, plugin_basename( dirname( __FILE__ ) ) . "/i18n" );
	}

      /**
       * Check if SSL is enabled and notify the user.
       */
      function epsilon_mccc_commerce_ssl_check() {
        if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'no' && $this->enabled == 'yes' ) {
            echo '<div class="error"><p>' . sprintf( __('GMO Epsilon Commerce is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'wc-epsilon' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '</p></div>';
            }
      }

      /**
       * Initialize Gateway Settings Form Fields.
       */
	    function init_form_fields() {

	      $this->form_fields = array(
	      'enabled'     => array(
	        'title'       => __( 'Enable/Disable', 'wc-epsilon' ),
	        'label'       => __( 'Enable Multi-currency Epsilon Payment', 'wc-epsilon' ),
	        'type'        => 'checkbox',
	        'description' => '',
	        'default'     => 'no'
	        ),
	      'title'       => array(
	        'title'       => __( 'Title', 'wc-epsilon' ),
	        'type'        => 'text',
	        'description' => __( 'This controls the title which the user sees during checkout.', 'wc-epsilon' ),
	        'default'     => __( 'Credit Card (Epsilon)', 'wc-epsilon' )
	        ),
	      'description' => array(
	        'title'       => __( 'Description', 'wc-epsilon' ),
	        'type'        => 'textarea',
	        'description' => __( 'This controls the description which the user sees during checkout.', 'wc-epsilon' ),
	        'default'     => __( 'Pay with your credit card via Epsilon.', 'wc-epsilon' )
	        ),
	      'contract_code'    => array(
	        'title'       => __( 'Contract Code', 'wc-epsilon' ),
	        'type'        => 'text',
	        'description' => __( 'This is the API Contract Code generated within the epsilon payment gateway.', 'wc-epsilon' ),
	        'default'     => ''
	        ),
			'security_check' => array(
				'title'       => __( 'Security Check Code', 'wc-epsilon' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable Security Check Code', 'wc-epsilon' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Require customer to enter credit card CVV code (Security Check Code).', 'wc-epsilon' )),
			),
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'wc-epsilon' ),
				'type'        => 'title',
				'description' => '',
			),
			'testmode' => array(
				'title'       => __( 'Epsilon Test mode', 'wc-epsilon' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable Epsilon Test mode', 'wc-epsilon' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Please check you want to use Epsilon Test mode.', 'wc-epsilon' )),
			)
			);
		  }


      /**
       * UI - Admin Panel Options
       */
			function admin_options() { ?>
				<h3><?php _e( 'Multi-currency Epsilon Payment','wc-epsilon' ); ?></h3>
			    <p><?php _e( 'The Epsilon Payment Gateway is simple and powerful.  The plugin works by adding credit card fields on the checkout page, and then sending the details to Epsilon Payment for verification.  <a href="http://www.wp-pay.com/payment-agency/epsilon/">Click here to read GMO epsilon order</a>.', 'wc-epsilon' ); ?></p>
			    <table class="form-table">
					<?php $this->generate_settings_html(); ?>
				</table>
			<?php }
      /**
       * UI - Payment page fields for Epsilon Payment.
       */
			function payment_fields() {
          		// Description of payment method from settings
          		if ( $this->description ) { ?>
            		<p><?php echo $this->description; ?></p>
      		<?php } ?>
			<fieldset  style="padding-left: 40px;">
		        <?php
		          $user = wp_get_current_user();
				  $customer_check = $this->user_has_stored_data( $user->ID );
		          if ( $customer_check['err_code']!=801) { ?>
						<fieldset>
							<input type="radio" name="epsilon-use-stored-payment-info" id="epsilon-use-stored-payment-info-yes" value="yes" checked="checked" onclick="document.getElementById('epsilon-new-info').style.display='none'; document.getElementById('epsilon-stored-info').style.display='block'"; /><label for="epsilon-use-stored-payment-info-yes" style="display: inline;"><?php _e( 'Use a stored credit card from Epsilon', 'wc-epsilon' ) ?></label>
								<div id="epsilon-stored-info" style="padding: 10px 0 0 40px; clear: both;">
				                    <p><?php if($customer_check['result']==1):?>
											<?php _e( 'credit card last 4 numbers: ', 'wc-epsilon' ) ?><?php echo $customer_check['card_number_mask']; ?> (<?php echo $customer_check['card_bland']; ?>)
											<br /><?php elseif($method['result']==9):?>
											<?php echo $method['err_detail']; ?> (<?php echo $method['err_code']; ?>)
											<br /><?php elseif($method['result']==3):?>
											<?php echo _e('Not Found your infomation, Epsilon maybe delete your infomation.', 'wc-epsilon' ); ?>
											<br /><?php endif;?>
				                    </p>
						</fieldset>
						<fieldset>
							<p>
								<input type="radio" name="epsilon-use-stored-payment-info" id="epsilon-use-stored-payment-info-no" value="no" onclick="document.getElementById('epsilon-stored-info').style.display='none'; document.getElementById('epsilon-new-info').style.display='block'"; />
		                  		<label for="epsilon-use-stored-payment-info-no"  style="display: inline;"><?php _e( 'Use a new payment method', 'wc-epsilon' ) ?></label>
		                	</p>
		                	<div id="epsilon-new-info" style="display:none">
						</fieldset>
				<?php } else { ?>
              			<fieldset>
              				<!-- Show input boxes for new data -->
              				<div id="epsilon-new-info">
              					<?php } ?>
								<!-- Credit card number -->
                    			<p class="form-row form-row-first">
									<label for="ccnum"><?php echo __( 'Credit Card number', 'wc-epsilon' ) ?> <span class="required">*</span></label>
									<input type="text" class="input-text" id="card_number" name="card_number" maxlength="16" />
                    			</p>
								<!-- Credit card type -->
								<div class="clear"></div>
								<!-- Credit card expiration -->
                    			<p class="form-row form-row-first">
                      				<label for="cc-expire-month"><?php echo __( 'Expiration date', 'wc-epsilon') ?> <span class="required">*</span></label>
                      				<select name="expire_m" id="expire_m" class="woocommerce-select woocommerce-cc-month">
                        				<option value=""><?php _e( 'Month', 'wc-epsilon' ) ?></option><?php
				                        $months = array();
				                        for ( $i = 1; $i <= 12; $i ++ ) {
				                          $timestamp = mktime( 0, 0, 0, $i, 1 );
				                          $months[ date( 'n', $timestamp ) ] = date( 'n', $timestamp );
				                        }
				                        foreach ( $months as $num => $name ) {
				                          printf( '<option value="%u">%s</option>', $num, $name );
				                        } ?>
                      				</select>
                      				<select name="expire_y" id="expire_y" class="woocommerce-select woocommerce-cc-year">
                        				<option value=""><?php _e( 'Year', 'wc-epsilon' ) ?></option><?php
				                        $years = array();
				                        for ( $i = date( 'y' ); $i <= date( 'y' ) + 15; $i ++ ) {
				                          printf( '<option value="20%u">20%u</option>', $i, $i );
				                        } ?>
                      				</select>
                    			</p>
								<?php

				                    // Credit card security code
				                    if ( $this->security_check == 'yes' ) { ?>
				                      <p class="form-row form-row-last">
				                        <label for="cvv"><?php _e( 'Card security code', 'wc-epsilon' ) ?> <span class="required">*</span></label>
				                        <input oninput="validate_cvv(this.value)" type="text" class="input-text" id="cvv" name="security_code" maxlength="4" style="width:45px" />
				                        <span class="help"><?php _e( '3 or 4 digits usually found on the signature strip.', 'wc-epsilon' ) ?></span>
				                      </p><?php
				                    }

			                    // Option to store credit card data
			                    if ( $this->saveinfo == 'yes' && ! ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) ) { ?>
			                      	<div style="clear: both;"></div>
										<p>
			                        		<label for="saveinfo"><?php _e( 'Save this billing method?', 'wc-epsilon' ) ?></label>
			                        		<input type="checkbox" class="input-checkbox" id="saveinfo" name="saveinfo" />
			                        		<span class="help"><?php _e( 'Select to store your billing information for future use.', 'wc-epsilon' ) ?></span>
			                      		</p>
									<?php  } ?>
            			</fieldset>
			</fieldset>
<?php
    }

		/**
		 * Process the payment and return the result.
		 */
		function process_payment( $order_id ) {

			global $woocommerce;

			$order = new WC_Order( $order_id );
      $user = new WP_User( $order->user_id );
	  // required request information
	$base_request = array (
		'user_name'   => $order->billing_first_name." ".$order->billing_last_name,
		'user_mail_add'       => $order->billing_email,
		'order_number' 	=> $order->id,
		'item_price' 	    => $order->order_total,
	);
	if($order->user_id){
		$base_request['user_id']   = $order->user_id;
	}else{
		$base_request['user_id']   = $order->id.'-user';
	}
			

		//
			if ( sizeof( $order->get_items() ) > 0 ) {
				foreach ( $order->get_items() as $item ) {
					if ( $item['qty'] ) {
						if ($item === end($order->get_items())) {
						// last
						$item_names .= $item['name'];
						$item_codes .= $item['product_id'];
						}else{
						$item_names .= $item['name'].' ';
						$item_codes .= $item['product_id'].'-';
						}
					}
				}
			}
			$item_names = substr($item_names, 0, 64);
			$item_codes = substr($item_codes, 0, 64);
			if(!$item_names)$item_names='no-items';
			if(!$item_codes)$item_codes='no-item-codes';
		$base_request['item_name'] = $item_names;
		$base_request['item_code'] = $item_codes;

      // Create server request using stored or new payment details
		if ( $this->get_post( 'epsilon-use-stored-payment-info' ) == 'yes' ) {
			$base_request['process_code'] 		= 1;//Using stored payment

		} else {

		//Credit Card Infomation
        $base_request['card_number'] 	= $this->get_post( 'card_number' );
        $base_request['expire_m'] 	= $this->get_post( 'expire_m' );
        $base_request['expire_y'] 	= $this->get_post( 'expire_y' );

		$base_request['process_code'] 	= 1;//First time payment

        // Using Security Check
        if ( $this->security_check == 'yes' ) {
			$base_request['security_code'] 	= $this->get_post( 'security_code' );
			$base_request['security_check'] 	= 1;
		}

      }
		//Get Currency infomation
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

      // Add transaction-specific details to the request
      $transaction_details = array (
        'version' => 2,
        'contract_code' => $this->contract_code,
        'st_code' 	=> '00000-0000-00000-00001-00000-00000-00000',
		'mission_code' => 1,
		'memo2' 	=> 'woocommerce',
		'character_code' 	=> 'UTF8',
		'currency_id' 	=> $currency,
		'tds_check_code' => ''
        );

		// Send request and get response from server
		$response = $this->post_and_get_response( array_merge( $transaction_details,$base_request ) );

      // Check response
      if ( $response['result'] == 1 ) {
        // Success
        $order->add_order_note( __( 'Epsilon Payment payment completed. Transaction ID: ' , 'wc-epsilon' ) . $response['trans_code'] );
        $order->payment_complete();

        // Return thank you redirect
        return array (
          'result'   => 'success',
          'redirect' => $this->get_return_url( $order ),
        );

      } else if ( $response['result'] == 5 ) {//3DS
//		$order_key = get_post_meta($order->id, '_order_key', true);
//		$current_url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//		$term_url = 'http://wc21.ecshop4u.info/checkout/order-received/'.$order->id.'/?key='.$order_key;
		$term_url = $this->get_return_url( $order );
		session_start();
		$_SESSION['acsurl'] = urldecode($response['acsurl']);
		$_SESSION['PaReq'] = urldecode($response['pareq']);
		$_SESSION['TermUrl'] = urldecode($term_url);
		$_SESSION['MD'] = $order->id;
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( '3DSecure Payment Processing.', 'woocommerce-4jp' ) );

			// Reduce stock levels
			$order->reduce_order_stock();

			// Remove cart
			WC()->cart->empty_cart();
			return array(
				'result' 	=> 'success',
				'redirect'	=> plugins_url('/woocommerce-for-gmo-epsilon-multi-currency-credit-card/3ds.php')
			);
			
      } else if ( $response['result'] == 9 ) {//System Error
        // Other transaction error
        $order->add_order_note( __( 'Epsilon Payment failed. Sysmte Error: ', 'wc-epsilon' ) . $response['err_code'] .':'. urldecode($response['err_detail']) .':'.$response['trans_code']);
        wc_add_notice( __( 'Sorry, there was an error: ', 'wc-epsilon' ) . $response['err_code'], $notice_type = 'error' );
      } else if ( $response['result'] == 0 ) {//Payment NG
        $order->add_order_note( __( "Epsilon Payment failed. Some trouble happened.", 'wc-epsilon' ). $response['err_code'] .':'. urldecode($response['err_detail']).':'.$response['trans_code'] );
        wc_add_notice( __( 'Payment NG.', 'wc-epsilon' ). $response['err_code'], $notice_type = 'error' );
      } else {
        // No response or unexpected response
        $order->add_order_note( __( "Epsilon Payment failed. Some trouble happened.", 'wc-epsilon' ). $response['err_code'] .':'. urldecode($response['err_detail']).':'.$response['trans_code'] );
        wc_add_notice( __( 'No response from payment gateway server. Try again later or contact the site administrator.', 'wc-epsilon' ). $response['err_code'], $notice_type = 'error' );

      }

	}

		/**
		 * Process a payment for an ongoing subscription.
		 */
    function process_scheduled_subscription_payment( $amount_to_charge, $order, $product_id ) {

      $user = new WP_User( $order->user_id );
      $customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
      $payment_method_number = get_post_meta( $order->id, 'payment_method_number', true );

      $inspire_request = array (
				'username' 		      => $this->username,
				'password' 	      	=> $this->password,
				'amount' 		      	=> $amount_to_charge,
				'type' 			        => $this->salemethod,
				'billing_method'    => 'recurring',
        );

      $id = $customer_vault_ids[ $payment_method_number ];
      if( substr( $id, 0, 1 ) !== '_' ) $inspire_request['customer_vault_id'] = $id;
      else {
        $inspire_request['customer_vault_id'] = $user->user_login;
        $inspire_request['billing_id']        = substr( $id , 1 );
        $inspire_request['ver']               = 2;
      }

      $response = $this->post_and_get_response( $inspire_request );

      if ( $response['response'] == 1 ) {
        // Success
        $order->add_order_note( __( 'Epsilon Payment scheduled subscription payment completed. Transaction ID: ' , 'wc-epsilon' ) . $response['transactionid'] );
        WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );

			} else if ( $response['response'] == 2 ) {
        // Decline
        $order->add_order_note( __( 'Epsilon Payment scheduled subscription payment failed. Payment declined.', 'wc-epsilon') );
        WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );

      } else if ( $response['response'] == 3 ) {
        // Other transaction error
        $order->add_order_note( __( 'Epsilon Payment scheduled subscription payment failed. Error: ', 'wc-epsilon') . $response['responsetext'] );
        WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );

      } else {
        // No response or unexpected response
        $order->add_order_note( __('Epsilon Payment scheduled subscription payment failed. Couldn\'t connect to gateway server.', 'wc-epsilon') );

      }
    }

    /**
     * Check if the user has any billing records in the Customer Vault
     */
    function user_has_stored_data( $user_id ) {
		if( $this->testmode == 'no' ){
		$get_user_info_url = "https://secure.epsilon.jp/cgi-bin/order/get_user_info.cgi";
		}else{
		$get_user_info_url = "https://beta.epsilon.jp/cgi-bin/order/get_user_info.cgi";
		}

		$post_data = array(
			"contract_code" => $this->contract_code ,
			"user_id"=>$user_id
		);
		// make new cURL resource
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
		curl_setopt($ch, CURLOPT_URL, $get_user_info_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($ch);
		curl_close($ch);
		$array = explode("\n", $output);
		foreach($array as $value){
			$title = substr($value,10,5);
			switch($title){
				case 'card_':
				if(substr($value,10,6)=='card_n'){
				$result['card_number_mask'] = substr(substr($value,65),0,-4);
				}elseif(substr($value,10,6)=='card_b'){
				$result['card_bland'] = substr(substr($value,22),0,-4);
				}
				break;
				case 'err_c':
				$result['err_code'] = substr(substr($value,20),0,-4);
				break;
				case 'err_d':
				$result['err_detail'] = substr(substr($value,22),0,-4);
				break;
				case 'resul':
				if(substr($value,10,7)!="result>"){
					$result['result'] = substr(substr($value,18),0,-4);
				}
				break;
			}
		}
      return $result;
    }

    /**
     * Check payment details for valid format
     */
		function validate_fields() {

      if ( $this->get_post( 'epsilon-use-stored-payment-info' ) == 'yes' ) return true;

			global $woocommerce;

			// Check for saving payment info without having or creating an account
			if ( $this->get_post( 'saveinfo' )  && ! is_user_logged_in() && ! $this->get_post( 'createaccount' ) ) {
        wc_add_notice( __( 'Sorry, you need to create an account in order for us to save your payment information.', 'wc-epsilon'), $notice_type = 'error' );
        return false;
      }

			$cardNumber          = $this->get_post( 'card_number' );
			$cardCSC             = $this->get_post( 'security_code' );
			$cardExpirationMonth = $this->get_post( 'expire_m' );
			$cardExpirationYear  = $this->get_post( 'expire_y' );

			// Check card number
			if ( empty( $cardNumber ) || ! ctype_digit( $cardNumber ) ) {
				wc_add_notice( __( 'Card number is invalid.', 'wc-epsilon' ), $notice_type = 'error' );
				return false;
			}

			if ( $this->security_check == 'yes' ){
				// Check security code
				if ( ! ctype_digit( $cardCSC ) ) {
					wc_add_notice( __( 'Card security code is invalid (only digits are allowed).', 'wc-epsilon' ), $notice_type = 'error' );
					return false;
				}
				if ( ( strlen( $cardCSC ) >4 ) ) {
					wc_add_notice( __( 'Card security code is invalid (wrong length).', 'wc-epsilon' ), $notice_type = 'error' );
					return false;
				}
			}

			// Check expiration data
			$currentYear = date( 'Y' );

			if ( ! ctype_digit( $cardExpirationMonth ) || ! ctype_digit( $cardExpirationYear ) ||
				 $cardExpirationMonth > 12 ||
				 $cardExpirationMonth < 1 ||
				 $cardExpirationYear < $currentYear ||
				 $cardExpirationYear > $currentYear + 20
			) {
				wc_add_notice( __( 'Card expiration date is invalid', 'wc-epsilon' ), $notice_type = 'error' );
				return false;
			}

			// Strip spaces and dashes
			$cardNumber = str_replace( array( ' ', '-' ), '', $cardNumber );

			return true;

		}

    /**
     * Send the payment data to the gateway server and return the response.
     */
    private function post_and_get_response( $request ) {

		if($this->testmode=='no'){
		$direct_card_url = "https://secure.epsilon.jp/cgi-bin/order/direct_card_multi.cgi";
		}else{
		$direct_card_url = "https://beta.epsilon.jp/cgi-bin/order/direct_card_multi.cgi";
		}

		// make new cURL resource
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));
		curl_setopt($ch, CURLOPT_URL, $direct_card_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($ch);
		curl_close($ch);

		$array = explode("\n", $output);
		foreach($array as $value){
			$title = substr($value,10,5);
			switch($title){
				case 'acsur':
				$result['acsurl'] = substr(substr($value,18),0,-4);
				break;
				case 'err_c':
				$result['err_code'] = substr(substr($value,20),0,-4);
				break;
				case 'err_d':
				$result['err_detail'] = substr(substr($value,22),0,-4);
				break;
				case 'pareq':
				$result['pareq'] = substr(substr($value,17),0,-4);
				break;
				case 'resul':
				if(substr($value,10,7)!="result>"){
					$result['result'] = substr(substr($value,18),0,-4);
				}
				break;
				case 'trans':
				$result['trans_code'] = substr(substr($value,22),0,-4);
				break;
				case 'kari_':
				$result['kari_flag'] = substr(substr($value,21),0,-4);
				break;
			}
		}

      // Return response array
      return $result;
    }


		function receipt_page( $order ) {
			echo '<p>' . __( 'Thank you for your order.', 'wc-epsilon' ) . '</p>';
		}

    /**
     * Include jQuery and our scripts
     */
    function add_epsilon_mccc_scripts() {

      if ( ! $this->user_has_stored_data( wp_get_current_user()->ID ) ) return;

      wp_enqueue_script( 'jquery' );
      wp_enqueue_script( 'edit_billing_details', PLUGIN_DIR . 'js/edit_billing_details.js', array( 'jquery' ), 1.0 );

      if ( $this->security_check == 'yes' ) wp_enqueue_script( 'check_cvv', PLUGIN_DIR . 'js/check_cvv.js', array( 'jquery' ), 1.0 );

    }

		/**
		 * Get post data if set
		 */
		private function get_post( $name ) {
			if ( isset( $_POST[ $name ] ) ) {
				return $_POST[ $name ];
			}
			return null;
		}

		/**
     * Check whether an order is a subscription
     */
		private function is_subscription( $order ) {
      return class_exists( 'WC_Subscriptions_Order' ) && WC_Subscriptions_Order::order_contains_subscription( $order );
		}

	}

	/**
	 * Add the gateway to woocommerce
	 */
	function add_epsilon_mccc_commerce_gateway( $methods ) {
		$methods[] = 'WC_Epsilon_Mccc';
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_epsilon_mccc_commerce_gateway' );

	/**
	 * Edit the available gateway to woocommerce
	 */
	function edit_available_gateways_epsilon_mccc( $_available_gateways ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}
		if($currency =='JPY'){
		unset($_available_gateways['epsilon_mccc']);
		}
		return $_available_gateways;
	}

	add_filter( 'woocommerce_available_payment_gateways', 'edit_available_gateways_epsilon_mccc' );

}