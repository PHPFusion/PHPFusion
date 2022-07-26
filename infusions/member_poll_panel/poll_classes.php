<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: poll_classes.php
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
use PHPFusion\Quantum\QuantumHelper;

/**
 * Class MemberPoll
 */
class MemberPoll {
    private $allowed_section = ['poll', 'poll_vote'];
    private static $instance = NULL;
    private static $locale = [];
    private static $limit = 4;
    private $data = [
        'poll_id'         => 0,
        'poll_title'      => '',
        'poll_opt'        => ['', ''],
        'poll_started'    => '',
        'poll_ended'      => '',
        'poll_visibility' => ''
    ];

    public function __construct() {
        self::$locale = fusion_get_locale("", POLLS_LOCALE);

        $action = check_get('action') ? get('action') : '';

        switch ($action) {
            case 'delete':
                self::deletePoll(get('poll_id'));
                break;
            case 'poll_add':
                self::startPoll(get('poll_id'));
                break;
            case 'poll_lock':
                self::pollLock(get('poll_id'));
                break;
            case 'poll_unlock':
                self::pollUnlock(get('poll_id'));
                break;
            default:
                break;
        }

        self::setPollDb();
        if (defined('ADMIN_PANEL')) {
            add_to_title(self::$locale['POLL_001']);
            self::setAdminPollDb();
        }
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private static function deletePoll($id) {
        if (self::verifyPoll($id)) {
            dbquery("DELETE FROM ".DB_POLLS." WHERE poll_id='".(int)$id."'");
            addnotice('success', self::$locale['POLL_007']);
            redirect(clean_request("", ["section=poll", "aid"]));
        }
    }

    static function verifyPoll($id) {
        if (isnum($id)) {
            return dbcount("(poll_id)", DB_POLLS, "poll_id='".(int)$id."'");
        }

        return FALSE;
    }

    private static function startPoll($id) {
        if (self::verifyPoll($id)) {
            dbquery("UPDATE ".DB_POLLS." SET poll_started='".time()."' WHERE poll_id='".(int)$id."'");
            addnotice('success', self::$locale['POLL_008']);
            redirect(clean_request("", ["section=poll", "aid"]));
        }
    }

    private static function pollLock($id) {
        if (self::verifyPoll($id)) {
            dbquery("UPDATE ".DB_POLLS." SET poll_ended='".time()."' WHERE poll_id='".(int)$id."'");

            addnotice('success', self::$locale['POLL_009']);
            redirect(clean_request("", ["section=poll", "aid"]));
        }
    }

    private static function pollUnlock($id) {
        if (self::verifyPoll($id)) {
            dbquery("UPDATE ".DB_POLLS." SET poll_ended='0' WHERE poll_id='".(int)$id."'");

            addnotice('success', self::$locale['POLL_010']);
            redirect(clean_request("", ["section=poll", "aid"]));
        }
    }

    private function setPollDb() {
        $_poll_id = post("poll_id", FILTER_VALIDATE_INT);

        if (check_post("cast_vote") && check_post("check") && $_poll_id) {

            $result = dbquery("SELECT v.vote_user, v.vote_id, v.vote_user_ip, v.vote_user_ip_type, p.poll_id, p.poll_opt, p.poll_started, p.poll_ended
                FROM ".DB_POLLS." p
                LEFT JOIN ".DB_POLL_VOTES." v ON p.poll_id = v.poll_id
                WHERE ".groupaccess('poll_visibility')." AND p.poll_id=:pid
                ORDER BY v.vote_id
            ", [":pid" => $_poll_id]);

            $data = [];

            while ($pdata = dbarray($result)) {
                $voters[] = iMEMBER ? $pdata['vote_user'] : $pdata['vote_user_ip'];
                $data = $pdata;
            }

            if ($data['poll_started'] < time() && (empty($data['poll_ended']) or ($data['poll_ended'] > time())) && (empty($voters) || !empty($data["poll_opt"]))) {
                $vote_save = [
                    'vote_user'         => iMEMBER ? fusion_get_userdata('user_id') : 0,
                    'vote_user_ip'      => USER_IP,
                    'vote_user_ip_type' => USER_IP_TYPE,
                    'vote_opt'          => sanitizer('check', 0, 'check'),
                    'poll_id'           => $_poll_id
                ];

                if (fusion_safe()) {

                    dbquery_insert(DB_POLL_VOTES, $vote_save, "save");
                    addnotice('success', "<i class='fa fa-check-square-o fa-lg m-r-10'></i>".self::$locale['POLL_013']);
                }

            } else {
                addnotice('warning', "<i class='fa fa-close fa-lg m-r-10'></i>".self::$locale['POLL_014']);
            }

            redirect(clean_request());
        }
    }

    private function setAdminPollDb() {
        if (check_post("save")) {

            $poll_opt = [];
            $i = 0;
            while ($i < post("opt_count")) {
                foreach (post(["poll_opt_".$i]) as $key => $value) {
                    if ($value != '') {
                        $poll_opt[$i][$key] = $value;
                    }
                }
                $i++;
            }
            $poll_option = array_filter($poll_opt);
            $_poll_id = (int)get("poll_id", FILTER_VALIDATE_INT);

            $this->data = [
                'poll_id'         => $_poll_id,
                'poll_title'      => sanitizer(["poll_title"], "", 'poll_title', TRUE),
                'poll_opt'        => htmlspecialchars_decode(descript(serialize($poll_option))),
                'poll_visibility' => sanitizer('poll_visibility', 0, 'poll_visibility'),
                'poll_started'    => sanitizer('poll_started', 0, 'poll_started'),
                'poll_ended'      => (check_post('poll_ended') ? sanitizer('poll_ended', 0, 'poll_ended') : 0)
            ];
            if (fusion_safe()) {

                addnotice("success", $this->data['poll_id'] == 0 ? self::$locale['POLL_005'] : self::$locale['POLL_006']);
                dbquery_insert(DB_POLLS, $this->data, ($this->data['poll_id'] == 0 ? "save" : "update"));
                redirect(clean_request("", ["section=poll", "aid"]));
            }

            $this->data["poll_opt"] = $poll_option;
        }
    }

    public function displayAdmin() {
        add_breadcrumb(['link' => INFUSIONS.'member_poll_panel/poll_admin.php'.fusion_get_aidlink(), 'title' => self::$locale['POLL_001']]);

        if (check_post("cancel")) {
            redirect(clean_request('section=poll', ['aid']));
        }

        $sections = in_array(get('section'), $this->allowed_section) ? get('section') : $this->allowed_section[0];
        $edit = (check_get('action') && get('action') == 'edit') && check_get('poll_id');
        if ($sections && $sections == 'poll_vote') {
            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $edit ? self::$locale['POLL_042'] : self::$locale['POLL_043']]);
        }

        opentable(self::$locale['POLL_001']);
        $master_tab_title['title'][] = self::$locale['POLL_001'];
        $master_tab_title['id'][] = "poll";
        $master_tab_title['icon'][] = "fa fa-bar-chart";
        $master_tab_title['title'][] = $edit ? self::$locale['POLL_042'] : self::$locale['POLL_043'];
        $master_tab_title['id'][] = "poll_vote";
        $master_tab_title['icon'][] = $edit ? 'fa fa-pencil' : 'fa fa-plus';

        echo opentab($master_tab_title, $sections, "poll", TRUE, "nav-tabs", "section", ['rowstart', 'action', 'poll_id']);
        switch ($sections) {
            case "poll_vote":
                $this->pollForm();
                break;
            default:
                $this->pollListing();
                break;
        }
        echo closetab();
        closetable();
    }

