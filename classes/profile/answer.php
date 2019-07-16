<?php
namespace PHPFusion\Infusions\Forum\Classes\Profile;

use PHPFusion\Infusions\Forum\Classes\Forum_Profile;
use PHPFusion\Template;

/**
 * Class Summary
 *
 * @package PHPFusion\Infusions\Forum\Classes\Profile
 */
class Answer  {

    private $profile_url = '';

    private $user_data = [];

    private $locale = [];

    private $class = NULL;

    /**
     * Summary constructor.
     * Lock implementation method
     *
     * @param Forum_Profile $obj
     */
    public function __construct(Forum_Profile $obj) {
        $this->profile_url = $obj->getProfileUrl();
        $this->user_data = $obj->getUserData();
        $this->locale = $obj->getLocale();
        $this->class = $obj;
    }


    public function displayProfile() {

        $ctpl = Template::getInstance('uf-forum-summary');

        $ctpl->set_template(__DIR__.'/../../templates/profile/thread-post.html');

        $this->class->setSQLResults(30);
        $sql = $this->class->getSQL('question-latest');

        // Function this to rowstart
        $rowstart = get('rowstart', FILTER_VALIDATE_INT);
        $max_count = dbrows(dbquery(str_replace('LIMIT 0,30', '', $sql)));
        $rowstart = $rowstart && $rowstart <= $max_count ? $rowstart : 0;

        $this->class->setSQLRowstart($rowstart);

        $result = dbquery($sql);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                print_p($data);
                $ctpl->set_block('thread_item', $data);
            }
        }


        return $ctpl->get_output();

    }


}