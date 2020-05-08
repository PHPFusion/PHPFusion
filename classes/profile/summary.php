<?php
namespace PHPFusion\Infusions\Forum\Classes\Profile;

use PHPFusion\Infusions\Forum\Classes\ForumProfile;
use PHPFusion\Template;

/**
 * Class Summary
 *
 * @package PHPFusion\Infusions\Forum\Classes\Profile
 */
class Summary  {

    private $profile_url = '';

    private $user_data = [];

    private $locale = [];

    private $class = NULL;

    /**
     * Summary constructor.
     * Lock implementation method
     *
     * @param ForumProfile $obj
     */
    public function __construct(ForumProfile $obj) {
        $this->profile_url = $obj->getProfileUrl();
        $this->user_data = $obj->getUserData();
        $this->locale = $obj->getLocale();
        $this->class = $obj;
    }

    /**
     * Profile Summary
     * @return string
     */
    public function displayProfile() {

        $ctpl = Template::getInstance('uf-forum-summary');

        $ctpl->set_template(__DIR__.'/../../templates/profile/summary.html');

        $ctpl->set_tag('answers_link', $this->profile_url.'ref=answers');

        $ctpl->set_tag('questions_link', $this->profile_url.'ref=questions');

        $ctpl->set_tag('reputation_link', $this->profile_url.'ref=reputation');

        $ctpl->set_tag('tags_link', $this->profile_url.'ref=tags');

        // ANSWER PANEL
        $sql = $this->class->getSQL('answer-latest');
        $answers_result = dbquery($sql);
        $row_count = 0;
        if (dbrows($answers_result)) {

            $row_count = dbrows(dbquery(str_replace('LIMIT 6', '', $sql)));

            while ($data = dbarray($answers_result)) {
                $ctpl->set_block('answered_threads', [
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
            'votes' => ['title' => $this->locale['forum_ufp_110'], 'link' => 'answer-votes'],
            'activity' => ['title' => $this->locale['forum_ufp_111'], 'link' => 'answer-activity'],
            'latest' => ['title' => $this->locale['forum_ufp_112'], 'link' => 'answer-latest'],
        ];
        $i = 1;
        foreach($answer_nav as $id => $tab) {
            $tab['active'] = count($answer_nav) == $i ? ' class="active"' : '';
            $tab['id'] = $id;
            $ctpl->set_block('answer_nav', $tab);
            $i++;
        }

        // REPUTATION PANEL
        $sql = $this->class->getSQL('reputation-latest');
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
            'votes' => ['title' => $this->locale['forum_ufp_110'], 'link' => 'reputation-votes'],
            'activity' => ['title' => $this->locale['forum_ufp_111'], 'link' => 'reputation-activity'],
            'latest' => ['title' => $this->locale['forum_ufp_112'], 'link' => 'reputation-latest'],
        ];
        $i = 1;
        foreach($answer_nav as $id => $tab) {
            $tab['active'] = count($answer_nav) == $i ? ' class="active"' : '';
            $tab['id'] = $id;
            $ctpl->set_block('reputation_nav', $tab);
            $i++;
        }

        // QUESTIONS PANEL - ALL THE QUESTIONS THE USER HAS STARTED.
        $sql = $this->class->getSQL('question-latest');
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
            'votes' => ['title' => $this->locale['forum_ufp_110'], 'link' => 'question-votes'],
            'activity' => ['title' => $this->locale['forum_ufp_111'], 'link' => 'question-activity'],
            'latest' => ['title' => $this->locale['forum_ufp_112'], 'link' => 'question-latest'],
        ];
        $i = 1;
        foreach($answer_nav as $id => $tab) {
            $tab['active'] = count($answer_nav) == $i ? ' class="active"' : '';
            $tab['id'] = $id;
            $ctpl->set_block('question_nav', $tab);
            $i++;
        }

        // TAGS
        $sql = $this->class->getSQL('tags-latest');
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
                            $tag_total_thread_count[$tag_id] = dbcount("(thread_id)", DB_FORUM_THREADS, in_group('thread_tags', $tag_id, '.'));
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

        add_to_jquery("
        //init tabs trigger event
        forum_summary.answer_panel('answer-summary-nav', 'answer-summary-content', 'answer-count', '".$this->user_data['user_id']."');
        forum_summary.answer_panel('question-summary-nav', 'question-summary-content', 'question-count', '".$this->user_data['user_id']."');
        forum_summary.answer_panel('reputation-summary-nav', 'reputation-summary-content', 'reputation-count', '".$this->user_data['user_id']."');
        ");

        return (string)$ctpl->get_output();
    }

}
