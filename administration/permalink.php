<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: permalink.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

pageAccess('PL');
$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/settings.php', LOCALE.LOCALESET.'admin/permalinks.php']);
$settings = fusion_get_settings();
$aidlink = fusion_get_aidlink();

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.$aidlink, 'title' => $locale['PL_428']]);

// Check if mod_rewrite is enabled
$mod_rewrite = FALSE;
if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
    $mod_rewrite = TRUE;
} else if (getenv('HTTP_MOD_REWRITE') == 'On') {
    $mod_rewrite = TRUE;
} else if (isset($_SERVER['IIS_UrlRewriteModule'])) {
    $mod_rewrite = TRUE;
} else if (isset($_SERVER['HTTP_MOD_REWRITE'])) {
    $mod_rewrite = TRUE;
}
define('MOD_REWRITE', $mod_rewrite);
if (!MOD_REWRITE) {
    addNotice("info", $locale['rewrite_disabled']);
}

$settings_seo = [
    'site_seo'      => $settings['site_seo'],
    'normalize_seo' => $settings['normalize_seo'],
    'debug_seo'     => $settings['debug_seo']
];

$available_rewrites = [];
$enabled_rewrites = [];
$rewrite_registers = [];
$permalink_name = '';

// Fetch Core Drivers
$file_regex = "/_rewrite_include\.php$/i";
$rewrite_dir = INCLUDES."rewrites/";
$rewrite_files = makefilelist($rewrite_dir, ".|..|index.php", TRUE, "files");
if (!empty($rewrite_files)) {
    foreach ($rewrite_files as $file_to_check) {
        if (preg_match($file_regex, $file_to_check)) {
            $rewrite_name = str_replace("_rewrite_include.php", "", $file_to_check);
            $available_rewrites[] = $rewrite_name;
            $driver_file = INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";
            $info_file = INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
            $locale_file = LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
            $rewrite_registers[$rewrite_name] = [];
            $rewrite_registers[$rewrite_name]['driver_path'] = $driver_file;
            if (file_exists($info_file)) {
                $rewrite_registers[$rewrite_name]['info_path'] = $info_file;
            }
            if (file_exists($locale_file)) {
                $rewrite_registers[$rewrite_name]['locale_path'] = $locale_file;
            }
            // de-register if info and locale is missing
            if (!isset($rewrite_registers[$rewrite_name]['info_path']) || !isset($rewrite_registers[$rewrite_name]['locale_path'])) {
                unset($rewrite_registers[$rewrite_name]);
            }
            unset($rewrite_name);
        }
    }
}

// Check Addons Drivers
$inf_list = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
if (!empty($inf_list)) {
    foreach ($inf_list as $infusions_to_check) {
        if (is_dir(INFUSIONS.$infusions_to_check.'/permalinks/')) {
            $rewrite_files = makefilelist(INFUSIONS.$infusions_to_check.'/permalinks/', ".|..|index.php", TRUE, "files");
            if (!empty($rewrite_files)) {
                foreach ($rewrite_files as $file_to_check) {
                    if (preg_match($file_regex, $file_to_check)) {
                        $rewrite_name = str_replace("_rewrite_include.php", "", $file_to_check);
                        $available_rewrites[] = $rewrite_name;
                        $driver_file = INFUSIONS.$infusions_to_check."/permalinks/".$rewrite_name."_rewrite_include.php";
                        $info_file = INFUSIONS.$infusions_to_check."/permalinks/".$rewrite_name."_rewrite_info.php";

                        if (file_exists(INFUSIONS.$infusions_to_check."/locale/".LANGUAGE."/permalinks/".$rewrite_name.".php")) {
                            $locale_file = INFUSIONS.$infusions_to_check."/locale/".LANGUAGE."/permalinks/".$rewrite_name.".php";
                        } else {
                            $locale_file = INFUSIONS.$infusions_to_check."/locale/English/permalinks/".$rewrite_name.".php";
                        }

                        $rewrite_registers[$rewrite_name] = [];
                        $rewrite_registers[$rewrite_name]['driver_path'] = $driver_file;
                        if (file_exists($info_file)) {
                            $rewrite_registers[$rewrite_name]['info_path'] = $info_file;
                        }
                        if (file_exists($locale_file)) {
                            $rewrite_registers[$rewrite_name]['locale_path'] = $locale_file;
                        }
                        // de-register if info and locale is missing
                        if (!isset($rewrite_registers[$rewrite_name]['info_path']) || !isset($rewrite_registers[$rewrite_name]['locale_path'])) {
                            unset($rewrite_registers[$rewrite_name]);
                        }
                        unset($rewrite_name);
                    }
                }
            }
        }
    }
}
sort($available_rewrites);

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.$aidlink);
}

