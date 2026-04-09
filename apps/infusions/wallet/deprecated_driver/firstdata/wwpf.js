
let wwpf = {

    init: function (paymentId, display_amount_field, order_amount, form_id, fusion_token,) {

        if (display_amount_field) {
            wwpf.amountAdjust();
        }
        $('body').on('click', '#pay_FirstData', function(e) {

            e.preventDefault();

            if (display_amount_field && !order_amount) {
                let c_val = $('#firstdata_amount').val();
                if (!c_val || c_val == 0) {
                    alert('Please fill in the amount field');
                    return false;
                }
            }

            let data = {
                'PaymentMethod': 'FirstData',
                'PaymentDesc': 'Transaction Payment via FirstData',
                'PaymentID': paymentId
            }

            let formData = $('#firstdataPaymentFrm').serialize() + '&' + $.param(data);

            $.ajax({
                url: INFUSIONS + 'wallet/checkout.json.php', // log transactions
                data: formData,
                dataType: 'json', // debug with html
                method: 'post',
                success: function (result) {

                    if (result.status == 'OK') {

                        let cdata = {
                            'payment_id': result.response,
                            'form_id': form_id,
                            'fusion_token': fusion_token,
                        }

                        let cformData = $('#firstdataPaymentFrm').serialize() + '&' + $.param(cdata)

                        $.ajax({
                            url: INFUSIONS + 'wallet/drivers/firstdata/payments.php',
                            data: cformData,
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function () {
                                $('#pay_FirstData').html('Contacting FirstData <img src=\"' + IMAGES + 'loader/progress/puff.svg\"/> ');
                            },
                            success: function (result) {
                                console.log(result);

                                if (result.field_error) {

                                    $.each(result.field_error, function (key, value) {

                                        if (value) {

                                            if (!$('#'+key+'-field').hasClass('has-error')) {
                                                $('#'+key+'-field').addClass('has-error');
                                                // check if field has error not
                                                $('#'+key+'-field').append('<div class="error-text"><span class=\"label label-danger\">Please fill in this field.</span></div>')
                                            }

                                        } else {
                                            $('#'+key+'-field').removeClass('has-error');
                                            $('#'+key+'-field').find('.error-text').remove();
                                        }

                                    });

                                    $('#pay_FirstData').html('Pay with Credit Card');

                                }

                                if (result.status == 'OK' && result.form) {
                                    //console.log(result.form);
                                    $('#firstdata-form-container').html(result.form);
                                    $('#firstdata_frm').submit();
                                }
                            },
                            error: function (e) {
                                console.log('Cannot fetch FirstData payment form');
                            }
                        });
                    }
                },
                error: function (e) {
                    console.log('FirstData driver transaction error.');
                }
            });
        });

    },

    amountAdjust: function () {
        $('#amount').on('input propertychange paste', function (e) {
            $('#firstdata_amount').val($(this).val());
        });
        return true;
    }


}