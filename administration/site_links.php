<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: site_links.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Administration;

use Exception;

require_once __DIR__.'/../maincore.php';

/**
 * Class Sitelinks
 *
 * @package PHPFusion\Administration
 */
class Sitelinks extends \PHPFusion\SiteLinks {

    private static $siteLinksAdmin_instance = NULL;

    private $data = [
        'link_id'          => 0,
        'link_name'        => '',
        'link_url'         => '',
        'link_icon'        => '',
        'link_cat'         => 0,
        'link_language'    => LANGUAGE,
        'link_visibility'  => 0,
        'link_status'      => 1,
        'link_order'       => 0,
        'link_position'    => 1,
        'link_position_id' => 0,
        'link_window'      => 0,
    ];

    private $language_opts;
    private $link_index;

    private $form_action;
    private $aidlink;
    private $locale;
    private $link_id;
    private $link_cat;

    private $title;
    private $refs;
    private $section;
    private $form_uri;
    private $action;

    /**
     * Sitelinks constructor.
     */
    private function __construct() {

        fusion_load_script(INCLUDES."jscripts/admin.js");

        $this->aidlink = fusion_get_aidlink();

        $this->locale = fusion_get_locale("", LOCALE.LOCALESET."admin/sitelinks.php") + fusion_get_locale("", [LOCALE.LOCALESET."admin/html_buttons.php"]);

        $this->language_opts = fusion_get_enabled_languages();

        $this->link_index = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');

        $this->link_id = (int)get("id", FILTER_VALIDATE_INT);

        $this->link_cat = (int)get("cat", FILTER_VALIDATE_INT);

        $this->title = $this->locale['SL_0012'];

        $this->refs = get("refs");

        $this->section = get("section");

        $this->action = get("action");

        if (!in_array($this->section, ["form", "settings"])) {
            $this->section = "links";
        }

        $this->form_action = FUSION_SELF.$this->aidlink."&amp;section=link_form";

    }

    /**
     * @return Sitelinks|null
     */
    public static function Admin() {
        if (empty(self::$siteLinksAdmin_instance)) {
            self::$siteLinksAdmin_instance = new Sitelinks();
        }
        return self::$siteLinksAdmin_instance;
    }

