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
        'rank_id' => 0,
        'rank_title' => '',
        'rank_image' => '',
        'rank_posts' => 0,
        'rank_type' => 2,
        'rank_apply_normal' => '',
        'rank_apply_special' => '',
        'rank_apply' => '',
    );

    public function viewRanksAdmin() {
        $aidlink = fusion_get_aidlink();
        pageAccess('F');
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                           'link' => INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=fr',
                           'title' => self::$locale['404']
                       ]);

        $forum_settings = $this->get_forum_settings();

        echo "<div class='well'>".self::$locale['forum_rank_0100']."</div>\n";

        if ($forum_settings['forum_ranks']) {

            $tab['title'][] = self::$locale['402'];
            $tab['id'][] = "rank_list";
            $tab['icon'][] = "";

            $tab['title'][] = isset($_GET['rank_id']) && isnum($_GET['rank_id']) ? self::$locale['401'] : self::$locale['400'];
            $tab['id'][] = "rank_form";
            $tab['icon'][] = "";

            $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $tab['id']) ? $_GET['ref'] : "rank_list";

            echo opentab($tab, $_GET['ref'], "rank_admin", TRUE, "nav-tabs m-t-10", "ref");

            switch ($_GET['ref']) {
                case "rank_form" :
                    echo $this->displayRanksForm();
                    break;
                case "rank_list":
                    echo $this->displayRankList();

            }

            echo closetab();

        } else {
            opentable(self::$locale['403']);
            ?>
            <div class="well text-center">
                <?php
                echo sprintf(self::$locale['450'], "<a href='".FUSION_SELF.$aidlink."&section=fs'>".self::$locale['451']."</a>");
                ?>
            </div>
            <?php
            closetable();
        }


    }

    protected function displayRanksForm() {
        global $aidlink;

        if (isset($_POST['cancel_rank'])) {
            redirect(clean_request("", array("rank_id", "ref"), FALSE));
        }

        add_to_footer("<script src='".FORUM."admin/admin_rank.js'></script>");

        $this->data['rank_language'] = LANGUAGE;

        $array_apply_normal_opts = array(
            USER_LEVEL_MEMBER => self::$locale['424'],
            '-104' => self::$locale['425'],
            USER_LEVEL_ADMIN => self::$locale['426'],
            USER_LEVEL_SUPER_ADMIN => self::$locale['427']
        );

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

        $form_action = FUSION_SELF.$aidlink.'&section=fr&ref=rank_form';

        if (isset($_GET['rank_id']) && isnum($_GET['rank_id'])) {

            $result = dbquery("SELECT * FROM ".DB_FORUM_RANKS." WHERE rank_id='".intval($_GET['rank_id'])."'");

            if (dbrows($result) > 0) {

                $this->data = dbarray($result);

                $form_action = FUSION_SELF.$aidlink."&section=fr&ref=rank_form&rank_id=".$_GET['rank_id']."";

            } else {
                redirect(clean_request("", array("rank_id", "ref"), FALSE));
            }

        }

        $html =
            openform('rank_form', 'post', $form_action, array('class' => 'm-t-20')).

            form_text('rank_title', self::$locale['420'], $this->data['rank_title'],
                      array('required' => 1, 'error_text' => self::$locale['414'], "inline" => TRUE)).

            form_select('rank_image', self::$locale['421'], $this->data['rank_image'],
                        array(
                            'options' => $this->get_rank_images(),
                            'placeholder' => self::$locale['choose'],
                            "inline" => TRUE
                        )
            );

        if (multilang_table("FR")) {
            $html .=
                form_select('rank_language', self::$locale['global_ML100'], $this->data['rank_language'], array(
                    'options' => $language_opts,
                    'placeholder' => self::$locale['choose'], "inline" => TRUE
                ));

        } else {
            $html .= form_hidden('rank_language', '', $this->data['rank_language']);
        }

        $html .= form_checkbox('rank_type', self::$locale['429'], $this->data['rank_type'],
                               array(
                                   "options" => array(
                                       self::$locale['429c'],
                                       self::$locale['429b'],
                                       self::$locale['429a'],
                                   ),
                                   "type" => "radio",
                                   "inline" => TRUE,
                               )
            ).

            form_text('rank_posts', self::$locale['422'], $this->data['rank_posts'],
                      array(
                          'inline' => TRUE,
                          'type' => 'number',
                          'width' => '10%',
                          'disabled' => $this->data['rank_type'] != 0
                      )
            ).

            "<span id='select_normal' ".($this->data['rank_type'] == 2 ? "class='display-none'" : "")." >".

            form_select('rank_apply_normal', self::$locale['423'], $this->data['rank_apply'],
                        array(
                            'options' => $array_apply_normal_opts,
                            'placeholder' => self::$locale['choose'], "inline" => TRUE
                        )).

            "</span>\n<span id='select_special'".($this->data['rank_type'] != 2 ? " class='display-none'" : "").">".

            form_select('rank_apply_special', self::$locale['423'], $this->data['rank_apply'], array(
                'options' => $group_opts, 'placeholder' => self::$locale['choose'], "inline" => TRUE
            )).

            "</span>\n".

            form_button('save_rank', self::$locale['428'], self::$locale['428'], array('class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o')).
            form_button('cancel_rank', self::$locale['cancel'], self::$locale['cancel'], array('class' => 'btn-default', 'icon' => 'fa fa-times')).

            closeform();

        return $html;
    }

    protected function post_forum_ranks() {
        global $aidlink;

        if (isset($_POST['save_rank'])) {

            $this->data = array(
                'rank_id' => isset($_GET['rank_id']) && isnum($_GET['rank_id']) ? intval($_GET['rank_id']) : 0,
                'rank_title' => form_sanitizer($_POST['rank_title'], '', 'rank_title'),
                'rank_image' => form_sanitizer($_POST['rank_image'], "", "rank_image"),
                'rank_language' => form_sanitizer($_POST['rank_language'], "", "rank_language"),
                'rank_posts' => isset($_POST['rank_posts']) && isnum($_POST['rank_posts']) ? $_POST['rank_posts'] : 0,
                'rank_type' => isset($_POST['rank_type']) && isnum($_POST['rank_type']) ? $_POST['rank_type'] : 0,
                'rank_apply_normal' => isset($_POST['rank_apply_normal']) ? $_POST['rank_apply_normal'] : USER_LEVEL_MEMBER,
                'rank_apply_special' => isset($_POST['rank_apply_special']) && isnum($_POST['rank_apply_special']) ? $_POST['rank_apply_special'] : 1,
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
                    addNotice('info', self::$locale['411']);
                    redirect(FUSION_SELF.$aidlink.'&section=fr');

                } elseif (!$this->check_duplicate_ranks()) {
                    /**
                     * Save New
                     */
                    dbquery_insert(DB_FORUM_RANKS, $this->data, "save");
                    addNotice('info', self::$locale['410']);
                    redirect(FUSION_SELF.$aidlink.'&section=fr');

                }
            }
        }

        if (isset($_GET['delete']) && isnum($_GET['delete'])) {
            $result = dbquery("DELETE FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['delete']."'");
            if ($result) {
                addNotice("success", self::$locale['412']);
                redirect(FUSION_SELF.$aidlink.'&section=fr');
            }
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
            addNotice('info', self::$locale['413']);
            redirect(FUSION_SELF.fusion_get_aidlink().'&section=fr');
        }

        return FALSE;
    }

    /**
     * Ranks Listing
     * @return string
     */
    protected function displayRankList() {

        $rank_list_query = "
        SELECT * FROM ".DB_FORUM_RANKS."
        ".(multilang_table("FR") ? "WHERE rank_language='".LANGUAGE."'" : "")."
        ORDER BY rank_type DESC, rank_apply DESC, rank_posts
        ";

        $result = dbquery( $rank_list_query );

        if ( dbrows($result) > 0 ) {

            $html = "<table class='table table-responsive table-striped table-hover center m-t-20'>\n<thead>\n<tr>\n".
            "<th class='col-xs-4'>".self::$locale['430']."</th>\n".
            "<th>".self::$locale['431']."</th>\n".
            "<th>".self::$locale['432']."</th>\n".
            "<th>".self::$locale['438']."</th>\n".
            "<th class='text-center'>".self::$locale['434']."</th>\n".
            "</tr>\n".
            "</thead>\n<tbody>\n";

            $i = 0;
            while ($data = dbarray($result)) {

                $html .= "<tr>\n".
                "<td '>".$data['rank_title']."</td>\n".
                "<td>".($data['rank_apply'] == -104 ? self::$locale['425'] : getgroupname($data['rank_apply']))."</td>\n".
                "<td class='col-xs-2'>".ForumServer::show_forum_rank($data['rank_posts'], $data['rank_apply'], $data['rank_apply'])."</td>\n".
                "<td>";

                if ($data['rank_type'] == 0) {
                    $html .= $data['rank_posts'];
                } elseif ($data['rank_type'] == 1) {
                    $html .= self::$locale['429b'];
                } else {
                    $html .= self::$locale['429a'];
                }

                $html .= "</td>\n<td width='1%' style='white-space:nowrap'>".
                "<a href='".clean_request("section=fr&ref=rank_form&rank_id=".$data['rank_id']."", array("rank_id", "ref"), false)."'>".self::$locale['435']."</a> -\n".
                "<a href='".clean_request("section=fr&ref=rank_form&delete=".$data['rank_id']."", array("rank_id", "ref"), false)."'>".self::$locale['436']."</a></td>\n</tr>\n";

                $i++;
            }
            $html .= "</tbody>\n</table>";
        } else {

            $html = "<div class='well text-center'>".self::$locale['437']."</div>\n";

        }
        return $html;
    }
}
