<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: reply.php
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
namespace PHPFusion\Forums\Postify;

use PHPFusion\BreadCrumbs;

/**
 * Forum Reply
 * Class Postify_Reply
 *
 * @status  Stable
 *
 * @package PHPFusion\Forums\Postify
 */
class Postify_Reply extends Forum_Postify {

    public function execute() {

        $settings = fusion_get_settings();

        add_to_title(self::$locale['global_201'].self::$locale['forum_0360']);

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0360']]);

        $thread_data = dbarray(dbquery("SELECT thread_id, forum_id, thread_lastpostid, thread_postcount, thread_subject FROM ".DB_FORUM_THREADS." WHERE thread_id=:thread_id", [':thread_id' => $_GET['thread_id']]));

        $thread_data['thread_link'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".$thread_data['thread_id']."&pid=".$thread_data['thread_lastpostid']."#post_".$thread_data['thread_lastpostid'];

        if ($_GET['error'] < 2) {

            if (!isset($_GET['post_id']) || !isnum($_GET['post_id'])) {
                throw new \Exception('$_GET[ post_id ] is blank, and not passed! Please report this.');
            }

            if (self::$forum_settings['thread_notify'] && isnum($_GET['thread_id'])) {
                // Find all users to notify
                $notify_query = "SELECT tn.*, tu.user_id, tu.user_name, tu.user_email, tu.user_level, tu.user_groups
                FROM ".DB_FORUM_THREAD_NOTIFY." tn
                LEFT JOIN ".DB_USERS." tu ON tn.notify_user=tu.user_id
                WHERE thread_id=:thread_id AND notify_user !=:my_id AND notify_status=:status GROUP BY tn.notify_user";
                $notify_bind = [
                    ':thread_id' => intval($_GET['thread_id']),
                    ':my_id'     => fusion_get_userdata('user_id'),
                    ':status'    => 1,
                ];
                $notify_result = dbquery($notify_query, $notify_bind);

                if (dbrows($notify_result)) {

                    $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
                    require_once INCLUDES.'sendmail_include.php';

                    $template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='POST' LIMIT 1");
                    if (dbrows($template_result) > 0) {
                        $template_data = dbarray($template_result);
                        if ($template_data['template_active'] == 1) {
                            while ($data = dbarray($notify_result)) {
                                if ($this->check_forum_access($forum_index, '', $_GET['thread_id'], $data['user_id'])) {
                                    sendemail_template("POST", $thread_data['thread_subject'], "", "", $data['user_name'], $thread_data['thread_link'], $data['user_email']);
                                }
                            }
                        } else {
                            while ($data = dbarray($notify_result)) {
                                if ($this->check_forum_access($forum_index, '', $_GET['thread_id'], $data['user_id'])) {
                                    $message_subject = str_replace("{THREAD_SUBJECT}", $thread_data['thread_subject'], self::$locale['forum_0660']);
                                    $message_content = strtr(self::$locale['forum_0661'], [
                                        '{USERNAME}'       => $data['user_name'],
                                        '{THREAD_SUBJECT}' => $thread_data['thread_subject'],
                                        '{THREAD_URL}'     => $thread_data['thread_link'],
                                        '{SITENAME}'       => self::$settings['sitename'],
                                        '{SITEUSERNAME}'   => self::$settings['siteusername'],
                                    ]);
                                    sendemail($data['user_name'], $data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], $message_subject, $message_content);
                                }
                            }
                        }
                    } else {
                        while ($data = dbarray($notify_result)) {
                            if ($this->check_forum_access($forum_index, '', $_GET['thread_id'], $data['user_id'])) {
                                $message_subject = str_replace("{THREAD_SUBJECT}", $thread_data['thread_subject'], self::$locale['forum_0660']);
                                $message_content = strtr(self::$locale['forum_0661'], [
                                    '{USERNAME}'       => $data['user_name'],
                                    '{THREAD_SUBJECT}' => $thread_data['thread_subject'],
                                    '{THREAD_URL}'     => $thread_data['thread_link'],
                                    '{SITENAME}'       => self::$settings['sitename'],
                                    '{SITEUSERNAME}'   => self::$settings['siteusername'],
                                ]);
                                sendemail($data['user_name'], $data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], $message_subject, $message_content);
                            }
                        }
                    }
                }
            }

            $thread_last_page = ($thread_data['thread_postcount'] > self::$forum_settings['posts_per_page'] ? floor(floor(($thread_data['thread_postcount'] - 1) / self::$forum_settings['posts_per_page']) * self::$forum_settings['posts_per_page']) : 0);
            $redirect_add = '';
            if ($thread_last_page) {
                $redirect_add = '&amp;rowstart='.$thread_last_page;
            }
            $link[] = [
                'url'   => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_data['thread_id'].$redirect_add.'&amp;pid='.$thread_data['thread_lastpostid'].'#post_'.$thread_data['thread_lastpostid'],
                'title' => self::$locale['forum_0548']
            ];
            redirect($settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_data['thread_id'].$redirect_add.'&amp;pid='.$thread_data['thread_lastpostid'].'#post_'.$thread_data['thread_lastpostid'], 3);

        } else {
            $link[] = [
                'url'   => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_data['thread_id'].'&amp;pid='.$thread_data['thread_lastpostid'].'#post_'.$thread_data['thread_lastpostid'],
                'title' => self::$locale['forum_0548']
            ];
            redirect($settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_data['thread_id'].'&amp;pid='.$thread_data['thread_lastpostid'].'#post_'.$thread_data['thread_lastpostid'], 4);
        }
        $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php?viewforum&amp;forum_id='.$thread_data['forum_id'], 'title' => self::$locale['forum_0549']];
        $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php', 'title' => self::$locale['forum_0550']];

        render_postify([
            'title'       => self::$locale['forum_0360'],
            'error'       => $this->get_postify_error_message(),
            'description' => $this->get_postify_error_message() ?: self::$locale['forum_0544'],
            'link'        => $link
        ]);
    }
}
