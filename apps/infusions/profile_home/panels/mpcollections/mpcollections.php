<?php
declare(strict_types=1);
namespace PHPFusion\Infusions\Profile_Home\Panels\mpCollections;

use PHPFusion\Infusions\Marketplace\Classes\Marketplace;
use PHPFusion\Template;

class mpCollections {

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
     */
    public function viewPanel() {
        //https://php-fusion.test/edit_profile.php?ref=marketplace&sref=add
        $html = '<div class="panel panel-profile"><div class="text-center"><h4>Nothing to Show Here Yet</h4>
        <p>Once you collect something on the marketplace, they will appear here.</p>
		<a href="'.\MARKETPLACE.'" class="text-success small text-uppercase strong">Start Collecting</a></div></div>';

        if ($items = $this->checkItems()) {
            $itemtpl = Template::getInstance('item');
            $itemtpl->set_template(__DIR__.'/item.html');

            if (count($items) > 5) {
                // do slick JS.
                $this->loadSlick();
            }

            foreach ($items as $item_data) {
                $itemtpl->set_block('item', $item_data);
            }

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

            $result = dbquery("SELECT c.*, i.*, cc.cid, cc.title, cc.digital, sh.title 'shop_title',
            count(r.rating_id) 'rating_votes', sum(r.rating_vote) 'rating_sum'
            FROM ".DB_FM_COLLECTIONS." c
            INNER JOIN ".DB_FM_ITEMS." i ON c.collection_item_id=i.item_id
            INNER JOIN ".DB_FM_CATS." cc ON cc.cid=i.item_cid
            INNER JOIN ".DB_FM_SHOPS." sh ON sh.id=i.item_user
            ##LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id=i.item_id AND r.rating_type='MP'
            WHERE c.collection_user=:uid AND i.item_status=1 AND ".groupaccess('cc.access')." AND sh.status=0
            GROUP BY c.collection_item_id
            ORDER BY i.item_updated_datestamp DESC LIMIT 24", [
                ':uid' => (int)$this->data['user_id'],
            ]);

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
        slick(" $('.mp-item-content').slick({
           slidesToShow: 6,
           dots: false,
           infinite: true,
           slidesToScroll: 1
        });");
    }


}
