<?php

namespace PHPFusion\Infusions\Wallet\Classes\Admin\Compo;

use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;

class Wallet_Packs extends Wallet_Model {

    /**
     * @return string
     */
    public function __view() {
        $aidlink = fusion_get_aidlink();

        $html = "<div class='row'>";

        $html .= "<div class='col-xs-12 col-sm-12 col-md-6'>";

        $html .= $this->__packForm();

        $html .= "</div><div class='col-xs-12 col-sm-12 col-md-6'>";

        $html .= "<h4>Coin Shop Items</h4>";

        $table_row_html = "";
        $sql = "SELECT p.*,
               (
                   SELECT p1.package_promotion_bonus
                   FROM ".DB_COIN_PACKS." p1
                   WHERE p1.package_promotion = '1' AND (p1.package_promotion_start >= '1' AND p1.package_promotion_end <= '1' AND p1.package_id = p.package_id)
                   OR p1.package_promotion=1 AND (p1.package_promotion_start = '' AND p1.package_promotion_end='' AND p1.package_id=p.package_id)
               ) 'package_promotion_bonus'
        
        FROM ".DB_COIN_PACKS." p
        ORDER BY p.package_datestamp
        ";

        $result = dbquery($sql);

        if (dbrows($result)) {

            while ($pack = dbarray($result)) {

                $status = [
                    0 => "Disabled",
                    1 => "Active"
                ];

                $pack_bonus = (float)$pack["package_promotion_bonus"];

                $pack_value = $this->packagePromotion($pack);

                $edit_link = INFUSIONS."wallet/administration/index.php".$aidlink."&section=ovw_packs&page=srp_packs&action=edit&id=".$pack["package_id"];

                $del_link = INFUSIONS."wallet/administration/index.php".$aidlink."&section=ovw_packs&page=srp_packs&action=del&id=".$pack["package_id"];

                $table_row_html .= "
                <tr>
                <td>$".number_format($pack["package_price"], 2)." Pack<br>ID".$pack["package_id"]." &middot; <a href='$edit_link'>Edit</a> &middot; <a onclick='if(!confirm(\"Delete this coin package?\")){ return false; }' href='$del_link'>Delete</a></td>
                <td>".$status[$pack["package_status"]]."</td>
                <td>".number_format($pack["package_coin_quantity"], 0)."</td>
                <td>".number_format($pack_bonus, 0)."</td>
                <td>".$pack_value."</td>
                <td>".date("j M Y", $pack["package_datestamp"])."</td>
                </tr>
                ";
            }
        }

        $table_id = fusion_table("coinv_table");

        $html .= "<table id='$table_id' class='table table-bordered'><thead><tr>        
        <th>Selling Price</th>                   
        <th>Status</th>
        <th class='min'>Units</th>             
        <th>Bonus</th>        
        <th>Value</th>
        <th>Date</th>
        </tr></thead>
        <tbody>
        ".$table_row_html."        
        </tbody>
        </table>";

        $html .= "</div></div>";

        return $html;
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function packagePromotion($data) {
        if ($data['package_promotion_bonus']) {
            $promotion_value = (self::walletSettings('coin_unit_value') * $data['package_promotion_bonus']);
            $price = $data['package_price']; // first we have 10 dollars
            $discount = $price - $promotion_value / $price * 100;
            $discount_rate = $discount ? number_format($discount, 2) : 0;
            return number_format($data['package_price'] - $promotion_value, self::walletSettings('coin_currency_number_delim'), self::walletSettings("coin_currency_decimal_delim"), self::walletSettings("coin_currency_thousand_delim"))." <span class='text-danger strong'>($discount_rate%)</span>";
        }

        return "--%";
    }

    /**
     * Recount all package prices when we have a coin price change
     *
     * @param $unit_price
     */
    private function __recountPackages($unit_price) {

        $result = dbquery("SELECT `package_id`, `package_coin_quantity` FROM ".DB_COIN_PACKS);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                dbquery("UPDATE ".DB_COIN_PACKS." SET `package_price`=:new_price WHERE `package_id`=:package_id", [
                    ":package_id" => $data['package_id'],
                    ":new_price"  => $unit_price * $data['package_coin_quantity']
                ]);
            }
        }
    }

    /**
     * Coin Package Bundle Editor Form
     *
     * @return string
     */
    private function __packForm() {

        $data = [
            "package_id"              => 0,
            "package_status"          => 1,
            "package_coin_quantity"   => '',
            'package_promotion'       => 0,
            'package_promotion_start' => '',
            'package_promotion_end'   => '',
            'package_promotion_bonus' => '',
        ];
        $title = "Create a New Coin Package";

        if (check_get("action")) {

            switch(get("action")) {
                case "edit":
                    if ($id = get("id", FILTER_VALIDATE_INT)) {

                        $result = dbquery("SELECT * FROM ".DB_COIN_PACKS." WHERE package_id=:id", [':id' => $id]);

                        if (dbrows($result)) {
                            $data = dbarray($result);

                            $title = "Edit current Package: ".$data['package_coin_quantity']." coins";

                        } else {

                            add_notice("danger", "Package Not Found");

                            redirect(clean_request("", ["action", "id"], FALSE));

                        }
                    } else {

                        add_notice("danger", "Invalid Package ID. Package Not Found.");

                        redirect(clean_request("", ["action", "id"], FALSE));
                    }
                    break;
                case "del":
                    if ($id = get("id", FILTER_VALIDATE_INT)) {
                        if (dbcount("(package_id)", DB_COIN_PACKS, "package_id=:id", [":id"=>(int)$id])) {

                            dbquery("DELETE FROM ".DB_COIN_PACKS." WHERE package_id=:id", [":id"=>(int)$id]);
                            add_notice("success", "Coin Package Item has been removed successfully.");
                        } else {
                            add_notice("danger", "Invalid Package ID. Package Not Found.");
                            redirect(clean_request("", ["action", "id"], FALSE));
                        }
                    } else {

                        add_notice("danger", "Invalid Package ID. Package Not Found.");

                        redirect(clean_request("", ["action", "id"], FALSE));
                    }
                    break;
            }

        }

        if (check_post("save_changes") || post("form_id") == 'coinPackageFrm') {

            $data = [
                "package_id"              => form_sanitizer($_POST['package_id'], "", "package_id"),
                "package_coin_quantity"   => form_sanitizer($_POST['package_coin_quantity'], "", "package_coin_quantity"),
                "package_status"          => (check_post("package_status") ? 1 : 0),
                "package_promotion"       => form_sanitizer($_POST['package_promotion'], "", "package_promotion"),
                "package_promotion_start" => form_sanitizer($_POST['package_promotion_start'], "", "package_promotion_start"),
                "package_promotion_end"   => form_sanitizer($_POST['package_promotion_end'], "", "package_promotion_end"),
                "package_promotion_bonus" => form_sanitizer($_POST['package_promotion_bonus'], "", "package_promotion_bonus"),
            ];

            if (fusion_safe()) {

                $data['package_price'] = self::walletSettings("coin_unit_value") * $data['package_coin_quantity'];

                $data['package_datestamp'] = time();

                dbquery_insert(DB_COIN_PACKS, $data, $data['package_id'] ? "update" : "save");

                add_notice("success", "Coin Package has been ".($data['package_id'] ? "updated" : "created")." successfully");

                redirect(clean_request("", ["action", "id"], FALSE));
            }
        }

        $html = "<h4>$title</h4><hr/>";

        $html .= openform('coinPackageFrm', 'POST');

        $html .= form_hidden('package_id', '', $data['package_id'], ['type' => 'number']);

        $html .= "<div class='clearfix'>";

        $html .= "<div class='col-xs-12 col-sm-3 p-l-0'>";

        $html .= "<label class='label-control'>\nPack Status</label>";

        $html .= "</div><div class='col-xs-12 col-sm-9'>";

        $html .= form_checkbox('package_status', 'This Pack is currently being Offered', $data['package_status'], ['reverse_label' => TRUE]);

        $html .= "</div></div>";

        $html .= form_text('package_coin_quantity', 'Coin Quantity', $data['package_coin_quantity'], [
            'width'        => '150px',
            'required'     => TRUE,
            "type"         => "number",
            'number_step'  => 1,
            'number_max'   => '5000',
            'inline'       => TRUE,
            'append'       => TRUE,
            'append_value' => 'Coin(s)'
        ]);

        $html .= "<hr/>";

        $html .= form_select("package_promotion", "Promotion", $data['package_promotion'], [
            'options' => [
                0 => "Disabled",
                1 => "Enabled",
            ],
            "inline"  => TRUE
        ]);
        $html .= form_text("package_promotion_bonus", "Extra Coins Bonus", $data['package_promotion_bonus'], ["inline" => TRUE, "width" => "150px", 'required' => FALSE, "append" => TRUE, "append_value" => "Coin(s)"]);

        $html .= "<div class='clearfix'>";

        $html .= "<div class='col-xs-12 col-sm-3 p-l-0'>";

        $html .= "<label class='label-control'>Promotion Period</label>";

        $html .= "</div><div class='col-xs-12 col-sm-9 p-l-5'>";

        $html .= "<div class='row'><div class='col-xs-12 col-sm-6'>";

        $html .= form_datepicker('package_promotion_start', 'Promotion Starting', $data['package_promotion_start'],
            [
                'reverse_label' => TRUE,
                'join_to_id'    => 'package_promotion_end'
            ]);

        $html .= "</div><div class='col-xs-12 col-sm-6'>";

        $html .= form_datepicker('package_promotion_end', 'Promotion Ending', $data['package_promotion_end'],
            [
                'reverse_label' => TRUE,
                'join_from_id'  => 'package_promotion_start'
            ]);

        $html .= "</div></div>";

        $html .= "</div></div>";

        $html .= "<hr/>";

        $html .= form_button("save_changes", $data['package_id'] ? 'Update Coin Package' : 'Create Coin Package', "save_changes", ["class" => "btn-primary"]);

        $html .= closeform();

        return $html;

    }

}
