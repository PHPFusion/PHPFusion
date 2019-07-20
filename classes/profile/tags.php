<?php
namespace PHPFusion\Infusions\Forum\Classes\Profile;

use PHPFusion\Infusions\Forum\Classes\Forum_Profile;
use PHPFusion\Template;

/**
 * Class Tags
 *
 * @package PHPFusion\Infusions\Forum\Classes\Profile
 */
class Tags  {

    private $profile_url = '';

    private $user_data = [];

    private $locale = [];

    private $class = NULL;

    private $nav_tabs = [];

    private $nav_active = 'latest';

    private $nav_sql = 'question-latest';

    /**
     * Summary constructor.
     * Lock implementation method
     *
     * @param Forum_Profile $obj
     */
    public function __construct(Forum_Profile $obj) {

        $this->profile_url = $obj->getProfileUrl().'ref=questions&amp;';

        $this->profile_url = $this->profile_url.'type='.$this->nav_active.'&amp;';
        $this->user_data = $obj->getUserData();
        $this->locale = $obj->getLocale();
        $this->class = $obj;
    }


    public function displayProfile() {
        $locale = fusion_get_locale();

        $ctpl = Template::getInstance('uf-forum-summary');

        $ctpl->set_template(__DIR__.'/../../templates/profile/tags.html');

        $limit = 24;

        $this->class->setSQLResults($limit);

        $sql = $this->class->getSQL('tags-latest'); // all types of posts.

        $rowstart = get('rowstart', FILTER_VALIDATE_INT);

        $max_count = dbrows(dbquery(str_replace('LIMIT 0,'.$limit, '', $sql)));

        $rowstart = $rowstart && $rowstart <= $max_count ? $rowstart : 0;

        $this->class->setSQLRowstart($rowstart);

        $sql = $this->class->getSQL('tags-latest');

        $ctpl->set_tag('row_count', format_word($max_count, 'question|questions') );

        $result = dbquery($sql);

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

        $ctpl->set_tag('tag_count', format_word(0, 'tag|tags'));
        if (!empty($tags)) {
            // the sum of tags
            $ctpl->set_tag('tag_count', format_word( count($tags), 'tag|tags') );
            foreach ($tags as $tag_id => $tag_data) {
                // now push the info into template
                //http://php-fusion.test/infusions/forum/tags.php?tag_id=2
                $ctpl->set_block('tag_block', [
                    'thread_count' => number_format($tag_data['thread_count']),
                    'tag_title'    => $tag_data['tag_title'],
                    'tag_link'     => FORUM.'tags.php?tag_id='.$tag_id,
                    'tag_info'     => isset($tag_involve_count[$tag_id]) ? number_format($tag_involve_count[$tag_id]) : 0
                ]);
            }
        }

        return $ctpl->get_output();
    }


}