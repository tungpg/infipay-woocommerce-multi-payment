<?php
/**
 * Plugin Name: Infipay Woocommerce Multi Payment
 * Plugin URI: 
 * Description: The plugin installs on websites that register with the payment gateway to support the Infipay multi-payment plugin.
 * Version: 1.0.1
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
define( 'INFIPAY_PAYMENT_STRIPE_VERSION', '1.1.0' );

register_activation_hook( INFIPAY_WOOCOMMERCE_MULTI_PAYMENT_PLUGIN_FILE, 'infipay_paypal_plugin_activation' );

global $ifp_options;
$ifp_options = array(
    'tool_server_domain'                            => 'payments.infipay.us',
);

function infipay_woocommerce_multi_payment_plugin_activation() {
	if ( ! current_user_can( 'activate_plugins' ) ) return;
	global $wpdb;
	global $ifp_options;
	
	// Create the required options...
	foreach ( $ifp_options as $name => $val ) {
	    add_option( $name, $val );
	}
	
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
			
			add_action('admin_menu', [$this, 'add_infipay_stripe_paygate_menu']);
		}
		
		function add_infipay_stripe_paygate_menu()
		{
		    add_menu_page('Infipay Multi Payment Settings', 'Infipay Multi Payment', 'manage_options', 'infipay-gateway-stripe', [$this, 'infipay_page_init']);
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
		
		/**
		 * MEcom Stripe Gateway
		 */
		
		function infipay_page_init()
		{
	    ?>
            <h3>Infipay Multi Payment Settings</h3>

	    	<table class="form-table">
    			<tr valign="top">
    				<th scope="row">
    					<label for="tool_server_domain">Tool Server Domain</label>
    				</th>
    				<td>
    					<input name="tool_server_domain" type="text" id="tool_server_domain" value="<?php echo esc_attr( get_option( 'tool_server_domain' ) ); ?>" size="40" class="regular-text"/>
    				</td>
    			</tr>
			</table>
			
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"/>
			</p>
            <?php
        }
	}

	new InfipayPayShield();
}

add_action( 'admin_enqueue_scripts', 'infipay_payment_shield_enqueue_admin_script' );
function infipay_payment_shield_enqueue_admin_script() {
	wp_enqueue_script( 'infipay-woocommerce-multi-payment-admin', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array(), time() );
}