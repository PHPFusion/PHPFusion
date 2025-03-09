<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: tags.php
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

class ForumAdminTags extends ForumAdminInterface {

    protected $data = [
        'tag_id'          => 0,
        'tag_title'       => '',
        'tag_description' => '',
        'tag_color'       => '#2e8c65',
        'tag_icon'        => '',
        'tag_status'      => 1,
        'tag_language'    => LANGUAGE,
    ];

    /**
     * Admin interface
     */
    public function viewTagsAdmin() {

        pageaccess('F');

        echo "<div class='well'>".self::$locale['forum_tag_0101']."</div>\n";
        $tag_pages = ["tag_list", "tag_form"];

        if (isset($_GET['ref']) && $_GET['ref'] == "back") {
            redirect(clean_request("section=ft", ["ref", "section", 'tag_id'], FALSE));
        }

        $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $tag_pages) ? $_GET['ref'] : $tag_pages[0];

        if ($_GET['ref'] != $tag_pages[0]) {
            $tab['title'][] = self::$locale['back'];
            $tab['id'][] = "back";
            $tab['icon'][] = "fa fa-fw fa-arrow-left";
        } else {
            $tab['title'][] = self::$locale['forum_tag_0102'];
            $tab['id'][] = "tag_list";
            $tab['icon'][] = "";
        }
        $tab['title'][] = isset($_GET['tag_id']) && isnum($_GET['tag_id']) ? self::$locale['forum_tag_0104'] : self::$locale['forum_tag_0103'];
        $tab['id'][] = "tag_form";
        $tab['icon'][] = "";

        echo opentab($tab, $_GET['ref'], "rank_admin", TRUE, 'nav-pills m-t-20', "ref");

        switch ($_GET['ref']) {
            case "tag_form":
                echo $this->displayTagForm();
                break;
            default:
                echo $this->displayTagList();
        }

