<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: comments.php
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
require_once THEMES.'templates/admin_header.php';

use \PHPFusion\BreadCrumbs;

class CommentsAdministration {
    private static $instance = NULL;
    private static $rows = 0;
    private $aidlink = '';
    private $locale = [];
    private $commentType = [];

    private function __construct() {

        pageAccess('C');
        $this->aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale("", LOCALE.LOCALESET."admin/comments.php");
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
        $this->commentType = \PHPFusion\Admins::getInstance()->getCommentType();
        $_GET['ctype'] = isset($_GET['ctype']) ? $_GET['ctype'] : key($this->commentType);

        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'delete':
                    $result = $this->delete_comments($_GET['comment_id']);
                    if ($result) {
                        addNotice('success', $this->locale['411']);
                        redirect(clean_request('', ['section', 'action', 'comment_id'], FALSE));
                    }
                    break;
                case 'delban':
                    $result = $this->ban_comments($_GET['comment_id']);
                    if ($result) {
                        addNotice('success', fusion_get_locale('BLS_011', LOCALE.LOCALESET."admin/blacklist.php"));
                        redirect(clean_request('', ['section', 'action', 'comment_id'], FALSE));
                    }
                    break;
                default:
                    break;
            }
        }

    }

    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new CommentsAdministration();
        }

        self::$rows = dbcount("(comment_id)", DB_COMMENTS, (!empty($_GET['ctype']) ? "comment_type='".$_GET['ctype']."'" : '').(!empty($_GET['comment_item_id']) ? " AND comment_item_id=".$_GET['comment_item_id']."" : ''));
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= self::$rows) ? $_GET['rowstart'] : 0;
        return self::$instance;
    }

    public function display_administration_form() {

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => ADMIN.'comments.php'.fusion_get_aidlink(),
            'title' => $this->locale['401']
        ]);

        $allowed_section = ['comments_view', 'comments_edit'];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'comments_view';

        if ($_GET['section'] == 'comments_edit') {
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => ADMIN.'comments.php'.fusion_get_aidlink(),
                'title' => $this->locale['400']
            ]);
        }

        $master_tab_title['title'][] = $this->locale['401'];
        $master_tab_title['id'][] = 'comments_view';
        $master_tab_title['icon'][] = 'fa fa-comment';

        if (!empty($_GET['comment_id'])) {
            $master_tab_title['title'][] = $this->locale['400'];
            $master_tab_title['id'][] = 'comments_edit';
            $master_tab_title['icon'][] = 'fa fa-edit';
        }

        opentable($this->locale['401']);
        echo opentab($master_tab_title, $_GET['section'], 'comments_view', TRUE, 'nav-tabs m-b-20');

        switch ($_GET['section']) {
            case "comments_view":
                $this->comments_view();
                break;
            case "comments_edit":
                $this->comments_edit();
                break;
            default:
                break;
        }

        echo closetab();
        closetable();
    }

    private function comments_edit() {
        if (isset($_POST['save_comment']) && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
            $comment_message = form_sanitizer($_POST['comment_message'], '', 'comment_message');
            dbquery("UPDATE ".DB_COMMENTS." SET comment_message=:CommentMessage WHERE comment_id=:CommentId", [
                ':CommentMessage' => $comment_message,
                ':CommentId'      => $_GET['comment_id']
            ]);
            addNotice('success', $this->locale['410']);
            redirect(clean_request('', ['section', 'comment_item_id', 'comment_id'], FALSE));
        }

        if (isset($_GET['comment_id']) && isnum($_GET['comment_id'])) {

            $result = dbquery(self::get_CommentsQuery());
            $data = dbarray($result);

            echo openform('settingsform', 'post', FUSION_REQUEST);
            echo form_textarea('comment_message', '', $data['comment_message'], [
                'autosize' => TRUE, 'bbcode' => TRUE, 'preview' => TRUE, 'form_name' => 'settingsform'
            ]);
            echo form_button('save_comment', $this->locale['421'], $this->locale['421'], ['class' => 'btn-primary']);
            echo closeform();
        }
    }

    private function comments_Button() {
        $text = "<div class='text-center well'>\n";
        $text .= "<div class='btn-group'>\n";
        foreach ($this->commentType as $key => $value) {
            $text .= "<a class='btn btn-default".($_GET['ctype'] == $key ? ' active' : '')."' href='".FUSION_SELF.$this->aidlink."&amp;ctype=$key'>".$value."</a>\n";
        }
        $text .= "</div>\n</div>\n";
        return $text;
    }

    protected static function get_NavQuery() {
        $condition = (!empty($_GET['ctype']) ? "WHERE c.comment_type='".$_GET['ctype']."'" : '');
        $query = "SELECT
            c.comment_id, c.comment_item_id, c.comment_name, c.comment_subject, c.comment_message, c.comment_datestamp, c.comment_ip, c.comment_type,
            u.user_id, u.user_name, u.user_status
            FROM ".DB_COMMENTS." AS c
            LEFT JOIN ".DB_USERS." AS u ON c.comment_name=u.user_id
            $condition
            ORDER BY c.comment_datestamp ASC
            ";
        return $query;

    }

    protected static function get_CommentsQuery() {
        $limit = 20;
        $ctype = !empty($_GET['ctype']) ? "WHERE c.comment_type='".$_GET['ctype']."'" : '';
        $comment_item_id = !empty($_GET['comment_item_id']) ? " AND c.comment_item_id=".$_GET['comment_item_id']."" : '';
        $comment_id = !empty($_GET['comment_id']) ? " AND c.comment_id=".$_GET['comment_id']."" : '';

        $condition = $ctype.$comment_id.$comment_item_id;
        $order = "c.comment_datestamp ASC";

        $query = "SELECT
            c.comment_id, c.comment_item_id, c.comment_name, c.comment_subject, c.comment_message, c.comment_datestamp, c.comment_ip, c.comment_ip_type, c.comment_type,
            u.user_id, u.user_name, u.user_status
            FROM ".DB_COMMENTS." AS c
            LEFT JOIN ".DB_USERS." AS u ON c.comment_name=u.user_id
            $condition
            ORDER BY $order LIMIT ".intval($_GET['rowstart']).", $limit
            ";
        return $query;

    }

    private function comments_view() {
        $row = '';
        $navrows = '';
        $result = '';
        $navresult = '';

        if (!empty($_GET['ctype'])) {
            $result = dbquery(self::get_CommentsQuery());
            $row = dbrows($result);
            $navresult = dbquery(self::get_NavQuery());
            $navrows = dbrows($navresult);
        }

        $info = [
            'table_name' => \PHPFusion\Admins::getInstance()->getCommentType($_GET['ctype'])." ".$this->locale['401'],
            'buttons'    => $this->comments_Button(),
            'no_data'    => (!$row) ? "<div class='alert alert-info text-center'>".$this->locale['434']."</div>\n" : '',
            'page_nav'   => '<div class="m-t-5 m-b-5">'.makepagenav($_GET['rowstart'], 20, self::$rows, 3, FUSION_SELF.fusion_get_aidlink()."&amp;ctype=".$_GET['ctype'].(!empty($_GET['comment_item_id']) ? "&amp;comment_item_id=".$_GET['comment_item_id'] : '')."&amp;").'</div>'
        ];

        if (self::$rows > 0) {
            if ($navrows) {
                while ($data = dbarray($navresult)) {
                    $info['item_id'][$data['comment_item_id']] = $data['comment_item_id'];
                }
            }

            if ($row) {

                while ($data = dbarray($result)) {
                    $info['data'][] = [
                        'edit_link'   => FUSION_SELF.fusion_get_aidlink()."&amp;section=comments_edit&amp;ctype=".$_GET['ctype']."&amp;comment_id=".$data['comment_id'].(!empty($_GET['comment_item_id']) ? "&amp;comment_item_id=".$_GET['comment_item_id'] : ''),
                        'del_link'    => FUSION_SELF.fusion_get_aidlink()."&amp;section=comments_view&amp;ctype=".$_GET['ctype']."&amp;action=delete&amp;comment_id=".$data['comment_id']."' onclick=\"return confirm('".$this->locale['433']."');\"",
                        'delban_link' => FUSION_SELF.fusion_get_aidlink()."&amp;section=comments_view&amp;ctype=".$_GET['ctype']."&amp;action=delban&amp;comment_id=".$data['comment_id']."' onclick=\"return confirm('".$this->locale['435']."');\"",
                        'profile'     => $data['user_name'] ? profile_link($data['comment_name'], $data['user_name'], $data['user_status']) : $data['comment_name'],
                        'date'        => $this->locale['global_071'].showdate("longdate", $data['comment_datestamp']),
                        'ip'          => "<span class='label label-default m-l-10'>".$this->locale['432']." ".$data['comment_ip']."</span>",
                        'subject'     => !empty($data['comment_subject']) ? "<div class='m-t-10'>".nl2br(parseubb(parsesmileys($data['comment_subject'])))."</div>\n" : "",
                        'messages'    => "<div class='m-t-10'>".nl2br(parseubb(parsesmileys($data['comment_message'])))."</div>\n",
                    ];
                }
            }
        }
        openside();
        self::reder_commentAdmin($info);
        closeside();
    }

    public function reder_commentAdmin($info) {
        if (!empty($info)) {
            echo $info['buttons'];

            if (!empty($info['data'])) {
                echo '<div class="list-group">';
                foreach ($info['data'] as $coment) {
                    echo "<div class='list-group-item'>\n";
                    echo "<div class='btn-group pull-right'>\n";
                    echo "<a class='btn btn-xs btn-default' href='".$coment['edit_link']."'>".$this->locale['edit']."</a>\n";
                    echo "<a class='btn btn-xs btn-default' href='".$coment['del_link']."'>".$this->locale['delete']."</a>\n";
                    echo "<a class='btn btn-xs btn-default' href='".$coment['delban_link']."'>".$this->locale['431']."</a>\n";
                    echo "</div>\n";
                    echo $coment['profile'].' '.$coment['date'].$coment['ip'];
                    echo $coment['subject'];
                    echo $coment['messages'];
                    echo "</div>\n";
                }
                echo '</div>';
                echo $info['page_nav'];
            } else {
                echo $info['no_data'];
            }
        }
    }

    private static function delete_comments($comment_id) {
        $result = NULL;
        if (isnum($comment_id)) {
            $result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id=:CommentId", [':CommentId' => $comment_id]);

            return $result;
        }

        return $result;

    }

    private function ban_comments($comment_id) {
        $result = NULL;
        if (isnum($comment_id)) {
            $resultquery = dbquery("SELECT * FROM ".DB_COMMENTS." WHERE comment_id=:CommentId", [':CommentId' => $comment_id]);

            $data = dbarray($resultquery);

            $info = [
                'blacklist_id'        => '',
                'blacklist_user_id'   => fusion_get_userdata('user_id'),
                'blacklist_ip'        => $data['comment_ip'],
                'blacklist_ip_type'   => $data['comment_ip_type'],
                'blacklist_email'     => '',
                'blacklist_reason'    => $this->locale['436'],
                'blacklist_datestamp' => time()
            ];

            dbquery_insert(DB_BLACKLIST, $info, 'save');
            $result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id=:CommentId", [':CommentId' => $comment_id]);

            return $result;
        }

        return $result;

    }

}

CommentsAdministration::getInstance()->display_administration_form();

require_once THEMES.'templates/footer.php';
