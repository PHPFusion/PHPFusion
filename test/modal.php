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
$modal_1 = openmodal('first', 'This is the first modal').lorem_ipsum(300).form_select('plug', 'Plugin', '', ['id' => 'drop1', 'inline'=>TRUE, 'options'=>$options]).closemodal();
$modal_2 = openmodal('second', 'This is the Second modal').lorem_ipsum(300).form_select('plug2', 'Plugin 2', '', ['id' => 'drop2', 'inline'=>TRUE, 'options'=>$options]).closemodal();

add_to_footer($modal_1);
add_to_footer($modal_2);

require_once THEMES.'templates/footer.php';