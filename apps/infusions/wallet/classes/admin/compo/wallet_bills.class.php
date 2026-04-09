<?php
namespace PHPFusion\Infusions\Wallet\Classes\Admin\Compo;

use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;

/**
 * Class Wallet_Bills
 *
 * @package PHPFusion\Infusions\Wallet\Classes\Admin\Compo
 */
class Wallet_Bills extends Wallet_Model {

    private $transaction = [];

    private $orders = [];


    public function __view() {

        if ($action = get("action")) {
            switch ($action) {
                case "new":
                    echo $this->invoiceForm('Create New Customized Order');
                    break;
                case "edit":
                    // Cannot edit transaction , must edit order.
                    if ($transaction_id = (int)get("id", FILTER_VALIDATE_INT)) {

                        $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_id=:id", [":id" => (int)$transaction_id]);

                        if (dbrows($result)) {

                            $this->transaction = dbarray($result);

                            // Get orders
                            $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_tid=:id", [":id" => $this->transaction["transaction_id"]]);
                            if (dbrows($cresult)) {
                                while ($orders = dbarray($cresult)) {
                                    $this->orders[$orders["order_id"]] = $orders;
                                }
                            }

                            echo $this->invoiceForm('Edit Order Bill #'.$this->transaction['transaction_ref']);

                        } else {

                            add_notice('warning', "Invalid Invoice");

                            redirect(clean_request('', ['action'], FALSE));
                        }
                    }
                    break;
                case "del":
                    if ($id = get("id", FILTER_VALIDATE_INT)) {

                        dbquery("DELETE FROM ".DB_WALLET_ORDERS." WHERE order_tid=:tid", [":tid" => $id]);

                        dbquery("DELETE FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_id=:tid", [":tid" => $id]);

                        add_notice("success", "Invoice has been deleted");

                        redirect(clean_request("", ["aid", "section"], TRUE));

                    } else {

                        add_notice("danger", "No invoice was found.");

                        redirect(clean_request('', ['action'], FALSE));
                    }
                    break;
                default:
                    redirect(clean_request('', ['action'], FALSE));
            }
        } else {
            echo $this->__listOrder();
        }

    }

    private function doUpdateInvoice() {
        $wallet_settings = get_settings("wallet");

        // delete all order or delete selected order
        if (post("order_mode") == "del_selected_order") {

            if ($selected_id = post(["del_id"], FILTER_VALIDATE_INT)) {

                if ($this->transaction["transaction_id"]) {

                    if (count($selected_id) != dbcount("(order_id)", DB_WALLET_ORDERS, "order_tid=:tid", [":tid" => $this->transaction["transaction_id"]])) {

                        $result = dbquery("SELECT order_id, order_tid, transaction_oid 
                        FROM ".DB_WALLET_ORDERS." o
                        LEFT JOIN ".DB_WALLET_TRANSACTIONS." t ON t.transaction_id=o.order_tid
                        WHERE o.order_id IN ('".implode("','", $selected_id)."')");

                        if (dbrows($result)) {

                            while ($data = dbarray($result)) {

                                $transaction_oid = explode(".", $data["transaction_oid"]);

                                if (((int)$key = array_search($data["order_id"], $transaction_oid)) !== FALSE) {
                                    unset($transaction_oid[$key]);
                                }

                                dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_oid=:oids WHERE transaction_id=:tid", [
                                    ":tid"  => $data["order_tid"],
                                    ":oids" => implode(".", $transaction_oid)
                                ]);

                                dbquery("DELETE FROM ".DB_WALLET_ORDERS." WHERE order_id=:id", ["id" => (int)$data["order_id"]]);
                            }

                            $this->updateTransactionTotal($data["order_tid"]);

                            add_notice("success", "The selected orders has been successfully removed successfully.");

                            redirect(FUSION_REQUEST);

                        } else {
                            add_notice("danger", "One of the selected orders does not exist in this invoice!");
                        }

                    } else {
                        // these are all orders. cannot be deleted. it's not supported.
                        add_notice("danger", "You cannot remove all orders. There must be one order at least per invoice.");
                    }
                } else {
                    add_notice("danger", "This invoice is invalid. Please update the invoice first.");
                }
            } else {
                add_notice("danger", "Please select at least an order to be removed.");
            }
        }

