<?php
## Charting IO Components
// PHP-Fusion V8
// coded by hien

function easypie($id, $percent, $title, $label_class, $animate=false, $barColor=false, $trackColor=false, $scaleColor=false, $lineCap=false, $lineWidth=false, $size=false, $timeout=false) {
    if (!defined("easypie")) {
        add_to_head("<script src='".DYNAMICS."charts/easy-pie-chart/jquery.easy-pie-chart.js'></script>\n");
        add_to_head("<link href='".DYNAMICS."charts/easy-pie-chart/jquery.easy-pie-chart.css' rel='stylesheet' type='text/css' media='screen' />");
        define("easypie",true);
    }
    /**
     * Easy pie chart by Rendro on http://rendro.github.io/easy-pie-chart
     * Full API implemented.
     **/

    if (isset($id) && ($id !=="")) { $id = stripinput($id); } else { $id = ""; }
    if (isset($percent) && ($percent !="")) { $percent = stripinput($percent); } else { $percent = ""; }
    if (isset($animate) && ($animate !="")) { $animate = "animate: '".stripinput($animate)."'"; } else { $animate = ""; }
    if (isset($title) && ($title !="")) { $title = stripinput($title); } else { $title = ""; }
    if (isset($label_class) && ($label_class !="")) { $label_class = stripinput($label_class); } else { $label_class = ""; }
    if (isset($barColor) && ($barColor !="")) { $barColor = ", barColor: '".stripinput($barColor)."'"; } else { $barColor = ""; }
    if (isset($trackColor) && ($trackColor !="")) { $trackColor = ", trackColor: '".stripinput($trackColor)."'"; } else { $trackColor = ""; }
    if (isset($scaleColor) && ($scaleColor !="")) { $scaleColor = ", scaleColor: '".stripinput($scaleColor)."'"; } else { $scaleColor = ""; }
    $lineCap_js = "";
    if (isset($lineCap) && ($lineCap !=="")) { $lineCap = stripinput($lineCap);  } else { $lineCap = "";  }
    if ($lineCap =="1") { $lineCap_js = ", lineCap : 'round'"; }
    if ($lineCap =="2") { $lineCap_js = ", lineCap : 'butt'"; }
    if ($lineCap =="3") { $lineCap_js = ", lineCap : 'square'"; }

    if (isset($size) && ($size !="")) { $size = "size: ".stripinput($size).","; $css_size = "style='width:".$size."px'";  } else { $size = ""; $css_size="style='width:110px'"; }
    if (isset($lineWidth) && ($lineWidth !="")) { $lineWidth = ", lineWidth: '".stripinput($lineWidth)."'"; } else { $lineWidth = ""; }

    $html ="<div class='text-center' $css_size>\n";
    $html .="<div id='$id' class='chart' data-percent='$percent'>".$percent."%</div>\n";
    $html .="<span class='$label_class' style='margin-top:5px;'>$title</span>\n";
    $html .="</div>\n";

    $html .=add_to_jquery("
    $('#".$id."').easyPieChart({ $animate $barColor $trackColor $scaleColor $lineCap_js $lineWidth $size $lineWidth
    });
    ");

    if (isset($timeout) && ($timeout !="")) {
        $timeout = stripinput($timeout);
        $html .= add_to_jquery("
        setTimeout (function() {
        $(''#".$id."').data('easyPieChart').update(40); }, $timeout);
        });
        ");
    } else { $timeout = ""; }

    return $html;
}


function showchart($id, $primary, $secondary=false, $array=false) {

    if (!defined("xcharts")) {
            define("xcharts", true);
        add_to_head("<script src='".DYNAMICS."charts/xcharts/d3.v3.min.js' charset='utf-8'></script>");
        add_to_head("<script src='".DYNAMICS."charts/xcharts/xcharts.min.js'></script>\n");
        add_to_head("<link href='".DYNAMICS."charts/xcharts/xcharts.css' rel='stylesheet' type='text/css' media='screen' />");
    }

    $vis_type = array(
        "bar" => "bar",
        "cumulative" => "cumulative",
        "line" => "line",
        "line-dotted" => "line-dotted"
    );

    $scale_type = array(
        "0" => "ordinal",
        "1" => "linear",
        "2" => "time",
        "3" => "exponential"
    );

    if (isset($array) && is_array($array)) {
        $axisPaddingLeft = (array_key_exists("axisPaddingLeft",$array)) ? $array['axisPaddingLeft'] : 20;
        $paddingLeft = (array_key_exists("paddingLeft",$array)) ? $array['paddingLeft'] : 30;
        $axisPaddingBottom = (array_key_exists("axisPaddingBottom",$array)) ? $array['axisPaddingBottom'] : 5;
        $paddingBottom = (array_key_exists("paddingBottom",$array)) ? $array['paddingBottom'] : 20;

        $hideY = (array_key_exists("hideY",$array)) ? add_to_head("<style>#$id .axisY { display:none; }</style>") : "";
        $hideX = (array_key_exists("hideX",$array)) ? add_to_head("<style>#$id .axisX { display:none; }</style>") : "";
        $height = (array_key_exists("height",$array)) ? $array['height'] : "200px";
        $x_scale_type = (array_key_exists("x_scale", $array)) ? $scale_type[$array['x_scale']] : $scale_type['0'];
        $y_scale_type = (array_key_exists("y_scale", $array)) ? $scale_type[$array['y_scale']] : $scale_type['1'];
        $type = (array_key_exists("type", $array)) ? $vis_type[$array['type']] : $vis_type['bar'];

    } else {
        $axisPaddingLeft = 20;
        $paddingLeft = 0;
        $axisPaddingBottom = 5;
        $paddingBottom = 20;
        $hideY = "";
        $hideX = "";
        $height = "200px";
        $x_scale_type = $scale_type['0'];
        $y_scale_type = $scale_type['1'];
        $type = $vis_type['bar'];
    }

    $html = "";

    $html .= "<figure style='width:100%; min-height:$height' id='$id'></figure>";
    // demo data
    //new xChart('bar', {"xScale":"ordinal","yScale":"linear","type":"bar",
    // "main":[{"className":".pizza","data":[{"x":"Pepperoni","y":12},{"x":"Cheese","y":8}]}],
    // "comp":[{"className":".pizza","type":"line-dotted","data":[{"x":"Pepperoni","y":10},{"x":"Cheese","y":4}]}]},
    //'#pizza');

    //    'type': '$type',
    $data = " {
    'xScale': '$x_scale_type',
    'yScale': '$y_scale_type',

    'main':";

    $data .= $primary;

    if ($secondary !=="") {
        $data .= ",";
        $data .= "'comp':";
        $data .= $secondary;
    }

    $data .= "}";


    $opts = "{
    paddingLeft : $paddingLeft ,
    axisPaddingLeft : $axisPaddingLeft,
    axisPaddingBottom : $axisPaddingBottom,
    paddingBottom : $paddingBottom
    }";

    $html .= add_to_jquery("
    var $id = new xChart('$type', $data, '#$id', $opts);
    ");

return $html;
//return print_p($secondary);
//return print_p($data);
}

function chart_data($x, $y, $type=false, $class=false) {

    $null_x = array("0"=>"No Data");
    $null_y = array("0"=> "0");

    if (is_array($x)) {
        $rel_x =  (in_array(" ",$x)) ? $null_x : $x;
    } else { $rel_x = $null_x; }

    if (is_array($y)) {
        $rel_y =  (in_array(" ",$y)) ? $null_y : $y;
    } else { $rel_y = $null_y; }

    $class = ($class == "") ? "pizza" : $class;

    $type = ($type !== "") ? $type : "bar";

    $chdata = "[{ 'className' : '.$class',";
    $chdata .= "'type' : '$type',";
    $chdata .= "'data': [";
    $counter = count($rel_x);
    foreach ($rel_x as $arr=>$v) {
        $x_value = $v;
        $y_value = $rel_y[$arr];
        $coma = ($arr == $counter-1 ) ? "":",";
        $chdata .= "{'x': '$x_value', 'y': $y_value }$coma";
    }

    $chdata .= "]";
    $chdata .= "}]";
    return $chdata;
    //return print_p($chdata);
}


?>