<?php
/**
 * All public facing functions
 */
namespace codexpert\twocheckout;
use codexpert\plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author codexpert <hello@codexpert.io>
 */
class Front extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'TWOCO_DEBUG' ) && TWOCO_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", TWOCO ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", TWOCO ), [ 'jquery' ], $this->version, true );
		
		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'config'	=> [
				'sandbox'		=> twoco_get_option( '2checkout_config', 'sandbox' ) == 'on' ? 1 : 0,
				'merchant_id'	=> twoco_get_option( '2checkout_config', 'merchant_id', false ),
			]
		];
		wp_localize_script( $this->slug, 'TWOCO', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function head()	{}

	/**
	 * Insert a transaction details to twoco_transaction table
	 * @author Jakaria Istauk <jakariamd35@gmail.com>
	 * @since 1.0
	 */
	public function insert_transaction(){
		
		if ( !isset( $_GET['refno'] ) ) return;

		if ( !isset( $_GET['form_id'] ) ) return;

		global $wpdb;
		$transaction_table 	 = $wpdb->prefix . '2co_transactions';
		$custom_fields_table = $wpdb->prefix . '2co_custom_fields';

		$refno 		= sanitize_text_field( $_GET['refno'] );
		$get_tranx 	= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$transaction_table} WHERE `refno` = %d", $refno ) );

		if ( count( $get_tranx ) > 0 ) return;

		$transaction = twoco_get_transaction( $refno );

		$transaction_id = $wpdb->insert(
			$transaction_table,
			[
				'form_id'		=> (int) sanitize_text_field( $_GET['form_id'] ),
				'refno'			=> $transaction->RefNo,
				'payer_name'	=> $transaction->BillingDetails->FirstName . ' ' . $transaction->BillingDetails->LastName,
				'payer_email'	=> $transaction->BillingDetails->Email,
				'amount'		=> $transaction->NetPrice,
				'currency'		=> $transaction->Currency,
				'status'		=> $transaction->Status,
				'time'			=> time(),
			],
			[
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
			]
		);

		if ( isset( $_GET['custom_fields'] ) && $transaction_id ) {
			$custom_fields =  $_GET['custom_fields'];
			$custom_fields =  stripcslashes( $custom_fields );
			$custom_fields =  json_decode( $custom_fields, true );
			$custom_fields =  serialize( $custom_fields );

			update_transaction_meta( $wpdb->insert_id, 'custom_fields', $custom_fields );
		}
	}
}