        // Save order new order
        if (check_post("add_order")) {
            // Guarantees a transaction exsit
            if (!$this->transaction["transaction_id"]) {

                $this->transaction["transaction_datestamp"] = time();

                $this->transaction["transaction_ref"] = $this->getTransactionReference();

                $this->transaction["transaction_number"] = $this->get_RandomString();

                $this->transaction["transaction_id"] = dbquery_insert(DB_WALLET_TRANSACTIONS, $this->transaction, "save", ["keep_session" => TRUE]);
            }

            $index = post("add_order", FILTER_VALIDATE_INT);

            $order = [
                "order_id"             => sanitizer(["order_id", $index]),
                "order_title"          => sanitizer(["order_title", $index]),
                "order_description"    => sanitizer(["order_description", $index]),
                "order_item_type"      => sanitizer(["order_item_type", $index]),
                "order_item_quantity"  => sanitizer(["order_item_quantity", $index]),
                "order_item_value"     => sanitizer(["order_item_value", $index]),
                "order_item_shippable" => (sanitizer(["order_item_shippable", $index]) ? "Y" : "N"),
                "order_item_tangible"  => (sanitizer(["order_item_tangible", $index]) ? "Y" : "N"),
                "order_item_taxable"   => (sanitizer(["order_item_taxable", $index]) ? "Y" : "N"),
                "order_item_id"        => 0,
                "order_item_tax_rate"  => $wallet_settings["tax_rate"],
                "order_tid"            => $this->transaction["transaction_id"],
                "order_currency"       => "USD",
                "order_ref"            => $this->transaction["transaction_number"], // Invoice No.
                "order_datestamp"      => (int)$this->transaction["transaction_datestamp"],
                "order_edited"         => (int)$this->transaction["transaction_datestamp"],
                "order_user"           => fusion_get_userdata('user_id'),
                "order_edited_user"    => fusion_get_userdata("user_id")
            ];
            // We do not have shipping modules yet, so the cost for shipping will be omitted
            // shipping is 0 for now
            $subtotal = ((int)$order['order_item_quantity'] * (float)$order['order_item_value']) + (float)($order['order_item_tangible'] == 'Y' ? $order['order_total_shipping'] : 0);

            $order['order_total_tax'] = $subtotal * ($order['order_item_tax_rate'] / 100);

            $order['order_total'] = $subtotal + $order['order_total_tax'];

            if (fusion_safe()) {
                $order["order_id"] = dbquery_insert(DB_WALLET_ORDERS, $order, "save", ["keep_session" => TRUE]);

                $this->updateTransactionTotal($order["order_tid"]);

                add_notice("success", "New order added to current invoice successfully.");

                redirect(FORM_REQUEST);
            }
        }

