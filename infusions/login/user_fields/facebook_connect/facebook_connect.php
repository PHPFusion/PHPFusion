<?php

/**
 * Class Facebook_Authenticate
 * Facebook web login handler
 */
class Facebook_Connect extends \PHPFusion\Infusions\Login\Login {

    /**
     * Configurable Parameters as of 25/3/2018
     * https://developers.facebook.com/docs/facebook-login/web#
     *
     * @var array
     */
    private $settings = [
        'app_id'               => '',
        'button_width'         => '',
        'button_size'          => '',
        'enable_friends_faces' => '',
        'enable_details'       => '',
        'max_photo_rows'       => '',
        'button_text'          => '',
        'enable_logout'        => '',
    ];

    /**
     * Display the driver settings form
     */
    public function display_settings_form() {

        $driver_settings = $this->get_driver_settings('user_fb_connect');
        if (!empty($driver_settings)) {
            $this->settings += explode(",", $driver_settings);
        }

        if (isset($_POST['save_fb'])) {
            $this->settings = [
                'app_id'               => form_sanitizer($_POST['app_id'], '', 'app_id'),
                'button_width'         => form_sanitizer($_POST['button_width'], '', 'button_width'),
                'button_size'          => form_sanitizer($_POST['button_size'], '', 'button_size'),
                'enable_friends_faces' => (isset($_POST['enable_friends_faces']) ? '1' : '0'),
                'enable_details'       => (isset($_POST['enable_details']) ? '1' : '0'),
                'max_photo_rows'       => form_sanitizer($_POST['max_photo_rows'], '', 'max_photo_rows'),
                'button_text'          => form_sanitizer($_POST['button_text'], '', 'button_text'),
                'enable_logout'        => (isset($_POST['enable_logout']) ? '1' : '0')
            ];
            //print_p($this->settings);
        }

        echo "<div class='well'>Facebook Login with the Facebook SDK enables people to sign into your webpage with their Facebook credentials.</div>\n";
        echo openside("<h4><i class='fab fa-facebook-square fa-lg m-r-10'></i>Facebook Plugin Settings</h4>");
        echo openform('facebook_settings_frm', 'post', FUSION_REQUEST);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('app_id', 'Facebook App ID', $this->settings['app_id'], ['required' => TRUE, 'placeholder' => 'Enter your Facebook Developer App ID']);
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo "<strong>You need to have a valid Facebook Client APP ID to use this login feature</strong>.<br/>If you do not have an APP ID, create and retrieve the Facebook Client APP ID in your Facebook Developer page.";
        echo "</div>\n";
        echo "</div>\n";

        echo "<hr/>\n";
        echo form_para('Configure Plugin', 'cfg');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('button_width', 'Width', $this->settings['button_width'], ['placeholder' => 'The pixel width of the facebook plugin button']);
        echo form_select('button_size', 'Button Size', $this->settings['button_size'], ['options' => ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large']]);
        echo form_checkbox('enable_friends_faces', 'Show Friends\' Faces', $this->settings['enable_friends_faces'], ['reverse_label' => TRUE, 'ext_tip' => 'Show profile photos of friends who have used this site']);
        echo form_checkbox('enable_details', 'Include name and profile picture when user is signed into Facebook', $this->settings['enable_details'], ['reverse_label' => TRUE]);
        echo "</div><div class='col-xs-12 col-sm-6'>\n";
        echo form_text('max_photo_rows', 'Maximum Rows of Photos', $this->settings['max_photo_rows'], ['placeholder' => 'The maximum number of rows of profile photos to show']);
        echo form_select('button_text', 'Button Text', $this->settings['button_text'], ['options' => ['continue_with' => 'Continue With...', 'login_with' => 'Login With...']]);
        echo form_checkbox('enable_logout', 'Enable Logout Button', $this->settings['enable_logout'], ['reverse_label' => TRUE]);
        echo "</div>\n</div>\n";
        echo form_button('save_fb', 'Save Facebook Settings', 'save_fb');
        echo closeform();
        echo closeside();

    }

    public function login_authenticate($user) {
        if (isset($user['user_gauth']) && !empty($user['user_gauth'])) { // check if there are any secret code.
            $_SESSION['secret_code'] = $user['user_gauth'];
            $_SESSION['uid'] = $user['user_id'];
            echo "<script>";
            //redirect(INFUSIONS.'login/user_fields/google_auth/authentication.php');
        }

        return NULL;
    }

    public function login_connector() {
        if (isset($user['user_gauth']) && !empty($user['user_gauth'])) { // check if there are any secret code.
            $_SESSION['secret_code'] = $user['user_gauth'];
            $_SESSION['uid'] = $user['user_id'];
            die('you have reached the connector');
            echo "<script>";

            //redirect(INFUSIONS.'login/user_fields/google_auth/authentication.php');
        }

        return NULL;
    }

}