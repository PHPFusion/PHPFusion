<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Members.inc
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
 *
 * @package PHPFusion
 */
class Members {
    protected static $filters = array();
    private static $instance = NULL;
    private static $default_condition = '';

    private static $locale = array();

    private static $rows = 0;
    private $default_info = array(
        'search_filter' => '',
        'member' => array(),
        'page_nav' => '',
        'page_result' => '',
        'search_table' => '',
    );

    private function __construct() {
        if (!isset($_GET['sortby']) || !ctype_alnum($_GET['sortby'])) {
            $_GET['sortby'] = "all";
        }
        $_GET['orderby'] = isset($_GET['orderby']) ? $_GET['orderby'] : 'active';
        $_GET['sort_order'] = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
        $_GET['search_text'] = isset($_GET['search_text']) ? $_GET['search_text'] : ($_GET['sortby'] != "all" ? $_GET['sortby'] : '');
    }

    public static function getInstance($set_info = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        if ($set_info) {
            self::$locale = fusion_get_locale('', LOCALE.LOCALESET."members.php");
            add_to_title(self::$locale['global_200'].self::$locale['MEMB_000'].SiteLinks::get_current_SiteLinks("", "link_name"));

            // get all params
            //self::$rows = dbcount("(user_id)", DB_USERS, (iADMIN ? "user_status>='0'" : "user_status='0'").self::getFilters());
            self::$rows = self::$instance->getMemberRows();

            $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= self::$rows) ? $_GET['rowstart'] : 0;
        }
        return self::$instance;
    }

    private function getMemberRows() {

        $select = !empty(self::$filters['select']) ? ', '.self::$filters['select'] : '';

        $condition = !empty(self::$filters['condition']) ? "AND ".self::$filters['condition'] : self::getFilters();

        $groupBy = !empty(self::$filters['group_by']) ? self::$filters['group_by'] : 'u.user_id';

        $join = !empty(self::$filters['join']) ? self::$filters['join'] : '';

        $result = dbquery("
                SELECT u.user_id $select
                FROM ".DB_USERS." u $join
                WHERE ".(iADMIN ? "u.user_status>='0'" : "u.user_status='0'").self::$default_condition."
                $condition GROUP BY $groupBy
                ");

        return dbrows($result);

    }

    private static function getFilters() {
        // alpha select condition
        $default_condition = ($_GET['sortby'] == "all" ? "" : " AND user_name LIKE '".stripinput($_GET['sortby'])."%'");
        return ((isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i",
                                                           $_GET['search_text'])) ? ' AND user_name LIKE "'.stripinput($_GET['search_text']).'%"' : $default_condition);
    }

    // condition filters

    public function display_members() {

        if (iMEMBER) {

            $search_form = openform('searchform', 'get', BASEDIR."members.php?");
            $search_form .= "<div class='display-inline-block pull-left m-r-10' style='width:300px;'>\n";
            $search_form .= form_text('search_text', '', $_GET['search_text'],
                                      array(
                                          'inline' => TRUE,
                                          'placeholder' => self::$locale['MEMB_005'],
                                          'append_button' => TRUE,
                                          'append_type' => "submit",
                                          "append_form_value" => self::$locale['MEMB_006'],
                                          "append_value" => "<i class='fa fa-search'></i> ".self::$locale['MEMB_006'],
                                          "append_button_name" => self::$locale['MEMB_006'],
                                          "width" => "260px",
                                          'class' => 'no-border m-b-0'
                                      )
            );
            $search_form .= "</div>\n";
            $search_form .= "<div class='display-inline-block' style='vertical-align:top;'>\n";
            $search_form .= form_select('orderby', self::$locale['MEMB_007'], $_GET['orderby'],
                                                                              array(
                                                                                  'options' => array(
                                                                                      'active' => self::$locale['MEMB_008'],
                                                                                      'registered' => self::$locale['MEMB_009'],
                                                                                      'name' => self::$locale['MEMB_010']
                                                                                  ),
                                                                                  'inline' => TRUE,
                                                                                  'inner_width' => '150px',
                                                                                  'class' => 'm-0 p-0'
                                                                              )
                );
            $search_form .= "</div>\n";
            $search_form .= "<div class='display-inline-block' style='vertical-align:top;'>\n";
            $search_form .= form_select("sort_order", "", $_GET['sort_order'], array(
                "allowclear" => TRUE, 'inner_width' => "150px", "options" => array(
                    'ASC' => self::$locale['MEMB_012'],
                    'DESC' => self::$locale['MEMB_013'],
                )
            ));
            $search_form .= "</div>\n";
            $search_form .= closeform();
            add_to_jquery("
            $('#orderby').bind('change', function(e) {
                $(this).closest('form').submit();
            });
            $('#sort_order').bind('change', function(e) {
                $(this).closest('form').submit();
            });
            ");

            $info = array(
                'search_filter' => array_merge(range("A", "Z"), range(0, 9)),
                'rows' => self::$rows,
                'search_form' => $search_form,
            );

            $info['search_table'] = "<table class='table table-responsive table-striped center alphabet-table'>\n<tr>\n";
            $info['search_table'] .= "<td rowspan='2' class='tbl2'><a class='strong' href='".BASEDIR."members.php?sortby=all'>".self::$locale['MEMB_014']."</a></td>";
            for ($i = 0; $i < count($info['search_filter']) != ""; $i++) {
                $info['search_table'] .= "<td align='center' class='tbl1'><div class='small'><a href='".BASEDIR."members.php?sortby=".$info['search_filter'][$i]."'>".$info['search_filter'][$i]."</a></div></td>";
                $info['search_table'] .= ($i == 17 ? "<td rowspan='2' class='tbl2'><a class='strong' href='".BASEDIR."members.php?sortby=all'>".self::$locale['MEMB_014']."</a></td>\n</tr>\n<tr>\n" : "\n");
            }
            $info['search_table'] .= "</tr>\n</table>\n";

            if (self::$rows > 0) {

                $result = dbquery(self::get_MembersQuery());

                $current_rows = dbrows($result);

                if ($current_rows) {

                    $info['page_nav'] = makepagenav($_GET['rowstart'], 24, self::$rows, 3, BASEDIR."members.php?sortby=".$_GET['sortby']."&amp;");

                    while ($data = dbarray($result)) {

                        $info['member'][$data['user_id']] = $data;
                        $info['member'][$data['user_id']]['user_avatar'] = display_avatar($data, '50px', '', TRUE, 'img-rounded');
                        $info['member'][$data['user_id']]['default_group'] = ($data['user_level'] == USER_LEVEL_SUPER_ADMIN ? self::$locale['MEMB_016'] : self::$locale['MEMB_015']);

                        $user_groups = explode(".", $data['user_groups']);
                        if (!empty($user_groups)) {
                            foreach ($user_groups as $key => $value) {
                                if ($value) {
                                    $info['member'][$data['user_id']]['groups'][$key] = array(
                                        'title' => getgroupname($value, FALSE, TRUE),
                                        'link' => BASEDIR."profile.php?group_id=".$value
                                    );
                                }
                            }
                        }
                    }

                    $end_rows = isset($_GET['rowstart']) && $_GET['rowstart'] > 0 ? $current_rows + $_GET['rowstart'] : $current_rows;

                    $info['page_result'] = strtr(self::$locale['MEMB_017'],
                                                 array(
                                                     "{%start_row%}" => ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart']),
                                                     "{%end_row%}" => $end_rows,
                                                     "{%max_row%}" => $info['rows'],
                                                     "{%member%}" => Locale::format_word($info['rows'], self::$locale['fmt_member'],
                                                                                         array(
                                                                                             'add_count' => FALSE,
                                                                                         )
                                                     )
                                                 )
                    );

                }
            }


            $info['no_result'] = self::$locale['MEMB_018'].(isset($_GET['search_text']) ? $_GET['search_text'] : $_GET['sortby']);

            $info += $this->default_info;
            render_members($info);

            return $info;

        } else {
            redirect(BASEDIR."index.php");
        }

    }

    protected static function get_MembersQuery() {

        $select = !empty(self::$filters['select']) ? ', '.self::$filters['select'] : '';

        $condition = !empty(self::$filters['condition']) ? "AND ".self::$filters['condition'] : self::getFilters();

        $groupBy = !empty(self::$filters['group_by']) ? self::$filters['group_by'] : 'u.user_id';

        $join = !empty(self::$filters['join']) ? self::$filters['join'] : '';

        $limit = isset(self::$filters['limit']) ? self::$filters['limit'] : 24;

        $default_sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

        $default_sorting = "u.user_level DESC, u.user_language DESC, u.user_name $default_sort_order";

        if (isset($_GET['orderby'])) {
            switch ($_GET['orderby']) {
                case 'active':
                    $default_sorting = "u.user_lastvisit $default_sort_order, $default_sorting";
                    break;
                case 'registered':
                    $default_sorting = "u.user_joined $default_sort_order, $default_sorting";
                    break;
                case 'name':
                    $default_sorting = "u.user_name $default_sort_order, $default_sorting";
                    break;
            }
        }

        $order = !empty(self::$filters['order']) ? self::$filters['order'] : $default_sorting;

        $query = "
                SELECT u.user_id, u.user_name, u.user_status, u.user_level, u.user_groups,
                u.user_language, u.user_joined, u.user_avatar, u.user_lastvisit $select
                FROM ".DB_USERS." u $join
                WHERE ".(iADMIN ? "u.user_status>='0'" : "u.user_status='0'").self::$default_condition."
                $condition GROUP BY $groupBy ORDER BY $order LIMIT ".intval($_GET['rowstart']).", $limit
                ";
        return $query;
    }

    /**
     * Set custom filters
     * @param array $filters
     *      Indexes:    'select' - query selection,
     *                  'condition', - query condition
     *                  'order', - order
     *                  'limit', - limitations
     *                  'join' - join statements
     * @return string
     */
    public function setFilters(array $filters = array()) {
        self::$filters = $filters;
    }

    private function __clone() {
    }

}
