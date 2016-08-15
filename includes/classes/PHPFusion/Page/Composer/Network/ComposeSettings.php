<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Network/ComposeSettings.php
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
use PHPFusion\SiteLinks;

class ComposeSettings extends PageAdmin {

    public static function displayContent() {
        add_to_jquery("
        function checkLinkPosition( val ) {
            if ( val == 4 ) {
                $('#link_position_id').prop('disabled', false).show();
            } else {
                $('#link_position_id').prop('disabled', true).hide();
            }
        }
        ");
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Site Links Attributes</strong></div>
                    <div class="panel-body">
                        <?php

                        $has_link = (!empty(self::$data['page_link_cat']) && SiteLinks::verify_sitelinks(self::$data['page_link_cat'])) ? TRUE : FALSE;

                        if ($has_link === FALSE and !isset($_GET['add_sl'])) : ?>
                            <div class="well text-center">
                                No Site Links defined<br/>
                                <a class="btn btn-primary m-t-20"
                                   href="<?php echo clean_request('add_sl=true', array('add_sl'), FALSE) ?>">
                                    Add Site Links
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Whether has link data or not
                        $data = array(
                            'link_id' => self::$data['page_link_cat'],
                            'link_name' => self::$data['page_title'],
                            'link_url' => 'viewpage.php?page_id='.self::$data['page_id'],
                            'link_icon' => '',
                            'link_cat' => 0,
                            'link_language' => LANGUAGE,
                            'link_visibility' => self::$data['page_access'],
                            'link_order' => 0,
                            'link_position' => 1,
                            'link_window' => 0,
                            'link_position_id' => 0,
                        );

                        if ($has_link) {
                            $data = SiteLinks::get_SiteLinks(self::$data['page_link_cat']);
                        }


                        if (isset($_GET['add_sl']) or $has_link === TRUE) {

                            if (isset($_POST['save_link'])) {

                                $data = array(
                                    "link_id" => $data['link_id'],
                                    "link_cat" => form_sanitizer($_POST['link_cat'], 0, 'link_cat'),
                                    "link_name" => form_sanitizer($_POST['link_name'], '', 'link_name'),
                                    "link_url" => $data['link_url'],
                                    "link_icon" => form_sanitizer($_POST['link_icon'], '', 'link_icon'),
                                    "link_language" => $data['link_language'],
                                    "link_visibility" => $data['link_visibility'],
                                    "link_position" => form_sanitizer($_POST['link_position'], '', 'link_position'),
                                    "link_order" => form_sanitizer($_POST['link_order'], '', 'link_order'),
                                    "link_window" => form_sanitizer(isset($_POST['link_window']) && $_POST['link_window'] == 1 ? 1 : 0,
                                                                    0,
                                                                    'link_window')
                                );
                                if ($data['link_position'] > 3) {
                                    $data['link_position'] = form_sanitizer($_POST['link_position_id'], 3,
                                                                            'link_position_id');
                                }

                                if (empty($data['link_order'])) {
                                    $max_order_query = "SELECT MAX(link_order) 'link_order' FROM ".DB_SITE_LINKS."
                                    ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."
                                    link_cat='".$data['link_cat']."'";

                                    $data['link_order'] = dbresult(dbquery($max_order_query), 0) + 1;
                                }

                                if (\defender::safe()) {

                                    if (!empty($data['link_id'])) {

                                        dbquery_order(DB_SITE_LINKS, $data['link_order'], "link_order",
                                                      $data['link_id'],
                                                      "link_id",
                                                      $data['link_cat'], "link_cat", multilang_table("SL"),
                                                      "link_language",
                                                      "update");

                                        dbquery_insert(DB_SITE_LINKS, $data, 'update');

                                        addNotice("success", 'Site Links updated');

                                    } else {

                                        dbquery_order(DB_SITE_LINKS, $data['link_order'], "link_order",
                                                      $data['link_id'],
                                                      "link_id",
                                                      $data['link_cat'], "link_cat", multilang_table("SL"),
                                                      "link_language", "save");

                                        dbquery_insert(DB_SITE_LINKS, $data, 'save');

                                        $id = dblastid();

                                        dbquery("UPDATE ".DB_CUSTOM_PAGES." SET page_link_cat='$id'");

                                        addNotice("success", 'Site Links created');

                                    }

                                    redirect(clean_request('', array('add_sl'), FALSE));
                                }
                            }

                            if ($data['link_position'] > 3) {
                                $data['link_position_id'] = $data['link_position'];
                                $data['link_position'] = 4;
                            }
                            add_to_jquery("
                                checkLinkPosition( ".$data['link_position']." );
                                $('#link_position').bind('change', function(e) {
                                    checkLinkPosition( $(this).val() );
                                });
                                ");

                            echo form_text('link_name', 'Link Name', $data['link_name'],
                                           array('required' => TRUE, 'inline' => TRUE)).
                                form_select('link_position', 'Link Position', $data['link_position'],
                                            array(
                                                'options' => SiteLinks::get_SiteLinksPosition(),
                                                'inline' => TRUE,
                                                'stacked' => form_text('link_position_id', '', '',
                                                    //$this->data['link_position_id'],
                                                                       array(
                                                                           'required' => TRUE,
                                                                           'placeholder' => 'ID',
                                                                           'type' => 'number',
                                                                           'type' => 'number',
                                                                           'width' => '150px',
                                                                           'class' => 'm-b-0'
                                                                       )
                                                )
                                            )).
                                form_text('link_order', 'Link Order', $data['link_order'],
                                          array('type' => 'number', 'width' => '150px', 'inline' => TRUE)).
                                form_text('link_icon', 'Link Icon', $data['link_icon'],
                                          array('width' => '150px', 'inline' => TRUE)).
                                form_select_tree('link_cat', 'Link Category', $data['link_cat'], array(
                                    "parent_value" => self::$locale['parent'],
                                    'inline' => TRUE,
                                    'query' => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : ''),
                                    'disable_opts' => self::$data['page_link_cat'],
                                    'hide_disabled' => FALSE,
                                    'class' => 'm-b-0'
                                ), DB_SITE_LINKS, "link_name", "link_id", "link_cat")."<hr/>",
                            form_button('save_link', 'Save Link', 'save_link', array('class' => 'btn-primary'));
                            ?>
                            <a class="btn btn-default" href="<?php echo clean_request('', array('add_sl'), FALSE) ?>">
                                <?php echo self::$locale['cancel'] ?>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Panel Attributes</strong></div>
                    <div class="panel-body">
                        <?php
                        echo form_btngroup("page_left_panel", 'Left Panels', self::$data['page_left_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_right_panel", 'Right Panels', self::$data['page_right_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_header_panel", 'Header Panels', self::$data['page_header_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_top_panel", 'Top Panels', self::$data['page_top_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_bottom_panel", 'Bottom Panels', self::$data['page_bottom_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_footer_panel", 'Footer Panels', self::$data['page_footer_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            ));
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php

    }
}