<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: admin_layout.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
include LOCALE.LOCALESET."admin/main.php";
header("Content-Type: text/html; charset=".$locale['charset']."");

echo "<!DOCTYPE html>";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>";
echo "<head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."'/>";
echo "<meta name='robots' content='none'/>";
echo "<meta name='googlebot' content='noarchive'/>";

if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'/>\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'/>\n";
    echo "<link href='".INCLUDES."bootstrap/css/bootstrap.min.css' rel='stylesheet' media='screen'/>";

    if ($locale['text-direction'] == 'rtl') {
        echo "<link href='".INCLUDES."bootstrap/css/bootstrap-rtl.min.css' rel='stylesheet' media='screen'/>";
    }
}

// Site Theme CSS
if (stristr($_SERVER['PHP_SELF'], $settings['site_path'].'infusions')) {
    $theme_css = file_exists(THEME.'styles.min.css') ? THEME.'styles.min.css' : THEME.'styles.css';
    echo "<link rel='stylesheet' href='".$theme_css."?v=".filemtime($theme_css)."'>\n";
}

// Default CSS styling which applies to all themes but can be overriden
if (!defined('NO_DEFAULT_CSS')) {
    echo "<link rel='stylesheet' href='".THEMES."templates/default.min.css?v=".filemtime(THEMES.'templates/default.min.css')."'>\n";
}

// Entypo
if ($settings['entypo'] || defined('ENTYPO')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
}

if ($settings['fontawesome'] || defined('FONTAWESOME')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css' type='text/css'/>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/v4-shims.min.css' type='text/css'/>\n";
}

// Admin Panel Theme CSS
$admin_theme_css = file_exists(THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.min.css') ? THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.min.css' : THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.css';
echo "<link rel='stylesheet' href='".$admin_theme_css."?v=".filemtime($admin_theme_css)."'/>\n";

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscripts/jscript.min.js'></script>\n";

if ($settings['tinymce_enabled'] == 1) {
    echo '<script src="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.js"></script>';
    echo '<link rel="stylesheet" href="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.css">';
    echo '<script src="'.INCLUDES.'elFinder/js/elfinder.min.js"></script>';
    echo '<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/elfinder.min.css">';
    echo '<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/theme.css">';
    echo "<script src='".INCLUDES."jscripts/tinymce/tinymce.min.js'></script>";
    echo "<script src='".INCLUDES."elFinder/js//tinymceElfinder.min.js'></script>";

    echo "<style type='text/css'>.mceIframeContainer iframe{width:100%!important;background-color: #000;}</style>\n";
    echo "<script type='text/javascript'>
        const mceElf = new tinymceElfinder({
            // connector URL (Set your connector)
            url: '".fusion_get_settings('siteurl')."includes/elFinder/php/connector.php".fusion_get_aidlink()."',
            // upload target folder hash for this tinyMCE
            uploadTargetHash: 'l1_lw', // Hash value on elFinder of writable folder
            // elFinder dialog node id
            nodeId: 'elfinder', // Any ID you decide
                ui: ['toolbar', 'tree', 'path', 'stat'],
                uiOptions: {
                    toolbar: [
                        ['home', 'back', 'forward', 'up', 'reload'],
                        ['mkdir', 'mkfile', 'upload'],
                        ['open'],
                        ['copy', 'cut', 'paste', 'rm', 'empty'],
                        ['duplicate', 'rename', 'edit', 'resize', 'chmod'],
                        ['quicklook', 'info'],
                        ['extract', 'archive'],
                        ['search'],
                        ['view', 'sort'],
                        ['preference', 'help']
                    ]
                }
        });

        function advanced() {
            tinymce.init({
                file_picker_callback : mceElf.browser,
                images_upload_handler: mceElf.uploadHandler,
                selector: 'textarea',
                resize: 'both',
                height: 300,
                theme: 'modern',
                branding: false,
                language:'".$locale['tinymce']."',
                plugins: [
                    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars code fullscreen',
                    'insertdatetime media nonbreaking save table contextmenu directionality',
                    'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc help importcss'
                ],
                toolbar1: 'undo redo | styleselect formatselect fontselect fontsizeselect removeformat',
                toolbar2: 'cut copy paste | bold underline italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
                toolbar3: 'link unlink anchor | hr | responsivefilemanager | image media | forecolor backcolor charmap emoticons | codesample | code | preview fullpage | fullscreen',
                menubar: 'edit insert view format table',
                image_advtab: true,
                relative_urls : false,
                remove_script_host : false,
                document_base_url : '".$settings['siteurl']."',
                content_css: [
                    '".(file_exists(THEME."editor.css") ? $settings['siteurl']."themes/".$settings['theme']."/editor.css" : $settings['siteurl']."themes/".$settings['theme']."/styles.css")."',
                ],
                content_style: 'body.mceDefBody {background:#".(isset($settings['tinymce_bgcolor']) ? $settings['tinymce_bgcolor'] : "FFFFFF").";}',
                body_class: 'mceDefBody',
            });
        }

        function simple() {
            tinymce.init({
                selector: 'textarea',
                height: 200,
                menubar: false,
                branding: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table contextmenu paste code'
                ],
                toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                content_css: [
                    '".(file_exists(THEME."editor.css") ? $settings['siteurl']."themes/".$settings['theme']."/editor.css" : $settings['siteurl']."themes/".$settings['theme']."/styles.css")."',
                ],
                content_style: 'body.mceDefBody {background:#".(isset($settings['tinymce_bgcolor']) ? $settings['tinymce_bgcolor'] : "FFFFFF").";}',
                body_class: 'mceDefBody'
            });
        }

        function toggleEditor(id) {
            if (!tinyMCE.get(id))
                tinyMCE.execCommand('mceAddControl', false, id);
            else
                tinyMCE.execCommand('mceRemoveControl', false, id);
        }
    </script>\n";
}
echo "</head>";

/**
 * Constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */

if (!defined("THEME_BODY")) {
    echo "<body>\n";
} else {
    echo THEME_BODY;
}

// Check login, skip infusions.
if (!check_admin_pass('') && !stristr($_SERVER['PHP_SELF'], $settings['site_path']."infusions")) {
    if (empty($userdata['user_admin_password'])) {
        redirect(BASEDIR."edit_profile.php");
    } else {
        render_admin_login();
    }
} else {
    render_admin_panel();
}

// Load Bootstrap javascript
if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/js/bootstrap.min.js'></script>\n";
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-scripts.js'></script>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
// Fix select2 on modal - http://stackoverflow.com/questions/13649459/twitter-bootstrap-multiple-modal-error/15856139#15856139
//$fusion_jquery_tags .= "$.fn.modal.Constructor.prototype.enforceFocus = function () {};";

if (!empty($fusion_jquery_tags)) {
    echo "<script type='text/javascript'>$(function(){".$fusion_jquery_tags."});</script>\n";
}
echo "</body>\n";
echo "</html>";
