<?php
namespace PHPFusion\UserFields;

class Profile_Activity {

    private $user_data = [];
    private $profile_id = 0;

    public function __construct($profile_id, $user_data) {
        $this->profile_id = $profile_id;
        $this->user_data = $user_data;
    }

    public function showActivityProfile() {
        $info = [];
        return $info;
    }

}