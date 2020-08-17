<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: charts_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/**
 * Charts.JS API
 * https://www.chartjs.org/docs/latest/getting-started/usage.html
 */
class Charts {

    private $options = [];
    private $chart_id = '';

    private static $default_chart_config = [
        "type"          => "line",
        "responsive"    => "true",
        "display_title" => "false",
        "title"         => "",
        "stack"         => "false",
        "begin_at_zero" => "true",
    ];

    private static $default_line_data = [
        "backgroundColor"           => "#000", // The Line fill color
        'borderCapStyle'            => 'butt', // Cap Style of the line
        'borderColor'               => 'rgba(0,0,0,.1)', // The Line color
        'borderDash'                => [], // number - Length and spacing of dashes
        'borderDashOffset'          => '0.0', // number - Offset for line dashes
        'borderJoinStyle'           => 'miter', //Line joint style
        'borderWidth'               => 3, //number // The line width
        //'cubicInterpolationMode'    => 'default', // 'default' - pleasant curves,
        //                                                  'monotone' - precision
        'fill'                      => true, // How the fill the area under the line
        //'label'                     => '',
        'lineTension'               => '0.4', // Bezier curve tension of the line. Set to 0 to draw straightlines. This option is ignored if monotone cubic interpolation is used.
        'pointBackgroundColor'      => 'rgba(0,0,0,.1)',
        'pointBorderColor'          => 'rgba(0,0,0,.1)',
        'pointBorderWidth'          => '1', // number
        'pointHitRadius'            => '1', // number
        'pointHoverBackgroundColor' => '', //Point background color when hovered.
        'pointHoverBorderColor'     => '', //Point border color when hovered.
        'pointHoverBorderWidth'     => '1', //Border width of point when hovered.
        'pointHoverRadius'          => '4', //The radius of the point when hovered.
        'pointRadius'               => '3',
        'pointRotation'             => '0',
        'pointStyle'                => 'circle',
        'showLine'                  => 'true', //If false, the line is not drawn for this dataset.
        //'spanGaps'                  => 'true', //If true, lines will be drawn between points with no or null data. If false, points with NaN data will create a break in the line.
        //'steppedLine'               => 'false', // 'false' (Step interpolation), 'true' - Step-before Interpolation, 'before' - Ste-before Interpolation, 'after' - Step-after interpolation, 'middle' - Step-middle interpolation
        //'xAxisID'                   => '', // first x axis
        //'yAxisID'                   => '', // first y axis

    ];

    private $categories = [];
    private $chart_data = [];
    private $type = 'line';

    public function __construct($type) {
        $this->type = $type;
    }

    /**
     * @param string $key
     * @param array  $config_options Chart Configuration Options
     *
     * @return object
     */

    /**
     * @param array $values
     */
    public function set_categories(array $values) {
        $this->categories = $values;
    }

    /**
     * @param       $title
     * @param array $values
     * @param array $options
     */
    public function set_data($title, array $values, array $options = []) {
        $chart_data['label'] = $title;
        $chart_data['data'] = $values;
        $chart_data += $options;
        if ($this->type == 'line') {
            $chart_data += self::$default_line_data;
        }

        $this->chart_data[] = $chart_data;
    }

    /**
     * Line Chart Graph
     *
     * @param       $key
     * @param array $config_options
     *
     * @return string
     */
    public function display_chart($key, $config_options = []) {
        if (!defined('ChartJs')) {
            add_to_footer("<script src='".INCLUDES."charts/Chart.min.js'></script>");
            add_to_footer("<script src='".INCLUDES."charts/Chart.bundle.min.js'></script>");
            define('ChartJs', TRUE);
        }
        $this->chart_id = $key;
        $this->options = $config_options;
        $this->options += self::$default_chart_config;

        $chart_categories = json_encode($this->categories);
        $chart_data = json_encode($this->chart_data, JSON_PRETTY_PRINT);
        $js = "
        var pfCharts = $('#chart-".$this->chart_id."');
        var chart = new Chart(pfCharts, {
            type: '".$this->type."',
            data: {
                labels: $chart_categories,
                datasets: ".$chart_data."
            },
            options: {
                responsive : true,
                maintainAspectRatio: false,
                title: {
                    display: ".$this->options['display_title'].",
                    text: '".$this->options['title']."',
                },
                ";
        if ($this->type == 'line') {
            $js .= "
                plugins: {
                    filler: {
                        propagate: true,
                    }
                },
                ";
        }
        if ($this->type == 'line' || $this->type == 'bar') {
            $js .= "
            scales: {
                yAxes: [{
                    gridLines: {
                        display:false,
                    },
                    stacked: ".$this->options['stack'].",
                    ticks: {
                        beginAtZero: ".$this->options['begin_at_zero']."
                    }
                }],
                xAxes: [{
                    gridLines: { display: true }
                }]
            }";
        }
        $js .= "}
        });";

        //print_P($js);
        if ($this->chart_id == 'xx') {
            //print_P($js);
        }
        //print_p($chart_data);
        //print_p($js);
        add_to_jquery($js);
        $height = !empty($options['height']) ? $options['height'] : '350px';
        return "<canvas id='chart-".$this->chart_id."' style='min-height:$height;'></canvas>\n";
    }

}
