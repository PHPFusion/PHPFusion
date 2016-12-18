<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: permalink.php
| Author: Ankur Thakur
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

pageAccess('PL');

require_once THEMES."templates/admin_header.php";

$locale = fusion_get_locale('', array(LOCALE.LOCALESET.'admin/settings.php', LOCALE.LOCALESET.'admin/permalinks.php'));

$settings = fusion_get_settings();

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.fusion_get_aidlink(), 'title' => $locale['428']]);

// Check if mod_rewrite is enabled
$mod_rewrite = FALSE;
if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
    $mod_rewrite = TRUE;
} elseif (getenv('HTTP_MOD_REWRITE') == 'On') {
    $mod_rewrite = TRUE;
} elseif (isset($_SERVER['IIS_UrlRewriteModule'])) {
    $mod_rewrite = TRUE;
} elseif (isset($_SERVER['HTTP_MOD_REWRITE'])) {
    $mod_rewrite = TRUE;
}
define('MOD_REWRITE', $mod_rewrite);

if (!MOD_REWRITE) {
    addNotice('danger', "<i class='fa fa-lg fa-warning m-r-10'></i>".$locale['rewrite_disabled']);
}

$settings_seo = array(
    'site_seo' => fusion_get_settings('site_seo'),
    'normalize_seo' => fusion_get_settings('normalize_seo'),
    'debug_seo' => fusion_get_settings('debug_seo'),
);

if (isset($_POST['savesettings'])) {
    foreach ($settings_seo as $key => $value) {
        $settings_seo[$key] = form_sanitizer($_POST[$key], 0, $key);

        if ($defender->safe()) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_seo[$key]."' WHERE settings_name='".$key."'");
        }
    }

    $htc = "# Force utf-8 charset".PHP_EOL;
    $htc .= "AddDefaultCharset utf-8".PHP_EOL.PHP_EOL;
    $htc .= "# Security".PHP_EOL;
    $htc .= "ServerSignature Off".PHP_EOL.PHP_EOL;
    $htc .= "# Secure htaccess file".PHP_EOL;
    $htc .= "<Files .htaccess>".PHP_EOL;
    $htc .= "order allow,deny".PHP_EOL;
    $htc .= "deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;
    $htc .= "# Protect config.php".PHP_EOL;
    $htc .= "<Files config.php>".PHP_EOL;
    $htc .= "order allow,deny".PHP_EOL;
    $htc .= "deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;
    $htc .= "# Block Nasty Bots".PHP_EOL;
    $htc .= "<IfModule mod_setenvifno.c>".PHP_EOL;
    $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "	Deny from env=HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "</IfModule>".PHP_EOL.PHP_EOL;
    $htc .= "# Disable directory listing".PHP_EOL;
    $htc .= "Options -Indexes".PHP_EOL.PHP_EOL;

    if ($settings_seo['site_seo'] == 1) {
        // Rewrite settings
        $htc .= "Options +SymLinksIfOwnerMatch".PHP_EOL;
        $htc .= "<IfModule mod_rewrite.c>".PHP_EOL;
        $htc .= "	# Let PHP know mod_rewrite is enabled".PHP_EOL;
        $htc .= "	<IfModule mod_env.c>".PHP_EOL;
        $htc .= "		SetEnv MOD_REWRITE On".PHP_EOL;
        $htc .= "	</IfModule>".PHP_EOL;
        $htc .= "	RewriteEngine On".PHP_EOL;
        $htc .= "	RewriteBase ".$settings['site_path'].PHP_EOL;
        $htc .= "	# Fix Apache internal dummy connections from breaking [(site_url)] cache".PHP_EOL;
        $htc .= "	RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]".PHP_EOL;
        $htc .= "	RewriteRule .* - [F,L]".PHP_EOL;
        $htc .= "	# Exclude /assets and /manager directories and images from rewrite rules".PHP_EOL;
        $htc .= "	RewriteRule ^(administration|themes)/*$ - [L]".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_FILENAME} !-f".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_FILENAME} !-d".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_FILENAME} !-l".PHP_EOL;
        $htc .= "	RewriteCond %{REQUEST_URI} !^/(administration|config|index.php)".PHP_EOL;
        $htc .= "	RewriteRule ^(.*?)$ index.php [L]".PHP_EOL;
        $htc .= "</IfModule>".PHP_EOL;

    } else {
        // Error pages
        $htc .= "ErrorDocument 400 ".$settings['site_path']."error.php?code=400".PHP_EOL;
        $htc .= "ErrorDocument 401 ".$settings['site_path']."error.php?code=401".PHP_EOL;
        $htc .= "ErrorDocument 403 ".$settings['site_path']."error.php?code=403".PHP_EOL;
        $htc .= "ErrorDocument 404 ".$settings['site_path']."error.php?code=404".PHP_EOL;
        $htc .= "ErrorDocument 500 ".$settings['site_path']."error.php?code=500".PHP_EOL;
    }

    // Create the .htaccess file
    if (!file_exists(BASEDIR.".htaccess")) {
        if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
            @rename(BASEDIR."_htaccess", BASEDIR.".htaccess");
        } else {
            touch(BASEDIR.".htaccess");
        }
    }

    // Write the contents to .htaccess
    $temp = fopen(BASEDIR.".htaccess", "w");
    if (fwrite($temp, $htc)) {
        fclose($temp);
    }

    if (\defender::safe()) {
        addNotice("success", $locale['900']);
        redirect(FUSION_SELF.$aidlink."&amp;section=pls");
    }
}

