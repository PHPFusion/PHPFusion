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
use \PHPFusion\BreadCrumbs;

require_once THEMES."templates/admin_header.php";

$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/settings.php', LOCALE.LOCALESET.'admin/permalinks.php']);

BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.fusion_get_aidlink(), 'title' => $locale['PL_428']]);

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

$settings_seo = [
    'site_seo'      => fusion_get_settings('site_seo'),
    'normalize_seo' => fusion_get_settings('normalize_seo'),
    'debug_seo'     => fusion_get_settings('debug_seo'),
];

if (isset($_POST['savesettings'])) {
    $settings_seo = [
        'site_seo'      => form_sanitizer($_POST['site_seo'], 0, 'site_seo'),
        'normalize_seo' => form_sanitizer($_POST['normalize_seo'], 0, 'normalize_seo'),
        'debug_seo'     => form_sanitizer($_POST['debug_seo'], 0, 'debug_seo'),
    ];

    if (\defender::safe()) {
        foreach ($settings_seo as $settings_key => $settings_value) {
            $data = [
                'settings_name'  => $settings_key,
                'settings_value' => $settings_value
            ];
            dbquery_insert(DB_SETTINGS, $data, 'update', array('primary_key' => 'settings_name'));
        }
        require_once(INCLUDES.'htaccess_include.php');
        write_htaccess();
        addNotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

if (isset($_POST['savepermalinks'])) {

    if (\defender::safe()) {
        if (isset($_POST['permalink']) && is_array($_POST['permalink'])) {
            $permalinks = stripinput($_POST['permalink']);
            foreach ($permalinks as $key => $value) {
                $result = dbquery("UPDATE ".DB_PERMALINK_METHOD." SET pattern_source='".$value."' WHERE pattern_id='".$key."'");
                if (!$result) {
                    addNotice("danger", $locale['PL_420']);
                    redirect(FUSION_REQUEST); // Required to refresh token
                }
            }
        }
        addNotice("success", $locale['PL_421']);
        redirect(clean_request('', ['edit'], FALSE));
    }
    addNotice("danger", $locale['PL_420']);
    redirect(FUSION_REQUEST); // Required to refresh token
}

if (isset($_GET['enable']) && file_exists(INCLUDES."rewrites/".stripinput($_GET['enable'])."_rewrite_include.php")) {

    $rewrite_name = stripinput($_GET['enable']);

    include_once INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";

    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."permalinks/".$rewrite_name.".php");
    }

    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include_once INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
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
            addNotice("success", sprintf($locale['PL_424'], $rewrite_name));
        } elseif ($error == 1) {
            addNotice("danger", $locale['PL_420']);
        }
    } else {
        addNotice("warning", sprintf($locale['PL_425'], $rewrite_name));
    }
    redirect(clean_request('', ['enable'], FALSE));

} elseif (isset($_GET['disable'])) {

    $rewrite_name = stripinput($_GET['disable']);
    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."permalinks/".$rewrite_name.".php");
    }
    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include_once INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
    }
    // Delete Data
    $rewrite_id = dbarray(dbquery("SELECT rewrite_id FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name='".$rewrite_name."' LIMIT 1"));
    $result = dbquery("DELETE FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_id=".$rewrite_id['rewrite_id']);
    $result = dbquery("DELETE FROM ".DB_PERMALINK_METHOD." WHERE pattern_type=".$rewrite_id['rewrite_id']);

    addNotice("success", sprintf($locale['PL_426'], $rewrite_name));
    redirect(clean_request('', ['disable'], FALSE));

} elseif (isset($_GET['reinstall'])) {
    /**
     * Delete Data (Copied from Disable)
     */
    $error = 0;
    $rewrite_name = stripinput($_GET['reinstall']);

    include_once INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";

    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."permalinks/".$rewrite_name.".php");
    }
    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include_once INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
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
        addNotice("success", sprintf($locale['PL_424'], $permalink_name));
    } elseif ($error == 1) {
        addNotice("danger", $locale['PL_420']);
    }
    redirect(clean_request('', ['reinstall'], FALSE));
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