    /**
     * @throws Exception
     */
    public function adminForm() {
        pageaccess("SL");

        if (check_post("cancel")) {
            redirect(FUSION_SELF.$this->aidlink);
        }

        // Add sitelinks breadcrumb
        $this->breadcrumbs();

        // Link actions
        switch ($this->action) {
            case "edit":
                if ($this->link_id) {
                    $this->title = $this->verifySiteLink($this->link_id) ? $this->locale['SL_0011'] : $this->locale['SL_0010'];
                }
                $this->data = self::getSiteLinks($this->link_id);
                if (empty($this->data['link_id'])) {
                    redirect(FUSION_SELF.$this->aidlink);
                }
                $this->form_uri = FUSION_SELF.$this->aidlink."&amp;action=edit&refs=form&id=".$this->link_id."&link_cat=".$this->link_cat;
                $this->data['link_position_id'] = 0;
                break;
            case "del":
                $link_order = dbresult(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id=:id", [":id" => $this->link_id]), 0);
                dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order > :order", [":order" => (int)$link_order]);
                dbquery("DELETE FROM  ".DB_SITE_LINKS." WHERE link_id=:id", [":id" => $this->link_id]);
                addnotice("success", $this->locale['SL_0017']);
                redirect(FUSION_SELF.$this->aidlink."&section=links&refs=".get("refs", FILTER_VALIDATE_INT)."&cat=".get("cat", FILTER_VALIDATE_INT));
                break;
            default:
                $this->form_uri = FUSION_SELF.$this->aidlink."&refs=form";
        }

        // buttons
        $links = "<a href='".FUSION_SELF.$this->aidlink."&refs=form&nrefs=$this->refs&cat=".$this->link_cat."' class='btn btn-primary'><i class='fas fa-plus m-r-5'></i>".$this->locale["SL_0010"]."</a>";
        if ($this->refs == "form") {
            $links .= "<a href='".FUSION_SELF.$this->aidlink."&refs=".(int)get("nrefs", FILTER_VALIDATE_INT)."&nrefs=$this->refs&cat=".$this->link_cat."' class='btn btn-default m-l-10'>".$this->locale["cancel"]."</a>";
        } else {
            $links .= form_button("link_del", $this->locale["delete"], "link_del", ["class" => "m-l-5 btn-danger"]);
            $links .= form_button("link_move", $this->locale["move"], "link_move", ["class" => "btn-default m-l-5"]);
            $links .= form_button("publish", $this->locale["publish"], "publish", ["class" => "btn-default m-l-5"]);
            $links .= form_button("unpublish", $this->locale["unpublish"], "unpublish", ["class" => "btn-default m-l-5"]);
        }

        $master_title['title'][] = $this->locale["SL_0012"];
        $master_title['id'][] = "links";
        $master_title['icon'][] = '';

        $master_title['title'][] = $this->locale['SL_0041'];
        $master_title['id'][] = "settings";
        $master_title['icon'][] = '';

        opentable($this->locale["SL_0001"]);

        echo opentab($master_title, $this->section, 'link', TRUE, "nav-tabs", "section", ['refs', 'action', 'id', 'cat']);

        switch ($this->section) {
            case "settings":
                $this->settings();
                break;
            default:
                echo "<div class='clearfix'>";
                echo "<div class='pull-right'>".$links."</div>";
                echo "<h4>$this->title</h4>";
                echo "<hr/>";
                echo "</div>";
                add_breadcrumb(['link' => $this->form_action, 'title' => ($this->refs == 'link_form' ? $this->locale['SL_0010'] : $this->locale['SL_0012'])]);
                if ($this->refs == "form") {
                    $this->form();
                } else {
                    $this->listing();
                }
        }
        echo closetab();
        closetable();
    }

    /**
     * @throws Exception
     */
    private function settings() {

        add_to_jquery(/** @lang JavaScript */ "slAdmin.slsettingsJs();");

        add_to_title($this->locale['SL_0041']);

        $settings = fusion_get_settings();

        if (post("save_settings")) {
            $settings = [
                "links_grouping" => (post("links_grouping", FILTER_VALIDATE_INT) ? "1" : "0"),
                'link_bbcode'    => (post("link_bbcode", FILTER_VALIDATE_INT) ? "1" : "0"),
                "links_per_page" => sanitizer('links_per_page', 0, 'links_per_page'),
            ];

            if (fusion_safe()) {
                foreach ($settings as $key => $value) {
                    $sql = "UPDATE ".DB_SETTINGS." SET settings_value = '$value' WHERE settings_name = '$key'";
                    dbquery($sql);
                }
                addnotice("success", $this->locale['SL_0018']);
                redirect(FUSION_REQUEST);
            }
        }

        echo openform("slsettingsfrm", "POST");

        echo form_checkbox('link_bbcode', $this->locale["SL_0063"], $settings['link_bbcode'], [
            'options' => [
                '0' => $this->locale['no'],
                '1' => $this->locale['yes']
            ],
            'type'    => "radio",
            "ext_tip" => $this->locale["SL_0064"],
            'inline'  => TRUE,
        ]);
        echo form_checkbox("links_grouping", $this->locale["SL_0046"], $settings['links_grouping'],
            [
                "options" => [
                    0 => $this->locale['SL_0048'],
                    1 => $this->locale['SL_0049']
                ],
                "type"    => "radio",
                "inline"  => TRUE,
                "width"   => "250px",
                "ext_tip" => $this->locale["SL_0047"]
            ]
        );
        echo "<div id='lpp' style='display:none;'>";
        echo form_text("links_per_page", $this->locale['SL_0043'], $settings['links_per_page'],
            [
                "type"        => "number",
                "placeholder" => $this->locale["SL_0045"],
                "width"       => "250px",
                "required"    => TRUE,
                "ext_tip"     => $this->locale["SL_0044"],
                "inline"      => TRUE,
                "inner_width" => "150px",
            ]
        );
        echo "</div>";
        echo form_button('save_settings', $this->locale['save_changes'], $this->locale['save_changes'], ['class' => 'btn-primary']);
        echo closeform();
    }

    /**
     *  Site Links Form
     *
     * @throws Exception
     */
    private function form() {

        if ($this->link_cat && isnum($this->link_cat)) {
            $this->data["link_cat"] = $this->link_cat;
        }
        if ($link_position = get("nrefs", FILTER_VALIDATE_INT)) {
            $this->data["link_position"] = $link_position;
        }

        if (check_post("save_link")) {

            $this->data = [
                "link_id"          => sanitizer('link_id', 0, 'link_id'),
                "link_cat"         => sanitizer('link_cat', 0, 'link_cat'),
                "link_name"        => sanitizer('link_name', '', 'link_name'),
                "link_url"         => sanitizer('link_url', '', 'link_url'),
                "link_icon"        => sanitizer('link_icon', '', 'link_icon'),
                "link_language"    => sanitizer('link_language', LANGUAGE, 'link_language'),
                "link_visibility"  => sanitizer('link_visibility', 0, 'link_visibility'),
                "link_position"    => sanitizer('link_position', 0, 'link_position'),
                'link_status'      => sanitizer('link_status', 0, 'link_status'),
                "link_order"       => sanitizer('link_order', 0, 'link_order'),
                "link_window"      => (check_post('link_window') ? '1' : '0'),
                "link_position_id" => 0,
            ];

            if ($this->data['link_position'] > 3) {
                $this->data['link_position'] = sanitizer('link_position_id', 3, 'link_position_id');
            }

            if (empty($this->data['link_order'])) {
                $max_order_query = "SELECT MAX(link_order) 'link_order' FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$this->data['link_cat']."'";
                $this->data['link_order'] = dbresult(dbquery($max_order_query), 0) + 1;
            }

            if (fusion_safe()) {
                if (!empty($this->data['link_id'])) {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'], "link_id", $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language");

                    dbquery_insert(DB_SITE_LINKS, $this->data, 'update');

                    $child = get_child($this->link_index, $this->data['link_id']);
                    if (!empty($child)) {
                        foreach ($child as $child_id) {
                            dbquery("UPDATE ".DB_SITE_LINKS." SET link_position='".$this->data['link_position']."' WHERE link_id='$child_id'");
                        }
                    }
                    addnotice("success", $this->locale['SL_0016']);

                } else {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'], "link_id", $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language", "save");

                    dbquery_insert(DB_SITE_LINKS, $this->data, 'save');
                    // New link will not have child
                    addnotice("success", $this->locale['SL_0015']);
                }

                redirect(FUSION_SELF.$this->aidlink."&section=links&refs=".(int)$this->data["link_position"]."&cat=".(int)$this->data["link_cat"]);
            }
        }

        add_to_jquery(/** @lang JavaScript */ "slAdmin.slFormJS();");

        echo openform('link_administration_frm', 'POST', $this->form_uri);

        echo "<div class='row'>";
        echo "<div class='col-xs-12 col-sm-9'>";

        echo form_hidden('link_id', '', $this->data['link_id']);
        echo form_text('link_name', $this->locale['SL_0020'], $this->data['link_name'], [
            'max_length' => 100,
            'required'   => TRUE,
            'error_text' => $this->locale['SL_0085'],
            'inline'     => TRUE
        ]);
        echo form_text('link_icon', $this->locale['SL_0020a'], $this->data['link_icon'], [
            'max_length' => 100,
            'inline'     => TRUE
        ]);
        echo form_text('link_url', $this->locale['SL_0021'], $this->data['link_url'], [
            'error_text' => $this->locale['SL_0086'],
            'inline'     => TRUE
        ]);
        echo form_text('link_order', $this->locale['SL_0023'], $this->data['link_order'], [
            'inline' => TRUE,
            'width'  => '250px',
            'type'   => 'number'
        ]);

        if ($this->data["link_position"] > 3) {
            $this->data['link_position_id'] = $this->data['link_position'];
            $this->data['link_position'] = 4;
        }

        echo form_select('link_position', $this->locale['SL_0024'], $this->data["link_position"],
            [
                'options'     => self::getSiteLinksPosition(),
                'inline'      => TRUE,
                "width"       => "300px",
                "inner_width" => "300px",
                'stacked'     => form_text('link_position_id', '', $this->data['link_position_id'],
                    [
                        'required'    => TRUE,
                        'placeholder' => 'New Position ID',
                        'type'        => 'number',
                        'width'       => '200px',
                        "inner_width" => "200px",
                        "class"       => "spacer-xs"
                    ]
                )
            ]);

        echo form_checkbox('link_status', $this->locale['SL_0031'], $this->data['link_status'], [
            'options' => [1 => $this->locale['publish'], 0 => $this->locale['unpublish']],
            'width'   => '100%',
            "type"    => "radio",
            "inline"  => TRUE,
        ]);

        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-3'>\n";
        openside("");
        echo form_select("link_cat", $this->locale['SL_0029'], $this->data['link_cat'], [
            'input_id'        => "link_categories",
            "parent_value"    => $this->locale['parent'],
            'width'           => '100%',
            'query'           => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : ''),
            'disable_opts'    => $this->data['link_id'],
            'hide_disabled'   => 1,
            "add_parent_opts" => TRUE,
            "db"              => DB_SITE_LINKS,
            "title_col"       => "link_name",
            "id_col"          => "link_id",
            "cat_col"         => "link_cat",
        ]);

        echo form_select('link_language', $this->locale['global_ML100'], $this->data['link_language'], [
            'options'     => $this->language_opts,
            'placeholder' => $this->locale['choose'],
            'width'       => '100%',
            "inline"      => FALSE,
        ]);

        echo form_select('link_visibility', $this->locale['SL_0022'], $this->data['link_visibility'], [
            'options'     => self::getLinkVisibility(),
            'placeholder' => $this->locale['choose'],
            'width'       => '100%',
        ]);
        echo form_checkbox('link_window', $this->locale['SL_0028'], $this->data['link_window'],
            ["default_checked" => FALSE]
        );
        closeside();
        echo "</div>\n";
        echo "</div>\n";
        echo form_button('save_link', $this->locale['SL_0040'], $this->locale['SL_0040'], ['class' => 'btn-success m-r-10', 'input_id' => 'savelink_2']);
        echo closeform();
    }

