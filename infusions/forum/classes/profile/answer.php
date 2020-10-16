<?php
namespace PHPFusion\Infusions\Forum\Classes\Profile;

use PHPFusion\Infusions\Forum\Classes\ForumProfile;
use PHPFusion\Template;

/**
 * Class Answer
 *
 * @package PHPFusion\Infusions\Forum\Classes\Profile
 */
class Answer {

    private $profile_url = '';

    private $user_data = [];

    private $locale = [];

    private $class = NULL;

    private $nav_tabs = [];
    private $nav_active = 'latest';
    private $nav_sql = 'answer-latest';
    private $self_noun = '';

    /**
     * Summary constructor.
     * Lock implementation method
     *
     * @param ForumProfile $obj
     */
    public function __construct(ForumProfile $obj) {

        $this->profile_url = $obj->getProfileUrl().'ref=answers&amp;';
        $this->self_noun = $obj->self_noun;

        $type = get('type');
        $this->nav_tabs = [
            'votes'    => [
                'link'  => $this->profile_url.'type=votes',
                'title' => 'Votes',
                'sql'   => 'answer-votes'
            ],
            'activity' => [
                'link'  => $this->profile_url.'type=activity',
                'title' => 'Activity',
                'sql'   => 'answer-activity',
            ],
            'latest'   => [
                'link'  => $this->profile_url.'type=latest',
                'title' => 'All',
                'sql'   => 'answer-latest'
            ],
        ];

        if ($type && isset($this->nav_tabs[$type])) {
            $this->nav_active = $type;
            $this->nav_sql = $this->nav_tabs[$type]['sql'];
        }

        $this->profile_url = $this->profile_url.'type='.$this->nav_active.'&amp;';
        $this->user_data = $obj->getUserData();
        $this->locale = $obj->getLocale();
        $this->class = $obj;
    }


    public function displayProfile() {
        $locale = fusion_get_locale();

        $ctpl = Template::getInstance('uf-forum-summary');
        $ctpl->set_template(__DIR__.'/../../templates/profile/thread-post.html');

        $limit = 24;
        $this->class->setSQLResults($limit);

        $sql = $this->class->getSQL($this->nav_sql); // all types of posts.

        $rowstart = get('rowstart', FILTER_VALIDATE_INT);

        $max_count = dbrows(dbquery(str_replace('LIMIT 0,'.$limit, '', $sql)));

        $rowstart = $rowstart && $rowstart <= $max_count ? $rowstart : 0;

        $this->class->setSQLRowstart($rowstart);

        $sql = $this->class->getSQL($this->nav_sql);

        $i = 0;
        foreach ($this->nav_tabs as $key => $tabs) {
            $tabs['class'] = $this->nav_active == $key ? ' class="active"' : '';
            $ctpl->set_block('nav_tabs', $tabs);
            $i++;
        }

        $ctpl->set_tag('row_count', format_word($max_count, 'answer|answers'));

        $result = dbquery($sql);

        if ($row_count = dbrows($result)) {

            // the actual rows
            while ($data = dbarray($result)) {
                $data['thread_lastpost'] = showdate('shortdate', $data['thread_lastpost']);
                $data['post_datestamp'] = showdate('shortdate', $data['post_datestamp']);
                $data['thread_viewcount'] = format_word($data['thread_views'], 'time|times');
                $data['thread_subject'] = ucfirst($data['thread_subject']);
                $data['thread_link'] = FORUM.'viewthread.php?thread_id='.$data['thread_id'].'&amp;pid='.$data['post_id'];
                $data['post_link'] = FORUM.'viewthread.php?thread_id='.$data['thread_id'].'&amp;pid='.$data['post_id'].'#post_'.$data['post_id'];

                $ctpl->set_block('thread_item', $data);
            }
            if ($max_count > $row_count) {
                $ctpl->set_block('page_nav', ['nav' => makepagenav($rowstart, $limit, $max_count, 3, $this->profile_url)]);
            }
        } else {
            $ctpl->set_block('no_thread_item', ['message' => $this->self_noun.' have not answered any question.']);
        }

        return $ctpl->get_output();
    }


}
