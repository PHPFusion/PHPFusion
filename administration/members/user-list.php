<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user-list.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Administration\Members;

/**
 * Class User_List
 *
 * @package Administration\Members\Users
 */
class UserList implements \PHPFusion\Interfaces\TableSDK {

    const USER_MEMBER = 0;
    const USER_BAN = 1;
    const USER_REINSTATE = 2;
    const USER_SUSPEND = 3;
    const USER_SECURITY_BAN = 4;
    const USER_CANCEL = 5;
    const USER_ANON = 6;
    const USER_DEACTIVATE = 7;
    const USER_UNACTIVATED = 2;

    /**
     *  Returns the table data source structure configurations
     *
     *
     * 'debug'                    => FALSE, // True to show the SQL query for the table.
     * 'table'                    => '',
     * 'id'                       => '', // if hierarchy
     * 'parent'                   => '', // if hierarchy
     * 'limit'                    => 24,
     * 'true_limit'               => FALSE, // if true, the limit is true limit (only limited results will display without page nav)
     * 'joins'                    => '',
     * 'select'                   => '',
     * 'conditions'               => '', // to match list to a condition. string value only
     * 'group'                    => '', // group by column
     * 'image_folder'             => '', // for deletion (i.e. IMAGES.'folder/') , use param for string match
     * 'image_field'              => '', // to delete (i.e. news_image)
     * 'file_field'               => '',  // to delete (i.e. news_attach)
     * 'file_folder'              => '', // to delete files from the folder, use param for string match
     * 'db'                       => [], // to delete other entries on delete -- use this key. Keys: 'select' => 'ratings_id', 'group' => 'ratings_item_id', 'custom' => "rating_type='CLS'"
     * 'delete_function_callback' => '', // can be array or string - if array, first parameter - class, second parameter method, third optional parameter file path
     *
     * @return array
     */
    public function data() {
        return [
            'debug'        => FALSE,
            'table'        => DB_USERS,
            'id'           => 'user_id',
            'limit'        => 24,
            'image_folder' => IMAGES.'avatars'.DIRECTORY_SEPARATOR,
            'image_field'  => 'user_avatar',
            // this method will work as well, but the bulkDelete function is much better option without using reflection class
            //'delete_function_callback' => ['Administration\\Members\\Users\\User_List', 'deleteUser', ADMIN.'members/users/user_list.table.php']
        ];
    }


    public function bulkDelete($data) {
        fusion_filter_current_hook('admin_user_delete', $data);
    }

    /**
     * For bulk action array
     */
    public function bulkActions() {
        $table_action = post('table_action');
        if ($table_action) {
            // merge with the member actions here.
            if ($user_ids = \Defender::getInstance()->filterPostArray(['id'])) {
                $user_action = new UserActions();
                $user_action->set_userID($user_ids);
                $user_action->set_action($table_action);
                $user_action->execute();
            }
        }
        /*
         *
        if (isset($_REQUEST['action']) && isset($_REQUEST['user_id']) || isset($_REQUEST['lookup'])) {

            if (isset($_REQUEST['lookup']) && !is_array($_REQUEST['lookup'])) {
                $_REQUEST['lookup'] = [$_REQUEST['lookup']];
            }
            $user_action->set_userID((array)(isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $_REQUEST['lookup']));
            $user_action->set_action((string)$_REQUEST['action']);
            $user_action->execute();
        }
         */
    }