if (isset($_POST['savepermalinks'])) {
    $error = 0;

    if (\defender::safe()) {

        if (isset($_POST['permalink']) && is_array($_POST['permalink'])) {
            $permalinks = stripinput($_POST['permalink']);
            foreach ($permalinks as $key => $value) {
                $result = dbquery("UPDATE ".DB_PERMALINK_METHOD." SET pattern_source='".$value."' WHERE pattern_id='".$key."'");
                if (!$result) {
                    $error = 1;
                }
            }
        } else {
            $error = 1;
        }
        if ($error == 0) {
            addNotice("success", $locale['421']);
            redirect(FUSION_SELF.$aidlink);
        } elseif ($error == 1) {
            addNotice("danger", $locale['420']);
            redirect(FUSION_REQUEST); // Required to refresh token
        }

    }
}

if (isset($_GET['enable']) && file_exists(INCLUDES."rewrites/".stripinput($_GET['enable'])."_rewrite_include.php")) {

    $rewrite_name = stripinput($_GET['enable']);

    include INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";

    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        include LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
    }

    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
    }

    $rows = dbcount("(rewrite_id)", DB_PERMALINK_REWRITE, "rewrite_name='".$rewrite_name."'");
    // If the Rewrite doesn't already exist
    if ($rows == 0) {
        $error = 0;
        $result = dbquery("INSERT INTO ".DB_PERMALINK_REWRITE." (rewrite_name) VALUES ('".$rewrite_name."')");
        if (!$result) {
            $error = 1;
        }
        $last_insert_id = dblastid();
        if (isset($pattern) && is_array($pattern)) {
            foreach ($pattern as $source => $target) {
                $result = dbquery("INSERT INTO ".DB_PERMALINK_METHOD." (pattern_type, pattern_source, pattern_target, pattern_cat) VALUES ('".$last_insert_id."', '".$source."', '".$target."', 'normal')");
                if (!$result) {
                    $error = 1;
                }
            }
        }
        if (isset($alias_pattern) && is_array($alias_pattern)) {
            foreach ($alias_pattern as $source => $target) {
                $result = dbquery("INSERT INTO ".DB_PERMALINK_METHOD." (pattern_type, pattern_source, pattern_target, pattern_cat) VALUES ('".$last_insert_id."', '".$source."', '".$target."', 'alias')");
                if (!$result) {
                    $error = 1;
                }
            }
        }
        if ($error == 0) {
            addNotice("success", sprintf($locale['424'], $rewrite_name));
        } elseif ($error == 1) {
            addNotice("danger", $locale['420']);
        }
    } else {
        addNotice("warning", sprintf($locale['425'], $rewrite_name));
    }
    redirect(FUSION_SELF.$aidlink."&amp;error=0&amp;section=pl2");

} elseif (isset($_GET['disable'])) {

    $rewrite_name = stripinput($_GET['disable']);
    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        include LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
    }
    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
    }

    // Delete Data

    $rewrite_id = dbarray(dbquery("SELECT rewrite_id FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name='".$rewrite_name."' LIMIT 1"));
    $result = dbquery("DELETE FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_id=".$rewrite_id['rewrite_id']);
    $result = dbquery("DELETE FROM ".DB_PERMALINK_METHOD." WHERE pattern_type=".$rewrite_id['rewrite_id']);

    addNotice("success", sprintf($locale['426'], $rewrite_name));
    redirect(FUSION_SELF.$aidlink."&amp;error=0&amp;section=pl");

} elseif (isset($_GET['reinstall'])) {

    /**
     * Delete Data (Copied from Disable)
     */
    $error = 0;
    $rewrite_name = stripinput($_GET['reinstall']);

    include INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";

    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        include LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
    }
    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
    }

    $rewrite_query = dbquery("SELECT rewrite_id FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name='".$rewrite_name."' LIMIT 1");

    if (dbrows($rewrite_query) > 0) {

        $rewrite_id = dbarray(dbquery("SELECT rewrite_id FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name='".$rewrite_name."' LIMIT 1"));

        $result = dbquery("DELETE FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_id=".$rewrite_id['rewrite_id']);

        $result = dbquery("DELETE FROM ".DB_PERMALINK_METHOD." WHERE pattern_type=".$rewrite_id['rewrite_id']);

    }

    /**
     * Reinsert Data (Copied from Enable)
     */


    $result = dbquery("INSERT INTO ".DB_PERMALINK_REWRITE." (rewrite_name) VALUES ('".$rewrite_name."')");
    if (!$result) {
        $error = 1;
    }
    $last_insert_id = dblastid();

    if (isset($pattern) && is_array($pattern)) {

        foreach ($pattern as $source => $target) {

            $result = dbquery("INSERT INTO ".DB_PERMALINK_METHOD." (pattern_type, pattern_source, pattern_target, pattern_cat) VALUES ('".$last_insert_id."', '".$source."', '".$target."', 'normal')");
            if (!$result) {
                $error = 1;
            }
        }
    }

    if (isset($alias_pattern) && is_array($alias_pattern)) {
        foreach ($alias_pattern as $source => $target) {
            $result = dbquery("INSERT INTO ".DB_PERMALINK_METHOD." (pattern_type, pattern_source, pattern_target, pattern_cat) VALUES ('".$last_insert_id."', '".$source."', '".$target."', 'alias')");
            if (!$result) {
                $error = 1;
            }
        }
    }
    if ($error == 0) {
        addNotice("success", sprintf($locale['424'], $permalink_name));
    } elseif ($error == 1) {
        addNotice("danger", $locale['420']);
    }
    redirect(FUSION_SELF.$aidlink."&amp;error=0");
}

