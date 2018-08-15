<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/classes/Templates/Home.inc
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
namespace Magazine\Templates;

use Magazine\Core;

class Home extends Core {
    public static function display_home($info) {
        foreach ($info as $db_id => $content) {
            $colwidth = $content['colwidth'];
            echo '<h2>'.$content['blockTitle'].'</h2>';
            if ($colwidth) {
                echo '<div class="row">';
                foreach ($content['data'] as $data) {
                    echo '<div class="col-xs-12 col-sm-'.$colwidth.' col-md-'.$colwidth.' col-lg-'.$colwidth.' content clearfix">';
                        echo '<div class="post-item">';

                            if (!empty($data['image'])) {
                                echo '<a href="'.$data['url'].'" class="thumb overflow-hide">';
                                    echo '<img class="img-responsive" src="'.$data['image'].'" alt="'.$data['title'].'"/>';
                                echo '</a>';
                            }

                            echo '<div class="post-meta">';
                                echo '<h4 class="title"><a href="'.$data['url'].'">'.$data['title'].'</a></h4>';
                                echo '<div class="small m-b-10 overflow-hide">'.$data['meta'].'</div>';
                                echo '<div class="overflow-hide hidden-xs">'.nl2br(trim_text(strip_tags($data['content']), 200)).'</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo $content['norecord'];
            }
        }
    }
}
