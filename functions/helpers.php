<?php
if( !function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if( ! function_exists( 'twoco_pri' ) ) :
function twoco_pri( $data ) {
	echo '<pre>';
	if( is_object( $data ) || is_array( $data ) ) {
		print_r( $data );
	}
	else {
		var_dump( $data );
	}
	echo '</pre>';
}
endif;

if( ! function_exists( 'twoco_get_option' ) ) :
function twoco_get_option( $key, $section, $default = '' ) {

	$options = get_option( $key );

	if ( isset( $options[ $section ] ) ) {
		return $options[ $section ];
	}

	return $default;
}
endif;

if( !function_exists( 'twoco_get_template' ) ) :
/**
 * Includes a template file resides in /views diretory
 *
 * It'll look into /2checkout directory of your active theme
 * first. if not found, default template will be used.
 * can be overriden with 2checkout_template_override_dir hook
 *
 * @param string $slug slug of template. Ex: template-slug.php
 * @param string $sub_dir sub-directory under base directory
 * @param array $fields fields of the form
 */
function twoco_get_template( $slug, $base = 'views', $args = null ) {

	// templates can be placed in this directory
	$override_template_dir = apply_filters( 'twoco_template_override_dir', get_stylesheet_directory() . '/2checkout/', $slug, $base, $args );
	
	// default template directory
	$plugin_template_dir = dirname( TWOCO ) . "/{$base}/";

	// full path of a template file in plugin directory
	$plugin_template_path =  $plugin_template_dir . $slug . '.php';
	
	// full path of a template file in override directory
	$override_template_path =  $override_template_dir . $slug . '.php';

	// if template is found in override directory
	if( file_exists( $override_template_path ) ) {
		ob_start();
		include $override_template_path;
		return ob_get_clean();
	}
	// otherwise use default one
	elseif ( file_exists( $plugin_template_path ) ) {
		ob_start();
		include $plugin_template_path;
		return ob_get_clean();
	}
	else {
		return __( 'Template not found!', '2checkout' );
	}
}
endif;

/**
 * return currencies list
 * @author Jakaria Istauk <jakariamd35@gmail.com>
 * @since 1.0
 */
if( !function_exists( 'twoco_get_currencies' ) ) :
function twoco_get_currencies() {
	$currencies = array_unique(
		apply_filters(
			'twoco_currencies',
			array(
				'AED' => __( 'United Arab Emirates dirham', '2checkout' ),
				'AFN' => __( 'Afghan afghani', '2checkout' ),
				'ALL' => __( 'Albanian lek', '2checkout' ),
				'AMD' => __( 'Armenian dram', '2checkout' ),
				'ANG' => __( 'Netherlands Antillean guilder', '2checkout' ),
				'AOA' => __( 'Angolan kwanza', '2checkout' ),
				'ARS' => __( 'Argentine peso', '2checkout' ),
				'AUD' => __( 'Australian dollar', '2checkout' ),
				'AWG' => __( 'Aruban florin', '2checkout' ),
				'AZN' => __( 'Azerbaijani manat', '2checkout' ),
				'BAM' => __( 'Bosnia and Herzegovina convertible mark', '2checkout' ),
				'BBD' => __( 'Barbadian dollar', '2checkout' ),
				'BDT' => __( 'Bangladeshi taka', '2checkout' ),
				'BGN' => __( 'Bulgarian lev', '2checkout' ),
				'BHD' => __( 'Bahraini dinar', '2checkout' ),
				'BIF' => __( 'Burundian franc', '2checkout' ),
				'BMD' => __( 'Bermudian dollar', '2checkout' ),
				'BND' => __( 'Brunei dollar', '2checkout' ),
				'BOB' => __( 'Bolivian boliviano', '2checkout' ),
				'BRL' => __( 'Brazilian real', '2checkout' ),
				'BSD' => __( 'Bahamian dollar', '2checkout' ),
				'BTC' => __( 'Bitcoin', '2checkout' ),
				'BTN' => __( 'Bhutanese ngultrum', '2checkout' ),
				'BWP' => __( 'Botswana pula', '2checkout' ),
				'BYR' => __( 'Belarusian ruble (old)', '2checkout' ),
				'BYN' => __( 'Belarusian ruble', '2checkout' ),
				'BZD' => __( 'Belize dollar', '2checkout' ),
				'CAD' => __( 'Canadian dollar', '2checkout' ),
				'CDF' => __( 'Congolese franc', '2checkout' ),
				'CHF' => __( 'Swiss franc', '2checkout' ),
				'CLP' => __( 'Chilean peso', '2checkout' ),
				'CNY' => __( 'Chinese yuan', '2checkout' ),
				'COP' => __( 'Colombian peso', '2checkout' ),
				'CRC' => __( 'Costa Rican col&oacute;n', '2checkout' ),
				'CUC' => __( 'Cuban convertible peso', '2checkout' ),
				'CUP' => __( 'Cuban peso', '2checkout' ),
				'CVE' => __( 'Cape Verdean escudo', '2checkout' ),
				'CZK' => __( 'Czech koruna', '2checkout' ),
				'DJF' => __( 'Djiboutian franc', '2checkout' ),
				'DKK' => __( 'Danish krone', '2checkout' ),
				'DOP' => __( 'Dominican peso', '2checkout' ),
				'DZD' => __( 'Algerian dinar', '2checkout' ),
				'EGP' => __( 'Egyptian pound', '2checkout' ),
				'ERN' => __( 'Eritrean nakfa', '2checkout' ),
				'ETB' => __( 'Ethiopian birr', '2checkout' ),
				'EUR' => __( 'Euro', '2checkout' ),
				'FJD' => __( 'Fijian dollar', '2checkout' ),
				'FKP' => __( 'Falkland Islands pound', '2checkout' ),
				'GBP' => __( 'Pound sterling', '2checkout' ),
				'GEL' => __( 'Georgian lari', '2checkout' ),
				'GGP' => __( 'Guernsey pound', '2checkout' ),
				'GHS' => __( 'Ghana cedi', '2checkout' ),
				'GIP' => __( 'Gibraltar pound', '2checkout' ),
				'GMD' => __( 'Gambian dalasi', '2checkout' ),
				'GNF' => __( 'Guinean franc', '2checkout' ),
				'GTQ' => __( 'Guatemalan quetzal', '2checkout' ),
				'GYD' => __( 'Guyanese dollar', '2checkout' ),
				'HKD' => __( 'Hong Kong dollar', '2checkout' ),
				'HNL' => __( 'Honduran lempira', '2checkout' ),
				'HRK' => __( 'Croatian kuna', '2checkout' ),
				'HTG' => __( 'Haitian gourde', '2checkout' ),
				'HUF' => __( 'Hungarian forint', '2checkout' ),
				'IDR' => __( 'Indonesian rupiah', '2checkout' ),
				'ILS' => __( 'Israeli new shekel', '2checkout' ),
				'IMP' => __( 'Manx pound', '2checkout' ),
				'INR' => __( 'Indian rupee', '2checkout' ),
				'IQD' => __( 'Iraqi dinar', '2checkout' ),
				'IRR' => __( 'Iranian rial', '2checkout' ),
				'IRT' => __( 'Iranian toman', '2checkout' ),
				'ISK' => __( 'Icelandic kr&oacute;na', '2checkout' ),
				'JEP' => __( 'Jersey pound', '2checkout' ),
				'JMD' => __( 'Jamaican dollar', '2checkout' ),
				'JOD' => __( 'Jordanian dinar', '2checkout' ),
				'JPY' => __( 'Japanese yen', '2checkout' ),
				'KES' => __( 'Kenyan shilling', '2checkout' ),
				'KGS' => __( 'Kyrgyzstani som', '2checkout' ),
				'KHR' => __( 'Cambodian riel', '2checkout' ),
				'KMF' => __( 'Comorian franc', '2checkout' ),
				'KPW' => __( 'North Korean won', '2checkout' ),
				'KRW' => __( 'South Korean won', '2checkout' ),
				'KWD' => __( 'Kuwaiti dinar', '2checkout' ),
				'KYD' => __( 'Cayman Islands dollar', '2checkout' ),
				'KZT' => __( 'Kazakhstani tenge', '2checkout' ),
				'LAK' => __( 'Lao kip', '2checkout' ),
				'LBP' => __( 'Lebanese pound', '2checkout' ),
				'LKR' => __( 'Sri Lankan rupee', '2checkout' ),
				'LRD' => __( 'Liberian dollar', '2checkout' ),
				'LSL' => __( 'Lesotho loti', '2checkout' ),
				'LYD' => __( 'Libyan dinar', '2checkout' ),
				'MAD' => __( 'Moroccan dirham', '2checkout' ),
				'MDL' => __( 'Moldovan leu', '2checkout' ),
				'MGA' => __( 'Malagasy ariary', '2checkout' ),
				'MKD' => __( 'Macedonian denar', '2checkout' ),
				'MMK' => __( 'Burmese kyat', '2checkout' ),
				'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', '2checkout' ),
				'MOP' => __( 'Macanese pataca', '2checkout' ),
				'MRU' => __( 'Mauritanian ouguiya', '2checkout' ),
				'MUR' => __( 'Mauritian rupee', '2checkout' ),
				'MVR' => __( 'Maldivian rufiyaa', '2checkout' ),
				'MWK' => __( 'Malawian kwacha', '2checkout' ),
				'MXN' => __( 'Mexican peso', '2checkout' ),
				'MYR' => __( 'Malaysian ringgit', '2checkout' ),
				'MZN' => __( 'Mozambican metical', '2checkout' ),
				'NAD' => __( 'Namibian dollar', '2checkout' ),
				'NGN' => __( 'Nigerian naira', '2checkout' ),
				'NIO' => __( 'Nicaraguan c&oacute;rdoba', '2checkout' ),
				'NOK' => __( 'Norwegian krone', '2checkout' ),
				'NPR' => __( 'Nepalese rupee', '2checkout' ),
				'NZD' => __( 'New Zealand dollar', '2checkout' ),
				'OMR' => __( 'Omani rial', '2checkout' ),
				'PAB' => __( 'Panamanian balboa', '2checkout' ),
				'PEN' => __( 'Sol', '2checkout' ),
				'PGK' => __( 'Papua New Guinean kina', '2checkout' ),
				'PHP' => __( 'Philippine peso', '2checkout' ),
				'PKR' => __( 'Pakistani rupee', '2checkout' ),
				'PLN' => __( 'Polish z&#x142;oty', '2checkout' ),
				'PRB' => __( 'Transnistrian ruble', '2checkout' ),
				'PYG' => __( 'Paraguayan guaran&iacute;', '2checkout' ),
				'QAR' => __( 'Qatari riyal', '2checkout' ),
				'RON' => __( 'Romanian leu', '2checkout' ),
				'RSD' => __( 'Serbian dinar', '2checkout' ),
				'RUB' => __( 'Russian ruble', '2checkout' ),
				'RWF' => __( 'Rwandan franc', '2checkout' ),
				'SAR' => __( 'Saudi riyal', '2checkout' ),
				'SBD' => __( 'Solomon Islands dollar', '2checkout' ),
				'SCR' => __( 'Seychellois rupee', '2checkout' ),
				'SDG' => __( 'Sudanese pound', '2checkout' ),
				'SEK' => __( 'Swedish krona', '2checkout' ),
				'SGD' => __( 'Singapore dollar', '2checkout' ),
				'SHP' => __( 'Saint Helena pound', '2checkout' ),
				'SLL' => __( 'Sierra Leonean leone', '2checkout' ),
				'SOS' => __( 'Somali shilling', '2checkout' ),
				'SRD' => __( 'Surinamese dollar', '2checkout' ),
				'SSP' => __( 'South Sudanese pound', '2checkout' ),
				'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', '2checkout' ),
				'SYP' => __( 'Syrian pound', '2checkout' ),
				'SZL' => __( 'Swazi lilangeni', '2checkout' ),
				'THB' => __( 'Thai baht', '2checkout' ),
				'TJS' => __( 'Tajikistani somoni', '2checkout' ),
				'TMT' => __( 'Turkmenistan manat', '2checkout' ),
				'TND' => __( 'Tunisian dinar', '2checkout' ),
				'TOP' => __( 'Tongan pa&#x2bb;anga', '2checkout' ),
				'TRY' => __( 'Turkish lira', '2checkout' ),
				'TTD' => __( 'Trinidad and Tobago dollar', '2checkout' ),
				'TWD' => __( 'New Taiwan dollar', '2checkout' ),
				'TZS' => __( 'Tanzanian shilling', '2checkout' ),
				'UAH' => __( 'Ukrainian hryvnia', '2checkout' ),
				'UGX' => __( 'Ugandan shilling', '2checkout' ),
				'USD' => __( 'United States (US) dollar', '2checkout' ),
				'UYU' => __( 'Uruguayan peso', '2checkout' ),
				'UZS' => __( 'Uzbekistani som', '2checkout' ),
				'VEF' => __( 'Venezuelan bol&iacute;var', '2checkout' ),
				'VES' => __( 'Bol&iacute;var soberano', '2checkout' ),
				'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', '2checkout' ),
				'VUV' => __( 'Vanuatu vatu', '2checkout' ),
				'WST' => __( 'Samoan t&#x101;l&#x101;', '2checkout' ),
				'XAF' => __( 'Central African CFA franc', '2checkout' ),
				'XCD' => __( 'East Caribbean dollar', '2checkout' ),
				'XOF' => __( 'West African CFA franc', '2checkout' ),
				'XPF' => __( 'CFP franc', '2checkout' ),
				'YER' => __( 'Yemeni rial', '2checkout' ),
				'ZAR' => __( 'South African rand', '2checkout' ),
				'ZMW' => __( 'Zambian kwacha', '2checkout' ),
			)
		)
	);

	return $currencies;
}
endif;

