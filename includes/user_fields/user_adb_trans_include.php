<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_forum-stat_include.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if ($profile_method == "input") {
    //Nothing here
    $user_fields = '';
    if (defined('ADMIN_PANEL')) { // To show in admin panel only.
        $user_fields = "<div class='well m-t-5 text-center'>Displays Transactions for AddonDB Purchases</div>";
    }
} elseif ($profile_method == "display") {
    if (infusion_exists('forum')) {
        global $userFields;
        $user = $userFields->getUserData();

        require_once INFUSIONS.'wallet/autoloader.php';

        if (isset($_GET['view_bill'])) {
            // then we view the bill here.
            $bill = new \Wallet\Bill();
            $bill->order_user = $user['user_id'];
            $bill->order_id = $_GET['view_bill'];
            $html = $bill->bill_view();
            $html .= "<div class='spacer-sm text-right'>\n";
            $html .= "<a class='btn btn-primary text-white' href='".clean_request('', ['view_bill'], FALSE)."'>Back</a>";
            $html .= "</div>\n";

        } else {
            $result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_user=:user ORDER BY order_datestamp DESC", [':user' => $user['user_id']]);
            if (dbrows($result)) {
                $html = "<table class='table table-striped'><thead><tr>
                    <th>Date</th><th>Bill No.#</th><th>Bill</th><th>This Bill Amount (USD)</th><th>Bill Status</th>
                    </tr></thead><tbody>";
                while ($data = dbarray($result)) {
                    $html .= "<tr>\n";
                    $html .= "<td>".showdate('shortdate', $data['order_datestamp'])."</td>";
                    $html .= "<td><strong><a title='View Bill #".$data['order_id']."' href='".clean_request('view_bill='.$data['order_id'], ['view_bill'], FALSE)."'>".$data['order_id']."</a></strong></td>";
                    $html .= "<td><strong>".$data['order_title']."</strong><br/>".$data['order_description']."</td>";
                    $html .= "<td>$".number_format($data['order_total'],2)."</td>";
                    $html .= "<td>".($data['order_paid'] ? "<span class='text-success'>Successful</span>" : "<span class='text-danger'>Failed</span>")."</td>";
                    $html .= "</tr>\n";
                }
                $html .= "</tbody></table>\n";
            } else {
                $html = "We did not find any orders.";
            }

        }

        $user_fields = array(
            'title' => "Recent Activity",
            'value' => $html
        );
    }
}