        // Update existing order
        if (check_post("update_order")) {

            $index = post("update_order", FILTER_VALIDATE_INT);

            $order = [
                "order_id"             => sanitizer(["order_id", $index], "", "order_id"),
                "order_title"          => sanitizer(["order_title", $index], "", "order_title"),
                "order_description"    => sanitizer(["order_description", $index], "", "order_description"),
                "order_item_type"      => sanitizer(["order_item_type", $index], "", "order_item_type"),
                "order_item_quantity"  => sanitizer(["order_item_quantity", $index], "", "order_item_quantity"),
                "order_item_value"     => sanitizer(["order_item_value", $index], "", "order_item_value"),
                "order_item_shippable" => (sanitizer(["order_item_shippable", $index]) ? "Y" : "N"),
                "order_item_tangible"  => (sanitizer(["order_item_tangible", $index]) ? "Y" : "N"),
                "order_item_taxable"   => (sanitizer(["order_item_taxable", $index]) ? "Y" : "N"),
                "order_item_id"        => 0,
                "order_item_tax_rate"  => $wallet_settings["tax_rate"],
                "order_tid"            => $this->transaction["transaction_id"],
                "order_currency"       => "USD",
                "order_ref"            => $this->transaction["transaction_number"] // Invoice No.
            ];

            // We do not have shipping modules yet, so the cost for shipping will be omitted
            // shipping is 0 for now
            $subtotal = ((int)$order['order_item_quantity'] * (float)$order['order_item_value']) + (float)($order['order_item_tangible'] == 'Y' ? $order['order_total_shipping'] : 0);

            $order['order_total_tax'] = $subtotal * ($order['order_item_tax_rate'] / 100);

            $order['order_total'] = $subtotal + $order['order_total_tax'];

            if (fusion_safe()) {

                if (dbcount('(order_id)', DB_WALLET_ORDERS, "order_id=:id", [":id" => (int)$order["order_id"]])) {
                    $order['order_edited'] = time();

                    $order['order_edited_user'] = fusion_get_userdata('user_id');

                    dbquery_insert(DB_WALLET_ORDERS, $order, "update", ["keep_session" => TRUE]);

                    // Recalculate Transaction Total Sum
                    $this->updateTransactionTotal($order["order_tid"]);

                    add_notice("success", "Order has been updated successfully.");

                    // link is
                    $redirect_link = INFUSIONS."wallet/administration/index.php".fusion_get_aidlink()."&section=ovw_reports&action=edit&id=".$this->transaction["transaction_id"];

                    redirect($redirect_link);
                } else {
                    add_notice("danger", "Cannot find the order to be updated.");
                }
            }
        }

