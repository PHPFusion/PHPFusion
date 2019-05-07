<?php

class AdminDashboard {

    public function __construct() {
        // drag and drop HTML comes here
        $tpl = \PHPFusion\Template::getInstance("dashboard");
        $tpl->set_text('
        {notice_widget.{
            {%content%}
        }}
        <div class="{[row]}">
        <div class="{[col(100,100,50,34)]}">
        {%column_1_widget%}
        </div><div class="{[col(100,100,50,34)]}">
        {%column_2_widget%}
        </div>
        <div class="{[col(100,100,50,34)]}">
        {%column_3_widget%}
        </div>
        </div>
        ');

        $tpl->set_tag("column_1_widget", $this->getWidget('column_1'));
        $tpl->set_tag("column_2_widget", $this->getWidget('column_2'));
        $tpl->set_tag("column_3_widget", $this->getWidget('column_3'));

        echo $tpl->get_output();
    }

    private  $default_position = [
        'column_1' => ['summary', 'comments'],
        'column_2' => [],
        'column_3' => [],
        'top' => ['welcome']
    ];

    private function getWidget($key) {
        // Fetch column widget
        $widgets = $this->cacheWidget();
        $content = '';
        $widget_position = isset($_SESSION['dashboard_widget'][$key]) ? $_SESSION['dashboard_widget'][$key] : $this->default_position[$key];
        foreach($widget_position as $widget_name) {
            if (isset($widgets[$widget_name])) {
                $content .= $widgets[$widget_name];
            }
        }
        return (string) $content;
    }

    // later move to admin class
    private function cacheWidget() {
        static $widgets = [];

        if (empty($widgets)) {
            $folder_path = ADMIN."dashboard/";
            $wfolders = makefilelist($folder_path, ".|..|index.php|._DS_Store|readme.md", TRUE, "folders");
            if (!empty($wfolders)) {
                foreach($wfolders as $folder) {
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

}

// Add compatibility mode function
/**
 * Template boiler using Bootstrap 3
 */
if (!function_exists('open_sidex')) {
    function open_sidex($title) {
        echo '<div class="sidex list-group">';
        echo '<div class="title list-group-item"><strong>'.$title.'</strong><span class="pull-right"><span class="caret"></span></span></div>';
        echo '<div class="body list-group-item">';
        if (!defined('sidex_js')) {
            define('sidex_js', true);
            add_to_jquery("
            $('body').on('click', '.sidex > .title', function(e) {
                let sidexBody = $(this).siblings('.body'); 
                sidexBody.toggleClass('display-none');
                if (sidexBody.is(':hidden')) {                    
                    $(this).closest('div').find('.pull-right').addClass('dropup');
                } else {
                    $(this).closest('div').find('.pull-right').removeClass('dropup');
                }                                        
            });
            ");
        }
    }
}

if (!function_exists('close_sidex')) {
    function close_sidex() {
        echo '</div></div>';
    }
}