/**
 * Get Transaction data by reference
 *
 * @author Jakaria Istauk <jakariamd35@gmail.com>
 * @since 1.0
 */
if( !function_exists( 'twoco_get_transaction' ) ) :
function twoco_get_transaction( $reference ) {

	$api_url 		= "https://api.2checkout.com/rest/6.0/orders";
	$api_url		= "{$api_url}/{$reference}/";
	$merchant_id	= '203572367';
	$secret_key		= 'ZcI~4GLOPBv|@^g=_(?z';
	$gm_date		= gmdate( 'Y-m-d H:i:s' );
	$hash_data		= strlen( $merchant_id ) . $merchant_id . strlen( $gm_date ) . $gm_date;
	$hash       	= hash_hmac( 'md5', $hash_data, $secret_key );
	$headers = [
				'X-Avangate-Authentication'	=> "code='{$merchant_id}' date='{$gm_date}' hash='{$hash}'",
			];

	$request	= [ 'headers' => $headers, 'timeout' => 10 ];

	$_response 	= wp_remote_get( $api_url, $request );
	$response 	= json_decode( wp_remote_retrieve_body( $_response ) );

	return $response;
}
endif;


/**
 * Generate HTML for input fields
 *
 * @author Jakaria Istauk <jakariamd35@gmail.com>
 * @since 1.0
 */
