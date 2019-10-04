<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: social_media_include.php
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


/**
 * Return a list of social media sharing services where an url can be shared
 * Requires the loading of Font Awesome which can be enabled in theme settings.
 *
 * @return array $services with a list of services
 *
 * @since 9.10.00
 */
function get_social_media_sharing_services() {

    $locale = fusion_get_locale();

    $services = array(
        /* Gets the GET parameters wrong in the url, only SEO friendly urls. */
        'facebook' => array(
            'name' => 'Facebook',
            'icon' => 'fa-facebook-square',
            'url'  => 'https://www.facebook.com/sharer.php?u=',
        ),
        'twitter'  => array(
            'name' => 'Twitter',
            'icon' => 'fa-twitter-square',
            'url'  => 'https://twitter.com/intent/tweet?url=',
        ),
        'reddit'   => array(
            'name' => 'Reddit',
            'icon' => 'fa-reddit-square',
            'url'  => 'https://www.reddit.com/submit?url=',
        ),
        'vk'       => array(
            'name' => 'VK',
            'icon' => 'fa-vk',
            'url'  => 'https://vk.com/share.php?url=',
        ),
        /* Gets the GET parameters wrong in the url, only SEO friendly urls. */
        'whatsapp' => array(
            'name' => 'WhatsApp',
            'icon' => 'fa-whatsapp',
            'url'  => 'https://api.whatsapp.com/send?text=',
        ),
        'telegram' => array(
            'name' => 'Telegram',
            'icon' => 'fa-telegram',
            'url'  => 'https://telegram.me/share/url?url=',
        ),
        'linkedin' => array(
            'name' => 'LinkedIn',
            'icon' => 'fa-linkedin',
            'url'  => 'https://www.linkedin.com/shareArticle?mini=true&url=',
        ),
        'email'    => array(
            'name' => 'Email',
            'icon' => 'fa-envelope-square',
            'url'  => 'mailto:?Subject=' . $locale['social_001'] . '&Body=',
        ),
    );

    return $services;
}


/**
 * Return html for a list of social media sharing services where an url can be shared
 *
 * @param string $url path of the url that is being shared
 *
 * @return string $html with a list of services
 *
 * @since 9.10.00
 */
function get_social_media_sharing_html( $url ) {
    if ( strlen( strval( $url ) ) < 5 ) {
        return;
    }

    $locale = fusion_get_locale();

    $services = get_social_media_sharing_services();
    if ( ! is_array( $services ) || empty( $services ) ) {
        return;
    }

    $html = '
        <span class="social-media-sharing">';
    foreach ( $services as $service ) {
        $service_url = $service['url'] . $url;
        $html .= '
            <a class="social-sharing-icons" href="' . $service_url . '" target="_blank" rel="nofollow noopener noreferrer" title="' . $locale['social_002'] . $service['name'] . '"  />
                <i class="fa fa-2x ' . $service['icon'] . '"></i>
            </a>
            ';
    }
    $html .= '
        </span>';

    return $html;
}
