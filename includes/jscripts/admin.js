/**
 * Sitelinks administration
 * @type {{slListing: slAdmin.slListing, slsettingsJs: slAdmin.slsettingsJs, slFormJS: slAdmin.slFormJS}}
 */
let slAdmin = {


    /**
     * Form
     */
    slFormJS: function () {
        let elem = $('#link_position');
        let lps = $('#link_position_id');
        let pos_val = elem.val();
        lps.attr('disabled', 'disabled');
        if (pos_val > 3) {
            lps.removeAttr('disabled');
        }
        $(document).on('change', '#link_position', function (ev) {
            ev.preventDefault();
            let lpval = $(this).val();
            lps.attr('disabled', 'disabled');
            if (lpval > 3) {
                lps.removeAttr('disabled');
                lps.focus();
            }
        });
    },

    smForm: function () {
        $(document).on('change', '#menu_branding', function (e) {
            let val = $(this).val(),
                header_wrapper = $('#menu_header_wrapper'),
                image_wrapper = $('#menu_image_wrapper');
            header_wrapper.hide();
            image_wrapper.hide();
            if (val == 1) {
                header_wrapper.show();
                image_wrapper.hide();
            } else if (val == 2) {
                header_wrapper.hide();
                image_wrapper.show();
            }
        });
    },

    /**
     *Listing page
     * @param locale
     * @param token
     */
    slListing: function (locale, token) {

        $('#check_all').on('change', function (e) {
            let check_status = $(this).is(':checked') ? 1 : 0;
            setChecked('fusion_sltable_form', 'link_id[]', check_status);
        });

        // Delete warning link
        $('body').on('click', '.del-warn', function (ev) {
            if (!confirm(locale.SL_0080)) {
                return false;
            }
        });

        // Movelinks JS.
        $('#link_move').on('click', function (ev) {
            ev.preventDefault();
            // check if any link is clicked
            $('#table_action').val('link_move');
            $('form#fusion_sltable_form').submit();
        });

        $('#link_publish').on('click', function (ev) {
            ev.preventDefault();
            // check if any link is clicked
            $('#table_action').val('publish');
            $('form#fusion_sltable_form').submit();
        });

        $('#link_unpublish').on('click', function (ev) {
            ev.preventDefault();
            // check if any link is clicked
            $('#table_action').val('unpublish');
            $('form#fusion_sltable_form').submit();
        });

        // Delete link JS
        $('#link_del').on('click', function (ev) {
            ev.preventDefault();
            if (confirm(locale.SL_0080)) {
                $('#table_action').val('link_del');
                $('form#fusion_sltable_form').submit();
            }
            return false;
        });

        // Sorting
        $('.sort').sortable({
            handle: '.handle',
            placeholder: 'state-highlight',
            connectWith: '.connected',
            scroll: true,
            axis: 'y',
            update: function (e, ui) {

                let tableElem = $(this).children('tr'),
                    order_array = [];

                tableElem.each(function () {
                    order_array.push($(this).attr('id'));
                });

                let order_array_string = order_array.join(','),
                    param = {
                        'fusion_token': token,
                        'form_id': 'sitelinks_order',
                        'order': order_array_string,
                    }

                $(this).find('.num').each(function (i) {
                    $(this).text(i + 1);
                });

                $.post(site_path + 'administration/includes/?api=sitelinks-order', param, function (response) {
                    if (response.status === 200) {
                        alert(locale.SL_0016);
                    }
                }).fail(function (ev) {
                    alert(locale.error_preview + '\n' + locale.error_preview_text);
                });

            }
        });
    },

    smListing: function (locale) {

        $('#check_all').on('change', function (e) {
            let check_status = $(this).is(':checked') ? 1 : 0;
            setChecked('fusion_smtable_form', 'menu_id[]', check_status);
        });

        // Delete warning link
        $('body').on('click', '.del-warn', function (ev) {
            if (!confirm(locale.SL_0081)) {
                return false;
            }
        });

        $('#menu_publish').on('click', function (ev) {
            ev.preventDefault();
            // check if any link is clicked
            $('#table_action').val('menu_publish');
            $('form#fusion_smtable_form').submit();
        });

        $('#menu_unpublish').on('click', function (ev) {
            ev.preventDefault();
            // check if any link is clicked
            $('#table_action').val('menu_unpublish');
            $('form#fusion_smtable_form').submit();
        });

        // Delete link JS
        $('#menu_del').on('click', function (ev) {
            ev.preventDefault();
            if (confirm(locale.SL_0081)) {
                $('#table_action').val('menu_del');
                $('form#fusion_smtable_form').submit();
            }
            return false;
        });
    },


}