        // Save transaction
        if (post("form_id") === "transactionFrm") {

            $this->transaction = [
                "transaction_id"        => sanitizer("transaction_id", "", "transaction_id"),
                "transaction_user"      => sanitizer("transaction_user", "", "transaction_user"),
                "transaction_datestamp" => sanitizer("transaction_datestamp", "", "transaction_datestamp"),
                "transaction_file"      => sanitizer("transaction_file", "", "transaction_file"),
                "transaction_status"    => sanitizer("transaction_status", "", "transaction_status"),
                "transaction_oid"       => sanitizer("transaction_oid", "", "transaction_oid"),
            ];

            $this->transaction["transaction_file"] = ltrim(urlencode($this->transaction['transaction_file']), '/');

            $this->transaction["transaction_currency"] = "USD";

            if (!$this->transaction["transaction_oid"]) {
                fusion_stop("Invoice must not be empty. Please add at least an order.");
            }

            if (fusion_safe()) {

                if ($this->transaction["transaction_id"]) {
                    dbquery_insert(DB_WALLET_TRANSACTIONS, $this->transaction, "update");
                    add_notice("success", "Invoice has been updated successfully.");
                } else {
                    $this->transaction["transaction_id"] = dbquery_insert(DB_WALLET_TRANSACTIONS, $this->transaction, "save");
                    add_notice("success", "Invoice has been created successfully.");
                }

                $this->updateTransactionTotal($this->transaction["transaction_id"]);

                redirect(INFUSIONS."wallet/administration/index.php".fusion_get_aidlink()."&section=ovw_reports");
            }
        }
    }

    /**
     * @param $transaction_id
     */
    private function updateTransactionTotal($transaction_id) {

        // Recalculate Transaction ID
        $order_ids = [];
        $result = dbquery("SELECT order_id FROM ".DB_WALLET_ORDERS." WHERE order_tid=:id", [":id" => (int)$transaction_id]);
        if (dbrows($result)) {
            while ($rdata = dbarray($result)) {
                $order_ids[$rdata["order_id"]] = $rdata["order_id"];
            }
        }

        $order_ids = implode(".", $order_ids);

        dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_oid=:oid WHERE transaction_id=:id", [
            ":oid" => $order_ids,
            ":id"  => $transaction_id
        ]);

        // Recalculate Transaction Total Sum
        $total = dbarray(dbquery("SELECT SUM(order_total) 'order_total', SUM(order_total_tax) 'order_total_tax', SUM(order_total_shipping) 'order_total_shipping' 
                    FROM ".DB_WALLET_ORDERS." WHERE order_tid=:tid", [":tid" => $transaction_id]));

        $item_total = $total["order_total"] - $total["order_total_tax"] - $total["order_total_shipping"];

        dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_amount=:total, transaction_item_total=:item_total, transaction_shipping=:shipping_total, transaction_tax=:tax_total WHERE transaction_id=:tid", [
            ":total"          => $total["order_total"],
            ":item_total"     => $item_total,
            ":shipping_total" => $total["order_total_shipping"],
            ":tax_total"      => $total["order_total_tax"],
            ":tid"            => $transaction_id
        ]);
    }


    /**
     * @param array $data
     *
     * @return string
     */
    private function __listOrderForm($data = []) {
        static $index = 0;

        $default_data = [
            "order_id"             => 0,
            "order_title"          => "",
            "order_description"    => "",
            "order_item_type"      => "",
            "order_item_quantity"  => 1,
            "order_item_value"     => "",
            "order_item_shippable" => "",
            "order_item_tangible"  => "",
            "order_item_taxable"   => "",
            "order_total_shipping" => 0,
            "order_paid"           => 0,
            "order_completed"      => 0,
        ];

        $data += $default_data;

        $html = "<tr>";

        $html .= "<td>";

        if ($data["order_id"]) {
            // only show if there is order_id present for deletion.
            $html .= form_checkbox("del_id[]", "", "", ["value" => $data["order_id"], "default_checked" => FALSE, "input_id" => "del_id_".$index]);
        }

        $html .= "</td><td>";

        $html .= form_hidden("order_id[]", "", $data["order_id"], ["input_id" => "order_id_".$index]);

        $html .= form_text("order_title[]", "", $data["order_title"], [
            "placeholder" => "Item Title",
            "required"    => TRUE,
            "input_id"    => "order_title_".$index
        ]);

        $html .= form_textarea("order_description[]", "", $data["order_description"], [
            "placeholder" => "Order Description",
            "required"    => TRUE,
            "input_id"    => "order_description_".$index
        ]);

        $html .= form_text("order_item_type[]", "", $data["order_item_type"], [
            "placeholder" => "Item Ticker",
            "max_length"  => 5,
            "input_id"    => "order_item_type_".$index
        ]);

        if ($data["order_id"]) {

            $html .= form_button("update_order", "Update Order", $index, ["class" => "btn-primary", "input_id" => "save_order_".$index]);

            $html .= form_button("del_order", "Remove Order", $data["order_id"], ["class" => "btn-danger", "input_id" => "del_order_".$index]);
        } else {

            $html .= form_button("add_order", "Save Order", $index, ["class" => "btn-primary", "input_id" => "save_order_".$index]);
        }

        $html .= "</td><td>";

        $html .= form_text("order_item_quantity[]", "", $data['order_item_quantity'],
            [
                'required'     => TRUE,
                'inline'       => TRUE,
                'type'         => 'number',
                'inner_width'  => '200px',
                'append'       => TRUE,
                'append_value' => 'Units',
                "input_id"     => "order_item_quantity_".$index
            ]);

        $html .= "</td><td>";

        $html .= form_text("order_item_value[]", "", $data["order_item_value"],
            [
                'required'      => TRUE,
                'inline'        => TRUE,
                'inner_width'   => '200px',
                'type'          => 'price',
                'prepend'       => TRUE,
                'prepend_value' => "USD$",
                "input_id"      => "order_item_value_".$index
            ]
        );

        $html .= "</td><td>";

        $html .= form_checkbox("order_item_tangible[]", "", $data['order_item_tangible'], [
            "input_id" => "order_item_tangible_".$index
        ]);

        $html .= "</td><td>".form_checkbox("order_item_taxable[]", "", $data["order_item_taxable"], [
                "input_id" => "order_item_taxable_".$index
            ])."</td>";

        $html .= "</td><td>".form_checkbox("order_item_shippable[]", "", ($data['order_total_shipping'] ? 1 : 0), [
                "input_id" => "order_total_shipping_".$index
            ])."</td>";

        $html .= "</td><td>".form_checkbox("order_paid[]", "", ($data["order_paid"] ? 1 : 0), [
                "input_id" => "order_paid_".$index
            ])."</td>";

        $html .= "</td><td>".form_checkbox("order_completed[]", "", ($data["order_completed"] ? 1 : 0), [
                "input_id" => "order_completed_".$index
            ])."</td>";

        $html .= "</td></tr>";

        $index++;

        return $html;
    }

    /*
     * Generate new Bill/ Edit Bill
     */
    private function invoiceForm($title) {

        $default = [
            "transaction_id"        => 0,
            "transaction_user"      => 0,
            "transaction_datestamp" => time(),
            "transaction_status"    => 0,
            "transaction_file"      => "",
            "transaction_oid"       => "",
        ];

        $this->transaction += $default;

        $this->doUpdateInvoice();

        $html = "<h4>$title</h4><hr/>";

        $html .= openform("transactionFrm", "POST");

        $html .= form_hidden("transaction_id", "", $this->transaction["transaction_id"]);

        $html .= form_hidden("transaction_oid", "", $this->transaction["transaction_oid"]);

        $html .= form_user_select("transaction_user", "Bill To", $this->transaction["transaction_user"], [
            "max_select"  => TRUE,
            "inline"      => TRUE,
            "allow_self"  => TRUE,
            "inner_width" => "100%",
            "required"    => TRUE
        ]);

        $html .= form_datepicker("transaction_datestamp", "Date", $this->transaction["transaction_datestamp"], ["required" => TRUE, "inline" => TRUE, "width" => "100%", "inner_width" => "100%"]);

        $html .= form_text("transaction_file", "Checkout Path", urldecode($this->transaction["transaction_file"]), [
            "inline"   => TRUE,
            "prepend"  => TRUE,
            "required" => TRUE,
        ]);

        $html .= form_select("transaction_status", "Invoice Status", $this->transaction["transaction_status"], [
            "inline"      => TRUE,
            "allow_clear" => TRUE,
            "options"     => [
                0 => "Pending",
                1 => "Paid",
                2 => "Pending",
                3 => "Failed"
            ],
        ]);

        $html .= closeform();

        // Now is the dynamic
        $html .= "<table id='orderFormTbl' class='table table-bordered'>";

        $html .= "<thead><tr>
        <th class='min'></th>
        <th>Item/Product Particulars</th>
        <th class='min'>Quantity</th>
        <th class='min'>Price</th>
        <th class='min'>Tangible?</th>
        <th class='min'>Taxable?</th>
        <th class='min'>Shippable?</th>
        <th class='min'>Paid?</th>
        <th class='min'>Delivered?</th></tr>
        </thead>";

        $html .= "<tbody id='bill_table'>";

        $html .= openform("orderfrm", "POST");

        $html .= form_hidden("order_mode", "");

        if (!empty($this->orders)) {
            foreach ($this->orders as $order) {
                $html .= $this->__listOrderForm($order);
            }
        }

        $html .= $this->__listOrderForm();

        $html .= closeform();

        $html .= "</tbody>";

        $html .= "</table>";

        $html .= "<hr>";

        $html .= form_button("cancel", "Cancel", "cancel", ["class" => "btn-default m-r-10"]);

        $html .= form_button("del_invoice", "Delete Invoice", "del_invoice", ["class" => "btn-default m-r-10"]);

        $html .= form_button("del_selected_order", "Delete Selected Order", "del_selected_order", ["class" => "btn-default m-r-10", "icon" => "fas fa-times"]);

        $html .= form_button("save_changes", ($this->transaction["transaction_id"] ? "Update Invoice" : "Create Invoice"), "save", ["class" => "btn-primary m-r-10"]);

        $html .= "</div></div>";

        $html .= closeform();

        $aidlink = fusion_get_aidlink();

        add_to_jquery("
        $('#del_selected_order').bind('click', function(ev) {
        ev.preventDefault;
        let val = $(this).val();
        $('#order_mode').val( val );                
        $('#orderfrm').submit();
        });
        
        $('#cancel').bind('click', function(ev) {
        ev.preventDefault;
        window.location = '".FUSION_SELF.$aidlink."&section=ovw_reports';
        });
        
        $('#del_invoice').bind('click', function(ev) {
        ev.preventDefault;
        window.location = '".FUSION_SELF.$aidlink."&section=ovw_reports&action=del&id=".$this->transaction["transaction_id"]."';                   
        });
        
        $('#save_changes, #save_changes_alt').bind('click', function(ev) {
        ev.preventDefault();
        $('#transactionFrm').submit();        
        });
        ");

        return $html;
    }

    /*
     * View Bill Listing
     */
    private function __listOrder() {

        $html = "<div class='spacer-sm text-right'><a href='".clean_request('action=new', ['action'], FALSE)."' class='btn btn-success'><i class='fas fa-plus m-r-10'></i>Create New Invoice</a></div>";

        $id = fusion_table("userorder_list", [
            "remote_file"  => INFUSIONS."wallet/api/?api=admin-user-order",
            "columns"      => [
                ["data" => "transaction_datestamp"],
                ["data" => "transaction_ref"],
                ["data" => "transaction_user"],
                ["data" => "transaction_method"],
                ["data" => "transaction_amount"],
                ["data" => "transaction_tax"],
                ["data" => "transaction_status"],
            ],
            "ajax_filters" => ["transaction_complete_filter", "transaction_method_filter", "transaction_start_filter", "transaction_end_filter"],
            "server_side"  => TRUE,
            "processing"   => TRUE,
            "empty_locale" => "There are no invoice",
            "debug"        => FALSE,
        ]);

        $html .= "<div class='list-group'>";

        $html .= "<div class='list-group-item' style='display:grid;grid-template-columns: repeat(4,1fr);align-items: center;gap:20px;'>";

        $html .= form_select("transaction_complete_filter", "Completion status", "", [
            "placeholder" => "Choose Complete Status",
            "allowclear"  => TRUE,
            "options"     => [
                0 => "Unpaid",
                1 => "Paid",
                2 => "Failed"
            ],
            "inner_width" => "100%",
            "inline"      => FALSE,
        ]);

        $html .= form_select("transaction_method_filter", "Payment method", "", [
            "placeholder" => "Choose Payment Status",
            "allowclear"  => TRUE,
            "options"     => [
                ""       => "All Types",
                "paypal" => "Paypal",
                "stripe" => "Stripe",
                "credit" => "Gold Coins"
            ],
            "width"       => "auto",
            "inner_width" => "100%",
            "inline"      => FALSE,
        ]);

        $html .= form_datepicker("transaction_start_filter", "From", "", [
            "join_to_id"  => "transaction_end_filter",
            "width"       => "auto",
            "inline"      => FALSE,
            "inner_width" => "100%",
            "placeholder" => "Bill dated from..."
        ]);

        $html .= form_datepicker("transaction_end_filter", "To", "", [
            "join_from_id" => "transaction_start_filter",
            "width"        => "auto",
            "inline"       => FALSE,
            "inner_width"  => "100%",
            "placeholder"  => "Until..."
        ]);

        $html .= "</div>";

        $html .= "</div>";

        $html .= "<table id='$id' class='table table-bordered'>
        <thead>
            <tr>
                <th>Date</th>
                <th>Order Details</th>
                <th>User</th>
                <th>Method</th>
                <th>Tax USD</th>
                <th>Total USD</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody></tbody>
        </table>";


        add_to_jquery("
        $(document).on('dp.change', '#transaction_start_filter_datepicker, #transaction_end_filter_datepicker', function(ev) {
            userorder_listTable.draw();
        });
        ");

        return $html;

    }

}
