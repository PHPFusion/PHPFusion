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
// $modal_1 = openmodal('first', 'This is the first modal').lorem_ipsum(300).form_select('plug', 'Plugin', '', ['input_id' => 'drop1', 'select_alt'=>TRUE, 'inline'=>TRUE, 'options'=>$options]).closemodal();
// $modal_2 = openmodal('second', 'This is the Second modal').lorem_ipsum(300).form_select('plug2', 'Plugin 2', '', ['input_id' => 'drop2', 'select_alt'=>TRUE, 'inline'=>TRUE, 'options'=>$options]).closemodal();
// add_to_footer($modal_1);
// add_to_footer($modal_2);
echo "<style>.selectize-dropdown { z-index: 99999 !important; position:absolute !important;}</style>";

echo '<div class="'.grid_container().'">';
// // Normal dropdown -- OK
echo form_select('selectize', 'Selectize Plugin', '2', ['select_alt' => TRUE, 'options' => $options]);
// // Tags dropdown -- OK
echo form_select('selectize_tags', 'Selectize Plugin Tags', 'Banana,Orange,Citrus', ['select_alt' => TRUE, 'options' => $options, 'tags' => TRUE]);
// // Multiple -- OK
echo form_select('selectize_multiple[]', 'Selectize Plugin Tags', '0,1,2', ['select_alt' => TRUE, 'multiple' => TRUE, 'required' => TRUE, 'options' => $options]);
// Simple callback from database -- OK
echo form_select('selectize_db', 'Selectize Database Callback', '', [
    'select_alt' => TRUE,
    'db'         => DB_NEWS,
    'id_col'     => 'news_id',
    'title_col'  => 'news_subject',

]);
// Advanced Examples
// Opt Group -- done (REMEMBER TO HIDE DISABLED IF OPTGROUP IS ON)
echo form_select('forum_id', 'Select your forum', '',
    [
        'required'      => TRUE,
        'width'         => '100%',
        'inner_width'   => '100%',
        'no_root'       => TRUE,
        'optgroup'      => TRUE,
        'disable_opts'  => $disabled_opts,
        'hide_disabled' => TRUE,
        'placeholder'   => $locale['forum_0395'],
        'db'            => DB_FORUMS,
        'id_col'        => 'forum_id',
        'cat_col'       => 'forum_cat',
        'title_col'     => 'forum_name',
        'select_alt'    => TRUE,
        'custom_query'  => "SELECT forum_id, forum_cat, forum_name FROM ".DB_FORUMS.(multilang_table('FO') ? ' WHERE forum_language="'.LANGUAGE.'"' : ''),
    ]);

// User Select
echo form_select('user_id', 'Select User', '', [
    'width'        => '100%',
    'inner_width'  => '100%',
    //'json' => INCLUDES.''
]);


echo '</div>';

require_once THEMES.'templates/footer.php';