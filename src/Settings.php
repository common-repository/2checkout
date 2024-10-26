<?php
/**
 * All settings related functions
 */
namespace codexpert\twocheckout;
use codexpert\plugin\Base;

/**
 * @package Plugin
 * @subpackage Settings
 * @author codexpert <hello@codexpert.io>
 */
class Settings extends Base {

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
	
	public function init_menu() {
		
		$settings = [
			'id'            => "{$this->slug}-settings",
			'label'         => __( 'Settings', 'wooffiliate' ),
			'title'         => __( '2Checkout', 'wooffiliate' ),
			'header'        => __( '2Checkout', 'wooffiliate' ),
			'parent'     	=> $this->slug,
			'priority'   	=> 11,
			'capability' 	=> 'manage_options',
			// 'icon'       => 'dashicons-wordpress',
			'position'   => 25,
			'sections'      => [
				'2checkout_config'	=> [
					'id'        => '2checkout_config',
					'label'     => __( 'Configuration', '2checkout' ),
					'icon'      => 'dashicons-admin-tools',
					'color'		=> '#4c3f93',
					'sticky'	=> true,
					'fields'    => [
						'sandbox' => [
							'id'		=> 'sandbox',
							'label'		=> __( 'Test Mode', '2checkout' ),
							'type'		=> 'checkbox',
							'desc'		=> __( 'Enable test mode', '2checkout' ),
						],
						'merchant_id' => [
							'id'		=> 'merchant_id',
							'label'		=> __( 'Merchant ID', '2checkout' ),
							'type'		=> 'number',
							'desc'		=> __( 'Your 2Checkout Merchant ID.', '2checkout' ),
						],
					]
				],
			],
		];

		new \codexpert\plugin\Settings( $settings );
	}
}