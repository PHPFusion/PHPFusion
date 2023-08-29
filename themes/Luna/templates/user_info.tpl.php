<?php

/**
 * Overrides user info panel template
 * @param array $info
 */

function display_user_info_panel( $info = [] ) {
    $locale = fusion_get_locale();
    $settings = fusion_get_settings();
    $path = INFUSIONS . 'user_info_panel/templates/styles.min.css';
    $filetime = filemtime( $path );
    add_to_head( '<link rel="stylesheet" href="' . $path . '?v=' . $filetime . '">' );

    if (iMEMBER) {
        openside( '', 'uip' );

        echo '<div class="p-header">';
        echo '<div class="cover"><img src="' . IMAGES . 'covers/no-cover.jpg" alt=""></div>';
        echo $info['user_avatar'];
        echo '</div>';

        echo '<div class="p-body">';
        echo '<h5>' . $info['user_name'] . '</h5>';
        echo '<p>' . $info['user_level'] . '</p>';
        echo(isset( $info['user_bio'] ) ? '<div>' . $info['user_bio'] . '</div>' : '');
        echo '</div>';

        if (!empty( $info['pm_progress'] )) {
            echo '<div class="user_pm_progressbar">' . $info['pm_progress'] . '</div>';
        }

        echo '<div class="p-stats">';
        if ($info['forum_exists'] && $info['show_reputation']) {
            echo '<div><h5>' . $info['userdata']['user_reputation'] . '</h5>' . fusion_get_locale( 'forum_0014', INFUSIONS . 'forum/locale/' . LOCALESET . 'forum.php' ) . '</div>';
            echo '<div class="divider"></div>';
        }
        echo '<div><h5>2.5k</h5>Followers</div>';
        echo '<div class="divider"></div>';
        echo '<div><h5>25k</h5>Following</div>';
        echo '</div>';

        if ($info['pm_msg_count'] > 0) {
            echo '<div class="user_pm_notice"><a href="' . $info['user_pm_link'] . '"><i class="fa fa-envelope-o"></i> ' . $info['user_pm_title'] . '</a></div>';
        }

        echo '<div class="p-links">';
        echo '<ul class="block">';
        echo '<li><a href="'.BASEDIR.$settings['opening_page'].'"><span>🏠</span>Feed</a>';
        echo '<li><a href="' . BASEDIR . 'messages.php"><span>📧</span>' . $locale['UM081'] . ' <i class="fa fa-envelope-o  fa-pull-right"></i></a></li>';
        echo '<li><a href=""><span>🌎</span>Connections</a>';
        echo '<li><a href=""><span>📰</span>Latest News</a>';
        if ($info['forum_exists'] && file_exists( INFUSIONS . 'forum_threads_list_panel/my_tracked_threads.php' )) {
            echo '<li><a href="' . INFUSIONS . 'forum_threads_list_panel/my_tracked_threads.php">' . $locale['UM088'] . '</a></li>';
        }
        echo '<li><a href="' . BASEDIR . 'members.php"><span>👤</span>' . $locale['UM082'] . ' <i class="fa fa-users fa-pull-right"></i></a></li>';
        echo '<li><a href=""><span>📆</span>Events</a>';
        echo '<li><a href=""><span>👥</span>Groups</a>';
        echo '<li><a href=""><span>🔔</span>Notifications</a>';

        if (!empty( $info['submissions'] )) {
            echo '<li>';
            echo '<a data-toggle="collapse" data-parent="#navigation-user" href="#uipcollapse" aria-expanded="false" aria-controls="#uipcollapse">' . $locale['UM089'] . ' <i class="fa fa-cloud-upload fa-pull-right"></i></a>';
            echo '<ul id="uipcollapse" class="panel-collapse collapse block">';
            foreach ($info['submissions'] as $modules) {
                echo '<li><a class="side p-l-15" href="' . $modules['link'] . '">' . $modules['title'] . '</a></li>';
            }
            echo '</ul>';
            echo '</li>';
        }
        if (iADMIN) {
            echo '<li><a href="' . ADMIN . 'index.php' . fusion_get_aidlink() . '&pagenum=0"><span>⚗</span>' . $locale['UM083'] . '</a></li>';
        }
        if ($info['login_session']) {
            echo '<li><a href="' . BASEDIR . 'index.php?logoff=' . $info['user_id'] . '">' . $locale['UM103'] . ' <i class="fa fa-sign-out fa-pull-right"></i></a></li>';
        }
        echo '<li><a href="' . BASEDIR . 'edit_profile.php"><span>⚙</span>' . $locale['UM080'] . '</a>';
        echo '</ul>';
        echo '</div>';

        echo '<div class="p-footer">';
        echo '<a href="' . BASEDIR . 'profile.php?lookup=' . $info['user_id'] . '">View Profile</a>';
        echo '</div>';

        //echo '<a class="btn btn-primary btn-block" href="' . BASEDIR . 'index.php?logout=yes">' . $locale['UM084'] . '</a>';
        closeside();
    } else {
        openside( $locale['global_100'] );
        echo $info['openform'];
        echo $info['login_name_field'];
        echo $info['login_pass_field'];
        echo $info['login_remember_field'];

        echo $info['login_submit'];
        echo $info['registration'];
        echo '<br>';
        echo $info['lostpassword'];
        echo $info['closeform'];
        closeside();
    }
}