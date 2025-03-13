<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: UserGroups.php
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
namespace PHPFusion;

/**
 * Class UserGroups
 *
 * @package PHPFusion
 */
class UserGroups {
    private static $instance = NULL;

    private $info = [
        'total_rows'    => 0,
        'rows'          => 0,
        'group_members' => [],
        'group_pagenav' => ''
    ];

    /**
     * Get the UserGroups Instance
     *
     * @return null|static
     */
    public static function getInstance() {
        if ( self::$instance === NULL ) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Fetch group information
     *
     * @param int $group_id
     *
     * @return array
     */
    protected function setGroupInfo( $group_id ) {

        $result = dbquery( "SELECT * FROM " . DB_USER_GROUPS . " WHERE group_id = :groupid", [':groupid' => (int)$group_id] );

        if ( dbrows( $result ) ) {
            $members = [];
            $members_per_page = 20;
            $data = dbarray( $result );

            set_title( $data['group_name'] );

            $rows = dbcount( "(user_id)", DB_USERS,
                ( iADMIN ? "user_status>='0'" : "user_status='0'") . " AND user_groups REGEXP('^\\\.$group_id$|\\\.$group_id\\\.|\\\.$group_id$')"
            );

            $rowstart = get_rowstart( "rowstart", $rows );

            $members_result = dbquery( "SELECT user_id, user_name, user_level, user_status, user_language, user_joined, user_avatar
                FROM " . DB_USERS . " WHERE " . ( iADMIN ? "user_status >= '0'" : "user_status = '0'" ) . "
                AND user_groups REGEXP('^\\\.$group_id$|\\\.$group_id\\\.|\\\.$group_id$')
                ORDER BY user_level DESC, user_name ASC
                LIMIT " . $rowstart . ", " . $members_per_page . "
             ");

            if ( dbrows( $members_result ) ) {
                while ( $mData = dbarray( $members_result ) ) {
                    $members[$mData['user_id']] = $mData;
                }
            }

            $this->info = [
                'total_rows'    => $rows,
                'rows'          => $members_per_page,
                'group_members' => $members,
                'group_pagenav' => makepagenav( $rowstart, $members_per_page, $rows, 3, FUSION_SELF . "?group_id=" . $group_id . "&" )
            ];
            $this->info += $data;
        } else {
            redirect( BASEDIR . 'index.php' );
        }

        return $this->info;
    }

    /**
     * Set the group id and trigger setGroupInfo
     *
     * @param int  $group_id
     * @param bool $set_info
     *
     * @return null|UserGroups|static
     */
    public function setGroup( $group_id, $set_info = TRUE ) {
        //$groupID = $group_id;
        if ( $group_id && isnum( $group_id ) && $set_info === TRUE ) {
            $this->info = $this->setGroupInfo($group_id);
        }
        return $this->getInstance();
    }

    /**
     * Render the global or custom template
     */
    public function showGroup() {
        require_once THEMES . 'templates/global/groups.tpl.php';
        render_user_group( $this->info );
    }
}
