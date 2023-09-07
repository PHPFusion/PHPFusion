<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: UserNotifications.php
| Author: meangczac (Chan)
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

class UserNotifications {

    /**
     * @return bool
     */
    function saveUpdate() {

        $locale = fusion_get_locale();

        $rows = [
            'user_comment_notify'    => check_post( 'n_comments' ) ?? '0',
            'user_tag_notify'        => check_post( 'n_tags' ) ?? '0',
            'user_newsletter_notify' => check_post( 'n_newsletter' ) ?? '0',
            'user_follow_notify'     => check_post( 'n_follow' ) ?? '0',
            'user_pm_notify'         => check_post( 'n_pm' ) ?? '0',
            'user_pm_email'          => check_post( 'e_pm' ) ?? '0',
            'user_follow_email'      => check_post( 'e_follow' ) ?? '0',
            'user_feedback_email'    => check_post( 'e_feedback' ) ?? '0',
            'user_email_duration'    => sanitizer( 'e_duration', '0', 'e_duration' ),
        ];
        if (fusion_safe()) {

            dbquery_insert( DB_USER_SETTINGS, $rows, 'update', ['no_unique' => TRUE, 'primary_key' => 'user_id'] );
            addnotice( 'success', $locale['u521'] );

            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return array
     */
    function displayInputFields() {

        $locale = fusion_get_locale();

        return [
            'user_comment_notify'    => form_checkbox( 'n_comments', $locale['u502'], '', ['toggle' => TRUE, 'ext_tip' => $locale['u503'], 'class' => 'form-check-lg'] ),
            'user_tag_notify'        => form_checkbox( 'n_tags', $locale['u504'], '', ['toggle' => TRUE, 'ext_tip' => $locale['u505'], 'class' => 'form-check-lg'] ),
            'user_newsletter_notify' => form_checkbox( 'n_newsletter', $locale['u506'], '', ['toggle' => TRUE, 'ext_tip' => $locale['u507'], 'class' => 'form-check-lg'] ),
            'user_follow_notify'     => form_checkbox( 'n_follow', $locale['u508'], '', ['toggle' => TRUE, 'ext_tip' => $locale['u509'], 'class' => 'form-check-lg'] ),
            'user_pm_notify'         => form_checkbox( 'n_pm', $locale['u510'], '', ['toggle' => TRUE, 'ext_tip' => $locale['u511'], 'class' => 'form-check-lg'] ),
            'user_pm_email'          => form_checkbox( 'e_pm', $locale['u510'], '' ),
            'user_follow_email'      => form_checkbox( 'e_follow', $locale['u514'], '' ),
            'user_feedback_email'    => form_checkbox( 'e_feedback', $locale['u515'], '' ),
            'user_email_duration'    => form_checkbox( 'e_duration', $locale['u516'], '', [
                'type'    => 'radio',
                'options' => [
                    '2' => $locale['u517'],
                    '3' => $locale['u518'],
                    '4' => $locale['u519'],
                    '1' => $locale['u520'],
                ],
                'class'   => 'form-check-lg'
            ] ),
            'notify_button'          => form_button( 'save_notify', $locale['save_changes'], 'save_notify', ['class' => 'btn-primary'] ),
        ];

    }

}