<?php

// News upgrade file.
// Fix submissions for news gallery
$check_array = [
    'submit_id' => " ADD submit_id MEDIUMINT(11) NOT NULL DEFAULT '0' AFTER news_id",
];
foreach ($check_array as $key => $value) {
    if (!column_exists(DB_NEWS_IMAGES, $key)) {
        $inf_altertable[] = DB_NEWS_IMAGES.$value;
    }
}