    private function breadcrumbs() {
        add_breadcrumb([
            "title" => $this->locale['SL_0001'],
            "link"  => FUSION_SELF.$this->aidlink,
        ]);

        if ($this->section == "settings") {

            add_breadcrumb(['link' => FUSION_SELF.$this->aidlink."&section=settings", 'title' => $this->locale["SL_0041"]]);

        } else {

            if (!$this->refs) {
                $this->refs = 2;
            }
            // adds current menu navigation
            switch ($this->refs) {
                case 3:
                    $title = $this->locale["SL_0027"];
                    break;
                case 1:
                    $title = $this->locale["SL_0025"];
                    break;
                case 2:
                    $title = $this->locale["SL_0026"];
                    break;
                default:
                    $title = $this->locale["SL_0072"]." ".$this->refs;
            }
            add_breadcrumb([
                "title" => $title,
                "link"  => FUSION_SELF.$this->aidlink."&refs=".$this->refs
            ]);

            if ($this->refs == "form") {
                if ($this->action == "edit") {
                    add_breadcrumb(['link' => $this->form_action, 'title' => $this->locale['SL_0011']]);
                } else {
                    $this->title = $this->locale["SL_0010"];
                    add_breadcrumb(['link' => FUSION_SELF.$this->aidlink."&refs=form", 'title' => $this->locale["SL_0010"]]);
                }
            }
        }

        $link_index = dbquery_tree(DB_SITE_LINKS, "link_id", "link_cat");

        $link_data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat");

        make_page_breadcrumbs($link_index, $link_data, "link_id", "link_name", "cat");

    }