$available_rewrites = array();

$enabled_rewrites = array();

if ($temp = opendir(INCLUDES."rewrites/")) {
    while (FALSE !== ($file = readdir($temp))) {
        if (!in_array($file, array("..", ".", "index.php")) && !is_dir(INCLUDES."rewrites/".$file)) {
            if (preg_match("/_rewrite_include\.php$/i", $file)) {
                $rewrite_name = str_replace("_rewrite_include.php", "", $file);
                $available_rewrites[] = $rewrite_name;
                unset($rewrite_name);
            }
        }
    }
    closedir($temp);
}
sort($available_rewrites);


$default_section = "pl";
$allowed_sections = array($default_section => TRUE, "pls" => TRUE, "pl2" => TRUE);

$_GET['section'] = isset($_GET['section']) && isset($allowed_sections[$_GET['section']]) ? $_GET['section'] : $default_section;

$edit_name = FALSE;
if (isset($_GET['edit']) && file_exists(INCLUDES."rewrites/".stripinput($_GET['edit'])."_rewrite_include.php")) {
    $rewrite_name = stripinput($_GET['edit']);
    $permalink_name = "";
    $driver = array();
    include INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";
    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        include LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
    }
    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
    }
    $rows = dbcount("(rewrite_id)", DB_PERMALINK_REWRITE, "rewrite_name='".$rewrite_name."'");
    if ($rows > 0) {
        $result = dbquery("SELECT p.* FROM ".DB_PERMALINK_REWRITE." r
                            INNER JOIN ".DB_PERMALINK_METHOD." p ON r.rewrite_id=p.pattern_type
                            WHERE r.rewrite_name='".$rewrite_name."'");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $driver[] = $data;
            }
            $edit_name = sprintf($locale['405'], $permalink_name);
        } else {
            addNotice("danger", sprintf($locale['422'], $permalink_name));
            redirect(FUSION_SELF.$aidlink);
        }
    } else {
        addNotice('danger', $locale['423']);
        redirect(FUSION_SELF.$aidlink);
    }
} else {
    $result = dbquery("SELECT * FROM ".DB_PERMALINK_REWRITE." ORDER BY rewrite_name ASC");
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $permalink[] = $data;
            $enabled_rewrites[] = $data['rewrite_name'];
        }
    }
}

