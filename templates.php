<?php
/**
 * Forum Page Control Layout and Viewer.
 * @param $info
 */
if (!function_exists('render_forum')) {
    function render_forum($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->render_forum($info);
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
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->viewforum($info);
    }
}

/**
 * Display The Forum Thread Page
 * Template File        templates/forum_threads.html
 */
if (!function_exists('render_thread')) {
    function render_thread($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->render_thread($info);
    }
}

/**
 * Display the Quick Reply Form
 * Template File        templates/forms/quick_reply.html
 */
if (!function_exists("display_quick_reply")) {
    function display_quick_reply($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->displayQuickReply($info);
    }
}

/**
 * Display the poll creation form
 * Template File    templates/forms/poll.html
 *
 * @param $info
 *
 * @return string
 */
if (!function_exists("display_forum_pollform")) {
    function display_forum_pollform($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->display_forum_pollform($info);
    }
}

/**
 * Forum Confirmation Message Box
 * Template File    templates/forum_postify.html
 */
if (!function_exists('render_postify')) {
    function render_postify($info) {
        echo \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->render_postify($info);
    }
}

/**
 * Display the post reply form
 * Template File    templates/forms/post.html
 */
if (!function_exists("display_forum_postform")) {
    function display_forum_postform($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->display_forum_postform($info);
    }
}

/**
 * Display the bounty creation form
 * Template File        templates/forms/bounty.html
 */
if (!function_exists('display_forum_bountyform')) {
    function display_forum_bountyform($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->display_forum_bountyform($info);
    }
}

/**
 * Display The Tags and Threads
 * Template File        templates/tags/tags_threads.html
 */
if (!function_exists("display_forum_tags")) {
    function display_forum_tags($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->display_forum_tags($info);
    }
}

/**
 * Switch between different types of forum list containers
 * Template File    templates/index/forum_item.html
 */
if (!function_exists('forum_subforums_item')) {
    function forum_subforums_item($info) {
        return \PHPFusion\Infusions\Forum\Classes\Forum_Viewer::getInstance()->forum_subforums_item($info);
    }
}

/* Forum Filter */
if (!function_exists('forum_filter')) {
    function forum_filter($info) {
        // Put into core views
        $locale = fusion_get_locale();
        // This one need to push to core.
        $selector = [
            'today'  => $locale['forum_0212'],
            '2days'  => $locale['forum_p002'],
            '1week'  => $locale['forum_p007'],
            '2week'  => $locale['forum_p014'],
            '1month' => $locale['forum_p030'],
            '2month' => $locale['forum_p060'],
            '3month' => $locale['forum_p090'],
            '6month' => $locale['forum_p180'],
            '1year'  => $locale['forum_3015']
        ];

        $selector3 = [
            'author'  => $locale['forum_0052'],
            'time'    => $locale['forum_0381'],
            'subject' => $locale['forum_0051'],
            'reply'   => $locale['forum_0054'],
            'view'    => $locale['forum_0053'],
        ];

        // how to stack it.
        $selector4 = [
            'descending' => $locale['forum_0230'],
            'ascending'  => $locale['forum_0231']
        ];
        // temporarily fix before moving to TPL
        ob_start();
        ?>
        <div class='clearfix'>
            <div class='pull-left'>
                <?php echo $locale['forum_0388']; ?>
                <div class='forum-filter dropdown'>
                    <button class='btn btn-sm <?php echo(isset($_GET['time']) ? "btn-default active" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                        <strong>
                            <?php echo(isset($_GET['time']) && in_array($_GET['time'], array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0211']) ?>
                        </strong>
                        <span class='caret m-l-5'></span>
                    </button>
                    <ul class='dropdown-menu'>
                        <?php
                        foreach ($info['filter']['time'] as $filter_locale => $filter_link) {
                            echo "<li><a href='".$filter_link."'>".$filter_locale."</a></li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class='pull-left'>
                <?php echo $locale['forum_0225'] ?>
                <div class='forum-filter dropdown'>
                    <button class='btn btn-sm <?php echo(isset($_GET['sort']) ? "btn-default active" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                        <strong><?php echo(isset($_GET['sort']) && in_array($_GET['sort'], array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0381']) ?></strong>
                        <span class='caret m-l-5'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        <?php
                        foreach ($info['filter']['sort'] as $filter_locale => $filter_link) {
                            echo "<li><a href='$filter_link'>$filter_locale</a></li>";
                        }
                        ?>
                    </ul>
                </div>
                <div class='forum-filter dropdown'>
                    <button class='btn btn-sm <?php echo(isset($_GET['order']) ? "btn-default active" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                        <strong>
                            <?php echo(isset($_GET['order']) && in_array($_GET['order'], array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0230']) ?>
                        </strong>
                        <span class='caret m-l-5'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        <?php
                        foreach ($info['filter']['order'] as $filter_locale => $filter_link) {
                            echo "<li><a href='$filter_link'>$filter_locale</a></li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <?php
            if (isset($_GET['time']) || isset($_GET['sort']) || isset($_GET['order'])) {
                $reset_url = clean_request('', ['time', 'sort', 'order'], FALSE);
                ?>
                <div class="pull-right">
                    <a href="<?php echo $reset_url ?>" class="btn btn-sm btn-default"><i class="fas fa-times-circle"></i> Reset</a>
                </div>
                <?php
            }
            ?>
        </div>
        <?php

        return ob_get_clean();
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
         WHERE ".groupaccess('a.forum_access')." ".(multilang_table("FO") ? "AND a.forum_language='".LANGUAGE."' AND" : "AND")."
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