    public function properties() {

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();

        $groups_array = [
            USER_LEVEL_MEMBER      => $locale['user1'],
            USER_LEVEL_ADMIN       => $locale['user2'],
            USER_LEVEL_SUPER_ADMIN => $locale['user3']
        ];

        return [
            'table_id'         => 'user-table',
            'search_col'       => 'user_name',
            'search_label'     => 'Search User',
            'date_col'         => 'user_lastvisit',
            'order_col'        => [
                'user_id'         => 'id',
                'user_email'      => 'email',
                'user_level'      => 'level',
                'user_status'     => 'status',
                'user_joined'     => 'joined',
                'user_lastvisit'  => 'lastvisit',
                'user_hide_email' => 'email',
                'user_ip'         => 'ip',
                'user_groups'     => 'groups'
            ],
            'updated_message'  => 'User have been updated',
            'deleted_message'  => 'User have been deleted',
            'edit_link_format' => ADMIN.'members.php'.$aidlink.'&amp;action=edit&amp;lookup=',
            'view_link_format' => BASEDIR.'profile.php?lookup=:user_id',
            'link_filters'     => [
                'user_level' => [
                    'title'   => 'Roles:',
                    'options' => $groups_array
                ]
            ],
            'dropdown_filters' => [
                'user_status' => [
                    'type'    => 'array',
                    'title'   => 'View member types:',
                    'options' => [
                        0 => getsuspension(0),
                        1 => getsuspension(1),
                        2 => getsuspension(2),
                        3 => getsuspension(3),
                        4 => getsuspension(4),
                        5 => getsuspension(5),
                        6 => getsuspension(6),
                        7 => getsuspension(7),
                        8 => getsuspension(8),
                    ],
                ]
            ],
            'action_filters'   => [
                self::USER_BAN          => $locale['ME_500'],
                self::USER_REINSTATE    => $locale['ME_501'],
                self::USER_SUSPEND      => $locale['ME_503'],
                self::USER_SECURITY_BAN => $locale['ME_504'],
                self::USER_CANCEL       => $locale['ME_505'],
                self::USER_ANON         => $locale['ME_506'],
                self::USER_DEACTIVATE   => $locale['ME_507']
            ]
        ];
    }


