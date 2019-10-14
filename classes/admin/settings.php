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

use PHPFusion\Locale;
use PHPFusion\UserFieldsQuantum;

/**
 * Class ForumAdminSettings
 *
 * @package PHPFusion\Forums\Admin
 */
class ForumAdminSettings extends ForumAdminInterface {

    private function recountUserPost() {
        if (post('recount_user_post')) {
            $result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
                }
                addNotice('success', self::$locale['forum_061']);
                redirect(FUSION_REQUEST);
            }
        }
    }

    public function viewSettingsAdmin() {
        pageAccess('F');

        $this->recountUserPost();

        $tab['title']['general'] = self::$locale['forum_137'];
        $tab['id']['general'] = 'general';
        $tab['callback']['general'] = 'generalSettings';

        $tab['title']['post'] = self::$locale['forum_138'];
        $tab['id']['post'] = 'post';
        $tab['callback']['post'] = 'display_post_settings';

        $tab['title']['ufields'] = self::$locale['forum_139'];
        $tab['id']['ufields'] = 'ufields';
        $tab['callback']['ufields'] = 'display_uf_settings';

        $refs = get('ref');
        $refs = isset($tab['callback'][$refs]) && method_exists($this, $tab['callback'][$refs]) ? $refs : 'general';

        echo opentab($tab, $refs, 'forum_settings_tab', TRUE, 'nav-tabs m-t-15', 'ref', ['ref'], TRUE);
        $function = $tab['callback'][$refs];
        $this->$function();
        echo closetab();
    }

    private function generalSettings() {

        if (post('save_forum_settings')) {
            $inputArray = [
                'numofthreads'              => sanitizer('numofthreads', 16, 'numofthreads'),
                'threads_per_page'          => sanitizer('threads_per_page', 20, 'threads_per_page'),
                'posts_per_page'            => sanitizer('posts_per_page', 20, 'posts_per_page'),
                'thread_notify'             => sanitizer('thread_notify', 0, 'thread_notify'),
                'forum_ranks'               => sanitizer('forum_ranks', 0, 'forum_ranks'),
                'forum_rank_style'          => sanitizer('forum_rank_style', 0, 'forum_rank_style'),
                'popular_threads_timeframe' => sanitizer('popular_threads_timeframe', 604800, 'popular_threads_timeframe'),
                'forum_last_posts_reply'    => sanitizer('forum_last_posts_reply', 0, 'forum_last_posts_reply'),
                'upvote_points'             => sanitizer('upvote_points', 2, 'upvote_points'),
                'downvote_points'           => sanitizer('downvote_points', 1, 'downvote_points'),
                'answering_points'          => sanitizer('answering_points', 15, 'answering_points'),
                'points_to_upvote'          => sanitizer('points_to_upvote', 100, 'points_to_upvote'),
                'points_to_downvote'        => sanitizer('points_to_downvote', 100, 'points_to_downvote'),
                'forum_show_reputation'     => sanitizer('forum_show_reputation', 0, 'forum_show_reputation'),
            ];
            if (fusion_safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        "settings_name"  => $settings_name,
                        "settings_value" => $settings_value,
                        "settings_inf"   => "forum",
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", ["primary_key" => "settings_name"]);
                }
                addNotice('success', self::$locale['900']);
                redirect(FUSION_SELF.fusion_get_aidlink().'&section=fs');
            } else {
                addNotice("danger", self::$locale['901']);
            }

        }

        $forum_settings = self::get_forum_settings();
        $yes_no_array = ['1' => self::$locale['yes'], '0' => self::$locale['no']];

        $timeframe_opts = [
            '604800'   => self::$locale['527'],
            '2419200'  => self::$locale['528'],
            '31557600' => self::$locale['529'],
            '0'        => self::$locale['530']
        ];

        $lastpost_opts = ['0' => self::$locale['no'], '1' => self::$locale['533']];
        for ($i = 2; $i <= 20; $i++) {
            $array_opts[$i] = sprintf(self::$locale['532'], $i);
        }
        $points_config = ['type' => 'number', 'width' => '150px', 'placeholder' => '1', 'inline' => TRUE, 'append' => TRUE, 'append_value' => self::$locale['forum_135']];

        echo openform('forum_uf_settings_frm', 'post');
        echo "<div class='m-b-20'>\n";
        echo "<h4>".self::$locale['forum_140']."</h4>";
        echo "<p>".self::$locale['forum_description']."</p>";
        echo "</div>\n";

        echo form_text('threads_per_page', self::$locale['forum_080'], $forum_settings['threads_per_page'], [
                'error_text'  => self::$locale['error_value'],
                'inline'      => TRUE,
                'inner_width' => '100px',
                'width'       => '100px',
                'type'        => 'number'
            ]).
            form_text('posts_per_page', self::$locale['forum_081'], $forum_settings['posts_per_page'], [
                'error_text'  => self::$locale['error_value'],
                'inline'      => TRUE,
                'inner_width' => '100px',
                'width'       => '100px',
                'type'        => 'number'
            ]);
        form_text('numofthreads', self::$locale['505'], $forum_settings['numofthreads'], [
            'ext_tip'     => self::$locale['506'],
            'error_text'  => self::$locale['error_value'],
            'inline'      => TRUE,
            'inner_width' => '100px',
            'width'       => '100px',
            'type'        => 'number',
        ]);

        if (get('action') == "count_posts") {
            echo alert(self::$locale['524'], ['class' => 'warning']);
        }

        echo form_select('popular_threads_timeframe', self::$locale['525'],
            $forum_settings['popular_threads_timeframe'], [
                'options'          => $timeframe_opts,
                'error_text'       => self::$locale['error_value'],
                'width'            => '100%',
                'inline'           => TRUE,
                'select2_disabled' => TRUE,
            ]);
        echo form_select('forum_last_posts_reply', self::$locale['531'], $forum_settings['forum_last_posts_reply'],
            [
                'options'          => $lastpost_opts,
                'error_text'       => self::$locale['error_value'],
                'width'            => '100%',
                'inline'           => TRUE,
                'select2_disabled' => TRUE,
            ]);

        echo "<h4 class='m-b-20'>".self::$locale['forum_141']."</h4>";

        echo form_select('thread_notify', self::$locale['512'], $forum_settings['thread_notify'], [
            'options'          => $yes_no_array,
            'error_text'       => self::$locale['error_value'],
            'inline'           => TRUE,
            'select2_disabled' => TRUE,
        ]);
        echo "</div>\n";

        echo form_select('forum_show_reputation', self::$locale['513'], $forum_settings['forum_show_reputation'], [
            'options'          => $yes_no_array,
            'error_text'       => self::$locale['error_value'],
            'inline'           => TRUE,
            'select2_disabled' => TRUE,
        ]);

        echo "<h4 class='m-b-20'>".self::$locale['forum_136']."</h4>";
        echo form_text('upvote_points', self::$locale['forum_130'], $forum_settings['upvote_points'], $points_config);
        echo form_text('downvote_points', self::$locale['forum_131'], $forum_settings['downvote_points'], $points_config);
        echo form_text('answering_points', self::$locale['forum_132'], $forum_settings['answering_points'], $points_config);
        echo form_text('points_to_upvote', self::$locale['forum_133'], $forum_settings['points_to_upvote'], $points_config);
        echo form_text('points_to_downvote', self::$locale['forum_134'], $forum_settings['points_to_downvote'], $points_config);

        echo "<h4>".self::$locale['forum_136']."</h4>";
        echo form_select('forum_ranks', self::$locale['520'], $forum_settings['forum_ranks'], [
            'options'          => $yes_no_array,
            'inline'           => TRUE,
            'error_text'       => self::$locale['error_value'],
            'select2_disabled' => TRUE,
        ]);
        echo form_select('forum_rank_style', self::$locale['forum_064'], $forum_settings['forum_rank_style'], [
            'options'          => [
                self::$locale['forum_063'],
                self::$locale['forum_062']
            ],
            'select2_disabled' => TRUE,
            'inline'           => TRUE,
            'error_text'       => self::$locale['error_value']
        ]);

        echo form_button('save_forum_settings', self::$locale['save_changes'], 'save_forum_settings', ['class' => 'btn-success m-r-5']);

        echo closeform();
    }

    private function display_post_settings() {

        if (post('save_forum_post_settings')) {
            $inputArray = [
                'forum_ips'                  => sanitizer('forum_ips', USER_LEVEL_SUPER_ADMIN, 'forum_ips'),
                'forum_attachmax'            => sanitizer('calc_b', 1048576, 'calc_b') * sanitizer('calc_c', 1, 'calc_c'),
                'forum_attachmax_count'      => sanitizer('forum_attachmax_count', 5, 'forum_attachmax_count'),
                'forum_attachtypes'          => sanitizer('forum_attachtypes', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum_attachtypes'),
                'forum_edit_lock'            => sanitizer('forum_edit_lock', 0, 'forum_edit_lock'),
                'forum_edit_timelimit'       => sanitizer('forum_edit_timelimit', 0, 'forum_edit_timelimit'),
                'forum_last_post_avatar'     => sanitizer('forum_last_post_avatar', 0, 'forum_last_post_avatar'),
                'forum_editpost_to_lastpost' => sanitizer('forum_editpost_to_lastpost', 0, 'forum_editpost_to_lastpost'),
            ];
            if (fusion_safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "forum",
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", ["primary_key" => "settings_name"]);
                }
                addNotice('success', self::$locale['900']);
                redirect(clean_request('section=fs&ref=post', ['ref'], FALSE));
            } else {
                addNotice("danger", self::$locale['901']);
            }

        }

        $forum_settings = self::get_forum_settings();

        $calc_opts = self::$locale['1020'];
        $calc_c = calculate_byte($forum_settings['forum_attachmax']);
        $calc_b = $forum_settings['forum_attachmax'] / $calc_c;
        require_once INCLUDES."mimetypes_include.php";
        $mime = mimeTypes();
        $mime_opts = [];
        foreach ($mime as $m => $Mime) {
            $ext = ".$m";
            $mime_opts[$ext] = $ext;
        }
        sort($mime_opts);

        $yes_no_array = ['1' => self::$locale['yes'], '0' => self::$locale['no']];

        $range = range(1, 10);


        echo openform('forum_post_settings_frm', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);

        echo "<div class='m-b-20'>\n";
        echo "<h4>".self::$locale['forum_142']."</h4>";
        echo "<p>".self::$locale['forum_description']."</p>";
        echo "</div>";

        echo '<div class="display-block overflow-hide m-b-20">';
        echo '<label class="text-normal col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" for="calc_b">'.self::$locale['508'].'</label>';
        echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
        echo "<div style='display:flex;'>\n";
        echo form_text('calc_b', '', $calc_b, [
            'required'   => TRUE,
            'type'       => 'number',
            'error_text' => self::$locale['error_rate'],
            'inline'     => TRUE,
            'width'      => '150px',
            'max_length' => 4,
            'class'      => 'm-b-0 m-r-10'
        ]);
        echo form_select('calc_c', '', $calc_c, [
                'options'          => $calc_opts,
                'placeholder'      => self::$locale['choose'],
                'inner_width'      => '100%',
                'width'            => '180px',
                'class'            => 'm-b-0',
                'select2_disabled' => TRUE,
            ])."</div>\n";
        echo "<div class='clearfix spacer-sm tip'><i>".self::$locale['509']."</i></div>";
        echo '</div>';
        echo '</div>';

        echo '<div class="display-block overflow-hide m-b-20">';
        echo '<label class="text-normal col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0">'.self::$locale['523'].'</label>';
        echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
        echo form_button('recount_user_post', self::$locale['523'], '1');
        echo '</div>';
        echo '</div>';

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
            'ext_tip'     => self::$locale['511'],
            'width'       => '50%',
            'inner_width' => '50%',
        ]);

        echo "<h4 class='m-b-20'>".self::$locale['forum_143']."</h4>";
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
            'error_text' => self::$locale['error_value'],
            'ext_tip'    => self::$locale['537'],
            'inline'     => TRUE
        ]);
        $yes_no_extarray = ['1' => self::$locale['yes'], USER_LEVEL_SUPER_ADMIN => self::$locale['no']];
        echo form_select('forum_ips', self::$locale['507'], $forum_settings['forum_ips'], [
            'options'    => $yes_no_extarray,
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
        echo form_button('save_forum_post_settings', self::$locale['save_changes'], 'save_forum_settings', ['class' => 'btn-primary']);
        echo closeform();
    }

    private function display_uf_settings() {

        $_enabled = self::get_forum_settings('forum_enabled_userfields');

        if (post('save_forum_uf')) {

            $current_uf = sanitizer('uf_field_enabled', '', 'uf_field_enabled');

            if (fusion_safe()) {
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

        echo openform('forum_uf_settings_frm', 'post');

        echo "<div class='m-b-20'>\n";
        echo "<h4>".self::$locale['forum_142']."</h4>";
        echo "<p>".str_replace(['[LINK]', '[/LINK]'], ["<a href='".ADMIN."user_fields.php".fusion_get_aidlink()."'>", "</a>"], self::$locale['forum_150'])."</p>";
        echo "</div>";

        // Check how many user fields is on.
        $ufc_select = "SELECT field_cat_id, field_cat_name FROM ".DB_USER_FIELD_CATS." ORDER BY field_cat_order ASC";
        $uf_select = "SELECT field_id, field_title, field_name, field_cat, field_type FROM ".DB_USER_FIELDS." WHERE field_cat=:field_cat ORDER BY field_order ASC";
        $ufc_query = dbquery($ufc_select);
        if (dbrows($ufc_query)) {
            while ($data = dbarray($ufc_query)) {
                echo "<div class='".grid_row()."'>
                <div class='".grid_column_size(100, 30, 30, 15)."'><h4>".UserFieldsQuantum::parse_label($data['field_cat_name'])."</h4></div>
                <div class='".grid_column_size(100, 70, 70, 75)."'>\n";


                $uf_query = dbquery($uf_select, [':field_cat' => $data['field_cat_id']]);
                if (dbrows($uf_query)) {
                    while ($cdata = dbarray($uf_query)) {
                        if (empty($cdata['field_title']) && $cdata['field_type'] == 'file') {
                            $locale_file = LOCALE.LOCALESET.'user_fields/'.$cdata['field_name'].'.php';
                            $var_file = INCLUDES.'user_fields/'.$cdata['field_name'].'_include_var.php';
                            if (file_exists($locale_file) && file_exists($var_file)) {
                                $user_field_name = '';
                                Locale::setLocale($locale_file);
                                // after that i need to include the file.
                                include $var_file;
                            }
                            $current_field_title = (!empty($user_field_name) ? $user_field_name : self::$locale['na']);
                        } else {
                            $current_field_title = UserFieldsQuantum::parse_label($cdata['field_title']);
                        }
                        $checked = (isset($enabled_uf[$cdata['field_name']]) ? $cdata['field_name'] : '');
                        echo form_checkbox('uf_field_enabled[]', $current_field_title, $checked, [
                            'input_id'      => 'uf_'.$cdata['field_id'],
                            'class'         => 'm-0',
                            'reverse_label' => TRUE, 'value' => $cdata['field_name']]);
                    }
                } else {
                    echo "<div class='display-block'>".self::$locale['forum_151']."</div>";
                }
                echo "</div>\n</div>\n";
                echo "<hr/>";
            }

            echo form_button('save_forum_uf', self::$locale['save_changes'], 'save_forum_uf', ['class' => 'btn-primary']);

        } else {
            ?>
            <div class='well'>
                <?php echo self::$locale['forum_152']; ?>
            </div>
            <?php
        }
        echo closeform();
    }


}
