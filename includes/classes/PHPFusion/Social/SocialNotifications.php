<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: SocialNotifications.php
| Author: Core Development Team
+--------------------------------------------------------*/

namespace PHPFusion\Social;

use PHPFusion\Notifications\Notifications;

class SocialNotifications {

    public static function deliver(array $payload): void {
        $event = $payload['event'] ?? '';
        $recipient_id = (int) ($payload['recipient_id'] ?? 0);
        $sender_id = (int) ($payload['sender_id'] ?? 0);
        $locale = SocialLocale::all();
        if ($recipient_id) {
            $recipient = dbarray(dbquery(
                "SELECT user_language FROM ".DB_USERS." WHERE user_id=:user",
                [':user' => $recipient_id]
            ));
            $language = $recipient['user_language'] ?? '';
            if ($language === '' || $language === 'Default') {
                $language = (string) fusion_get_settings('locale');
            }
            $locale = SocialLocale::forLanguage($language);
        }
        $events = [
            'friend_request'  => [
                'title'   => $locale['SOCIAL_069'],
                'message' => $locale['SOCIAL_070'],
                'view'  => 'requests',
            ],
            'friend_accepted' => [
                'title'   => $locale['SOCIAL_071'],
                'message' => $locale['SOCIAL_072'],
                'view'  => 'friends',
            ],
            'follow'           => [
                'title'   => $locale['SOCIAL_073'],
                'message' => $locale['SOCIAL_074'],
                'view'  => 'followers',
            ],
        ];

        if (!$recipient_id || !$sender_id || !isset($events[$event])) {
            throw new \InvalidArgumentException($locale['SOCIAL_075']);
        }
        if (!SocialPrivacy::wantsNotification($recipient_id, $event)) {
            return;
        }

        $sender = dbarray(dbquery(
            "SELECT user_name FROM ".DB_USERS." WHERE user_id=:user",
            [':user' => $sender_id]
        ));
        if (empty($sender['user_name'])) {
            throw new \RuntimeException($locale['SOCIAL_076']);
        }

        $event_data = $events[$event];
        $result = Notifications::send($recipient_id, [
            'sender_id' => $sender_id,
            'infusion'  => 'social',
            'type'      => 'info',
            'title'     => $event_data['title'],
            'message'   => sprintf($event_data['message'], $sender['user_name']),
            'link'      => BASEDIR.'social.php?view='.$event_data['view'],
            'icon'      => 'fa-users',
            'key'       => 'social:'.$event.':'.$recipient_id.':'.$sender_id,
        ]);

        if ($result === FALSE) {
            throw new \RuntimeException($locale['SOCIAL_077']);
        }
    }
}