    public function pollForm() {
        fusion_confirm_exit();

        $this->data['poll_started'] = time();

        $_poll_id = get("poll_id", FILTER_VALIDATE_INT);
        if (get("action") === "edit" && $_poll_id) {
            if (self::verifyPoll($_poll_id)) {
                $result = dbquery("SELECT poll_id, poll_title, poll_opt, poll_started, poll_ended, poll_visibility
                    FROM ".DB_POLLS."
                    WHERE poll_id='".(int)$_poll_id."'
                ");
                if (dbrows($result) > 0) {
                    $this->data = dbarray($result);
                }

                $this->data['poll_title'] = unserialize(stripslashes($this->data['poll_title']));
                $this->data['poll_opt'] = unserialize(stripslashes($this->data['poll_opt']));
            }
        }

        if (check_post("addoption")) {

            $this->data['poll_title'] = stripinput(post('poll_title'));
            $this->data['poll_visibility'] = stripinput(post('poll_visibility'));
            $i = 0;
            while ($i < $_POST['opt_count']) {
                $opt_field = "poll_opt_".$i;
                $this->data['poll_opt'][$i] = \Defender::sanitize_array($_POST[$opt_field]);
                $i++;
            }
            // Add new selection
            $this->data['poll_opt'][$i] = '';
        }

        $opt_count = count($this->data['poll_opt']);
        echo openform('addcat', 'post');
        echo "<div class='clearfix m-b-20'>\n";
        echo form_button('addoption', self::$locale['POLL_050'], self::$locale['POLL_050'], [
            'class'    => 'btn-primary m-r-10',
            'inline'   => TRUE,
            'icon'     => 'fa fa-plus',
            'input_id' => 'button_1'

        ]);
        echo form_button('save', self::$locale['POLL_052'], self::$locale['POLL_052'], [
            'class'    => 'btn-success m-r-10',
            'inline'   => TRUE,
            'icon'     => 'fa fa-hdd-o',
            'input_id' => 'button_2'
        ]);
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'], ['input_id' => 'button_3']);
        echo "</div>\n";

        echo form_hidden('poll_id', '', $this->data['poll_id']);
        echo form_hidden('opt_count', '', $opt_count);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6 col-md-8 col-lg-9'>\n";
        echo QuantumHelper::quantumMultilocaleFields('poll_title', self::$locale['POLL_045'], $this->data['poll_title'], [
            'required'    => TRUE,
            'inline'      => FALSE,
            'placeholder' => self::$locale['POLL_069']
        ]);

        echo "<div class='panel panel-default'>\n";
        echo "<div class='panel-body'>\n";
        $i = 1;
        foreach ($this->data['poll_opt'] as $im1 => $data1) {
            $nam = "poll_opt_$im1";
            echo QuantumHelper::quantumMultilocaleFields($nam, self::$locale['POLL_046'].' '.$im1, $data1, [
                'required'    => TRUE,
                'inline'      => TRUE,
                'placeholder' => self::$locale['POLL_070']
            ]);
            echo($i < $opt_count ? "<hr/>\n" : '');
            $i++;
        }
        echo "</div>\n</div>\n";

        echo "</div><div class='col-xs-12 col-sm-6 col-md-4 col-lg-3'>\n";
        openside('');
        echo form_select('poll_visibility', self::$locale['POLL_044'], $this->data['poll_visibility'], [
            'inline'      => FALSE,
            'width'       => '100%',
            'inner_width' => '100%',
            'options'     => fusion_get_groups()
        ]);
        echo form_datepicker('poll_started', self::$locale['POLL_048'], $this->data['poll_started'], ['inline' => FALSE]);
        echo form_datepicker('poll_ended', self::$locale['POLL_049'], $this->data['poll_ended'], ['inline' => FALSE]);
        closeside();
        echo "</div>\n</div>\n";

        echo form_button('addoption', self::$locale['POLL_050'], self::$locale['POLL_050'], [
            'class'  => 'btn-primary m-r-10',
            'inline' => TRUE,
            'icon'   => 'fa fa-plus'
        ]);

        echo form_button('save', self::$locale['POLL_052'], self::$locale['POLL_052'], [
            'class'  => 'btn-success m-r-10',
            'inline' => TRUE,
            'icon'   => 'fa fa-hdd-o'
        ]);
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
        echo closeform();
    }

    public function pollListing() {
        $aidlink = fusion_get_aidlink();
        $total_rows = dbcount("(poll_id)", DB_POLLS, groupaccess('poll_visibility'));
        $rowstart = get_rowstart("rowstart", $total_rows);
        $result = $this->selectDB($rowstart);
        $rows = dbrows($result);

        echo "<div class='clearfix'>\n";
        echo "<span class='pull-right'>".sprintf(self::$locale['POLL_011'], $rows, $total_rows)."</span>\n";
        echo "</div>\n";

        echo ($total_rows > $rows) ? makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", ["aid", "section"])."&amp;") : "";

        if ($rows > 0) {
            echo "<div class='row'>\n";
            while ($data = dbarray($result)) {
                $title = unserialize(stripslashes($data['poll_title']));
                $poll_opt = unserialize(stripslashes($data['poll_opt']));
                echo "<div class='col-xs-12 col-sm-3'>\n";
                echo "<div class='panel panel-default'>\n";
                echo "<div class='panel-heading text-left'>\n";
                foreach ($title as $key => $info) {
                    echo "<p class='m-b-0'>".(!empty($info) ? translate_lang_names($key).": ".$info : $info)."</p>\n";
                }
                echo '<hr>';
                echo "<span>".self::$locale['POLL_048']." ".showdate("shortdate", $data['poll_started'])."</span>\n";
                echo "<span class='badge'>".self::$locale['POLL_064'].' '.($data['poll_started'] > time() ? self::$locale['POLL_065'] : (!empty($data['poll_ended']) && ($data['poll_ended'] < time()) ? self::$locale['POLL_024'] : self::$locale['POLL_067']))."</span>\n";
                if (!empty($data['poll_ended']) && $data['poll_ended'] < time()) {
                    echo "<p>".self::$locale['POLL_024'].": ".showdate("shortdate", $data['poll_ended'])."</p>\n";
                }
                echo "</div>\n";

                echo "<div class='panel-body'>\n";
                $db_info = dbcount("(vote_opt)", DB_POLL_VOTES, "poll_id='".$data['poll_id']."'");
                foreach ($poll_opt as $keys => $data1) {
                    $text = "";
                    foreach ($data1 as $key => $inf) {
                        $text .= "<p>".(!empty($inf) ? translate_lang_names($key).": ".$inf : $inf)."</p>\n";
                    }
                    $num_votes = dbcount("(vote_opt)", DB_POLL_VOTES, "vote_opt='".$keys."' AND poll_id='".$data['poll_id']."'");
                    $opt_votes = ($num_votes ? number_format(($num_votes / $db_info) * 100) : number_format(0 * 100));
                    echo progress_bar($opt_votes, $text);
                    echo "<p><strong>".$opt_votes."% [".(format_word($num_votes, self::$locale['POLL_040']))."]</strong></p>\n";
                }
                echo "<p><strong>".self::$locale['POLL_060'].' '.$db_info."</strong></p>\n";
                echo "</div>\n";

                echo "<div class='panel-footer'>\n";
                echo "<div class='dropdown'>\n";
                echo "<button id='ddp".$data['poll_id']."' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' class='btn btn-default dropdown-toggle btn-block' type='button'>".self::$locale['POLL_021']." <span class='caret'></span></button>\n";
                echo "<ul class='dropdown-menu' aria-labelledby='ddp".$data['poll_id']."'>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&section=poll_vote&action=edit&poll_id=".$data['poll_id']."'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a></li>\n";
                if ($data['poll_started'] > time()) {
                    echo "<li><a href='".FUSION_SELF.$aidlink."&section=poll&action=poll_add&poll_id=".$data['poll_id']."'><i class='fa fa-play fa-fw'></i> ".self::$locale['POLL_022']."</a></li>\n";
                }
                if (!empty($data['poll_ended']) && ($data['poll_ended'] < time())) {
                    echo "<li><a href='".FUSION_SELF.$aidlink."&section=poll&action=poll_unlock&poll_id=".$data['poll_id']."'><i class='fa fa-refresh fa-fw'></i> ".self::$locale['POLL_023']."</a></li>\n";
                }
                if ($data['poll_started'] < time() && empty($data['poll_ended']) or $data['poll_ended'] > time()) {
                    echo "<li><a href='".FUSION_SELF.$aidlink."&section=poll&action=poll_lock&poll_id=".$data['poll_id']."'><i class='fa fa-times fa-fw'></i> ".self::$locale['POLL_024']."</a></li>\n";
                }
                echo "<li class='divider'></li>\n";
                echo "<li><a href='".FUSION_SELF.$aidlink."&section=poll_vote&action=delete&poll_id=".$data['poll_id']."'><i class='fa fa-trash fa-fw'></i> ".self::$locale['delete']."</a></li>\n";
                echo "</ul>\n";
                echo "</div>\n";
                echo "</div>\n";

                echo "</div>\n";
                echo "</div>\n"; // .col-xs-12
            }
            echo "</div>\n";
        } else {
            echo "<div class='well text-center'>".self::$locale['POLL_012']."</div>\n";
        }
    }

    public function selectDB($rows) {
        return dbquery("SELECT poll_id, poll_title, poll_opt, poll_started, poll_ended, poll_visibility
            FROM ".DB_POLLS."
            WHERE ".groupaccess('poll_visibility')."
            ORDER BY poll_id DESC
            LIMIT ".(int)$rows.", ".self::$limit
        );
    }

    public function displayPoll() {
        $res = $this->selectPoll();
        if (!$res) {
            return;
        }

        $poll_title = unserialize($res['poll_title']);
        $poll_opt = unserialize($res['poll_opt']);
        $data = [
            'poll_id'         => $res['poll_id'],
            'poll_title'      => !empty($poll_title[LANGUAGE]) ? $poll_title[LANGUAGE] : "",
            'poll_started'    => $res['poll_started'],
            'poll_ended'      => $res['poll_ended'],
            'poll_visibility' => $res['poll_visibility'],
        ];

        for ($i = 0; $i < count($poll_opt); $i++) {
            $data['poll_option'][$i] = !empty($poll_opt[$i][LANGUAGE]) ? $poll_opt[$i][LANGUAGE] : "";
        }

        $render = [];

        if (!empty($data)) {
            $data_user = !(checkgroup($data['poll_visibility']) && !empty($data['poll_title']) && ($data['poll_ended'] == 0 || $data['poll_ended'] > time())) || $this->selectVote(fusion_get_userdata((iMEMBER ? 'user_id' : 'user_ip')), $data['poll_id']);

            if ($data_user == FALSE) {
                $render['poll_table'][0]['max_vote'] = $this->countVote("poll_id='".$data['poll_id']."'");
                $render['poll_table'][0]['poll_title'] = $data['poll_title'];

                foreach ($data['poll_option'] as $im1 => $data1) {
                    $render['poll_table'][0]['poll_option'][] = form_checkbox('check', $data1, '-1', ['reverse_label' => TRUE, 'type' => 'radio', 'value' => $im1, 'input_id' => 'check-'.$im1]);
                }

                $render['poll_table'][0]['openform'] = openform('voteform', 'post', clean_request(), ['max_tokens' => 1]).form_hidden('poll_id', '', $data['poll_id']);
                $render['poll_table'][0]['button'] = form_button("cast_vote", self::$locale['POLL_020'], self::$locale['POLL_020'], ['class' => 'btn-primary']);
                $render['poll_table'][0]['closeform'] = closeform();

            } else {
                if (!empty($data['poll_title']) && $data['poll_started'] < time()) {
                    $render['poll_table'][0]['max_vote'] = $this->countVote("poll_id='".$data['poll_id']."'");
                    $render['poll_table'][0]['poll_title'] = $data['poll_title'];

                    foreach ($data['poll_option'] as $im1 => $data1) {
                        $num_votes = $this->countVote("vote_opt='".$im1."' AND poll_id='".$data['poll_id']."'");
                        $opt_votes = ($num_votes ? number_format(($num_votes / $render['poll_table'][0]['max_vote']) * 100) : number_format(0 * 100));
                        $render['poll_table'][0]['poll_option'][] = progress_bar($opt_votes, $data1);
                        $render['poll_table'][0]['poll_option'][] = $opt_votes."% [".format_word($num_votes, self::$locale['POLL_040'])."]";
                    }

                    $render['poll_table'][0]['poll_foot'][] = self::$locale['POLL_060']." ".$render['poll_table'][0]['max_vote'];
                    $render['poll_table'][0]['poll_foot'][] = self::$locale['POLL_048']." ".showdate("shortdate", $data['poll_started']);

                    if ($data['poll_started'] < time() && (!empty($data['poll_ended']) && ($data['poll_ended'] < time()))) {
                        $render['poll_table'][0]['poll_foot'][] = self::$locale['POLL_049'].": ".showdate("shortdate", $data['poll_ended']);
                    }
                }
            }

            $render['poll_tablename'] = self::$locale['POLL_001'];

            if (dbcount("(poll_id)", DB_POLLS, groupaccess('poll_visibility')) > 1) {
                $render['poll_arch'] = "<a class='btn btn-default btn-sm' href='".INFUSIONS."member_poll_panel/polls_archive.php'>".self::$locale['POLL_063']."</a>";
            }

            render_poll($render);
        }
    }

    public function selectPoll() {
        $result = dbquery("SELECT poll_id, poll_title, poll_opt, poll_started, poll_ended, poll_visibility
            FROM ".DB_POLLS."
            WHERE poll_id=COALESCE(
                (
                    SELECT poll_id
                    FROM ".DB_POLLS."
                    WHERE ".groupaccess('poll_visibility')." AND poll_started < ".time()." AND (poll_ended=0 OR poll_ended > ".time().")
                    ORDER BY poll_started DESC
                    LIMIT 1
                ),
                (
                    SELECT poll_id
                    FROM ".DB_POLLS."
                    WHERE ".groupaccess('poll_visibility')." AND poll_started < ".time()."
                    ORDER BY poll_started DESC
                    LIMIT 1
                )
            )
        ");

        if (dbrows($result)) {
            return dbarray($result);
        } else {
            return NULL;
        }
    }

    public function selectVote($user, $pollid) {
        $whr = iMEMBER ? "vote_user='".$user."'" : "vote_user_ip='".USER_IP."'";
        $result = dbquery("SELECT vote_id, vote_user, vote_opt, vote_user_ip, poll_id
            FROM ".DB_POLL_VOTES."
            WHERE poll_id='".$pollid."' AND ".$whr
        );

        if (!dbrows($result)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function countVote($opt) {
        return dbcount("(vote_id)", DB_POLL_VOTES, $opt);
    }

    public function pollsArchive() {
        opentable(self::$locale['POLL_002']);
        add_to_title(self::$locale['POLL_002']);

        $total_rows = dbcount("(poll_id)", DB_POLLS, groupaccess('poll_visibility'));
        $rowstart = get_rowstart("rowstart", $total_rows);
        $result = $this->selectDB($rowstart);
        $rows = dbrows($result);

        if ($rows > 0) {
            echo "<div class='row m-t-20'>\n";
            while ($data = dbarray($result)) {
                $title = unserialize(stripslashes($data['poll_title']));
                $poll_opt = unserialize(stripslashes($data['poll_opt']));

                echo "<div class='col-xs-12 col-sm-3'>\n";
                echo "<div class='panel panel-default'>\n";
                echo "<div class='panel-heading text-left'>\n";
                echo !empty($title[LANGUAGE]) ? $title[LANGUAGE] : "";
                echo "</div>\n";

                echo "<div class='panel-body'>\n";
                $db_info = dbcount("(vote_opt)", DB_POLL_VOTES, "poll_id='".$data['poll_id']."'");

                foreach ($poll_opt as $keys => $data1) {
                    $text = !empty($data1[LANGUAGE]) ? $data1[LANGUAGE] : "";
                    $num_votes = dbcount("(vote_opt)", DB_POLL_VOTES, "vote_opt='".$keys."' AND poll_id='".$data['poll_id']."'");
                    $opt_votes = ($num_votes ? number_format(($num_votes / $db_info) * 100) : number_format(0 * 100));
                    echo progress_bar($opt_votes, $text);
                    echo "<p><strong>".$opt_votes."% [".format_word($num_votes, self::$locale['POLL_040'])."]</strong></p>\n";
                }
                echo "</div>\n";

                echo "<div class='panel-footer'>\n";
                echo "<p class='m-b-0'><strong>".self::$locale['POLL_060'].' '.$db_info."</strong></p>\n";
                echo "<span>".self::$locale['POLL_048']." ".showdate("shortdate", $data['poll_started'])."</span>\n";
                echo "<span class='badge'>".self::$locale['POLL_064'].' '.($data['poll_started'] > time() ? self::$locale['POLL_065'] : (!empty($data['poll_ended']) && ($data['poll_ended'] < time()) ? self::$locale['POLL_024'] : self::$locale['POLL_067']))."</span>\n";

                if (!empty($data['poll_ended']) && $data['poll_ended'] < time()) {
                    echo "<p>".self::$locale['POLL_024'].": ".showdate("shortdate", $data['poll_ended'])."</p>\n";
                }

                echo "</div>\n";
                echo "</div>\n";
                echo "</div>\n"; // .col-xs-12
            }
            echo "</div>\n";
            echo ($total_rows > $rows) ? '<div class="text-center">'.makepagenav($rowstart, self::$limit, $total_rows, self::$limit, INFUSIONS."member_poll_panel/polls_archive.php?").'</div>' : "";
        } else {
            echo "<div class='well text-center'>".self::$locale['POLL_012']."</div>\n";
        }
        closetable();
    }
}
