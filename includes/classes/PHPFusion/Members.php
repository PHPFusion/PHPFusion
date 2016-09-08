<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PHPFusion\Members.php
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
namespace PHPFusion;

/**
 * Class Members
 * @package PHPFusion
 */
class Members {

    private static $instance = NULL;

    private $default_info = array(
        'search_filter' => '',
        'member' => array(
            'groups' => array()
        ),
        'page_nav' => '',
        'search_table' => '',
    );

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }


    public function display_members() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."members.php");

        add_to_title($locale['global_200'].$locale['400'].SiteLinks::get_current_SiteLinks("", "link_name"));

        if (iMEMBER) {

            if (!isset($_GET['sortby']) || !ctype_alnum($_GET['sortby'])) {
                $_GET['sortby'] = "all";
            }

            $orderby = ($_GET['sortby'] == "all" ? "" : " AND user_name LIKE '".stripinput($_GET['sortby'])."%'");
            $search_text = ((isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i",
                                                                       $_GET['search_text'])) ? ' AND user_name LIKE "'.stripinput($_GET['search_text']).'%"' : $orderby);
            $rows = dbcount("(user_id)", DB_USERS, (iADMIN ? "user_status>='0'" : "user_status='0'").$search_text);

            $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $rows) ? $_GET['rowstart'] : 0;

            $search_form = openform('searchform', 'get', FUSION_SELF);
            $search_form .= form_text('search_text', $locale['408'], '', array(
                'inline' => TRUE,
                'placeholder' => $locale['401'],
                'append_button' => TRUE,
                'append_type' => "submit",
                "append_form_value" => $locale['409'],
                "append_value" => "<i class='fa fa-search'></i> ".$locale['409'],
                "append_button_name" => $locale['409'],
                'class' => 'no-border m-b-0',
            ));
            $search_form .= closeform();

            $info = array(
                'search_filter' => array_merge(range("A", "Z"), range(0, 9)),
                'rows' => $rows,
                'search_form' => $search_form,
            );

            $info['search_table'] = "<table class='table table-responsive table-striped center'>\n<tr>\n";
            $info['search_table'] .= "<td rowspan='2' class='tbl2'><a class='strong' href='".FUSION_SELF."?sortby=all'>".$locale['404']."</a></td>";
            for ($i = 0; $i < count($info['search_filter']) != ""; $i++) {
                $info['search_table'] .= "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF."?sortby=".$info['search_filter'][$i]."'>".$info['search_filter'][$i]."</a></div></td>";
                $info['search_table'] .= ($i == 17 ? "<td rowspan='2' class='tbl2'><a class='strong' href='".FUSION_SELF."?sortby=all'>".$locale['404']."</a></td>\n</tr>\n<tr>\n" : "\n");
            }
            $info['search_table'] .= "</tr>\n</table>\n";


            if ($rows > 0) {
                // @todo: support extra queries
                $result = dbquery("SELECT user_id, user_name, user_status, user_level, user_groups, user_language, user_joined, user_avatar
                FROM ".DB_USERS."
                WHERE ".(iADMIN ? "user_status>='0'" : "user_status='0'").$orderby."
                ORDER BY user_level DESC, user_language, user_name ASC
                LIMIT ".intval($_GET['rowstart']).",20"
                );
                $current_rows = dbrows($result);

                if ($current_rows) {

                    $info['page_nav'] = makepagenav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF."?sortby=".$_GET['sortby']."&amp;");

                    while ($data = dbarray($result)) {

                        $info['member'][$data['user_id']] = $data;
                        $info['member'][$data['user_id']]['user_avatar'] = display_avatar($data, '50px');
                        $info['member'][$data['user_id']]['default_group'] = ($data['user_level'] == USER_LEVEL_SUPER_ADMIN ? $locale['407'] : $locale['406']);

                        $user_groups = explode(".", $data['user_groups']);
                        if (!empty($user_groups)) {
                            foreach ($user_groups as $key => $value) {
                                if ($value) {
                                    $info['member'][$data['user_id']]['groups'][$key] = array(
                                        'title' => getgroupname($value),
                                        'link' => BASEDIR."profile.php?group_id=".$value
                                    );
                                }
                            }
                        }
                    }
                }
            }

            $info['no_result'] = $locale['403'].(isset($_GET['search_text']) ? $_GET['search_text'] : $_GET['sortby']);

            $info += $this->default_info;
            render_members($info);

            return $info;

        } else {
            redirect(BASEDIR."index.php");
        }

    }

}