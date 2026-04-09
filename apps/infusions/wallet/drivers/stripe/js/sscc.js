/**
 *
 * @type {{ccfunction: wwcc.ccfunction, amountAdjust: (function(): boolean)}}
 */
let wwcc = {
    /**
     *
     * @returns {boolean}
     */
    amountAdjust: function () {
        $('#amount').on('input propertychange paste', function (e) {
            $('#stripe_amount').val($(this).val());
        });
        return true;
    },

    /**
     * Processing the credit card
     *
     * @param payment_id
     * @param publishable_key
     * @param options
     * @param display_amount_field
     * @param order_amount
     * @param stripe_token
     * @param fusion_token
     */
    ccfunction: function (payment_id, publishable_key, options, display_amount_field, order_amount, stripe_token, fusion_token) {

        if (display_amount_field) {
            wwcc.amountAdjust();
        }
        const config = {
            publishable_key: publishable_key,
        };
        // let transaction = JSON.parse(options);
        let transaction = options;
        let stripe = Stripe(config.publishable_key);
        let elements = stripe.elements();
        let card = elements.create("card");
        card.mount("#card-element");
        let form = document.getElementById('stripePaymentForm');
        let errors = document.getElementById('button-text');

        form.addEventListener('submit', function (evt) {

            evt.preventDefault();

            if (display_amount_field && !order_amount) {
                let c_val = $('#paypal_amount').val();
                if (!c_val || c_val === 0) {
                    $.alert({
                        icon: 'fa fa-warning',
                        title: 'Invalid amount entered',
                        content: 'Please fill in the amount field.',
                        closeIcon: true,
                        type: 'dark',
                        typeAnimated: true,
                    });

                    return false;
                }
            }

            let data = {
                'PaymentMethod': 'creditcard',
                'PaymentTitle': 'Payment via Stripe',
                'PaymentDesc': 'Transaction Payment via Stripe',
                'PaymentID': payment_id
            };

            if (transaction["display_amount_field"]) {
                transaction["custom_amount"] = $('#wallet_custom_price').val();
            }

            let formData = $('#stripePaymentForm').serialize() + '&' + $.param(data);
            let $submit_button = $('#submit');

            $.ajax({
                url: `${INFUSIONS}wallet/api/?api=checkout`, // log transactions
                data: formData,
                dataType: 'json',
                method: 'post',
                beforeSend: function() {
                    $submit_button.attr('disabled', true);
                    $submit_button.addClass('disabled');
                },
                success: function (result) {
                    if (result.status === 'OK') {
                        stripe.createPaymentMethod('card', card).then(function (cardresult) {
                            // console.log("<---CARD RESULT---->");
                            // console.log(cardresult);
                            if (cardresult.error) {
                                errors.textContent = cardresult.error.message;
                                $submit_button.attr('disabled', false);
                                $submit_button.removeClass('disabled');
                                return;
                            }

                            errors.textContent = "";

                            // why never do any transaction? Doing transaction itself in create_payment file.
                            let req = $.ajax({
                                url: `${INFUSIONS}wallet/drivers/stripe/create_payment.php`,
                                method: 'POST',
                                contentType: 'application/json',
                                dataType: 'json',
                                data: JSON.stringify({
                                    payment_method_id: cardresult.paymentMethod.id,
                                    transaction_ref: result.response,
                                    transaction: transaction,
                                }),
                                beforeSend: function (ev) {
                                    $submit_button.attr('disabled', true);
                                    $submit_button.addClass('disabled');
                                    $submit_button.html('Processing Credit Card <img class=\"loader-icon\" alt=\"\" src=\"' + IMAGES + 'loaders/puff.svg\">');
                                },
                                error: function (e) {
                                    $.alert({
                                        icon: 'fa fa-warning',
                                        title: 'Wallet Error',
                                        content: 'Failed to process payment with Stripe payment gateway.',
                                        closeIcon: true,
                                        type: 'dark',
                                        typeAnimated: true,
                                    });
                                    // console.log(e);
                                    // console.log('Stripe file could not be found');
                                }
                            });

                            req.then(function (response) {
                                // Handle Server Response Function
                                handleServerResponse(response);
                            });
                        });

                    } else if (result.status === 'Redirect') {

                        $.confirm({
                            icon: 'fa fa-warning',
                            title: result.data,
                            closeIcon: true,
                            type: 'dark',
                            typeAnimated: true,
                            content: 'Please go to your edit profile and fill your billing information. The details are required for your invoice and transaction records.',
                            buttons: {
                                confirm: {
                                    text: 'Go to Profile Settings',
                                    action: function () {
                                        let document_link = BASEDIR + "edit_profile.php";
                                        if (result.link) {
                                            document_link = result.link;
                                        }
                                        document.location = document_link;
                                    }
                                }
                            }
                        });
                    }
                },
                error: function (e) {

                    $.alert({
                        icon: 'fa fa-warning',
                        title: 'Stripe Error',
                        content: e.responseText + ' Please contact administrator for support.',
                        closeIcon: true,
                        type: 'dark',
                        typeAnimated: true,
                    });
                }
            });


        })

        // handle SCA
        function handleAuthentication(response) {
            stripe.handleCardAction(response.payment_intent_client_secret).then(function (result) {

                if (result.error) {
                    document.getElementById("submit").textContent = result.error.message;
                } else {
                    // this is to handle customer SCA
                    // The card action has been handled
                    // The PaymentIntent can be confirmed again on the server
                    fetch(`${INFUSIONS}wallet/drivers/stripe/confirm_payment.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            payment_intent_id: result.paymentIntent.id, // from handleCardAction
                            token: response.token,
                            transaction_ref: response.transaction_ref,
                            transaction_url: response.transaction_url,
                        })

                    }).then(function (confirmResult) {

                        return confirmResult.json();

                    }).then(function (response) {
                        handleServerResponse(response);
                    });
                }
            });
        }

        function handleServerResponse(response) {
            if (response["error"]) {
                // console.log("STRIPE A");
                document.getElementById("submit").textContent = response.error.message;

            } else if (response["requires_action"]) {
                // console.log("STRIPE B");
                document.getElementById("submit").textContent = "Further verification is required";
                // Use Stripe.js to handle required card action
                handleAuthentication(response);

            } else {
                // console.log("STRIPE C SEND DATA");
                let sendData = {
                    payment_intent_id: response.payment_intent_id,
                    token: response.token,
                    transaction_ref: response.transaction_ref,
                    transaction_url: response.transaction_url,
                };
                // console.log(sendData);
                document.getElementById("submit").textContent = "Payment success! Checking out..";

                // Final Step --
                fetch(`${INFUSIONS}wallet/drivers/stripe/confirm_payment.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(sendData)
                })
                    .then(function (confirmResult) {

                        return confirmResult.json();

                    })
                    .then(function (confirmResult) {

                        // console.log('---FINAL CONFIRMED RESPONSE_RESULT---');
                        // console.log(confirmResult);
                        // we will redirect to the new transaction status page.
                        setTimeout(function (evt) {
                            document.location.href = confirmResult.transaction_url;
                        }, 300)
                    })

            }
        }

    }

};

