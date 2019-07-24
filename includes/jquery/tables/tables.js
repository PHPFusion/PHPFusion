/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: tables.js
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
let phpfusion_tables = {

    'init' : function(table_fields, table_key, table_cat, table_db, actions_validate, table_ordering, table_multilang) {

        phpfusion_tables.toggleRow();

        phpfusion_tables.applyFilter(actions_validate);

        phpfusion_tables.masterCheck();

        phpfusion_tables.quickEdit(table_fields, table_key, table_cat, table_db, table_ordering, table_multilang);

        phpfusion_tables.customBox();

    },
    // controls the custom box link behavior
    'customBox' : function() {
        $('body').on('click', '.custom-table', function(e){
            let target = $('#' + $(this).data('table') + '-custom-box');
            if (target.is(':visible')) {
                target.slideUp(150);
                $(this).find('.caret').removeClass('caret-up');
            } else {
                target.slideDown(150);
                $(this).find('.caret').addClass('caret-up');
            }
        });
    },
    // responsive row toggling
    'toggleRow' : function() {
        $('button.toggle-row').bind('click', function(e) {
            $(this).closest('tr').toggleClass('expanded');
        });
        // fix layout problems when table resizes.
        window.onresize = function(event) {
            let viewPortWidth = $(window).width();
            if (viewPortWidth > 1023) {
                $('table.fusion-table tr').removeClass('expanded');
            }
        }

    },
    // controls filter actions behavior
    'applyFilter' : function(opts) {

        //let checkboxes = '';
        /* let array_values = [];
        $('input[name^="id[]"]').each( function() {
            if( $(this).is(':checked') ) {
                array_values.push( $(this).val() );
            }
        });
        data: {
            'show_request': arrayValues
        },*/

        $('#apply_filter').bind('click', function (e) {

            e.preventDefault();

            e.stopPropagation();

            let current_val = $('#filter_actions').val();

            let confirmation = function(opts, current_val) {

                let has_confirmation;

                $.each(opts, function (key, val) {

                    if (key == current_val) { // found a required confirm.

                        if (confirm(val)) {

                            $('#table_action').val(current_val);

                            $('#table_frm').submit();

                        }

                        has_confirmation = true;
                    }

                });

                if (!has_confirmation) {

                    $('#table_action').val(current_val);

                    $('#table_frm').submit();
                }


            }

           confirmation(opts, current_val);

        });

    },
    // Controls checkbox behavior
    'masterCheck' : function() {

        $('#chk_all, #chk_all2').bind('click', function(e) {

            let val = $(this).is(':checked') ? 1 : 0;

            setChecked('table_frm', 'id[]', val);

            setChecked('table_frm', 'chk_all', val);

            setChecked('table_frm', 'chk_all2', val);
        });
    },

    fetchQuickEdit: function(id, table_key, table_db, success_function) {
        let data = {
            table_col :id,
            table_key: table_key,
            table_db : table_db
        };

        $.ajax({

            url: INCLUDES + 'jquery/tables/table-fetch.php',

            type: 'GET',

            data: data,

            dataType: "json",

            error: function(e){
                console.log('Failed to fetch file');
            },

            complete: function(e, xhr) {
                if (xhr == 'success') {
                    if (e.responseJSON['status'] == 'success') {
                        //response = e.responseJSON;
                        success_function(id, table_key, table_db, e.responseJSON);
                    }
                }
            }
        });
    },

    quickEdit: function(table_fields, table_key, table_cat, table_db, table_ordering, table_multilang) {

        $('.quick_edit').bind('click', function(e) {
            e.preventDefault();

            let val = {

                table_col : $(this).data('value'),

                table_key: table_key,

                table_db : table_db
            };

            let showQuickEdit = function(id, table_col, table_key, response) {

                $('.qc-input #qc_id').val( id );

                if (id) {
                    // copy the qc-input into the row.
                    let qc_rows = $('#form-row-'+id+' > td');

                    $('#qc-input').find('button[name*="cancel_quick_editor"]').val( id );

                    $('#qc-input').clone().appendTo( qc_rows ).show();

                    let frm = qc_rows;

                    $.each(response.data, function (fieldName, fieldValue) {

                        let inputDOM = frm.find('input[name=' + fieldName + ']');

                        if (inputDOM.length) {
                            if (inputDOM.length > 1) {

                                if (inputDOM.prop('type') == 'radio' || inputDOM.prop('type') == 'checkbox') {
                                    $('.qc-input input[name='+ fieldName+'][value *=\"'+ fieldValue +'\"]').prop('checked', true);
                                }
                            } else {
                                $('.qc-input input[name=' + fieldName +']').val( fieldValue ); // conflict, this set all input value to 2.
                            }
                        }

                    });


                    $('#form-row-'+id).show();

                    $('#entry-row-'+id).hide();
                }

            }

            phpfusion_tables.fetchQuickEdit(val.table_col, table_key, table_db, showQuickEdit);

        });

        $('body').on('click', 'button[name*="cancel_quick_editor"]', function(e) {
            e.preventDefault();
            let val = $(this).val();
            let qc_rows = $('#form-row-'+val+' > td');
            if (val) {
                qc_rows.html('');
                $('#form-row-'+val).hide();
                $('#entry-row-'+val).show();
            }
        });

        $('body').on('click', 'button[name*="save_quick_editor"]', function(e) {

            e.preventDefault();

            let loader_ui =  $(this).find('.qc-spinner');

            loader_ui.show();

            let fields = {}

            let post_data = {

                fusion_token: $('#table_frm').find('input[name*="fusion_token"]').val(),

                form_id: $('#table_frm').find('input[name*="form_id"]').val(),

                table_db : table_db,

                table_key : table_key,

                table_cat: table_cat,

                table_ordering: table_ordering,

                table_multilang: table_multilang,
            }

            let qcfrm = $(this).closest('.qc-input');

            let row_id = qcfrm.find('#qc_id').val();

            fields[ table_key ] = row_id;

            //console.log(table_fields);

            $.each(JSON.parse(table_fields), function (index, old_value) {

                let inputDOM = qcfrm.find('input[name='+ index + ']');

                let val = inputDOM.val();

                if (inputDOM.prop('type') == 'radio' || inputDOM.prop('type') == 'checkbox') {
                   val = qcfrm.find('input[name='+index+']:checked').val();
                }
                  // this would be differ if the input_id is different.
                fields[ index ] = val;

            });

            post_data['table_fields'] = fields;

            console.log(post_data);

            let qc_rows = $('#form-row-'+row_id+' > td'); // the form container

            $.ajax({

                data: post_data,

                method: 'post',

                url: INCLUDES + 'jquery/tables/table-update.php',

                dataType: 'json',

                success: function(e) {
                    console.log(e);
                },

                error: function(e) {
                    console.log('Error fetching file');
                },

                complete: function(e, xhr) {

                    //console.log(e.responseJSON);

                    if (xhr == 'success') {

                        if (e.responseJSON['status'] == 'success') {

                            //console.log(e.responseJSON);

                            $.each(e.responseJSON['data'], function(key, value) {

                                // conflict with options parse back value
                                // check if there are any options attached to the field.
                                let s = $('#entry-row-'+ row_id).find('[data-col="'+ key + '"] span.value');
                                let td_options = $('#entry-row-'+ row_id).find('[data-col="'+ key + '"]').data('col-options');
                                if (td_options && td_options[value]) {
                                    //console.log(td_options);
                                    s.html(td_options[value]);
                                } else {
                                    s.html( value );
                                }


                                // check the column options




                                //console.log(s);


                            });

                            loader_ui.hide();

                            qc_rows.html('');

                            // update values with id tags to all columns.
                            $('#form-row-'+row_id).hide();

                            $('#entry-row-'+row_id).show();

                        } else {
                            console.log(e.responseJSON['error_message']);
                        }
                    }
                }
            });
        });
    },

}
