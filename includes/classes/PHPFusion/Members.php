<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Members.php
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
namespace PHPFusion;

/**
 * Class Members
 *
 * @package PHPFusion
 */
class Members {
    protected static $filters = [];
    private static $instance = NULL;
    private static $locale = [];
    private static $max_rows = 0;
    private $default_info = [
        'search_filter' => '',
        'member'        => [],
        'page_nav'      => '',
        'page_result'   => '',
        'search_table'  => '',
    ];
    private $sortby = "all";
    private $orderby = "active";
    private $sort_order = "ASC";
    private $search_text = "";
    private $rowstart = 0;

    private function __construct() {
        $sortby = isset($_GET['sortby']) ? $_GET['sortby'] : $this->sortby;
        if ($sortby) {
            if (in_array($sortby, array_merge(range("A", "Z"), range(0, 9)))) {
                $this->sortby = $sortby;
            }
        }

        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : $this->orderby;
        if ($orderby) {
            if (in_array($orderby, ["active", "registered", "name"])) {
                $this->orderby = $orderby;
            }
        }

        $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : $this->sort_order;
        if ($sort_order) {
            if (in_array($sort_order, ["ASC", "DESC"])) {
                $this->sort_order = $sort_order;
            }
        }

        $search_text = isset($_GET['search_text']) ? $_GET['search_text'] : $this->sortby;
        if ($search_text) {
            $search_text = stripinput(descript($search_text));
            if (preg_check("/^[-0-9A-Z_@\s]+$/i", $search_text)) {
                $this->search_text = $search_text;
            }
        }
    }

    /**
     * @param bool $set_info
     *
     * @return static|null
     */
    public static function getInstance($set_info = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
            if ($set_info) {
                self::$locale = fusion_get_locale('', LOCALE.LOCALESET."members.php");
                add_to_title(self::$locale['MEMB_000'].SiteLinks::getCurrentSiteLinks("", "link_name"));

                /** @var
                 * max_rows maximum allowable rows under current filter
                 */
                self::$max_rows = self::$instance->getMemberRows();
            }
        }

