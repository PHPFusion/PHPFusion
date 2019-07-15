<?php
/**
 * Babylon user profile forum extensions
 */
defined('IN_FUSION') || exit;

add_to_footer("<script src='".FORUM."templates/ajax/js/forum-profile.js'></script>");

$lookup_id = get('lookup', FILTER_VALIDATE_INT);

$user_data = fusion_get_user(intval($lookup_id ?: 0));

$profile_url = BASEDIR.'profile.php?lookup='.$user_data['user_id'].'&amp;profile_page=forum&amp;';

$tpl = \PHPFusion\Template::getInstance('uf-forum');

$tpl->set_template(__DIR__.'/templates/forum-profile.html');

$tpl->set_tag('reputation_count', number_format($user_data['user_reputation']));

$tpl->set_tag('post_count', number_format($user_data['user_posts']));

$total_thread_views = dbresult(dbquery("SELECT SUM(thread_views) 'total_views' FROM ".DB_FORUM_THREADS." WHERE thread_author=:uid", [':uid'=>$user_data['user_id']]),0);
$tpl->set_tag('affect_count', format_num($total_thread_views)); // count the people who has viewed my threads.

$edit_count = dbresult(dbquery("SELECT COUNT(post_id) 'total_posts' FROM ".DB_FORUM_POSTS." WHERE post_edituser=:uid", [':uid'=>$user_data['user_id']]),0);
$tpl->set_tag('edit_count', number_format($edit_count));

$vote_count = dbresult(dbquery("SELECT COUNT(rep_id) 'total_votes' FROM ".DB_FORUM_USER_REP." WHERE voter_id=:uid", [':uid'=>$user_data['user_id']]),0);
$tpl->set_tag('vote_count', number_format($vote_count));

// reactions
$result = dbquery("SELECT count(pn.post_id) 'notify_count', fm.mood_description, fm.mood_icon 
FROM ".DB_FORUM_POST_NOTIFY." pn
INNER JOIN ".DB_FORUM_MOODS." fm ON fm.mood_id=pn.notify_mood_id AND fm.mood_status=1
WHERE notify_sender=:uid GROUP BY fm.mood_id", [':uid'=>$user_data['user_id']]);
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $tpl->set_block('mood', [
            'count' => number_format($data['notify_count']),
            'title' => fusion_parse_locale($data['mood_description']).' post',
            'icon' => $data['mood_icon'] ? '<i class="'.$data['mood_icon'].' fa-fw m-r-10"></i>' : '',
        ]);
    }
}




$locale['forum_ufp_100'] = 'Summary';
$locale['forum_ufp_101'] = 'Answers';
$locale['forum_ufp_102'] = 'Questions';
$locale['forum_ufp_103'] = 'Tags';
$locale['forum_ufp_104'] = 'Tracked';
$locale['forum_ufp_105'] = 'Bounties';
$locale['forum_ufp_106'] = 'Reputation';
$locale['forum_ufp_110'] = 'Votes';
$locale['forum_ufp_111'] = 'Activity';
$locale['forum_ufp_112'] = 'Latest';

// the pages to count
//http://php-fusion.test/profile.php?lookup=1&profile_page=forum

