<?php
require_once __DIR__.'/../../../maincore.php';
require_once INCLUDES.'theme_functions_include.php';
require_once TEMPLATES.'render_functions.php';
function add_links($data) {
    $time  = time();
    echo opencollapse($time);
    echo opencollapsebody($data['link_name'], $time.'m', $time);
    echo form_text('link_url', 'URL', $data['link_url']);
    echo form_text('link_name', 'Name', $data['link_name']);
    echo form_text('link_title', 'Title Attribute', '', []);
    echo form_checkbox('link_window', 'Open link in a new tab', '', ['type'=>'checkbox', 'reverse_label'=>TRUE]);
    echo form_text('link_description', 'Description', '', ['ext_tip'=>'The description will be displayed in the menu if the current theme supports it.']);
    echo "<a href='' class='remove_link text-danger' data-id='".$time."_menu'>Remove</a> | ";
    echo closecollapsebody();
    echo closecollapse();
}
