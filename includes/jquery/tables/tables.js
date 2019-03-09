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
var phpfusion_tables = {

    'init' : function(table_fields, table_key, table_db) {

        phpfusion_tables.toggleRow();

        phpfusion_tables.applyFilter();

        phpfusion_tables.masterCheck();

        phpfusion_tables.quickEdit(table_fields, table_key, table_db);


    },

    'toggleRow' : function() {
        $('button.toggle-row').bind('click', function(e) {
            $(this).closest('tr').toggleClass('expanded');
        });
        // fix layout problems when table resizes.
        window.onresize = function(event) {
            viewportwidth = $(window).width();
            if (viewportwidth > 1023) {
                $('table.fusion-table tr').removeClass('expanded');
            }
        }

    },

    'applyFilter': function() {

        //var checkboxes = '';
        /* var array_values = [];
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

            $('#table_action').val($('#filter_actions').val());

            $('#table_frm').submit();
        });
    },

    'masterCheck' : function() {
        $('#chk_all, #chk_all2').bind('click', function(e) {

            var val = $(this).is(':checked') ? 1 : 0;

            setChecked('table_frm', 'id[]', val);

            setChecked('table_frm', 'chk_all', val);

            setChecked('table_frm', 'chk_all2', val);
        });
    },

    'fetchQuickEdit': function(id, table_key, table_db, success_function) {
        var data = {
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

    'quickEdit': function(table_fields, table_key, table_db) {

        $('.quick_edit').bind('click', function(e) {
            e.preventDefault();
            var val = {
                table_col : $(this).data('value'),
                table_key: table_key,
                table_db : table_db
            };

            var showQuickEdit = function(id, table_col, table_key, response) {

                $('#qc-input #qc_id').val( id );

                $.each(response.data, function (fieldName, fieldValue) {

                    $('#qc-input #' + fieldName ).val( fieldValue );
                });

                if (id) {
                    // copy the qc-input into the row.
                    var qc_rows = $('#form-row-'+id+' > td');

                    $('#qc-input').find('button[name*="cancel_quick_editor"]').val( id );

                    $('#qc-input').clone().appendTo( qc_rows ).show();

                    $('#form-row-'+id).show();

                    $('#entry-row-'+id).hide();
                }

            }

            phpfusion_tables.fetchQuickEdit(val.table_col, table_key, table_db, showQuickEdit);

        });

        $('body').on('click', 'button[name*="cancel_quick_editor"]', function(e) {
            e.preventDefault();
            var val = $(this).val();
            var qc_rows = $('#form-row-'+val+' > td');
            if (val) {
                qc_rows.html('');
                $('#form-row-'+val).hide();
                $('#entry-row-'+val).show();
            }
        });

        $('body').on('click', 'button[name*="save_quick_editor"]', function(e) {

            e.preventDefault();

            var loader_ui =  $(this).find('.qc-spinner');

            loader_ui.show();

            var fields = {}

            var post_data = {

                fusion_token: $('#table_frm').find('input[name*="fusion_token"]').val(),

                form_id: $('#table_frm').find('input[name*="form_id"]').val(),

                table_db : table_db,

                table_key : table_key
            }

            var row_id = $(this).closest('#qc-input').find('#qc_id').val();

            fields[ table_key ] = row_id;

            $.each(JSON.parse(table_fields), function (index, old_value) {

                fields[ index ] = $('#'+ index).val();

            });

            post_data['table_fields'] = fields;

            var qc_rows = $('#form-row-'+row_id+' > td'); // the form container

            $.ajax({

                data: post_data,

                method: 'post',

                url: INCLUDES + 'jquery/tables/table-update.php',

                dataType: 'json',

                success: function(e) {},

                error: function(e) {
                    console.log('Error fetching file');
                },

                complete: function(e, xhr) {

                    //console.log(e.responseJSON);

                    if (xhr == 'success') {

                        if (e.responseJSON['status'] == 'success') {

                            //console.log(e.responseJSON);

                            $.each(e.responseJSON['data'], function(key, value) {

                                var s = $('#entry-row-'+ row_id).find('[data-col="'+ key + '"] span.value');

                                //console.log(s);

                                s.html( value );
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
    }
}


