<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/view.php
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

class ForumAdminRanks extends ForumAdminInterface {

    /**
     * @Todo - shorten
     */
    public function viewRanksAdmin() {

        global $aidlink;
        pageAccess('FR');
        $forum_settings = $this->get_forum_settings();
        $language_opts = fusion_get_enabled_languages();
        add_breadcrumb(array('link'=>INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=fr', 'title'=>self::$locale['404']));

        if ($forum_settings['forum_ranks']) {

            if (isset($_POST['save_rank'])) {

                $rank_title = form_sanitizer($_POST['rank_title'], '', 'rank_title');
                $rank_image = form_sanitizer($_POST['rank_image'], "", "rank_image");
                $rank_language = form_sanitizer($_POST['rank_language'], "", "rank_language");
                $rank_posts = isset($_POST['rank_posts']) && isnum($_POST['rank_posts']) ? $_POST['rank_posts'] : 0;
                $rank_type = isset($_POST['rank_type']) && isnum($_POST['rank_type']) ? $_POST['rank_type'] : 0;
                $rank_apply_normal = isset($_POST['rank_apply_normal']) && isnum($_POST['rank_apply_normal']) ? $_POST['rank_apply_normal'] : USER_LEVEL_MEMBER;
                $rank_apply_special = isset($_POST['rank_apply_special']) && isnum($_POST['rank_apply_special']) ? $_POST['rank_apply_special'] : 1;
                $rank_apply = $rank_type == 2 ? $rank_apply_special : $rank_apply_normal;

                if (\defender::safe()) {

                    if (isset($_GET['rank_id']) && isnum($_GET['rank_id'])) {

                        $data = dbarray(dbquery("SELECT rank_apply FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['rank_id']."'"));

                        if (($rank_apply < USER_LEVEL_MEMBER && $rank_apply != $data['rank_apply']) && (dbcount("(rank_id)", DB_FORUM_RANKS, "".(multilang_table("FR") ? "rank_language='".LANGUAGE."' AND" : "")." rank_id!='".$_GET['rank_id']."' AND rank_apply='".$rank_apply."'"))) {
                            addNotice('info', self::$locale['413']);
                            redirect(FUSION_SELF.$aidlink.'&section=fr');
                        } else {
                            $result = dbquery("UPDATE ".DB_FORUM_RANKS." SET rank_title='".$rank_title."', rank_image='".$rank_image."', rank_posts='".$rank_posts."', rank_type='".$rank_type."', rank_apply='".$rank_apply."', rank_language='".$rank_language."' WHERE rank_id='".$_GET['rank_id']."'");
                            addNotice('info', self::$locale['411']);
                            redirect(FUSION_SELF.$aidlink.'&section=fr');
                        }

                    } else {

                        if ($rank_apply < USER_LEVEL_MEMBER && dbcount("(rank_id)", DB_FORUM_RANKS, "".(multilang_table("FR") ? "rank_language='".LANGUAGE."' AND" : "")." rank_apply='".$rank_apply."'")) {

                            addNotice('info', self::$locale['413']);
                            redirect(FUSION_SELF.$aidlink.'&section=fr');

                        } else {

                            $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('$rank_title', '$rank_image', '$rank_posts', '$rank_type', '$rank_apply', '$rank_language')");
                            addNotice('success', self::$locale['410']);
                            redirect(FUSION_SELF.$aidlink.'&section=fr');

                        }
                    }
                }
            } else if (isset($_GET['delete']) && isnum($_GET['delete'])) {
                $result = dbquery("DELETE FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['delete']."'");
                addNotice("success", self::$locale['412']);
                redirect(FUSION_SELF.$aidlink.'&section=fr');
            }

            $rank_title = "";
            $rank_image = "";
            $rank_posts = "0";
            $rank_type = "2";
            $rank_apply = "";
            $rank_language = LANGUAGE;
            $form_action = FUSION_SELF.$aidlink.'&section=fr';

            if (isset($_GET['rank_id']) && isnum($_GET['rank_id'])) {
                $result = dbquery("SELECT rank_id, rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['rank_id']."'");
                if (dbrows($result)) {
                    $data = dbarray($result);
                    $rank_title = $data['rank_title'];
                    $rank_image = $data['rank_image'];
                    $rank_posts = $data['rank_posts'];
                    $rank_type = $data['rank_type'];
                    $rank_apply = $data['rank_apply'];
                    $rank_language = $data['rank_language'];
                    $form_action = FUSION_SELF.$aidlink."&section=fr&rank_id=".$_GET['rank_id'];
                    opentable(self::$locale['401']);
                } else {
                    redirect(FUSION_SELF.$aidlink.'&section=fr');
                }
            } else {
                opentable(self::$locale['400']);
            }
            echo openform('rank_form', 'post', $form_action, array('max_tokens' => 1));

            echo form_text('rank_title', self::$locale['420'], $rank_title, array('required' => 1, 'error_text' => self::$locale['414'], "inline"=>TRUE));

            $image_files = makefilelist(RANKS."", ".|..|index.php|.svn|.DS_Store", TRUE);
            foreach ($image_files as $value) {
                $opts[$value] = $value;
            }
            echo form_select('rank_image', self::$locale['421'], $rank_image, array('options' => $opts, 'placeholder' => self::$locale['choose'], "inline"=>TRUE));

            if (multilang_table("FR")) {
                echo form_select('rank_language', self::$locale['global_ML100'], $rank_language, array('options' => $language_opts,
                                                                                                 'placeholder' => self::$locale['choose'], "inline"=>TRUE));
            } else {
                echo form_hidden('rank_language', '', $rank_language);
            }
            echo form_checkbox('rank_type', self::$locale['429'], $rank_type,
                               array("options"=>array(
                                   2 => self::$locale['429a'],
                                   1 => self::$locale['429b'],
                                   0 => self::$locale['429c'],
                               ),
                                     "type" => "radio",
                                     "inline" => TRUE,
                               )
            );
            echo form_text('rank_posts', self::$locale['422'], $rank_posts, array("inline"=>TRUE, 'disabled' => $rank_type != 0));


            $array = array(
                USER_LEVEL_MEMBER => self::$locale['424'],
                '104' => self::$locale['425'],
                USER_LEVEL_ADMIN => self::$locale['426'],
                USER_LEVEL_SUPER_ADMIN => self::$locale['427']
            );

            echo "<span id='select_normal' ".($rank_type == 2 ? "class='display-none'" : "")." >";
            echo form_select('rank_apply_normal', self::$locale['423'], $rank_apply, array('options' => $array, 'placeholder' => self::$locale['choose'], "inline"=>TRUE));
            echo "</span>\n";


            // Special Select
            $groups_arr = getusergroups();
            $groups_except = array(USER_LEVEL_PUBLIC, USER_LEVEL_MEMBER, USER_LEVEL_ADMIN, USER_LEVEL_SUPER_ADMIN);
            $group_opts = array();
            foreach ($groups_arr as $group) {
                if (!in_array($group[0], $groups_except)) {
                    $group_opts[$group[0]] = $group[1];
                }
            }

            echo "<span id='select_special'".($rank_type != 2 ? " class='display-none'" : "").">";
            echo form_select('rank_apply_special', self::$locale['423'], $rank_apply, array('options' => $group_opts, 'placeholder' => self::$locale['choose'], "inline"=>TRUE));
            echo "</span>\n";

            /* echo "<td class='tbl'><strong>".self::$locale['429']."</strong></td>\n";
            echo "<td class='tbl'>\n";
            echo "<label><input type='radio' name='rank_type' value='2'".($rank_type == 2 ? " checked='checked'" : "")." /> ".self::$locale['429a']."</label>\n";
            echo "<label><input type='radio' name='rank_type' value='1'".($rank_type == 1 ? " checked='checked'" : "")." /> ".self::$locale['429b']."</label>\n";
            echo "<label><input type='radio' name='rank_type' value='0'".($rank_type == 0 ? " checked='checked'" : "")." /> ".self::$locale['429c']."</label>\n";
            echo "</td>\n";
            echo "</tr>\n<tr>\n";
            */

            echo form_button('save_rank', self::$locale['428'], self::$locale['428'], array('class' => 'btn-primary'));
            closetable();

            opentable(self::$locale['402']);
            $result = dbquery("SELECT rank_id, rank_title, rank_image, rank_posts, rank_type, rank_apply FROM ".DB_FORUM_RANKS." ".(multilang_table("FR") ? "WHERE rank_language='".LANGUAGE."'" : "")." ORDER BY rank_type DESC, rank_apply DESC, rank_posts");
            if (dbrows($result)) {
                echo "<table cellpadding='0' cellspacing='1' class='table table-responsive tbl-border center'>\n<thead>\n<tr>\n";
                echo "<th class='tbl2'><strong>".self::$locale['430']."</strong></th>\n";
                echo "<th width='1%' class='tbl2' style='white-space:nowrap'><strong>".self::$locale['431']."</strong></th>\n";
                echo "<th width='1%' class='tbl2' style='white-space:nowrap'><strong>".self::$locale['432']."</strong></th>\n";
                echo "<th width='1%' class='tbl2' style='white-space:nowrap'><strong>".self::$locale['438']."</strong></th>\n";
                echo "<th align='center' width='1%' class='tbl2' style='white-space:nowrap'><strong>".self::$locale['434']."</strong></th>\n";
                echo "</tr>\n";
                echo "</thead>\n<tbody>\n";
                $i = 0;
                while ($data = dbarray($result)) {
                    $row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
                    echo "<tr>\n";
                    echo "<td class='".$row_color."'>".$data['rank_title']."</td>\n";
                    echo "<td width='1%' class='".$row_color."' style='white-space:nowrap'>".($data['rank_apply'] == 104 ? self::$locale['425'] : getgroupname($data['rank_apply']))."</td>\n";
                    echo "<td width='1%' class='".$row_color."' style='white-space:nowrap'><img src='".RANKS.$data['rank_image']."' alt='' style='border:0;' /></td>\n";
                    echo "<td width='1%' class='".$row_color."' style='white-space:nowrap'>";
                    if ($data['rank_type'] == 0) {
                        echo $data['rank_posts'];
                    } elseif ($data['rank_type'] == 1) {
                        echo self::$locale['429b'];
                    } else {
                        echo self::$locale['429a'];
                    }
                    echo "</td>\n<td width='1%' class='".$row_color."' style='white-space:nowrap'>";
                    echo "<a href='".FUSION_SELF.$aidlink."&amp;rank_id=".$data['rank_id']."&amp;section=fr'>".self::$locale['435']."</a> -\n";
                    echo "<a href='".FUSION_SELF.$aidlink."&amp;delete=".$data['rank_id']."&amp;section=fr'>".self::$locale['436']."</a></td>\n</tr>\n";
                    $i++;
                }
                echo "</tbody>\n</table>";
            } else {
                echo "<div style='text-align:center'>".self::$locale['437']."</div>\n";
            }
            closetable();
        } else {
            opentable(self::$locale['403']);
            echo "<div style='text-align:center'>\n".sprintf(self::$locale['450'], "<a href='".FUSION_SELF.$aidlink."&section=fs'>".self::$locale['451']."</a>")."</div>\n";
            closetable();
        }
        echo "<script language='JavaScript' type='text/javascript'>
jQuery(function(){
	jQuery('input:radio[name=rank_type]').change(function() {
		var val = jQuery('input:radio[name=rank_type]:checked').val(),
			special = jQuery('#select_special'),
			normal = jQuery('#select_normal'),
			posts = jQuery('#rank_posts');
		if (val == 2) {
			special.show();
			normal.hide();
			posts.attr('readonly', 'readonly');
		} else {
			if (val == 1) {
				posts.attr('readonly', 'readonly');
			} else {
				posts.removeAttr('readonly');
			}
			special.hide();
			normal.show();
		}
	});
});
</script>";

    }


}