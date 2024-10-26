<?php
/**
 * All Shortcode related functions
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
 * @subpackage Shortcode
 * @author codexpert <hello@codexpert.io>
 */
class Shortcode extends Base {

    public $plugin;

    /**
     * Constructor function
     */
    public function __construct( $plugin ) {
        $this->plugin   = $plugin;
        $this->slug     = $this->plugin['TextDomain'];
        $this->name     = $this->plugin['Name'];
        $this->version  = $this->plugin['Version'];
    }

    /**
     * print payment related form or buttons
     * @author Jakaria Istauk <jakariamd35@gmail.com>
     * @since 1.0
     */
    public function payment_button( $args ) {
        return twoco_get_template( 'payment-section', 'views', $args );
    }
}