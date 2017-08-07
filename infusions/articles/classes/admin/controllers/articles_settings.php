<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/admin/controllers/article_settings.php
| Author: PHP-Fusion Development Team
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
    private $locale = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayArticlesAdmin() {

        pageAccess("A");
        $this->locale = self::get_articleAdminLocale();
        $article_settings = self::get_article_settings();

        // Save
        if (isset($_POST['savesettings'])) {
            $inputArray = array(
                "article_pagination"        => form_sanitizer($_POST['article_pagination'], 1, "article_pagination"),
                "article_allow_submission"  => form_sanitizer($_POST['article_allow_submission'], 0, "article_allow_submission"),
                "article_extended_required" => form_sanitizer($_POST['article_extended_required'], 0, "article_extended_required")
            );

            // Update
            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = array(
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "article",
                    );
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
                }
                addNotice("success", $this->locale['900']);
                redirect(FUSION_REQUEST);
            } else {
                addNotice("danger", $this->locale['901']);
                $article_settings = $inputArray;
            }
        }

        //opentable("");
        ?>
        <div class="well m-t-10">
            <?php echo $this->locale['article_0400']; ?>
        </div>
        <?php
        echo openform("settingsform", "post", FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo form_text("article_pagination", $this->locale['article_0401'], $article_settings['article_pagination'], array('inline' => true, 'max_length' => 4, 'inner_width' => '250px', 'width' => '150px', 'type' => 'number'));
        echo form_select("article_allow_submission", $this->locale['article_0007'], $article_settings['article_allow_submission'], array("inline" => true, "options" => array($this->locale['disable'], $this->locale['enable'])));
        echo form_select("article_extended_required", $this->locale['article_0403'], $article_settings['article_extended_required'], array("inline" => true, "options" => array($this->locale['disable'], $this->locale['enable'])));
        echo form_button("savesettings", $this->locale['750'], $this->locale['750'], array("class" => "btn-success"));
        echo closeform();
    }
}