        echo closetab();
    }

    protected function displayTagForm() {

        if (isset($_POST['cancel_tag'])) {
            redirect(clean_request("", ["tag_id", "ref"], FALSE));
        }

        $language_opts = fusion_get_enabled_languages();

        $this->postTags();

        $form_action = clean_request("section=ft&ref=tag_form", ["tag_id", "ref"], FALSE);

        if (isset($_GET['tag_id']) && isnum($_GET['tag_id'])) {

            $result = dbquery("SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_id='".intval($_GET['tag_id'])."'");

            if (dbrows($result) > 0) {

                $this->data = dbarray($result);

                $form_action = clean_request("section=ft&ref=tag_form&tag_id=".$_GET['tag_id'], ["tag_id", "ref"], FALSE);

            }
        }

        $button_locale = $this->data['tag_id'] ? self::$locale['forum_tag_0208'] : self::$locale['forum_tag_0103'];

        $html =
            openform('tag_form', 'post', $form_action).
            form_hidden('tag_id', '', $this->data['tag_id']).

            form_text('tag_title', self::$locale['forum_tag_0200'], $this->data['tag_title'], [
                    'required' => TRUE,
                    "inline"   => TRUE]
            ).

            form_textarea('tag_description', self::$locale['forum_tag_0201'], $this->data['tag_description'], [
                'inline'   => TRUE,
                'type'     => 'bbcode',
                'autosize' => TRUE,
                'preview'  => TRUE,
            ]).

            form_colorpicker('tag_color', self::$locale['forum_tag_0202'], $this->data['tag_color'], [
                'inline' => TRUE
            ]).

            form_text('tag_icon', self::$locale['forum_tag_0202a'], $this->data['tag_icon'], [
                'inline'      => TRUE,
                'placeholder' => 'fa fa-hashtag'
            ]);

        if (multilang_table("FR")) {
            $html .=
                form_select('tag_language[]', self::$locale['forum_tag_0203'], $this->data['tag_language'], [
                    'inline'      => TRUE,
                    'options'     => $language_opts,
                    'placeholder' => self::$locale['choose'],
                    'multiple'    => TRUE
                ]);


        } else {
            $html .= form_hidden('tag_language', '', $this->data['tag_language']);
        }

        $html .= form_checkbox('tag_status', self::$locale['forum_tag_0204'], $this->data['tag_status'],
                ['options' => [
                    1 => self::$locale['forum_tag_0205'],
                    0 => self::$locale['forum_tag_0206'],
                ],
                 'type'    => 'radio',
                 'inline'  => TRUE,
                ]).

            form_button('save_tag', $button_locale, $button_locale, ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o']).
            form_button('cancel_tag', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default', 'icon' => 'fa fa-times']).
            closeform();

        return $html;

    }

    protected function postTags() {

        if (isset($_POST['save_tag'])) {

            $this->data = [
                'tag_id'          => form_sanitizer($_POST['tag_id'], '0', 'tag_id'),
                'tag_title'       => form_sanitizer($_POST['tag_title'], '', 'tag_title'),
                'tag_color'       => form_sanitizer($_POST['tag_color'], '', 'tag_color'),
                'tag_icon'        => form_sanitizer($_POST['tag_icon'], '', 'tag_icon'),
                'tag_description' => form_sanitizer($_POST['tag_description'], '', 'tag_description'),
                'tag_status'      => form_sanitizer($_POST['tag_status'], '', 'tag_status'),
                'tag_language'    => form_sanitizer($_POST['tag_language'], LANGUAGE, 'tag_language'),
            ];

            if (fusion_safe()) {

                if (!empty($this->data['tag_id'])) {
                    /**
                     * Update
                     */
                    dbquery_insert(DB_FORUM_TAGS, $this->data, "update");
                    addnotice('success', self::$locale['forum_tag_0106']);

                } else {
                    /**
                     * Save New
                     */
                    dbquery_insert(DB_FORUM_TAGS, $this->data, "save");
                    addnotice('success', self::$locale['forum_tag_0105']);

                }
                redirect(clean_request("section=ft", ["tag_id", "ref"], FALSE));
            }
        }

        if (isset($_GET['delete']) && isnum($_GET['delete'])) {
            dbquery("DELETE FROM ".DB_FORUM_TAGS." WHERE tag_id='".$_GET['delete']."'");
            addnotice("success", self::$locale['forum_tag_0107']);
            redirect(clean_request("section=ft", ["delete", "ref"], FALSE));
        }
    }

    /**
     * Ranks Listing
     *
     * @return string
     */
    protected function displayTagList() {

        $tag_list_query = "
        SELECT * FROM ".DB_FORUM_TAGS."
        ".(multilang_table("FO") ? "WHERE ".in_group('tag_language', LANGUAGE) : "")."
        ORDER BY tag_id DESC, tag_title ASC
        ";

        $result = dbquery($tag_list_query);

        if (dbrows($result) > 0) {
            $html = "<div class='row equal-height'>\n";

            while ($data = dbarray($result)) {

                $html .= "<div class='col-xs-12 col-sm-3'>\n";
                $html .= "<div class='list-group-item tag-container'>\n";
                $html .= "<div class='pull-left m-r-10'>\n";
                $html .= '<span class="fa-stack"><i class="fa-stack-2x fa fa-square" style="color:'.$data['tag_color'].';"></i>';
                if (!empty($data['tag_icon'])) {
                    $html .= '<i class="text-white fa-stack-1x '.$data['tag_icon'].'"></i>';
                }
                $html .= '</span>';
                $html .= "</div>\n";
                $html .= "<div class='overflow-hide'>\n";
                $html .= "<div class='strong text-bigger m-b-5'>".$data['tag_title']."</div>\n";
                $html .= "<p class='description'>".$data['tag_description']."</p>";
                $html .= "<small>".($data['tag_status'] ? self::$locale['forum_tag_0205'] : self::$locale['forum_tag_0206'])."</small><br/>".
                    "<span class='tag-action''>".
                    "<a href='".clean_request("tag_id=".$data['tag_id']."&section=ft&ref=tag_form", ["tag_id", "ref"], FALSE)."'>".self::$locale['edit']."</a> -\n".
                    "<a href='".clean_request("delete=".$data['tag_id']."&section=ft&ref=tag_form", ["tag_id", "ref"], FALSE)."'>".self::$locale['delete']."</a>".
                    "</span>".
                    "</div>\n</div>\n</div>\n";
            }

            $html .= "</div>\n";

        } else {

            $html = "<div class='well text-center m-t-10'>".self::$locale['forum_tag_0209']."</div>\n";

        }
        return $html;
    }
}
