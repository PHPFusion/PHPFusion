<?php
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';
/**
 * Testing the new integration of #2210
 */
if (post('save')) {
    $data = [
        'input_string'    => sanitizer('input_string', 'No input string', 'input_string'),
        'input_name_arr'  => sanitizer(['input_name_arr'], 'No input array', 'input_name_arr'),
        'all_names'       => sanitizer(['name'], 'No input array'), // can't target error since this is all 4 fields
        'name_prefix'     => sanitizer(['name', 'name_prefix'], 'No input', 'name[name_prefix]'), // targeting name prefix specifically
        'name_firstname'  => sanitizer(['name', 'first_name'], 'No input', 'name[first_name]'), // targeting first_name specifically
        'name_middlename' => sanitizer(['name', 'middle_name'], 'No input', 'name[middle_name]'), // targeting middle_name prefix specifically
        'name_lastname'   => sanitizer(['name', 'last_name'], 'No input', 'name[last_name]'), // targeting last_name prefix specifically
    ];
    print_p($data);
}
echo "<div class='container'>\n";
echo "<div class='spacer-md'>\n<h3>\nTest Sanitizer\n</h3>\n";
echo openform('inputform', 'post').
    form_text('input_string', 'Enter anything', '').
    form_select('input_name_arr[]', 'Select Groups', '', [
        'options'     => fusion_get_groups(),
        'multiple'    => TRUE,
        'width'       => '100%',
        'inner_width' => '100%',
    ]).
    "<div class='spacer-sm'>
        <div class='display-flex'>".
    form_select('name[name_prefix]', 'Salutations', '', ['required' => TRUE, 'class' => 'display-inline-block', 'inner_width' => '100px', 'options' => ['Mr' => 'Mr.', 'Ms' => 'Ms.', 'Dr' => 'Dr.']]).
    form_text('name[first_name]', 'First Name', '', ['required' => TRUE, 'class' => 'display-inline-block']).
    form_text('name[middle_name]', 'Middle Name', '', ['required' => TRUE, 'class' => 'display-inline-block']).
    form_text('name[last_name]', 'Last Name', '', ['required' => TRUE, 'class' => 'display-inline-block']).
    "</div>
    </div>".
    form_button('save', 'Test sanitization', 'save', ['class' => 'btn-primary']).
    closeform();
echo "</div>\n";
echo "</div>\n";

require_once THEMES.'templates/footer.php';