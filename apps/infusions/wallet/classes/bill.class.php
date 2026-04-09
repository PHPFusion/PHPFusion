<?php
namespace Wallet;

use PHPFusion\Infusions\Wallet\Classes\Gateways;

class Bill extends Gateways {

    /*
     * You need to set these 3 parameters for the class
     * to function. They are here for the purpose of
     * validation
     */
    public $order_update = FALSE;
    public $order_user = 0;
    public $order_id = 0;
    public $bill = [];

    public $default_bill = [
        'order_id' => 0,
        'order_pay_method' => '',
        'order_pay_method_name' => '',
        'order_pay_method_ref' => '',
        'order_user' => '',
        'order_title' => '',
        'order_type' => '',
        'order_item_id' => '',
        'order_datestamp' => '',
        'order_paid' => 0,
        'order_paid_datestamp' => '',
        'order_completed' => 0,
        'order_completed_datestamp' => '',
        'order_quantity' => '',
        'order_item_value' => '',
        'order_shipping_value' => '',
        'order_tax_value' => '',
        'order_total_value' => '',
        'order_tangible' => '',
        'order_bill' => ''
    ];

    public function insert_bill($redirect_link = '') {
        if ($this->order_update === TRUE && isset($this->order_id) &&  isnum($this->order_id) ) {
            if (dbcount('(order_id)', DB_WALLET_ORDERS, "order_id='".$this->order_id."' AND order_user='".$this->order_user."'")) {
                $this->bill['order_id'] = $this->order_id;
                $this->bill['order_user'] = $this->order_user;
                dbquery_insert(DB_WALLET_ORDERS, $this->bill, 'update', ['keep_session'=>TRUE]);
            } else {
                throw new \Exception('There is no bill found with this order ID with the current user');
            }
        } else {
            $this->order_id = dbquery_insert(DB_WALLET_ORDERS, $this->bill, 'save', ['keep_session'=>TRUE]);
        }
        if ($redirect_link) {
            redirect( $redirect_link );
        } elseif ($this->order_id) {
            return $this->order_id;
        }
    }

    private $bill_view = [];

    protected function get_bill() {
        if ($this->order_id) {
            if (empty($this->bill_view[$this->order_id])) {
                $bill_sql = "SELECT ord.*, it.item_title, it.item_description,
                CONCAT(cust.first_name, ' ', cust.last_name) 'customer_name',
                CONCAT(cust.address, '<br/>', IF(cust.address_2, CONCAT(cust.address_2, '<br>'), ''), cust.city, ', ', cust.region, ',<br/>', cust.country, '- ZIP:', cust.postcode) 'customer_address',
                cust.phone 'customer_phone',
                IF(cust.fax, cust.fax, '-') 'customer_fax',
                cust.email 'customer_email'
                FROM ".DB_WALLET_ORDERS." ord
                INNER JOIN ".DB_WALLET_ITEMS." it ON (ord.order_item_id=it.item_id AND ord.order_item_type=it.item_type)
                INNER JOIN ".DB_USER_WALLET." cust ON ord.order_user=cust.user_id
                #LEFT JOIN ".DB_USER_WALLET." payee ON ord.order_paid_user = payee.user_id
                #INNER JOIN ".DB_USERS." admin ON ord.order_completed_user=admin.user_id
                WHERE order_id='".$this->order_id."'";
                $result = dbquery($bill_sql);
                if (dbrows($result)>0) {
                    $data = dbarray($result);
                    $this->bill_view[$data['order_id']] = $data;
                } else {
                    throw new \Exception('The order is not found.');
                }
            }
        } else {
            throw new \Exception('The order ID is not set.');
        }
        return $this->bill_view[$this->order_id];
    }

