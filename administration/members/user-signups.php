<?php
namespace PHPFusion\Administration\Members;
/**
 * Class User_List
 *
 * @package Administration\Members\Users
 */
class UserSignUps implements \PHPFusion\Interfaces\TableSDK {

    private $class = NULL;

    public function __construct(UserForms $obj) {
        $this->class = $obj;
    }

    public function data() {
        return [
            'debug' => FALSE,
            'table' => DB_NEW_USERS,
            'id'    => 'user_email',
            'limit' => 24,
        ];
    }

    public function bulkActions() {
        $id = post_array(['id']);
        $table_action = post('table_action');
        if (!empty($id) && $table_action) {
            foreach ($id as $email) {
                switch ($table_action) {
                    case 'approve':
                        $this->approveUser($email);
                        addNotice('success', 'All selected users have been approved.');
                        break;
                    case 'reject':
                        $this->rejectUser($email);
                        addNotice('success', 'All selected users have been rejected.');
                        break;
                    case 'review':
                        $this->reviewUser($email);
                        addNotice('success', 'All selected users have been set for administrator reviews.');
                        break;
                    case 'email':
                        $this->resendVerificationEmail($email);
                        addNotice('success', 'Verification email has been sent to all selected users.');
                        break;
                    case 'deactivate':
                        $this->deactivateUser($email);
                        addNotice('success', 'All selected users have been deactivated.');
                        break;
                    case 'reactivate':
                        $this->reactivateUser($email);
                        addNotice('success', 'All selected users have been reactivated.');
                        break;
                }
            }
            redirect(ADMIN.'members.php'.fusion_get_aidlink().'&amp;action=signup');
        }
    }

    // approve user application
    private function approveUser($email) {
        $result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_email=:email", [':email' => $email]);
        if (dbrows($result)) {
            $user_data = dbarray($result);
            $user_info = unserialize(base64_decode($user_data['user_info']));
            $user_info['user_id'] = 0;
            dbquery_insert(DB_NEW_USERS, $user_info, 'save');
            dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_email=:email", [':email' => $email]);
        }
    }

    // reject user application
    private function rejectUser($email) {
        dbquery("UPDATE ".DB_USERS." SET user_status=:status WHERE user_email=:email", [':email' => $email, ':status' => $this->class::VERIFY_USER_REJECTED]);
    }

    // pending administrator approval
    private function reviewUser($email) {
        dbquery("UPDATE ".DB_USERS." SET user_status=:status WHERE user_email=:email", [':email' => $email, ':status' => $this->class::VERIFY_USER_REVIEW]);
    }

    // cannot be activated
    private function deactivateUser($email) {
        dbquery("UPDATE ".DB_USERS." SET user_status=:status WHERE user_email=:email", [':email' => $email, ':status' => $this->class::VERIFY_USER_INACTIVE]);
    }

    // back to email confirmation
    private function reactivateUser($email) {
        dbquery("UPDATE ".DB_USERS." SET user_status=:status WHERE user_email=:email", [':email' => $email, ':status' => $this->class::VERIFY_USER_EMAIL]);
    }

    private function resendVerificationEmail($email) {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        $result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_email=:email", [':email' => $email]);
        if (dbrows($result)) {
            $user_data = dbarray($result);
            $user_info = unserialize(base64_decode($user_data['user_info']));
            $activationUrl = $settings['siteurl']."register.php?email=".$user_info['user_email']."&code=".$user_data['user_code'];
            $message = str_replace("USER_NAME", $user_info['user_name'], $locale['u152']);
            $message = str_replace("SITENAME", $settings['sitename'], $message);
            $message = str_replace("SITEUSERNAME", $settings['siteusername'], $message);
            $message = str_replace("USER_PASSWORD", '********', $message);
            $message = str_replace("ACTIVATION_LINK", $activationUrl, $message);
            $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['u151']);
            if (!sendemail($user_info['user_name'], $user_info['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message)) {
                $message = strtr($locale['u154'], [
                    '[LINK]'  => "<a href='".BASEDIR."contact.php'><strong>",
                    '[/LINK]' => "</strong></a>"
                ]);
                addNotice('warning', $locale['u153']."<br />".$message, 'all');
            }
        }
    }


    public function properties() {
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();

        return [
            'table_id'         => 'user-signups-table',
            'search_col'       => 'user_name',
            'search_label'     => 'Search User',
            'date_col'         => 'user_datestamp',
            'order_col'        => [
                'user_datestamp' => 'signed',
                'user_language'  => 'language',
                'user_firstname' => 'firstname',
                'user_name'      => 'name',
                'user_email'     => 'email'
            ],
            'updated_message'  => 'User have been updated',
            'deleted_message'  => 'User have been deleted',
            'edit_link_format' => ADMIN.'members.php'.$aidlink.'&amp;action=edit&amp;lookup=',
            'view_link_format' => BASEDIR.'profile.php?lookup=:user_id',
            'link_filters'     => [
                'user_status' => [
                    'title'   => 'Roles:',
                    'count'   => TRUE,
                    'options' => [
                        1 => 'Pending Review',
                        0 => 'Waiting for email confirmation',
                        2 => 'Inactive',
                        3 => 'Rejected'
                    ]
                ]
            ],
            'action_filters'   => [
                'approve'    => 'Approve Membership',
                'reject'     => 'Reject Membership',
                'review'     => 'Pending Review',
                'email'      => 'Resend Activation Email',
                'deactivate' => 'Deactivate',
                'reactivate' => 'Reactivate',
            ],
        ];
    }

    public function column() {

        return [
            'user_name'      => [
                'title'       => 'User Name',
                'title_class' => 'col-xs-3',
                'view_link'   => FALSE,
                'edit_link'   => FALSE,
                'delete_link' => FALSE,
            ],
            'user_firstname' => [
                'title'       => 'First and Last Name',
                'title_class' => 'col-xs-3',
                'visibility'  => TRUE,
                'callback'    => ['Members_Administration', 'diplayRealName'],
            ],
            'user_email'     => [
                'title'      => 'Email',
                'visibility' => TRUE,
            ],
            'user_datestamp' => [
                'title' => 'Signup Date',
                'date'  => TRUE,
                'class' => 'width-15',
            ],
            'user_language'  => [
                'title' => 'Language',
                'class' => 'width-15',
            ]
        ];

        /*
         * $tLocale = [
            'user_groups'     => self::$locale['ME_425'],
            'user_timezone'   => self::$locale['ME_426'],
            ''     => self::$locale['']
        ];
         */
    }

    public function quickEdit() {
        return [];
    }

}
