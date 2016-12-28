<?php
namespace Administration\Members;

class Members_View extends Members_Admin {

    public static function display_members() {
        $locale = self::$locale;

        if (isset($_GET['status']) && ($_GET['status']) == "der") {
            addNotice("warning", $locale['error']);
        }
        if (isset($_GET['status']) && ($_GET['status']) == "dok") {
            addNotice("success", $locale['422']);
        }
        $html = "{%opentable%}";
        $html .= "<div class='clearfix'>
        <div class='pull-right'>{%action_button%}</div>
        <div class='pull-left'>{%filter_text%} {%filter_button%}</div>
        </div>
        <!----filter---->
        <div id='filter_panel' class='spacer-xs' style='display:none'>            
            <div class='list-group-item'>
                <div class='row'>
                    <div class='col-xs-3'><strong>Display Results.</strong></div>
                    <div class='col-xs-9'>{%filter_options%}{%filter_extras%}</div>
                </div>
            </div>                        
            <div class='list-group-item spacer-xs'>
                <div class='row'>
                    <div class='col-xs-3'><strong>Display User With Status</strong></div>
                    <div class='col-xs-9'>{%filter_status%}</div>
                </div>
            </div>                                    
            <br/>{%filter_apply_button%}
        </div>
        <!----//filter---->
        <hr/>       
        <div class='clearfix spacer-xs'>{%page_count%}<div class='pull-right'>{%page_nav%}</div></div>
        <div class='list-group-item spacer-sm p-5'>{%user_actions%}</div>
        <table id='user_table' class='table table-hover table-striped ".fusion_sort_table('user_table')."'>
            <thead>
                {%list_head%}
                {%list_column%}                
            </thead>
            <tbody>
                {%list_result%}
            </tbody>
            <tfoot>
            {%list_footer%}
            </tfoot>
        </table>
        ";
        $html .= "{%closetable%}";

        return $html;

        echo "<div class='clearfix'><div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-3'>";
        echo "<div class='btn-group m-b-15'>\n";
        echo "<a class='button btn btn-primary' href='".FUSION_SELF.fusion_get_aidlink()."&amp;step=add'>".$locale['402']."</a>\n";
        if (self::$settings['enable_deactivation'] == 1) {
            if (dbcount("(user_id)", DB_USERS, "user_status='0' AND user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".self::$time_overdue."' AND user_actiontime='0'")) {
                echo "<a class='button btn btn-default' href='".FUSION_SELF.fusion_get_aidlink()."&amp;step=inactive'>".$locale['580']."</a>\n";
            }
        }
        echo "</div>\n";
        echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-3 pull-right'>";
        echo openform('viewstatus', 'get', FUSION_SELF.fusion_get_aidlink(), array('class' => 'p-0 m-t-0 m-b-15'));
        echo "</div>\n</div>\n</div>\n";
        if (isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i", $_GET['search_text'])) {
            $user_name = " user_name LIKE '".stripinput($_GET['search_text'])."%' AND";
            $list_link = "search_text=".stripinput($_GET['search_text']);
        } elseif (isset($_GET['sortby']) && preg_check("/^[0-9A-Z]$/", $_GET['sortby'])) {
            $user_name = ($_GET['sortby'] == "all" ? "" : " user_name LIKE '".stripinput($_GET['sortby'])."%' AND");
            $list_link = "sortby=".stripinput($_GET['sortby']);
        } else {
            $user_name = "";
            $list_link = "sortby=all";
            $_GET['sortby'] = "all";
        }
        $rows = dbcount("(user_id)", DB_USERS, "$user_name user_status='".self::$usr_mysql_status."' AND user_level>".USER_LEVEL_SUPER_ADMIN);
        if ($rows) {
            $result = dbquery("
                SELECT user_id, user_name, user_level, user_avatar, user_status FROM ".DB_USERS."
		        WHERE $user_name user_status='".self::$usr_mysql_status."' AND user_level>".USER_LEVEL_SUPER_ADMIN."
                ORDER BY user_level DESC, user_name
		        LIMIT ".self::$rowstart.",20");

            $i = 0;
            echo "<div class='list-group clearfix'>\n";
            while ($data = dbarray($result)) {
                echo "<div class='list-group-item clearfix'>\n";
                echo "<div class='pull-left m-r-10'>\n".display_avatar($data, '50px', '', '', 'img-rounded')."</div>\n";
                echo "<div class='pull-right m-l-15 m-t-10'>\n";
                echo "<div class='btn-group'>\n";
                if (iSUPERADMIN || $data['user_level'] > -102) {
                    echo "<a class='btn button btn-sm btn-default ' href='".FUSION_SELF.fusion_get_aidlink()."&amp;step=edit&amp;user_id=".$data['user_id']."&amp;settings'>".$locale['406']."</a>\n";
                    if (self::$status == 0) {
                        echo "<a class='btn button btn-sm btn-default ' href='".stripinput(USER_MANAGEMENT_SELF."&action=3&user_id=".$data['user_id'])."'>".$locale['553']."</a>\n";
                    } elseif (self::$status == 2) {
                        $title = $locale['407'];
                    } elseif (self::$status != 8) {
                        $title = $locale['419'];
                    }
                    if (isset($title)) {
                        echo "<a class='btn button btn-sm btn-default' href='".stripinput(USER_MANAGEMENT_SELF."&action=".self::$status."&amp;user_id=".$data['user_id'])."'>$title</a>\n";
                    }
                    echo "<div class='btn-group'>\n";
                    echo "<a class='btn button btn-sm btn-default' href='".stripinput(USER_MANAGEMENT_SELF."&step=delete&user_id=".$data['user_id'])."'>".$locale['410']."</a>\n";
                    // more actions.
                    echo "<a class='btn button btn-sm btn-default dropdown-toggle' data-toggle='dropdown'>\n<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></a>\n";
                    echo "<ul class='dropdown-menu text-left' role='action-menu'>\n";
                    foreach(self::$link_uri as $key => $uri_value) {
                        $uri_value = strtr($uri_value, ['{%user_id%}'=>$data['user_id']]);
                        echo "<li><a href='$uri_value'>".getsuspension($key, TRUE)."</a></li>\n";
                    }
                    echo "</ul>\n";
                    echo "</div>\n";
                }
                echo "</div>\n";
                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo "<a class='strong display-inline-block' href='".FUSION_SELF.fusion_get_aidlink()."&amp;step=view&amp;user_id=".$data['user_id']."'>".$data['user_name']."</a>\n";
                echo "<br/><span class='text-smaller'>".getuserlevel($data['user_level'])."</span>\n";
                echo "</div>\n";
                echo "</div>\n";
                $i++;
            }
            echo "</div>\n";

        } else {
            if (isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i", $_GET['search_text'])) {
                echo "<div class='well' style='text-align:center'><br />".sprintf($locale['411'],
                        (self::$status == 0 ? "" : getsuspension(self::$status))).$locale['413']."'".stripinput($_GET['search_text'])."'<br /><br />\n</div>\n";
            } else {
                echo "<div class='well' style='text-align:center'><br />".sprintf($locale['411'],
                        (self::$status == 0 ? "" : getsuspension(self::$status))).($_GET['sortby'] == "all" ? "" : $locale['412'].$_GET['sortby']).".<br /><br />\n</div>\n";
            }
        }


        echo "<hr/>\n";
        $alphanum = array(
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z",
            "0",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9"
        );
        echo "<table class='table table-responsive table-striped center'>\n<tr>\n";
        echo "<td rowspan='2' class='tbl2'><a class='strong' href='".FUSION_SELF.fusion_get_aidlink()."&amp;status=".self::$status."'>".$locale['414']."</a></td>";
        for ($i = 0; $i < 36; $i++) {
            echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF.fusion_get_aidlink()."&amp;sortby=".$alphanum[$i]."&amp;status=".self::$status."'>".$alphanum[$i]."</a></div></td>";
            echo($i == 17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.fusion_get_aidlink()."&amp;status=".self::$status."'>".$locale['414']."</a></td>\n</tr>\n<tr>\n" : "\n");
        }
        echo "</tr>\n</table>\n";
        echo "<hr />\n";
        echo openform('searchform', 'get', FUSION_SELF.fusion_get_aidlink(), array('max_tokens' => 1, 'notice' => 0));
        echo form_hidden('aid', '', iAUTH);
        echo form_hidden('status', '', self::$status);
        echo form_text('search_text', $locale['415'], '', array('inline' => 1));
        echo form_button('search', $locale['416'], $locale['416'], array('class' => 'col-sm-offset-3 btn-sm btn-primary'));
        echo closeform();
        closetable();
        if ($rows > 20) {
            echo "<div align='center' style='margin-top:5px;'>\n".makepagenav(self::$rowstart, 20, $rows, 3, FUSION_SELF.fusion_get_aidlink()."&amp;sortby=".self::$sortby."&amp;status=".self::$status."&amp;")."\n</div>\n";
        }

    }








}
require_once(THEMES.'templates/global/profile.php');