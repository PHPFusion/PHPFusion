<?php

namespace PHPFusion\Infusions\Wallet\Classes\Admin\Compo;

use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;

class Wallet_Overview extends Wallet_Model {

    public function __view() {

        $net_sales = format_num('8723981');

        $all_sales = format_num('58');

        $total_refund = format_num('98');

        $withdraw_rate = format_num('12500');

        $total_sales = format_num('8723981');

        $aov = format_num('58');

        $orders = format_num('98');

        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-lg-3'><div class='list-group-item'><h3 class='m-0'>$net_sales USD</h3><small class='strong'>Net sales</small></div></div>";
        echo "<div class='col-xs-12 col-lg-3'><div class='list-group-item'><h3 class='m-0'>$all_sales USD</h3><small class='strong'>Total sales</small></div></div>";
        echo "<div class='col-xs-12 col-lg-3'><div class='list-group-item'><h3 class='m-0'>$total_refund USD</h3><small class='strong'>Refund rate</small></div></div>";
        echo "<div class='col-xs-12 col-lg-3'><div class='list-group-item'><h3 class='m-0'>$withdraw_rate USD</h3><small class='strong'>Withdraw rate</small></div></div>";
        echo "</div>\n";

        echo "<div class='list-group-item m-b-20'>";
        echo "<h3 class='m-0 display-inline-block m-r-10'>Notifications</h3><small>You have 4 new notifications requiring your attention</small>";
        echo "</div>";

        echo "<div class='list-group-item m-b-20'>";
        echo "<h3 class='spacer-md m-t-10'>Sales overview</h3>";
        echo "<div class='row'>
        <div class='col-xs-12 col-sm-6'>
            <div class='row'>
                <div class='col-xs-12 col-lg-4'><h3 class='m-b-0'>$total_sales USD</h3><small>Total sales</small></div>
                <div class='col-xs-12 col-lg-4'><h3 class='m-b-0'>$aov USD</h3><small>AOV (Average per order)</small></div>
                <div class='col-xs-12 col-lg-4'><h3 class='m-b-0'>$orders</h3><small>Number of orders</small></div>      
        </div>
        </div><div class='col-xs-12 col-sm-6'>
            <div class='pull-right'>
            ".form_select('switch_cat', '', '', ['options'=>['0' => 'All categories'], 'select2_disabled'=>TRUE])."
            </div>
        </div></div>";

        //include INCLUDES.'charts/charts_include.php';
        //
        //$graph = new \FusionCharts\Charts('line');
        //$graph->set_categories(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
        //$graph->set_data('September 2019', [
        //    ['x'=>1, 'y'=>1],
        //    ['x'=>2, 'y'=>2],
        //    ['x'=>3, 'y'=>6],
        //    ['x'=>4, 'y'=>2],
        //    ['x'=>5, 'y'=>3],
        //    ['x'=>6, 'y'=>0],
        //    ], [
        //        'borderColor' => 'rgba(88,201,244, 1)', //88:201:244
        //        'backgroundColor' => 'rgba(221,245,255,.8)', //221:245:255
        //]);
        //$graph->set_data('August 2019', [
        //    ['x'=>1, 'y'=>2],
        //    ['x'=>2, 'y'=>3],
        //    ['x'=>3, 'y'=>7],
        //    ['x'=>4, 'y'=>1],
        //    ['x'=>5, 'y'=>5],
        //    ['x'=>6, 'y'=>6],
        //], [
        //    'borderColor' => 'rgba(17,200,120, 1)', //224:247:237
        //    'backgroundColor' => 'rgba(224,247,237,.8)', //221:245:255
        //]);

        echo "<div style='height:250px; margin:25px 0;'>\n";
        //echo $graph->display_chart('example', []);
        echo "</div>\n";

        echo "</div>\n";

        echo "<div class='row'><div class='col-xs-12 col-lg-8'>\n";
        echo "<div class='list-group-item'>";
        echo "<h3 class='spacer-md m-t-10'>Subscriptions</h3>";
        //$graph = new \FusionCharts\Charts('bar');
        //$graph->set_categories(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
        //$graph->set_data('New', [
        //    ['x'=>1, 'y'=>1],
        //    ['x'=>2, 'y'=>2],
        //    ['x'=>3, 'y'=>6],
        //    ['x'=>4, 'y'=>2],
        //    ['x'=>5, 'y'=>3],
        //    ['x'=>6, 'y'=>0],
        //], [
        //    'borderColor' => 'rgba(30,20,30, 0.4)',
        //    'backgroundColor' => 'rgba(53,116,255,1)', //53:116:255
        //]);
        //$graph->set_data('Expired', [
        //    ['x'=>1, 'y'=>1],
        //    ['x'=>2, 'y'=>2],
        //    ['x'=>3, 'y'=>6],
        //    ['x'=>4, 'y'=>2],
        //    ['x'=>5, 'y'=>3],
        //    ['x'=>6, 'y'=>0],
        //], [
        //    'borderColor' => 'rgba(30,20,30, 0.4)',
        //    'backgroundColor' => 'rgba(0,0,0,.1)',
        //]);
        echo "<div style='height:250px; margin:25px 0;'>\n";
        //echo $graph->display_chart('dd', []);
        echo "</div>\n";
        echo "</div>";

        echo "</div><div class='col-xs-12 col-lg-4'>\n";

        echo "<div class='list-group-item'>";
        echo "<h3 class='spacer-md m-t-10'>Customers</h3>";
        //$graph = new \FusionCharts\Charts('doughnut');
        //$graph->set_categories(['Inactive', 'Active']);
        //$graph->set_data('Inactive', ['70'], ['backgroundColor'=> 'rgba(17,200,120,1)']);
        //$graph->set_data('Active', ['30']);
        echo "<div style='height:250px; margin:25px 0;'>\n";
        //echo $graph->display_chart('users', []);
        echo "</div>";
        echo "</div>\n";

        echo "</div>\n</div>\n";
    }

}
