<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

echo '<div class="container">';
echo '<div class="spacer-md">';
echo '<h4>Fusion Media Gallery Demo</h4>';

echo fusion_media( 'user_cover', 'Cover Image', '', [
    'folders' => [
        // User Library
        [
            'id'    => 'gallery',
            'path'  => INFUSIONS.'profile_home/media/gallery/'.fusion_get_userdata( 'user_id' ),
            'file'  => INFUSIONS.'profile_home/media/gallery/index.php',
            'title' => 'Gallery'
        ],
        // News Shared among Admins
        [
            'id'    => 'news',
            'path'  => INFUSIONS.'profile_home/media/news/',
            'file'  => INFUSIONS.'profile_home/media/news/index.php',
            'title' => 'News Stash'
        ]
    ],
    'class'   => 'btn-primary',
    'icon'    => 'fas fa-camera'
] );
echo '</div>';
echo '</div>';


require_once THEMES.'templates/footer.php';
