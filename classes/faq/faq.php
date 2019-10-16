<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/faq/faq.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\FAQ;

use PHPFusion\BreadCrumbs;
use PHPFusion\SiteLinks;

/**
 * Class Faq
 *
 * @package PHPFusion\FAQ
 */
abstract class Faq extends FaqServer {
    private static $locale = [];
    public $info = [];

    /**
     * Executes main page information
     *
     * @param int $category
     *
     * @return array
     */
    public function set_FaqInfo($category = 0) {
        self::$locale = fusion_get_locale("", FAQ_LOCALE);

        set_title(SiteLinks::get_current_SiteLinks('infusions/faq/faq.php', 'link_name'));

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'faq/faq.php',
            'title' => SiteLinks::get_current_SiteLinks('', 'link_name')
        ]);

        $info = [
            'faq_categories' => [],
            'faq_items'      => [],
            'faq_tablename'  => self::$locale['faq_0000'],
            'faq_get'        => 0
        ];

        $info = array_merge($info, self::get_FaqData($category));

        if ($this->catid && isset($info['faq_categories'][$this->catid])) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'faq/faq.php', 'link_name'));
            add_to_title(self::$locale['global_201'].$info['faq_categories'][$this->catid]['faq_cat_name']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'faq/faq.php?cat_id='.$this->catid,
                'title' => $info['faq_categories'][$this->catid]['faq_cat_name']
            ]);
        }

        $this->info = $info;

        return (array)$info;
    }

    /**
     * Outputs category variables
     *
     * @param int $cat
     *
     * @return array
     */
    protected static function get_FaqData($cat = 0) {
        $info = [
            'faq_items'      => [],
            'cat_locale'     => self::$locale['faq_0001'],
            'cat_top'        => self::$locale['faq_0002'],
            'faq_get_name'   => '',
            'faq_categories' => [],
        ];

        $c_result = dbquery("SELECT fc.*, count(fq.faq_id) 'faq_count'
            FROM ".DB_FAQ_CATS." fc
            LEFT JOIN ".DB_FAQS." fq using (faq_cat_id)
            ".(multilang_table("FQ") ? "WHERE ".in_group('faq_cat_language', LANGUAGE) : "")."
            GROUP BY fc.faq_cat_id
            ORDER BY faq_cat_id ASC
        ");

        if (dbrows($c_result)) {
            while ($c_data = dbarray($c_result)) {
                $info['faq_categories'][$c_data['faq_cat_id']] = $c_data;
                $info['faq_categories'][$c_data['faq_cat_id']]['faq_cat_link'] = INFUSIONS."faq/faq.php?cat_id=".$c_data['faq_cat_id'];

                $info['faq_get'] = $cat;

                if (!empty($info['faq_categories'][$info['faq_get']]['faq_cat_name'])) {
                    $info['faq_get_name'] = $info['faq_categories'][$info['faq_get']]['faq_cat_name'];
                }
            }
        }

        // Get Items
        $result = dbquery("SELECT fq.*,
            fu.user_id, fu.user_name, fu.user_status, fu.user_avatar, fu.user_level, fu.user_joined
            FROM ".DB_FAQS." fq
            LEFT JOIN ".DB_USERS." AS fu ON fq.faq_name=fu.user_id
            WHERE fq.faq_status='1' AND ".groupaccess("fq.faq_visibility").
            (multilang_table('FQ') ? " AND ".in_group('fq.faq_language', LANGUAGE) : '').($cat ? " AND fq.faq_cat_id='$cat'" : ' AND fq.faq_cat_id=0')."
            GROUP BY fq.faq_id ORDER BY fq.faq_cat_id ASC, fq.faq_id ASC
        ");

        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $data['faq_answer'] = parse_textarea($data['faq_answer'], FALSE, FALSE, TRUE, FALSE, $data['faq_breaks'] == 'y' ? TRUE : FALSE);
                $info['faq_items'][$data['faq_id']] = $data;
                $info['faq_items'][$data['faq_id']]['print']['title'] = self::$locale['print'];
                $info['faq_items'][$data['faq_id']]['print']['link'] = BASEDIR."print.php?type=FQ&amp;item_id=".$data['faq_id'];
                $info['faq_items'][$data['faq_id']]['edit']['title'] = (iADMIN && checkrights("FQ")) ? self::$locale['edit'] : '';
                $info['faq_items'][$data['faq_id']]['edit']['link'] = (iADMIN && checkrights("FQ")) ? INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=faq&amp;ref=faq_form&amp;action=edit&amp;cat_id=".$data['faq_cat_id']."&amp;faq_id=".$data['faq_id'] : '';
                $info['faq_items'][$data['faq_id']]['delete']['title'] = (iADMIN && checkrights("FQ")) ? self::$locale['delete'] : '';
                $info['faq_items'][$data['faq_id']]['delete']['link'] = (iADMIN && checkrights("FQ")) ? INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=faq&amp;ref=faq_form&amp;action=delete&amp;faq_id=".$data['faq_id'] : '';
            }
        }

        return (array)$info;
    }

    protected function __clone() {
    }
}
