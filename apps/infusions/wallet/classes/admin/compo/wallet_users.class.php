<?php

namespace PHPFusion\Infusions\Wallet\Classes\Admin\Compo;

use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;

/**
 * Class Wallet_Users
 * Note: Verification System and Wallet Deletion is not developed yet
 *
 * @package PHPFusion\Infusions\Wallet\Classes\Admin\Compo
 */
class Wallet_Users extends Wallet_Model {

    private $user = [
        "wallet_id"  => '',
        "user_id"    => '',
        "first_name" => '',
        "last_name"  => '',
        "country"    => '',
        "region"     => '',
        "city"       => '',
        "postcode"   => '',
        "address"    => '',
        "address_2"  => '',
        "phone"      => '',
        "fax"        => '',
        "email"      => '',
        "dob_d"      => '',
        "dob_m"      => '',
        "dob_y"      => '',
        "lastupdate" => TIME,
    ];

    /**
     * View administration
     */
    public function __view() {

        $title = "Wallet Accounts Overview";

        $content = '';

        $button = "<div class='pull-right m-l-10'><a href='".clean_request('action=new', ['action', 'walletID'], FALSE)."' class='btn btn-success'>Create New Account</a>\n</div>\n";

        $this->setPage('Wallet Accounts', 'srp_user');

        $page_active = tab_active(self::$page, 'srp_user', 'page');

        switch ($page_active) {
            default:
            case 'srp_user':
                if (check_get("action")) {

                    switch (get("action")) {
                        case "new":

                            $title = 'Create new wallet';

                            $this->user = [
                                "wallet_id" => 0,
                                "user_id"   => "",
                                "verified"  => 0,
                                "status"    => 0,
                                "balance"   => "0.0000",
                                "diamonds"  => "0.0000"
                            ];

                            $content = $this->displayForm();
                            break;

                        case "edit":

                            if ($wallet_id = get("id", FILTER_VALIDATE_INT)) {

                                $result = dbquery("SELECT * FROM ".DB_USER_WALLET." WHERE wallet_id=:id", [":id" => $wallet_id]);

                                if (dbrows($result)) {

                                    $this->user = dbarray($result);

                                    if (dbcount("(validate_id)", DB_USER_WALLET_VERIFICATION, "validate_user_id=:uid AND validate_status=1", [':uid' => $this->user['user_id']])) {

                                        $title = 'User Account Verification for '.$this->get_username();

                                        $content = $this->__verificationForm('User Account Verification for '.$this->get_username());

                                    } else {

                                        $title = "Edit User Wallet";

                                        $content = $this->displayForm();
                                    }

                                } else {
                                    add_notice('warning', 'Invalid Pack ID');

                                    redirect(clean_request('', ['action'], FALSE));
                                }
                            } else {

                                redirect(clean_request('', ['action'], FALSE));
                            }
                            break;
                        case "delete":
                            if ($wallet_id = get("id", FILTER_VALIDATE_INT)) {

                                $title = "Delete wallet";

                                // Need to confirm delete here, it's not coded yet

                            } else {
                                redirect(clean_request('', ['action'], FALSE));
                            }
                            break;
                        default:
                            redirect(clean_request('', ['action'], FALSE));
                    }

                } else {
                    $title = 'Wallet Users';
                    $content = $this->__listUsers();
                }
                break;
        }

        // Display Administration
        add_to_title($title);
        echo "<div class='spacer-md'>".($button.'<h3>'.$title.'</h3>')."</div>";
        echo trim($content);

    }