$tab['title'][] = $edit_name == TRUE ? $edit_name : $locale['400'];
$tab['id'][] = $default_section;
$tab['icon'][] = "";

$tab['title'][] = $locale['401'];
$tab['id'][] = "pl2";
$tab['icon'][] = "";

$tab['title'][] = $locale['401a'];
$tab['id'][] = "pls";
$tab['icon'][] = "";

opentable($locale['428']);
echo "<div class='well'>\n";
echo $locale['415'];
echo "</div>\n";

echo opentab($tab, $_GET['section'], "permalinkTab", TRUE, "nav-tabs m-t-20 m-b-20");

switch ($_GET['section']) {
    case "pl":
        // edit
        if (!empty($edit_name) && !empty($driver)) {

            echo openform('editpatterns', 'post', FUSION_REQUEST);

            ob_start();
            echo openmodal("permalinkHelper", $locale['408'], array("button_id" => "pButton"));
            if (!empty($regex)) {
                echo "<table class='table table-responsive table-striped'>\n";
                foreach ($regex as $key => $values) {
                    echo "<tr>\n";
                    echo "<td>".$key."</td>\n";
                    echo "<td>".$values."</td>\n";
                    echo "<td>\n";
                    echo(isset($permalink_tags_desc[$key]) ? $permalink_tags_desc[$key] : $locale['na']);
                    echo "</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            }
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();

            echo "<div class='text-right display-block'>\n";
            echo form_button("pButton", $locale['help'], $locale['help'], array("input_id" => "pButton", "type" => "button"));
            echo form_button("savepermalinks", $locale['save_changes'], $locale['413'],
                             array("class" => "m-l-10 btn-primary", "input_id" => "save_top"));
            echo "</div>\n";

            // Driver Rules Installed
            echo "<h4>\n".$locale['409']."</h4>\n";
            $i = 1;
            foreach ($driver as $data) {

                echo "<div class='list-group-item m-b-20'>\n";
                $source = preg_replace("/%(.*?)%/i", "<kbd class='m-2'>%$1%</kbd>", $data['pattern_source']);
                $target = preg_replace("/%(.*?)%/i", "<kbd class='m-2'>%$1%</kbd>", $data['pattern_target']);
                echo "<p class='m-t-10 m-b-10'>
                <label class='label' style='background:#ddd; color: #000; font-weight:normal; font-size: 1rem;'>
                ".$target."\n</label>\n";
                echo "</p>\n";
                // new text input
                echo form_text("permalink[".$data['pattern_id']."]",
                               "",
                               $data['pattern_source'],
                               array(
                                   "prepend_value" => fusion_get_settings("siteurl"),
                                   "inline" => TRUE,
                                   "class" => "m-b-0",
                               )
                );
                echo "</div>\n";
                $i++;
            }
            echo form_button("savepermalinks", $locale['save_changes'], $locale['413'], array("class" => "btn-primary m-b-20"));
            echo closeform();

        } else {

            echo "<table class='table table-responsive table-hover table-striped m-t-20'>\n";

            if (!empty($permalink)) {
                echo "<tr>\n";
                echo "<th width='1%' style='white-space:nowrap'>".$locale['402']."</th>\n";
                echo "<th style='white-space:nowrap'><strong>".$locale['403']."</th>\n";
                echo "<th width='1%' style='white-space:nowrap'>".$locale['404']."</th>\n";
                echo "</tr>\n";

                foreach ($permalink as $data) {
                    echo "<tr>\n";
                    if (!file_exists(INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_include.php") || !file_exists(INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_info.php") || !file_exists(LOCALE.LOCALESET."permalinks/".$data['rewrite_name'].".php")) {
                        echo "<td colspan='2'><strong>".$locale['411'].":</strong> ".sprintf($locale['412'], $data['rewrite_name'])."</td>\n";
                    } else {
                        include LOCALE.LOCALESET."permalinks/".$data['rewrite_name'].".php";
                        include INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_include.php";
                        include INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_info.php";
                        echo "<td width='15%'><strong>".$permalink_name."</strong></td>\n";
                        echo "<td>".$permalink_desc."</td>\n";
                    }
                    echo "<td style='white-space:nowrap'>\n";
                    echo "<a href='".FUSION_SELF.$aidlink."&amp;reinstall=".$data['rewrite_name']."'>".$locale['404d']."</a>\n";
                    echo "- <a href='".FUSION_SELF.$aidlink."&amp;edit=".$data['rewrite_name']."'>".$locale['404c']."</a>\n";
                    echo "- <a href='".FUSION_SELF.$aidlink."&amp;disable=".$data['rewrite_name']."'>".$locale['404b']."</a></td>\n";
                    echo "</tr>\n";
                }
            } else {
                echo "<tr><td class='text-center'>".$locale['427']."</td>\n</tr>\n";
            }
            echo "</table>\n";

        }
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.FUSION_REQUEST, 'title' => $locale['400']]);
        break;

    case "pl2":
        echo "<table class='table table-responsive table-hover table-striped m-t-20'>\n<tbody>\n<tr>\n";
        if (count($available_rewrites) != count($enabled_rewrites)) {
            echo "<tr>\n";
            echo "<th width='1%' style='white-space:nowrap'>".$locale['402']."</td>\n";
            echo "<th style='white-space:nowrap'><strong>".$locale['403']."</td>\n";
            echo "<th width='1%' style='white-space:nowrap'>".$locale['404']."</td>\n";
            echo "</tr>\n";
            $k = 0;
            foreach ($available_rewrites as $available_rewrite) {
                if (!in_array($available_rewrite, $enabled_rewrites)) {
                    if (file_exists(INCLUDES."rewrites/".$available_rewrite."_rewrite_info.php") && file_exists(LOCALE.LOCALESET."permalinks/".$available_rewrite.".php")) {
                        include LOCALE.LOCALESET."permalinks/".$available_rewrite.".php";
                        include INCLUDES."rewrites/".$available_rewrite."_rewrite_info.php";
                        echo "<tr>\n";
                        echo "<td width='15%' style='white-space:nowrap'><strong>".$permalink_name."</strong></td>\n";
                        echo "<td style='white-space:nowrap'>".$permalink_desc."</td>\n";
                        echo "<td width='1%' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;enable=".$available_rewrite."'>".$locale['404a']."</td>\n";
                        echo "</tr>\n";
                    }
                }
            }
        }
        echo "</tbody>\n</table>\n";
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.FUSION_REQUEST, 'title' => $locale['401']]);
        break;

    case "pls":
        echo openform('settingsseo', 'post', FUSION_REQUEST);
        echo "<div class='well m-t-20'><i class='fa fa-lg fa-exclamation-circle m-r-10'></i>".$locale['seo_htc_warning']."</div>";
        echo "<div class='panel panel-default m-t-20'>\n<div class='panel-body'>\n";
        $opts = array('0' => $locale['disable'], '1' => $locale['enable']);
        echo form_select('site_seo', $locale['438'], $settings_seo['site_seo'], array("options" => $opts, 'inline' => 1));
        echo form_select('normalize_seo', $locale['439'], $settings_seo['normalize_seo'], array("options" => $opts, 'inline' => 1));
        echo form_select('debug_seo', $locale['440'], $settings_seo['debug_seo'], array("options" => $opts, 'inline' => 1));
        echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-primary', 'inline' => 1));
        echo "</div></div>\n";
        echo closeform();
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.FUSION_REQUEST, 'title' => $locale['401a']]);
        break;
}

echo closetab();
closetable();
require_once THEMES."templates/footer.php";