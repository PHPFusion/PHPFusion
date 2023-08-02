<?php
require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/header.php';

// Text inputs
echo form_text( 'text_input', 'Label', '', [
    'required' => TRUE
] );

echo form_text( 'text_input_2', 'Label 2', '', [
    'required'      => TRUE,
    'inline'        => FALSE,
    'prepend'       => TRUE,
    'prepend_value' => 'xxx',
    'append'        => TRUE,
    'append_value'  => 'yyy',
] );

// Dropdowns
echo form_select( 'dropdown_1', 'Dropdown 1', '', [
    'required' => TRUE,
    'options'  => [
        1 => 'Option 3',
        2 => 'Option 2',
        3 => 'Option 3',
        4 => 'Option 4',
        5 => 'Option 5',
    ]
] );

echo form_user_select( 'dropdown_2', 'Dropdown 2', '', [
    'required' => TRUE,
] );

echo form_hidden( 'hidden', 'Label', '' );

// Datepicker
echo form_datepicker( 'datepicker', 'Datepicker', '' );

echo form_colorpicker( 'colorpicker', 'Colorpicker', '' );

echo form_checkbox( 'checkbox_inline', 'Checkbox Inline', '1', ['type' => 'toggle'] );

/**
 * <div id="checkbox_inline-field" class="form-group check-group "><label class="control-label" data-checked="0" for="checkbox_inline"><div
 * class="overflow-hide">Checkbox Inline</div></label><div class="pull-left m-r-10"><input id="checkbox_inline"
 * style="margin:0;vertical-align:middle;" name="checkbox_inline" value="1" type="checkbox"></div></div>
 */
echo form_checkbox( 'checkbox_inline', 'Checkbox Inline', '' );

?>
    <!--    <div id="checkbox_options-field" class="form-group check-group "><label class="control-label" data-checked="1" for="checkbox_options">-->
    <!--            <div class="overflow-hide">Checkbox Options</div>-->
    <!--        </label>-->
    <!--        <div class="checkbox"><label class="control-label m-r-10" for="checkbox_options-1"><input id="checkbox_options-1" name="checkbox_options"-->
    <!--                                                                                                  value="1" type="checkbox">Option 1</label></div>-->
    <!--        <div class="checkbox"><label class="control-label m-r-10" for="checkbox_options-2"><input id="checkbox_options-2" name="checkbox_options"-->
    <!--                                                                                                  value="2" type="checkbox">Option 2</label></div>-->
    <!--        <div class="checkbox"><label class="control-label m-r-10" for="checkbox_options-3"><input id="checkbox_options-3" name="checkbox_options"-->
    <!--                                                                                                  value="3" type="checkbox">Option 3</label></div>-->
    <!--    </div>-->
<?php

echo form_checkbox( 'checkbox_options', 'Checkbox Options', '1', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3']] );
?>
    <!--    <div id="checkbox_inline_options-field" class="form-group check-group row ">-->
    <!--        <label class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3" data-checked="1" for="checkbox_inline_options">-->
    <!--            <div class="overflow-hide">Checkbox Inline Options</div>-->
    <!--        </label>-->
    <!--        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">-->
    <!--            <div class="checkbox">-->
    <!--                <label class="control-label m-r-10" for="checkbox_inline_options-1"><input id="checkbox_inline_options-1"-->
    <!--                                                                                           name="checkbox_inline_options" value="1" type="checkbox">Option-->
    <!--                    1</label>-->
    <!--            </div>-->
    <!--            <div class="checkbox"><label class="control-label m-r-10" for="checkbox_inline_options-2"><input id="checkbox_inline_options-2"-->
    <!--                                                                                                             name="checkbox_inline_options" value="2"-->
    <!--                                                                                                             type="checkbox">Option 2</label></div>-->
    <!--            <div class="checkbox"><label class="control-label m-r-10" for="checkbox_inline_options-3"><input id="checkbox_inline_options-3"-->
    <!--                                                                                                             name="checkbox_inline_options" value="3"-->
    <!--                                                                                                             type="checkbox">Option 3</label></div>-->
    <!--        </div>-->
    <!--    </div>-->
<?php

echo form_checkbox( 'checkbox_inline_options', 'Checkbox Inline Options', '2', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3'], 'inline' => TRUE] );

?>
    <!--    <div id="checkbox_radio_options-field" class="form-group check-group "><label class="control-label" data-checked="1" for="checkbox_radio_options">-->
    <!--            <div class="overflow-hide">Checkbox Radio Options</div>-->
    <!--        </label>-->
    <!--        <div class="radio"><label class="control-label m-r-10" for="checkbox_radio_options-1"><input id="checkbox_radio_options-1"-->
    <!--                                                                                                     name="checkbox_radio_options" value="1"-->
    <!--                                                                                                     type="radio" checked="">Option 1</label></div>-->
    <!--        <div class="radio"><label class="control-label m-r-10" for="checkbox_radio_options-2"><input id="checkbox_radio_options-2"-->
    <!--                                                                                                     name="checkbox_radio_options" value="2"-->
    <!--                                                                                                     type="radio">Option 2</label></div>-->
    <!--        <div class="radio"><label class="control-label m-r-10" for="checkbox_radio_options-3"><input id="checkbox_radio_options-3"-->
    <!--                                                                                                     name="checkbox_radio_options" value="3"-->
    <!--                                                                                                     type="radio">Option 3</label></div>-->
    <!--    </div>-->
<?php
echo form_checkbox( 'checkbox_radio_options', 'Checkbox Radio Options', '', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3'], 'type' => 'radio'] );

?>
    <!--    <div id="checkbox_inline_radio_options-field" class="form-group check-group row "><label-->
    <!--                class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3" data-checked="1" for="checkbox_inline_radio_options">-->
    <!--            <div class="overflow-hide">Checkbox Radio Options</div>-->
    <!--        </label>-->
    <!--        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">-->
    <!--            <div class="radio"><label class="control-label m-r-10" for="checkbox_inline_radio_options-1"><input id="checkbox_inline_radio_options-1"-->
    <!--                                                                                                                name="checkbox_inline_radio_options"-->
    <!--                                                                                                                value="1" type="radio" checked="">Option-->
    <!--                    1</label></div>-->
    <!--            <div class="radio"><label class="control-label m-r-10" for="checkbox_inline_radio_options-2"><input id="checkbox_inline_radio_options-2"-->
    <!--                                                                                                                name="checkbox_inline_radio_options"-->
    <!--                                                                                                                value="2" type="radio">Option-->
    <!--                    2</label></div>-->
    <!--            <div class="radio"><label class="control-label m-r-10" for="checkbox_inline_radio_options-3"><input id="checkbox_inline_radio_options-3"-->
    <!--                                                                                                                name="checkbox_inline_radio_options"-->
    <!--                                                                                                                value="3" type="radio">Option-->
    <!--                    3</label></div>-->
    <!--        </div>-->
    <!--    </div>-->
<?php
echo form_checkbox( 'checkbox_inline_radio_options', 'Checkbox Radio Options', '', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3'], 'type' => 'radio', 'inline' => TRUE] );


require_once THEMES . 'templates/footer.php';