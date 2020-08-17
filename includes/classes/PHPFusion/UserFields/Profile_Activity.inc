<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Profile_Activity.inc
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\UserFields;

class Profile_Activity {

    private $user_data = [];
    private $profile_id = 0;
    private $pages = [];
    private $url = '';
    private $info = [];
    private $page_nav = '';

    public function __construct( $profile_id, $user_data ) {
        $this->profile_id = $profile_id;
        $this->user_data = $user_data;
        $this->url = BASEDIR.'profile.php?lookup='.$this->user_data['user_id'].'&amp;profile_page=activity';
        $this->getPages();

    }

    private function getPages() {
        // Check settings to see if it is enabled
        $this->pages = [
            // new friendship, broken friendship, joined groups, your submissions, your comments, your new stuff.
            'personal' => [ 'link' => $this->url.'&amp;section=personal', 'title' => 'Personal' ],

            // friends level - all updates.
            'friends'  => [ 'link' => $this->url.'&amp;section=friends', 'title' => 'Friends' ],

            // groups activity
            'groups'   => [ 'link' => $this->url.'&amp;section=groups', 'title' => 'Groups' ],

            // all your tracked notices
            'tracked'  => [ 'link' => $this->url.'&amp;section=tracked', 'title' => 'Tracked' ],

            // all your mention notices
            'mentions' => [ 'link' => $this->url.'&amp;section=mentions', 'title' => 'Mentions' ],
        ];
    }

    /**
     * Personal activity info
     *
     * @return array
     */
    private function getPersonalFeed() {

        $sql = [
            'personal' => [
                'sql'   => 'SELECT * FROM '.DB_USER_ACTIVITY.' WHERE action_user_id=:uid AND '.groupaccess( 'action_visibility' ).' ORDER BY action_datestamp DESC LIMIT :rowstart, :limiter',
                'count' => 'action_user_id=:uid AND '.groupaccess( 'action_visibility' ),
            ],
        ];

        $section = get( 'section' );
        if ( empty( $section ) ) {
            $section = 'personal';
        }
        if ( $section && isset( $sql[ $section ] ) ) {
            $max_rows = dbcount( '(action_id)', DB_USER_ACTIVITY, $sql[ $section ]['count'], [ ':uid' => $this->user_data['user_id'] ] );
            if ( $max_rows ) {

                $sql = $sql[ $section ]['sql'];

                $rowstart = get( 'rowstart', FILTER_VALIDATE_INT );

                $rowstart = $rowstart && $rowstart <= $max_rows ? $rowstart : 0;

                $limit = 3;

                $result = dbquery( $sql, [ ':uid' => $this->user_data['user_id'], ':rowstart' => $rowstart, ':limiter' => $limit ] );

                if ( $rows = dbrows( $result ) ) {

                    if ( $max_rows > $rows ) {
                        $this->page_nav = makepagenav( $rowstart, $limit, $max_rows, 3, $this->url.'&amp;' );
                    }

                    while ( $data = dbarray( $result ) ) {
                        $user = fusion_get_user( $this->user_data['user_id'] );
                        $activity_title = fusion_get_activity_title( $data );
                        $activity_content = fusion_get_activity_content( $data );
                        $avatar_size = '50px';
                        if ( $activity_content && $activity_title ) {
                            $avatar_size = '80px';
                        }
                        $this->info[ $data['action_id'] ] = [
                            'profile_link' => profile_link( $user['user_id'], $user['user_name'], $user['user_status'] ),
                            'title'        => $activity_title,
                            'content'      => $activity_content,
                            'avatar'       => display_avatar( $user, $avatar_size )
                        ];
                    }
                }
            }
        }


        return (array)$this->info;
    }


    public function showActivityProfile() {

        $this->getPersonalFeed();
        $info = [
            'items'   => $this->info,
            'pages'   => $this->pages,
            'section' => $this->getActivitySection(),
            'pagenav' => $this->page_nav,
        ];

        return display_profile_activity( $info );
    }

    public function getActivitySection() {
        $section = get( 'section' );
        return ( $section && isset( $this->pages[ $section ] ) ? $section : 'personal' );
    }
}
