<?php
declare(strict_types=1);
namespace PHPFusion\Infusions\Profile_Home\Panels\mpItems;

use PHPFusion\Infusions\Marketplace\Classes\Marketplace;
use PHPFusion\Template;

class mpItems {

    private static $item = [];
    private static $item_checked = FALSE;
    private $data = [];
    private $store_id = 0;

    /**
     * mpItems constructor.
     *
     * @param $data
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function viewPanel() {
        //https://php-fusion.test/edit_profile.php?ref=marketplace&sref=add
        $html = '<div class="panel panel-profile"><div class="text-center"><h4>Nothing to Show Here Yet</h4>
		<p>Once you submit a marketplace item, they will appear here.</p>
		<a href="'.BASEDIR.'edit_profile.php?ref=marketplace&amp;sref=add" class="text-success small text-uppercase strong">Submit</a></div></div>';

        if ($items = $this->checkItems()) {
            $itemtpl = Template::getInstance('item');

            if (count($items) > 5) {
                // do slick JS.
                $this->loadSlick();

            }

            foreach ($items as $item_data) {
                // we view it out.
                $itemtpl->set_template(__DIR__.'/item.html');
                $itemtpl->set_block('item', $item_data);
            }
            $itemtpl->set_block('all', ['link' => \MARKETPLACE.'?view=stores&amp;sid='.$this->store_id]);

            $html = $itemtpl->get_output();
        }

        return $html;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function checkItems() {

        $this->store_id = Marketplace::getInstance()->getStoreUID($this->data['user_id']);

        if ($this->store_id && empty(self::$item) && !self::$item_checked) {

            $result = dbquery("SELECT i.*, cc.*, sh.title 'shop_title',
            count(r.rating_id) 'rating_votes', sum(r.rating_vote) 'rating_sum'
            FROM ".DB_FM_ITEMS." i
            INNER JOIN ".DB_FM_CATS." cc ON cc.cid=i.item_cid
            INNER JOIN ".DB_FM_SHOPS." sh ON sh.id=i.item_user
            LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id=i.item_id AND r.rating_type='MP'
            WHERE item_status=1 AND ".groupaccess("cc.access")." AND sh.status=0
            AND item_user=:sid
            GROUP BY i.item_id
            ORDER BY item_updated_datestamp DESC LIMIT 0,12", [':sid' => (int)$this->store_id]);
            if (dbrows($result)) {
                add_to_footer("<script src='".INFUSIONS."marketplace/templates/js/marketplace_wish.js'></script>");
                while ($data = dbarray($result)) {
                    self::$item[$data['item_id']] = Marketplace::getInstance()->getAddons($data);;
                }
            }
        }

        return self::$item;
    }

    /**
     * Add Slick into the Template
     */
    private function loadSlick() {
        include THEME.'themefactory/lib/js/slick/slick_include.php';
        // language=JavaScript
        $js = "
        $('.mp-item-content').slick({
           slidesToShow: 6,
           dots: false,
           infinite: true,
           slidesToScroll: 1
        });";

        slick($js);
    }
}
