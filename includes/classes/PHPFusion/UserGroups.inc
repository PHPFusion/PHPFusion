<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserGroups.inc
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
namespace PHPFusion;

/**
 * Class UserGroups
 *
 * @package PHPFusion
 */
class UserGroups {

    private static $instance = NULL;

    private $groupID = 0;


    private $info = [
        'total_rows'    => 0,
        'rows'          => 0,
        'group_members' => []
    ];

    // For Profile output
    public $user_data = [];
    public $profile_id = 0;

    /**
     * Get the UserGroups Instance
     *
     * @return null|static
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Fetch group information
     *
     * @param $group_id
     *
     * @return array|bool
     */
    protected function setGroupInfo($group_id) {

        $_GET['rowstart'] = (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) ? 0 : $_GET['rowstart'];

        $result = dbquery("SELECT * FROM ".DB_USER_GROUPS." WHERE group_id='".intval($group_id)."'");

        if (dbrows($result) > 0) {
            $members = [];
            $members_per_page = 20;
            $data = dbarray($result);

            set_title($data['group_name']);

            $rows = dbcount("(user_id)", DB_USERS,
                (iADMIN ? "user_status>='0'" : "user_status='0'")." AND user_groups REGEXP('^\\\.{$group_id}$|\\\.{$group_id}\\\.|\\\.{$group_id}$')");

            $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $rows ? $_GET['rowstart'] : 0);

            $members_query = "
              SELECT user_id, user_name, user_level, user_status, user_language, user_joined, user_avatar
              FROM ".DB_USERS." WHERE ".(iADMIN ? "user_status>='0'" : "user_status='0'")."
              AND user_groups REGEXP('^\\\.{$group_id}$|\\\.{$group_id}\\\.|\\\.{$group_id}$')
              ORDER BY user_level DESC, user_name ASC LIMIT ".intval($_GET['rowstart']).", $members_per_page
             ";

            $members_result = dbquery($members_query);
            if (dbrows($members_result) > 0) {
                while ($mData = dbarray($members_result)) {
                    $members[$mData['user_id']] = $mData;
                }
            }

            $this->info = [
                'total_rows'    => $rows,
                'rows'          => $members_per_page,
                'group_members' => $members,
            ];
            $this->info += $data;
        } else {
            redirect(BASEDIR.'index.php');
        }

        return $this->info;
    }

    /**
     * Set the group id and trigger setGroupInfo
     *
     * @param           $group_id
     * @param bool|TRUE $set_info
     *
     * @return null|UserGroups|static
     */
    public function setGroup($group_id, $set_info = TRUE) {
        $this->groupID = $group_id;
        if ($this->groupID && isnum($this->groupID) && $set_info === TRUE) {
            $this->info = $this->setGroupInfo($group_id);
        }

        return $this;
    }

    /**
     * Render the global or custom template
     */
    public function showGroup() {
        require_once THEMES."templates/global/groups.php";
        render_user_group($this->info);
    }

    /**
     * Note: You are required to add setGroup function prior to using this function
     * @param $user_id
     *
     * @return bool
     */
    public function add($user_id) {
        if (!empty($this->info['group_id'])) {
            $user_groups = fusion_get_user($user_id, "user_groups");
            if (!preg_match("(^\.{$this->info['group_id']}$|\.{$this->info['group_id']}\.|\.{$this->info['group_id']}$)", $user_groups)) {
                $userdata = [
                    'user_id'     => $user_id,
                    'user_groups' => $user_groups.".".$this->info['group_id']
                ];
                dbquery_insert(DB_USERS, $userdata, 'update');
                add_notice("success", "User has been added to group id ".$this->info['group_id']);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function get_groupVisibility($group_access) {
        $groups = fusion_get_groups();
        return $groups[$group_access].' Group';
    }

    /**
     *
     * @param $user_groups  $userdata['user_groups']
     */
    public static function get_userGroupCount($user_groups) {
    	if (!empty($user_groups)) {
            $user_groups = strpos($user_groups, ".") == 0 ? substr($user_groups, 1) : $user_groups;
            return count(explode(".", $user_groups));
    	}
    	return NULL;
    }

    public function get_OutputInfo() {
        static $info = [];

        // Groups - need translating.
        //$this->info['core_field']['profile_user_group']['title'] = $locale['u057'];
        if (empty($info)) {

            $info = [
                'group_max_count' => 0,
                'limit' => 16,
                'pagenav' => '',
                'user_groups' => []
            ];

            $info['group_max_count'] = self::get_userGroupCount($this->user_data['user_groups']);

            if ($info['group_max_count']) {

                $rowstart = get("rowstart");
                $rowstart = $rowstart && $rowstart <= $info['group_max_count'] ? $rowstart : 0;

                $user_groups = strpos($this->user_data['user_groups'], ".") == 0 ? substr($this->user_data['user_groups'], 1) : $this->user_data['user_groups'];
                $user_groups = str_replace(".", ",", $user_groups);

                $result = dbquery("SELECT * FROM ".DB_USER_GROUPS."
                WHERE group_id IN ($user_groups) ORDER BY group_name ASC LIMIT :rowstart, :limiter", [
                    ':rowstart' => $rowstart,
                    ":limiter" => $info['limit']
                ]);

                if ($group_count = dbrows($result)) {

                    $info['pagenav'] = makepagenav($rowstart, $info['limit'], $info['group_max_count'], 3,
                        BASEDIR."profile.php?lookup=".$this->profile_id."&amp;profile_page=groups&amp;",
                        "rowstart"
                        );

                    while ($data = dbarray($result)) {
                        // group members checks
                        $user_rows = dbrows(dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_groups REGEXP('^\\.{".$data['group_id']."}$|\\.{".$data['group_id']."}\\.|\\.{".$data['group_id']."}$')"));
                        $info['user_groups'][$data['group_id']] = $data + [
                            "group_url" => BASEDIR."profile.php?group_id=".$data['group_id'],
                            "group_member_count" => format_word($user_rows, "member|members", ['format_num'=>TRUE]),
                            "group_created" => timer(TIME),
                            "group_visibility" => $this->get_groupVisibility($data['group_id'])
                            ];
                    }
                }
            }
        }

        return (array) $info;

    }
}
