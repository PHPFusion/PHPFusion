<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: article_settings.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Articles;

class ArticlesSettingsAdmin extends ArticlesAdminModel {
    private static $instance = NULL;

    public static function articles() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayArticlesAdmin() {
        pageaccess("A");
        $locale = self::getArticleAdminLocales();
        $article_settings = self::getArticleSettings();

        // Save
        if (isset($_POST['savesettings'])) {
            $inputArray = [
                'article_pagination'        => form_sanitizer($_POST['article_pagination'], 15, 'article_pagination'),
                'article_allow_submission'  => form_sanitizer($_POST['article_allow_submission'], 0, 'article_allow_submission'),
                'article_extended_required' => form_sanitizer($_POST['article_extended_required'], 0, 'article_extended_required'),
                'article_submission_access' => form_sanitizer($_POST['article_submission_access'], USER_LEVEL_MEMBER, 'article_submission_access')
            ];

            // Update
            if (fusion_safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        'settings_name' => $settings_name, 'settings_value' => $settings_value, 'settings_inf' => 'articles',
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
                }
                addnotice('success', $locale['900']);
                redirect(FUSION_REQUEST);
            } else {
                addnotice('danger', $locale['901']);
                $article_settings = $inputArray;
            }
        }

        echo "<div class='well'>".$locale['article_0400']."</div>";

        echo openform('settingsform', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo form_text('article_pagination', $locale['article_0401'], $article_settings['article_pagination'], [
            'inline'      => TRUE,
            'max_length'  => 4,
            'inner_width' => '250px',
            'width'       => '150px',
            'type'        => 'number'
        ]);
        echo form_select('article_allow_submission', $locale['article_0007'], $article_settings['article_allow_submission'], [
            'inline'  => TRUE,
            'options' => [$locale['disable'], $locale['enable']]
        ]);
        echo form_select('article_submission_access[]', $locale['submit_access'], $article_settings['article_submission_access'], [
            'inline'   => TRUE,
            'options'  => fusion_get_groups([USER_LEVEL_PUBLIC]),
            'multiple' => TRUE,
        ]);
        echo form_select('article_extended_required', $locale['article_0403'], $article_settings['article_extended_required'], [
            'inline'  => TRUE,
            'options' => [$locale['no'], $locale['yes']]
        ]);
        echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o']);
        echo closeform();
    }
}
