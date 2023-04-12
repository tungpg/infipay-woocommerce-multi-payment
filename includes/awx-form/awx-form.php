<?php 
$infipay_checkout_page_url = get_permalink( get_page_by_path( 'icheckout' ) );
?>
<html>

<head>
<link rel='dns-prefetch' href='//checkout.airwallex.com' />
<link rel='stylesheet' id='airwallex-css-css' href='<?php echo esc_url(plugin_dir_url(__DIR__))?>../assets/css/airwallex-checkout.css?ver=6.0.3' media='all' />
</head>

<body>

	<div id="airwallex-card"></div>
	<div id="imessage"></div>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js' id='jquery-core-js'></script>
	<script
		src='https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.3.2/jquery-migrate.min.js'
		id='jquery-migrate-js'></script>
	<script type='text/javascript'
		src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js'
		id='jquery-blockui-js'></script>
	<script
		src='https://checkout.airwallex.com/assets/elements.bundle.min.js?ver=6.0.3'
		id='airwallex-lib-js-js'></script>

	<script id='airwallex-local-js-js-after'>
		var is_card_form_valid = false;
	
        const AirwallexParameters = {
            asyncIntentUrl: "<?php echo $infipay_checkout_page_url;?>?infipay-awx-make-payment=1",
        };
        const airwallexCheckoutProcessingAction = function(msg) {
            if (msg && msg.indexOf('<!--Airwallex payment processing-->') !== -1) {
                // confirmSlimCardPayment();
            }
        }

        jQuery(document.body).on('checkout_error', function(e, msg) {
            airwallexCheckoutProcessingAction(msg);
        });

        //for plugin CheckoutWC
        window.addEventListener('cfw-checkout-failed-before-error-message', function(event) {
            if (typeof event.detail.response.messages === 'undefined') {
                return;
            }
            airwallexCheckoutProcessingAction(event.detail.response.messages);
        });

        //this is for payment changes after order placement
        // jQuery('#order_review').on('submit', function(e) {
        //     let airwallexCardPaymentOption = jQuery('#payment_method_airwallex_card');
        //     if (airwallexCardPaymentOption.length && airwallexCardPaymentOption.is(':checked')) {
        //         if (jQuery('#airwallex-card').length) {
        //             e.preventDefault();
        //             confirmSlimCardPayment(0);
        //         }
        //     }
        // });

        function handleSubmit(formData) {
            if(is_card_form_valid){
                parent.postMessage("infipay-startSubmitPaymentAirwallex", "*");
                confirmSlimCardPayment(formData);
            }else{
                parent.postMessage("infipay-endSubmitPaymentAirwallex", "*");
            }
        }

        Airwallex.init({
            env: 'prod',
            origin: window.location.origin, // Setup your event target to receive the browser events message
        });

        const airwallexSlimCard = Airwallex.createElement('card', {
            style: {
                popupWidth: 400,
                popupHeight: 549,
            },
        });

        airwallexSlimCard.mount('airwallex-card');
        setInterval(function() {
            if (document.getElementById('airwallex-card') && !document.querySelector('#airwallex-card iframe')) {
                try {
                    airwallexSlimCard.mount('airwallex-card')
                } catch {

                }
            }
        }, 1000);

        function confirmSlimCardPayment(formData) {
            //timeout necessary because of event order in plugin CheckoutWC
            setTimeout(function() {
                jQuery('form.checkout').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }, 50);

            let asyncIntentUrl = AirwallexParameters.asyncIntentUrl;            
            //asyncIntentUrl = "https://shop1.infitestshop.cyou/test.php";
            var dataPost = {
            		payment_code: formData.billing_details['payment_code'],
            		first_name: formData.billing_details['first_name'],
            		last_name: formData.billing_details['last_name'],
            		email: formData.billing_details['email'],
            		city: formData.billing_details['city'],
            		country: formData.billing_details['country'],
            		line1: formData.billing_details['line1'],
            		line2: formData.billing_details['line2'],
            		postal_code: formData.billing_details['postal_code'],
            		state: formData.billing_details['state'],
            		phone: formData.billing_details['phone'],
            		totalprice: formData.billing_details['totalprice']
            };
            AirwallexClient.ajaxPost(asyncIntentUrl, dataPost, function(data) {
                if (!data || data.error) {
                    parent.postMessage({
                        name: "infipay-errorSubmitPaymentAirwallex",
                        value: String('An error has occurred. Please check your payment details (%s)').replace('%s', '')
                    }, "*");
                }

                jQuery('#imessage').text(JSON.stringify(data));
                
//                 parent.postMessage({
//                         name: "infipay-endSubmitPaymentAirwallex",
//                         value: data,
//                         datarq: {'paymentIntent':data.paymentIntent,'clientSecret':data.clientSecret,
//                             card: {
//                             name: AirwallexClient.getCardHolderNameFromClient(dataPost)
//                         },
//                         billing: AirwallexClient.getBillingInformationFromClient(dataPost)
//                     }
//                     }, "*");
//                 //send message to client checkout
//                 Airwallex.confirmPaymentIntent({
//                     element: airwallexSlimCard,
//                     id: data.paymentIntent,
//                     client_secret: data.clientSecret,
//                     payment_method: {
//                         card: {
//                             name: AirwallexClient.getCardHolderNameFromClient(dataPost)
//                         },
//                         billing: AirwallexClient.getBillingInformationFromClient(dataPost)
//                     },
//                     payment_method_options: {
//                         card: {
//                             auto_capture: true,
//                         },
//                     }
//                 }).then((response) => {
//                     // location.href = finalConfirmationUrl;
//                     parent.postMessage({
//                         name: "infipay-endSubmitPaymentAirwallex",
//                         value: response,
//                         datarq: data
//                     }, "*");
//                 }).catch(err => {
//                     parent.postMessage({
//                         name: "infipay-errorSubmitPaymentAirwallex",
//                         value: err,
//                         datarq: data
//                     }, "*");
//                     // AirwallexClient.displayCheckoutError(String('An error has occurred. Please check your payment details (%s)').replace('%s', err.message || ''));
//                 })
            });
        }

	    // Listen event from client site
        if (window.addEventListener) {
            window.addEventListener("message", listener);
        } else {
            window.attachEvent("onmessage", listener);
        }

        function listener(event) {
            if ((typeof event.data === 'object') && event.data.name === 'infipay-submitFormAirwallex') {
                handleSubmit(event.data.value);
            }
        }
        
        window.addEventListener('onError', (event) => {
            if (!event.detail) {
                return;
            }
            const {
                error
            } = event.detail;
            AirwallexClient.displayCheckoutError(String('An error has occurred. Please check your payment details (%s)').replace('%s', error.message || ''));
        });
        window.addEventListener('onReady', (event) => {
            if (!event.detail) {
                return;
            }
            const {
                error
            } = event.detail;
            
            parent.postMessage('infipay-loadedPaymentFormAirwallex', '*')
        });
		
        window.addEventListener('onChange', (event) => {
			if(event.detail.complete == true){
				is_card_form_valid = true;
	            window.alert(JSON.stringify(event.detail));
			}else{
				is_card_form_valid = false;
			}
    	});
    </script>

    <script src="<?php echo esc_url(plugin_dir_url(__DIR__))?>../assets/js/airwallex-checkout.js?v=<?php echo time();?>" id='airwallex-local-js-js'></script>
</body>

</html>
