<?php
namespace Administration\Members\Sub_Controllers;

use Administration\Members\Members_Admin;
use PHPFusion\UserFields;
use PHPFusion\UserFieldsInput;

/**
 * Class Members_Profile
 * Controller for View, Add, Edit and Delete Users Account
 *
 * @package Administration\Members\Sub_Controllers
 */
class Members_Profile extends Members_Admin {

    /*
     * Displays new user form
     */
    public static function display_new_user_form() {
        if (isset($_POST['add_new_user'])) {
            $userInput = new UserFieldsInput();
            $userInput->validation = FALSE;
            $userInput->emailVerification = FALSE;
            $userInput->adminActivation = FALSE;
            $userInput->registration = TRUE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->saveInsert();
            unset($userInput);
            if (defender::safe()) {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
        $userFields = new UserFields();
        $userFields->postName = "add_new_user";
        $userFields->postValue = self::$locale['ME_450'];
        $userFields->displayValidation = fusion_get_settings("display_validation");
        $userFields->plugin_folder = INCLUDES."user_fields/";
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = TRUE;
        $userFields->method = 'input';
        $userFields->display_profile_input();
    }

    /*
     * Displays user profile
     */
    public static function display_user_profile() {
        $settings = fusion_get_settings();
        $userFields = new UserFields();
        $userFields->postName = "register";
        $userFields->postValue = self::$locale['u101'];
        $userFields->displayValidation = $settings['display_validation'];
        $userFields->displayTerms = $settings['enable_terms'];
        $userFields->plugin_folder = INCLUDES."user_fields/";
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = FALSE;
        $userFields->userData = self::$user_data;
        $userFields->method = 'display';
        $userFields->display_profile_output();
    }

}