if (isset($_POST['savesettings'])) {
    foreach ($settings_seo as $key => $value) {
        $settings_seo[$key] = form_sanitizer($_POST[$key], 0, $key);
        if (\defender::safe()) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:value WHERE settings_name=:name", [':value' => $settings_seo[$key], ':name' => $key]);
        }
    }

    if (\defender::safe()) {
        require_once(INCLUDES.'htaccess_include.php');
        write_htaccess();
        addNotice('success', $locale['900']);
    }
    redirect(clean_request('section=pls', [], FALSE));
}

if (isset($_POST['savepermalinks'])) {
    $error = 0;

    if (\defender::safe()) {
        if (isset($_POST['permalink']) && is_array($_POST['permalink'])) {
            $permalinks = stripinput($_POST['permalink']);
            foreach ($permalinks as $key => $value) {
                $result = dbquery("UPDATE ".DB_PERMALINK_METHOD." SET pattern_source=:source WHERE pattern_id=:id", [':source' => $value, ':id' => $key]);
                if (!$result) {
                    $error = 1;
                }
            }
        } else {
            $error = 1;
        }
        if ($error == 0) {
            addNotice('success', $locale['PL_421']);
        } else if ($error == 1) {
            addNotice('danger', $locale['PL_420']);
        }
    }
    redirect(clean_request('section=pl', ['edit'], FALSE));
}

if (isset($_GET['enable']) && !empty($rewrite_registers[$_GET['enable']])) {
    $rewrite_name = stripinput($_GET['enable']);
    $locale = fusion_get_locale("", $rewrite_registers[$rewrite_name]['locale_path']);
    include $rewrite_registers[$rewrite_name]['driver_path'];
    include $rewrite_registers[$rewrite_name]['info_path'];

    $rows = dbcount("(rewrite_id)", DB_PERMALINK_REWRITE, "rewrite_name=:rwname", [':rwname' => $rewrite_name]);
    // If the Rewrite doesn't already exist
    if ($rows == 0) {
        $error = 0;
        $result = dbquery("INSERT INTO ".DB_PERMALINK_REWRITE." (rewrite_name) VALUES (:rwname)", [':rwname' => $rewrite_name]);
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
            addNotice('success', sprintf($locale['PL_424'], $permalink_name));
        } else if ($error == 1) {
            addNotice('danger', $locale['PL_420']);
        }
    } else {
        addNotice('warning', sprintf($locale['PL_425'], $permalink_name));
    }
    redirect(clean_request('', ['enable', 'section'], FALSE));
} else if (isset($_GET['disable'])) {
    $rewrite_name = stripinput($_GET['disable']);

    // Delete Data
    $rewrite_id = dbarray(dbquery("SELECT rewrite_id FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name=:rewritename LIMIT 1", [':rewritename' => $rewrite_name]));
    $result = dbquery("DELETE FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_id=:rewriteid", [':rewriteid' => $rewrite_id['rewrite_id']]);
    $result = dbquery("DELETE FROM ".DB_PERMALINK_METHOD." WHERE pattern_type=:rewritetype", [':rewritetype' => $rewrite_id['rewrite_id']]);

    // This file might not exist, because user has deleted it. It's not required to have such file.
    if (!empty($rewrite_registers[$_GET['disable']])) {
        $locale = fusion_get_locale("", $rewrite_registers[$rewrite_name]['locale_path']);
        include $rewrite_registers[$rewrite_name]['driver_path'];
        include $rewrite_registers[$rewrite_name]['info_path'];
    }

    $permalink_name = !empty($permalink_name) ? $permalink_name : ucfirst($rewrite_name);

    addNotice('success', sprintf($locale['PL_426'], $permalink_name));
    redirect(clean_request('', ['disable', 'section'], FALSE));
} else if (isset($_GET['reinstall']) && !empty($rewrite_registers[$_GET['reinstall']])) {
    /**
     * Delete Data (Copied from Disable)
     */
    $error = 0;
    $rewrite_name = stripinput($_GET['reinstall']);
    $locale = fusion_get_locale("", $rewrite_registers[$rewrite_name]['locale_path']);
    include $rewrite_registers[$rewrite_name]['driver_path'];
    include $rewrite_registers[$rewrite_name]['info_path'];

    $rewrite_query = dbquery("SELECT rewrite_id FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name=:rewritename LIMIT 1", [':rewritename' => $rewrite_name]);
    if (dbrows($rewrite_query)) {
        $rewrite_id = dbarray($rewrite_query);
        $result = dbquery("DELETE FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_id=:rewriteid", [':rewriteid' => $rewrite_id['rewrite_id']]);
        $result = dbquery("DELETE FROM ".DB_PERMALINK_METHOD." WHERE pattern_type=:patterntype", [':patterntype' => $rewrite_id['rewrite_id']]);
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
        addNotice('success', sprintf($locale['PL_424'], $permalink_name));
    } else if ($error == 1) {
        addNotice('danger', $locale['PL_420']);
    }
    redirect(clean_request('', ['reinstall', 'section'], FALSE));
}


