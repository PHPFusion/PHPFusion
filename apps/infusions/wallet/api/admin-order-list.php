<?php
(defined("IN_FUSION") || exit);

function admin_user_orders() {

    require_once INCLUDES."theme_functions_include.php";

    $list = [];

    $max_rows = 0;

    $rows = 0;

    $sql_query = "";

    if (iADMIN) {

        $aidlink = fusion_get_aidlink();

        $rowstart = (int)(post("start", FILTER_VALIDATE_INT) ?: 0);

        $limit = (int)(post("length", FILTER_VALIDATE_INT) ?: 36);

        $search = post(["search", "value"]);

        $columns = [
            "u.user_name LIKE '$search%' OR t.transaction_ref LIKE '$search%'",
        ];

        $orderby = "ORDER BY o.transaction_datestamp";

        $ordering = [];

        if (!empty($_POST["order"])) {
            foreach ($_POST["order"] as $order) {

                $column_index = $order["column"];

                $order_direction = form_sanitizer($order["dir"]);

                if ($column_name = post(["columns", $column_index, "data"])) {

                    //if ($column_name == "transaction_datestamp") {
                    //    $order_direction = "desc";
                    //}

                    $ordering[] = form_sanitizer($column_name)." ".$order_direction;
                }

            }
            $orderby = "ORDER BY ".implode(",", $ordering);
        }

        $sql_cond = [];

        $table = DB_WALLET_TRANSACTIONS." t          
        INNER JOIN ".DB_USERS." u ON u.user_id=t.transaction_user";

        //$total = ($data['order_item_value'] * $data['order_item_quantity']) + $data['order_total_shipping'];
        //$current_tax = ($data['order_item_taxable'] ? $total * $data['order_item_tax_rate'] / 100 : 0);

        $column_sel = "t.*, u.user_name";

        if ($search) {
            $count_cond = "(".implode(" OR ", $columns).")";
            $sql_cond[] = "$count_cond";
        }

        if (check_post("transaction_complete_filter")) {
            $completed = post("transaction_complete_filter", FILTER_VALIDATE_INT);
            if ($completed === 0 || $completed === 1) {
                $sql_cond[] = "transaction_status='$completed'";
            }
        }

        if (check_post("transaction_method_filter")) {
            $method = post("transaction_method_filter", FILTER_DEFAULT);
            if ($method) {
                $sql_cond[] = "transaction_method='$method'";
            }
        }

        if (check_post("transaction_start_filter") || check_post("transaction_end_filter")) {

            $start_date = (int)sanitizer("transaction_start_filter", "", "transaction_start_filter");

            $end_date = (int)sanitizer("transaction_end_filter", "", "transaction_end_filter");

            if ($start_date && $end_date) {
                $sql_cond[] = "t.transaction_datestamp >=$start_date AND t.transaction_datestamp <=$end_date";
            } else if ($start_date) {
                $sql_cond[] = "t.transaction_datestamp >= $start_date";
            } else if ($end_date) {
                $sql_cond[] = "t.transaction_datestamp <= $end_date";
            }
        }

        $rowsearch = "LIMIT $rowstart, $limit";

        $conditions = "";
        if (!empty($sql_cond)) {
            $conditions = whitespace("WHERE ".implode(" AND ", $sql_cond));
        }

        $list = [];

        $count_query = "SELECT ".$column_sel." FROM ".$table.$conditions." GROUP BY t.transaction_id";

        $max_rows = dbrows(dbquery($count_query));

        $sql_query = "SELECT ".$column_sel." FROM ".$table.$conditions." GROUP BY t.transaction_id".whitespace($orderby).whitespace($rowsearch);

        $result = dbquery($sql_query);

        if ($rows = dbrows($result)) {

            while ($data = dbarray($result)) {

                // Calculate item original price
                //$total = ($data['order_item_value'] * $data['order_item_quantity']) + $data['order_total_shipping'];

                //$current_tax = ($data['order_item_taxable'] ? $total * $data['order_item_tax_rate'] / 100 : 0);

                //$current_total = $total + $current_tax;

                $edit_link = INFUSIONS."wallet/administration/index.php".$aidlink."&amp;section=ovw_reports&amp;action=edit&amp;id=".$data['transaction_id'];

                $delete_link = INFUSIONS."wallet/administration/index.php".$aidlink."&amp;section=ovw_reports&amp;action=del&amp;id=".$data['transaction_id'];

                switch($data["transaction_method"]) {
                    case "paypal":
                        $method = "Paypal";
                        break;
                    case "stripe":
                        $method = "Stripe";
                        break;
                    case "credit":
                        $method = "Gold coins";
                        break;
                    default:
                        $method = "-";
                        break;
                }

                switch($data["transaction_status"]) {
                    case 1:
                        $status = "Paid";
                        break;
                    case 2:
                        $status = "Failed";
                        break;
                    default:
                    case 0:
                        $status = "Unpaid";
                }

                $list[] = [
                    "transaction_datestamp" => date('j M, Y', $data['transaction_datestamp']),
                    "transaction_ref"       => "<a href='$edit_link'>#".$data['transaction_number']."</a><br/><span>".$data["transaction_ref"]." - <a href='$edit_link'>Edit Bill</a> &middot; <a href='$delete_link'>Delete Bill</a></span>",
                    "transaction_user"      => $data["user_name"],
                    "transaction_method"    => $method,
                    "transaction_tax"       => number_format($data["transaction_tax"], 2),
                    "transaction_amount"    => number_format($data["transaction_amount"], 2),
                    "transaction_status"    => $status
                ];

            }
        }
    }

    header('Content-Type: application/json', TRUE);

    $json = json_encode(['data' => $list, "recordsFiltered" => (int)$max_rows, "recordsTotal" => (int)$rows, "responsive" => TRUE, "sql" => $sql_query], JSON_PRETTY_PRINT);

    echo $json;
}

fusion_add_hook("wallet_filters", "admin_user_orders");
