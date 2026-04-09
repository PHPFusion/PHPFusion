/**
 *
 * @type {{displayWallet: walletJs.displayWallet, BASEDIR: string, THEMES: string, INFUSIONS: string, CLASSES: string, INCLUDES: string, printInvoice: walletJs.printInvoice}}
 */
let walletJs = {

        BASEDIR: document.location.origin + site_path,

        INFUSIONS: document.location.origin + "/infusions/",

        INCLUDES: document.location.origin + "/includes/",

        THEMES: document.location.origin + "/themes/",

        CLASSES: document.location.origin + "/includes/classes/",

        IMAGES: document.location.origin + "/images/",

        // Fusion JS Resources Header Script
        displayWallet: function (sel) {
            console.log(sel);

            $('#' + sel + '-form').show();

            // set default active - use php
            $('#wallet_options-' + sel).closest('.radio').addClass('active');
            $('body').on('click', '#wallet_options-field input[type=radio]', function (e) {

                $('#wallet_form .radio').removeClass('active');
                $(this).closest('.radio').addClass('active');
                let f = $(this).val();

                $('.driversform').hide();
                $('#' + f + '-form').show();
            });
        }
        ,

        printInvoice: function (divName) {
            let printContents = document.getElementById(divName).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    }
;
