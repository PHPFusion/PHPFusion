/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: admin-form.js
| Author: Frederick MC Chan (Deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

jQuery(document).ready(function() {

    var admin_meta_ui = {
        'registerCancelEvent': function() {
            $('body').on('click', '.meta-block .cancel', function(ev) {
                var container;
                if ( container = $(this).data('container') ) {
                    $(container).hide();
                } else {
                    alert('Container has to be defined.');
                }
            });
        },
        'registerEditEvent' : function () {
            $('body').on('click', '.meta-block > .admin-form-edit-link', function(ev) {
                ev.preventDefault();
                var target = $(this).data('target');
                if ( $(target).hasClass('on') ) {
                    $(target).slideUp();
                    $(target).removeClass('on');
                } else {
                    $(target).slideDown();
                    $(target).addClass('on');
                }
            });
        },
        'registerOKEvent' : function () {

            $('body').on('click', '.meta-block .ok', function(ev) {

                var container, source_input, input_text, input_value, hidden_input, post_display;

                ev.preventDefault();

                if ( source_input = $(this).data('source') ) { // source input field

                    var source_type = $(source_input).prop('type');

                    if (source_type == 'select-one') {

                        input_text = $(source_input + ' option:selected').text(); // source input text for source input

                        input_value = $(source_input).val(); // source input value for source input

                    } else if (source_type == 'radio') {

                        input_text = $(source_input + ':checked').closest('label').text(); // source input text for source input

                        input_value = $(source_input + ':checked').val(); // source input value for source input

                    } else if ( source_type == 'text') {

                        // @todo: If checked on PHP Time is the value, then the text value must be - Immediately

                        input_text = $(source_input).val(); // source input text for source input

                        input_value = $(source_input).val(); // source input value for source input

                    }

                    if ( post_display = $(this).data('display') ) { // the display value container

                        $(post_display).text( input_text );

                    } else {
                        alert('Display container has to be defined.');
                    }

                    if ( hidden_input = $(this).data('input') ) { // the hidden input we need to fill

                        $(hidden_input).val( input_value );

                    } else {
                        alert('Input value field has to be defined.');
                    }

                    if ( container = $(this).data('container') ) {  // the wrapper container

                        $(container).hide();

                    } else {
                        alert('Container has to be defined.');
                    }

                } else {

                    alert('Input value field has to be defined.');

                }

            });
        },
        'registerVisibilityInputEvent' : function() {

            $('input[name^=\"af_visibility\"]').bind('click', function(ev) {

                if ( $(this).val() == USER_LEVEL_PASSWORD ) {
                    $('#visibility-password').show();
                } else {
                    $('#visibility-password').hide();
                }

            });
        },
        'init' : function() {
            admin_meta_ui.registerCancelEvent();
            admin_meta_ui.registerEditEvent();
            admin_meta_ui.registerOKEvent();
            admin_meta_ui.registerVisibilityInputEvent();
        }
    }
    var admin_cat_meta_ui = {
        'init' : function() {
            $('.admin-new-ui-cat-btn').bind('click', function(e) {
                e.preventDefault();
                $('.admin-new-ui-cat').show();
            });
        }
    }
    var admin_tag_meta_ui = {
        'init' : function() {
            admin_tag_meta_ui.bindReturnPress();

            admin_tag_meta_ui.registerRemoveTag();

            admin_tag_meta_ui.addCommonTag();
        },
        'bindReturnPress' : function() {
            $('#admin_ui_tags').on('keypress paste', function(e) {
                var evnt = window.event;
                var key = evnt.keyCode;
                var tag_input = $('#admin_ui_tags').val();
                if(key == 13) { // Handle Enter
                    admin_tag_meta_ui.addTag(tag_input);
                    return false;
                }
                $('#admin_ui_tags_button').bind('click', function(e) {
                    admin_tag_meta_ui.addTag(tag_input);
                });
            });
            $('#admin_ui_tags_list').bind('click', function(e) {
                $('#admin_ui_common_tags').show();
            });

        },
        'addCommonTag': function() {
            $('body').on('click', '.btn-tag', function(e) {
                e.preventDefault();
                admin_tag_meta_ui.addTag( $(this).val() );
            })
        },
        'addTag': function(tag_input) {
            if (tag_input) {
                var c = tag_input.split(',');
                if (c.length) {
                    var tag_val = []
                    var c_tag_val = $('#tags').val().split(',');
                    $.each(c, function(index, value) {
                        value = value.trim();
                        var in_array = $.inArray(value, c_tag_val);
                        if ($.inArray(value, c_tag_val) == -1) {
                            // push this to the bottom container.
                            $('#admin_tags_list').append('<li><button type="button" id="tag-'+index+'" class="admin_ui_tag_del">' +
                                '<i class="far fa-times-circle" aria-hidden="true"></i>' +
                                '</button>'+value+'</li>');

                            tag_val.push(value); // we will push the first key, ddd. eee. fff. ggg. one value.
                        }
                    });
                      if (c_tag_val.length) {
                        $.each(c_tag_val, function(index, value) {
                            //console.log(value);
                            value = value.trim();
                            tag_val.push(value);
                        });
                    }
                    $('#tags').val( tag_val.join(',').rtrim(',') );
                    $('#admin_ui_tags').val('');
                }
            }
        },
        'registerRemoveTag': function() {
            $('body').on('click', '#admin_tags_list button', function(e){
                e.preventDefault();
                var pos_index = $(this).attr('id').ltrim('tag-'); // this will handle the position.
                var c_tag_val = $('#tags').val().split(',');

                    $.each(c_tag_val, function(index, value) {
                        value = value.trim();
                        if (index == pos_index) {
                            console.log(value);
                            c_tag_val = $.grep(c_tag_val, function(cval) {
                                return cval != value;
                            })
                        }
                    });
                $('#tags').val(c_tag_val.join(',').rtrim(','));
                $(this).closest('li').remove();
            });
        }
    }

    admin_meta_ui.init();
    admin_cat_meta_ui.init();
    admin_tag_meta_ui.init();
});

var admin_cat_meta_ui = {
    'ajaxCall' : function(token, options) {

        $('#save_new_ui_cat').on('click', function() {

            var input = {
                form_id: 'ui-category',
                fusion_token: token,
                cat_title: $('#ui_cat_title').val(),
                cat_parent: $('#ui_cat_parent').val(),
                cat_options : options
            }

            var spinner = $('#save_new_ui_cat').find('span.fa');
            spinner.show();

            $.ajax({
                url: INCLUDES + 'jquery/form/meta-ui-category.php',
                method: 'post',
                data: input,
                dataType: 'json',
                success: function(e){
                    //console.log(e);
                },
                error: function() {
                    console.log('Failed to fetch file');
                },
                complete: function(e, xhr) {
                    //console.log(xhr);
                    if (xhr == 'success') {
                        spinner.hide();
                        //console.log(e.responseJSON);
                        $('#admin_category_list').html( e.responseJSON['checkbox'] );

                        $('#ui_cat_select').html( e.responseJSON['select']);

                        $('#ui_cat_title').val('');

                        //$('.admin-new-ui-cat ').hide();
                    }

                }
            });

        });

    },
    'init' : function() {

    }
}