    /**
     * Form for Listing Menu
     */
    private function listing() {

        add_to_footer("<script src='".INCLUDES."jquery/jquery-ui/jquery-ui.min.js'></script>");

        $token = fusion_get_token("sitelinks_order", 10);

        add_to_jquery(/** @lang JavaScript */ "slAdmin.slListing({
        'SL_0080' : '".$this->locale["SL_0080"]."',
        'SL_0016' : '".$this->locale["SL_0016"]."',
        'error_preview' : '".$this->locale["error_preview"]."',
        'error_preview_text' : '".$this->locale["error_preview_text"]."',
        }, '$token');");

        $this->doMenuAction();

        $menus = $this->menuList();

        $tab = [];
        foreach ($menus as $pos_id => $menu_name) {
            $tab["title"][$pos_id] = $menu_name;
            $tab["id"][$pos_id] = $pos_id;
        }

        $tab_active = tab_active($tab, 2, "refs");
        $cat = get("cat", FILTER_VALIDATE_INT) ?: 0;

        echo opentab($tab, $tab_active, "sl-menu", TRUE, "nav-pills m-b-10", "refs", ["cat", "refs"]);
        echo opentabbody($tab["title"][$tab_active], $tab["id"][$tab_active], $tab_active, TRUE);

        // now do the listing
        $table_api = fusion_table("sitelink", [
            "remote_file" => ADMIN."includes/?api=sitelinks-list&refs=".$tab_active."&cat=$cat",
            "server_side" => TRUE,
            "processing"  => TRUE,
            //"responsive"  => TRUE,
            "debug"       => FALSE,
            "zero_locale" => $this->locale["SL_0062"],
            "columns"     => [
                ["data" => "link_checkbox", "width" => "30", "orderable" => FALSE],
                ["data" => "link_name", "width" => "45%", "className" => "all"],
                ["data" => "link_count", "width" => "10%", "className" => "not-mobile"],
                ["data" => "link_status", "width" => "10%", "className" => "not-mobile"],
                ["data" => "link_window", "width" => "10%"],
                ["data" => "link_visibility", "className" => "not-mobile"],
                ["data" => "link_order", "width" => "50"],
            ]
        ]);
        echo openform("fusion_sltable_form", "POST");
        echo "<table id='$table_api' class='table table-bordered table-striped table-hover'><thead>";
        echo "<tr>";
        echo "<th class='text-center'>".form_checkbox('check_all', '', "", ["input_value" => 1, "input_id" => "check_all", "default_checked" => FALSE])."</th>";
        echo "<th>".$this->locale["SL_0050"]."</th>";
        echo "<th>".$this->locale["SL_0035"]."</th>";
        echo "<th>".$this->locale["SL_0031"]."</th>";
        echo "<th>".$this->locale["SL_0071"]."</th>";
        echo "<th>".$this->locale["SL_0051"]."</th>";
        echo "<th>".$this->locale["SL_0052"]."</th>";
        echo "</tr>";
        echo "</thead><tbody class='sort'></tbody></table>";
        echo form_hidden("table_action");
        echo closeform();
        echo closetabbody();
        echo closetab();
    }

    /**
     * Perform site links modifications
     */
    private function doMenuAction() {

        if ($action = post("table_action")) {
            if (in_array($action, ["link_move", "move_confirm", "link_del", 'publish', 'unpublish'])) {

                $link_id = sanitizer(["link_id"], "", "link_id");
                $link_array = explode(",", $link_id);
                // Link position
                //$link_position = get("refs", FILTER_VALIDATE_INT);
                //if (!$link_position) {
                //    $link_position = 1;
                //}

                if (!empty($link_id)) {

                    if ($action === "link_move") {

                        //@ group by link position could be a better one for the move down list.
                        //$available_query = "SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id NOT IN ($link_id) ORDER BY link_name";
                        //$list_query = dbquery($available_query);
                        //if (dbrows($list_query)) {
                        //    $list[0] = $this->locale['SL_0032'];
                        //    while ($lData = dbarray($list_query)) {
                        //        if (!stristr($lData['link_name'], '-')) {
                        //            $list[$lData['link_id']] = $lData['link_name'];
                        //        }
                        //    }
                        //} else {
                        //    addNotice("warning", $this->locale['SL_0038']);
                        //    redirect(FUSION_REQUEST);
                        //}

                        $modal = openmodal('move_to_mdl', $this->locale['SL_0036'], ['static' => TRUE]);
                        $modal .= openform('move_frm', 'POST');
                        $modal .= form_select('move_to_id', $this->locale['SL_0037'], '', [
                            "db"           => DB_SITE_LINKS,
                            "id_col"       => "link_id",
                            "title_col"    => "link_name",
                            "cat_col"      => "link_cat",
                            "parent_value" => $this->locale['SL_0032'],
                            "inline"       => FALSE,
                            "optgroup"     => FALSE,
                            "custom_query" => "SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id NOT IN ($link_id) ORDER BY link_name",
                        ]);

                        foreach ($link_array as $link_id) {
                            $modal .= form_hidden('link_id[]', '', $link_id);
                        }

                        $modal .= form_button('table_action', $this->locale['SL_0039'], 'move_confirm', ['class' => 'btn-primary']);
                        $modal .= form_button('link_clear', $this->locale['cancel'], 'cancel', ['class' => 'btn-default m-l-10']);
                        $modal .= closeform();
                        $modal .= closemodal();

                        add_to_footer($modal);

                    } else {

                        // Perform menu action
                        foreach ($link_array as $link_id) {
                            // check input table
                            if (self::verifySiteLink($link_id) && fusion_safe()) {
                                switch ($action) {
                                    case "publish":
                                        dbquery("UPDATE ".DB_SITE_LINKS." SET link_status='1' WHERE link_id=:id", [":id" => (int)$link_id]);
                                        break;
                                    case "unpublish":
                                        dbquery("UPDATE ".DB_SITE_LINKS." SET link_status='0' WHERE link_id=:id", [":id" => (int)$link_id]);
                                        break;
                                    case "move_confirm":
                                        $link_move_to = (check_post("move_to_id") ? sanitizer('move_to_id', 0, 'move_to_id') : 0);
                                        dbquery("UPDATE ".DB_SITE_LINKS." SET link_cat=:mid WHERE link_id=:id", [":mid" => (int)$link_move_to, "id" => (int)$link_id]);

                                        break;
                                    case "link_del":
                                        $link_order = dbresult(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id=:id", [":id" => (int)$link_id]), 0);
                                        dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order > :order", [":order" => (int)$link_order]);
                                        dbquery("DELETE FROM  ".DB_SITE_LINKS." WHERE link_id=:id", [":id" => (int)$link_id]);
                                        break;
                                    default:
                                        redirect(FUSION_SELF.$this->aidlink);
                                }
                            }
                        }
                        addnotice("success", $this->locale['SL_0016']);
                        redirect(FUSION_REQUEST);
                    }
                } else {
                    addnotice("danger", $this->locale['SL_0087']);
                }
            } else {
                addnotice("danger", "Invalid action");
                redirect(FUSION_REQUEST);
            }
        }

    }

    /**
     * @return array
     */
    private function menuList() {
        $list = [
            '1' => $this->locale['SL_0025'],
            '2' => $this->locale['SL_0026'],
            '3' => $this->locale['SL_0027']
        ];
        $result = dbquery("SELECT link_position FROM ".DB_SITE_LINKS." WHERE link_position > 3 ORDER BY link_name");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $list[$data["link_position"]] = "Menu ".$data["link_position"];
            }
        }
        return $list;
    }
}

require_once THEMES."templates/admin_header.php";

try {
    Sitelinks::Admin()->adminForm();
} catch (Exception $e) {
    die($e->getMessage());
}

require_once THEMES."templates/footer.php";
