<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';
echo '<div class="container">';
opentable('Testing Checkbox Dynamics');
echo "<h4>Without multiple options, inline should not be separated by grid for better UX</h4>";
echo form_checkbox('test', 'Test 1 without inline', '', ['inline'=>FALSE]).'<hr>';
echo form_checkbox('test2', 'Test 2 without inline, reverse label true', '', ['inline'=>FALSE, 'reverse_label'=>TRUE]).'<hr>';
echo form_checkbox('test3', 'Test 3, with inline, without reverse label', '', ['inline'=>TRUE]).'<hr>';
echo form_checkbox('test4', 'Test 4, with inline, with reverse label', '1', ['inline'=>TRUE , 'reverse_label'=>TRUE]).'<hr>';

echo "<h4>With multiple options, inline <strong>must be separated</strong> by grid for better UX</h4>";
echo form_checkbox('test5', 'Test 5, without Inline', '', ['inline'=>FALSE, 'options'=>[1 => 'Yes',2 => 'No']]).'<hr>';
echo form_checkbox('test6', 'Test 6, with Inline', '', ['inline'=>TRUE, 'options'=>[1 => 'Yes',2 => 'No']]).'<hr>';
echo form_checkbox('test7', 'Test 7, reverse label without Inline', '', ['inline'=>FALSE, 'reverse_label' => TRUE, 'options'=>[1 => 'Yes',2 => 'No']]).'<hr>';
echo form_checkbox('test8', 'Test 8, reverse label with Inline', '', ['inline'=>TRUE, 'reverse_label' => FALSE, 'options'=>[1 => 'Yes',2 => 'No']]).'<hr>';

echo "<h4>With multiple options, we can also set whether the options should be inlined or not</h4>";
echo form_checkbox('test9', 'Test 9, with inline and with options inline', '', ['inline'=>TRUE, 'inline_options'=>TRUE, 'options'=>[1 => 'Yes',2 => 'No']]).'<hr>';
echo form_checkbox('test10', 'Test 10, with inline and without options inline', '1,2', ['inline'=>TRUE, 'inline_options'=>FALSE, 'options'=>[1 => 'Yes',2 => 'No']]).'<hr>';

echo "<h4>Type radios will work on the same layout. We do not add special styling to these .radio, .checkbox layout on themes.</h4>";
echo form_checkbox('test11', 'Test 11, Type radio', '1', ['inline'=>TRUE, 'type'=>'radio', 'inline_options'=>TRUE, 'options'=>[1 => 'Yes',2 => 'No']]).'<hr>';
?>
<h4>This is copied from Bootstrap 3.4 markup</h4>
<a href="https://getbootstrap.com/docs/3.4/css/#forms">https://getbootstrap.com/docs/3.4/css/#forms</a><br/><br/>

<?php
print_p('<div class="checkbox">
    <label>
        <input type="checkbox" value="">
        Option one is this and that&mdash;be sure to include why it\'s great
    </label>
</div>');
?>
<div class="checkbox">
    <label>
        <input type="checkbox" value="">
        Option one is this and that&mdash;be sure to include why it's great
    </label>
</div>

<?php
closetable();
echo '</div>';

require_once THEMES.'templates/footer.php';