switch ($_GET['section']) {
    case "pl":
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.$aidlink, 'title' => $locale['PL_400']]);
        break;
    case "pl2":
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.$aidlink, 'title' => $locale['PL_401']]);
        break;
    case "pls":
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'permalink.php'.$aidlink, 'title' => $locale['PL_401a']]);
        break;
}

$edit_name = FALSE;

if (isset($_GET['edit']) && file_exists(INCLUDES."rewrites/".stripinput($_GET['edit'])."_rewrite_include.php")) {
    $rewrite_name = stripinput($_GET['edit']);
    $permalink_name = "";
    $driver = array();
    include_once INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";
    if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."permalinks/".$rewrite_name.".php");
    }
    if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
        include_once INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
    }

    $result = dbquery("SELECT p.*
                        FROM ".DB_PERMALINK_REWRITE." r
                        INNER JOIN ".DB_PERMALINK_METHOD." p ON r.rewrite_id=p.pattern_type
                        WHERE r.rewrite_name='".$rewrite_name."'");
    $rows = dbrows($result);
    if ($rows > 0) {
        while ($data = dbarray($result)) {
             $driver[] = $data;
            }
            $edit_name = sprintf($locale['PL_405'], $permalink_name);
    } else {
        addNotice('danger', sprintf($locale['PL_422'], $permalink_name));
        redirect(clean_request('section', ['edit'], FALSE));
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
$tab['id'][] = $default_section;
$tab['icon'][] = "";

$tab['title'][] = $locale['PL_401'];
$tab['id'][] = "pl2";
$tab['icon'][] = "";

$tab['title'][] = $locale['PL_401a'];
$tab['id'][] = "pls";
$tab['icon'][] = "";

opentable($locale['PL_428']);
echo "<div class='well'>".$locale['PL_415']."</div>\n";

echo opentab($tab, $_GET['section'], "permalinkTab", TRUE, "nav-tabs m-t-20");
switch ($_GET['section']) {
    case "pl":
        // edit
        if (!empty($edit_name) && !empty($driver)) {

            echo openform('editpatterns', 'post', FUSION_REQUEST);

            ob_start();
            echo openmodal('permalinkHelper', $locale['PL_408'], ['button_id' => 'pButton']);
            if (!empty($regex)) {
                echo "<div class='table-responsive'><table class='table table-striped'>\n";
                foreach ($regex as $key => $values) {
                    echo "<tr>
                            <td>".$key."</td>
                            <td>".$values."</td>
                            <td>".(isset($permalink_tags_desc[$key]) ? $permalink_tags_desc[$key] : $locale['na'])."</td>
                        </tr>\n";
                }
                echo "</table>\n</div>";
            }
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();

            echo "<div class='text-right display-block'>\n";
            echo form_button('pButton', $locale['help'], $locale['help'], ['input_id' => 'pButton', 'type' => 'button']);
            echo form_button('savepermalinks', $locale['save_changes'], $locale['PL_413'], ['class' => 'btn-primary', 'input_id' => 'save_top']);
            echo "</div>\n";

            // Driver Rules Installed
            openside($locale['PL_409']);
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
                echo form_text("permalink[".$data['pattern_id']."]", "", $data['pattern_source'], [
                               'prepend_value' => fusion_get_settings('siteurl'),
                               'inline'        => TRUE,
                               'class'         => 'm-b-0',
                               ]);

                echo "</div>\n";
                $i++;
            }
            echo form_button('savepermalinks', $locale['save_changes'], $locale['PL_413'], ['class' => 'btn-primary']);
            echo closeform();
            closeside();
        } else {

            echo "<div class='table-responsive'><table class='table table-hover table-striped m-t-20'>\n";

            if (!empty($permalink)) {
                echo "<thead>
                        <tr>
                            <th>".$locale['PL_402']."</th>
                            <th>".$locale['PL_403']."</th>
                            <th>".$locale['PL_404']."</th>
                        </tr>
                    </thead>
                    <tbody>";

                foreach ($permalink as $data) {
                    echo "<tr id='link-".$data['rewrite_id']."' data-id=".$data['rewrite_id']."'>";
                    if (!file_exists(INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_include.php") || !file_exists(INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_info.php") || !file_exists(LOCALE.LOCALESET."permalinks/".$data['rewrite_name'].".php")) {
                        echo "<td colspan='3'><strong>".$locale['411'].":</strong> ".sprintf($locale['412'], $data['rewrite_name'])."</td>\n";
                    } else {
                        $locale = fusion_get_locale('', LOCALE.LOCALESET."permalinks/".$data['rewrite_name'].".php");
                        include_once INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_include.php";
                        include_once INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_info.php";
                        echo "<td><strong>".$permalink_name."</strong></td>
                            <td>".$permalink_desc."</td>\n";
                    }
                    echo "<td>
                            <a class='btn btn-default' href='".FUSION_REQUEST."&amp;reinstall=".$data['rewrite_name']."'>".$locale['PL_404d']."</a>
                            <a class='btn btn-default' href='".FUSION_REQUEST."&amp;edit=".$data['rewrite_name']."'>".$locale['PL_404c']."</a>
                            <a class='btn btn-default' href='".FUSION_REQUEST."&amp;disable=".$data['rewrite_name']."'>".$locale['PL_404b']."</a>
                            </td>
                        </tr>\n";
                }
            } else {
                echo "<tr><td colspan='3' class='text-center'>".$locale['PL_427']."</td>\n</tr>\n";
            }
            echo "</tbody></table>\n</div>";

        }
        break;
    case "pl2":
        if (count($available_rewrites) != count($enabled_rewrites)) {
        echo "<div class='table-responsive'><table class='table table-hover table-striped m-t-20'>
                <thead>
                    <tr>
                        <th>".$locale['PL_402']."</th>
                        <th>".$locale['PL_403']."</th>
                        <th>".$locale['PL_404']."</th>
                    </tr>
                </thead>
            <tbody>\n";
            $k = 0;
            foreach ($available_rewrites as $available_rewrite) {
                if (!in_array($available_rewrite, $enabled_rewrites)) {
                    if (file_exists(INCLUDES."rewrites/".$available_rewrite."_rewrite_info.php") && file_exists(LOCALE.LOCALESET."permalinks/".$available_rewrite.".php")) {
                        $locale = fusion_get_locale('', LOCALE.LOCALESET."permalinks/".$available_rewrite.".php");
                        include_once INCLUDES."rewrites/".$available_rewrite."_rewrite_info.php";
                        echo "<tr>
                                <td><strong>".$permalink_name."</strong></td>
                                <td>".$permalink_desc."</td>
                                <td><a class='btn btn-default' href='".FUSION_REQUEST."&amp;enable=".$available_rewrite."'>".$locale['PL_404a']."</td>
                            </tr>\n";
                    }
                }
            }
        echo "</tbody>\n</table>\n</div>";
        }
        break;
    case "pls":
        echo openform('settingsseo', 'post', FUSION_REQUEST);
        echo "<div class='well'><i class='fa fa-lg fa-exclamation-circle m-r-10'></i>".$locale['seo_htc_warning']."</div>";
        $opts = array('0' => $locale['disable'], '1' => $locale['enable']);
        echo form_select('site_seo', $locale['438'], $settings_seo['site_seo'], ['options' => $opts, 'inline' => TRUE]);
        echo form_select('normalize_seo', $locale['439'], $settings_seo['normalize_seo'], ['options' => $opts, 'inline' => TRUE]);
        echo form_select('debug_seo', $locale['440'], $settings_seo['debug_seo'], ['options' => $opts, 'inline' => TRUE]);
        echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary', 'inline' => TRUE]);
        echo closeform();
        break;
}

echo closetab();
closetable();
require_once THEMES."templates/footer.php";
