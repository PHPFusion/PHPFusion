<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/ranks.php
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

class ForumAdminRanks extends ForumAdminInterface {

    protected $data = array(
        'rank_id'            => 0,
        'rank_title'         => '',
        'rank_image'         => '',
        'rank_posts'         => 0,
        'rank_type'          => 2,
        'rank_apply_normal'  => '',
        'rank_apply_special' => '',
        'rank_apply'         => '',
        'rank_language'      => LANGUAGE,
    );

    public function viewRanksAdmin() {

        pageAccess('F');

        $forum_settings = $this->get_forum_settings();

        echo "<div class='well m-t-15'>".self::$locale['forum_rank_0100']."</div>\n";

        if ($forum_settings['forum_ranks']) {
            $tab_pages = array("rank_list", "rank_form");

            if (isset($_GET['ref']) && $_GET['ref'] == "back") {
                redirect(clean_request("section=fr", array("ref", "section", 'rank_id'), FALSE));
            }

            $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $tab_pages) ? $_GET['ref'] : $tab_pages[0];

            if ($_GET['ref'] != $tab_pages[0]) {
                $tab['title'][] = self::$locale['back'];
                $tab['id'][] = "back";
                $tab['icon'][] = "fa fa-fw fa-arrow-left";
            } else {
                $tab['title'][] = self::$locale['forum_rank_402'];
                $tab['id'][] = "rank_list";
                $tab['icon'][] = "";
            }

            $tab['title'][] = isset($_GET['rank_id']) && isnum($_GET['rank_id']) ? self::$locale['forum_rank_401'] : self::$locale['forum_rank_400'];
            $tab['id'][] = "rank_form";
            $tab['icon'][] = isset($_GET['rank_id']) && isnum($_GET['rank_id']) ? "fa fa-fw fa fa-pencil" : "fa fa-fw fa fa-plus";


            echo opentab($tab, $_GET['ref'], "rank_admin", TRUE, "nav-tabs m-t-10", "ref");

            switch ($_GET['ref']) {
                case "rank_form" :
                    echo $this->displayRanksForm();
                    break;
            	default:
                    echo $this->displayRankList();
            }

            echo closetab();

        } else {
            echo '<h3>'.self::$locale['forum_rank_403'].'</h3>';
            echo "<div class='well text-center'>";
                echo sprintf(self::$locale['forum_rank_450'], "<a href='".clean_request("section=fs", array("section"), FALSE)."'>".self::$locale['forum_rank_451']."</a>");
            echo "</div>";
        }
    }

    protected function displayRanksForm() {

        if (isset($_POST['cancel_rank'])) {
            redirect(clean_request("", array("rank_id", "ref"), FALSE));
        }

        add_to_footer("<script src='".FORUM."admin/admin_rank.js'></script>");

        $array_apply_normal_opts = [
            USER_LEVEL_MEMBER       => self::$locale['forum_rank_424'],
            '-104'                  => self::$locale['forum_rank_425'],
            USER_LEVEL_ADMIN        => self::$locale['forum_rank_426'],
            USER_LEVEL_SUPER_ADMIN  => self::$locale['forum_rank_427']
        ];

        // Special Select
        $groups_arr = getusergroups();
        $groups_except = array(USER_LEVEL_PUBLIC, USER_LEVEL_MEMBER, USER_LEVEL_ADMIN, USER_LEVEL_SUPER_ADMIN);
        $group_opts = array();
        foreach ($groups_arr as $group) {
            if (in_array($group[0], $groups_except)) {
                $group_opts[$group[0]] = $group[1];
            }
        }

        $language_opts = fusion_get_enabled_languages();

        $this->post_forum_ranks();

        $form_action = clean_request("section=fr&ref=rank_form", array("rank_id", "ref"), FALSE);

        if (isset($_GET['rank_id']) && isnum($_GET['rank_id'])) {

            $result = dbquery("SELECT * FROM ".DB_FORUM_RANKS." WHERE rank_id='".intval($_GET['rank_id'])."'");

            if (dbrows($result) > 0) {

                $this->data = dbarray($result);

                $form_action = clean_request("section=fr&ref=rank_form&rank_id=".$_GET['rank_id'], array("rank_id", "ref"), FALSE);

            }

        }

        $html =
            openform('rank_form', 'post', $form_action, array('class' => 'm-t-20')).

            form_hidden('rank_id', '', $this->data['rank_id']).

            form_text('rank_title', self::$locale['forum_rank_420'], $this->data['rank_title'],
                      ['required' => TRUE, 'inline' => TRUE, 'error_text' => self::$locale['forum_rank_414']]).

            form_select('rank_image', self::$locale['forum_rank_421'], $this->data['rank_image'],
                       ['inline' => TRUE, 'options' => $this->get_rank_images(), 'placeholder' => self::$locale['choose'],]);

        if (multilang_table("FR")) {
            $html .=
                form_select('rank_language', self::$locale['global_ML100'], $this->data['rank_language'], [
                    'inline' => TRUE, 'options' => $language_opts, 'placeholder' => self::$locale['choose']]);

        } else {
            $html .= form_hidden('rank_language', '', $this->data['rank_language']);
        }

        $html .= form_checkbox('rank_type', self::$locale['forum_rank_429'], $this->data['rank_type'],
                               [
                                   'options' => [
                                       self::$locale['forum_rank_429c'],
                                       self::$locale['forum_rank_429b'],
                                       self::$locale['forum_rank_429a'],
                                   ],
                                   'type'   => 'radio',
                                   'inline' => TRUE,
                               ]).

            form_text('rank_posts', self::$locale['forum_rank_422'], $this->data['rank_posts'],
                      [
                          'inline' => TRUE,
                          'type' => 'number',
                          'inner_width' => '10%',
                          'disabled' => $this->data['rank_type'] != 0
                      ]
            ).

            "<span id='select_normal' ".($this->data['rank_type'] == 2 ? "class='display-none'" : "")." >".

            form_select('rank_apply_normal', self::$locale['forum_rank_423'], $this->data['rank_apply'],
                        ['inline' => TRUE, 'options' => $array_apply_normal_opts, 'placeholder' => self::$locale['choose']]).

            "</span>\n<span id='select_special' ".($this->data['rank_type'] != 2 ? " class='display-none'" : "").">".

            form_select('rank_apply_special', self::$locale['forum_rank_423'], $this->data['rank_apply'],
                        ['inline' => TRUE, 'options' => $group_opts, 'placeholder' => self::$locale['choose']]).

            "</span>\n".

            form_button('save_rank', self::$locale['save'], self::$locale['save'], ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o']).
            form_button('cancel_rank', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default', 'icon' => 'fa fa-times']).

            closeform();

        return $html;
    }

    protected function post_forum_ranks() {

        if (isset($_POST['save_rank'])) {

            $this->data = array(
                'rank_id' => form_sanitizer($_POST['rank_id'], '0', 'rank_id'),
                'rank_title' => form_sanitizer($_POST['rank_title'], '', 'rank_title'),
                'rank_image' => form_sanitizer($_POST['rank_image'], "", "rank_image"),
                'rank_language' => form_sanitizer($_POST['rank_language'], "", "rank_language"),
                'rank_posts' => isset($_POST['rank_posts']) && isnum($_POST['rank_posts']) ? $_POST['rank_posts'] : 0,
                'rank_type' => isset($_POST['rank_type']) && isnum($_POST['rank_type']) ? $_POST['rank_type'] : 0,
                'rank_apply_normal' => isset($_POST['rank_apply_normal']) ? $_POST['rank_apply_normal'] : USER_LEVEL_MEMBER,
                'rank_apply_special' => isset($_POST['rank_apply_special']) && isnum($_POST['rank_apply_special']) ? $_POST['rank_apply_special'] : 0,
            );
            $this->data += array(
                'rank_apply' => $this->data['rank_type'] == 2 ? $this->data['rank_apply_special'] : $this->data['rank_apply_normal']
            );

            if (\defender::safe()) {

                if (!empty($this->data['rank_id']) && !$this->check_duplicate_ranks()) {
                    /**
                     * Update
                     */
                    dbquery_insert(DB_FORUM_RANKS, $this->data, "update");
                    addNotice('info', self::$locale['forum_rank_411']);
                    redirect(clean_request("section", array("rank_id", "ref"), FALSE));

                } elseif (!$this->check_duplicate_ranks()) {
                    /**
                     * Save New
                     */
                    dbquery_insert(DB_FORUM_RANKS, $this->data, "save");
                    addNotice('info', self::$locale['forum_rank_410']);
                   redirect(clean_request("section", array("rank_id", "ref"), FALSE));

                }
            }
        }

        if (isset($_GET['delete']) && isnum($_GET['delete'])) {
            dbquery("DELETE FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['delete']."'");
            addNotice("success", self::$locale['forum_rank_412']);
            redirect(clean_request("section=fr", array("delete", "ref"), FALSE));
        }
    }

    protected function check_duplicate_ranks() {
        $comparing_data = dbarray(
            dbquery(
                "SELECT rank_apply FROM ".DB_FORUM_RANKS." WHERE rank_id='".$this->data['rank_id']."'"
            ));
        if (
            ($this->data['rank_apply'] < USER_LEVEL_MEMBER && $this->data['rank_apply'] != $comparing_data['rank_apply'])
            && (dbcount("(rank_id)",
                        DB_FORUM_RANKS,
                        (multilang_table("FR") ? "rank_language='".LANGUAGE."' AND" : "")."
                                    rank_id!='".$this->data['rank_id']."' AND rank_apply='".$this->data['rank_apply']."'"))
        ) {
            addNotice('info', self::$locale['forum_rank_413']);
            redirect(clean_request("section=fr", array(""), FALSE));
        }

        return FALSE;
    }

    /**
     * Ranks Listing
     * @return string
     */
    protected function displayRankList() {

        $rank_list_query = "SELECT *
        FROM ".DB_FORUM_RANKS."
        ".(multilang_table("FR") ? "WHERE rank_language='".LANGUAGE."'" : "")."
        ORDER BY rank_type DESC, rank_apply DESC, rank_posts
        ";

        $result = dbquery( $rank_list_query );

        if (dbrows($result) > 0 ) {

            $html = "<div class='table-responsive'><table class='table table-striped table-hover center m-t-20'>\n<thead>\n<tr>\n".
            "<th class='col-xs-4'>".self::$locale['forum_rank_430']."</th>\n".
            "<th>".self::$locale['forum_rank_431']."</th>\n".
            "<th>".self::$locale['forum_rank_432']."</th>\n".
            "<th>".self::$locale['forum_rank_438']."</th>\n".
            "<th class='text-center'>".self::$locale['forum_rank_434']."</th>\n".
            "</tr>\n".
            "</thead>\n<tbody>\n";

            $i = 0;
            while ($data = dbarray($result)) {

                $html .= "<tr>\n".
                "<td '>".$data['rank_title']."</td>\n".
                "<td>".($data['rank_apply'] == -104 ? self::$locale['forum_rank_425'] : getgroupname($data['rank_apply']))."</td>\n".
                "<td class='col-xs-2'>".ForumServer::show_forum_rank($data['rank_posts'], $data['rank_apply'], $data['rank_apply'])."</td>\n".
                "<td>";

                if ($data['rank_type'] == 0) {
                    $html .= $data['rank_posts'];
                } elseif ($data['rank_type'] == 1) {
                    $html .= self::$locale['forum_rank_429b'];
                } else {
                    $html .= self::$locale['forum_rank_429a'];
                }

                $html .= "</td>\n<td width='1%' style='white-space:nowrap'>".
                "<a href='".clean_request("section=fr&ref=rank_form&rank_id=".$data['rank_id']."", array("rank_id", "ref"), false)."'>".self::$locale['edit']."</a> -\n".
                "<a href='".clean_request("section=fr&ref=rank_form&delete=".$data['rank_id']."", array("rank_id", "ref"), false)."'>".self::$locale['delete']."</a></td>\n</tr>\n";

                $i++;
            }
            $html .= "</tbody>\n</table></div>";
        } else {

            $html = "<div class='well text-center'>".self::$locale['forum_rank_437']."</div>\n";

        }
        return $html;
    }
}
