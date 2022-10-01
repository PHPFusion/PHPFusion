<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ratings_include.php
| Author: Core Development Team
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

/**
 * Display a ratings form.
 *
 * @param string $rating_type    The rating type you want to display.
 * @param int    $rating_item_id The item id you are rating.
 * @param string $rating_link    The link for the page which the rating is on.
 */
function showratings($rating_type, $rating_item_id, $rating_link) {
    $locale = fusion_get_locale("", LOCALE.LOCALESET."ratings.php");
    $userdata = fusion_get_userdata();
    $settings = \fusion_get_settings();

    if ($settings['ratings_enabled'] == "1") {
        if (iMEMBER) {
            $d_rating = dbarray(dbquery("SELECT rating_vote,rating_datestamp
                FROM ".DB_RATINGS."
                WHERE rating_item_id= :ratingitemid AND rating_type=:ratingtype AND rating_user=:ratinguser",
                [':ratingitemid' => $rating_item_id, ':ratingtype' => $rating_type, ':ratinguser' => $userdata['user_id']]
            ));
            if (check_post('post_rating')) {
                // Rate
                if (isnum(post('rating')) && post('rating') > 0 && post('rating') < 6 && !isset($d_rating['rating_vote'])) {
                	$saverating = [
                	    'rating_id'        => '',
                	    'rating_item_id'   => $rating_item_id,
                	    'rating_type'      => $rating_type,
                	    'rating_user'      => $userdata['user_id'],
                	    'rating_vote'      => post('rating'),
                	    'rating_datestamp' => time(),
                	    'rating_ip'        => USER_IP,
                	    'rating_ip_type'   => USER_IP_TYPE
                	];
                    $result = dbquery_insert(DB_RATINGS, $saverating, 'save');
                    if ($result) {
                        Defender::unset_field_session();
                    }
                }
                redirect($rating_link);
            } else if (check_post('remove_rating')) {
                // Unrate
                $result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$rating_item_id' AND rating_type='$rating_type' AND rating_user='".$userdata['user_id']."'");
                if ($result) {
                    Defender::unset_field_session();
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
        $rating_votes = dbarray(dbquery("SELECT
            SUM(IF(rating_vote='5', 1, 0)) AS r120,
            SUM(IF(rating_vote='4', 1, 0)) AS r121,
            SUM(IF(rating_vote='3', 1, 0)) AS r122,
            SUM(IF(rating_vote='2', 1, 0)) AS r123,
            SUM(IF(rating_vote='1', 1, 0)) AS r124
            FROM ".DB_RATINGS." WHERE rating_type=:ratingtype AND rating_item_id=:ratingitemid",
            [':ratingtype' => $rating_type, ':ratingitemid' => (int)$rating_item_id]
        ));
        if (!empty($rating_votes)) {

            $rating_sum = dbcount("(rating_id)", DB_RATINGS, "rating_type=:ratingtype AND rating_item_id=:ratingitemid", [':ratingtype' => $rating_type, ':ratingitemid' => (int)$rating_item_id]);

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
