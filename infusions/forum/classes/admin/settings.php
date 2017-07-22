<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/settings.php
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

use PHPFusion\BreadCrumbs;
use PHPFusion\Locale;
use PHPFusion\QuantumFields;

/**
 * Class ForumAdminSettings
 *
 * @package PHPFusion\Forums\Admin
 */
class ForumAdminSettings extends ForumAdminInterface {

    public function viewSettingsAdmin() {
        pageAccess('F');

        if (isset($_POST['recount_user_post'])) {
            $result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $result2 = dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
                }
                addNotice('success', self::$locale['forum_061']);
                redirect(FUSION_REQUEST);
            }
        }

        $tab['title']['general'] = self::$locale['forum_137'];
        $tab['id']['general'] = 'general';
        $tab['callback']['general'] = 'display_general_settings';

        $tab['title']['post'] = self::$locale['forum_138'];
        $tab['id']['post'] = 'post';
        $tab['callback']['post'] = 'display_post_settings';

        $tab['title']['ufields'] = self::$locale['forum_139'];
        $tab['id']['ufields'] = 'ufields';
        $tab['callback']['ufields'] = 'display_uf_settings';

        $_GET['ref'] = (isset($_GET['ref']) && method_exists($this, $tab['callback'][$_GET['ref']]) ? $_GET['ref'] : 'general');

        echo opentab($tab, $_GET['ref'], 'forum_settings_tab', TRUE, 'nav-tabs m-t-15', 'ref', ['ref'], TRUE);
        $function = $tab['callback'][$_GET['ref']];
        $this->$function();
        echo closetab();
    }

    private function display_uf_settings() {

        $_enabled = $this->get_forum_settings('forum_enabled_userfields');

        if (isset($_POST['save_forum_uf'])) {
            $current_uf = !empty($_POST['uf_field_enabled']) ? form_sanitizer($_POST['uf_field_enabled'], '', 'uf_field_enabled') : '';
            if (\defender::safe()) {
                if ($_enabled === NULL) {
                    $result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_enabled_userfields', :current_uf, 'forum')", [':current_uf' => $current_uf]);
                } else {
                    $result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value=:current_uf WHERE settings_name='forum_enabled_userfields' AND settings_inf='forum'", [':current_uf' => $current_uf]);
                }
                if (dbrows($result)) {
                    addNotice('success', self::$locale['900']);
                    redirect(FUSION_SELF.fusion_get_aidlink().'&section=fs&ref=ufields');
                }
            }
        }
        if (!empty($_enabled)) {
            $enabled_uf = explode(",", $_enabled);
            $enabled_uf = array_flip($enabled_uf);
        }
        ?>
        <div class='well spacer-sm'>
            <?php echo str_replace(['[LINK]', '[/LINK]'],
                ["<a href='".ADMIN."user_fields.php".fusion_get_aidlink()."'>", "</a>"], self::$locale['forum_150']);
        ?>
        </div>
        <?php
        echo openform('forum_uf_settings_frm', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        ?>
        <div class='clearfix'>
            <?php echo form_button('save_forum_uf', self::$locale['save_changes'], 'save_forum_uf', ['class' => 'btn-success']); ?>
        </div>
        <hr/>
        <?php
        // Check how many user fields is on.
        $ufc_select = "SELECT field_cat_id, field_cat_name FROM ".DB_USER_FIELD_CATS." ORDER BY field_cat_order ASC";
        $uf_select = "SELECT field_id, field_title, field_name, field_cat, field_type FROM ".DB_USER_FIELDS." WHERE field_cat=:field_cat ORDER BY field_order ASC";
        $ufc_query = dbquery($ufc_select);
        if (dbrows($ufc_query)) {
            while ($data = dbarray($ufc_query)) {
                ?>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='row'>
                            <div class='col-xs-12 col-sm-2'>
                                <h4>
                                    <strong>
                                        <?php echo QuantumFields::parse_label($data['field_cat_name']) ?>
                                    </strong>
                                </h4>
                            </div>
                            <div class='col-xs-12 col-sm-10'>
                                <div class='list-group-item'>
                                    <?php
                                    $uf_query = dbquery($uf_select, [':field_cat' => $data['field_cat_id']]);
                                    if (dbrows($uf_query)) {
                                        while ($cdata = dbarray($uf_query)) {
                                            $locale = fusion_get_locale();
                                            if (empty($cdata['field_title']) && $cdata['field_type'] == 'file') {
                                                $locale_file = LOCALE.LOCALESET.'user_fields/'.$cdata['field_name'].'.php';
                                                $var_file = INCLUDES.'user_fields/'.$cdata['field_name'].'_include_var.php';
                                                if (file_exists($locale_file) && file_exists($var_file)) {
                                                    $user_field_name = '';
                                                    Locale::setLocale($locale_file);
                                                    $locale = fusion_get_locale();
                                                    // after that i need to include the file.
                                                    include $var_file;
                                                }
                                                $current_field_title = (!empty($user_field_name) ? $user_field_name : self::$locale['na']);
                                            } else {
                                                $current_field_title = QuantumFields::parse_label($cdata['field_title']);
                                            }
                                            $checked = (isset($enabled_uf[$cdata['field_name']]) ? $cdata['field_name'] : '');
                                            echo form_checkbox('uf_field_enabled[]', $current_field_title, $checked, ['input_id' => 'uf_'.$cdata['field_id'], 'reverse_label' => TRUE, 'value' => $cdata['field_name'], 'class' => 'spacer-sm']);
                                        }
                                    } else {
                                        echo self::$locale['forum_151'];
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class='well'>
                <?php echo self::$locale['forum_152']; ?>
            </div>
            <?php
        }
        echo closeform();
    }

    private function display_general_settings() {

        if (isset($_POST['save_forum_settings'])) {
            $inputArray = array(
                'numofthreads'              => form_sanitizer($_POST['numofthreads'], 20, 'numofthreads'),
                'threads_per_page'          => form_sanitizer($_POST['threads_per_page'], 20, 'threads_per_page'),
                'posts_per_page'            => form_sanitizer($_POST['posts_per_page'], 20, 'posts_per_page'),
                'thread_notify'             => form_sanitizer($_POST['thread_notify'], '0', 'thread_notify'),
                'forum_ranks'               => form_sanitizer($_POST['forum_ranks'], '0', 'forum_ranks'),
                'forum_rank_style'          => form_sanitizer($_POST['forum_rank_style'], '0', 'forum_rank_style'),
                'popular_threads_timeframe' => form_sanitizer($_POST['popular_threads_timeframe'], '604800', 'popular_threads_timeframe'),
                'forum_last_posts_reply'    => form_sanitizer($_POST['forum_last_posts_reply'], '0', 'forum_last_posts_reply'),
                'upvote_points'             => form_sanitizer($_POST['upvote_points'], 2, 'upvote_points'),
                'downvote_points'           => form_sanitizer($_POST['downvote_points'], 1, 'downvote_points'),
                'answering_points'          => form_sanitizer($_POST['answering_points'], 15, 'answering_points'),
                'points_to_upvote'          => form_sanitizer($_POST['points_to_upvote'], 100, 'points_to_upvote'),
                'points_to_downvote'        => form_sanitizer($_POST['points_to_downvote'], 100, 'points_to_downvote'),
            );
            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = array(
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "forum",
                    );
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
                }
                addNotice('success', self::$locale['900']);
                redirect(FUSION_SELF.fusion_get_aidlink().'&section=fs');
            } else {
                addNotice("danger", self::$locale['901']);
                $forum_settings = $inputArray;
            }

        }

        $forum_settings = $this->get_forum_settings();
        $yes_no_array = array('1' => self::$locale['yes'], '0' => self::$locale['no']);
        // change the locale file here to this - echo "<div class='well'>".self::$locale['forum_description']."</div>";
        ?>
        <div class='well spacer-sm'>
            <strong><?php echo self::$locale['forum_description'] ?></strong>
        </div>
        <?php

        echo openform('forum_uf_settings_frm', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        ?>
        <div class='clearfix'>
            <?php echo form_button('save_forum_settings', self::$locale['save_changes'], 'save_forum_settings', ['class' => 'btn-success m-r-5']);
            echo form_button('recount_user_post', self::$locale['523'], '1');
            ?>
        </div>
        <hr/>
        <div class='row'>
            <div class='col-xs-12 col-sm-6'>
                <?php
                openside(self::$locale['forum_140']);
                echo form_text('threads_per_page', self::$locale['forum_080'], $forum_settings['threads_per_page'], [
                    'error_text'  => self::$locale['error_value'],
                    'inline'      => TRUE,
                    'inner_width' => '100px',
                    'width'       => '100px',
                    'type'        => 'number'
                ]);
                echo form_text('posts_per_page', self::$locale['forum_081'], $forum_settings['posts_per_page'], [
                    'error_text'  => self::$locale['error_value'],
                    'inline'      => TRUE,
                    'inner_width' => '100px',
                    'width'       => '100px',
                    'type'        => 'number'
                ]);
                echo form_text('numofthreads', self::$locale['505'], $forum_settings['numofthreads'], [
            'ext_tip' => self::$locale['506'],
                    'error_text'  => self::$locale['error_value'],
                    'inline'      => TRUE,
                    'inner_width' => '100px',
                    'width'       => '100px',
                    'type'        => 'number',
                ]);

                $timeframe_opts = array(
                    '604800'   => self::$locale['527'],
                    '2419200'  => self::$locale['528'],
                    '31557600' => self::$locale['529'],
                    '0'        => self::$locale['530']
                );
                $lastpost_opts = array('0' => self::$locale['519'], '1' => self::$locale['533']);
                for ($i = 2; $i <= 20; $i++) {
                    $array_opts[$i] = sprintf(self::$locale['532'], $i);
                }
                if (isset($_GET['action']) && $_GET['action'] == "count_posts") {
                    echo alert(self::$locale['524'], '', array('class' => 'warning'));
                }
                echo "<div class='clearfix'>\n";
                echo form_select('popular_threads_timeframe', self::$locale['525'],
                    $forum_settings['popular_threads_timeframe'], [
                        'options'    => $timeframe_opts,
                        'error_text' => self::$locale['error_value'],
                        'width'      => '100%',
                        'inline'     => TRUE,
                    ]);
                echo "</div>\n";
                echo "<div class='clearfix'>\n";
                echo form_select('forum_last_posts_reply', self::$locale['531'], $forum_settings['forum_last_posts_reply'],
                    [
                        'options'    => $lastpost_opts,
                        'error_text' => self::$locale['error_value'],
                        'width'      => '100%',
                        'inline'     => TRUE,
                    ]);
                echo "</div>\n";

                closeside();
                openside(self::$locale['forum_141']);
                echo form_select('thread_notify', self::$locale['512'], $forum_settings['thread_notify'], [
                    'options'    => $yes_no_array,
                    'error_text' => self::$locale['error_value'],
                    'inline'     => TRUE
                ]);
                closeside();
                ?>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <?php
                openside(self::$locale['forum_136']);
                $points_config = ['type' => 'number', 'width' => '150px', 'placeholder' => '1', 'inline' => TRUE, 'append' => TRUE, 'append_value' => self::$locale['forum_135']];
                echo form_text('upvote_points', self::$locale['forum_130'], $forum_settings['upvote_points'], $points_config);
                echo form_text('downvote_points', self::$locale['forum_131'], $forum_settings['downvote_points'], $points_config);
                echo form_text('answering_points', self::$locale['forum_132'], $forum_settings['answering_points'], $points_config);
                echo form_text('points_to_upvote', self::$locale['forum_133'], $forum_settings['points_to_upvote'], $points_config);
                echo form_text('points_to_downvote', self::$locale['forum_134'], $forum_settings['points_to_downvote'], $points_config);
                closeside();
                openside(self::$locale['forum_admin_001']);
                echo form_select('forum_ranks', self::$locale['520'], $forum_settings['forum_ranks'], [
                    'options'    => $yes_no_array,
                    'inline'     => TRUE,
                    'error_text' => self::$locale['error_value']
                ]);
                echo form_select('forum_rank_style', self::$locale['forum_064'], $forum_settings['forum_rank_style'], [
                    'options'    => [
                        self::$locale['forum_063'],
                        self::$locale['forum_062']
                    ],
                    'inline'     => TRUE,
                    'error_text' => self::$locale['error_value']
                ]);
                closeside();
                ?>
            </div>
        </div>
        <?php
        echo closeform();
    }

    private function display_post_settings() {

        if (isset($_POST['save_forum_post_settings'])) {
            $inputArray = array(
                'forum_ips'                  => form_sanitizer($_POST['forum_ips'], USER_LEVEL_SUPER_ADMIN, 'forum_ips'),
                'attachmax'                  => form_sanitizer($_POST['calc_b'], 1, 'calc_b') * form_sanitizer($_POST['calc_c'], 1000000, 'calc_c'),
                'forum_attachmax_count'      => form_sanitizer($_POST['forum_attachmax_count'], 5, 'forum_attachmax_count'),
                'forum_attachtypes'          => form_sanitizer($_POST['forum_attachtypes'], '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum_attachtypes'),
                'forum_edit_lock'            => form_sanitizer($_POST['forum_edit_lock'], '0', 'forum_edit_lock'),
                'forum_edit_timelimit'       => form_sanitizer($_POST['forum_edit_timelimit'], '0', 'forum_edit_timelimit'),
                'forum_last_post_avatar'     => form_sanitizer($_POST['forum_last_post_avatar'], '0', 'forum_last_post_avatar'),
                'forum_editpost_to_lastpost' => form_sanitizer($_POST['forum_editpost_to_lastpost'], '0', 'forum_editpost_to_lastpost'),
            );
            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = array(
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "forum",
                    );
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
                }
                addNotice('success', self::$locale['900']);
                redirect(clean_request('section=fs&ref=post', array('ref'), FALSE));
            } else {
                addNotice("danger", self::$locale['901']);
                $forum_settings = $inputArray;
            }

        }

        $forum_settings = $this->get_forum_settings();

        $yes_no_array = array('1' => self::$locale['yes'], '0' => self::$locale['no']);
        // change the locale file here to this - echo "<div class='well'>".self::$locale['forum_description']."</div>";
        ?>
        <div class='well spacer-sm'>
            <strong><?php echo self::$locale['forum_description'] ?></strong>
        </div>
        <?php

        echo openform('forum_post_settings_frm', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        ?>
        <div class='clearfix'>
            <?php echo form_button('save_forum_settings', self::$locale['save_changes'], 'save_forum_settings', ['class' => 'btn-success m-r-5']);
            echo form_button('recount_user_post', self::$locale['523'], '1');
            ?>
        </div>
        <hr/>
        <div class='row'>
            <div class='col-xs-12 col-sm-6'>
                <?php
                openside(self::$locale['forum_142']);
                $calc_opts = self::$locale['1020'];
                $calc_c = self::calculate_byte($forum_settings['forum_attachmax']);
                $calc_b = $forum_settings['forum_attachmax'] / $calc_c;
                require_once INCLUDES."mimetypes_include.php";
                $mime = mimeTypes();
                $mime_opts = array();
                foreach ($mime as $m => $Mime) {
                    $ext = ".$m";
                    $mime_opts[$ext] = $ext;
                }
                sort($mime_opts);

                echo form_text('calc_b', self::$locale['508'], $calc_b, [
                    'required'    => TRUE,
                    'type'        => 'number',
                    'error_text'  => self::$locale['error_rate'],
                    'width'       => '100%',
                    'inner_width' => '100px',
                    'max_length'  => '3',
                    'inline'      => TRUE,
                    'ext_tip'     => self::$locale['509'],
                    'stacked'     => form_select('calc_c', '', $calc_c, [
                        'options'     => $calc_opts,
                        'placeholder' => self::$locale['choose'],
                        'class'       => 'display-inline-block',
                        'width'       => '150px',
                        'inner_width' => '150px'
                    ])
                ]);
                $range = range(1, 10);
                echo form_btngroup('forum_attachmax_count', self::$locale['534'], $forum_settings['forum_attachmax_count'], [
                    'options'    => array_combine(range(1, count($range)), array_values($range)),
                    'error_text' => self::$locale['error_value'],
                    'width'      => '100%',
                    'inline'     => TRUE,
                    'ext_tip'    => self::$locale['535'],
                ]);
                echo form_select('forum_attachtypes', self::$locale['510'], $forum_settings['forum_attachtypes'], [
                    'options'     => $mime_opts,
                    'width'       => '100%',
                    'inner_width' => '100%',
                    'error_text'  => self::$locale['error_type'],
                    'tags'        => TRUE,
                    'multiple'    => TRUE,
                    'placeholder' => self::$locale['choose'],
                    'inline'      => TRUE,
                    'ext_tip'     => self::$locale['511']
                ]);
                closeside();
                ?>
            </div>
            <div class='col-xs-12 col-sm-6'>
                <?php
                openside(self::$locale['forum_143']);
                echo "<span class='pull-right position-absolute small' style='right:30px;'>".self::$locale['537']."</span>\n";
                echo form_btngroup('forum_edit_timelimit', self::$locale['536'], $forum_settings['forum_edit_timelimit'], [
                    'options'    => [
                        '0',
                        '10',
                        '30',
                        '45',
                        '60'
                    ],
                    'max_length' => 2,
                    'width'      => '100px',
                    'required'   => TRUE,
                    'error_text' => self::$locale['error_value'],
                    'inline'     => TRUE
                ]);
                echo form_select('forum_ips', self::$locale['507'], $forum_settings['forum_ips'], [
                    'options'    => $yes_no_array,
                    'error_text' => self::$locale['error_value'],
                    'inline'     => TRUE
                ]);

                echo form_select('forum_last_post_avatar', self::$locale['539'], $forum_settings['forum_last_post_avatar'],
                    [
                        'options'    => $yes_no_array,
                        'error_text' => self::$locale['error_value'],
                        'inline'     => TRUE
                    ]);
                echo form_select('forum_edit_lock', self::$locale['521'], $forum_settings['forum_edit_lock'], [
                    'options'    => $yes_no_array,
                    'error_text' => self::$locale['error_value'],
                    'inline'     => TRUE
                ]);
                echo form_select('forum_editpost_to_lastpost', self::$locale['538'],
                    $forum_settings['forum_editpost_to_lastpost'], [
                        'options'    => $yes_no_array,
                        'error_text' => self::$locale['error_value'],
                        'inline'     => TRUE
                    ]);
                closeside();
                ?>
            </div>
        </div>
        <?php
        echo closeform();
    }

    /**
     * Calculate byte
     *
     * @param $download_max_b
     *
     * @return int|string
     */
    protected static function calculate_byte($download_max_b) {
        $calc_opts = self::$locale['1020'];
        foreach ($calc_opts as $byte => $val) {
            if ($download_max_b / $byte <= 999) {
                return $byte;
            }
        }
        return 1000000;
    }
}