<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Network/ComposeContent.php
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

namespace PHPFusion\Page\Composer\Network;

use PHPFusion\Page\PageAdmin;

class ComposeContent extends PageAdmin {


    public static function displayContent() {

        if (empty(self::$data['page_datestamp'])) {
            self::$data['page_datestamp'] = time();
        }

        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_text('page_title', self::$locale['422'], self::$data['page_title'],
                               array('required' => TRUE)).
                    form_select('page_keywords', 'Page Meta Keywords (Seperate each keywords with Enter key)',
                                self::$data['page_keywords'], array(
                                    'max_length' => 320,
                                    'width' => '100%',
                                    'tags' => 1,
                                    'multiple' => 1,
                                )).
                    form_textarea('page_content', '', self::$data['page_content'], self::$textarea_options);

                ?>
                <div class="row m-b-20">
                    <div class="col-xs-12 col-sm-3">
                        <strong>Enabled Languages</strong><br/><i>The language of this page</i>
                    </div>
                    <div class="col-xs-12 col-sm-9">
                        <?php
                        if (multilang_table("CP")) {
                            $page_lang = !empty(self::$data['page_language']) ? explode('.',
                                                                                        self::$data['page_language']) : array();
                            foreach (fusion_get_enabled_languages() as $language => $language_name) {
                                echo form_checkbox('page_language[]', $language_name,
                                                   in_array($language, $page_lang) ? TRUE : FALSE,
                                                   array(
                                                       'class' => 'm-b-0',
                                                       'value' => $language,
                                                       'input_id' => 'page_lang-'.$language,
                                                       "delimiter" => ".",
                                                       'reverse_label' => TRUE,
                                                       'required' => TRUE
                                                   ));
                            }
                        } else {
                            echo form_hidden('page_language', '', self::$data['page_language']);
                        }
                        ?>
                    </div>
                </div>

            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Publication</strong></div>
                    <div class="panel-body">
                        <?php
                        echo form_select('page_status', 'Page Status', self::$data['page_status'], array(
                                'options' => array('Unpublished', 'Published'),
                                'width' => '100%',
                                'inline' => TRUE
                            )).
                            form_select('page_access', 'Page Access', self::$data['page_access'], array(
                                'options' => fusion_get_groups(),
                                'width' => '100%',
                                'inline' => TRUE,
                            )).
                            form_datepicker('page_datestamp', 'Published On', self::$data['page_datestamp'], array(
                                'width' => '100%',
                                'inline' => TRUE,
                            )).
                            form_select_tree('page_cat', 'Page Category', self::$data['page_cat'], array(
                                'inline' => TRUE,
                                'width' => '100%',
                                'placeholder' => self::$locale['choose'],
                            ), DB_CUSTOM_PAGES, 'page_title', 'page_id', 'page_cat', self::$data['page_id']);

                        ?>

                    </div>
                    <div class="panel-footer">
                        <?php
                        echo form_button('save', self::$locale['save'], self::$locale['save'],
                                         array('class' => 'btn-primary m-r-10'));
                        echo form_button('save_and_close', 'Save and Close', 'Save and Close',
                                         array('class' => 'btn-success m-r-10'));
                        echo form_button('preview', self::$locale['preview'], self::$locale['preview'],
                                         array('class' => 'btn-default m-r-10'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

}


