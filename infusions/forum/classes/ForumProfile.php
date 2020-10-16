<?php
namespace PHPFusion\Infusions\Forum\Classes;

use PHPFusion\Infusions\Forum\Classes\Profile\Answer;
use PHPFusion\Infusions\Forum\Classes\Profile\Bounty;
use PHPFusion\Infusions\Forum\Classes\Profile\Questions;
use PHPFusion\Infusions\Forum\Classes\Profile\Reputation;
use PHPFusion\Infusions\Forum\Classes\Profile\Summary;
use PHPFusion\Infusions\Forum\Classes\Profile\Tags;
use PHPFusion\Infusions\Forum\Classes\Profile\Tracked;
use PHPFusion\Template;

class ForumProfile {

    private $user_data = [];

    private $profile_url = '';

    private $locale = [];

    private $SQL_results = 6;

    private $SQL_rowstart = 0;

    public $self_noun = '';

    public function __construct($lookup_id, $locale) {
        $this->locale = $locale;

        $this->user_data = fusion_get_user(intval($lookup_id ?: 0));

        $this->profile_url = BASEDIR.'profile.php?lookup='.$this->user_data['user_id'].'&amp;profile_page=forum&amp;';

        $this->self_noun = 'This user';
        if (fusion_get_userdata('user_id') == $this->user_data['user_id']) {
            $this->self_noun = 'You';
        }
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
            'tracked'     => ['title' => $this->locale['forum_ufp_104'], 'link' => $this->profile_url.'ref=tracked'],
            'bounties'   => ['title' => $this->locale['forum_ufp_105'], 'link' => $this->profile_url.'ref=bounties'],
            'reputation' => ['title' => $this->locale['forum_ufp_106'], 'link' => $this->profile_url.'ref=reputation'],
        ];
        return (array)$pages;
    }

    public function viewUserProfile() {

        add_to_footer( "<script src='".FORUM."templates/ajax/js/forum-profile.js'></script>" );

        $tpl = Template::getInstance( 'uf-forum' );

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
        $result = dbquery( "SELECT count(pn.post_id) 'notify_count', fm.mood_description, fm.mood_icon
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
                $profile = new Summary($this);
                $page_output = $profile->displayProfile();
                break;
            case 'answers':
                $profile = new Answer($this);
                $page_output = $profile->displayProfile();
                break;
            case 'questions':
                $profile = new Questions($this);
                $page_output = $profile->displayProfile();
                break;
            case 'tags':
                $profile = new Tags($this);
                $page_output = $profile->displayProfile();
                break;
            case 'tracked':
                $profile = new Tracked($this);
                $page_output = $profile->displayProfile();
                break;
            case 'bounties':
                $profile = new Bounty($this);
                $page_output = $profile->displayProfile();
                break;
            case 'reputation':
                $profile = new Reputation($this);
                $page_output = $profile->displayProfile();
                break;
        }

        $tpl->set_tag('forum_profile_content', $page_output);

        return $tpl->get_output();


    }

    /**
     * @return string
     */
    private function displayAnswerProfile() {
        $ctpl = Template::getInstance( 'uf-forum-answers' );
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
            case 'answer-latest': //answers latest
                // to find the first post of this thread, we need to use MIN post id of the thread id.
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, fp.post_id, fp.post_datestamp
                FROM ".DB_FORUM_POSTS." fp
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess( 'f.forum_access' )."
                WHERE (
                ## that the current post id which is fp.post_id IS NOT the first post of the current thread
                SELECT MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id=ft.thread_id
                ) !=fp.post_id AND fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id
                ORDER BY fp.post_datestamp DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'answer-activity':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, fp.post_id, fp.post_datestamp
                FROM ".DB_FORUM_POSTS." fp
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE (
                ## that the current post id which is fp.post_id IS NOT the first post of the current thread
                SELECT MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id=ft.thread_id
                ) !=fp.post_id AND fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0
                GROUP BY ft.thread_id ORDER BY ft.thread_lastpost DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'answer-votes':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, fp.post_id, fp.post_datestamp
                FROM ".DB_FORUM_POSTS." fp
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUM_VOTES." fv ON fv.thread_id=fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE (
                ## that the current post id which is fp.post_id IS NOT the first post of the current thread
                SELECT MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id=ft.thread_id
                ) !=fp.post_id AND fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fv.vote_datestamp DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'question-latest':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, fp.post_id, fp.post_datestamp
                FROM ".DB_FORUM_POSTS." fp
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE ft.thread_author='".$this->user_data['user_id']."' AND fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fp.post_datestamp DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results."
                ";
                break;
            case 'question-activity':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, fp.post_id, fp.post_datestamp
                FROM ".DB_FORUM_POSTS." fp
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4
                WHERE ft.thread_author='".$this->user_data['user_id']."' AND  fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0
                GROUP BY ft.thread_id ORDER BY ft.thread_lastpost DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'question-votes':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, fp.post_id, fp.post_datestamp
                FROM ".DB_FORUM_POSTS." fp
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id AND ft.thread_author='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUM_VOTES." fv ON fv.thread_id=ft.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND f.forum_type=4 AND ".groupaccess('f.forum_access')."
                WHERE ft.thread_author='".$this->user_data['user_id']."' AND  fp.post_author='".$this->user_data['user_id']."' AND fp.post_hidden=0 GROUP BY ft.thread_id ORDER BY fv.vote_datestamp DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'reputation-latest':
                $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost
                FROM ".DB_FORUM_USER_REP." frp
                INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."'
                GROUP BY frp.thread_id
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'reputation-activity':
                $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost
                FROM ".DB_FORUM_USER_REP." frp
                INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."'
                GROUP BY frp.thread_id ORDER BY ft.thread_lastpost DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'reputation-votes':
                $sql = "SELECT SUM(frpp.points_gain) 'thread_points', ft.thread_id, ft.thread_subject, ft.thread_views, fv.vote_datestamp, ft.thread_lastpost
                FROM ".DB_FORUM_USER_REP." frp
                INNER JOIN ".DB_FORUM_USER_REP." frpp ON frpp.thread_id=frp.thread_id AND frpp.user_id='".$this->user_data['user_id']."'
                INNER JOIN ".DB_FORUM_VOTES." fv ON fv.thread_id=frp.thread_id
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = frp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id = frp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."'
                GROUP BY frp.thread_id ORDER BY fv.vote_datestamp DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'tracked-latest':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, ft.thread_lastpostid
                FROM ".DB_FORUM_THREAD_NOTIFY." tn
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = tn.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=ft.forum_id AND ".groupaccess('f.forum_access')."
                WHERE tn.notify_user='".$this->user_data['user_id']."' AND tn.notify_status=1
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'tracked-activity':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_lastpost, ft.thread_lastpostid
                FROM ".DB_FORUM_THREAD_NOTIFY." tn
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = tn.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=ft.forum_id AND ".groupaccess('f.forum_access')."
                WHERE tn.notify_user='".$this->user_data['user_id']."' AND tn.notify_status=1 ORDER BY ft.thread_lastpost DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;

            case 'tags-latest':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags, ft.thread_lastpost
                FROM ".DB_FORUM_POSTS." fp
                INNER JOIN ".DB_FORUM_THREADS." ft ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=fp.forum_id AND ".groupaccess('f.forum_access')."
                WHERE fp.post_author='".$this->user_data['user_id']."' AND ft.thread_tags !='' AND fp.post_hidden=0 GROUP BY ft.thread_id
                ORDER BY fp.post_datestamp DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;

                break;
            case 'bounty-active':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags, ft.thread_lastpost
                FROM ".DB_FORUM_THREADS." ft
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=ft.forum_id AND ".groupaccess('f.forum_access')."
                WHERE ft.thread_bounty_user='".$this->user_data['user_id']."' AND ft.thread_answered = '0'
                GROUP BY ft.thread_id
                ORDER BY ft.thread_id DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'bounty-offered':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags, ft.thread_lastpost
                FROM ".DB_FORUM_THREADS." ft
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=ft.forum_id AND ".groupaccess('f.forum_access')."
                WHERE ft.thread_bounty_user='".$this->user_data['user_id']."' AND ft.thread_answered = '0'
                GROUP BY ft.thread_id
                ORDER BY ft.thread_bounty_start DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'bounty-earned':
                $sql = "SELECT ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags, ft.thread_lastpost
                FROM ".DB_FORUM_USER_REP." frp
                INNER JOIN  ".DB_FORUM_THREADS." ft ON frp.thread_id=ft.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=ft.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."' AND frp.rep_answer = '1'
                GROUP BY ft.thread_id
                ORDER BY frp.datestamp DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'reputation-post':
                // Sort by year, month, day in results
                $sql = "SELECT frp.rep_id,
                YEAR(from_unixtime(frp.datestamp)) 'rep_year', MONTH(from_unixtime(frp.datestamp)) 'rep_month', DAY(from_unixtime(frp.datestamp)) 'rep_day',
                frp.rep_type, frp.points_gain, ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags, ft.thread_lastpost, frp.datestamp,
                COUNT(fp.post_id) 'post_count',
                fp.post_datestamp, fp.post_id
                FROM ".DB_FORUM_USER_REP." frp
                INNER JOIN ".DB_FORUM_POSTS." fp ON fp.post_id=frp.post_id
                INNER JOIN  ".DB_FORUM_THREADS." ft ON frp.thread_id=ft.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=ft.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."' AND frp.rep_answer = '0'
                GROUP BY frp.rep_id
                ORDER BY rep_year DESC, rep_month DESC, rep_day DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
                break;
            case 'reputation-chart':
                // poll for 30 days of the current month
                $sql = "SELECT frp.rep_id,
                YEAR(from_unixtime(frp.datestamp)) 'rep_year', MONTH(from_unixtime(frp.datestamp)) 'rep_month', DAY(from_unixtime(frp.datestamp)) 'rep_day',
                frp.rep_type, frp.points_gain, ft.thread_id, ft.thread_subject, ft.thread_views, ft.thread_tags, ft.thread_lastpost, frp.datestamp,
                COUNT(fp.post_id) 'post_count',
                fp.post_datestamp, fp.post_id
                FROM ".DB_FORUM_USER_REP." frp
                INNER JOIN ".DB_FORUM_POSTS." fp ON fp.post_id=frp.post_id
                INNER JOIN  ".DB_FORUM_THREADS." ft ON frp.thread_id=ft.thread_id
                INNER JOIN ".DB_FORUMS." f ON f.forum_id=ft.forum_id AND ".groupaccess('f.forum_access')."
                WHERE frp.user_id='".$this->user_data['user_id']."' AND frp.rep_answer = '0' AND frp.datestamp > '".strtotime('last month')."' AND frp.datestamp < '".strtotime('next month')."'
                GROUP BY frp.rep_id
                ORDER BY rep_year DESC, rep_month DESC, rep_day DESC
                LIMIT ".$this->SQL_rowstart.",".$this->SQL_results;
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
     * @return array
     */
    public function getUserData() : array {
        return (array)$this->user_data;
    }

    /**
     * @return array
     */
    public function getLocale(): array {
        return (array)$this->locale;
    }

}
