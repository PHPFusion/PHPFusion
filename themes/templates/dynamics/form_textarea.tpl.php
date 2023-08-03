<?php

$html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ($options['inline'] && $label ? 'row' : '') . $error_class . $options['class'] . "'" . ($options['width'] ? " style='width: " . $options['width'] . " !important;'" : '') . ">\n";

$html .= ($label) ? "<label class='control-label " . ($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '') . "' for='" . $options['input_id'] . "'>" . $label . ($options['required'] == 1 ? "<span class='required'>&nbsp;*</span>" : '') . " " . ($options['tip'] ? "<i class='pointer fa fa-question-circle' title='" . $options['tip'] . "'></i>" : '') . "</label>\n" : '';

$html .= ($options['inline']) ? "<div class='clearfix" . ($label ? ' col-xs-12 col-sm-9 col-md-9 col-lg-9' : '') . "'>\n" : '';

// $tab_active = 0;
// $tab_title = [];
if ($options['preview'] && ($options['type'] == "html" || $options['type'] == "bbcode")) {

    $preview_button = "<button type='button' class='bbcode' data-action='preview'><span class='bbcode-icon-wrap p-l-5 p-r-5'><i class='far fa-eye m-r-10'></i><span class='preview-text'>" . $locale['preview'] . "</span></span></button>";

    $tab_title['title'][] = $locale['preview'];
    $tab_title['id'][] = "prw-" . $options['input_id'];
    $tab_title['icon'][] = '';
    $tab_title['title'][] = $locale['texts'];
    $tab_title['id'][] = "txt-" . $options['input_id'];
    $tab_title['icon'][] = '';
    $tab_active = tab_active( $tab_title, 1 );
}

$html .= ($options['type'] == "html" || $options['type'] == "bbcode") ? "<div class='panel panel-default panel-txtarea m-b-0' " . ($options['preview'] ? "style='border-radius:0;'" : '') . ">\n<div class='panel-heading clearfix'>\n" : '';

if ($options['preview'] && ($options['type'] == "bbcode" || $options['type'] == "html")) {

    $html .= "<div class='nav-wrapper editor-wrapper'>\n";

    // $html .= openeditortab($tab_title, $tab_active, $options['input_id'] . "-link", "", "editor-wrapper");
}

if ($options['type'] == "bbcode" && $options['form_name']) {
    $html .= "<div class='bbcode_input' style='line-height:0;'>\n";
    $html .= display_bbcodes( '100%', $options['input_id'], $options['form_name'], $options['input_bbcode'] );

    $html .= ($preview_button ?? '') . ($options['preview'] ? "</div>\n" : "");
} else if ($options['type'] == "html" && $options['form_name']) {
    $html .= "<div class='html-buttons'>\n";
    $html .= display_html( $options['form_name'], $options['input_id'], TRUE, TRUE, TRUE, $options['path'] );
    $html .= ($preview_button ?? '') . $options['preview'] ? "</div>\n" : "";
}

$html .= ($options['type'] == "html" || $options['type'] == "bbcode") ? "</div>\n</div>\n<div class='panel-body p-0'>\n" : '';

if ($options['preview'] && ($options['type'] == "bbcode" || $options['type'] == "html")) {
    $html .= "<div id='tab-content-" . $options['input_id'] . "-link' class='tab-content p-0'>\n";
    $html .= opentabbody( $tab_title['title'][1], "txt-" . $options['input_id'], $tab_active );
}

if ($options['inline_editing'] == TRUE) {
    $html .= "<div id='" . $options['input_id'] . "' " . ($options['width'] ? "style='display:block; width: " . $options['width'] . ";'" : '') . ">" . $input_value . "</div>\n";
} else {
    $html .= "<textarea name='$input_name' style='width: " . $options['inner_width'] . "; height:" . $options['height'] . ";" . ($options['no_resize'] ? ' resize: none;' : '') . "' rows='" . $options['rows'] . "' cols='' class='form-control m-0 " . ($options['inner_class'] ? " " . $options['inner_class'] . " " : '') . ($options['autosize'] ? 'animated-height' : '') . " " . (($options['type'] == "html" || $options['type'] == "bbcode") ? "no-shadow no-border bbr-0" : '') . " textbox'" . ($options['placeholder'] ? " placeholder='" . $options['placeholder'] . "' " : '') . " id='" . $options['input_id'] . "'" . ($options['deactivate'] ? ' readonly' : '') . " " . ($options['maxlength'] ? " maxlength='" . $options['maxlength'] . "'" : '') . ">" . $input_value . "</textarea>\n";
}

