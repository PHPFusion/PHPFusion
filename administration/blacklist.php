<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blacklist.php
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
require_once __DIR__.'/../maincore.php';
pageAccess('B');
require_once THEMES.'templates/admin_header.php';

use \PHPFusion\BreadCrumbs;

class BlacklistAdministration {
    private static $instance = NULL;
    private static $locale = [];
    private static $limit = 20;

    private $data = [
        'blacklist_id'        => 0,
        'blacklist_user_id'   => '',
        'blacklist_ip'        => '',
        'blacklist_ip_type'   => '4',
        'blacklist_email'     => '',
        'blacklist_reason'    => '',
        'blacklist_datestamp' => ''
    ];

    public function __construct() {
        self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/blacklist.php');

        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($_GET['action']) {
            case 'delete':
                self::delete_blacklist($_GET['blacklist_id']);
                break;
            case 'edit':
                $this->data = self::_selectBlacklist($_GET['blacklist_id']);
                break;
            default:
                break;
        }

        self::set_adminsdb();
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function set_adminsdb() {
        if (isset($_POST['blacklist_admins'])) {
            $blacklist_ip_type = 6;

            if (strpos($_POST['blacklist_ip'], ".")) {
                $blacklist_ip_type = strpos($_POST['blacklist_ip'], ":") === FALSE ? 4 : 5;
            }

            $this->data = [
                'blacklist_id'        => form_sanitizer($_POST['blacklist_id'], 0, 'blacklist_id'),
                'blacklist_user_id'   => empty($_POST['blacklist_id']) ? fusion_get_userdata('user_id') : '',
                'blacklist_ip'        => form_sanitizer($_POST['blacklist_ip'], '', 'blacklist_ip'),
                'blacklist_ip_type'   => $blacklist_ip_type,
                'blacklist_email'     => !empty($_POST['blacklist_email']) ? form_sanitizer($_POST['blacklist_email'], '', 'blacklist_email') : '',
                'blacklist_reason'    => form_sanitizer($_POST['blacklist_reason'], '', 'blacklist_reason'),
                'blacklist_datestamp' => empty($_POST['blacklist_datestamp']) ? time() : $_POST['blacklist_datestamp']
            ];

            if (\defender::safe()) {
                if (empty($this->data['blacklist_ip']) && empty($this->data['blacklist_email'])) {
                    \defender::stop();
                    addNotice('danger', self::$locale['BLS_010']);
                } else {
                    dbquery_insert(DB_BLACKLIST, $this->data, empty($this->data['blacklist_id']) ? 'save' : 'update');
                    addNotice('success', empty($this->data['blacklist_id']) ? self::$locale['BLS_011'] : self::$locale['BLS_012']);
                    redirect(clean_request('', ['section', 'action', 'blacklist_id'], FALSE));
                }
            }
        }
    }

    static function verify_blacklist($id) {
        if (isnum($id)) {
            return dbcount("(blacklist_id)", DB_BLACKLIST, "blacklist_id='".intval($id)."'");
        }

        return FALSE;
    }

    public function _countBlist($opt) {
        $DBc = dbcount("(blacklist_id)", DB_BLACKLIST, $opt);
        return $DBc;
    }

    private static function delete_blacklist($id) {
        if (self::verify_blacklist($id)) {
            dbquery("DELETE FROM ".DB_BLACKLIST." WHERE blacklist_id='".intval($id)."'");
            addNotice('warning', self::$locale['BLS_013']);
            redirect(clean_request('', ['section', 'action', 'blacklist_id'], FALSE));
        }
    }

    public function _selectBlacklist($ids) {
        if (self::verify_blacklist($ids)) {
            $result = dbquery("SELECT blacklist_id, blacklist_user_id, blacklist_ip, blacklist_ip_type, blacklist_email, blacklist_reason, blacklist_datestamp
                FROM ".DB_BLACKLIST."
                WHERE blacklist_id=".intval($ids)
            );

            if (dbrows($result) > 0) {
                return $data = dbarray($result);
            }
        }

        return NULL;
    }

    public function _selectDB($rows) {
        $result = dbquery("SELECT b.blacklist_id, b.blacklist_ip, b.blacklist_email, b.blacklist_reason, b.blacklist_datestamp, u.user_id, u.user_name, u.user_status
            FROM ".DB_BLACKLIST." b
            LEFT JOIN ".DB_USERS." u ON u.user_id=b.blacklist_user_id
            ORDER BY blacklist_datestamp DESC
            LIMIT ".intval($rows).", ".self::$limit
        );
        return $result;
    }

    public function display_admin() {
        $allowed_section = ['blacklist', 'blacklist_form'];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'blacklist';
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['blacklist_id']) ? TRUE : FALSE;
        $_GET['blacklist_id'] = isset($_GET['blacklist_id']) && isnum($_GET['blacklist_id']) ? $_GET['blacklist_id'] : 0;
        $title = !empty($edit) ? self::$locale['BLS_021'] : self::$locale['BLS_022'];
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'blacklist.php'.fusion_get_aidlink(), 'title' => self::$locale['BLS_000']]);

        $master_tab_title['title'][] = self::$locale['BLS_020'];
        $master_tab_title['id'][] = 'blacklist';
        $master_tab_title['icon'][] = '';

        if ($_GET['section'] == 'blacklist_form') {
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $title]);
            $master_tab_title['title'][] = $edit ? self::$locale['BLS_021'] : self::$locale['BLS_022'];
            $master_tab_title['id'][] = 'blacklist_form';
            $master_tab_title['icon'][] = '';
        }

        opentable(self::$locale['BLS_000']);
        echo opentab($master_tab_title, $_GET['section'], "blacklist", TRUE, 'nav-tabs m-b-15');
        switch ($_GET['section']) {
            case "blacklist_form":
                $this->blacklistForm();
                break;
            default:
                $this->blacklist_listing();
                break;
        }
        echo closetab();
        closetable();
    }

    public function blacklist_listing() {

        // Table Actions
        if (isset($_POST['table_action'])) {

            $input = (isset($_POST['blacklist_id'])) ? explode(",", form_sanitizer($_POST['blacklist_id'], "", "blacklist_id")) : "";

            if (!empty($input)) {
                foreach ($input as $blacklist_id) {
                    if (self::verify_blacklist($blacklist_id) && \defender::safe()) {
                        if ($_POST['table_action'] == 'delete') {
                            self::delete_blacklist($blacklist_id);
                            addNotice('warning', self::$locale['BLS_013']);
                        }
                    }
                }
                redirect(clean_request('', ['section', 'action', 'blacklist_id'], FALSE));
            }
            addNotice('warning', self::$locale['BLS_017']);
            redirect(clean_request('', ['section', 'action', 'blacklist_id'], FALSE));
        }

        $aidlink = fusion_get_aidlink();
        $total_rows = $this->_countBlist("");
        $rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
        $result = $this->_selectDB($rowstart);
        $rows = dbrows($result);
        openside('');
        echo openform('blacklist_table', 'post', FUSION_REQUEST);
        echo form_hidden('table_action', '', '');
        echo "<div class='m-t-15'>\n";
        echo "<div class='clearfix'>\n";
        echo "<div class='pull-right'>";
        echo "<a class='btn btn-success btn-sm m-r-10' href=".clean_request('section=blacklist_form', ['section', 'rowstart'], FALSE)."><i class='fa fa-fw fa-plus'></i>".self::$locale['BLS_022']."</a>";
        echo "<a class='btn btn-danger btn-sm m-r-10' onclick=\"run_admin('delete', '#table_action','#blacklist_table');\"><i class='fa fa-fw fa-trash-o'></i>".self::$locale['delete']."</a>";
        echo "</div>";
        echo "<div class='pull-left'>";
        echo "<span class='pull-right m-t-10'>".sprintf(self::$locale['BLS_023'], $rows, $total_rows)."</span>\n";
        echo "</div>\n";
        echo "</div>\n";
        echo ($total_rows > $rows) ? makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request('', ['section'], FALSE).'&amp;') : '';
        echo "</div>\n";

        if ($rows > 0) {
            echo "<div class='table-responsive'><table id='blist-table' class='table table-hover table-striped'>\n";
            echo "<thead><tr>\n";
            echo "<th>&nbsp;</th>\n";
            echo "<th>".self::$locale['BLS_030']."</th>\n";
            echo "<th>".self::$locale['BLS_031']."</th>\n";
            echo "<th>".self::$locale['BLS_032']."</th>\n";
            echo "<th>".self::$locale['BLS_033']."</th>\n";
            echo "</tr>\n</thead>";
            echo "<tbody>\n";

            while ($data = dbarray($result)) {
                echo "<tr id='blist-".$data['blacklist_id']."' data-id=".$data['blacklist_id'].">\n";
                echo "<td>";
                echo form_checkbox('blacklist_id[]', '', '', ['value' => $data['blacklist_id'], 'input_id' => 'blist-id-'.$data['blacklist_id']]);
                echo "</td>";
                echo "<td>".($data['blacklist_ip'] ? $data['blacklist_ip'] : $data['blacklist_email']);
                if ($data['blacklist_reason']) {
                    echo "<br /><span class='small2'>".$data['blacklist_reason']."</span>";
                }
                echo "</td>\n<td>".(!empty($data['user_name']) ? profile_link($data['user_id'], $data['user_name'], $data['user_status']) : self::$locale['na'])."</td>\n";
                echo "<td>".(!empty($data['blacklist_datestamp']) ? showdate("shortdate", $data['blacklist_datestamp']) : self::$locale['na'])."</td>\n";
                echo "<td>
                <a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=blacklist_form&amp;action=edit&amp;blacklist_id=".$data['blacklist_id']."'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>
                <a class='btn btn-danger btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=blacklist&amp;action=delete&amp;blacklist_id=".$data['blacklist_id']."' onclick=\"return confirm('".self::$locale['BLS_014']."');\">".self::$locale['delete']."<i class='fa fa-trash m-l-10'></i></a>
                </td>\n";
                echo "</tr>\n";
                add_to_jquery('$("#blist-id-'.$data['blacklist_id'].'").click(function() {
                    if ($(this).prop("checked")) {
                        $("#blist-'.$data['blacklist_id'].'").addClass("active");
                    } else {
                        $("#blist-'.$data['blacklist_id'].'").removeClass("active");
                    }
                    });
                ');
            }

            echo "</tbody>\n";
            echo "</table>\n</div>\n";

            echo form_checkbox('check_all', self::$locale['BLS_039'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE]);
            echo closeform();
            add_to_jquery("
                $('#check_all').bind('click', function() {
                    if ($(this).is(':checked')) {
                        $('input[name^=blacklist_id]:checkbox').prop('checked', true);
                        $('#blist-table tbody tr').addClass('active');
                    } else {
                        $('input[name^=blacklist_id]:checkbox').prop('checked', false);
                        $('#blist-table tbody tr').removeClass('active');
                    }
                });
            ");

        } else {
            echo "<div class='text-center'>".self::$locale['BLS_015']."</div>\n";
        }
        closeside();
    }

    public function blacklistForm() {
        fusion_confirm_exit();
        openside('');
        echo "<div class='well'>".self::$locale['BLS_MS']."</div>\n";
        echo openform('blacklist_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=blacklist_form");
        echo form_hidden('blacklist_id', '', $this->data['blacklist_id']);
        echo form_hidden('blacklist_datestamp', '', $this->data['blacklist_datestamp']);

        echo form_text('blacklist_ip', str_replace(['[STRONG]', '[/STRONG]'], ['<strong>', '</strong>'], self::$locale['BLS_034']).'<span class="required">&nbsp;*</span>', $this->data['blacklist_ip'], ['inline' => TRUE]);

        echo form_text('blacklist_email', self::$locale['BLS_035'].'<span class="required">&nbsp;*</span>', $this->data['blacklist_email'], ['inline' => TRUE, 'type' => 'text', 'error_text' => self::$locale['BLS_016']]);

        echo form_textarea('blacklist_reason', self::$locale['BLS_036'], $this->data['blacklist_reason'], ['inline' => TRUE, 'autosize' => TRUE]);

        echo form_button('blacklist_admins', empty($_GET['blacklist_id']) ? self::$locale['BLS_037'] : self::$locale['BLS_038'], empty($_GET['blacklist_id']) ? self::$locale['BLS_037'] : self::$locale['BLS_038'], ['class' => 'btn-primary']);
        echo closeform();

        closeside();
    }
}

BlacklistAdministration::getInstance()->display_admin();
require_once THEMES.'templates/footer.php';