$allowed_sections = ['pl', 'pls'];
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_sections) ? $_GET['section'] : $allowed_sections[0];
$edit_name = FALSE;

switch ($_GET['section']) {
    case "pl":
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.$aidlink, 'title' => $locale['400']]);
        break;
    case "pls":
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.$aidlink, 'title' => $locale['PL_401a']]);
        break;
    default:
        break;
}

if (isset($_GET['edit']) && !empty($rewrite_registers[$_GET['edit']])) {
    $rewrite_name = stripinput($_GET['edit']);

    $locale = fusion_get_locale("", $rewrite_registers[$rewrite_name]['locale_path']);
    include $rewrite_registers[$rewrite_name]['driver_path'];
    include $rewrite_registers[$rewrite_name]['info_path'];

    $driver = [];
    $rows = dbcount("(rewrite_id)", DB_PERMALINK_REWRITE, "rewrite_name=:rewritename", [':rewritename' => $rewrite_name]);
    if ($rows > 0) {
        $result = dbquery("SELECT p.*
            FROM ".DB_PERMALINK_REWRITE." r
            INNER JOIN ".DB_PERMALINK_METHOD." p ON r.rewrite_id=p.pattern_type
            WHERE r.rewrite_name=:rewritename", [':rewritename' => $rewrite_name]);
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $driver[] = $data;
            }
            $edit_name = sprintf($locale['PL_405'], $permalink_name);
        } else {
            addNotice("danger", sprintf($locale['PL_422'], $permalink_name));
            redirect(clean_request('section=pl', ['edit'], FALSE));
        }
    } else {
        addNotice('danger', $locale['PL_423']);
        redirect(clean_request('section=pl', ['edit'], FALSE));
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

$tab['title'][] = $edit_name == TRUE ? $edit_name : $locale['PL_400'];
$tab['id'][] = "pl";
$tab['icon'][] = "";

$tab['title'][] = $locale['PL_401a'];
$tab['id'][] = "pls";
$tab['icon'][] = "";

opentable($locale['PL_428']);
echo opentab($tab, $_GET['section'], 'permalinkTab', TRUE, 'nav-tabs m-b-15', 'section');
switch ($_GET['section']) {
    case "pl":
        echo "<p>".$locale['PL_415']."</p>\n";
        if (!empty($edit_name) && !empty($driver)) {

            echo openform('editpatterns', 'post', FUSION_REQUEST);

            ob_start();
            echo openmodal('permalinkHelper', $locale['PL_408'], ['button_id' => 'pButton']);
            if (!empty($regex)) {
                echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
                foreach ($regex as $key => $values) {
                    echo "<tr>\n";
                    echo "<td>".$key."</td>\n";
                    echo "<td>".$values."</td>\n";
                    echo "<td>".(isset($permalink_tags_desc[$key]) ? $permalink_tags_desc[$key] : $locale['na'])."</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n</div>";
            }
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();

            echo "<div class='text-right display-block'>\n";
            echo form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default', 'input_id' => 'cancel']);
            echo form_button('pButton', $locale['help'], $locale['help'], ['class' => 'btn-success m-l-10', 'input_id' => 'pButton']);
            echo form_button('savepermalinks', $locale['save_changes'], $locale['PL_413'], ['class' => 'btn-primary m-l-10', 'input_id' => 'save_top']);
            echo "</div>\n";

            // Driver Rules Installed
            echo "<h4>".$locale['PL_409']."</h4>\n";
            foreach ($driver as $data) {

                echo "<div class='panel panel-default panel-body m-b-10'>\n";
                $source = preg_replace("/%(.*?)%/i", "<kbd class='m-2'>%$1%</kbd>", $data['pattern_source']);
                $target = preg_replace("/%(.*?)%/i", "<kbd class='m-2'>%$1%</kbd>", $data['pattern_target']);
                echo "<p class='m-b-10'>
                <label class='label' style='background:#ddd; color: #000; font-weight:normal; font-size: 1rem;'>
                ".$target."\n</label>\n";
                echo "</p>\n";
                // new text input
                echo form_text('permalink['.$data['pattern_id'].']', '', $data['pattern_source'], [
                    'prepend_value' => fusion_get_settings('siteurl'),
                    'inline'        => TRUE,
                    'class'         => 'm-b-0'
                ]);
                echo "</div>\n";
            }
            echo form_button('savepermalinks', $locale['save_changes'], $locale['PL_413'], ['class' => 'btn-primary m-b-20']);
            echo closeform();

        } else {
            if (!empty($available_rewrites)) {
                echo "<div class='table-responsive'><table class='table table-hover'>\n";
                echo "<thead><tr>\n";
                echo "<th><strong>".$locale['PL_402']."</strong></th>\n";
                echo "<th><strong>".$locale['PL_403']."</strong></th>\n";
                echo "</tr>\n</thead>";
                echo "<tbody>\n";
                foreach ($available_rewrites as $rewrite_name) {
                    if (!empty($rewrite_registers[$rewrite_name])) {
                        // include file paths
                        $locale = fusion_get_locale("", $rewrite_registers[$rewrite_name]['locale_path']);
                        include $rewrite_registers[$rewrite_name]['driver_path'];
                        include $rewrite_registers[$rewrite_name]['info_path'];

                        $name = (!empty($permalink_name) ? $permalink_name : ucfirst($rewrite_name));
                        $version = (!empty($permalink_version) ? $permalink_version : "1.00");
                        $author = (!empty($permalink_author) ? $permalink_author : "PHP-Fusion Core Developers Team");
                        $description = (!empty($permalink_desc) ? $permalink_desc : sprintf($locale['PL_429'], $permalink_name));
                        $row_class = "";
                        $link = "<a href='".FUSION_SELF.$aidlink."&amp;enable=".$rewrite_name."'>".$locale['PL_404a']."</a>\n";
                        if (in_array($rewrite_name, $enabled_rewrites)) {
                            $row_class = " class='active'";
                            $link = "<a href='".FUSION_SELF.$aidlink."&amp;disable=".$rewrite_name."'>".$locale['PL_404b']."</a><span class='m-l-5 m-r-5'>&middot;</span>";
                            // edit
                            $link .= "<a href='".FUSION_SELF.$aidlink."&amp;edit=".$rewrite_name."'>".$locale['edit']."</a><span class='m-l-5 m-r-5'>&middot;</span>\n";
                            // reinstall
                            $link .= "<a href='".FUSION_SELF.$aidlink."&amp;reinstall=".$rewrite_name."'>".$locale['PL_404d']."</a>\n";
                        }
                        echo "<tr".$row_class.">\n";
                        echo "<td>\n<h4 class='m-b-5'>".$name."</h4>$link</td>\n";
                        echo "<td>\n<div class='spacer-xs'><p>".$description."</p><span>v$version</span> <span>".$locale['by']." $author</span></div></td>\n";
                        echo "</tr>\n";
                    } else {
                        echo "<tr><td colspan='2'><strong>".$locale['PL_411'].":</strong> ".sprintf($locale['412'], $data['rewrite_name'])."</td>\n</tr>";
                    }
                }
                echo "</tbody>\n</table>\n</div>";
            } else {
                echo "<h4 class='text-center spacer-md'>".$locale['PL_427']."</h4>";
            }
        }
        break;
    case "pls":
        echo openform('settingsseo', 'post', FUSION_REQUEST);
        echo "<div class='well m-t-20'><i class='fa fa-lg fa-exclamation-circle m-r-10'></i>".$locale['seo_htc_warning']."</div>";
        $opts = ['0' => $locale['disable'], '1' => $locale['enable']];
        echo form_select('site_seo', $locale['438'], $settings_seo['site_seo'], ['options' => $opts, 'inline' => TRUE]);
        echo form_select('normalize_seo', $locale['439'], $settings_seo['normalize_seo'], ['options' => $opts, 'inline' => TRUE]);
        echo form_select('debug_seo', $locale['440'], $settings_seo['debug_seo'], ['options' => $opts, 'inline' => TRUE]);
        echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
        echo closeform();
        break;
}
echo closetab();
closetable();
require_once THEMES.'templates/footer.php';
