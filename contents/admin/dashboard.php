<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminDashboard.php
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
namespace PHPFusion\Administration;

class Dashboard {


    private $storage_value = [];
    private $default_position = [];

    public function __construct() {
        $this->default_position = [
            'column_1' => json_encode(['summary', 'comments', 'ratings']),
            'column_2' => json_encode(['news']),
            'column_3' => [],
            'top'      => json_encode(['welcome', 'notice'])
        ];

        $this->storage_value = $this->getCurrentSetup();
    }

    /**
     * @return array|null
     */
    private function getCurrentSetup() {
        $current_position = session_get(['dashboard_setup']);
        if (!empty($current_position)) {
            return $current_position;
        }
        return $this->default_position;
    }

    public function showWidget() {
        //unset($_SESSION);
        $info = [
            'column_1_widget' => $this->getWidget('column_1'),
            'column_2_widget' => $this->getWidget('column_2'),
            'column_3_widget' => $this->getWidget('column_3'),
            'column_4_widget' => $this->getWidget('column_4'),
            'top_widget'      => $this->getWidget('top')
        ];
        $locale = fusion_get_locale();
        add_to_footer("<script src='".INCLUDES."jquery/jquery-ui/jquery-ui.min.js'></script>");
        opentable($locale['250'], [
            [
                'id'      => 'screen-options',
                'title'   => 'Screen Options <span class="screen-caret fas fa-caret-down"></span>',
                'link'    => '#screen-options',
                'content' => $this->widgetScreenOptions(),
                'class'   => ''
            ],
            [
                'id'      => 'screen-help',
                'title'   => 'Screen Help <span class="screen-caret fas fa-caret-down"></span>',
                'link'    => '#screen-help',
                'content' => $this->widgetScreenHelp(),
                //'class' => 'active'
            ]
        ]);
        echo fusion_render(THEMES.'templates/global/admin/', 'admin-dashboard.twig', $info, TRUE);
        $this->theRestoftheHTML();

        // Screen options caret show/hide
        $jquery_name = 'dashboard_token';
        $jquery_token = fusion_get_token($jquery_name, 1);
        $path = ADMIN."includes/dashboard_update.php";

        add_to_jquery(/** @lang JavaScript */ "        
        // Sortables
        let col_1 = $('#col1');
        let col_2 = $('#col2');
        let col_3 = $('#col3');
        let col_4 = $('#col4');
        if (!col_1.children().length) {
            col_1.addClass('is-empty');
        }
        if (!col_2.children().length) {
            col_2.addClass('is-empty');
        }
        if (!col_3.children().length) {
            col_3.addClass('is-empty');
        }
        if (!col_4.children().length) {
            col_4.addClass('is-empty');
        }
        $(\"#col1, #col2, #col3, #col4\").sortable({
            connectWith: \".connectList\",
            update: function( event, ui ) {
                let col_1_array = col_1.sortable( \"toArray\" );
                let col_2_array = col_2.sortable( \"toArray\" );
                let col_3_array = col_3.sortable( \"toArray\" );
                let col_4_array = col_4.sortable( \"toArray\" );
                col_1.removeClass('is-empty');
                col_2.removeClass('is-empty');
                col_3.removeClass('is-empty');
                col_4.removeClass('is-empty');
                if (! col_1_array.length) {
                    col_1.addClass('is-empty');
                }
                if (! col_2_array.length) {
                    col_2.addClass('is-empty');
                }
                if (! col_3_array.length) {
                    col_3.addClass('is-empty');
                }
                if (! col_4_array.length) {
                    col_4.addClass('is-empty');
                }

                // Stores items into local storage
                let col_1_setup = window.JSON.stringify(col_1_array);
                let col_2_setup = window.JSON.stringify(col_2_array);
                let col_3_setup = window.JSON.stringify(col_3_array);
                let col_4_setup = window.JSON.stringify(col_4_array);
                window.localStorage.setItem('col_1_setup', col_1_setup);
                window.localStorage.setItem('col_2_setup', col_2_setup);
                window.localStorage.setItem('col_3_setup', col_3_setup);
                window.localStorage.setItem('col_4_setup', col_4_setup);

                $.post('$path', {'fusion_token': '$jquery_token', 'form_id':'$jquery_name', 'dashboard_setup': {
                    'column_1' : col_1_setup,
                    'column_2' : col_2_setup,
                    'column_3' : col_3_setup,
                    'column_4' : col_4_setup,
                    }, function(data) {
                        console.log(data);
                    }
                 });

                $('.output').html(\"Column 1: \" + window.JSON.stringify(col_1_array) + \"<br/>\" + \"Column 2: \" + window.JSON.stringify(col_2_array) + \"<br/>\" + \"Column 3: \" + window.JSON.stringify(col_3_array)+
                    \"<br/>\" + \"Column 4: \" + window.JSON.stringify(col_4_array));
            }
        }).disableSelection();
        ");

        closetable();
    }

    /**
     * @param $column_pos
     *
     * @return array|string
     */
    private function getWidget($column_pos) {
        $widgets = $this->cacheWidget();
        try {
            $content = '';
            if (!empty($this->storage_value[$column_pos])) {
                $widgets_list = json_decode($this->storage_value[$column_pos]);
                foreach ($widgets_list as $widget_name) {
                    if (isset($widgets[$widget_name])) {
                        $content .= $widgets[$widget_name];
                    }
                }
            }
            return (string)$content;
        } catch (\Exception $e) {
            set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), 'Widget Error');
        }
        return [];
    }

    /**
     * @return array
     */
    private function cacheWidget() {
        static $widgets = [];

        if (empty($widgets)) {
            $folder_path = ADMIN."dashboard/";
            $wfolders = makefilelist($folder_path, ".|..|index.php|._DS_Store|readme.md", TRUE, "folders");
            if (!empty($wfolders)) {
                foreach ($wfolders as $folder) {
                    if (is_dir($folder_path.$folder)) {
                        $widget_file_path = $folder_path.$folder.'/'.$folder.'.php';
                        if (is_file($widget_file_path)) {
                            include $widget_file_path;
                            $function_name = "display_".$folder."_widget";
                            if (function_exists($function_name)) {
                                $widgets[$folder] = $function_name();
                            }
                        }
                    }
                }
            }
        }

        return $widgets;
    }

    private function widgetScreenOptions() {

        $widget_options = [
            'summary'  => 'Summary',
            'comments' => 'Comments',
            'ratings'  => 'Ratings',
            'news'     => 'News Quick Draft',
            'welcome'  => 'Welcome',
            'notices'  => 'Notices'
        ];

        add_to_jquery("
        // on check.
        $('#active_widget-field input').bind('change', function(e) {
            let widgetStatus = $(this).is(':checked');
            let widgetName = $(this).val();
        });
        ");

        return form_checkbox('active_widget[]', 'Admin Widgets', $this->getEnabledWidgets(), [
            'input_id'       => 'active_widget',
            'options'        => $widget_options,
            'inline_options' => TRUE,
        ]);
    }

    private function getEnabledWidgets() {
        $enabled = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($this->storage_value['column_'.$i])) {
                $current_array = json_decode($this->storage_value['column_'.$i]);
                $enabled = array_merge($enabled, $current_array);
            }
        }
        return array_flip($enabled);
    }

    private function widgetScreenHelp() {
        return '
        <div class="row">

        </div>
        ';
    }

    // later move to admin class

    private function theRestoftheHTML() {
        global $members, $global_submissions, $infusions_count, $global_infusions, $submit_data, $upgrade_info;
        $aidlink = fusion_get_aidlink();
        $locale = fusion_get_locale();
        $panels = [
            'registered'   => ['link' => '', 'title' => 251],
            'cancelled'    => ['link' => 'status=5', 'title' => 263],
            'unactivated'  => ['link' => 'status=2', 'title' => 252],
            'security_ban' => ['link' => 'status=4', 'title' => 253]
        ];

        echo "<div class='".grid_row()."'>\n";
        echo "<div class='".grid_column_size(100, 100, 50, 33)." responsive-admin-column'>\n";
        // lets do an internal analytics
        // members registered
        // members online
        // members
        echo "<div class='list-group'>\n";
        echo "<div class='list-group-item'><h4 class='m-0'>Users</h4></div>";
        echo "<!--Start Members-->\n";
        echo "<div class='list-group-item'>\n";
        echo "<div class='row'>\n";
        foreach ($panels as $panel => $block) {
            $link_start = '';
            $link_end = '';
            if (checkrights('M')) {
                $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                $link_start = "<a class='text-sm' href='".ADMIN."members.php".$aidlink.$block['link']."'>";
                $link_end = "</a>\n";
            }
            echo "<div class='col-xs-12 col-sm-3'>\n";
            echo "<h2 class='m-0 text-light text-info'>".number_format($members[$panel])."</h2>\n";
            echo "<span class='m-t-10'>".$link_start.$locale[$block['title']].$link_end."</span>\n";
            echo "</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "<!--End Members-->\n";
        echo "<div class='list-group-item'>\n";
        echo "<h4 class='m-0 display-inline-block'>".$locale['283']."</h4> <span class='pull-right badge'>".number_format((int)$infusions_count)."</span>";
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        if ($infusions_count > 0) {
            echo "<div class='comment_content'>\n";
            if (!empty($global_infusions)) {
                foreach ($global_infusions as $inf_id => $inf_data) {
                    echo "<span class='badge m-b-10 m-r-5'>".$inf_data['inf_title']."</span>\n";
                }
            }
            echo "</div>\n";
            echo checkrights("I") ? "<div class='text-right'>\n<a href='".ADMIN."infusions.php".$aidlink."'>".$locale['285']."</a><i class='fas fa-angle-right text-lighter m-l-15'></i></div>\n" : '';
        } else {
            echo "<div class='text-center'>".$locale['284']."</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n<div class='".grid_column_size(100, 100, 50, 33)." responsive-admin-column'>\n";
        echo "<div class='list-group'>\n<div class='list-group-item'>\n";
        echo "<h4 class='display-inline-block m-0'>".$locale['279']."</h4>\n";
        echo "<span class='pull-right badge'>".number_format($global_submissions['rows'])."</span>\n";
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        if (count($global_submissions['data']) > 0) {
            foreach ($global_submissions['data'] as $i => $submit_date) {
                $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                echo "<!--Start Submissions Item-->\n";
                echo "<div data-id='$i' class='submission_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                echo "<div class='pull-left display-inline-block' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($submit_date, "25px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                echo "<strong>".profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status'])." </strong>\n";
                echo "<span class='text-lighter'>".$locale['273b']." <strong>".$submit_data[$submit_date['submit_type']]['submit_locale']."</strong></span><br/>\n";
                echo timer($submit_date['submit_datestamp'])."<br/>\n";
                if (!empty($review_link)) {
                    echo "<a class='btn btn-xs btn-default m-t-5' title='".$locale['286']."' href='".$review_link."'>".$locale['286']."</a>\n";
                }
                echo "</div>\n";
                echo "<!--End Submissions Item-->\n";
            }

            if (isset($global_submissions['submissions_nav'])) {
                echo "<div class='clearfix'>\n";
                echo "<span class='pull-right text-smaller'>".$global_submissions['submissions_nav']."</span>";
                echo "</div>\n";
            }
        } else {
            echo "<div class='text-center'>".$global_submissions['nodata']."</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n<div class='".grid_column_size(100, 100, 50, 33)." responsive-admin-column'>\n";

        echo "</div>\n</div>\n";
    }

}
