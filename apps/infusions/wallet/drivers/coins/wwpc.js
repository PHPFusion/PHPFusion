/**
 * Copyright (c) PHP Fusion Inc
 * @type {{init: wwpc.init, amountAdjust: (function(): boolean)}}
 */
let wwpc = {

    init: function (paymentId, display_amount_field, order_amount, form_id, fusion_token,) {

        if (display_amount_field) {
            wwpc.amountAdjust();
        }

        $(document).on('click', "button#pay_credit", function (e) {

            e.preventDefault();

            if (display_amount_field && !order_amount) {
                let c_val = $('#paypal_amount').val();
                if (!c_val || c_val === 0) {
                    alert('Please fill in the amount field');
                    return false;
                }
            }

            let data = {
                'PaymentMethod': 'Fusion Credits',
                'PaymentDesc': 'Transaction Payment via Fusion Credit',
                'PaymentID': paymentId
            };

            let formData = $('#creditPaymentForm').serialize() + '&' + $.param(data);
            let $credit = $("#pay_credit");

            $.ajax({
                url: `${INFUSIONS}wallet/api/?api=checkout`, // log transactions
                data: formData,
                dataType: 'json', // debug with html
                method: 'POST',
                success: function (result) {
                    // DEBUGGING
                    console.log(result);
                    if (result["status"] === 'OK') {
                        // execute payment.
                        $.ajax({
                            url: `${INFUSIONS}wallet/drivers/coins/payments.php`,
                            data: {
                                'payment_id': result["response"],
                                'form_id': form_id,
                                'fusion_token': fusion_token,
                            },
                            type: 'post',
                            dataType: 'json',
                            beforeSend: function () {
                                $credit.prop('disabled', 'disabled');
                                $credit.html('Counting Fusion Coins <img alt=\"\" src=\"' + this.IMAGES + 'loaders/puff.svg\"/> ');
                            },
                            success: function (response) {

                                if (response["status"] === "OK" && response["form"]) {
                                    document.getElementById("credit_form").innerHTML = response.form;
                                    document.getElementById("credit_form").submit();
                                    // this should redirect... with ajax post, params will be very likely to be interrupted.
                                    // we should hardcode script to redirect.
                                    //console.log(result.form);
                                    // go to checkout and post. this is where we need to validate
                                } else {
                                    //$('#credit-form-container').html(response.form);
                                    $.alert({
                                        "text": "You do not have enough coins to make this purchase. Please top up and try again.",
                                    });
                                    // console.log("Payment failed");
                                }
                            },
                            error:function(e, status, error) {
                                console.log(e.responseText);

                                $.alert({
                                    icon: 'fa fa-warning',
                                    title: 'Wallet Error',
                                    content: 'Failed to process payment with Paypal',
                                    closeIcon: true,
                                    type: 'dark',
                                    typeAnimated: true,
                                });

                                console.log('Cannot fetch credit payment');
                            }
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
                        title: 'Paypal Error',
                        content: e.responseText +' Please contact administrator for support.',
                        closeIcon: true,
                        type: 'dark',
                        typeAnimated: true,
                    });
                }
            });
        });
    },
    amountAdjust: function () {
        $('#amount').on('input propertychange paste', function (e) {
            $('#credit_amount').val($(this).val());
        });
        return true;
    }
};
