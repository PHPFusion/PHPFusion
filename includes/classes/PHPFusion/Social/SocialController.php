<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: SocialController.php
| Author: Core Development Team
+--------------------------------------------------------*/

namespace PHPFusion\Social;

class SocialController {

    private int $user_id;

    public function __construct(int $user_id) {
        $this->user_id = $user_id;
    }

    public function display(?string $view = NULL, ?string $search = NULL): void {
        $locale = SocialLocale::all();
        $titles = [
            'friends'   => $locale['SOCIAL_001'],
            'requests'  => $locale['SOCIAL_002'],
            'sent'      => $locale['SOCIAL_003'],
            'followers' => $locale['SOCIAL_004'],
            'following' => $locale['SOCIAL_005'],
            'blacklist' => $locale['SOCIAL_006'],
            'search'    => $locale['SOCIAL_007'],
            'settings'  => $locale['SOCIAL_008'],
        ];
        $view = $view ?: (string) get('view');
        $view = isset($titles[$view]) ? $view : 'friends';
        $search = $search ?? (string) get('q');

        $model = new SocialModel($this->user_id);
        $data = $model->getPage($view, $search);
        $data += [
            'title'      => $titles[$view],
            'locale'     => $locale,
            'endpoint'   => BASEDIR.'api/?api=social',
            'form_id'    => 'social_actions',
            'token'      => fusion_get_token('social_actions', 20),
            'profile_url'=> BASEDIR.'profile.php?lookup=',
        ];

        add_to_title($data['title']);
        fusion_load_script(THEMES.'templates/global/css/social.tailwind.css', 'css');
        fusion_load_script(INCLUDES.'jscripts/social.js', 'js');
        SocialView::render($data);
    }
}
