
<html>

<head>
<link rel='dns-prefetch' href='//checkout.airwallex.com' />

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
	src="<?php echo esc_url(plugin_dir_url(__DIR__))?>../assets/js/airwallex-checkout.js?a=<?php echo time();?>"
	id='airwallex-local-js-js'></script>

   <style>
     #cardNumber,
     #expiry,
     #cvc {
       border: 1px solid #612fff;
       border-radius: 5px;
       padding: 5px 10px;
       width: 100%;
       box-shadow: #612fff 0px 0px 0px 1px;
       display: flex;
       justify-content: center;
       align-items: center;
       margin-top: 5px;
     }
     
     /**
 * style.css
 * Airwallex Payment Demo - Static HTML.  Created by Josie Ku.
 *
 * This file provides styling to the demo site.  This is not required for the integration.
 */

body {
  font-family: 'AxLLCircular';
  padding: 15px 25px;
}

span#code {
  font-family: monospace;
  background-color: lightgrey;
  font-size: 16px;
}

div#links {
  display: flex;
  flex-direction: column;
}

button {
  margin-top: 15px;
  font-family: 'AxLLCircular';
  padding: 5px 10px;
  font-weight: 500;
  cursor: pointer;
  background-color: #612fff;
  color: #fff;
  outline: none;
  border: none;
  border-radius: 5px;
  min-width: 40px;
  min-height: 40px;
}

button:disabled {
  opacity: 0.75;
}

p#error {
  background: #ffebee;
  border-radius: 5px;
  padding: 5px 10px;
  max-width: 400px;
  display: none;
}
p#success {
  background: lightgreen;
  border-radius: 5px;
  padding: 5px 10px;
  max-width: 400px;
  display: none;
}

p#author {
  position: absolute;
  bottom: 0;
}

.field-container {
  margin: 15px 0px;
}

div.instruction-container {
  padding: 10px 50px;
  text-align: left;
}

span#code {
  font-family: monospace;
  background-color: lightgrey;
  font-size: 16px;
  margin: 0px 5px;
}

p#bullet {
  font-weight: bold;
}

@font-face {
  src: url('https://checkout.airwallex.com/fonts/CircularXXWeb/CircularXXWeb-Bold.woff');
  font-family: 'AxLLCircular';
  weight: 'bold';
}
@font-face {
  src: url('https://checkout.airwallex.com/fonts/CircularXXWeb/CircularXXWeb-Regular.woff2');
  font-family: 'AxLLCircular';
  weight: '400';
}
   </style>
	
</head>

<body>

    <!-- Hide all elements before they are all mounted -->
    <div id="element" style="display: none;">
       <div class="field-container">
         <div>Card number</div>
         <div id="cardNumber"></div>
         <p id="cardNumber-error" style="color: red;"></p>
       </div>
       <div class="field-container">
         <div>Expiry</div>
         <div id="expiry"></div>
         <p id="expiry-error" style="color: red;"></p>
       </div>
       <div class="field-container">
         <div>Cvc</div>
         <div id="cvc"></div>
         <p id="cvc-error" style="color: red;"></p>
       </div>
    </div>
	<p id="error"></p>
	
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

            try {
                Airwallex.init({
                    env: 'prod',
                    origin: window.location.origin, // Setup your event target to receive the browser events message
                });

             	// Create split card elements
                const cardNumber = Airwallex.createElement("cardNumber");
                const expiry = Airwallex.createElement("expiry");
                const cvc = Airwallex.createElement("cvc");

                // Mount split card elements
                cardNumber.mount('cardNumber'); // Injects iframe into the Card Number Element container
                expiry.mount('expiry'); // Injects iframe into the Expiry Element container
                cvc.mount('cvc'); // Injects iframe into the CVC Element container                
            } catch (error) {
                document.getElementById("error").style.display = "block"; // Example: show error
                document.getElementById("error").innerHTML = error.message; // Example: set error message
            	console.error("There was an error", error);
            }

            // Set up local variable to check all elements are mounted
            const elementsReady = {
              cardNumber: false,
              expiry: false,
              cvc: false
            };
            // STEP #7: Add an event listener to ensure the element is mounted
            const cardNumberElement = document.getElementById("cardNumber");
            const expiryElement = document.getElementById("expiry");
            const cvcElement = document.getElementById("cvc");
            const domElementArray = [cardNumberElement, expiryElement, cvcElement];
      
            domElementArray.forEach((element) => {
              element.addEventListener("onReady", (event) => {
                /*
              ... Handle event
               */
                const { type } = event.detail;
                if (elementsReady.hasOwnProperty(type)) {
                  elementsReady[type] = true; // Set element ready state
                }
      
                if (!Object.values(elementsReady).includes(false)) {
                  document.getElementById("element").style.display = "block"; // Example: show element when mounted
                }
              });
            });            

            // Set up local variable to validate element inputs
            const elementsCompleted = {
              cardNumber: false,
              expiry: false,
              cvc: false
            };
      
            domElementArray.forEach((element) => {
              element.addEventListener("onChange", (event) => {
                /*
             ... Handle event
               */
                const { type, complete } = event.detail;
                if (elementsCompleted.hasOwnProperty(type)) {
                  elementsCompleted[type] = complete; // Set element completion state
                }
      
                // Check if all elements are completed, and set submit button disabled state
                const allElementsCompleted = !Object.values(
                  elementsCompleted
                ).includes(false);
                document.getElementById("submit").disabled = !allElementsCompleted;
              });
            });
      
            // STEP #9: Add an event listener to get input focus status
            domElementArray.forEach((element) => {
              element.addEventListener("onFocus", (event) => {
                // Customize your input focus style by listen onFocus event
                const element = document.getElementById(type + "-error");
                if (element) {
                  element.innerHTML = ""; // Example: clear input error message
                }
              });
            });

            // STEP #10: Add an event listener to show input error message when finish typing
            domElementArray.forEach((element) => {
              element.addEventListener("onBlur", (event) => {
                const { error, type } = event.detail;
                const element = document.getElementById(type + "-error");
                if (element && error) {
                  element.innerHTML = error.message || JSON.stringify(error); // Example: set input error message
                }
              });
            });
            // STEP #9: Add an event listener to handle events when there is an error
            domElementArray.forEach((element) => {
              element.addEventListener("onBlur", (event) => {
                /*
               ... Handle event on error
             */
                const { error } = event.detail;
//                 document.getElementById("error").style.display = "block"; // Example: show error block
//                 document.getElementById("error").innerHTML = error.message; // Example: set error message
                console.error("There was an error", event.detail.error);
              });
            });
            
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
