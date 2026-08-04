<?php

namespace PHPFusion\Social;

class SocialProfile {

    public static function render(int $profile_id): void {
        $locale = SocialLocale::all();
        $viewer_id = iMEMBER ? (int) fusion_get_userdata('user_id') : 0;
        if (!SocialPrivacy::canViewFriends($profile_id, $viewer_id)) {
            echo "<div class='alert alert-info'>".htmlspecialchars($locale['SOCIAL_078'])."</div>";
            return;
        }

        $friend_ids = (new SocialBuddy($profile_id))->getFriends();
        if (!$friend_ids) {
            echo "<div class='text-center text-secondary p-4'>".htmlspecialchars($locale['SOCIAL_079'])."</div>";
            return;
        }

        $friend_ids = array_slice(array_map('intval', $friend_ids), 0, 24);
        $placeholders = [];
        $parameters = [];
        foreach ($friend_ids as $index => $friend_id) {
            $key = ':friend_'.$index;
            $placeholders[] = $key;
            $parameters[$key] = $friend_id;
        }
        $result = dbquery(
            "SELECT user_id, user_name, user_avatar, user_status, user_level
             FROM ".DB_USERS."
             WHERE user_id IN (".implode(',', $placeholders).")
             ORDER BY user_name",
            $parameters
        );

        echo "<div class='row g-3'>";
        while ($friend = dbarray($result)) {
            echo "<div class='col-6 col-md-4 col-xl-3'>";
            echo "<div class='card h-100'><div class='card-body text-center'>";
            echo display_avatar($friend, '64px', '', TRUE, 'rounded-circle mx-auto');
            echo "<div class='mt-2 fw-semibold'>".profile_link(
                $friend['user_id'],
                $friend['user_name'],
                $friend['user_status']
            )."</div>";
            echo "<div class='small text-secondary'>".getuserlevel($friend['user_level'])."</div>";
            echo "</div></div></div>";
        }
        echo "</div>";
    }
}
