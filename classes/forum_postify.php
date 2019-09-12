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

/**
 * Class Forum_Postify
 *
 * @package PHPFusion\Infusions\Forum\Classes
 */
class Forum_Postify extends Forum_Server {

    /**
     * @var array|null
     */
    protected static $locale = [];
    /**
     * @var string
     */
    protected static $default_redirect_link = '';
    /**
     * @var array
     */
    protected static $postify_uri = [];
    /**
     * @var array|string|string[]
     */
    protected static $settings = [];
    /**
     * @var
     */
    private $postify_action;
    /**
     * @var int
     */
    private $forum_id = 0;
    /**
     * @var int
     */
    private $thread_id = 0;

    /**
     * Forum_Postify constructor.
     *
     * @throws \Exception
     */
    public function __construct() {
        require_once INCLUDES."infusions_include.php";
        require_once INFUSIONS."forum/templates.php";
        self::$locale = fusion_get_locale();
        self::$settings = fusion_get_settings();
        self::get_forum_settings();
        $this->forum_id = get('forum_id', FILTER_VALIDATE_INT);
        $this->thread_id = get('thread_id', FILTER_VALIDATE_INT);

        if (!$this->forum_id) {
            throw new \Exception(self::$locale['forum_0587']);
        }
        if (!$this->thread_id) {
            throw new \Exception(self::$locale['forum_0588']);
        }

        self::$default_redirect_link = fusion_get_settings('site_seo') && defined('IN_PERMALINK') ? fusion_get_settings('siteurl').'infusions/forum/index.php' : fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".$_GET['thread_id'];

        if (!iMEMBER) {
            redirect(self::$default_redirect_link);
        }

        set_title(self::$locale['forum_0000']);
        add_breadcrumb(['link' => FORUM.'index.php', 'title' => self::$locale['forum_0000']]);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function do_postify() {
        $post = get('post');
        if ($postify = $this->load_postify($post)) {
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
     * @return bool
     */
    private function loaded_postify($class_actions) {
        if (is_file(FORUM_CLASS.'postify/'.strtolower($class_actions).'.php')) {
            include FORUM_CLASS.'postify/'.strtolower($class_actions).'.php';
            $namespace_ = '\\PHPFusion\\Infusions\\Forum\\Classes\\Postify\\Postify_';
            $class_name = $namespace_.ucfirst($class_actions);

            $this->postify_action[$class_actions] = new $class_name();

            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param $class_actions
     *
     * @return mixed
     * @throws \Exception
     */
    private function load_postify($class_actions) {
        /*
         * Overrides
         */
        $override_functions = [
            'on'       => 'track',
            'off'      => 'track',
            'voteup'   => 'vote',
            'votedown' => 'vote'
        ];

        if (isset($override_functions[$class_actions])) {
            $class_actions = $override_functions[$class_actions];
        }

        if ($this->loaded_postify($class_actions)) {
            return $this->postify_action[$class_actions];
        } else {
            throw new \Exception('File does not exist');
        }
    }

    /**
     * @return int
     */
    protected function getPostifyError() {
        $error = get('error', FILTER_VALIDATE_INT);
        return (int)($error < 6 ? $error : 0);
    }

    protected function get_postify_error_message() {
        $error = $this->getPostifyError();

        if (!empty($error)) {
            switch ($error) {
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
        $error = $this->getPostifyError();

        if ($error < 3) {
            if (!$this->thread_id) {
                addNotice('danger', 'URL Error');
                redirect(self::$default_redirect_link);
            }
            $link[] = ['url' => fusion_get_settings('siteurl').'infusions/forum/viewthread.php?thread_id='.$this->thread_id, 'title' => self::$locale['forum_0548']];
            redirect(fusion_get_settings('siteurl').'infusions/forum/viewthread.php?thread_id='.$this->thread_id, 3);
        }
        $link[] = ['url' => fusion_get_settings('siteurl')."infusions/forum/index.php?viewforum&amp;forum_id=".$this->forum_id, 'title' => self::$locale['forum_0549']];
        $link[] = ['url' => fusion_get_settings('siteurl')."infusions/forum/index.php", 'title' => self::$locale['forum_0550']];

        return (array)$link;
    }
}
