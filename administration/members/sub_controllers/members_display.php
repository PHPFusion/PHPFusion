<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members/sub_controllers/members_display.php
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
namespace Administration\Members\Sub_Controllers;

use Administration\Members\Members_Admin;
use Administration\Members\Members_View;
use PHPFusion\QuantumFields;
/**
 * Class Members_Display
 *
 * @package Administration\Members\Sub_Controllers
 */
class Members_Display extends Members_Admin {
    public static function render_listing() {
        add_to_footer("<script type='text/javascript' src='".ADMIN."members/js/user_display.js'></script>");

        $c_name = 'usertbl_results';
        $default_selected = array('user_timezone', 'user_joined', 'user_lastvisit', 'user_groups');
        $default_status_selected = array('0');
        $s_name = 'usertbl_status';

        if (isset($_POST['apply_filter'])) {
            // Display Cookie
            if (isset($_POST['display']) && is_array($_POST['display'])) {
                $selected_display_keys = \defender::sanitize_array(array_keys($_POST['display']));
                $cookie_selected = implode(',', $selected_display_keys);
                setcookie($c_name, $cookie_selected, time() + (86400 * 30), "/");
            } else {
                // Prevent cookie tampering and reverted to default result
                $cookie_selected = implode(',', $default_selected);
                setcookie($c_name, $cookie_selected, time() + (86400 * 30), "/");
            }
            if (isset($_POST['user_status']) && is_array($_POST['user_status'])) {
                $selected_display_keys = \defender::sanitize_array(array_keys($_POST['user_status']));
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
        $selected_fields = array();
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
            'user_timezone'   => self::$locale['ME_426']
        ];

        $field_checkboxes = [
            'user_hide_email' => form_checkbox('display[user_hide_email]', $tLocale['user_hide_email'], (isset($selected_fields['user_hide_email']) ? 1 : 0), array('reverse_label' => TRUE)),
            'user_joined'     => form_checkbox('display[user_joined]', $tLocale['user_joined'], (isset($selected_fields['user_joined']) ? 1 : 0), array('reverse_label' => TRUE)),
            'user_lastvisit'  => form_checkbox('display[user_lastvisit]', $tLocale['user_lastvisit'], (isset($selected_fields['user_lastvisit']) ? 1 : 0), array('reverse_label' => TRUE)),
            'user_ip'         => form_checkbox('display[user_ip]', $tLocale['user_ip'], (isset($selected_fields['user_ip']) ? 1 : 0), array('reverse_label' => TRUE)),
            'user_ip_type'    => form_checkbox('display[user_ip_type]', $tLocale['user_ip_type'], (isset($selected_fields['user_ip_type']) ? 1 : 0), array('reverse_label' => TRUE)),
            'user_groups'     => form_checkbox('display[user_groups]', $tLocale['user_groups'], (isset($selected_fields['user_groups']) ? 1 : 0), array('reverse_label' => TRUE)),
        ];
        $extra_checkboxes = array();
        $result = dbquery("SELECT field_id, field_name, field_title FROM ".DB_USER_FIELDS." ORDER BY field_cat, field_order ASC");
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $name = $data['field_name'];
            $title = $data['field_title'];
            if (QuantumFields::is_serialized($title)) {
                $title = QuantumFields::parse_label($title);
            }
            $tLocale[$data['field_name']] = $title;
            $extra_checkboxes[$name] = form_checkbox("display[".$data['field_name']."]", $title, (isset($selected_fields[$data['field_name']]) ? 1 : 0), array('input_id' => 'custom_'.$data['field_id'], 'reverse_label' => TRUE));
        }

        $field_status = array();
        for ($i = 0; $i < 9; $i++) {
            if ($i < 8 || self::$settings['enable_deactivation'] == 1) {
                $field_status[$i] = form_checkbox('user_status['.$i.']', getsuspension($i), (isset($selected_status[$i]) ? 1 : 0), array('input_id' => 'user_status_'.$i, 'reverse_label' => TRUE));
            }
        }

        $search_bind = array();
        $search_cond = '';
        $field_to_search = array_merge(array_values(array('user_name', 'user_id', 'user_email')), array_keys($extra_checkboxes));
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
            $status_bind = array();
            foreach ($selected_status as $susp_i) {
                $statuses[$susp_i] = $susp_i;//'<strong>'.getsuspension($susp_i).'</strong>';
            }
        } else {
            $status_cond = ' WHERE user_status=:status';
            $status_bind = array(
                ':status' => 0,
            );
            $statuses = array(0 => 0);
        }

