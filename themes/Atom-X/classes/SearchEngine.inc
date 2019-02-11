<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X/classes/SearchEngine.inc
| Author: PHP-Fusion Inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace AtomXTheme;

use \PHPFusion\Search\Search_Engine;

class SearchEngine extends Core {
    public function __construct() {
        parent::__construct();

        $this->locale += fusion_get_locale('', LOCALE.LOCALESET.'search.php');

        add_to_jquery('
            $("#atom-menu li .search-btn").click(function (e) {
                e.preventDefault();
                $(".searchbox").slideToggle(600);
                $(this).closest("li").toggleClass("active");
            });
        ');

        echo '<div class="searchbox" style="display: none;"><div class="jumbotron">';
            echo '<h4 style="font-weight: 600;">'.str_replace('[SITENAME]', $this->settings['sitename'], $this->locale['400']).'</h4>';

            echo openform('searchform', 'post', BASEDIR.'search.php');
            echo '<div class="row m-t-20">';
                echo '<div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">';
                    echo form_text('stext', '', urldecode(Search_Engine::get_param('stext')), ['inline' => FALSE, 'placeholder' => $this->locale['401']]);
                echo '</div>';
                echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                    echo form_button('search', $this->locale['search'], $this->locale['search'], ['class' => 'btn-success btn-block', 'icon' => 'fa fa-search']);
                echo '</div>';
            echo '</div>'; // .row

            if (method_exists(Search_Engine::getInstance(), 'load_search_modules')) {
                $reflection = new \ReflectionMethod(Search_Engine::getInstance(), 'load_search_modules');

                if ($reflection->isPublic()) {
                    $modules = Search_Engine::getInstance()->load_search_modules();

                    echo form_checkbox('method', '', Search_Engine::get_param('method'), [
                        'options'        => [
                            'OR'  => $this->locale['403'],
                            'AND' => $this->locale['404']
                        ],
                        'type'           => 'radio',
                        'reverse_label'  => TRUE,
                        'inline_options' => TRUE,
                        'class'          => 'm-b-0'
                    ]);

                    echo '<div class="row">';
                        echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">';
                            echo '<div class="m-b-10"><b>'.$this->locale['405'].'</b></div>';

                            if (!empty($modules['radio_button'])) {
                                foreach ($modules['radio_button'] as $key => $value) {
                                    echo $value;
                                }
                            }

                            echo form_checkbox('stype', $this->locale['407'], Search_Engine::get_param('stype'), [
                                'type'          => 'radio',
                                'value'         => 'all',
                                'onclick'       => 'display(this.value)',
                                'reverse_label' => TRUE,
                                'class'         => 'm-b-0'
                            ]);
                        echo '</div>';

                        echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
                            echo '<div class="m-b-10"><b>'.$this->locale['420'].'</b></div>';

                            $form_elements = $modules['form_elements'];
                            $disabled_status = FALSE;

                            if (isset($form_elements[Search_Engine::get_param('stype')]['disabled'])) {
                                $disabled_status = !empty($form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE;

                                if (Search_Engine::get_param('stype') != 'all') {
                                    $disabled_status = in_array('datelimit', $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE;
                                }
                            }

                            if (Search_Engine::get_param('stype') == 'all') {
                                $disabled_status = TRUE;
                            }

                            echo form_select('datelimit', '', Search_Engine::get_param('datelimit'), [
                                'inner_width' => '150px',
                                'options'     => [
                                    '0'        => $this->locale['421'],
                                    '86400'    => $this->locale['422'],
                                    '604800'   => $this->locale['423'],
                                    '1209600'  => $this->locale['424'],
                                    '2419200'  => $this->locale['425'],
                                    '7257600'  => $this->locale['426'],
                                    '14515200' => $this->locale['427']
                                ],
                                'deactivate'  => $disabled_status
                            ]);

                            echo form_checkbox('fields', $this->locale['430'], Search_Engine::get_param('fields'), [
                                'type'          => 'radio',
                                'value'         => '2',
                                'reverse_label' => TRUE,
                                'input_id'      => 'fields1',
                                'class'         => 'm-b-0',
                                'deactivate'    => (Search_Engine::get_param('stype') != 'all' ? (isset($form_elements[Search_Engine::get_param('stype')]) && in_array("fields1", $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                            ]);

                            echo form_checkbox('fields', $this->locale['431'], Search_Engine::get_param('fields'), [
                                'type'          => 'radio',
                                'value'         => '1',
                                'reverse_label' => TRUE,
                                'input_id'      => 'fields2',
                                'class'         => 'm-b-0',
                                'deactivate'    => (Search_Engine::get_param('stype') != 'all' ? (isset($form_elements[Search_Engine::get_param('stype')]) && in_array("fields2", $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                            ]);

                            echo form_checkbox('fields', $this->locale['432'], Search_Engine::get_param('fields'), [
                                'type'          => 'radio',
                                'value'         => '0',
                                'reverse_label' => TRUE,
                                'input_id'      => 'fields3',
                                'class'         => 'm-b-0',
                                'deactivate'    => (Search_Engine::get_param('stype') != 'all' ? (isset($form_elements[Search_Engine::get_param('stype')]) && in_array('fields3', $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                            ]);
                        echo '</div>';

                        echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                            echo '<div class="m-b-10"><b>'.$this->locale['440'].'</b></div>';
                            echo form_select('sort', '', Search_Engine::get_param('sort'), [
                                'inner_width' => '150px',
                                'options'     => [
                                    'datestamp' => $this->locale['441'],
                                    'subject'   => $this->locale['442'],
                                    'author'    => $this->locale['443']
                                ],
                                'deactivate'  => (Search_Engine::get_param('stype') != 'all' ? (isset($form_elements[Search_Engine::get_param('stype')]) && in_array('sort', $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                            ]);

                            echo form_checkbox('order', $this->locale['450'], Search_Engine::get_param('order'), [
                                'type'          => 'radio',
                                'value'         => '0',
                                'reverse_label' => TRUE,
                                'input_id'      => 'order1',
                                'class'         => 'm-b-0',
                                'deactivate'    => (Search_Engine::get_param('stype') != 'all' ? (isset($form_elements[Search_Engine::get_param('stype')]) && in_array('order1', $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                            ]);

                            echo form_checkbox('order', $this->locale['451'], Search_Engine::get_param('order'), [
                                'type'          => 'radio',
                                'value'         => '1',
                                'reverse_label' => TRUE,
                                'input_id'      => 'order2',
                                'class'         => 'm-b-0',
                                'deactivate'    => (Search_Engine::get_param('stype') != 'all' ? (isset($form_elements[Search_Engine::get_param('stype')]) && in_array('order2', $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                            ]);
                        echo '</div>';

                        echo '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">';
                            echo '<div class="m-b-10"><b>'.$this->locale['460'].'</b></div>';
                            echo form_select('chars', '', Search_Engine::get_param('chars'), [
                                'inner_width' => '150px',
                                'options'     => [
                                    '50'  => '50',
                                    '100' => '100',
                                    '150' => '150',
                                    '200' => '200'
                                ],
                                'deactivate'  => (Search_Engine::get_param('stype') != 'all' ? (isset($form_elements[Search_Engine::get_param('stype')]) && in_array('chars', $form_elements[Search_Engine::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                            ]);
                        echo '</div>';
                    echo '</div>';
                }
            }

            echo closeform();
        echo '</div></div>';
    }
}
