<?php

const DB_LISTING = DB_PREFIX.'listing'; // the listing
const DB_LISTING_ATTR = DB_PREFIX.'listing_attr'; // the listing
const DB_LISTING_FORM = DB_PREFIX.'listing_form'; // the type of forms that is supported
const DB_LISTING_FIELDS = DB_PREFIX.'listing_fields'; // the listing field types
const DB_LISTING_CAT = DB_PREFIX.'listing_cat'; // the listing category
const DB_LISTING_TAGS = DB_PREFIX.'listing_tags'; // taxanomy tags

const IMAGES_DC = INFUSIONS.'assets/gallery/';

/* Registers the application end points */
require_once __DIR__.'/api_register.php';

function social_networks() {
    return [
        'facebook'   => 'Facebook',
        'twitter'    => 'Twitter',
        'instagram'  => 'Instagram',
        'youtube'    => 'YouTube',
        'snapchat'   => 'Snapchat',
        'tumblr'     => 'Tumblr',
        'reddit'     => 'Reddit',
        'linkedin'   => 'Linkedin',
        'pinterest'  => 'Pinterest',
        'deviantart' => 'DeviantArt',
        'vkontakte'  => 'VKontakte',
        'soundcloud' => 'SoundCloud',
        'website'    => 'Website',
        'other'      => 'Other'
    ];
}

function working_time() {
        static $time_array = [];
        if (empty($time_array)) {
            for ($h = 0; $h < 24; $h++) {
                $m_range = [0, 15, 30, 45];
                foreach ($m_range as $m) {
                    $mtime = date('h:i A', mktime($h, $m));
                    $ktime = date('H:i', mktime($h, $m));
                    $time_array[$ktime] = $mtime;
                }
            }
        }
        
        return $time_array;
    
}
