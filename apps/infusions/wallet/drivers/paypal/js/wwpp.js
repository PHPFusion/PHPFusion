let wwpp = {

    /**
     *
     * @param paymentId
     * @param display_amount_field
     * @param order_amount
     * @param form_id
     * @param fusion_token
     */
    init: function (paymentId, display_amount_field, order_amount, form_id, fusion_token,) {

        if (display_amount_field) {
            wwpp.amountAdjust();
        }

        $(document).on('click', 'button#pay_Paypal', function (e) {

            e.preventDefault();

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
                'PaymentMethod': 'Paypal',
                'PaymentDesc': 'Transaction Payment via Paypal Account',
                'PaymentID': paymentId
            };

            let formData = $('#paypalPaymentFrm').serialize() + '&' + $.param(data);
            let $paypal = $('#pay_Paypal');

            $.ajax({
                url: `${INFUSIONS}wallet/api/?api=checkout`, // log transactions
                data: formData,
                dataType: 'json', // debug with html
                method: 'post',
                success: function (result) {
                    // console.log(result);
                    if (result.status === 'OK') {
                        $.ajax({
                            url: `${INFUSIONS}wallet/drivers/paypal/payments.php`,
                            data: {
                                'payment_id': result.response,
                                'form_id': form_id,
                                'fusion_token': fusion_token,
                            },
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function () {
                                $paypal.prop('disabled', 'disabled');
                                $paypal.html('Redirecting to Paypal <img class=\"loader-icon\" alt=\"\" src=\"' + IMAGES + 'loaders/puff.svg\">');
                            },
                            success: function (result) {
                                if (result["status"] === 'OK' && result.form) {
                                    document.getElementById('paypal_frm').innerHTML = result.form;
                                    document.getElementById('paypal_frm').submit();
                                }
                            },
                            error: function (e) {

                                $.alert({
                                    icon: 'fa fa-warning',
                                    title: 'Wallet Error',
                                    content: 'Failed to process payment with Paypal',
                                    closeIcon: true,
                                    type: 'dark',
                                    typeAnimated: true,
                                });
                                // console.log('Cannot fetch paypal payment form');
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
    /**
     *
     * @returns {boolean}
     */
    amountAdjust: function () {
        $('#amount').on('input propertychange paste', function (e) {
            $('#paypal_amount').val($(this).val());
        });
        return true;
    }
};
