<?php
/**
 * All admin facing functions
 */
namespace codexpert\twocheckout;
use codexpert\plugin\Base;
use codexpert\plugin\Table;
use codexpert\plugin\Metabox;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author codexpert <hello@codexpert.io>
 */
class Admin extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->server	= $this->plugin['server'];
		$this->version	= $this->plugin['Version'];
	}

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( '2checkout', false, TWOCO_DIR . '/languages/' );
	}

	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {

		/**
		 * Create database tables
		 */
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/**
		 * Transaction Table
		 * @author Jakaria Istauk <jakariamd35@gmail.com>
		 */
		$charset_collate = $wpdb->get_charset_collate();

		$transaction_sql = "CREATE TABLE `{$wpdb->prefix}2co_transactions` (
		    id int(11) NOT NULL AUTO_INCREMENT,
		    form_id int(20) NOT NULL,
		    refno varchar(16) NOT NULL,
		    payer_name varchar(255) NOT NULL,
		    payer_email varchar(255) NOT NULL,
		    amount varchar(50) NOT NULL,
		    currency varchar(3) NOT NULL,
		    status varchar(16) NOT NULL,
		    time int(10) NOT NULL,
		    UNIQUE KEY id (id)
		) $charset_collate;";

		dbDelta( $transaction_sql );

		/**
		 * transaction meta Table
		 * @author Jakaria Istauk <jakariamd35@gmail.com>
		 */

		$transactionmeta_sql = "CREATE TABLE `{$wpdb->prefix}2co_transactionmeta` (
		    meta_id int(20) NOT NULL AUTO_INCREMENT,
		    transaction_id int(20) NOT NULL,
		    meta_key varchar(255) NOT NULL,
		    meta_value varchar(255) NOT NULL,
		    PRIMARY KEY (meta_id),
		    KEY transaction_id (transaction_id),
		    KEY meta_key (meta_key)
		) $charset_collate;";

		dbDelta( $transactionmeta_sql );

		/**
		 * Schedule an event to sync help docs
		 */
		if ( !wp_next_scheduled ( '2checkout_daily' )) {
		    wp_schedule_event( time(), 'daily', '2checkout_daily' );
		}

		if( !get_option( '2checkout_version' ) ){
			update_option( '2checkout_version', $this->version );
		}
		
		if( !get_option( '2checkout_install_time' ) ){
			update_option( '2checkout_install_time', time() );
		}


		$this->daily();

	}

	/**
	 * Uninstaller. Runs once when the plugin in deactivated.
	 *
	 * @since 1.0
	 */
	public function uninstall() {
		/**
		 * Remove scheduled hooks
		 */
		wp_clear_scheduled_hook( '2checkout_daily' );
	}

	/**
	 * Daily events
	 */
	public function daily() {
		/**
		 * Sync blog posts from https://codexpert.io
		 *
		 * @since 1.0
		 */
	    $_posts = 'https://codexpert.io/wp-json/wp/v2/posts/';
	    if( !is_wp_error( $_posts_data = wp_remote_get( $_posts ) ) ) {
	        update_option( 'codexpert-blog-json', json_decode( $_posts_data['body'], true ) );
	    }

		/**
		 * Sync docs from https://help.codexpert.io
		 *
		 * @since 1.0
		 */
	    // $_docs = "https://help.codexpert.io/wp-json/wp/v2/docs/?parent={$this->plugin['doc_id']}&per_page=20";
	    // if( !is_wp_error( $_docs_data = wp_remote_get( $_docs ) ) ) {
	    //     update_option( '2checkout-docs-json', json_decode( $_docs_data['body'], true ) );
	    // }
	    
	}

	/**
	 * Adds a widget in /wp-admin/index.php page
	 *
	 * @since 1.0
	 */
	public function dashboard_widget() {
		wp_add_dashboard_widget( 'cx-overview', __( 'Latest From Our Blog', '2checkout' ), [ $this, 'callback_dashboard_widget' ] );

		// Move our widget to top.
		global $wp_meta_boxes;

		$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		$ours = [
			'cx-overview' => $dashboard['cx-overview'],
		];

		$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $ours, $dashboard );
	}

	/**
	 * Call back for dashboard widget in /wp-admin/
	 *
	 * @see dashboard_widget()
	 *
	 * @since 1.0
	 */
	public function callback_dashboard_widget() {
		$posts = get_option( 'codexpert-blog-json', [] );
		
		if( count( $posts ) > 0 ) :
		
		$posts = array_slice( $posts, 0, 5 );
		$utm = [ 'utm_source' => 'dashboard', 'utm_medium' => 'metabox', 'utm_campaign' => 'blog-post' ];

		echo '<ul id="cx-posts-wrapper">';
		
		foreach ( $posts as $post ) {

			$post_link = add_query_arg( $utm, $post['link'] );
			echo "
			<li>
				<a href='{$post_link}' target='_blank'><span aria-hidden='true' class='cx-post-title-icon dashicons dashicons-external'></span> <span class='cx-post-title'>{$post['title']['rendered']}</span></a>
				" . wpautop( wp_trim_words( $post['content']['rendered'], 10 ) ) . "
			</li>";
		}
		
		echo '</ul>';
		endif; // count( $posts ) > 0

		$_links = apply_filters( 'cx-overview_links', [
			'products'	=> [
				'url'		=> add_query_arg( $utm, 'https://codexpert.io/products/' ),
				'label'		=> __( 'Products', '2checkout' ),
				'target'	=> '_blank',
			],
			'hire'	=> [
				'url'		=> add_query_arg( $utm, 'https://codexpert.io/hire/' ),
				'label'		=> __( 'Hire Us', '2checkout' ),
				'target'	=> '_blank',
			],
		] );

		$footer_links = [];
		foreach ( $_links as $id => $link ) {
			$_has_icon = ( $link['target'] == '_blank' ) ? '<span class="screen-reader-text">' . __( '(opens in a new tab)', '2checkout' ) . '</span><span aria-hidden="true" class="dashicons dashicons-external"></span>' : '';

			$footer_links[] = "<a href='{$link['url']}' target='{$link['target']}'>{$link['label']}{$_has_icon}</a>";
		}

		echo '<p class="community-events-footer">' . implode( ' | ', $footer_links ) . '</p>';
	}

	/**
	 * adds custom meta box for 2checkout options
	 *
	 */
	public function add_meta_boxes() {
		$metabox = [
			'id'            => $this->slug,
			'label'         => __( 'Configuration', '2checkout' ),
			'post_type'  	=> [ 'twoco' ],
			'context'    	=> 'normal',
			'sections'      => [
				'twoco_payment_type'	=> [
					'id'        => 'twoco_payment_type',
					'label'     => __( 'Payment Type', '2checkout' ),
					'icon'      => 'dashicons-admin-tools',
					'color'		=> '#4c3f93',
					'sticky'	=> true,
					'fields'    => [
						'payment_type' => [
							'id'     	=> 'payment_type',
							'label'     => __( 'Payment Type', '2checkout' ),
							'type'      => 'select',
							'desc'      => __( 'Type of a payment', '2checkout' ),
							'options'   => [
								'one_time'  	=> __( 'One Time', '2checkout' ),
								'subscription'  => __( 'Subscription', '2checkout' ),
							],
							'default'   => 'one_time',
						],
						'subscription'	=> [
							'id'		=> 'subscription',
							'label'		=> __( 'Subscription Period', '2checkout' ),
							'type'		=> 'group',
							'desc'		=> __( 'Subscription Length', '2checkout' ),
							'items'		=> [								
								'period_time' 	=> [
									'id'			=> 'period_time',
									'label'     	=> __( 'Period', '2checkout' ),
									'type'      	=>  'number',
									'placeholder'	=> __( 'Input Period', '2checkout' ),
									'default'		=> 1
								],
								'period_unit' 	=> [
									'id'		=> 'period_unit',
									'label'     => __( 'Period unit', '2checkout' ),
									'type'      => 'select',
									'options'	=> [
										"Minute" 	=> __( 'Minutes', '2checkout' ),
										"Hour"		=> __( 'Hours', '2checkout' ),
										"Day"		=> __( 'Days', '2checkout' ),
										"Month" 	=> __( 'Months', '2checkout' ),
										"Year" 		=> __( 'Years', '2checkout' ),
									],								    
									'default'	=> "Minute",
								],
							],
							'condition'=>[
								'key' 		=> 'payment_type',
								'compare' 	=> '==',
								'value'		=> 'subscription'
							]
						],
					]
				],
				'twoco_amount'	=> [
					'id'        => 'twoco_amount',
					'label'     => __( 'Amount', '2checkout' ),
					'icon'      => 'dashicons-money-alt',
					'color'		=> '#0c9bd3',
					'fields'	=> [						
						'amount_type' => [
							'id'		=> 'amount_type',
							'label'     => __( 'Amount Type', '2checkout' ),
							'type'      => 'select',
							'desc'      => __( 'Type of a amount', '2checkout' ),
							'options'   => [
								'fixed'  		=> __( 'Fixed', '2checkout' ),
								'flexible'  	=> __( 'Flexible', '2checkout' )
							],
							'default'   => 'fixed',
						],						
						'amount' => [
							'id'		=> 'amount',
							'label'     => __( 'Amount', '2checkout' ),
							'type'      => 'number',
							'default'   => 1,
							'condition'=>[
								'key' 		=> 'amount_type',
								'compare' 	=> '==',
								'value'		=> 'fixed'
							]
						],
						'min_amount' => [
							'id'      => 'min_amount',
							'label'     => __( 'Minimum Amount', '2checkout' ),
							'type'      => 'number',
							'default'   => 1,
							'condition'=>[
								'key' 		=> 'amount_type',
								'compare' 	=> '==',
								'value'		=> 'flexible'
							]
						],
						'max_amount' => [
							'id'		=> 'max_amount',
							'label'     => __( 'Maximum Amount', '2checkout' ),
							'type'      => 'number',
							'default'   => 1,
							'condition'=>[
								'key' 		=> 'amount_type',
								'compare' 	=> '==',
								'value'		=> 'flexible'
							]
						],
					],
				],
				'twoco_others'	=> [
					'id'        => 'twoco_others',
					'label'     => __( 'Others', '2checkout' ),
					'icon'      => 'dashicons-admin-generic',
					'color'		=> '#c36',
					'fields'	=> [
						'currency' => [
							'id'		=> 'currency',
							'label'     => __( 'Currency', '2checkout' ),
							'type'      => 'select',
							'chosen'	=> true,
							'options'   => twoco_get_currencies(),
							'default'   => 'USD',
						],
						'btn_label' => [
							'id'		=> 'btn_label',
							'label'     => __( 'Button Label', '2checkout' ),
							'type'      => 'text',
							'desc'		=> sprintf( __( 'This %s will replace with your given amount. Use the placeholder when you use fixed amount.', '2checkout' ), '%%amount%%' ),
							'default'   => 'Pay With 2checkout',
						],
						'btn_color' => [
							'id'      	=> 'btn_color',
							'label'     => __( 'Button Color', '2checkout' ),
							'type'      => 'color',
							'default'   => '#2271b1',
						],
						'style' => [
							'id'      	=> 'style',
							'label'     => __( 'Payment Form Style', '2checkout' ),
							'type'      => 'select',
							'options'   => [
								'popup' 	=> __( 'Popup', '2checkout' ),
								'embedded' 	=> __( 'Embedded', '2checkout' )
							],
						],
						'enable_custom_field' => [
							'id'     	=> 'enable_custom_field',
							'label'     => __( 'Enable Custom Field', '2checkout' ),
							'type'      => 'checkbox',
						],
						'repeater' => [
							'id'      	=> 'repeater',
							'label'     => __( 'Custom Fields', '2checkout' ),
							'type'      => 'repeater',
							'items'   => [
									[
										'id'      	=> 'label',
										'label'     => __( 'Label', '2checkout' ),
										'type'      => 'text',						
										'placeholder' => 'Label',
									],
									[
										'id'      	=> 'type',
										'label'     => __( 'Type', '2checkout' ),
										'type'      => 'select',
										'options'   => [
											'text' 	=> __( 'Text', '2checkout' ),
											'email' => __( 'Email', '2checkout' ),
											'number' => __( 'Number', '2checkout' ),
										],
									],
							],
							'condition'=>[
								'key' 		=> 'enable_custom_field',
								'compare' 	=> 'checked',
							],
						],
						'ridirect' => [
							'id'      	=> 'ridirect',
							'label'     => __( 'Ridirect Url', '2checkout' ),
							'type'      => 'divider',
						],
						'enable_redirect' => [
							'id'     	=> 'enable_redirect',
							'label'     => __( 'Enable Redirect', '2checkout' ),
							'type'      => 'checkbox',
						],
						'success_page' => [
							'id'      	=> 'success_page',
							'label'     => __( 'Success Page', '2checkout' ),
							'type'      => 'text',						
							'placeholder'      => 'http://redirect.com',						
							'condition'=>[
								'key' 		=> 'enable_redirect',
								'compare' 	=> 'checked',
							]
						],
						'failed_page' => [
							'id'      	=> 'failed_page',
							'label'     => __( 'Failed Page', '2checkout' ),
							'type'      => 'text',							
							'placeholder'      => 'http://redirect.com',							
							'condition'=>[
								'key' 		=> 'enable_redirect',
								'compare' 	=> 'checked',
							]
						],
					]
				],
			]
		];
		new Metabox( $metabox );
	}

	/**
	 * generate shortcode
	 * @author Jakaria Istauk <jakariamd35@gmail.com>
	 * @since 1.0
	 */
	public function meta_boxes() { 

	    add_meta_box(
            'twoco_payment_shortcode',
            'Shortcode',
            [ $this, 'meta_box_payment_shortcode' ],
            'twoco',
            'side',
            'high'
        );
	}

	/**
	 * generate shortcode
	 * @author Jakaria Istauk <jakariamd35@gmail.com>
	 * @since 1.0
	 */
	public function meta_box_payment_shortcode( $post ) {
		$post_id 	= $post->ID;
		$screen 	= get_current_screen();

		if ( $screen->action == 'add' ) {
			_e( "A shortcode will generate here", '2checkout' );
		}
		else {
			?>
			<div class="twoco-shortcode">
    			<input id="twoco-sc-generator" type="text" name="shortcode" value='[2checkout id="<?php echo $post_id; ?>"]' readonly>
    			<p title="copy" id="twoco-sc-copy-btn" class="button"><span class="dashicons dashicons-admin-page"></span></p title="copy">
		    	<p id="twoco-sc-copied-notice"> <?php _e( 'Copied', '2checkout' ) ?> </p>
		    </div>
			<?php
		}
	}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'TWOCO_DEBUG' ) && TWOCO_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", TWOCO ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", TWOCO ), [ 'jquery' ], $this->version, true );
	}

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'admin.php' );

		$new_links = [
			'settings'	=> sprintf( '<a href="%1$s">' . __( 'Settings', '2checkout' ) . '</a>', add_query_arg( 'page', "{$this->slug}-settings", $this->admin_url ) )
		];
		
		return array_merge( $new_links, $links );
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		
		if ( $this->plugin['basename'] === $plugin_file ) {
			$plugin_meta['help'] = '<a href="https://help.codexpert.io/" target="_blank" class="cx-help">' . __( 'Help', '2checkout' ) . '</a>';
		}

		return $plugin_meta;
	}

	public function footer_text( $text ) {
		if( get_current_screen()->parent_base != $this->slug ) return $text;

		return sprintf( __( 'If you like <strong>%1$s</strong>, please <a href="%2$s" target="_blank">leave us a %3$s rating</a> on WordPress.org! It\'d motivate and inspire us to make the plugin even better!', '2checkout' ), $this->name, "https://wordpress.org/support/plugin/{$this->slug}/reviews/?filter=5#new-post", '⭐⭐⭐⭐⭐' );
	}

	public function add_menu()	{
		add_menu_page( __( '2Checkout', '2checkout' ), __( '2Checkout', '2checkout' ), 'manage_options', $this->slug, '', 'dashicons-chart-line', 25 );
		add_submenu_page( $this->slug, __( 'Transactions', '2checkout' ), __( 'Transactions', '2checkout' ), 'manage_options', "{$this->slug}-transactions", [ $this, 'callback_transactions' ] );
	}

	/**
	 * Register Post Type Payment form
	 * @author Jakaria Istauk <jakariamd35@gmail.com>
	 * @since 1.0
	 */
	public function register_custom_posts() {

		/**
		 * Post Type: Payment Forms.
		 */
		$labels = [
			'name'			=> __( 'Payment Forms', '2checkout' ),
			'singular_name'	=> __( 'Payment Form', '2checkout' ),
		];

		$args = [
			'label'					=> __( 'Payment Forms', '2checkout' ),
			'labels'				=> $labels,
			'description'			=> '',
			'public'				=> false,
			'publicly_queryable'	=> false,
			'show_ui'				=> true,
			'show_in_rest'			=> true,
			'rest_base'				=> '',
			'rest_controller_class'	=> 'WP_REST_Posts_Controller',
			'has_archive'			=> false,
			'show_in_menu'			=> '2checkout',
			'menu_position'			=> 100,
			'show_in_nav_menus'		=> true,
			'delete_with_user'		=> false,
			'exclude_from_search'	=> true,
			'capability_type'		=> 'page',
			'map_meta_cap'			=> true,
			'hierarchical'			=> false,
			'rewrite'				=> [ 'slug' => 'twoco', 'with_front' => true ],
			'query_var'				=> true,
			'supports'				=> [ 'title' ],
		];

		register_post_type( 'twoco', $args );
	}

	/**
	 * Display Transactions table
	 * @author Jakaria Istauk <jakariamd35@gmail.com>
	 * @since 1.0
	 */
	public function callback_transactions() {
		echo twoco_get_template( 'transactions' );
	}

	/**
	 * Extend wp_meta function
	 * @author Jakaria Istauk <jakariamd35@gmail.com>
	 * @since 1.0
	 */
	public function twoco_register_metadata_table()	{
		global $wpdb;
		$wpdb->transactionmeta = "{$wpdb->prefix}2co_transactionmeta";
	}

}