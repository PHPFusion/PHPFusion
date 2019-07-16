<?php
namespace PHPFusion\Infusions\Forum\Classes;

use PHPFusion\Infusions\Forum\Classes\Profile\Answer;
use PHPFusion\Infusions\Forum\Classes\Profile\Summary;

class Forum_Profile {

    private $user_data = [];

    private $profile_url = '';

    private $locale = [];

    private $SQL_results = 6;

    private $SQL_rowstart = 0;

    public function __construct($lookup_id, $locale) {
        $this->locale = $locale;

        $this->user_data = fusion_get_user(intval($lookup_id ?: 0));

        $this->profile_url = BASEDIR.'profile.php?lookup='.$this->user_data['user_id'].'&amp;profile_page=forum&amp;';
    }

    public function setSQLResults($limit = 6) {
        $this->SQL_results = $limit;
    }

    public function setSQLRowstart($value = 0) {
        $this->SQL_rowstart = $value;
    }

    private function profilePageLink() {
        $pages = [
            'summary'    => ['title' => $this->locale['forum_ufp_100'], 'link' => $this->profile_url.'ref=summary'],
            'answers'    => ['title' => $this->locale['forum_ufp_101'], 'link' => $this->profile_url.'ref=answers'],
            'questions'  => ['title' => $this->locale['forum_ufp_102'], 'link' => $this->profile_url.'ref=questions'],
            'tags'       => ['title' => $this->locale['forum_ufp_103'], 'link' => $this->profile_url.'ref=tags'],
            'tracks'     => ['title' => $this->locale['forum_ufp_104'], 'link' => $this->profile_url.'ref=tracks'],
            'bounties'   => ['title' => $this->locale['forum_ufp_105'], 'link' => $this->profile_url.'ref=bounties'],
            'reputation' => ['title' => $this->locale['forum_ufp_106'], 'link' => $this->profile_url.'ref=reputation'],
        ];
        return (array)$pages;
    }

    public function viewUserProfile() {

        $tpl = \PHPFusion\Template::getInstance('uf-forum');

        $tpl->set_template(__DIR__.'/../templates/forum-profile.html');

        $tpl->set_tag('reputation_count', number_format($this->user_data['user_reputation']));

        $tpl->set_tag('post_count', number_format($this->user_data['user_posts']));

        $total_thread_views = dbresult(dbquery("SELECT SUM(thread_views) 'total_views' FROM ".DB_FORUM_THREADS." WHERE thread_author=:uid", [':uid'=>$this->user_data['user_id']]),0);
        $tpl->set_tag('affect_count', format_num($total_thread_views)); // count the people who has viewed my threads.

        $edit_count = dbresult(dbquery("SELECT COUNT(post_id) 'total_posts' FROM ".DB_FORUM_POSTS." WHERE post_edituser=:uid", [':uid'=>$this->user_data['user_id']]),0);
        $tpl->set_tag('edit_count', number_format($edit_count));

        $vote_count = dbresult(dbquery("SELECT COUNT(rep_id) 'total_votes' FROM ".DB_FORUM_USER_REP." WHERE voter_id=:uid", [':uid'=>$this->user_data['user_id']]),0);
        $tpl->set_tag('vote_count', number_format($vote_count));

        // reactions
        $result = dbquery("SELECT count(pn.post_id) 'notify_count', fm.mood_description, fm.mood_icon 
        FROM ".DB_FORUM_POST_NOTIFY." pn
        INNER JOIN ".DB_FORUM_MOODS." fm ON fm.mood_id=pn.notify_mood_id AND fm.mood_status=1
        WHERE notify_sender=:uid GROUP BY fm.mood_id", [':uid'=>$this->user_data['user_id']]);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $description = fusion_parse_locale($data['mood_description']);
                $tpl->set_block('mood', [
                    'count' => number_format($data['notify_count']),
                    'title' => format_word($data['notify_count'], $description.' '.$this->locale['forum_ufp_113']."|".$description.' '.$this->locale['forum_ufp_114']),
                    'icon' => $data['mood_icon'] ? '<i class="'.$data['mood_icon'].' fa-fw m-r-10"></i>' : '',
                ]);
            }
        }

        $pages = $this->profilePageLink();
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
                $profile_summary = new Summary($this);
                $page_output = $profile_summary->displayProfile();
                break;
            case 'answers':
                $profile_answer = new Answer($this);
                $page_output = $profile_answer->displayProfile();
                break;
        }

        $tpl->set_tag('forum_profile_content', $page_output);

        echo $tpl->get_output();

        add_to_footer("<script src='".FORUM."templates/ajax/js/forum-profile.js'></script>");
    }

    /**
     * @return string
     */
    private function displayAnswerProfile() {
        $ctpl = \PHPFusion\Template::getInstance('uf-forum-answers');
        $ctpl->set_template(__DIR__.'/templates/profile/thread-post.html');

        return (string)$ctpl->get_output();
    }

    /**
     * @param $type
     *
     * @return string
     */
    public function getSQL($type) {
        switch($type) {
            case 'answer-latest':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE fp.post_answer=1 AND fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'answer-activity':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY ft.thread_lastpost DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'answer-votes':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views 
                FROM ".DB_FORUM_POSTS." fp 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUM_VOTES." fv ON fv.thread_id=fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fv.vote_datestamp DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'question-latest':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results."
                ";
                break;
            case 'question-activity':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views FROM ".DB_FORUM_POSTS." fp 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
                WHERE fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY ft.thread_lastpost DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'question-votes':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views 
                FROM ".DB_FORUM_POSTS." fp 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUM_VOTES." fv ON fv.thread_id=ft.thread_id 
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fv.vote_datestamp DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'reputation-latest':
                $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views 
                FROM ".DB_FORUM_USER_REP." frp 
                INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$this->user_data['user_id']."' 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."'
                GROUP BY frp.thread_id 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'reputation-activity':
                $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views 
                FROM ".DB_FORUM_USER_REP." frp 
                INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$this->user_data['user_id']."' 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."'
                GROUP BY frp.thread_id ORDER BY ft.thread_lastpost DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'reputation-votes':
                $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views, fv.vote_datestamp 
                FROM ".DB_FORUM_USER_REP." frp 
                INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$this->user_data['user_id']."' 
                INNER JOIN ".DB_FORUM_VOTES." fv ON fv.thread_id=frp.thread_id 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."'
                GROUP BY frp.thread_id ORDER BY fv.vote_datestamp DESC 
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'tags-latest':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags
                FROM ".DB_FORUM_POSTS." fp 
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE fp.post_author='".$this->user_data['user_id']."' AND ft.thread_tags !='' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC
                ";
                break;
            default:
                $sql = '';
        }
        return $sql;
    }

    /**
     * @return string
     */
    public function getProfileUrl(): string {
        return $this->profile_url;
    }

    /**
     * @return array|mixed
     */
    public function getUserData() {
        return $this->user_data;
    }

    /**
     * @return array
     */
    public function getLocale(): array {
        return $this->locale;
    }

}
