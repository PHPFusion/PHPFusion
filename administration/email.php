<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: email.php
| Author: MarcusG
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess('MAIL');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/emails.php";
require_once INCLUDES."html_buttons_include.php";
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'email.php'.fusion_get_aidlink(), 'title' => $locale['400']]);

if (isset($_POST['save_template'])) {
    $data = array(
        'template_id' => form_sanitizer($_POST['template_id'], '', 'template_id'),
        'template_key' => form_sanitizer($_POST['template_key'], '', 'template_key'),
        'template_format' => form_sanitizer($_POST['template_format'], '', 'template_format'),
        'template_subject' => form_sanitizer($_POST['template_subject'], '', 'template_subject'),
        'template_content' => form_sanitizer($_POST['template_content'], '', 'template_content'),
        'template_active' => form_sanitizer($_POST['template_active'], '', 'template_active'),
        'template_sender_name' => form_sanitizer($_POST['template_sender_name'], '', 'template_sender_name'),
        'template_sender_email' => form_sanitizer($_POST['template_sender_email'], '', 'template_sender_email'),
    );
    if (\defender::safe()) {
        dbquery_insert(DB_EMAIL_TEMPLATES, $data, "update");
        addNotice('success', $locale['410']);
        redirect(FUSION_SELF.fusion_get_aidlink()."&amp;template_id=".$data['template_id']);
    }
} elseif (isset($_POST['test_template'])) {
    $data = array(
        'template_id' => form_sanitizer($_POST['template_id'], '', 'template_id'),
        'template_key' => form_sanitizer($_POST['template_key'], '', 'template_key'),
        'template_format' => form_sanitizer($_POST['template_format'], '', 'template_format'),
        'template_subject' => form_sanitizer($_POST['template_subject'], '', 'template_subject'),
        'template_content' => form_sanitizer($_POST['template_content'], '', 'template_content'),
        'template_active' => form_sanitizer($_POST['template_active'], '', 'template_active'),
        'template_sender_name' => form_sanitizer($_POST['template_sender_name'], '', 'template_sender_name'),
        'template_sender_email' => form_sanitizer($_POST['template_sender_email'], '', 'template_sender_email'),
    );
    if (\defender::safe()) {
        require_once INCLUDES."sendmail_include.php";
        dbquery_insert(DB_EMAIL_TEMPLATES, $data, "update");
        sendemail_template($data['template_key'], $locale['412'], $locale['413'], $locale['414'], $locale['415'], $locale['416'],
                           $userdata['user_email']);
        addNotice('success', sprintf($locale['411'], $userdata['user_email']));
        redirect(FUSION_SELF.fusion_get_aidlink()."&amp;template_id=".$data['template_id']);
    }
}
$result = dbquery("SELECT template_id, template_key, template_name, template_language
FROM ".DB_EMAIL_TEMPLATES." ".(multilang_table("ET") ? "WHERE template_language='".LANGUAGE."'" : "")."
ORDER BY template_id ASC");
$template = array();
if (dbrows($result) != 0) {
    while ($data = dbarray($result)) {
        $template[$data['template_id']] = $data['template_name'];
    }
}
$tab_title = array();
foreach ($template as $id => $tname) {
    $tab_title['title'][$id] = $tname;
    $tab_title['id'][$id] = $id;
    $tab_title['icon'][$id] = '';
}

$tab_values = array_values($tab_title['id']);
$_GET['section'] = isset($_GET['section']) && isnum($_GET['section']) && in_array($_GET['section'], $tab_values) ? $_GET['section'] : $tab_values[0];

opentable($locale['400']);

echo opentab($tab_title, $_GET['section'], "email-templates-tab", TRUE);
echo opentabbody($tab_title['title'][$_GET['section']], $tab_title['id'][$_GET['section']], $_GET['section'], TRUE);
$result = dbquery("SELECT * FROM ".DB_EMAIL_TEMPLATES." WHERE template_id='".intval($_GET['section'])."' LIMIT 1");
if (dbrows($result)) {
    $data = dbarray($result);

    $html_helper = "";
    $text_helper = "";

    if ($data['template_active']) {
        if ($data['template_format'] == "html") {
            $text_helper = "class='display-none'";
            $html_text = $locale['418'];
        } else {
            $html_helper = "class='display-none'";
            $html_text = $locale['419'];
        }
    } else {
        if ($data['template_format'] == "html") {
            $html_helper = "class='display-none'";
            $html_text = $locale['418'];
        } else {
            $html_text = $locale['419'];
        }
    }
}

echo openform('emailtemplateform', 'post', FUSION_SELF.fusion_get_aidlink(), array("class" => "m-t-20"));
echo form_hidden('template_id', '', $data['template_id']);
echo form_hidden('template_key', '', $data['template_key']);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";

echo form_select('template_active', $locale['421'], $data['template_active'], array(
    'options' => array($locale['disable'], $locale['enable']),
    'placeholder' => $locale['choose'],
    'inline' => TRUE,
));

echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";

echo "<div id='active_info' ".$html_helper.">".sprintf($locale['422'], $html_text)."</div>\n";
echo "<div id='inactive_info' ".$text_helper." >".$locale['423']."</div>\n";

echo "</div>\n";
echo "</div>\n";


echo "<div class='row m-b-10'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";

echo form_select('template_format', $locale['426'], $data['template_format'], array(
    'options' => array('html' => $locale['418'], 'plain' => $locale['419']),
    'inline' => TRUE,
));

echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
echo "<div id='html_info' class='m-t-10' >".$locale['427']."</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='row m-b-10'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
echo form_text('template_sender_name', $data['template_key'] == "CONTACT" ? $locale['428'] : $locale['429'], $data['template_sender_name'], array(
    'required' => TRUE,
    'error_text' => $locale['472'],
    'inline' => TRUE,
    'class' => 'm-b-0',
));

if ($data['template_key'] == "CONTACT") {
    echo "<p><small>** ".$locale['430']."</small>\n</p>\n";
}
echo form_text('template_sender_email', $data['template_key'] == "CONTACT" ? $locale['431'] : $locale['432'], $data['template_sender_email'], array(
    'required' => TRUE,
    'error_text' => $locale['473'],
    'inline' => TRUE,
    'class' => 'm-b-0',
));
if ($data['template_key'] == "CONTACT") {
    echo "<p><small>** ".$locale['433']."</small>\n</p>\n";
}
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside("");
echo form_button('save_template', $locale['439'], $locale['439'], array('class' => 'btn-primary'));
echo form_button('test_template', $locale['437'], $locale['437'], array('class' => 'btn-default'));
echo form_button('reset', $locale['440'], $locale['440'], array('class' => 'btn-default'));
closeside();
echo "</div>\n";
echo "</div>\n";


openside("");
echo form_text('template_subject', $locale['434'], $data['template_subject'], array(
    'required' => 1,
    'error_text' => $locale['470'],
    'autosize' => TRUE,
));
echo "<div class='btn-group'>\n";
echo "<button type='button' class='btn btn-default button' value='[SITENAME]' onclick=\"insertText('template_subject', '[SITENAME]', 'emailtemplateform');\">SITENAME</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SITEURL]' onclick=\"insertText('template_subject', '[SITEURL]', 'emailtemplateform');\">SITEURL</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SUBJECT]' onclick=\"insertText('template_subject', '[SUBJECT]', 'emailtemplateform');\">SUBJECT</button>\n";
echo "<button type='button' class='btn btn-default button' value='[USER]' onclick=\"insertText('template_subject', '[USER]', 'emailtemplateform');\">USER</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SENDER]' onclick=\"insertText('template_subject', '[SENDER]', 'emailtemplateform');\">SENDER</button>\n";
echo "<button type='button' class='btn btn-default button' value='[RECEIVER]' onclick=\"insertText('template_subject', '[RECEIVER]', 'emailtemplateform');\">RECEIVER</button>\n";
echo "</div>\n";


echo "<div class='m-t-20 m-b-20'>\n";
echo "<a class='pointer' data-target='#email_tutorial' data-toggle='collapse'>".$locale['451']."</a>";
echo "</div>\n";
echo "<div id='email_tutorial' class='collapse'>\n";
echo "<table class='table table-responsive'>\n";
echo "<tr>\n";
echo "<th>".$locale['452']."</th>\n";
echo "<th>".$locale['453']."</th>\n";
echo "</tr>\n";
echo "<tbody>\n";
echo "<tr>\n";
echo "<td>[SITENAME]</td>\n";
echo "<td>".fusion_get_settings('sitename')."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[SITEURL]</td>\n";
echo "<td>".fusion_get_settings('siteurl')."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[SUBJECT]</td>\n";
echo "<td>".$locale['454']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[MESSAGE]</td>\n";
echo "<td>".$locale['455']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[USER]</td>\n";
echo "<td>".$locale['456']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[SENDER]</td>\n";
echo "<td>".$locale['457']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[RECEIVER]</td>\n";
echo "<td>".$locale['458']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[THREAD_URL]</td>\n";
echo "<td>".$locale['459']."</td>\n";
echo "</tr>\n";
echo "</tbody>\n</table>\n";
echo "</div>\n";
closeside();

openside("");
if ($data['template_format'] == "plain") {
    add_to_head("
	<style>
	#template_content { border:none;}
	</style>

	");
}
echo form_textarea('template_content', $locale['435'], $data['template_content'], array(
    'required' => TRUE,
    'error_text' => $locale['471'],
    'autosize' => TRUE,
    'preview' => $data['template_format'] == 'html' ? TRUE : FALSE,
    'html' => $data['template_format'] == 'html' ? TRUE : FALSE,
    'inputform' => 'emailtemplateform',
));
echo "<div class='btn-group'>\n";
echo "<button type='button' class='btn btn-default button' value='[SUBJECT]' onclick=\"insertText('template_content', '[SUBJECT]', 'emailtemplateform');\">SUBJECT</button>\n";
echo "<button type='button' class='btn btn-default button' value='[MESSAGE]' onclick=\"insertText('template_content', '[MESSAGE]', 'emailtemplateform');\">MESSAGE</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SENDER]' onclick=\"insertText('template_content', '[SENDER]', 'emailtemplateform');\">SENDER</button>\n";
echo "<button type='button' class='btn btn-default button' value='[RECEIVER]' onclick=\"insertText('template_content', '[RECEIVER]', 'emailtemplateform');\">RECEIVER</button>\n";
echo "<button type='button' class='btn btn-default button' value='[USER]' onclick=\"insertText('template_content', '[USER]', 'emailtemplateform');\">USER</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SITENAME]' onclick=\"insertText('template_content', '[SITENAME]', 'emailtemplateform');\">SITENAME</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SITEURL]' onclick=\"insertText('template_content', '[SITEURL]', 'emailtemplateform');\">SITEURL</button>\n";
echo "<button type='button' class='btn btn-default button' value='[THREAD_URL]' onclick=\"insertText('template_content', '[THREAD_URL]', 'emailtemplateform');\">THREAD URL</button>\n";
echo "</div>\n";
echo "<div id='html_buttons' class='".($data['template_format'] == "html" ? "m-t-5" : "display-none")."'>\n";
echo "<button type='button' class='btn btn-default button' value='".$locale['436']."' onMousedown=\"javascript:this.form.template_content.focus();this.form.template_content.select();\" onmouseup=\"addText('template_content', '&lt;body style=\'background-color:#D7F9D7;\'&gt;', '&lt;/body&gt;', 'emailtemplateform');\">\n".$locale['436']."</button>\n";
$folder = BASEDIR."images/";
$image_files = makefilelist($folder, ".|..|index.php", TRUE);
$opts = array();
foreach ($image_files as $image) {
    $opts[$image] = $image;
}
echo form_select('insertimage', '', '', array(
    'options' => $opts,
    'placeholder' => $locale['469'],
    'allowclear' => TRUE
));
echo "</div>\n";
closeside();
echo form_button('save_template', $locale['439'], $locale['439'], array('class' => 'btn-primary'));
echo form_button('test_template', $locale['437'], $locale['437'], array('class' => 'btn-default'));
echo form_button('reset', $locale['440'], $locale['440'], array('class' => 'btn-default'));

echo closeform();
echo closetabbody();
echo closetab();

closetable();
$loc = $locale['469'];
add_to_jquery("
     $('#template_active').bind('change', function() {
        var value = $(this).select2().val();
        if (value == 1) {
        $('#active_info').show();
        $('#inactive_info').hide();
        } else {
        $('#active_info').hide();
        $('#inactive_info').show();
        }
     });
     $('#template_format').bind('change', function() {
        var value = $(this).select2().val();
        if (value == 'plain') {
        $('#html_info').show();
        $('#html_buttons').hide();
        } else {
        $('#html_info').hide();
        $('#html_buttons').show();
        }
     });
     $('#insertimage').on('change', function(e) {
        insertText('template_content', '<img src=\"".fusion_get_settings('siteurl')."images/' + this.options[this.selectedIndex].value + '\" alt=\"\" style=\"margin:5px;\" align=\"left\" />', 'emailtemplateform'); this.selectedIndex=0;
        $(this).select2({
                formatSelection: color,
                escapeMarkup: function(m) { return m; },
                formatResult: color,
                placeholder:'$loc',
                allowClear:true}).val('');
        });
    ");
require_once THEMES."templates/footer.php";
