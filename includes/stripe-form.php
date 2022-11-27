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
		<!-- <div id="payment-element"></div> -->
		<fieldset class="infipay-credit-card-form wc-payment-form" style="background:transparent;">
			<div class="form-row form-row-wide">
				<label for="infipay-stripe-card-element"><?php esc_html_e( 'Card Number', 'payment-gateway-stripe-and-woocommerce-integration' ); ?> <span class="required">*</span></label>
				<div class="stripe-card-group">
					<div id="infipay-stripe-card-element" class="infipay-stripe-elements-field">
					<!-- a Stripe Element will be inserted here. -->
					</div>
					<i class="infipay-stripe-card-brand"></i>
				</div>
			</div>
			<div class="form-row form-row-first">
				<label for="infipay-stripe-exp-element"><?php esc_html_e( 'Expiry Date', 'payment-gateway-stripe-and-woocommerce-integration' ); ?> <span class="required">*</span></label>

				<div id="infipay-stripe-exp-element" class="infipay-stripe-elements-field">
				<!-- a Stripe Element will be inserted here. -->
				</div>
			</div>
			<div class="form-row form-row-last">
				<label for="infipay-stripe-cvc-element"><?php esc_html_e( 'Card Code (CVC)','payment-gateway-stripe-and-woocommerce-integration' ); ?> <span class="required">*</span></label>
				<div id="infipay-stripe-cvc-element" class="infipay-stripe-elements-field">
				<!-- a Stripe Element will be inserted here. -->
				</div>
			</div>
		</fieldset>
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