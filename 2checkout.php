<?php
/**
 * Plugin Name: WP Super Pay
 * Description: 2Checkout integration for WordPress. Collect donation and payments without any e-Commerce prograns!
 * Plugin URI: https://codexpert.io/product/2checkout/?utm_campaign=wporg
 * Author: Codexpert
 * Author URI: https://codexpert.io/?utm_campaign=wporg
 * Version: 1.0
 * Text Domain: 2checkout
 * Domain Path: /languages
 *
 * 2Checkout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * 2Checkout is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace codexpert\twocheckout;
use codexpert\plugin\Survey;
use codexpert\plugin\Notice;
use codexpert\plugin\Deactivator;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author codexpert <hello@codexpert.io>
 */
final class Plugin {
	
	public static $_instance;

	public function __construct() {
		$this->include();
		$this->define();
		$this->hook();
	}

	/**
	 * Includes files
	 */
	public function include() {
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
	}

	/**
	 * Define variables and constants
	 */
	public function define() {
		// constants
		define( 'TWOCO', __FILE__ );
		define( 'TWOCO_DIR', dirname( TWOCO ) );
		define( 'TWOCO_DEBUG', apply_filters( '2checkout_debug', true ) );

		// plugin data
		$this->plugin				= get_plugin_data( TWOCO );
		$this->plugin['basename']	= plugin_basename( TWOCO );
		$this->plugin['file']		= TWOCO;
		$this->plugin['server']		= apply_filters( '2checkout_server', 'https://my.codexpert.io' );
		$this->plugin['min_php']	= '5.6';
		$this->plugin['min_wp']		= '4.0';
		$this->plugin['doc_id']		= 1960;
		$this->plugin['depends']	= [];
	}

	/**
	 * Hooks
	 */
	public function hook() {

		// if( is_admin() ) :
			
			/**
			 * Admin facing hooks
			 *
			 * To add an action, use $admin->action()
			 * To apply a filter, use $admin->filter()
			 */
			$admin = new Admin( $this->plugin );
			$admin->activate( 'install' );
			$admin->deactivate( 'uninstall' );
			$admin->action( 'plugins_loaded', 'i18n' );
			$admin->action( 'wp_dashboard_setup', 'dashboard_widget', 99 );
			$admin->action( 'admin_init', 'add_meta_boxes' );
			$admin->action( 'add_meta_boxes', 'meta_boxes' );
			$admin->action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$admin->action( '2checkout_daily', 'daily' );
			$admin->filter( "plugin_action_links_{$this->plugin['basename']}", 'action_links' );
			$admin->filter( 'plugin_row_meta', 'plugin_row_meta', 10, 2 );
			$admin->action( 'admin_footer_text', 'footer_text' );
			$admin->action( 'admin_menu', 'add_menu' );
			$admin->action( 'init', 'register_custom_posts' );
			$admin->action( 'init', 'twoco_register_metadata_table' );

			/**
			 * Settings related hooks
			 *
			 * To add an action, use $settings->action()
			 * To apply a filter, use $settings->filter()
			 */
			$settings = new Settings( $this->plugin );
			$settings->action( 'plugins_loaded', 'init_menu' );

			// Product related classes
			$survey				= new Survey( $this->plugin );
			$notice				= new Notice( $this->plugin );
			$deactivator		= new Deactivator( $this->plugin );

		// else : // !is_admin() ?

			/**
			 * Front facing hooks
			 *
			 * To add an action, use $front->action()
			 * To apply a filter, use $front->filter()
			 */
			$front = new Front( $this->plugin );
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );
			$front->action( 'wp_head', 'head' );
			$front->action( 'init', 'insert_transaction' );

			/**
			 * Shortcode hooks
			 *
			 * To enable a shortcode, use $shortcode->register()
			 */
			$shortcode = new Shortcode( $this->plugin );
			$shortcode->register( '2checkout', 'payment_button' );

		// endif;
	}

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	Public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Plugin::instance();