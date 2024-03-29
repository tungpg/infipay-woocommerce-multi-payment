var stripe = Stripe(window.stripePublicKey);
Object.defineProperty(document, "referrer", {
    get: function () {
        return window.infipayProxySite;
    }
});

var elements;
initialize();

document.querySelector("#infipay-payment-form").addEventListener("submit", handleSubmit);

function initialize() {
    elements = stripe.elements();
    var elementStyles = {
        base: {
            iconColor: '#666EE8',
            color: '#31325F',
            fontSize: '15px',
            fontFamily: '"Roboto", Arial, Helvetica, "sans-serif"',
            '::placeholder': {
                color: '#CFD7E0',
            }
        }
    };

    var elementClasses = {
        focus: 'focused',
        empty: 'empty',
        invalid: 'invalid',
    };

    var stripe_card = elements.create( 'cardNumber', { style: elementStyles, classes: elementClasses } );
    var stripe_exp  = elements.create( 'cardExpiry', { style: elementStyles, classes: elementClasses } );
    var stripe_cvc  = elements.create( 'cardCvc', { style: elementStyles, classes: elementClasses } );

    stripe_card.mount( '#infipay-stripe-card-element' );
    stripe_exp.mount( '#infipay-stripe-exp-element' );
    stripe_cvc.mount( '#infipay-stripe-cvc-element' );

    // cardElement.mount("#payment-element");
    window.stripe_card = stripe_card;
    window.stripe_exp = stripe_exp;
    window.stripe_cvc = stripe_cvc;

    stripe_card.on('ready', function () {
        parent.postMessage('infipay-loadedPaymentFormStripe', '*')
    });
    stripe_exp.on('ready', function () {
        parent.postMessage('infipay-loadedPaymentFormStripe', '*')
    });
    stripe_cvc.on('ready', function () {
        parent.postMessage('infipay-loadedPaymentFormStripe', '*')
    });

    stripe_card.on('change', function (event) {
        updateCardBrand( event.brand );
        if (event.complete) {
            parent.postMessage('infipay-paymentFormCompletedStripe', '*')
        } else {
            parent.postMessage('infipay-paymentFormFailStripe', '*')
        }
    });
    stripe_exp.on('change', function (event) {
        if (event.complete) {
            parent.postMessage('infipay-paymentFormCompletedStripe', '*')
        } else {
            parent.postMessage('infipay-paymentFormFailStripe', '*')
        }
    });
    stripe_cvc.on('change', function (event) {
        if (event.complete) {
            parent.postMessage('infipay-paymentFormCompletedStripe', '*')
        } else {
            parent.postMessage('infipay-paymentFormFailStripe', '*')
        }
    });
}


/*function handleSubmit(formData) {
    parent.postMessage('infipay-startSubmitPaymentStripe', '*')
    stripe.confirmCardPayment({
        card: window.stripe_card,
        billing_details: formData.billing_details
    }).then(function (e) {
        if (e.paymentIntent && e.paymentIntent.id) {
            parent.postMessage({
                name: 'infipay-paymentIntentIdStripe',
                value: e.paymentIntent.id
            }, '*');
        } else if (e.error) {
            if (['incomplete_number', 'invalid_number', 'incomplete_expiry', 'invalid_expiry', 'incomplete_cvc', 'invalid_cvc'].includes(e.error.code)) {
                parent.postMessage('infipay-endSubmitPaymentStripe', '*')
            } else {
                parent.postMessage({
                    name: 'infipay-errorSubmitPaymentStripe',
                    value: e.error.message
                }, '*');
            }
        } else {
            parent.postMessage('infipay-endSubmitPaymentStripe', '*')
        }
    })
}*/

function handleSubmit(formData) {
    parent.postMessage('infipay-startSubmitPaymentStripe', '*')
    stripe.createPaymentMethod({
        type: 'card',
        card: window.stripe_card,
        billing_details: formData.billing_details
    }).then(function (e) {
        if (e.paymentMethod && e.paymentMethod.id) {
            parent.postMessage({
                name: 'infipay-paymentMethodIdStripe',
                value: e.paymentMethod.id
            }, '*');
        } else if (e.error) {
            if (['incomplete_number', 'invalid_number', 'incomplete_expiry', 'invalid_expiry', 'incomplete_cvc', 'invalid_cvc'].includes(e.error.code)) {
                parent.postMessage('infipay-endSubmitPaymentStripe', '*')
            } else {
                parent.postMessage({
                    name: 'infipay-errorSubmitPaymentStripe',
                    value: e.error.message
                }, '*');
            }
        } else {
            parent.postMessage('infipay-endSubmitPaymentStripe', '*')
        }
    })
}

function updateCardBrand( brand ) {
    var brandClass = {
        'visa': 'infipay-stripe-visa-brand',
        'mastercard': 'infipay-stripe-mastercard-brand',
        'amex': 'infipay-stripe-amex-brand',
        'discover': 'infipay-stripe-discover-brand',
        'diners': 'infipay-stripe-diners-brand',
        'jcb': 'infipay-stripe-jcb-brand',
        'unknown': 'stripe-credit-card-brand'
    };

    var imageElements = document.getElementsByClassName( 'infipay-stripe-card-brand' ),
        imageClass = 'infipay-stripe-credit-card-brand';

    if ( brand in brandClass ) {
        imageClass = brandClass[ brand ];
    }

    // Remove existing card brand class.
    for (var key in brandClass) {
        if (!brandClass.hasOwnProperty(key)) continue;
        var bClass = brandClass[key];
        
        for (var i=0; i < imageElements.length; i++) {
            var imageElement = imageElements[i];
            imageElement.classList.remove( bClass );
        }
    }

    for (var i=0; i < imageElements.length; i++) {
        var imageElement = imageElements[i];
        imageElement.classList.add( imageClass );
    }
}

// Listen event from client site
if (window.addEventListener) {
    window.addEventListener("message", listener);
} else {
    window.attachEvent("onmessage", listener);
}

function listener(event) {
    if ((typeof event.data === 'object') && event.data.name === 'infipay-submitFormStripe') {
        handleSubmit(event.data.value);
    }
}