    public function column() {
        $locale = fusion_get_locale();
        // Find all user fields
        // @todo: Extend it to all database as per UFv1.2 data model.
        $user_fields = [];
        $result = dbquery("SELECT child.field_cat_id FROM ".DB_USER_FIELD_CATS." root LEFT JOIN ".DB_USER_FIELD_CATS." child ON child.field_parent=root.field_cat_id
        WHERE root.field_parent=0 AND root.field_cat_db='users' GROUP BY child.field_cat_id");
        if (dbrows($result)) {
            $rows = [];
            while ($data = dbarray($result)) {
                $rows[] = $data['field_cat_id'];
            }
            $cresult = dbquery("SELECT  field_title, field_name, field_type FROM ".DB_USER_FIELDS." WHERE field_cat IN (".implode(',', $rows).")");
            if (dbrows($cresult)) {
                while ($cdata = dbarray($cresult)) {
                    $user_fields[$cdata['field_name']]['title'] = fusion_parse_locale($cdata['field_title']);
                    $user_fields[$cdata['field_name']]['visibility'] = FALSE;
                }
            }
        }

        return [
                'user_id'         => [
                    'title'       => 'User Name',
                    'title_class' => 'col-xs-3',
                    'user_avatar' => TRUE,
                    'user'        => TRUE,
                    'view_link'   => TRUE,
                    'edit_link'   => TRUE,
                    'delete_link' => TRUE,
                ],
                'user_email'      => [
                    'title' => 'Email',
                ],
                'user_level'      => [
                    'title'   => 'Role',
                    'class'   => 'width-15',
                    'options' => fusion_get_groups(),
                ],
                'user_status'     => [
                    'title'      => $locale['ME_427'],
                    'callback'   => ['Members_Administration', 'checkUserStatus'],
                    'visibility' => TRUE,
                ],
                'user_joined'     => [
                    'title'      => $locale['ME_421'],
                    'date'       => TRUE,
                    'visibility' => FALSE,
                ],
                'user_lastvisit'  => [
                    'title'      => $locale['ME_422'],
                    'date'       => TRUE,
                    'visibility' => FALSE,
                ],
                'user_hide_email' => [
                    'title'   => $locale['ME_420'],
                    'options' => [
                        0 => $locale['no'],
                        1 => $locale['yes'],
                        2 => 'N/A'
                    ]
                ],
                'user_ip'         => [
                    'title'      => $locale['ME_423'],
                    'visibility' => FALSE,
                ],
                'user_ip_type'    => [
                    'title'      => $locale['ME_424'],
                    'visibility' => FALSE,
                ],
            ] + $user_fields;

        /*
         * $tLocale = [
            'user_groups'     => self::$locale['ME_425'],
            'user_timezone'   => self::$locale['ME_426'],
            ''     => self::$locale['']
        ];
         */
    }

    /**
     * Every row of the array is a field input.
     *
     * @return array
     */
    public function quickEdit() {
        return [];
    }

    // Will port into List
    private function deprecated() {
        //add_to_footer("<script type='text/javascript' src='".ADMIN."members/js/user_display.js'></script>");

        $c_name = 'usertbl_results';
        $default_selected = ['user_timezone', 'user_joined', 'user_lastvisit', 'user_groups'];
        $default_status_selected = ['0'];
        $s_name = 'usertbl_status';
        $selected_status = [];
        $statuses = [];

        if (isset($_POST['apply_filter'])) {
            // Display Cookie
            if (isset($_POST['display']) && is_array($_POST['display'])) {
                $selected_display_keys = \Defender::sanitize_array(array_keys($_POST['display']));
                $cookie_selected = implode(',', $selected_display_keys);
                setcookie($c_name, $cookie_selected, time() + (86400 * 30), "/");
            } else {
                // Prevent cookie tampering and reverted to default result
                $cookie_selected = implode(',', $default_selected);
                setcookie($c_name, $cookie_selected, time() + (86400 * 30), "/");
            }
            if (isset($_POST['user_status']) && is_array($_POST['user_status'])) {
                $selected_display_keys = \Defender::sanitize_array(array_keys($_POST['user_status']));
                $status_cookie_selected = implode(',', $selected_display_keys);
                setcookie($s_name, $status_cookie_selected, time() + (86400 * 30), "/");
            } else {
                // Prevent cookie tampering and reverted to default result
                $status_cookie_selected = implode(',', $default_status_selected);
                setcookie($s_name, $status_cookie_selected, time() + (86400 * 30), "/");
            }
        } else {
            if (!isset($_COOKIE[$c_name])) {
                $cookie_selected = implode(',', $default_selected);
                setcookie($c_name, $cookie_selected, time() + (86400 * 30), "/");
            } else {
                $cookie_selected = stripinput($_COOKIE[$c_name]);
            }
            if (isset($_GET['status']) && isnum($_GET['status']) && $_GET['status'] <= 7) {
                $status_cookie_selected = $_GET['status'];
                setcookie($s_name, $status_cookie_selected, time() + (86400 * 30), "/");
            } else {
                if (!isset($_COOKIE[$s_name])) {
                    $status_cookie_selected = implode(',', $default_status_selected);
                    setcookie($s_name, $status_cookie_selected, time() + (86400 * 30), "/");
                } else {
                    $status_cookie_selected = stripinput($_COOKIE[$s_name]);
                }
            }
        }

        /*
         * Sanitize Cookie Input - Select
         */
        $usertable_column = array_flip(fieldgenerator(DB_USERS));
        unset($usertable_column['user_password']);
        unset($usertable_column['user_admin_password']);
        unset($usertable_column['user_salt']);
        unset($usertable_column['user_algo']);
        unset($usertable_column['user_admin_algo']);
        unset($usertable_column['user_admin_salt']);
        unset($usertable_column['user_status']);

        $user_fields = array_map('trim', explode(',', $cookie_selected));
        // Sanitize fields
        $selected_fields = [];
        if (!empty($user_fields)) {
            foreach ($user_fields as $field_name) {
                if (isset($usertable_column[$field_name])) {
                    // there we have a verified one.
                    $selected_fields[$field_name] = $field_name;
                }
            }
        }
        /*
         * Sanitize Cookie Input - Condition
         */
        $user_status = array_map('trim', explode(',', $status_cookie_selected));
        if (!empty($user_status)) {
            foreach ($user_status as $status) {
                if (isnum($status)) {
                    $selected_status[$status] = $status;
                }
            }
        }

        $tLocale = [
            'user_hide_email' => self::$locale['ME_420'],
            'user_joined'     => self::$locale['ME_421'],
            'user_lastvisit'  => self::$locale['ME_422'],
            'user_ip'         => self::$locale['ME_423'],
            'user_ip_type'    => self::$locale['ME_424'],
            'user_groups'     => self::$locale['ME_425'],
            'user_timezone'   => self::$locale['ME_426'],
            'user_status'     => self::$locale['ME_427']
        ];

        $field_checkboxes = [
            'user_hide_email' => form_checkbox('display[user_hide_email]', $tLocale['user_hide_email'], (isset($selected_fields['user_hide_email']) ? 1 : 0), ['reverse_label' => TRUE]),
            'user_joined'     => form_checkbox('display[user_joined]', $tLocale['user_joined'], (isset($selected_fields['user_joined']) ? 1 : 0), ['reverse_label' => TRUE]),
            'user_lastvisit'  => form_checkbox('display[user_lastvisit]', $tLocale['user_lastvisit'], (isset($selected_fields['user_lastvisit']) ? 1 : 0), ['reverse_label' => TRUE]),
            'user_ip'         => form_checkbox('display[user_ip]', $tLocale['user_ip'], (isset($selected_fields['user_ip']) ? 1 : 0), ['reverse_label' => TRUE]),
            'user_ip_type'    => form_checkbox('display[user_ip_type]', $tLocale['user_ip_type'], (isset($selected_fields['user_ip_type']) ? 1 : 0), ['reverse_label' => TRUE]),
            'user_groups'     => form_checkbox('display[user_groups]', $tLocale['user_groups'], (isset($selected_fields['user_groups']) ? 1 : 0), ['reverse_label' => TRUE]),
        ];
        $extra_checkboxes = [];
        $result = dbquery("SELECT field_id, field_name, field_title FROM ".DB_USER_FIELDS." ORDER BY field_cat, field_order ASC");
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $name = $data['field_name'];
            $title = (UserFieldsQuantum::is_serialized($data['field_title']) ? UserFieldsQuantum::parse_label($data['field_title']) : $data['field_title']);
            $tLocale[$name] = $title;
            $extra_checkboxes[$name] = form_checkbox("display[".$name."]", $title, (isset($selected_fields[$name]) ? 1 : 0), ['input_id' => 'custom_'.$data['field_id'], 'reverse_label' => TRUE]);
        }

        $field_status = [];
        for ($i = 0; $i < 9; $i++) {
            if ($i < 8 || self::$settings['enable_deactivation'] == 1) {
                $field_status[$i] = form_checkbox('user_status['.$i.']', getsuspension($i), (isset($selected_status[$i]) ? 1 : 0), ['input_id' => 'user_status_'.$i, 'reverse_label' => TRUE]);
            }
        }

        $search_bind = [];
        $search_cond = '';
        $field_to_search = array_merge(array_values(['user_name', 'user_id', 'user_email']), array_keys($extra_checkboxes));
        if (!empty($_POST['search_text'])) {
            $search_text = form_sanitizer($_POST['search_text'], '', 'search_text');
            if (!empty($search_text)) {
                $search_cond = 'AND (';
                $i = 0;
                foreach (array_values($field_to_search) as $key) {
                    $search_cond .= "$key LIKE :text_$i".($i == count($field_to_search) - 1 ? '' : ' OR ');
                    $search_bind[':text_'.$i] = '%'.$search_text.'%';
                    $i++;
                }
                $search_cond .= ')';
            }
        }

        if (!empty($selected_status)) {
            $status_cond = " WHERE user_status IN (".implode(',', $selected_status).") ";
            $status_bind = [];
            foreach ($selected_status as $susp_i) {
                $statuses[$susp_i] = $susp_i;//'<strong>'.getsuspension($susp_i).'</strong>';
            }
        } else {
            $status_cond = ' WHERE user_status=:status';
            $status_bind = [
                ':status' => 0,
            ];
            $statuses = [0 => 0];
        }

        $query_bind = array_merge($status_bind, $search_bind);
        $rowCount = dbcount('(user_id)', DB_USERS, ltrim($status_cond, 'WHERE ').$search_cond, $query_bind);
        $rowstart = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $rowCount ? intval($_GET['rowstart']) : 0;
        $limit = 16;
        if (in_array(2, $selected_status)) {
            $nquery = "SELECT * FROM ".DB_NEW_USERS;
            $nresult = dbquery($nquery);
            $i = 999999;
            while ($data = dbarray($nresult)) {
                $list[$data['user_name']] = [
                    'user_id'      => $i,
                    'checkbox'     => '',
                    'user_name'    => $data['user_name']."<br />".getsuspension(2),
                    'user_level'   => self::$locale['ME_562'],
                    'user_actions' => "<a href='".self::$status_uri['delete'].$data['user_name']."&amp;newuser=1'>".self::$locale['delete']."</a>",
                    'user_email'   => $data['user_email'],
                    'user_joined'  => showdate('longdate', $data['user_datestamp'])
                ];
                $i++;
            }
        }

        $query = "SELECT user_id, user_name, user_avatar, user_email, user_level, user_status ".($cookie_selected ? ', '.$cookie_selected : '')."
                  FROM ".DB_USERS.$status_cond.$search_cond." LIMIT $rowstart, $limit
                  ";
        $result = dbquery($query, $query_bind);
        $rows = dbrows($result);
        $page_nav = $rowCount > $limit ? makepagenav($rowstart, $limit, $rowCount, 5, FUSION_SELF.fusion_get_aidlink().'&amp;') : '';
        $interface = new static();

        $list_sum = sprintf(self::$locale['ME_407'], implode(', ', array_map([$interface, 'list_uri'], $statuses)), $rows, $rowCount);

        if ($rows != '0') {
            while ($data = dbarray($result)) {
                // the key which to be excluded should be unset
                $key = array_keys($data);
                foreach ($key as $data_key) {
                    switch ($data_key) {
                        case 'user_joined' :
                            $data[$data_key] = showdate('shortdate', $data[$data_key]);
                            break;
                        case 'user_lastvisit':
                            $data[$data_key] = showdate('shortdate', $data[$data_key]);
                            break;
                        case 'user_groups':
                            if (!empty($data[$data_key])) {
                                $group = array_filter(explode('.', $data[$data_key]));
                                $groups = "<ul class='block'>";
                                foreach ($group as $group_id) {
                                    $groups .= '<li><a href="'.BASEDIR.'profile.php?group_id='.$group_id.'">'.getgroupname($group_id).'</a></li>';
                                }
                                $groups .= "</ul>\n";
                                $data[$data_key] = $groups;
                            }
                            break;
                        case 'user_hide_email':
                            $data[$data_key] = $data[$data_key] ? self::$locale['ME_415'] : self::$locale['ME_416'];
                            break;
                    }
                    // custom ones
                    $list[$data['user_id']][$data_key] = $data[$data_key];
                }

                $list[$data['user_id']]['checkbox'] = ($data['user_level'] > USER_LEVEL_SUPER_ADMIN) ? form_checkbox('user_id[]', '', '', ['input_id' => 'user_id_'.$data['user_id'], 'value' => $data['user_id']]) : '';

                $list[$data['user_id']]['user_name'] = "<div class='clearfix'>\n<div class='pull-left m-r-10'>".display_avatar($data, '35px', '', FALSE, '')."</div>\n
                <div class='overflow-hide'><a href='".self::$status_uri['view'].$data['user_id']."'>".$data['user_name']."</a><br/>".getsuspension($data['user_status'])."</div>
                </div>\n";

                $list[$data['user_id']]['user_actions'] = ($data['user_level'] > USER_LEVEL_SUPER_ADMIN ? "<a href='".self::$status_uri['edit'].$data['user_id']."'>".self::$locale['edit']."</a> - <a href='".self::$status_uri['delete'].$data['user_id']."'>".self::$locale['delete']."</a> -" : "")." <a href='".self::$status_uri['view'].$data['user_id']."'>".self::$locale['view']."</a>";

                $list[$data['user_id']]['user_level'] = getuserlevel($data['user_level']);

                $list[$data['user_id']]['user_email'] = $data['user_email'];

                $list[$data['user_id']]['user_status'] = getuserstatus($data['user_status']);
            }
        }

        // Render table header and table result
        $detail_span = count($selected_fields) + 1;
        $table_head = "<tr><th class='min'></th><th colspan='4' class='text-center'>".self::$locale['ME_408']."</th><th colspan='$detail_span' class='text-center'>".self::$locale['ME_409']."</th></tr>";

        $table_subheader = "<th></th><th colspan='2' class='col-xs-2'>".self::$locale['ME_410']."</th><th class='min'>".self::$locale['ME_411']."</th>\n<th>".self::$locale['ME_427']."</th>\n<th class='min'>".self::$locale['ME_412']."</th>";

        foreach ($selected_fields as $column) {
            $table_subheader .= "<th>".$tLocale[$column]."</th>\n";
        }
        $table_subheader = "<tr>$table_subheader</tr>\n";
        $table_footer = "<tr><th class='p-10 min' colspan='5'>".form_checkbox('check_all', self::$locale['ME_414'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE])."</th><th colspan='$detail_span' class='text-right'>$page_nav</th></tr>\n";
        $list_result = "<tr>\n<td colspan='".(count($selected_fields) + 5)."' class='text-center'>".self::$locale['ME_405']."</td>\n</tr>\n";

        if (!empty($list)) {
            $list_result = '';
            foreach ($list as $user_id => $prop) {
                $list_result .= call_user_func_array([$interface, 'list_func'], [$user_id, $list, $selected_fields]);
            }
        }
        /*
         * User Actions Button
         */
        $user_actions = form_button('action', self::$locale['ME_501'], self::USER_REINSTATE, ['class' => 'btn-success m-r-10']).
            form_button('action', self::$locale['ME_500'], self::USER_BAN, ['class' => ' btn-default m-r-10']).
            form_button('action', self::$locale['ME_502'], self::USER_DEACTIVATE, ['class' => ' btn-default m-r-10']).
            form_button('action', self::$locale['ME_503'], self::USER_SUSPEND, ['class' => ' btn-default m-r-10']).
            form_button('action', self::$locale['ME_504'], self::USER_SECURITY_BAN, ['class' => ' btn-default m-r-10']).
            form_button('action', self::$locale['ME_505'], self::USER_CANCEL, ['class' => ' btn-default m-r-10']).
            form_button('action', self::$locale['ME_506'], self::USER_ANON, ['class' => ' btn-default m-r-10']);

        $html = openform('member_frm', 'post', FUSION_SELF.fusion_get_aidlink(), ['class' => 'form-inline']);
        $html .= form_hidden('aid', '', iAUTH);

        $tpl = Template::getInstance('member_listing');
        $tpl->set_locale(self::$locale);
        $tpl->set_tag('filter_text', form_text('search_text', '', '', [
            'placeholder'        => self::$locale['ME_401'],
            'append'             => TRUE,
            'append_button'      => TRUE,
            'append_value'       => self::$locale['search'],
            'append_form_value'  => 'search_member',
            'append_button_name' => 'search_member',
            'class'              => 'm-b-0'
        ]));
        $tpl->set_tag('filter_button', form_button('filter_btn', self::$locale['ME_402'], 'filter_btn', ['icon' => 'caret']));
        $tpl->set_tag('action_button', "<a class='btn btn-success' href='".FUSION_SELF.fusion_get_aidlink()."&amp;ref=add'>".self::$locale['ME_403']."</a>");
        $tpl->set_tag('filter_status', "<span class='m-r-15'>".implode("</span><span class='m-r-15'>", array_values($field_status))."</span>");
        $tpl->set_tag('filter_options', "<span class='m-r-15'>".implode("</span><span class='m-r-15'>", array_values($field_checkboxes))."</span>");
        $tpl->set_tag('filter_extras', "<span class='m-r-15'>".implode("</span><span class='m-r-15'>", array_values($extra_checkboxes))."</span>");
        $tpl->set_tag('filter_apply_button', form_button('apply_filter', self::$locale['ME_404'], 'apply_filter', ['class' => 'btn-primary']));
        $tpl->set_tag('page_count', $list_sum);
        $tpl->set_tag('list_head', $table_head);
        $tpl->set_tag('list_column', $table_subheader);
        $tpl->set_tag('list_result', $list_result);
        $tpl->set_tag('list_footer', $table_footer);
        $tpl->set_tag('page_nav', $page_nav);
        $tpl->set_tag('user_actions', $user_actions);
        $tpl->set_text(Members_View::display_members());

        $html .= $tpl->get_output();
        $html .= closeform();

        return (string)$html;
    }

}

require_once(__DIR__.'/../../includes/sendmail_include.php');
require_once(__DIR__.'/../../includes/suspend_include.php');
