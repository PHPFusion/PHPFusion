<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: acp_theme.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

define('INSPIRE', THEMES.'admin_themes/Inspire/');
define('INSPIRE_TEMPLATES', THEMES.'admin_themes/Inspire/templates/');
define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);
ini_set('display_errors', 1);
define("THEME_BODY", '<body class="fixed-sidebar">');

function opentable($title = '', $class = FALSE) {
    return Inspire\AdminPanel::opentable($title, $class);
}

function closetable($title = '', $class = FALSE) {
    return Inspire\AdminPanel::closetable($title, $class);
}

function openside($title = '', array $links = [], array $options = []) {
    return Inspire\AdminPanel::openside($title, $links, $options);
}

function closeside() {
    return Inspire\AdminPanel::closeside();
}

function opensidex($title = '', array $links = [], array $options = []) {
    return Inspire\AdminPanel::opensidex($title, $links, $options);
}

function closesidex($title = '') {
    return Inspire\AdminPanel::closesidex($title);
}

function render_admin_login() {
    return Inspire\Controller::Instance(FALSE)->do_login_panel();
}

function render_admin_panel() {
    return Inspire\Controller::Instance(FALSE)->do_admin_panel();
}

function render_admin_dashboard() {
    Inspire\Controller::Instance(FALSE)->do_admin_dashboard();
}

/* Main Scripts */
add_to_footer("<script src='".INSPIRE."templates/assets/js/popper.min.js'></script>");
add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/metisMenu/jquery.metisMenu.js'></script>");
add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/slimscroll/jquery.slimscroll.min.js'></script>");
add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/feathericons/feather.min.js'></script>");

/* Flot Charts */
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/flot/jquery.flot.js'></script>");
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/flot/jquery.flot.tooltip.min.js'></script>");
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/flot/jquery.flot.spline.js'></script>");
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/flot/jquery.flot.resize.js'></script>");
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/flot/jquery.flot.pie.js'></script>");
/* Chart JS */
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/chartJs/Chart.min.js'></script>");
/* Piety Charts */
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/peity/jquery.peity.min.js'></script>");
/* Small Line Charts */
add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/sparkline/jquery.sparkline.min.js'></script>");

/* Custom Plugin */
add_to_footer("<script src='".INSPIRE."templates/assets/js/inspire.js'></script>");
add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/pace/pace.min.js'></script>");
// Pop up notifications
add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/gritter/jquery.gritter.min.js'></script>");
//add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/jquery-ui/jquery-ui.min.js'></script>");
add_to_head("<link rel='stylesheet' href='".INSPIRE."templates/assets/css/plugins/toastr/toastr.min.css'>");
add_to_footer("<script src='".INSPIRE."templates/assets/js/plugins/toastr/toastr.min.js'></script>");
add_to_footer("<script src='".INCLUDES."jquery/jquery.cookie.js'></script>");
//add_to_jquery("
//setTimeout(function() {
//    toastr.options = {
//        closeButton: true,
//        progressBar: true,
//        showMethod: 'slideDown',
//        timeOut: 3000
//    };
//    toastr.success('Welcome back ".fusion_get_userdata('user_name')."!', 'PHPFusion X Inspire Admin Theme');
//}, 1300);
//");


/*
 *
<!-- Mainly scripts -->
<script>
    $(document).ready(function() {
        // setTimeout(function() {
        //     toastr.options = {
        //         closeButton: true,
        //         progressBar: true,
        //         showMethod: 'slideDown',
        //         timeOut: 4000
        //     };
        //     toastr.success('Responsive Admin Theme', 'Welcome to INSPIRE');
        // }, 1300);


        var data1 = [
            [0,4],[1,8],[2,5],[3,10],[4,4],[5,16],[6,5],[7,11],[8,6],[9,11],[10,30],[11,10],[12,13],[13,4],[14,3],[15,3],[16,6]
        ];
        var data2 = [
            [0,1],[1,0],[2,2],[3,0],[4,1],[5,3],[6,1],[7,5],[8,2],[9,3],[10,2],[11,1],[12,0],[13,2],[14,8],[15,0],[16,0]
        ];
        $("#flot-dashboard-chart").length && $.plot($("#flot-dashboard-chart"), [
                data1, data2
            ],
            {
                series: {
                    lines: {
                        show: false,
                        fill: true
                    },
                    splines: {
                        show: true,
                        tension: 0.4,
                        lineWidth: 1,
                        fill: 0.4
                    },
                    points: {
                        radius: 0,
                        show: true
                    },
                    shadowSize: 2
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    tickColor: "#d5d5d5",
                    borderWidth: 1,
                    color: '#d5d5d5'
                },
                colors: ["#1ab394", "#1C84C6"],
                xaxis:{
                },
                yaxis: {
                    ticks: 4
                },
                tooltip: false
            }
        );

        var doughnutData = {
            labels: ["App","Software","Laptop" ],
            datasets: [{
                data: [300,50,100],
                backgroundColor: ["#a3e1d4","#dedede","#9CC3DA"]
            }]
        } ;


        var doughnutOptions = {
            responsive: false,
            legend: {
                display: false
            }
        };


        var ctx4 = document.getElementById("doughnutChart").getContext("2d");
        new Chart(ctx4, {type: 'doughnut', data: doughnutData, options:doughnutOptions});

        var doughnutData = {
            labels: ["App","Software","Laptop" ],
            datasets: [{
                data: [70,27,85],
                backgroundColor: ["#a3e1d4","#dedede","#9CC3DA"]
            }]
        } ;


        var doughnutOptions = {
            responsive: false,
            legend: {
                display: false
            }
        };


        var ctx4 = document.getElementById("doughnutChart2").getContext("2d");
        new Chart(ctx4, {type: 'doughnut', data: doughnutData, options:doughnutOptions});

    });
</script>

 */


/*
<!-- Custom and plugin javascript -->
<!-- jQuery UI -->
<!-- Sparkline demo data  -->
<script src="js/demo/sparkline-demo.js"></script>
<!-- ChartJS-->
<script src="js/plugins/chartJs/Chart.min.js"></script>
<!-- Toastr -->
<script src="js/plugins/toastr/toastr.min.js"></script>
 */

require_once INCLUDES.'theme_functions_include.php';

spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "Inspire\\AdminPanel" => INSPIRE."theme.adminpanel.php",
        "Inspire\\Controller" => INSPIRE."theme.controller.php",
        "Inspire\\Dashboard"  => INSPIRE."theme.dashboard.php",
        "Inspire\\LoginPanel" => INSPIRE."theme.loginpanel.php",
        "Inspire\\adminApps"  => INSPIRE."theme.apps.php",
        "Inspire\\Viewer"     => INSPIRE."theme.viewer.php",
        "Inspire\\Helper"     => INSPIRE."theme.helper.php",
        //"Inspire\\get_apps" => THEMES."admin_themes/Genesis/drivers/subcontroller/get_apps.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (file_exists($fullPath)) {
            require $fullPath;
        }
    }
});
