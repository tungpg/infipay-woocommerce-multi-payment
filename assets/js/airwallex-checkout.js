const AirwallexClient = {
    getCustomerInformation: function (fieldId, parameterName) {
        const $inputField = jQuery('#' + fieldId);
        if ($inputField.length) {
            return $inputField.val();
        } else if (typeof AirwallexParameters[parameterName] !== 'undefined') {
            return AirwallexParameters[parameterName];
        } else {
            return '';
        }
    },
    getCardHolderName: function () {
        return String(AirwallexClient.getCustomerInformation('billing_first_name', 'billingFirstName') + ' ' + AirwallexClient.getCustomerInformation('billing_last_name', 'billingLastName')).trim();
    },
    getCardHolderNameFromClient: function (billingdata) {
        return String(billingdata.first_name + ' ' + billingdata.last_name).trim();
    },
    getBillingInformation: function () {
        return {
            address: {
                city: AirwallexClient.getCustomerInformation('billing_city', 'billingCity'),
                country_code: AirwallexClient.getCustomerInformation('billing_country', 'billingCountry'),
                postcode: AirwallexClient.getCustomerInformation('billing_postcode', 'billingPostcode'),
                state: AirwallexClient.getCustomerInformation('billing_state', 'billingState'),
                street: String(AirwallexClient.getCustomerInformation('billing_address_1', 'billingAddress1') + ' ' + AirwallexClient.getCustomerInformation('billing_address_2', 'billingAddress2')),
            },
            first_name: AirwallexClient.getCustomerInformation('billing_first_name', 'billingFirstName'),
            last_name: AirwallexClient.getCustomerInformation('billing_last_name', 'billingLastName'),
            email: AirwallexClient.getCustomerInformation('billing_email', 'billingEmail'),
        }
    },
    getBillingInformationFromClient: function (billingdata) {
        return {
            address: {
                city: billingdata.city,
                country_code: billingdata.country,
                postcode: billingdata.postal_code,
                state: billingdata.state,
                street: String(billingdata.line1 + ' ' + billingdata.line2),
            },
            first_name: billingdata.first_name,
            last_name: billingdata.last_name,
            email: billingdata.email,
        }
    },
    ajaxGet: function (url, callback) {
        const xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                try {
                    var data = JSON.parse(xmlhttp.responseText);
                } catch (err) {
                    console.log(err.message + " in " + xmlhttp.responseText);
                    return;
                }
                callback(data);
            }
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    },
    ajaxPost: function (url, datapost, callback) {
//        alert('hehe--' + datapost);
//        alert('payment_code=' + datapost['payment_code']);
		
		var formData = {
	      payment_code: 'code123',
	      first_name: 'tung',
	      last_name: 'pham',
	    };
	alert(typeof datapost);
	    
	    formData = Object.entries(datapost);
		
		$.ajax({
            url: url,
            data: formData,
            type: 'post',
            dataType: 'json',
            encode: true,
            success: function (data) {
				alert(JSON.stringify(data));
				callback(data);
                $('#target').html(data.msg);
            }
        });


        /*const formData = new FormData();
        Object.keys(datapost).forEach(key => formData.append(key, datapost[key]));

        const xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                try {
                    var data = JSON.parse(xmlhttp.responseText);
                } catch (err) {
                    console.log(err.message + " in " + xmlhttp.responseText);
                    return;
                }
                callback(data);
            }
        };
        xmlhttp.open("POST", url, true);
        //xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xmlhttp.send(formData);*/
    },
    displayCheckoutError: function (msg) {
        const checkout_form = jQuery('form.checkout');
        jQuery('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
        checkout_form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"><ul class="woocommerce-error"><li>' + msg + '</li></ul></div>');
        checkout_form.removeClass('processing').unblock();
        checkout_form.find('.input-text, select, input:checkbox').trigger('validate').blur();
        var scrollElement = jQuery('.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout');

        if (!scrollElement.length) {
            scrollElement = checkout_form;
        }
        if (typeof jQuery.scroll_to_notices === 'function') {
            jQuery.scroll_to_notices(scrollElement);
        }
    }
};