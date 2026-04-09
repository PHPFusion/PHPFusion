<?php
(defined('IN_FUSION') || exit);

// Display user field input
if ($profile_method == "input") {

    if (iMEMBER) {

        if (defined("ADMIN_PANEL")) {

            $user_fields = "<div class='well'>Transaction Table</div>";

        } else {

            $user_id = fusion_get_userdata("user_id");

            $id = "wallet_transaction_table";

            // Table API
            $request_uri = INFUSIONS."wallet/api/?api=usertrans";
            if ($filter = get("_filter")) {
                $request_uri = $request_uri."&filter=".$filter;
            }

            $fusion_table = fusion_table($id, [
                "zero_locale" => "No transactions found.",
                "remote_file" => $request_uri,
                "order"       => [0, "desc"],
                "columns"     => [
                    ["data" => "transaction_date", "width" => "150px"],
                    ["data" => "transaction_description"],
                    //["data" => "transaction_type", "width" => "200px"],
                    ["data" => "transaction_method", "width" => "150px"],
                    ["data" => "transaction_status", "width" => "100px"],
                    ["data" => "transaction_in", "width" => "100px"],
                    ["data" => "transaction_out", "width" => "100px"],
                ],
                "debug"       => FALSE,
            ]);

            // Make date filter
            $result = dbarray(dbquery("SELECT MIN(order_datestamp) 'start', MAX(order_datestamp) 'end' FROM ".DB_WALLET_ORDERS." 
            WHERE order_user=:uid LIMIT 1", [":uid" => $user_id]));

            if (!empty($result["start"]) && !empty($result["end"])) {

                $datestart = new DateTime();
                $datestart->setTimestamp($result["start"]);
                $dateend = new DateTime();
                $dateend->modify("this month");
                $period = new DatePeriod(
                    $datestart,
                    new DateInterval("P1M"),
                    $dateend
                );

                $periods = iterator_to_array($period);

                krsort($periods);

                $chunked_periods = array_chunk($periods, 12);

                $chunked_datetime = new DateTime();

                foreach ($chunked_periods[0] as $dates) {

                    $current_datetime = clone $dates;

                    $start = $dates->modify("first day of this month")->getTimestamp();

                    $end = $dates->modify("last day of this month")->getTimestamp();

                    $order_count = (int)dbcount("(order_id)", DB_WALLET_ORDERS, "order_user='$user_id' AND (order_datestamp BETWEEN '$start' AND '$end')");

                    $date_array[$start.'-'.$end] = $current_datetime->format('M Y').($order_count ? whitespace("(".$order_count.")") : "");
                }
            }

            $user_fields = "";

            if (isset($date_array)) {
                $user_fields = "<div class='filter'>";
                $user_fields .= form_select($id."_filter", "", get("_filter"), ["options" => $date_array, "inline" => FALSE]);
                $user_fields .= "</div>";
            }

            $user_fields .= "<table id='$fusion_table' class='table table-striped m-t-5'>";

            $user_fields .= "<thead><tr><th>Date</th><th>Transaction Description</th><th>Method</th><th>Status</th><th>Inwards</th><th>Outwards</th></tr></thead><tbody>";

            $user_fields .= "</tbody></table>";

            $redirect_request = clean_request("", ["_filter"], FALSE);

            add_to_jquery(/** @lang JavaScript */ "
            $('#".$id."_filter').bind('change', function(ev) {
            window.location = '".$redirect_request."&_filter=' + $(this).val();
            });
            ");

        }
    }

}
