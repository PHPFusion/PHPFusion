<?php
(defined("IN_FUSION") || exit);

function user_wallet_list() {

    require_once INCLUDES."theme_functions_include.php";

    $list = [];
    $rows = 0;
    $max_rows = 0;
    $sql_query = "";

    if (iADMIN) {

        $aidlink = fusion_get_aidlink();

        $rowstart = (int)(post("start", FILTER_VALIDATE_INT) ?: 0);

        $limit = (int)(post("length", FILTER_VALIDATE_INT) ?: 36);

        $search = post(["search", "value"]);

        $columns = [
            "u.user_name LIKE '$search%'",
        ];

        $orderby = "ORDER BY u.user_id";
        $ordering = [];

        if (!empty($_POST["order"])) {
            foreach ($_POST["order"] as $order) {
                $column_index = $order["column"];
                //if (!$column_index or $column_index == 1) {
                //    $column_index = 6;
                //}
                if ($column_name = post(["columns", $column_index, "data"])) {
                    $ordering[] = form_sanitizer($column_name)." ".form_sanitizer($order["dir"]);
                }
            }
            $orderby = "ORDER BY ".implode(",", $ordering);
        }

        $sql_cond = "";

        $table = DB_USER_WALLET." w1 INNER JOIN ".DB_USERS." u ON u.user_id=w1.user_id
        LEFT JOIN ".DB_WALLET_ORDERS." o ON o.order_user=u.user_id";

        $column_sel = "w1.*, u.user_name, u.user_lastvisit, MAX(o.order_datestamp) 'last_purchased'";

        if ($search) {
            $count_cond = "(".implode(" OR ", $columns).")";
            $sql_cond .= " AND $count_cond";
        }

        $rowsearch = "LIMIT $rowstart, $limit";

        $list = [];

        $count_query = "SELECT ".$column_sel." FROM ".$table.whitespace($sql_cond)." GROUP BY w1.wallet_id";

        $max_rows = dbrows(dbquery($count_query));

        $sql_query = "SELECT ".$column_sel." FROM ".$table.whitespace($sql_cond)." GROUP BY w1.wallet_id".whitespace($orderby).whitespace($rowsearch);

        $result = dbquery($sql_query);

        if ($rows = dbrows($result)) {

            while ($data = dbarray($result)) {

                $edit_link = INFUSIONS."wallet/administration/index.php".$aidlink."&amp;section=ovw_acc&amp;action=edit&amp;id=".$data['wallet_id'];

                $list[] = [
                    "wallet_id"       => $data["wallet_id"],
                    "user_name"       => "<a href='$edit_link'>".$data['user_name']."</a>",
                    "user_lastvisit"    => date('j M, Y', $data['user_lastvisit']),
                    "last_updated"    => date('j M, Y', $data['lastupdate']),
                    "last_purchased"  => date('j M, Y', $data['last_purchased']),
                    "wallet_status"   => ($data["wallet_status"] ? "Active" : "Inactive"),
                    "gold_balance"    => number_format($data["gold_balance"], 2),
                    "diamond_balance" => number_format($data["diamond_balance"], 2),
                ];

            }
        }

    }

    header('Content-Type: application/json', TRUE);

    $json = json_encode(['data' => $list, "recordsFiltered" => (int)$max_rows, "recordsTotal" => (int)$rows, "responsive" => TRUE, "sql" => $sql_query], JSON_PRETTY_PRINT);
    echo $json;
}

fusion_add_hook("wallet_filters", "user_wallet_list");
