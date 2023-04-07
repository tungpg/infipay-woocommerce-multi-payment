
<html>

<head>
<link rel='dns-prefetch' href='//checkout.airwallex.com' />
<link rel='stylesheet' id='airwallex-css-css' href='<?php echo esc_url(plugin_dir_url(__DIR__))?>assets/css/airwallex-checkout.css?ver=6.0.3' media='all' />

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
<script
	src="<?php echo esc_url(plugin_dir_url(__DIR__))?>assets/js/airwallex-checkout.js?a=<?php echo time();?>"
	id='airwallex-local-js-js'></script>

</head>

<body>

    <!-- Add empty containers for each card input element  -->
    <div style={containerStyle}>
        <div>Card number</div>
        <div id="cardNumber"></div>
      </div>
      <div style={containerStyle}>
        <div>Expiry</div>
        <div id="expiry"></div>
      </div>
      <div style={containerStyle}>
        <div>CVC</div>
        <div id="cvc"></div>
    </div>
	
	

	<script id='airwallex-local-js-js-after'>
            const AirwallexParameters = {
                asyncIntentUrl: "<?=WooCommerce::instance()->api_request_url('checkout_async_intent')?>",
                confirmationUrl: "<?=WooCommerce::instance()->api_request_url('airwallex_payment_confirmation')?>"
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
                parent.postMessage("infipay-startSubmitPaymentAirwallex", "*");
                confirmSlimCardPayment(0, formData);
            }


            Airwallex.init({
                env: 'prod',
                origin: window.location.origin, // Setup your event target to receive the browser events message
            });

         	// Create split card elements
            const cardNumber = Airwallex.createElement('cardNumber');
            const expiry = Airwallex.createElement('expiry');
            const cvc = Airwallex.createElement('cvc');

            // Mount split card elements
            cardNumber.mount('cardNumber'); // Injects iframe into the Card Number Element container
            expiry.mount('expiry'); // Injects iframe into the Expiry Element container
            cvc.mount('cvc'); // Injects iframe into the CVC Element container
            
//             setInterval(function() {
//                 if (document.getElementById('airwallex-card') && !document.querySelector('#airwallex-card iframe')) {
//                     try {
//                         airwallexSlimCard.mount('airwallex-card')
//                     } catch {

//                     }
//                 }
//             }, 1000);

            function confirmSlimCardPayment(orderId, formData) {
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
                if (orderId) {
                    asyncIntentUrl += (asyncIntentUrl.indexOf('?') !== -1 ? '&' : '?') + 'airwallexOrderId=' + orderId;
                }
                var dataPost = formData.billing_details;
                AirwallexClient.ajaxPost(asyncIntentUrl, dataPost, function(data) {
                    if (!data || data.error) {
                        parent.postMessage({
                            name: "infipay-errorSubmitPaymentAirwallex",
                            value: String('An error has occurred. Please check your payment details (%s)').replace('%s', '')
                        }, "*");
                    }
                    parent.postMessage({
                            name: "infipay-endSubmitPaymentAirwallex",
                            value: data,
                            datarq: {'paymentIntent':data.paymentIntent,'clientSecret':data.clientSecret,
                                card: {
                                name: AirwallexClient.getCardHolderNameFromClient(dataPost)
                            },
                            billing: AirwallexClient.getBillingInformationFromClient(dataPost)
                        }
                        }, "*");
                    //send message to client checkout
                    Airwallex.confirmPaymentIntent({
                        element: cardNumber,
                        id: data.paymentIntent,
                        client_secret: data.clientSecret,
                        payment_method: {
                            card: {
                                name: AirwallexClient.getCardHolderNameFromClient(dataPost)
                            },
                            billing: AirwallexClient.getBillingInformationFromClient(dataPost)
                        },
                        payment_method_options: {
                            card: {
                                auto_capture: true,
                            },
                        }
                    }).then((response) => {
                        // location.href = finalConfirmationUrl;
                        parent.postMessage({
                            name: "infipay-endSubmitPaymentAirwallex",
                            value: response,
                            datarq: data
                        }, "*");
                    }).catch(err => {
                        parent.postMessage({
                            name: "infipay-errorSubmitPaymentAirwallex",
                            value: err,
                            datarq: data
                        }, "*");
                        // AirwallexClient.displayCheckoutError(String('An error has occurred. Please check your payment details (%s)').replace('%s', err.message || ''));
                    })

                    /*
                    const finalConfirmationUrl = AirwallexParameters.confirmationUrl + 'order_id=' + data.orderId + '&intent_id=' + data.paymentIntent;

                    Airwallex.confirmPaymentIntent({
                        element: airwallexSlimCard,
                        id: data.paymentIntent,
                        client_secret: data.clientSecret,
                        payment_method: {
                            card: {
                                name: AirwallexClient.getCardHolderName()
                            },
                            billing: AirwallexClient.getBillingInformation()
                        },
                        payment_method_options: {
                            card: {
                                auto_capture: true,
                            },
                        }
                    }).then((response) => {
                        location.href = finalConfirmationUrl;
                    }).catch(err => {
                        console.log(err);
                        jQuery('form.checkout').unblock();
                        AirwallexClient.displayCheckoutError(String('An error has occurred. Please check your payment details (%s)').replace('%s', err.message || ''));
                    })

                    */
                });

            }

            function listener(event) {
                "object" == typeof event.data && "infipay-submitFormAirwallex" === event.data.name && handleSubmit(event.data.value)
            }
            window.addEventListener ? window.addEventListener("message", listener) : window.attachEvent("onmessage", listener);
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
        </script>

</body>

</html>