        $query_bind = array_merge($status_bind, $search_bind);
        $rowCount = dbcount('(user_id)', DB_USERS, ltrim($status_cond, 'WHERE ').$search_cond, $query_bind);
        $rowstart = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $rowCount ? intval($_GET['rowstart']) : 0;
        $limit = 16;
        $query = "SELECT user_id, user_name, user_avatar, user_email, user_level, user_status ".($cookie_selected ? ', '.$cookie_selected : '')."
                  FROM ".DB_USERS.$status_cond.$search_cond." LIMIT $rowstart, $limit
                  ";
        $result = dbquery($query, $query_bind);
        $rows = dbrows($result);
        $page_nav = $rowCount > $rows ? makepagenav($rowstart, $limit, $rowCount, 5, FUSION_SELF.fusion_get_aidlink().'&amp;') : '';
        $interface = new static();

        $list_sum = sprintf(self::$locale['ME_407'], implode(', ', array_map(array($interface, 'list_uri'), $statuses)), $rows, $rowCount);

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
                                    $groups .= "<li><a href='".BASEDIR."profile.php?group_id='".$group_id."'>".getgroupname($group_id)."</a></li>\n";
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

                $list[$data['user_id']]['user_actions'] = ($data['user_level'] > USER_LEVEL_SUPER_ADMIN ? "<a href='".self::$status_uri['edit'].$data['user_id']."'>".self::$locale['edit']."</a> - <a href='".self::$status_uri['delete'].$data['user_id']."''>".self::$locale['delete']."</a> -" : "")." <a href='".self::$status_uri['view'].$data['user_id']."'>".self::$locale['view']."</a>";