    /**
     *
     * @return string
     */
    private function displayForm() {

        if (post("cancel")) {
            redirect(clean_request('', ['action', 'edit'], FALSE));
        }

        if (check_post("save_changes")) {

            $userInput = [
                'wallet_id'     => $this->user['wallet_id'],
                'user_id'       => sanitizer('user_id', '', 'user_id'),
                'verified'      => sanitizer('verified', '', 'verified'),
                'wallet_status' => sanitizer('wallet_status', '', 'wallet_status'),
                'updated'       => time(),
            ];

            // Assign to callback value
            $this->user = $userInput;

            $has_wallet = dbcount('(wallet_id)', DB_USER_WALLET, "wallet_id=:id", [":id" => (int)$this->user['wallet_id']]);

            if (fusion_safe()) {

                if ($this->user['wallet_id'] && $has_wallet) {

                    $id = $this->user['wallet_id'];
                    //print_P($this->user);
                    dbquery_insert(DB_USER_WALLET, $this->user, 'update');
                    add_notice('success', 'User wallet has been updated');

                } else {

                    $id = dbquery_insert(DB_USER_WALLET, $this->user, 'save');
                    add_notice('success', 'User wallet has been created');
                }

                $redirect_url = (check_post("save_changes") == 'close' ? clean_request('page=srp_user', ['action', 'walletID'], FALSE) :
                    clean_request('page=srp_user&action=edit&walletID='.$id, ['action', 'walletID'], FALSE));

                redirect($redirect_url);
            }
        }

        // resets
        $html = openform("user_form", "post");

        $html .= form_hidden("wallet_id", '', $this->user['wallet_id']);

        $html .= form_user_select("user_id", "Wallet User", $this->user['user_id'], [
            "inline"      => TRUE,
            'inner_width' => '100%',
            'max_select'  => 1,
            'required'    => TRUE,
            'deactivate'  => ($this->user["user_id"] ? TRUE : FALSE),
        ]);

        $html .= form_select('verified', 'Status Verification', $this->user['verified'], ['inline' => TRUE, 'options' => $this->get_membership_status()]);

        $html .= form_select("wallet_status", "Status", $this->user["wallet_status"], [
            "inline"  => TRUE,
            "options" => [
                0 => "Disabled",
                1 => "Enabled",
            ],
            "width"   => "250px"
        ]);

        $html .= form_text("gold_balance", "Gold Coins", $this->user["gold_balance"], ["inline" => TRUE, "append" => TRUE, "append_value" => "Coins", "deactivate" => TRUE]);

        $html .= form_text("diamond_balance", "Diamonds", $this->user["diamond_balance"], ["inline" => TRUE, "append" => TRUE, "append_value" => "Coins", "deactivate" => TRUE]);

        if (!empty($this->user["validate_image"]) && file_exists($this->user["validate_image"])) {
            $html .= "<h4>Validation Document (Click Image to Enlarge)</h4>\n<hr/>\n";
            $html .= colorbox($this->user["validate_image"], "Document for Validation", TRUE, "img-thumbnail");
        }

        $html .= "<hr/>\n";

        $html .= form_button("cancel", "Cancel", "cancel", ["class" => "btn-default m-r-10"]);

        $html .= form_button("save_changes", $this->user["wallet_id"] ? "Update Wallet" : "Create Wallet", "save", ["class" => "btn-primary m-r-10"]);

        $html .= form_button("save_changes", $this->user["wallet_id"] ? "Update and Close" : "Create Wallet & Close", "close", ["input_id" => "save_2", "class" => "btn-success"]);

        $html .= closeform();

        return (string)$html;
    }

    /**
     * Get the user name for a specific wallet account
     *
     * @return mixed|null|string
     */
    private function get_username() {
        if (!empty($this->user['type'])) {
            if ($this->user['type'] == 1 && !empty($this->user['company'])) {
                // company
                return $this->user['company'];
            } else if (!empty($this->user['first_name']) && !empty($this->user['last_name'])) {
                // individual
                return $this->user['first_name'].' '.$this->user['last_name'];
            }

            return NULL;
        }

        return NULL;
    }