if ($options['preview'] && ($options['type'] == "bbcode" || $options['type'] == "html")) {
    $html .= closetabbody();
    $html .= opentabbody( $tab_title['title'][0], "prw-" . $options['input_id'] . "", $tab_active );
    $html .= $locale['global_003'];
    $html .= closetabbody();
    $html .= "</div>\n";
    add_to_jquery( "
        $(document).on('click', '[data-action=\"preview\"]', function(e) {
            e.preventDefault();
            let preview_tab = $('#prw-" . $options['input_id'] . "'),
            editor_tab = $('#txt-" . $options['input_id'] . "'),
            placeholder = $(this).find('.preview-text');

            if ( editor_tab.is(':visible') ) {
                $(this).addClass('active');
                placeholder.text('Hide Preview');

                let text = $('#" . $options['input_id'] . "').val(),
                format = '" . ($options['type'] == "bbcode" ? 'bbcode' : 'html') . "',
                data = {
                    " . (defined( 'ADMIN_PANEL' ) ? "'mode': 'admin', " : "") . "
                    'text' : text,
                    'editor' : format,
                    'url' : '" . $_SERVER['REQUEST_URI'] . "',
                    'form_id' : 'prw-" . $options['form_name'] . "',
                    'fusion_token' : '" . fusion_get_token( "prw-" . $options['form_name'], 30 ) . "'
                },
                sendData = $(this).closest('form').serialize() + '&' + $.param(data);

                $.ajax({
                    url: '" . FUSION_ROOT . INCLUDES . "dynamics/assets/preview/preview.ajax.php',
                    type: 'POST',
                    dataType: 'html',
                    data : sendData,
                    success: function(result) {
                        console.log(result);
                        preview_tab.html(result).addClass('in active');
                        editor_tab.removeClass('in active');

                    },
                    error: function(result) {
                        alert('" . $locale['error_preview'] . "' + '\\n" . $locale['error_preview_text'] . "');
                    }
                });

            } else {
                $(this).removeClass('active');
                placeholder.text('Preview');
                preview_tab.removeClass('in active');
                editor_tab.addClass('in active');
            }
        });
        " );
}

if (($options['type'] == "html" || $options['type'] == "bbcode") && $options['wordcount'] === TRUE) {
    $html .= "</div>\n<div class='panel-footer clearfix'>\n";
    $html .= "<div class='overflow-hide'><i><small>" . $locale['word_count'] . ": <span id='" . $options['input_id'] . "-wordcount'></span>" . (!empty( $options['maxlength'] ) ? " / " . $options['maxlength'] : '') . "</small></i></div>";
    add_to_jquery( "
        if ($('#" . $options['input_id'] . "').length) {
            var init_str = $('#" . $options['input_id'] . "').val().length;
            $('#" . $options['input_id'] . "-wordcount').text(init_str);
        }
        $('#" . $options['input_id'] . "').on('input propertychange paste', function() {
            var str = $(this).val().length;
            $('#" . $options['input_id'] . "-wordcount').text(str);
        });
        " );
    $html .= "</div>\n<!---panel-footer-->";
}

if ((!$options['type'] == "bbcode" && !$options['type'] == "html")) {
    $html .= $options['ext_tip'] ? "<span class='tip'><i>" . $options['ext_tip'] . "</i></span>" : "";
}

$html .= $options['inline'] ? "</div>\n" : '';

if (($options['type'] == "bbcode" || $options['type'] == "html")) {
    if ($options['wordcount']) {
        $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>" . $options['ext_tip'] . "</i></span>" : "";
        $html .= "</div>\n";

    } else {
        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>" . $options['ext_tip'] . "</i></span>" : "";

    }
}

$html .= (($options['required'] == 1 && \Defender::inputHasError( $input_name )) || \Defender::inputHasError( $input_name )) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger text-white p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";

$html .= "</div>\n";