    public function bill_view() {

        $bill = $this->get_bill();
        $currency = 'USD'; //Model::walletSettings('wallet_base_currency');
        return strtr(
            $this->bill_template(),
            [
                '{%currency%}' => $currency,
                '{%order_id%}' => $bill['order_id'],
                '{%order_date%}' => Model::display_date($bill['order_datestamp']),
                '{%total%}' => Model::parse_price($bill['order_total'], $currency),
                '{%method%}' => $bill['order_pay_method_name'],
                '{%payment%}' => $bill['order_paid'] ? 'Paid' : 'Unpaid',
                '{%payment_class%}' => $bill['order_paid'] ? 'text-success' : 'text-danger strong',
                '{%payment_ref%}' => $bill['order_pay_method_ref'],
                '{%complete%}' => $bill['order_completed'] ? 'Completed' : 'Pending Delivery',
                '{%complete_class%}' => $bill['order_completed'] ? 'text-success' : 'text-danger strong',
                '{%wallet_id%}' => $bill['order_paid_wallet'],
                '{%user_id%}' => $bill['order_user'],
                '{%name%}' => $bill['customer_name'],
                '{%address%}' => $bill['customer_address'],
                '{%phone%}' => $bill['customer_phone'],
                '{%fax%}' => $bill['customer_fax'],
                '{%email%}' => $bill['customer_email'],
                '{%order_title%}' => $bill['order_title'],
                '{%order_description%}' => $bill['order_description'],
                '{%item_id%}' => $bill['order_item_id'],
                '{%item_type%}' => $bill['order_item_type'],
                '{%item_name%}' => $bill['item_title'],
                '{%item_description%}' => $bill['item_description'],
                '{%order_title%}' => $bill['order_title'],
                '{%order_description%}' => $bill['order_description'],
                '{%item_value%}' => number_format($bill['order_item_value'],2),
                '{%item_quantity%}' => $bill['order_item_quantity'],
                '{%item_tax_rate%}' => $bill['order_item_tax_rate'],
                '{%item_tax%}' => number_format($bill['order_total_tax'],2),
                '{%item_total%}' => number_format($bill['order_total'],2),
            ]);
    }

    public function bill_template() {

        $html = "<div class='panel panel-default'>\n";
        $html .= "<div class='panel-body'><h3 class='text-light'>Order Reference: #{%order_id%}</h3></div>\n";


        $html .= "<div class='panel-body'>\n";

        $html .= "<h2>{%order_title%}</h2>\n";
        $html .= "<h4 class='m-b-20 text-light'>{%order_description%}</h4>\n";

        $html .= "<div class='list-group-item'>\n";

        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-6'>\n";

        $html .= "<h3 class='text-light'>Transaction Summary</h3>\n";
        $html .= "<h5 class='text-light'>Total:</h5><h2 class='text-light m-0'>{%total%}</h2>";
        $html .= "<h5 class='text-light'>Order Date: <span>{%order_date%}</span></h5>";
        $html .= "<h5 class='text-light'>Payment Channel: <span>{%method%}</span></h5>";
        $html .= "<h5 class='text-light'>Payment :</h5><p class='{%payment_class%}'>{%payment%}</p>";
        $html .= "<h5 class='text-light'>Payment Ref :</h5><p class='{%payment_class%}'>{%payment_ref%}</p>";
        $html .= "<h5 class='text-light'>Completion Status:</h5><p class='{%complete_class%}'>{%complete%}</p>";

        $html .= "</div>\n<div class='col-xs-12 col-sm-6 well'>\n";

        $html .= "<h3 class='text-light'>Customer For</h3>\n";
        $html .= "<h5 class='text-light'>Your ID: <span>#{%user_id%} (Wallet #{%wallet_id%})</span></h5>";
        $html .= "<h5 class='text-light'>Name: <span>{%name%}</span></h5>";
        $html .= "<h5 class='text-light'>Email: <span>{%email%}</span></h5>";
        $html .= "<h5 class='text-light'>Phone: <span>{%phone%}</span></h5>";
        $html .= "<h5 class='text-light'>Fax: <span>{%fax%}</span></h5>";
        $html .= "<h5 class='text-light'>Address:</h5><p>{%address%}</p>";
        $html .= "</div>\n</div>\n";

        $html .= "</div>\n";



        $html .= "<div class='p-15'><table class='table table-striped'>";
        $html .= "<thead>";
        $html .= "<tr>
                    <th>ID</th>
                    <th>Product Code</th>
                    <th>Product Details</th>
                    <th>Price ({%currency%})</th>
                    <th>Quantity</th>
                    <th>Tax</th>
                    <th>Subtotal ({%currency%})</th>
                </tr>";
        $html .= "</thead>\n<tbody>\n";
        $html .= "<tr>
                    <td>{%item_id%}</td>\n
                    <td>{%item_type%}</td>\n
                    <td>{%item_name%}<br/>{%item_description%}</td>\n
                    <td>{%item_value%}</td>\n
                    <td>{%item_quantity%}</td>\n
                    <td>{%item_tax%} ({%item_tax_rate%}%)</td>\n
                    <td><h3 class='m-0 text-light'>{%item_total%}</h3></td>\n
                </tr>
                ";
        $html .= "</tbody>\n";
        $html .= "</table>\n</div>\n";

        $html .= "</div>\n";
        $html .= "</div>\n";

        return $html;
    }


}