if( !function_exists( 'twoco_generate_html' ) ) :
function twoco_generate_html( $fields ) {

	if( !is_array( $fields ) || count( $fields ) < 1 ) return;
	$html = "<form class='twoco-custom-fields-form'><div class='twoco-custom-fields'>";

	// twoco_pri( $fields );

	foreach ( $fields as $key => $field ) {
		$html .= "<div class='twoco-input-group'>";
		if ( isset( $field['label'] ) && $field['label'] != '' ) {
			$html .= "<label>{$field['label']}</label>";
		}
		$name = isset( $field['id'] ) ? $field['id'] : str_replace(' ', '_', strtolower( $field['label'] ) );
		$html .= "<input type='{$field['type']}' name='{$name}'>";
		$html .= "</div>";
	}

	$html .= "</div></form>"; 
	return $html;
}
endif;
	
/**  
* Insert transaction meta field for a transaction.  
* @param   int    $transaction_id  transaction ID.  
* @param   string $meta_key      The meta key to retrieve.  
* @param   string|array $value        		the data saved to database
* @return  int  the meta ID.  
* @access  public  
* @author  Jakaria Istauk <jakariamd35@gmail.com>
* @since   1.0 
*/
function add_transaction_meta( $transaction_id = 0, $meta_key = '', $value = '' ) {
 	return add_metadata( 'transaction', $transaction_id, $meta_key, $value );
 }

