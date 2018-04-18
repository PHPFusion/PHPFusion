<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Network/ComposeContent.inc
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Page\Composer\Node;

use PHPFusion\Page\PageAdmin;

class ComposeContent extends PageAdmin {
    public static function displayContent() {
        // add page row and grid data to custom page
        if (empty(self::$data['page_datestamp'])) {
            self::$data['page_datestamp'] = TIME;
        }
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-9">
                <?php
                echo form_text('page_title', self::$locale['page_0300'], self::$data['page_title'],
                        [
                            'required'   => TRUE,
                            'error_text' => self::$locale['page_0117']
                        ]
                    ).
                    form_select('page_keywords', self::$locale['page_0301'], self::$data['page_keywords'],
                        [
                            'max_length'  => 320,
                            'inner_width' => '100%',
                            'width'       => '100%',
                            'tags'        => 1,
                            'multiple'    => 1,
                        ]).
                    form_textarea('page_content', '', self::$data['page_content'], self::$textarea_options);
                ?>
                <div class='well'>
                    <div class="row m-b-20">
                        <div class="col-xs-3 col-sm-4">
                            <strong><?php echo self::$locale['page_0302'] ?></strong><br/><i><?php echo self::$locale['page_0303'] ?></i>
                        </div>
                        <div class="col-xs-9 col-sm-8">
                            <?php
                            if (multilang_table("CP")) {
                                $page_lang = !empty(self::$data['page_language']) ? explode('.', self::$data['page_language']) : [];
                                foreach (fusion_get_enabled_languages() as $language => $language_name) {
                                    echo form_checkbox('page_language[]', $language_name,
                                        in_array($language, $page_lang) ? TRUE : FALSE,
                                        [
                                            'class'         => 'm-b-0',
                                            'value'         => $language,
                                            'input_id'      => 'page_lang-'.$language,
                                            "delimiter"     => ".",
                                            'reverse_label' => TRUE,
                                            'required'      => TRUE
                                        ]
                                    );
                                }
                            } else {
                                echo form_hidden('page_language', '', self::$data['page_language']);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><?php echo self::$locale['page_0304'] ?></strong></div>
                    <div class="panel-body">
                        <?php
                        echo form_select('page_status', self::$locale['page_0305'], self::$data['page_status'], [
                                'options'     => [self::$locale['unpublish'], self::$locale['publish']],
                                'inner_width' => '100%',
                                'inline'      => FALSE
                            ]).
                            form_select('page_access', self::$locale['page_0306'], self::$data['page_access'], [
                                'options'     => fusion_get_groups(),
                                'inner_width' => '100%',
                                'inline'      => FALSE,
                            ]).
                            form_datepicker('page_datestamp', self::$locale['page_0307'], self::$data['page_datestamp'], [
                                'inner_width' => '100%',
                                'inline'      => FALSE,
                            ]).
                            form_select_tree('page_cat', self::$locale['page_0308'], self::$data['page_cat'], [
                                'inner_width' => '100%',
                                'placeholder' => self::$locale['choose'],
                                'inline'      => FALSE,
                            ], DB_CUSTOM_PAGES, 'page_title', 'page_id', 'page_cat', self::$data['page_id']);

                        if (fusion_get_settings('tinymce_enabled') != 1) {
                            echo form_checkbox('page_breaks', self::$locale['page_0308a'], self::$data['page_breaks'], [
                                'value'         => 'y',
                                'class'         => 'm-b-5',
                                'reverse_label' => TRUE
                            ]);
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="spacer-sm">
            <?php
            echo form_button('save', self::$locale['save'], self::$locale['save'], ['class' => 'btn-success btn-sm m-r-10', 'icon' => 'fa fa-hdd-o']);
            echo form_button('save_and_close', self::$locale['save_and_close'], self::$locale['save_and_close'], ['class' => 'btn-primary btn-sm m-r-10', 'icon' => 'fa fa-hdd-o']);
            ?>
        </div>
        <?php
    }

}