        return self::$instance;
    }

    /**
     * @return int
     */
    private function getMemberRows() {
        $result = dbquery("SELECT u.user_id ".$this->getSelectors()."
        FROM ".DB_USERS." u ".$this->getJoins()."
        WHERE ".(iADMIN ? "u.user_status>='0'" : "u.user_status='0'")."
        ".$this->getConditions()." GROUP BY ".$this->getGroupBy()."
        ");

        return dbrows($result);
    }

    /**
     * @return string
     */
    private function getSelectors() {
        if (!empty(self::$filters["select"])) {
            return ", ".self::$filters["select"];
        }
        return "";
    }

    /**
     * @return mixed|string
     */
    private function getJoins() {
        if (!empty(self::$filters["join"])) {
            return self::$filters["join"];
        }
        return "";
    }

    /**
     * @return string
     */
    private function getConditions() {
        if (!empty(self::$filters["condition"])) {
            return " AND ".self::$filters["condition"];
        }
        return self::getFilters();
    }

    /**
     * @return string
     */
    private function getFilters() {
        // alpha select condition
        $default_condition = ($this->sortby == "all" ? "" : " AND user_name !=''");
        if ($this->search_text != "all") {
            return " AND user_name LIKE '".$this->search_text."%'";
        }
        return $default_condition;
    }

    /**
     * @return mixed|string
     */
    private function getGroupBy() {
        if (!empty(self::$filters["group_by"])) {
            return self::$filters["group_by"];
        }
        return "u.user_id";
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function display_members() {

        $settings = fusion_get_settings();
        if (iMEMBER) {

            $search_form = openform('searchform', 'get', $settings['site_seo'] ? PERMALINK_CURRENT_PATH : FUSION_REQUEST);
            $search_form .= "<div class='display-inline-block pull-left m-r-10'>\n";
            $search_form .= form_text('search_text', '', form_sanitizer($this->search_text, '', 'search_text'),
                [
                    'inline'             => TRUE,
                    'placeholder'        => self::$locale['MEMB_005'],
                    'append_button'      => TRUE,
                    'append_type'        => "submit",
                    'append_form_value'  => 'search',
                    'append_value'       => "<i class='fa fa-search'></i> ".self::$locale['MEMB_006'],
                    'append_button_name' => 'search',
                    'width'              => "200px",
                    'class'              => 'no-border m-b-0',
                    'group_size'         => 'sm'
                ]
            );
            $search_form .= "</div>\n";
            $search_form .= '<span class="m-r-10">'.self::$locale['MEMB_007'].'</span>';
            $search_form .= "<div class='display-inline-block' style='vertical-align:top;'>\n";
            $search_form .= form_select('orderby', '', $this->orderby,
                [
                    'options'     => [
                        'active'     => self::$locale['MEMB_008'],
                        'registered' => self::$locale['MEMB_009'],
                        'name'       => self::$locale['MEMB_010']
                    ],
                    'inline'      => TRUE,
                    'inner_width' => '150px',
                    'class'       => 'm-0 p-0'
                ]
            );
            $search_form .= "</div>\n";
            $search_form .= "<div class='display-inline-block' style='vertical-align:top;'>\n";
            $search_form .= form_select('sort_order', '', $this->sort_order, [
                'options'     => [
                    'ASC'  => self::$locale['MEMB_012'],
                    'DESC' => self::$locale['MEMB_013'],
                ],
                'inner_width' => "150px",
                'inline'      => TRUE,
                'class'       => 'm-0 p-0'
            ]);
            $search_form .= "</div>\n";
            $search_form .= closeform();

            $search_filter = array_merge(range("A", "Z"), range(0, 9));

            $search_table = "<div class='table-responsive'><table class='table table-striped center alphabet-table'>\n<tr>\n";
            $search_table .= "<td rowspan='2' class='tbl2 va'><a class='strong' href='".BASEDIR."members.php?sortby=all'>".self::$locale['MEMB_014']."</a></td>";
            for ($i = 0; $i < count($search_filter) != ""; $i++) {
                $search_table .= "<td class='tbl1 text-center'><div class='small'><a href='".BASEDIR."members.php?sortby=".$search_filter[$i]."'>".$search_filter[$i]."</a></div></td>";
                $search_table .= ($i == 17 ? "<td rowspan='2' class='tbl2 va'><a class='strong' href='".BASEDIR."members.php?sortby=all'>".self::$locale['MEMB_014']."</a></td>\n</tr>\n<tr>\n" : "\n");
            }
            $search_table .= "</tr>\n</table>\n</div>";

            $info = [
                'search_filter' => $search_filter,
                'rows'          => self::$max_rows,
                'search_form'   => $search_form,
                "search_table"  => $search_table,
                "no_result"     => self::$locale['MEMB_018'].(isset($this->search_text) ? form_sanitizer($this->search_text, '', 'search_text') : $this->sortby)
            ];

            if (self::$max_rows > 0) {
                $this->rowstart = get_rowstart("rowstart", self::$max_rows);
                $result = $this->getMembers();
                $current_rows = dbrows($result);

                if ($current_rows) {

                    $info['page_nav'] = makepagenav($this->rowstart, 24, self::$max_rows, 3, BASEDIR."members.php?sortby=".$this->sortby."&amp;");

                    while ($data = dbarray($result)) {

                        $info['member'][$data['user_id']] = $data;
                        $info['member'][$data['user_id']]['user_avatar'] = display_avatar($data, '25px', '', TRUE, 'img-rounded');
                        $info['member'][$data['user_id']]['default_group'] = ($data['user_level'] == USER_LEVEL_SUPER_ADMIN ? self::$locale['MEMB_016'] : self::$locale['MEMB_015']);

                        $user_groups = explode(".", $data['user_groups']);
                        if (!empty($user_groups)) {
                            foreach ($user_groups as $key => $value) {
                                if ($value) {
                                    $info['member'][$data['user_id']]['groups'][$key] = [
                                        'title' => getgroupname($value, FALSE, TRUE),
                                        'link'  => BASEDIR."profile.php?group_id=".$value
                                    ];
                                }
                            }
                        }
                    }

                    $end_rows = $this->rowstart > 0 ? $current_rows + $this->rowstart : $current_rows;

                    $info['page_result'] = strtr(self::$locale['MEMB_017'],
                        [
                            "{%start_row%}" => ($this->rowstart == 0 ? 1 : $this->rowstart),
                            "{%end_row%}"   => $end_rows,
                            "{%max_row%}"   => $info['rows'],
                            "{%member%}"    => format_word($info['rows'], self::$locale['fmt_member'],
                                [
                                    'add_count' => FALSE,
                                ]
                            )
                        ]
                    );

                }
            }

            $info += $this->default_info;

            render_members($info);

            add_to_jquery("
            $('#orderby').bind('change', function(e) {
                $(this).closest('form').submit();
            });
            $('#sort_order').bind('change', function(e) {
                $(this).closest('form').submit();
            });
            ");

            return $info;

        } else {
            redirect(BASEDIR."index.php");
        }
        return NULL;
    }

    /**
     * @return mixed
     */
    protected function getMembers() {
        return dbquery("
            SELECT u.user_id, u.user_name, u.user_status, u.user_level, u.user_groups, u.user_joined, u.user_avatar, u.user_lastvisit, us.user_language ".$this->getSelectors()."
            FROM ".DB_USERS." AS u ".$this->getJoins()."
            LEFT JOIN ".DB_USER_SETTINGS." AS us ON us.user_id = u.user_id
            WHERE ".(iADMIN ? "u.user_status>='0'" : "u.user_status='0'")."
            ".$this->getConditions()." GROUP BY ".$this->getGroupBy()." ORDER BY ".$this->getOrderBy()." LIMIT ".$this->rowstart.",".$this->getLimit()."
        ");
    }

    /**
     * @return mixed|string
     */
    private function getOrderBy() {

        if (!empty(self::$filters["order"])) {
            return self::$filters["order"];
        }

        $default_sorting = "u.user_level DESC, us.user_language DESC, u.user_name $this->sort_order";

        if (isset($this->orderby)) {
            switch ($this->orderby) {
                case 'active':
                    return "u.user_lastvisit $this->sort_order, $default_sorting";
                    break;
                case 'registered':
                    return "u.user_joined $this->sort_order, $default_sorting";
                    break;
                case 'name':
                    return "u.user_name $this->sort_order, $default_sorting";
                    break;
            }
        }

        return $default_sorting;
    }

    /**
     * @return int|mixed
     */
    private function getLimit() {
        if (!empty(self::$filters["limit"])) {
            return self::$filters["limit"];
        }
        return 24;
    }

    /**
     * Set custom filters
     *
     * @param array $filters
     * Indexes:
     * 'select' - query selection,
     * 'condition', - query condition
     * 'order', - order
     * 'limit', - limitations
     * 'join' - join statements
     */
    public function setFilters(array $filters = []) {
        self::$filters = $filters;
    }

    private function __clone() {
    }
}