$pages = [
    'summary'    => ['title' => $locale['forum_ufp_100'], 'link' => $profile_url.'ref=summary'],
    'answers'    => ['title' => $locale['forum_ufp_101'], 'link' => $profile_url.'ref=answers'],
    'questions'  => ['title' => $locale['forum_ufp_102'], 'link' => $profile_url.'ref=questions'],
    'tags'       => ['title' => $locale['forum_ufp_103'], 'link' => $profile_url.'ref=tags'],
    'tracks'     => ['title' => $locale['forum_ufp_104'], 'link' => $profile_url.'ref=tracks'],
    'bounties'   => ['title' => $locale['forum_ufp_105'], 'link' => $profile_url.'ref=bounties'],
    'reputation' => ['title' => $locale['forum_ufp_106'], 'link' => $profile_url.'ref=reputation'],
];
$ref_link = get('ref');
$ref_link = $ref_link && isset($pages[$ref_link]) ? $ref_link : 'summary';
$i = 0;
foreach ($pages as $id => $page) {
    $page['active'] = ($ref_link == $id || !$ref_link && $i = 0) ? ' class="active"' : '';
    $page['id'] = $id;

    $tpl->set_block('navs', $page);
    $i++;
}
switch($ref_link) {
    default:
    case 'summary':
        $ctpl = \PHPFusion\Template::getInstance('uf-forum-summary');
        $ctpl->set_template(__DIR__.'/templates/profile/summary.html');
        $ctpl->set_tag('answers_link', $profile_url.'ref=answers');
        $ctpl->set_tag('questions_link', $profile_url.'ref=questions');
        $ctpl->set_tag('reputation_link', $profile_url.'ref=reputation');
        $ctpl->set_tag('tags_link', $profile_url.'ref=tags');

        // ANSWER PANEL
        $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views 
        FROM ".DB_FORUM_POSTS." fp 
        INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
        INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
        WHERE fp.post_answer=1 AND fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC LIMIT 6
        ";
        $answers_result = dbquery($sql);
        $row_count = 0;
        if (dbrows($answers_result)) {
            $row_count = dbrows(dbquery(str_replace('LIMIT 6', '', $sql)));
            while ($data = dbarray($answers_result)) {
                $ctpl->set_block('answer_threads', [
                    'thread_views' => format_num($data['thread_views']),
                    'thread_subject' => $data['thread_subject'],
                    'thread_link' => FORUM.'viewthread.php?thread_id='.$data['thread_id'],
                ]);
            }
        } else {
            $ctpl->set_block('no_answer_threads');
        }
        $ctpl->set_tag('answer_rowcount', number_format($row_count,0));
        $ctpl->set_tag('answer_trigger', 'answer-summary-nav');
        $ctpl->set_tag('answer_target', 'answer-summary-content');
        $ctpl->set_tag('answer_count', 'answer-count');
        $answer_nav = [
            'votes' => ['title' => $locale['forum_ufp_110'], 'link' => 'answer-votes'],
            'activity' => ['title' => $locale['forum_ufp_111'], 'link' => 'answer-activity'],
            'latest' => ['title' => $locale['forum_ufp_112'], 'link' => 'answer-latest'],
        ];
        $i = 1;
        foreach($answer_nav as $id => $tab) {
            $tab['active'] = count($answer_nav) == $i ? ' class="active"' : '';
            $tab['id'] = $id;
            $ctpl->set_block('answer_nav', $tab);
            $i++;
        }

        // REPUTATION PANEL
        // read into the reputation table
        $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views 
        FROM ".DB_FORUM_USER_REP." frp 
        INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$user_data['user_id']."' 
        INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
        INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
        WHERE frp.user_id='".$user_data['user_id']."'
        GROUP BY frp.thread_id LIMIT 6
        ";
        $result = dbquery($sql);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $row_count = dbrows(dbquery(str_replace('LIMIT 6', '', $sql)));
                $ctpl->set_block('reputation_threads', [
                    'thread_views' => $data['thread_points'] > 0 ? '+'.$data['thread_points'] : $data['thread_points'],
                    'thread_subject' => $data['thread_subject'],
                    'thread_link' => FORUM.'viewthread.php?thread_id='.$data['thread_id'],
                ]);
            }
        } else {
            $ctpl->set_block('no_reputation_threads');
        }
        $ctpl->set_tag('reputation_rowcount', number_format($row_count,0));
        $ctpl->set_tag('reputation_trigger', 'reputation-summary-nav');
        $ctpl->set_tag('reputation_target', 'reputation-summary-content');
        $ctpl->set_tag('reputation_count', 'reputation-count');
        $answer_nav = [
            'votes' => ['title' => $locale['forum_ufp_110'], 'link' => 'reputation-votes'],
            'activity' => ['title' => $locale['forum_ufp_111'], 'link' => 'reputation-activity'],
            'latest' => ['title' => $locale['forum_ufp_112'], 'link' => 'reputation-latest'],
        ];
        $i = 1;
        foreach($answer_nav as $id => $tab) {
            $tab['active'] = count($answer_nav) == $i ? ' class="active"' : '';
            $tab['id'] = $id;
            $ctpl->set_block('reputation_nav', $tab);
            $i++;
        }

        // QUESTIONS PANEL - ALL THE QUESTIONS THE USER HAS STARTED.
        $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC LIMIT 6
            ";
        $questions_result = dbquery($sql);
        $row_count =0;
        if (dbrows($questions_result)) {
            $row_count = dbrows(dbquery(str_replace('LIMIT 6', '', $sql)));
            while ($data = dbarray($questions_result)) {
                $ctpl->set_block('question_threads', [
                    'thread_views' => format_num($data['thread_views']),
                    'thread_subject' => $data['thread_subject'],
                    'thread_link' => FORUM.'viewthread.php?thread_id='.$data['thread_id'],
                ]);
            }
        } else {
            $ctpl->set_block('no_question_threads');
        }
        $ctpl->set_tag('question_rowcount', number_format($row_count,0));
        $ctpl->set_tag('question_trigger', 'question-summary-nav');
        $ctpl->set_tag('question_target', 'question-summary-content');
        $ctpl->set_tag('question_count', 'question-count');
        $answer_nav = [
            'votes' => ['title' => $locale['forum_ufp_110'], 'link' => 'question-votes'],
            'activity' => ['title' => $locale['forum_ufp_111'], 'link' => 'question-activity'],
            'latest' => ['title' => $locale['forum_ufp_112'], 'link' => 'question-latest'],
        ];
        $i = 1;
        foreach($answer_nav as $id => $tab) {
            $tab['active'] = count($answer_nav) == $i ? ' class="active"' : '';
            $tab['id'] = $id;
            $ctpl->set_block('question_nav', $tab);
            $i++;
        }

        // TAGS
        $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags
            FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND ".groupaccess('f.forum_access')."
            WHERE fp.post_author='".$user_data['user_id']."' AND ft.thread_tags !='' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC
            ";
        $result = dbquery($sql);
        if ($row_count = dbrows($result)) { // number of threads found from user involvement.
            $tag_total_thread_count = [];
            $tag_involve_count = [];
            $tags = [];
            while ($data = dbarray($result)) {
                $thread_tags = explode('.', $data['thread_tags']);
                if (!empty($thread_tags)) {
                    foreach($thread_tags as $tag_id) {
                        // fetch the information about this tag
                        $tag_involve_count[$tag_id] = isset($tag_involve_count[$tag_id]) ? $tag_involve_count[$tag_id] + 1 : 1;
                        // get the tag info once.
                        if (!isset($tag_total_thread_count[$tag_id])) {
                            // count the total threads with this tag.
                            $tag_total_thread_count[$tag_id] = dbcount("(thread_id)", DB_FORUM_THREADS, in_group('thread_tags', $tag_id));
                            // fetch the tag data
                            $t_result = dbquery("SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_id=:tid AND tag_status=1", [':tid'=>$tag_id]);
                            if (dbrows($t_result)) {
                                $t_data = dbarray($t_result);
                                $t_data['thread_count'] = $tag_total_thread_count[$t_data['tag_id']];
                                $tags[$t_data['tag_id']] = $t_data;
                            }
                        }

                    }
                }
            }

            $ctpl->set_tag('tag_count', 0);

            if (!empty($tags)) {
                // the sum of tags
                $ctpl->set_tag('tag_count', number_format(count($tags)));
                foreach($tags as $tag_id => $tag_data) {
                    // now push the info into template
                    //http://php-fusion.test/infusions/forum/tags.php?tag_id=2
                    $ctpl->set_block('tag_block', [
                        'thread_count' => number_format($tag_data['thread_count']),
                        'tag_title' => $tag_data['tag_title'],
                        'tag_link' => FORUM.'tags.php?tag_id='.$tag_id,
                        'tag_info' => isset($tag_involve_count[$tag_id]) ? number_format($tag_involve_count[$tag_id]) : 0
                    ]);
                }
            } else {
                $ctpl->set_block('no_tags');
            }


        }

        break;
}

$tpl->set_tag('forum_profile_content', $ctpl->get_output());

add_to_jquery("
//init tabs trigger event
forum_summary.answer_panel('answer-summary-nav', 'answer-summary-content', 'answer-count', '".$user_data['user_id']."');
forum_summary.answer_panel('question-summary-nav', 'question-summary-content', 'question-count', '".$user_data['user_id']."');
forum_summary.answer_panel('reputation-summary-nav', 'reputation-summary-content', 'reputation-count', '".$user_data['user_id']."');
");


// first get the content running
// summary link


//affect_count
// first find my posts.

// find the people that has viewed your participated discussions once you have a accepted as answer.


// Appreciators

$result = dbquery("SELECT notify_sender FROM ".DB_FORUM_POST_NOTIFY." WHERE notify_user=:uid GROUP BY notify_sender", [':uid' => $user_data['user_id']]);
if (dbrows($result)) {
    // unique sender
    while ($data = dbarray($result)) {

    }
}

//print_P($user_data);


echo $tpl->get_output();
