<?php
/**
 * Plugin Name: Infipay Woocommerce Multi Payment
 * Plugin URI: 
 * Description: An eCommerce toolkit that helps you sell anything. Beautifully.
 * Version: 1.0
 * Author: Infipay
 * Author URI: https//infipay.us
 * Text Domain: infipay-woocommerce-multi-payment
 *
 */

define( 'INFIPAY_WOOCOMMERCE_MULTI_PAYMENT_PLUGIN_FILE', __FILE__ );
define( 'INFIPAY_PAYMENT_STRIPE_VERSION', '1.0' );

register_activation_hook( INFIPAY_WOOCOMMERCE_MULTI_PAYMENT_PLUGIN_FILE, 'infipay_paypal_plugin_activation' );

function infipay_paypal_plugin_activation() {
	if ( ! current_user_can( 'activate_plugins' ) ) return;
	global $wpdb;
    	
	// Delete and recreate checkout page for infipay
	$page = get_page_by_path( 'checkout' , OBJECT );
	if ( isset($page) ) {
		wp_delete_post( $page->ID, true );
	}
	
	$current_user = wp_get_current_user();
	$page_param = array(
		'post_title'  => __( 'iCheckout' ),
		'post_slug'   => 'checkout',
		'post_status' => 'publish',
		'post_author' => $current_user->ID,
		'post_type'   => 'page',
	);
	$page = wp_insert_post( $page_param );
	
	// Delete and recreate checkout2 page for normal checkout
	$page2 = get_page_by_path( 'checkout2' , OBJECT );
	if ( isset($page2) ) {
	    wp_delete_post( $page2->ID, true );
	}
	
	$page_param2 = array(
	    'post_title'  => __( 'Checkout' ),
	    'post_content'  => __( '[woocommerce_checkout]' ),	    
	    'post_slug'   => 'checkout2',
	    'post_status' => 'publish',
	    'post_author' => $current_user->ID,
	    'post_type'   => 'page',
	);
	$page2 = wp_insert_post( $page_param2 );
}

add_filter( 'page_template', 'infipay_drt_reserve_page_template', 99);

function infipay_drt_reserve_page_template($page_template) {
    if ( is_page( 'checkout' ) ) {
        $page_template = dirname( __FILE__ ) . '/includes/checkout.php';
    } else if(is_page( 'thank-you' )) {
        $page_template = dirname( __FILE__ ) . '/includes/thank-you.php';
    }
    return $page_template;
}


if ( ! class_exists( 'InfipayPayShield' ) ) {
	class InfipayPayShield {
		public $plugin_slug;
        public $version;
        public $cache_key;
        public $cache_allowed;
		public $_req_url;

		public function __construct() {
			if ( ! is_admin() ) return;
			if( ! function_exists('get_plugin_data') ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( __FILE__ );
			$this->plugin_slug   = plugin_basename( __DIR__ );
			$this->version       = $plugin_data['Version'];
			$this->cache_key     = 'infipay_payment_shield_update_checker';
			$this->cache_allowed = true;
			$this->_req_url = 'https://payment.infipay.us/index.php?r=site%2Fcheck-version';

			add_filter( 'plugins_api', array($this, 'info'), 20, 3 );
			add_filter( 'site_transient_update_plugins', array($this, 'update') );
			add_action( 'upgrader_process_complete', array($this, 'purge'), 10, 2 );

			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			add_action( 'wp_ajax_infipay_payment_shield_get_update', array($this, 'ajax_clear_update_info') );
		}

		public function isPluginPage() {
            return strpos($_SERVER['REQUEST_URI'], 'wp-admin/plugins.php') !== false;
        }

		public function request() {
			$remote = get_transient( $this->cache_key );
			// $remote = false;
			if ( false === $remote || (!$this->cache_allowed && $this->isPluginPage()) ) {
				$remote = wp_remote_get(
                    $this->_req_url,
                    [
                        'timeout' => 30,
                        'headers' => [
                            'Accept' => 'application/json'
                        ]
                    ]
                );

				if (is_wp_error( $remote ) || 200 !== wp_remote_retrieve_response_code( $remote ) || empty( wp_remote_retrieve_body( $remote ) )) {
					return false;
				}
				set_transient( $this->cache_key, $remote, DAY_IN_SECONDS );
			}
			$remote = json_decode( wp_remote_retrieve_body( $remote ) );
			return $remote;
		}

		function info($res, $action, $args) {
			if ( 'plugin_information' !== $action ) {
				return $res;
			}

			// do nothing if it is not our plugin
			if ( $this->plugin_slug !== $args->slug ) {
				return $res;
			}

			// get updates
			$remote = $this->request();

			$res = new stdClass();
			$res->name           = $remote->name;
            $res->slug           = $remote->slug;
            $res->version        = $remote->version;
            $res->tested         = $remote->tested;
            $res->requires       = $remote->requires;
            $res->author         = $remote->author;
            $res->author_profile = $remote->author_profile;
            $res->download_link  = $remote->download_url;
            $res->trunk          = $remote->download_url;
            $res->requires_php   = $remote->requires_php;
            $res->last_updated   = $remote->last_updated;

			$res->sections = [
				'description'  => $remote->sections->description,
				'installation' => $remote->sections->installation,
				'changelog'    => $remote->sections->changelog
			];
			if ( ! empty( $remote->banners ) ) {
				$res->banners = [
					'low'  => $remote->banners->low,
					'high' => $remote->banners->high
				];
			}

			return $res;
		}

		public function update($transient) {
			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$remote = $this->request();

			if ( $remote && version_compare( $this->version, $remote->version, '<' ) ) {
				$res              = new stdClass();
				$res->slug        = $this->plugin_slug;
				$res->plugin      = plugin_basename( __FILE__ ); // misha-update-plugin/misha-update-plugin.php
				$res->new_version = $remote->version;
				$res->tested      = $remote->tested;
				$res->package     = $remote->download_url;

				$transient->response[ $res->plugin ] = $res;
			}

			return $transient;
		}

		public function purge($upgrader_object, $options) {
			if ( $this->cache_allowed && 'update' === $options['action'] && 'plugin' === $options['type'] ) {
				// just clean the cache when new plugin version is installed
				delete_transient( $this->cache_key );
			}
		}

		public function plugin_row_meta( $plugin_meta, $plugin_file) {
			if ( 'infipay-woocommerce-multi-payment/infipay-woocommerce-multi-payment.php' !== $plugin_file ) {
				return $plugin_meta;
			}

			$row_meta = array(
				'infipay-get-update'    => '<a class="infipay-woocommerce-multi-payment-ajax-link" href="' . get_admin_url(null, 'admin-ajax.php?action=infipay_payment_shield_get_update&security='.wp_create_nonce( "infipay" )) . '" aria-label="' . esc_attr__( 'Manual get Update Info', 'infipay' ) . '">' . esc_html__( 'Get Update Info', 'infipay' ) . '</a>',
			);
			return array_merge( $plugin_meta, $row_meta );
		}

		public function ajax_clear_update_info() {
			check_ajax_referer( 'infipay', 'security' );
			delete_transient($this->cache_key);
			$checkData = get_transient($this->cache_key);
			wp_send_json(array(
				'status' => 'success',
				'body' => $checkData
			));
		}
	}

	new InfipayPayShield();
}

add_action( 'admin_enqueue_scripts', 'infipay_payment_shield_enqueue_admin_script' );
function infipay_payment_shield_enqueue_admin_script() {
	wp_enqueue_script( 'infipay-woocommerce-multi-payment-admin', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array(), time() );
}