<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/tags.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums\Admin;

use PHPFusion\Forums\ForumServer;

class ForumAdminTags extends ForumAdminInterface {

    protected $data = array(
        'tag_id' => 0,
        'tag_title' => '',
        'tag_description' => '',
        'tag_language' => '',
        'tag_color' => '#2e8c65',
        'tag_status' => 1,
    );

    /**
     * Admin interface
     */
    public function viewTagsAdmin() {
        $aidlink = fusion_get_aidlink();

        pageAccess('F');

        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                           'link' => INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=ft',
                           'title' => self::$locale['forum_tag_0100']
                       ]);

        echo "<div class='well'>".self::$locale['forum_tag_0101']."</div>\n";

        $tab['title'][] = self::$locale['forum_tag_0102'];
        $tab['id'][] = "tag_list";
        $tab['icon'][] = "";

        $tab['title'][] = isset($_GET['tag_id']) && isnum($_GET['tag_id']) ? self::$locale['forum_tag_0104'] : self::$locale['forum_tag_0103'];
        $tab['id'][] = "tag_form";
        $tab['icon'][] = "";

        $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $tab['id']) ? $_GET['ref'] : "tag_list";

        echo opentab($tab, $_GET['ref'], "rank_admin", TRUE, 'nav-pills m-t-20', "ref");

        switch ($_GET['ref']) {
            case "tag_form" :
                echo $this->displayTagForm();
                break;
            case "tag_list":
                echo $this->displayTagList();

        }

        echo closetab();
    }

    protected function displayTagForm() {
        global $aidlink;

        if (isset($_POST['cancel_tag'])) {
            redirect(clean_request("", array("tag_id", "ref"), FALSE));
        }


        $this->data['rank_language'] = LANGUAGE;

        // Special Select
        $groups_arr = getusergroups();
        $groups_except = array(USER_LEVEL_PUBLIC, USER_LEVEL_MEMBER, USER_LEVEL_ADMIN, USER_LEVEL_SUPER_ADMIN);
        $group_opts = array();
        foreach ($groups_arr as $group) {
            if (!in_array($group[0], $groups_except)) {
                $group_opts[$group[0]] = $group[1];
            }
        }

        $language_opts = fusion_get_enabled_languages();

        $this->post_tags();

        $form_action = FUSION_SELF.$aidlink.'&amp;section=ft&amp;ref=tag_form';

        if (isset($_GET['tag_id']) && isnum($_GET['tag_id'])) {

            $result = dbquery("SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_id='".intval($_GET['tag_id'])."'");

            if (dbrows($result) > 0) {

                $this->data = dbarray($result);

                $form_action = FUSION_SELF.$aidlink."&amp;section=ft&amp;ref=tag_form&amp;tag_id=".$_GET['tag_id'];

            } else {
                redirect(clean_request("", array("rank_id", "ref"), FALSE));
            }

        }

        $button_locale = $this->data['tag_id'] ? self::$locale['forum_tag_0208'] : self::$locale['forum_tag_0207'];


        $html =
            openform('tag_form', 'post', $form_action, array('class' => 'm-t-20')).

            form_text('tag_title', self::$locale['forum_tag_0200'], $this->data['tag_title'],
                      array('required' => 1, 'error_text' => self::$locale['414'], "inline" => TRUE)).

            form_textarea('tag_description', self::$locale['forum_tag_0201'], $this->data['tag_description'],
                          array(
                              'inline' => TRUE,
                              'type' => 'bbcode',
                              'autosize' => TRUE,
                              'preview' => TRUE,
                          )
            ).
            form_colorpicker('tag_color', self::$locale['forum_tag_0202'], $this->data['tag_color'],
                             array(
                                 'inline' => TRUE,
                                 'required' => TRUE,
                             ));

        if (multilang_table("FR")) {
            $html .=
                form_select('tag_language', self::$locale['forum_tag_0203'], $this->data['tag_language'], array(
                    'options' => $language_opts,
                    'placeholder' => self::$locale['choose'], "inline" => TRUE
                ));

        } else {
            $html .= form_hidden('tag_language', '', $this->data['tag_language']);
        }

        $html .= form_checkbox('tag_status', self::$locale['forum_tag_0204'], $this->data['tag_status'],
                               array(
                                   "options" => array(
                                       1 => self::$locale['forum_tag_0205'],
                                       0 => self::$locale['forum_tag_0206'],
                                   ),
                                   "type" => "radio",
                                   "inline" => TRUE,
                               )
            ).

            form_button('save_tag', $button_locale, $button_locale, array('class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o')).
            form_button('cancel_tag', self::$locale['cancel'], self::$locale['cancel'], array('class' => 'btn-default', 'icon' => 'fa fa-times')).
            closeform();

        return $html;

    }

    protected function post_tags() {
        global $aidlink;

        if (isset($_POST['save_tag'])) {

            $this->data = array(
                'tag_id' => isset($_GET['tag_id']) && isnum($_GET['tag_id']) ? intval($_GET['tag_id']) : 0,
                'tag_title' => form_sanitizer($_POST['tag_title'], '', 'tag_title'),
                'tag_language' => form_sanitizer($_POST['tag_language'], '', 'tag_language'),
                'tag_color' => form_sanitizer($_POST['tag_color'], '', 'tag_color'),
                'tag_description' => form_sanitizer($_POST['tag_description'], '', 'tag_description'),
                'tag_status' => isset($_POST['tag_status']) ? 1 : 0,
            );

            if (\defender::safe()) {

                if (!empty($this->data['tag_id'])) {
                    /**
                     * Update
                     */
                    dbquery_insert(DB_FORUM_TAGS, $this->data, "update");
                    addNotice('success', self::$locale['forum_tag_0105']);
                    redirect(FUSION_SELF.$aidlink.'&section=ft');

                } else {
                    /**
                     * Save New
                     */
                    dbquery_insert(DB_FORUM_TAGS, $this->data, "save");
                    addNotice('success', self::$locale['forum_tag_0106']);
                    redirect(FUSION_SELF.$aidlink.'&section=ft');

                }
            }
        }

        if (isset($_GET['delete']) && isnum($_GET['delete'])) {
            $result = dbquery("DELETE FROM ".DB_FORUM_TAGS." WHERE tag_id='".$_GET['delete']."'");
            if ($result) {
                addNotice("success", self::$locale['forum_tag_0107']);
                redirect(FUSION_SELF.$aidlink.'&section=ft');
            }
        }
    }

    /**
     * Ranks Listing
     * @return string
     */
    protected function displayTagList() {

        $tag_list_query = "
        SELECT * FROM ".DB_FORUM_TAGS."
        ".(multilang_table("FO") ? "WHERE tag_language='".LANGUAGE."'" : "")."
        ORDER BY tag_id DESC, tag_title ASC
        ";

        $result = dbquery( $tag_list_query );

        if ( dbrows($result) > 0 ) {
            add_to_jquery("$('.tag-container').hover(
            function(e) { $(this).parent().find('.tag-action').show(); },
            function(e) { $(this).parent().find('.tag-action').hide(); }
            );
            ");
            $html = "<div class='row m-t-20'>\n";

            while ($data = dbarray($result)) {

                $html .= "<div class='col-xs-12 col-sm-3'>\n";
                $html .= "<div class='list-group-item tag-container'>\n";
                $html .= "<div class='pull-left'>\n";
                $html .= "<i class='fa fa-square fa-2x fa-fw m-r-10' style='color:".$data['tag_color']."'></i>\n";
                $html .= "</div>\n";
                $html .= "<div class='overflow-hide'>\n";
                $html .= "<div class='strong text-bigger m-b-5'>".$data['tag_title']."</div>\n";
                $html .= "<p class='description'>".$data['tag_description']."</p>";
                $html .= "<small>".($data['tag_status'] ? self::$locale['forum_tag_0205'] : self::$locale['forum_tag_0206'])."</small><br/><br/>".
                "<span class='tag-action' style='display:none; height: 40px;'>".
                "<a href='".clean_request("tag_id=".$data['tag_id']."&section=ft&ref=tag_form", array("tag_id", "ref"), false)."'>".self::$locale['edit']."</a> -\n".
                "<a href='".clean_request("delete=".$data['tag_id']."&section=ft&ref=tag_form", array("tag_id", "ref"), false)."'>".self::$locale['delete']."</a>".
                "</span>".
                "</div>\n</div>\n</div>\n";
            }

            $html .= "</div>\n";

        } else {

            $html = "<div class='well text-center'>".self::$locale['437']."</div>\n";

        }
        return $html;
    }
}