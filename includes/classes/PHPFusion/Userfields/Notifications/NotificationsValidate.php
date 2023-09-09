<?php
namespace PHPFusion\Userfields\Notifications;

use PHPFusion\Userfields\UserFieldsValidate;

/**
 * Class NotificationsValidate
 *
 * @package PHPFusion\Userfields\Notifications
 */
class NotificationsValidate extends UserFieldsValidate {


    /**
     * Validate notification input data
     *
     * Access with UserFieldsInput only
     */
    function validate() {

        return [
            'user_comments_notify'    => check_post( 'n_comments' ) ?? '0',
            'user_tag_notify'        => check_post( 'n_tags' ) ?? '0',
            'user_newsletter_notify' => check_post( 'n_newsletter' ) ?? '0',
            'user_follow_notify'     => check_post( 'n_follow' ) ?? '0',
            'user_pm_notify'         => check_post( 'n_pm' ) ?? '0',
            'user_pm_email'          => check_post( 'e_pm' ) ?? '0',
            'user_follow_email'      => check_post( 'e_follow' ) ?? '0',
            'user_feedback_email'    => check_post( 'e_feedback' ) ?? '0',
            'user_email_duration'    => sanitizer( 'e_duration', '0', 'e_duration' ),
        ];

    }

}