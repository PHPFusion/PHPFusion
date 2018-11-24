<?php
/**
 * Charts.JS API
 */

class Charts {

    private static $chart_instance = NULL;
    private $options = array();
    private $chart_id = '';

    private static $default_chart_config = [
        "type" => "line",
        "responsive" => "true",
        "display_title" => "false",
        "title" => "",
        "stack" => "false",
        "begin_at_zero" => "false",
    ];
    private static $default_chart_data = [
        "fill" => 'false',
        "backgroundColor" => "#ccc",
        "borderColor" => "#fff",
        "borderWidth" => 1,
    ];

    private $categories = array();
    private $chart_data = array();

    /**
     * @param string $key
     * @param array  $config_options    Chart Configuration Options
     *
     * @return object
     */
    public static function __getInstance($key = "default", $config_options = array()) {
        if (empty(self::$chart_instance[$key])) {
            if (!defined('ChartJs')) {
                add_to_footer("<script src='".INCLUDES."charts/Chart.min.js'></script>");
                add_to_footer("<script src='".INCLUDES."charts/Chart.bundle.min.js'></script>");
                define('ChartJs', true);
            }
            self::$chart_instance[$key] = new Static();
        }

        return (object)self::$chart_instance[$key];
    }

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
    public function set_data($title, array $values, array $options = array()) {
        $chart_data['label'] = $title;
        $chart_data['data'] = $values;
        $chart_data += $options;
        $chart_data += self::$default_chart_data;

        $this->chart_data[] = $chart_data;

    }

    /**
     * Line Chart Graph
     * @return string
     */
    public function display_chart($key, $config_options) {

        $this->chart_id = $key;
        $this->options = $config_options;
        $this->options += self::$default_chart_config;

        $chart_categories = json_encode($this->categories);
        $chart_data = json_encode($this->chart_data, JSON_PRETTY_PRINT);

        $js = "
        var pfCharts = $('#chart-".$this->chart_id."');        
        var chart = new Chart(pfCharts, {
            type: '".$this->options['type']."',
            data: {
                labels: $chart_categories,
                datasets: ".$chart_data."
            },
            options: {
                responsive : true,
                ".($this->options['type'] == 'line' ? "maintainAspectRatio: false," : "")."
                title: {
                    display: ".$this->options['display_title'].", 
                    text: '".$this->options['title']."',
                },
                ";
                if ($this->options['type'] == 'line') {
                    $js .= "
                    scales: {
                        yAxes: [{
                            stacked: ".$this->options['stack'].",
                            ticks: {
                                beginAtZero: ".$this->options['begin_at_zero']."
                            }
                        }]
                    }                    
                    ";
                }
        $js .= "}
        });";

        if ($this->chart_id == 'xx') {
            //print_P($js);
        }
        //print_p($chart_data);
        //print_p($js);
        add_to_jquery($js);
        return "<canvas id='chart-".$this->chart_id."'></canvas>\n";
    }

}