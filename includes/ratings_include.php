<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ratings_include.php
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
defined('IN_FUSION') || exit;

function showratings($rating_type, $rating_item_id, $rating_link) {

    $locale = fusion_get_locale("", LOCALE.LOCALESET."ratings.php");
    $userdata = fusion_get_userdata();
    $settings = \fusion_get_settings();

    if ($settings['ratings_enabled'] == "1") {
        if (iMEMBER) {
            $d_rating = dbarray(dbquery("SELECT rating_vote,rating_datestamp FROM ".DB_RATINGS." WHERE rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."' AND rating_user='".$userdata['user_id']."'"));
            if (isset($_POST['post_rating'])) {
                // Rate
                if (isnum($_POST['rating']) && $_POST['rating'] > 0 && $_POST['rating'] < 6 && !isset($d_rating['rating_vote'])) {
                    $result = dbquery("INSERT INTO ".DB_RATINGS." (rating_item_id, rating_type, rating_user, rating_vote, rating_datestamp, rating_ip, rating_ip_type) VALUES ('$rating_item_id', '$rating_type', '".$userdata['user_id']."', '".$_POST['rating']."', '".time()."', '".USER_IP."', '".USER_IP_TYPE."')");
                    if ($result) {
                        defender::unset_field_session();
                    }
                }
                redirect($rating_link);
            } else if (isset($_POST['remove_rating'])) {
                // Unrate
                $result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$rating_item_id' AND rating_type='$rating_type' AND rating_user='".$userdata['user_id']."'");
                if ($result) {
                    defender::unset_field_session();
                }
                redirect($rating_link);
            }
        }
        $ratings = [
            5 => $locale['r120'],
            4 => $locale['r121'],
            3 => $locale['r122'],
            2 => $locale['r123'],
            1 => $locale['r124']
        ];
        if (!iMEMBER) {
            $message = str_replace("[RATING_ACTION]", "<a href='".BASEDIR."login.php'>".$locale['login']."</a>", $locale['r104']);
            if (fusion_get_settings("enable_registration") == TRUE) {
                $message = str_replace("[RATING_ACTION]",
                    "<a href='".BASEDIR."login.php'>".$locale['login']."</a> ".$locale['or']." <a href='".BASEDIR."register.php'>".$locale['register']."</a>",
                    $locale['r104']);
            }
            echo "<div class='text-center'>".$message."</div>\n";
        } else if (!empty($d_rating['rating_vote'])) {
            echo "<div class='display-block'>\n";
            echo openform('removerating', 'post', $rating_link, ['class' => 'display-block text-center']);
            echo sprintf($locale['r105'], $ratings[$d_rating['rating_vote']], showdate("longdate", $d_rating['rating_datestamp']))."<br /><br />\n";
            echo form_button('remove_rating', $locale['r102'], $locale['r102'], ['class' => 'btn-default m-b-10', 'icon' => 'fa fa-times m-r-10']);
            echo closeform();
            echo "</div>\n";
        } else {
            echo "<div class='display-block'>\n";
            echo openform('postrating', 'post', $rating_link, ['notice' => 0, 'class' => 'm-b-20 text-center']);
            echo form_select('rating', $locale['r106'], '', [
                'options'              => $ratings,
                'inner_width'          => '200px',
                'width'                => '200px',
                'allowclear'           => TRUE,
                'display_search_count' => -1
            ]);
            echo form_button('post_rating', $locale['r103'], $locale['r103'], ['class' => 'btn-primary btn-block']);
            echo closeform();
            echo "</div>\n";
        }
        $rating_votes = dbarray(dbquery("
            SELECT
            SUM(IF(rating_vote='5', 1, 0)) as r120,
            SUM(IF(rating_vote='4', 1, 0)) as r121,
            SUM(IF(rating_vote='3', 1, 0)) as r122,
            SUM(IF(rating_vote='2', 1, 0)) as r123,
            SUM(IF(rating_vote='1', 1, 0)) as r124
            FROM ".DB_RATINGS." WHERE rating_type='".$rating_type."' and rating_item_id='".intval($rating_item_id)."'
        "));
        if (!empty($rating_votes)) {

            $rating_sum = dbcount("(rating_id)", DB_RATINGS, "rating_type='".$rating_type."' AND rating_item_id='".intval($rating_item_id)."'");

            echo "<div id='ratings' class='rating_container'>\n";

            foreach ($rating_votes as $key => $num) {
                $num = intval($num);
                $percentage = $rating_sum == 0 ? 0 : round((($num / $rating_sum) * 100), 1);

                echo progress_bar($percentage, $locale[$key]." ($num)", ['height' => '10px']);

            }
            echo "</div>\n";

        } else {

            echo "<div class='text-center'>".$locale['r101']."</div>\n";

        }
    }
}
