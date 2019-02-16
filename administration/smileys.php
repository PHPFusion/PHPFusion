<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: smileys.php
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
pageAccess('SM');
require_once THEMES.'templates/admin_header.php';

class SmileysAdministration {
    private static $locale = [];
    private static $instances = [];

    private $data = [
        'smiley_id'    => 0,
        'smiley_code'  => "",
        'smiley_image' => "",
        'smiley_text'  => "",
    ];

    private $formaction = '';

    public function __construct() {

        $aidlink = fusion_get_aidlink();

        $this->set_locale();

        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($_GET['action']) {
            case 'edit':
                if (isset($_GET['smiley_id'])) {
                    $this->data = self::load_smileys($_GET['smiley_id']);
                    $this->formaction = FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=edit&amp;smiley_id=".$_GET['smiley_id'];
                } else {
                    redirect(FUSION_REQUEST);
                }
                break;
            case 'delete':
                self::delete_smileys($_GET['smiley_id']);
                break;
            default:
                if (isset($_GET['smiley_text'])) {
                    $this->data['smiley_text'] = str_replace(['.gif', '.png', '.jpg', '.svg'], '', $_GET['smiley_text']);
                    $this->data['smiley_image'] = $_GET['smiley_text'];
                    $this->formaction = FUSION_SELF.$aidlink."&amp;section=smiley_form";
                } else {
                    $this->formaction = FUSION_SELF.$aidlink."&amp;section=smiley_form";
                }
                break;
        }

        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'smileys.php'.fusion_get_aidlink(), "title" => self::$locale['SMLY_403']]);
        self::set_smileydb();
    }

    public static function getInstance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }

    private static function set_locale() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/smileys.php");
    }

    private function load_all_smileys() {
        $list = [];
        $result = dbquery("SELECT smiley_id, smiley_code, smiley_image, smiley_text
        FROM ".DB_SMILEYS."
        ORDER BY smiley_text ASC");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $list[] = $data;
            }
        }

        return $list;
    }

    static function load_smileys($id) {
        if (isnum($id)) {
            $result = dbquery("SELECT smiley_id, smiley_code, smiley_image, smiley_text
            FROM ".DB_SMILEYS."
            WHERE smiley_id=:smileyid", [':smileyid' => intval($id)]
            );
            if (dbrows($result) > 0) {
                return dbarray($result);
            }
        }

        return [];
    }

    static function verify_smileys($id) {
        if (isnum($id)) {
            return dbcount("(smiley_id)", DB_SMILEYS, "smiley_id=:smileyid", [':smileyid' => intval($id)]);
        }

        return FALSE;
    }

    private static function delete_smileys($id) {
        if (self::verify_smileys($id)) {
            $data = self::load_smileys($id);
            dbquery("DELETE FROM ".DB_SMILEYS." WHERE smiley_id=:smileyid", [':smileyid' => intval($id)]);
            if (!empty(isset($_GET['inactive']))) {
                if (!empty($data['smiley_image']) && file_exists(IMAGES."smiley/".$data['smiley_image'])) {
                    unlink(IMAGES."smiley/".$data['smiley_image']);
                }
            }
            addNotice('warning', (isset($_GET['inactive']) ? self::$locale['SMLY_412'] : self::$locale['SMLY_413']));
            redirect(clean_request('', ['section=smiley_list', 'aid'], TRUE));
        }
    }

    private function set_smileydb() {
        if (isset($_POST['smiley_save'])) {
            $smiley_code = $_POST['smiley_code'];

            if (QUOTES_GPC) {
                $_POST['smiley_code'] = stripslashes($_POST['smiley_code']);
                $smiley_code = str_replace(["\"", "'", "\\", '\"', "\'", "<", ">"], "", $_POST['smiley_code']);
            }

            if (!empty(isset($_POST['smiley_image']))) {
                $this->data['smiley_image'] = form_sanitizer($_POST['smiley_image'], '', 'smiley_image');
            }

            if (!empty($_FILES['smiley_file']) && is_uploaded_file($_FILES['smiley_file']['tmp_name'])) {

                $upload = form_sanitizer($_FILES['smiley_file'], '', 'smiley_file');
                if ($upload['error'] == 0) {
                    $this->data['smiley_image'] = $upload['image_name'];
                }
            }

            $this->data['smiley_id'] = isset($_POST['smiley_id']) ? form_sanitizer($_POST['smiley_id'], '0', 'smiley_id') : 0;
            $this->data['smiley_code'] = isset($_POST['smiley_code']) ? form_sanitizer($smiley_code, '', 'smiley_code') : '';
            $this->data['smiley_text'] = isset($_POST['smiley_text']) ? form_sanitizer($_POST['smiley_text'], '', 'smiley_text') : '';

            $error = "";
            $error .= empty($this->data['smiley_image']) ? self::$locale['SMLY_418'] : "";
            $error .= dbcount("(smiley_id)", DB_SMILEYS, "smiley_id !=:smileyid AND smiley_code=:smileycode", [':smileyid' => intval($this->data['smiley_id']), ':smileycode' => $this->data['smiley_code']]) ? self::$locale['SMLY_415'] : "";
            $error .= dbcount("(smiley_id)", DB_SMILEYS, "smiley_id !=:smileyid AND smiley_text=:smileytext", [':smileyid' => intval($this->data['smiley_id']), ':smileytext' => $this->data['smiley_text']]) ? self::$locale['SMLY_414'] : "";

            if (\defender::safe()) {
                if ($error == "") {
                    dbquery_insert(DB_SMILEYS, $this->data, empty($this->data['smiley_id']) ? 'save' : 'update');
                    addNotice('success', empty($this->data['smiley_id']) ? self::$locale['SMLY_410'] : self::$locale['SMLY_411']);
                    redirect(clean_request('', ['section=smiley_list', 'aid'], TRUE));

                } else {
                    addNotice('danger', $error);

                }
            }
        }
    }

    public function display_admin() {

        opentable(self::$locale['SMLY_403']);
        $allowed_section = ["smiley_form", "smiley_list"];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'smiley_list';
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $this->verify_smileys($_GET['smiley_id']) : 0;

        $tab_title['title'][] = self::$locale['SMLY_400'];
        $tab_title['id'][] = 'smiley_list';
        $tab_title['icon'][] = '';
        $tab_title['title'][] = $edit ? self::$locale['SMLY_402'] : self::$locale['SMLY_401'];
        $tab_title['id'][] = 'smiley_form';
        $tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

        echo opentab($tab_title, $_GET['section'], 'smiley_list', TRUE);

        switch ($_GET['section']) {
            case "smiley_form":
                $this->add_smiley_form();
                break;
            default:
                $this->smiley_listing();
                break;
        }

        echo closetab();
        closetable();
    }

    public function smiley_listing() {
        $aidlink = fusion_get_aidlink();

        $all_smileys = self::load_all_smileys();
        $smileys_list = self::smiley_list();

        echo '<div class="m-t-10">';
        echo '<h2>'.self::$locale['SMLY_404'].'</h2>';

        if (!empty($all_smileys)) {
            echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            echo "<tr>\n";
            echo "<td class='col-xs-2'><strong>".self::$locale['SMLY_430']."</strong></td>\n";
            echo "<td class='col-xs-2'><strong>".self::$locale['SMLY_431']."</strong></td>\n";
            echo "<td class='col-xs-2'><strong>".self::$locale['SMLY_432']."</strong></td>\n";
            echo "<td class='col-xs-4'><strong>".self::$locale['SMLY_433']."</strong></td>\n";
            echo "</tr>\n";

            foreach ($all_smileys as $info) {
                echo "<tr>\n";
                echo "<td class='col-xs-2'>".$info['smiley_code']."</td>\n";
                echo "<td class='col-xs-2'><img style='width:20px;height:20px;' src='".IMAGES."smiley/".$info['smiley_image']."' alt='".$info['smiley_text']."' title='".$info['smiley_text']."' /></td>\n";
                echo "<td class='col-xs-2'>".$info['smiley_text']."</td>\n";
                echo "<td class='col-xs-4'><a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=edit&amp;smiley_id=".$info['smiley_id']."'>".self::$locale['edit']."<i class='fa fa-edit m-l-10'></i></a> \n";
                echo "<a id='confirm' class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=delete&amp;smiley_id=".$info['smiley_id']."' onclick=\"return confirm('".self::$locale['SMLY_417']."');\">".self::$locale['SMLY_435']."<i class='fa fa-close m-l-10'></i></a> \n";
                echo "<a id='confirm' class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=delete&amp;inactive=1&amp;smiley_id=".$info['smiley_id']."' onclick=\"return confirm('".self::$locale['SMLY_416']."');\">".self::$locale['delete']."<i class='fa fa-trash m-l-10'></i></a></td>\n</tr>\n";
            }
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center'>".self::$locale['SMLY_440']."</div>\n";
        }
        echo '</div>';

        echo '<div class="m-t-10">';
        echo '<h2>'.self::$locale['SMLY_405'].'</h2>';
        if (!empty($smileys_list)) {
            echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            foreach ($smileys_list as $list) {
                echo "<tr>\n";
                echo "<td class='col-xs-2'><img style='width:20px;height:20px;' src='".IMAGES."smiley/".$list."' alt='' title='' style='border:none;' /></td>\n";
                echo "<td class='col-xs-2'>".ucwords(str_replace(['.gif', '.png', '.jpg', '.svg'], '', $list))."</td>\n";
                echo "<td class='col-xs-2'><a id='confirm' class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;smiley_text=".$list."'>".self::$locale['add']."<i class='fa fa-plus m-l-10'></i></a></td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center'>".self::$locale['SMLY_441']."</div>\n";
        }
        echo '</div>';
    }

    private function smiley_list() {
        $smiley_list = [];
        $smiley = [];
        $result = dbquery("SELECT smiley_id, smiley_code, smiley_image, smiley_text
        FROM ".DB_SMILEYS."
        ORDER BY smiley_id");
        while ($data = dbarray($result)) {
            $smiley[] = $data['smiley_image'];
        }

        $temp = IMAGES."smiley/";
        $smiley_files = makefilelist($temp, '.|..|.DS_Store|index.php', TRUE, "files");
        foreach ($smiley_files as $smiley_check) {

            if (!in_array($smiley_check, $smiley)) {
                $smiley_list[] = $smiley_check;
            }

        }

        return (array)$smiley_list;
    }


    public function add_smiley_form() {

        fusion_confirm_exit();
        echo '<div class="m-t-10">';
        echo openform('smiley_form', 'post', $this->formaction, ['enctype' => TRUE]);

        echo form_hidden('smiley_id', '', $this->data['smiley_id']);
        $image_opts = [];
        $image_opts_ = [];
        $image_files = makefilelist(IMAGES."smiley/", ".|..|index.php", TRUE);
        $result = dbquery("SELECT smiley_image FROM ".DB_SMILEYS);
        while ($data = dbarray($result)) {
            $name = explode(".", $data['smiley_image']);
            $image_opts_[$data['smiley_image']] = ucwords($name[0]);
        }

        foreach ($image_files as $filename) {
            $name = explode(".", $filename);
            $image_opts[$filename] = ucwords($name[0]);
        }

        $smileys_opts = array_diff($image_opts, $image_opts_);

        if ($this->data['smiley_image']) {
            echo form_select('smiley_image', self::$locale['SMLY_421'], $this->data['smiley_image'], [
                'options'    => $smileys_opts,
                'required'   => TRUE,
                'inline'     => TRUE,
                'error_text' => self::$locale['SMLY_438'],
            ]);
        }
        if (!$this->data['smiley_image']) {
            echo form_fileinput('smiley_file', '', '', [
                'upload_path'     => IMAGES.'smiley/',
                'delete_original' => TRUE,
                'template'        => 'modern',
                'type'            => 'image',
                'required'        => TRUE,
            ]);
        }
        echo form_text('smiley_code', self::$locale['SMLY_420'], $this->data['smiley_code'], [
            'required'   => TRUE,
            'inline'     => TRUE,
            'error_text' => self::$locale['SMLY_437']
        ]);
        echo form_text('smiley_text', self::$locale['SMLY_422'], $this->data['smiley_text'], [
            'required'   => TRUE,
            'inline'     => TRUE,
            'error_text' => self::$locale['SMLY_439'],
        ]);
        echo form_button('smiley_save', ($this->data['smiley_id'] ? self::$locale['SMLY_424'] : self::$locale['SMLY_423']), ($this->data['smiley_id'] ? self::$locale['SMLY_424'] : self::$locale['SMLY_423']), ['class' => 'btn-primary']);
        echo closeform();

        if (!empty($smileys_opts)) {
            add_to_jquery("
                function showMeSmileys(item) {
                    return '<aside class=\"pull-left\" style=\"width:20px;height:20px;\"><img style=\"height:15px;\" class=\"img-rounded\" alt=\"'+item.text+'\" src=\"".IMAGES."smiley/'+item.id+'\"/></aside> - ' + item.text;
                }
                $('#smiley_image').select2({
                formatSelection: function(m) { return showMeSmileys(m); },
                formatResult: function(m) { return showMeSmileys(m); },
                escapeMarkup: function(m) { return m; },
                });
            ");
        }
        echo '</div>';
    }
}

$smileys = SmileysAdministration::getInstance();
$smileys->display_admin();

require_once THEMES.'templates/footer.php';
