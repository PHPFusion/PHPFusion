<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/IgnitionPacks/DefaultPack/Templates/Home.inc
| Author: Frederick MC Chan (Chan)
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Atom9Theme\IgnitionPacks\DefaultPack\Templates;

use Atom9Theme\Core;

class Home extends Core {
    public static function HomePanel($info) {
        $locale = fusion_get_locale('', ATOM9_LOCALE);

        self::SetParam('left', FALSE);
        self::SetParam('right', FALSE);

        add_to_head('<link rel="stylesheet" href="'.IGNITION_PACK.'Templates/css/home.css"/>');

        $row = [];
        foreach ($info as $db_id => $content) {
            foreach ($content['data'] as $data) {
                $data['db_id'] = $db_id;
                $data['blockTitle'] = $content['blockTitle'];
                $data['norecord'] = $content['norecord'];
                $row[$data['datestamp']][] = $data;
            }
        }

        krsort($row);

        if (!empty($row)) {
            echo '<div class="row m-0 m-b-50">';
            foreach ($row as $time => $cdata) {
                foreach ($cdata as $data) {
                    $css = '';

                    switch ($data['db_id']) {
                        case DB_NEWS:
                            $css = 'news';
                            break;
                        case DB_ARTICLES:
                            $css = 'articles';
                            break;
                        case DB_BLOG:
                            $css = 'blogs';
                            break;
                        case DB_DOWNLOADS:
                            $css = 'downloads';
                            break;
                        default:
                            break;
                    }

                    echo '<div class="col-xs-12 col-sm-3 home_item '.$css.'">';
                        echo '<div class="home_content">';
                            if (!empty($data['image'])) {
                                echo '<div class="cat_img pull-right">'.thumbnail($data['image'], '60px').'</div>';
                            }
                            echo '<div class="heading">'.$data['blockTitle'].'</div>';
                            echo '<div class="subheading">'.ucfirst($locale['in']).' '.$data['cat'].'</div>';
                            echo '<h4><a href="'.$data['url'].'">'.$data['title'].'</a></h4>';
                            echo '<span>'.trim_text(strip_tags($data['content']), 150).'</span>';
                        echo '</div>';
                        echo '<div class="footer"><a class="btn btn-primary btn-sm" href="'.$data['url'].'">'.$locale['A9_101'].'</a></div>';
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            opentable();
            echo $locale['A9_102'];
            closetable();
        }
    }
}