    /**
     * @param $title
     *
     * @return string
     *
     *  Status for Verification              1 (Apply), 2 (Approve), 3 (Reject)
     * @todo: PENDING FUTURE UPGRADE
     */
    private function __verificationForm($title) {

        if (isset($_POST['cancel'])) {
            redirect(clean_request('', ['action', 'walletID'], FALSE));
        }
        $data = [];
        $verification_result = dbquery("SELECT * FROM ".DB_USER_WALLET_VERIFICATION." WHERE validate_user_id=:uid", [':uid' => $this->user['user_id']]);
        if (dbrows($verification_result)) {
            $data = dbarray($verification_result);
            if (!empty($data['validate_data'])) {
                $cdata = \Defender::decode($data['validate_data']);
                $cdata['validate_image'] = WALLET.'attachments/'.$data['validate_filename'];
            }
        }

        if (isset($_POST['save_changes'])) {
            $cdata = [
                "wallet_id" => $this->user['wallet_id'],
                "type"      => form_sanitizer($_POST['type'], "", "type"),
                "country"   => form_sanitizer($_POST['country'], "", "country"),
                "address"   => form_sanitizer($_POST['address'], "", "address"),
                "address_2" => form_sanitizer($_POST['address_2'], "", "address_2"),
                "address_3" => form_sanitizer($_POST['address_3'], "", "address_3"),
                "city"      => form_sanitizer($_POST['city'], "", "city"),
                "region"    => form_sanitizer($_POST['region'], "", "region"),
                "postcode"  => form_sanitizer($_POST['postcode'], "", "postcode"),
                "mobile"    => form_sanitizer($_POST['mobile'], "", "mobile"),
                "phone"     => form_sanitizer($_POST['phone'], "", "phone"),
            ];
            if ($cdata['type'] == 2) {
                $cdata['company'] = form_sanitizer($_POST['company'], "", "company");
                $cdata['company_no'] = form_sanitizer($_POST['company_no'], "", "company_no");
            } else {
                $cdata['first_name'] = form_sanitizer($_POST['first_name'], "", "first_name");
                $cdata['last_name'] = form_sanitizer($_POST['last_name'], "", "last_name");
                $cdata['identity_no'] = form_sanitizer($_POST['identity_no'], "", "identity_no");
            }
            if (fusion_safe()) {
                $cdata['verified'] = 1;
                $cdata['lastupdate'] = TIME;
                dbquery_insert(DB_USER_WALLET, $cdata, "update");
                add_notice("success", "The Account has been Verified");
                // set the status to 1
                dbquery("UPDATE ".DB_USER_WALLET_VERIFICATION." SET status=:status WHERE user_id=:user_id", [':status' => 2, ':user_id' => $this->user['user_id']]);
                redirect(FUSION_REQUEST);
            }
        }

        if (isset($_POST['reject_application'])) {
            $reject_data = [
                "validate_id"        => $data['validate_id'],
                "validate_status"    => 3,
                "validate_code"      => form_sanitizer($_POST['validate_code'], "", "validate_code"),
                "validate_message"   => form_sanitizer($_POST['validate_message'], "", "validate_message"),
                "validate_datestamp" => TIME,
            ];
            dbquery_insert(DB_USER_WALLET_VERIFICATION, $reject_data, "update");

            add_notice("success", "User verification application has been rejected.");

            redirect(clean_request("", ["action", "walletID"], FALSE));
        }

        $html = openform("user_form", "POST");

        $html .= form_hidden("wallet_id", '', $this->user['wallet_id']);

        $html .= "<div class='clearfix'>\n";

        $html .= "<h4>$title</h4></div>";

        $html .= "<hr/>";

        $html .= form_checkbox('type', 'Account Type', $cdata['type'], ['inline' => TRUE, 'options' => $this->get_fusion_membership(), 'type' => 'radio', "class" => "well", "deactivate" => TRUE]);

        if (!empty($cdata['validate_image']) && file_exists($cdata['validate_image'])) {
            $html .= "<h4>Validation Document (Click Image to Enlarge)</h4>\n<hr/>\n";
            $html .= colorbox($cdata['validate_image'], 'Document for Validation', TRUE, "img-thumbnail");
        }

        // Buttons
        $html .= form_button('cancel', 'Cancel', 'cancel', ["class" => "btn-default m-r-10"]);

        $html .= form_button("save_changes", 'Accept Verification', 'save', ["class" => "btn-default m-r-10", 'input_id' => "bsave"]);

        $html .= form_button("reject_changes", "Reject Verification", 'close', ['input_id' => 'reject', "type" => "button", "class" => "btn-danger"]);

        $html .= closeform();

        $modal = openmodal("rva", "<h4 class='m-0'>Confirm Reject Verification</h4>", ['hidden' => TRUE]);
        $modal .= openform("rejectfrm", "post", FUSION_REQUEST);
        $modal .= form_checkbox("validate_code", "Please explain the reason for the rejection", "", ['required' => TRUE, "options" => $this->get_validate_code(), "width" => "100%", "inner_width" => "100%", "type" => 'radio']);
        $modal .= form_textarea("validate_message", "Please explain the reason for the rejection", "", ['required' => TRUE, "placeholder" => "Please state reason for rejection...", "autosize" => TRUE]);
        $modal .= modalfooter(form_button('reject_application', "Confirm Document Rejection", "reject_application", ["class" => "btn-danger"]));
        $modal .= closeform();
        $modal .= closemodal();
        add_to_footer($modal);
        add_to_jquery("
        $('button#reject, button#reject2').bind('click', function(e) {
            $('#rva-Modal').modal('show');
        });
        ");

        return $html;
    }

    /**
     * @return string
     */
    private function __listUsers() {

        $table_id = fusion_table("userwallet_list", [
            "remote_file"  => INFUSIONS."wallet/api/?api=user-list",
            "columns"      => [
                ["data" => "wallet_id", "width"=>"150px"],
                ["data" => "user_name"],
                ["data" => "user_lastvisit"],
                ["data" => "last_purchased"],
                ["data" => "wallet_status"],
                ["data" => "gold_balance"],
                ["data" => "diamond_balance"]
            ],
            "server_side"  => TRUE,
            "processing"   => TRUE,
            "empty_locale" => "There are no user wallet found",
            "debug"        => FALSE,
        ]);

        $html = "<table class='table table-bordered' id='$table_id'>
        <thead>               
            <tr>
                <th>Login ID</th>               
                <th>Login Name</th>                 
                <th>Last Active</th>                
                <th>Last Purchase</th>
                <th>Status</th>
                <th>Gold Balance</th>
                <th>Diamond Balance</th>
            </tr>
        </thead>
        <tbody></tbody>
        </table>";

        return $html;
    }

}
