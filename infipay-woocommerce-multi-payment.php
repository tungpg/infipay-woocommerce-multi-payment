<?php
/**
 * Plugin Name: Infipay Woocommerce Multi Payment
 * Plugin URI: 
 * Description: The plugin installs on websites that register with the payment gateway to support the Infipay multi-payment plugin.
 * Version: 1.0.0
 * Author: Infipay
 * Author URI: https//infipay.us
 * Text Domain: infipay-woocommerce-multi-payment
 *
 */

require plugin_dir_path(__FILE__) . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/tungpg/infipay-woocommerce-multi-payment/',
    __FILE__,
    'infipay-woocommerce-multi-payment'
    );

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');

define( 'INFIPAY_WOOCOMMERCE_MULTI_PAYMENT_PLUGIN_FILE', __FILE__ );
define( 'INFIPAY_PAYMENT_STRIPE_VERSION', '1.0.0' );

register_activation_hook( INFIPAY_WOOCOMMERCE_MULTI_PAYMENT_PLUGIN_FILE, 'infipay_paypal_plugin_activation' );

function infipay_paypal_plugin_activation() {
	if ( ! current_user_can( 'activate_plugins' ) ) return;
	global $wpdb;

	$page = get_page_by_path( 'icheckout' , OBJECT );
	if ( isset($page) ) {
		wp_delete_post( $page->ID, true );
	}
	
	$current_user = wp_get_current_user();
	$page_param = array(
		'post_title'  => __( 'iCheckout' ),
		'post_slug'   => 'icheckout',
		'post_status' => 'publish',
		'post_author' => $current_user->ID,
		'post_type'   => 'page',
	);
	$page = wp_insert_post( $page_param );
}

add_filter( 'page_template', 'infipay_drt_reserve_page_template', 99);

function infipay_drt_reserve_page_template($page_template) {
    if ( is_page( 'icheckout' ) ) {
        $page_template = dirname( __FILE__ ) . '/includes/checkout.php';
    } else if(is_page( 'thank-you' )) {
        $page_template = dirname( __FILE__ ) . '/includes/thank-you.php';
    }
    return $page_template;
}


if ( ! class_exists( 'InfipayPayShield' ) ) {
	class InfipayPayShield {

		public function __construct() {
			if ( ! is_admin() ) return;
			if( ! function_exists('get_plugin_data') ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			add_action( 'upgrader_process_complete', array($this, 'purge'), 10, 2 );

			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			add_action( 'wp_ajax_infipay_payment_shield_get_update', array($this, 'ajax_clear_update_info') );
		}

		public function isPluginPage() {
            return strpos($_SERVER['REQUEST_URI'], 'wp-admin/plugins.php') !== false;
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