/**  
* Update transaction meta field for a transaction.  
* @param   int    		$transaction_id 	transaction ID.  
* @param   string 		$meta_key      		The meta key to retrieve.  
* @param   string|array $value        		the data saved to database 
* @return  bool 
* @access  public  
* @author  Jakaria Istauk <jakariamd35@gmail.com>
* @since   1.0 
*/
function update_transaction_meta( $transaction_id = 0, $meta_key = '', $value = '' ) {	
	return update_metadata( 'transaction', $transaction_id, $meta_key, $value );
}

/**  
* Retrieve transaction meta field for a transaction.  
* @param   int    $transaction_id  transaction ID.  
* @param   string $meta_key      The meta key to retrieve.  
* @param   bool   $single        Whether to return a single value.  
* @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.  
* @access  public  
* @author  Jakaria Istauk <jakariamd35@gmail.com>
* @since   1.0 
*/
function get_transaction_meta( $transaction_id = 0, $meta_key = '', $single = false ) {
	return get_metadata( 'transaction', $transaction_id, $meta_key, $single );
}

/**  
* Delete transaction meta field for a meta key.  
* @param   int    $transaction_id  transaction ID.  
* @param   string $meta_key      The meta key to retrieve.  
* @return  bool
* @access  public  
* @author  Jakaria Istauk <jakariamd35@gmail.com>
* @since   1.0 
*/
function delete_transaction_meta( $transaction_id = 0, $meta_key = '' ) {
	return delete_metadata( 'transaction', $transaction_id, $meta_key );
}