                $list[$data['user_id']]['user_level'] = getuserlevel($data['user_level']);
                $list[$data['user_id']]['user_email'] = $data['user_email'];
            }
        }

        // Render table header and table result
        $table_head = "<tr><th></th><th colspan='4' class='text-center'>".self::$locale['ME_408']."</th><th colspan='".count($selected_fields)."' class='text-center'>".self::$locale['ME_409']."</th></tr>";

        $table_subheader = "<th></th><th colspan='2' class='col-xs-2'>".self::$locale['ME_410']."</th><th class='min'>".self::$locale['ME_411']."</th>\n<th class='min'>".self::$locale['ME_412']."</th>";

        foreach ($selected_fields as $column) {
            $table_subheader .= "<th>".$tLocale[$column]."</th>\n";
        }
        $table_subheader = "<tr>$table_subheader</tr>\n";
        $table_footer = "<tr><th class='p-10 min' colspan='5'>".form_checkbox('check_all', self::$locale['ME_414'], '', array('class' => 'm-b-0', 'reverse_label'=>TRUE))."</th><th colspan='".(count($selected_fields))."' class='text-right'>$page_nav</th></tr>\n";
        $list_result = "<tr>\n<td colspan='".(count($selected_fields) + 5)."' class='text-center'>".self::$locale['ME_405']."</td>\n</tr>\n";

        if (!empty($list)) {
            $list_result = '';
            foreach ($list as $user_id => $prop) {
                $list_result .= call_user_func_array(array($interface, 'list_func'), array($user_id, $list, $selected_fields));
            }
        }
        /*
         * User Actions Button
         */
        $user_actions = form_button('action', self::$locale['ME_501'], self::USER_REINSTATE, array('class' => 'btn-success m-r-10')).
            form_button('action', self::$locale['ME_500'], self::USER_BAN, array('class' => ' btn-default m-r-10')).
            form_button('action', self::$locale['ME_502'], self::USER_DEACTIVATE, array('class' => ' btn-default m-r-10')).
            form_button('action', self::$locale['ME_503'], self::USER_SUSPEND, array('class' => ' btn-default m-r-10')).
            form_button('action', self::$locale['ME_504'], self::USER_SECURITY_BAN, array('class' => ' btn-default m-r-10')).
            form_button('action', self::$locale['ME_505'], self::USER_CANCEL, array('class' => ' btn-default m-r-10')).
            form_button('action', self::$locale['ME_506'], self::USER_ANON, array('class' => ' btn-default m-r-10'));

        $html = openform('member_frm', 'post', FUSION_SELF.fusion_get_aidlink(), array('class' => 'form-inline'));
        $html .= form_hidden('aid', '', iAUTH);
        $html .= strtr(Members_View::display_members(), array(
                '{%filter_text%}'         => form_text('search_text', '', '', array('placeholder'        => self::$locale['ME_401'],
                                                                                    'append'             => TRUE,
                                                                                    'append_button'      => TRUE,
                                                                                    'append_value'       => self::$locale['search'],
                                                                                    'append_form_value'  => 'search_member',
                                                                                    'append_button_name' => 'search_member',
                                                                                    'class'              => 'm-b-0'
                )),
                '{%filter_button%}'       => form_button('filter_btn', self::$locale['ME_402'], 'filter_btn', array('icon' => 'caret')),
                '{%action_button%}'       => "<a class='btn btn-success' href='".FUSION_SELF.fusion_get_aidlink()."&amp;ref=add'>".self::$locale['ME_403']."</a>\n",
                '{%filter_status%}'       => "<span class='m-r-15'>".implode("</span><span class='m-r-15'>", array_values($field_status))."</span>",
                '{%filter_options%}'      => "<span class='m-r-15'>".implode("</span><span class='m-r-15'>", array_values($field_checkboxes))."</span>",
                '{%filter_extras%}'       => "<span class='m-r-15'>".implode("</span><span class='m-r-15'>", array_values($extra_checkboxes))."</span>",
                '{%filter_apply_button%}' => form_button('apply_filter', self::$locale['ME_404'], 'apply_filter', array('class' => 'btn-primary')),
                '{%page_count%}'          => $list_sum,
                '{%list_head%}'           => $table_head,
                '{%list_column%}'         => $table_subheader,
                '{%list_result%}'         => $list_result,
                '{%list_footer%}'         => $table_footer,
                '{%page_nav%}'            => $page_nav,
                '{%user_actions%}'        => $user_actions,
            )
        );
        $html .= closeform();
        return $html;
    }

    protected function list_uri($value) {
        return "<a href='".self::$status_uri[$value]."'><strong>".getsuspension($value)."</strong></a>\n";
    }

    /*
     * Render Listing Functions
     */
    protected static function list_func($user_id, $list, $selected_fields) {
        $html = "<tr id='user-".$user_id."'>\n
                <td class='p-10'>\n".$list[$user_id]['checkbox']."</td>\n
                <td class='col-xs-2'>".$list[$user_id]['user_name']."</td>\n
                <td class='no-break'>".$list[$user_id]['user_actions']."</td>
                <td class='no-break'>\n".$list[$user_id]['user_level']."</td>\n
                <td>\n".$list[$user_id]['user_email']."</td>\n";

        add_to_jquery('$("#user_id_'.$user_id.'").click(function() {
            if ($(this).prop("checked")) {
                $("#user-'.$user_id.'").addClass("active");
            } else {
                $("#user-'.$user_id.'").removeClass("active");
            }
        });');

        foreach ($selected_fields as $column) {
            $html .= "<td>".(!empty($list[$user_id][$column]) ? $list[$user_id][$column] : "-")."</td>\n";
        }

        $html .= "</tr>\n";

        return $html;
    }
}
