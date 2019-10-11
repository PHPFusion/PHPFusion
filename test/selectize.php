<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

// Trigger first modal
$options = [
    0 => 'Banana',
    1 => 'Apple',
    2 => 'Orange',
    3 => 'Citrus',
    4 => 'Mango',
    5 => 'Peach',
    6 => 'Strawberries',
    7 => 'Kiwi'
];
$modal_1 = openmodal('first', 'This is the first modal').lorem_ipsum(300).form_select('plug', 'Plugin', '', ['input_id' => 'drop1', 'select_alt'=>TRUE, 'inline'=>TRUE, 'options'=>$options]).closemodal();
$modal_2 = openmodal('second', 'This is the Second modal').lorem_ipsum(300).form_select('plug2', 'Plugin 2', '', ['input_id' => 'drop2', 'select_alt'=>TRUE, 'inline'=>TRUE, 'options'=>$options]).closemodal();
add_to_footer($modal_1);
add_to_footer($modal_2);
echo "<style>.selectize-dropdown { z-index: 99999 !important; position:absolute !important;}</style>";

// echo '<div class="'.grid_container().'">';
// // Normal dropdown
// echo form_select('selectize', 'Selectize Plugin', '2', ['select_alt'=>TRUE, 'options'=>$options]);
// // Tags dropdown
// echo form_select('selectize_tags', 'Selectize Plugin Tags', 'Minotaur,Tigreal,Aldous', ['select_alt'=>TRUE, 'options'=>$options, 'tags'=>TRUE]);
// // Multiple
// echo form_select('selectize_multiple[]', 'Selectize Plugin Tags', 'Minotaur,Tigreal,Aldous', ['select_alt'=>TRUE,'multiple'=>TRUE, 'options'=>$options]);
// echo '</div>';


require_once THEMES.'templates/footer.php';