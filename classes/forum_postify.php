<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: postify.php
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

namespace PHPFusion\Infusions\Forum\Classes;

use PHPFusion\BreadCrumbs;

class Forum_Postify extends Forum_Server {

    protected static $locale = [];
    protected static $default_redirect_link = '';
    protected static $postify_uri = [];
    protected static $settings = [];
    private $postify_action;

    public function __construct() {
        require_once INCLUDES."infusions_include.php";
        require_once INFUSIONS."forum/templates.php";
        self::$locale = fusion_get_locale();
        self::$settings = fusion_get_settings();
        self::get_forum_settings();

        if (!isset($_GET['forum_id'])) {
            throw new \Exception(self::$locale['forum_0587']);
        }

        if (!isset($_GET['thread_id'])) {
            throw new \Exception(self::$locale['forum_0588']);
        }

        self::$default_redirect_link = fusion_get_settings('site_seo') && defined('IN_PERMALINK') ? fusion_get_settings('siteurl').'infusions/forum/index.php' : fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".$_GET['thread_id'];

        if (!iMEMBER) {
            redirect(self::$default_redirect_link);
        }

        set_title(self::$locale['forum_0000']);
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FORUM.'index.php', 'title' => self::$locale['forum_0000']]);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function do_postify() {
        if ($postify = $this->load_postify($_GET['post'])) {
            if (method_exists($postify, 'execute')) {
                return $postify->execute();
            } else {
                if (iMOD) {
                    addNotice('danger', 'No action taken');
                    redirect(self::$default_redirect_link);
                }
            }
        } else {
            if (iMOD) {
                addNotice('danger', 'No action taken');
                redirect(self::$default_redirect_link);
            }
        }

        return NULL;
    }

    /**
     * @param $class_actions
     *
     * @return object
     * @throws \ReflectionException
     */
    private function load_postify($class_actions) {
        /*
         * Overrides
         */
        $implements = [
            'on'       => 'Track',
            'off'      => 'Track',
            'voteup'   => 'Vote',
            'votedown' => 'Vote'
        ];
        // Override the class action with the implemented method.
        $class_actions = (isset($implements[$class_actions]) ? $implements[$class_actions] : $class_actions);
        if (file_exists(FORUM_CLASS.'postify/postify_'.strtolower($class_actions).'.php')) {
            $namespace_ = '\\PHPFusion\\Infusions\\Forum\\Classes\\Postify\\Postify_';
            $class_name = $namespace_.$class_actions;
            $obj = new \ReflectionClass($class_name);
            if (!empty($obj)) {
                $this->postify_action[$class_actions] = $obj->newInstance();

                return (object)$this->postify_action[$class_actions];
            } else {
                throw new \Exception('Invalid Action');
            }
        } else {
            throw new \Exception('File does not exist');
        }
    }

    protected function get_postify_error_message() {
        $_GET['error'] = (!empty($_GET['error']) && isnum($_GET['error']) && $_GET['error'] <= 6 ? $_GET['error'] : 0);
        if (!empty($_GET['error'])) {
            switch ($_GET['error']) {
                case 1:
                    // Attachment file type is not allowed
                    return self::$locale['forum_0540'];
                    break;
                case 2:
                    // Invalid attachment of filesize
                    return self::$locale['forum_0541'];
                    break;
                case 3:
                    // Error: You did not specify a Subject and/or Message
                    return self::$locale['forum_0542'];
                    break;
                case 4:
                    // Error: Your cookie session has expired, please login and repost
                    return self::$locale['forum_0551'];
                case 5:
                    // This post is locked. Contact the moderator for further information.
                    return self::$locale['forum_0555'];
                    break;
                case 6:
                    // You may only edit a post for %d minute(s) after initial submission.
                    return sprintf(self::$locale['forum_0556'], self::$forum_settings['forum_edit_timelimit']);
                    break;
            }
        }

        return NULL;
    }

    /**
     * Generate default postify uri
     *
     * @return array
     */
    protected function get_postify_uri() {
        if ($_GET['error'] < 3) {
            if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
                addNotice('danger', 'URL Error');
                redirect(self::$default_redirect_link);
            }
            $link[] = ['url' => fusion_get_settings('siteurl').'infusions/forum/viewthread.php?thread_id='.$_GET['thread_id'], 'title' => self::$locale['forum_0548']];
            redirect(fusion_get_settings('siteurl').'infusions/forum/viewthread.php?thread_id='.$_GET['thread_id'], 3);
        }
        $link[] = ['url' => fusion_get_settings('siteurl')."infusions/forum/index.php?viewforum&amp;forum_id=".$_GET['forum_id'], 'title' => self::$locale['forum_0549']];
        $link[] = ['url' => fusion_get_settings('siteurl')."infusions/forum/index.php", 'title' => self::$locale['forum_0550']];

        return (array)$link;
    }
}
