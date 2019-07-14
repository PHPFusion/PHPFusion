<?php
/**
 * Babylon user profile forum extensions
 */
defined('IN_FUSION') || exit;

add_to_footer("<script src='".FORUM."templates/ajax/js/forum-profile.js'></script>");

$lookup_id = get('lookup', FILTER_VALIDATE_INT);
$user_data = fusion_get_user(intval($lookup_id ?: 0));

$tpl = \PHPFusion\Template::getInstance('uf-forum');

$tpl->set_template(__DIR__.'/templates/forum-profile.html');
$tpl->set_tag('reputation_count', format_num($user_data['user_reputation']));
$tpl->set_tag('post_count', format_num($user_data['user_posts']));
$tpl->set_tag('affect_count', '0');
$tpl->set_tag('edit_count', '0');
$tpl->set_tag('mood_count', '0');
$tpl->set_tag('vote_count', '0');

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
$profile_url = BASEDIR.'profile.php?lookup='.$user_data['user_id'].'&amp;profile_page=forum&amp;';
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

        // ANSWER PANEL
        $sql = "SELECT thread_subject, thread_views FROM ".DB_FORUM_POSTS." fp 
        INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
        INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id
        WHERE fp.post_answer=1 AND fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
        ";
        $answers_result = dbquery($sql);
        if ($rows = dbrows($answers_result)) {
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
        $ctpl->set_tag('answer_rowcount', number_format($rows,0));
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

        // QUESTIONS PANEL - ALL THE QUESTIONS THE USER HAS STARTED.
        $sql = "SELECT thread_subject, thread_views FROM ".DB_FORUM_POSTS." fp 
            INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$user_data['user_id']."'
            INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
            WHERE fp.post_author='".$user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY post_datestamp DESC
            ";
        $questions_result = dbquery($sql);
        if ($rows = dbrows($questions_result)) {
            while ($data = dbarray($questions_result)) {
                $ctpl->set_block('question_thread', [
                    'thread_views' => format_num($data['thread_views']),
                    'thread_subject' => $data['thread_subject'],
                    'thread_link' => FORUM.'viewthread.php?thread_id='.$data['thread_id'],
                ]);
            }
        } else {
            $ctpl->set_block('no_question_threads');
        }
        $ctpl->set_tag('question_rowcount', number_format($rows,0));
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
        break;
}

$tpl->set_tag('forum_profile_content', $ctpl->get_output());

add_to_jquery("
//init tabs trigger event
forum_summary.answer_panel('answer-summary-nav', 'answer-summary-content', 'answer-count', '".$user_data['user_id']."');
forum_summary.answer_panel('question-summary-nav', 'question-summary-content', 'question-count', '".$user_data['user_id']."');
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
