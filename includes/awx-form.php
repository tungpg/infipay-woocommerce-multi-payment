<?php 
$shop_domain = $_SERVER['HTTP_HOST'];
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo esc_url(plugin_dir_url(__DIR__))?>assets/css/stripe-payment-form.css?v=<?= time() ?>" />
</head>
<body>
	<form id="payment-form">
		<div class="wc_payment_method payment_method_airwallex_card">
        	<input id="payment_method_airwallex_card" type="radio" class="input-radio" name="payment_method" value="airwallex_card" checked="checked" data-order_button_text="">
        	<label for="payment_method_airwallex_card">
        	Credit Card <img src="https://<?=$shop_domain;?>/wp-content/plugins/airwallex-online-payments-gateway/assets/images/airwallex_cc_icon.svg" alt="Credit Card">	</label>
        	<div class="payment_box payment_method_airwallex_card" style="">
        		<p>Pay via AWX</p>
        		<div id="airwallex-card">
        			<iframe frameborder="0" allowtransparency="true" importance="high" scrolling="no" allowpaymentrequest="true" style="transition: height 0.35s ease 0s; height: 23.9844px; width: 100%; display: block;" src="https://checkout-demo.airwallex.com/#/elements/card?options={&quot;style&quot;:{&quot;popupWidth&quot;:400,&quot;popupHeight&quot;:549},&quot;origin&quot;:&quot;https://<?=$shop_domain;?>&quot;}&amp;lang=undefined" name="Airwallex card element iframe" title="Airwallex card element iframe"/>
        		</div>
        	</div>
        </div>
	</form>
	<script>
		<?php 
		$setting_key = 'woocommerce_eh_stripe_pay_settings';
		$settings = get_option($setting_key, false);
		
		$pk = ($settings['eh_stripe_mode'] === 'test') ? $settings['eh_stripe_test_publishable_key'] : $settings['eh_stripe_live_publishable_key'];
		?>
		window.stripePublicKey = "<?= $pk ?>";
		window.infipayProxySite = "<?= get_site_url(null, '/checkout', 'https')?>";
	</script>
	<script src="https://js.stripe.com/v3/"></script>
	<script src="<?php echo esc_url(plugin_dir_url(__DIR__))?>assets/js/checkout.js?v=<?= time()?>"></script>
</body>
</html>