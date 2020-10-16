<?php
/**
 * Forum Page Control Layout and Viewer.
 */

use PHPFusion\Infusions\Forum\Classes\ForumViewer;

if (!function_exists('render_forum')) {
    /**
     * @param $info
     *
     * @return string
     */
    function render_forum($info) {
        return ForumViewer::getInstance()->render_forum( $info );
    }
}

/**
 * Displays viewforum for 4 basic pages
 * get view - threads
 * get view - subforums
 * get view - people
 * get view - activity
 */
if (!function_exists('render_viewforum')) {
    function render_viewforum($info) {
        return ForumViewer::getInstance()->viewforum( $info );
    }
}

/**
 * Display The Forum Thread Page
 * Template File        templates/forum_threads.html
 */
if (!function_exists('render_thread')) {
    /**
     * @param $info
     *
     * @return string
     */
    function render_thread($info) {
        return ForumViewer::getInstance()->render_thread( $info );
    }
}

/**
 * Display the Quick Reply Form
 * Template File        templates/forms/quick_reply.html
 */
if (!function_exists("display_quick_reply")) {
    /**
     * @param $info
     *
     * @return string
     */
    function display_quick_reply($info) {
        return ForumViewer::getInstance()->displayQuickReply( $info );
    }
}

/**
 * Display the poll creation form
 * Template File    templates/forms/poll.html
 */
if (!function_exists("display_forum_pollform")) {
    /**
     * @param $info
     *
     * @return string
     */
    function display_forum_pollform($info) {
        return ForumViewer::getInstance()->display_forum_pollform( $info );
    }
}

/**
 * Forum Confirmation Message Box
 * Template File    templates/forum_postify.html
 */
if (!function_exists('render_postify')) {
    /**
     * @param $info
     */
    function render_postify($info) {
        echo ForumViewer::getInstance()->render_postify( $info );
    }
}

/**
 * Display the post reply form
 * Template File    templates/forms/post.html
 */
if (!function_exists("display_forum_postform")) {
    function display_forum_postform($info) {
        return ForumViewer::getInstance()->display_forum_postform( $info );
    }
}

/**
 * Display the bounty creation form
 * Template File        templates/forms/bounty.html
 */
if (!function_exists('display_forum_bountyform')) {
    function display_forum_bountyform($info) {
        return ForumViewer::getInstance()->display_forum_bountyform( $info );
    }
}

/**
 * Display The Tags and Threads
 * Template File        templates/tags/tags_threads.html
 */
if (!function_exists("display_forum_tags")) {
    function display_forum_tags($info) {
        return ForumViewer::getInstance()->display_forum_tags( $info );
    }
}

/**
 * Switch between different types of forum list containers
 * Template File    templates/index/forum_item.html
 */
if (!function_exists('forum_subforums_item')) {
    function forum_subforums_item($info) {
        return ForumViewer::getInstance()->forum_subforums_item( $info );
    }
}

/* Forum Filter */
if (!function_exists('forum_filter')) {
    function forum_filter($info) {
        return ForumViewer::getInstance()->forum_filter( $info );
    }
}

/**
 * Custom Modal New Topic
 * This is unused by the core but you can implement it.
 */
if (!function_exists('forum_newtopic')) {
    function forum_newtopic() {
        $locale = fusion_get_locale();
        if (isset($_POST['select_forum'])) {
            $_POST['forum_sel'] = isset($_POST['forum_sel']) && isnum($_POST['forum_sel']) ? $_POST['forum_sel'] : 0;
            redirect(FORUM.'post.php?action=newthread&forum_id='.$_POST['forum_sel']);
        }
        echo openmodal('newtopic', $locale['forum_0057'], ['button_id' => 'newtopic', 'class_dialog' => 'modal-md']);
        $index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
        $result = dbquery("SELECT a.forum_id, a.forum_name, b.forum_name as forum_cat_name, a.forum_post
         FROM ".DB_FORUMS." a
         LEFT JOIN ".DB_FORUMS." b ON a.forum_cat=b.forum_id
         WHERE ".groupaccess('a.forum_access')." ".(multilang_table("FO") ? "AND ".in_group('a.forum_language', LANGUAGE)." AND" : "AND")."
         (a.forum_type ='2' or a.forum_type='4') AND a.forum_post < ".USER_LEVEL_PUBLIC." AND a.forum_lock !='1' ORDER BY a.forum_cat ASC, a.forum_branch ASC, a.forum_name ASC");
        $options = [];
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $depth = get_depth($index, $data['forum_id']);
                if (checkgroup($data['forum_post'])) {
                    $options[$data['forum_id']] = str_repeat("&#8212;", $depth).$data['forum_name']." ".($data['forum_cat_name'] ? "(".$data['forum_cat_name'].")" : '');
                }
            }

            echo "<div class='well clearfix m-t-10'>";
            echo form_select('forum_sel', $locale['forum_0395'], '', [
                'options' => $options,
                'inline'  => 1,
                'width'   => '100%'
            ]);
            echo "<div class='display-inline-block col-xs-12 col-sm-offset-3'>";
            echo form_button('select_forum', $locale['forum_0396'], 'select_forum', ['class' => 'btn-primary btn-sm']);
            echo "</div>";
            echo "</div>";
            echo closeform();
        } else {
            echo "<div class='well text-center'>";
            echo $locale['forum_0328'];
            echo "</div>";
        }
        echo closemodal();
    }
}
