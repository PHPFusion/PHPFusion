<?php
require_once __DIR__.'/../../maincore.php';
require_once INFUSIONS.'facebook_connect/class/autoload.php';
// facebook connect code
if (iMEMBER) {
    if (\Defender::safe()) {
        $action = post('action');
        $settings = get_settings('facebook_connect');
        if ($action == 'connect') {
            $user_id = fusion_get_userdata('user_id');
            $fb = new Facebook\Facebook([
                'app_id'                => $settings['fb_app_id'],
                'app_secret'            => $settings['fb_secret'],
                'default_graph_version' => 'v2.10',
            ]);
            $helper = $fb->getRedirectLoginHelper();
            $permissions = ['email']; // Optional permissions
            $loginUrl = $helper->getLoginUrl(fusion_get_settings('siteurl').'test/facebook/fb-callback.php', $permissions);
            echo '<script>header(url:'.$loginUrl.');</script>';
        }
    }
}
