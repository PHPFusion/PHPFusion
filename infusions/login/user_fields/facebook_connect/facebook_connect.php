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
    private $default_settings = [
        'app_id'               => '',
        'button_width'         => '',
        'button_size'          => '',
        'enable_friends_faces' => '',
        'enable_details'       => '',
        'max_photo_rows'       => '',
        'button_text'          => '',
        'enable_logout'        => '',
    ];
    private $settings = [];

    /**
     * Display the driver settings form
     */
    public function display_settings_form() {
        $locale = fusion_get_locale('', LOGIN_LOCALESET.'user_fb_connect.php');
        $this->settings += $this->load_driver_settings('user_fb_connect');
        $this->settings += $this->default_settings;

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
            // Stored as an encrypted value to protect sensitive information.
            if ($this->update_driver_settings('user_fb_connect', $this->settings)) {
                redirect(FUSION_REQUEST);
            }
        }

        echo "<div class='well'>".$locale['uf_fb_connect_200']."</div>\n";
        echo openside("<h4><i class='fab fa-facebook-square fa-lg m-r-10'></i>".$locale['uf_fb_connect_201']."</h4>");
        echo openform('facebook_settings_frm', 'post', FUSION_REQUEST);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('app_id', $locale['uf_fb_connect_202'], $this->settings['app_id'], ['required' => TRUE, 'placeholder' => $locale['uf_fb_connect_203']]);
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo "<strong>".$locale['uf_fb_connect_204']."</strong>.<br/>".$locale['uf_fb_connect_205'];
        echo "</div>\n";
        echo "</div>\n";
        echo "<hr/>\n";
        echo form_para($locale['uf_fb_connect_206'], 'cfg');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('button_width', $locale['uf_fb_connect_207'], $this->settings['button_width'], ['placeholder' => $locale['uf_fb_connect_208']]);
        echo form_select('button_size', $locale['uf_fb_connect_209'], $this->settings['button_size'], ['options' => [
            'small'  => $locale['uf_fb_connect_210'],
            'medium' => $locale['uf_fb_connect_211'],
            'large'  => $locale['uf_fb_connect_212']
        ]]);
        echo form_checkbox('enable_friends_faces', $locale['uf_fb_connect_213'], $this->settings['enable_friends_faces'], ['reverse_label' => TRUE, 'ext_tip' => $locale['uf_fb_connect_214']]);
        echo form_checkbox('enable_details', $locale['uf_fb_connect_215'], $this->settings['enable_details'], ['reverse_label' => TRUE]);
        echo "</div><div class='col-xs-12 col-sm-6'>\n";
        echo form_text('max_photo_rows', $locale['uf_fb_connect_216'], $this->settings['max_photo_rows'], ['placeholder' => $locale['uf_fb_connect_217']]);
        echo form_select('button_text', $locale['uf_fb_connect_218'], $this->settings['button_text'], ['options' => [
            'continue_with' => $locale['uf_fb_connect_219'],
            'login_with'    => $locale['uf_fb_connect_220']
        ]]);
        echo form_checkbox('enable_logout', $locale['uf_fb_connect_221'], $this->settings['enable_logout'], ['reverse_label' => TRUE]);
        echo "</div>\n</div>\n";
        echo form_button('save_fb', $locale['uf_fb_connect_222'], 'save_fb');
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