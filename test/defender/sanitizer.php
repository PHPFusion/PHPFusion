<?php
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';
/**
 * Testing the new integration of #2210
 */
if (post('save')) {
    $data = [
        'input_string' => sanitizer('input_string', 'No input string', 'input_string'),
        'input_name_arr' => sanitizer(['input_name_arr'], 'No input array', 'input_name_arr'),
    ];
    print_p($data);
}
echo "<div class='container'>\n";
echo "<div class='spacer-md'>\n<h3>\nTest Sanitizer\n</h3>\n";
echo openform('inputform', 'post').
    form_text('input_string', 'Enter anything', '').
    form_select('input_name_arr[]', 'Select Groups', '', [
        'options'=>fusion_get_groups(),
        'multiple' => TRUE,
        'width' => '100%',
        'inner_width' => '100%',
        ]).
    form_button('save', 'Test sanitization', 'save', ['class'=>'btn-primary']).
    closeform();
echo "</div>\n";
echo "</div>\n";

require_once THEMES.'templates/footer.php';