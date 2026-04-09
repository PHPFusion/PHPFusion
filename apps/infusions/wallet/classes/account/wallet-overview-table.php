<?php

use PHPFusion\Interfaces\TableSDK;

require_once __DIR__.'/../../../../maincore.php';

class Wallet_Overview_Table implements TableSDK {

    private $table_query = [];

    private $header_content = '';

    public function __construct($table_type, $user_id) {
        switch ($table_type) {
            case 'Purchases':
            // purchase, condition - in must be 0 and
                $this->table_query = [
                    'table'      => DB_COIN_TRANSACTIONS,
                    'id'         => 'ct_id',
                    //'select' => 'FORMAT(ct_total_in,4) AS total_in, FORMAT(ct_total_out,4) AS total_in',
                    'conditions' => "base.ct_user='$user_id' AND base.ct_item_type !='COINP'",
                    'limit'      => 24,
                ];
                $this->table_properties['header_content'] = 'Purchase Transactions';
                break;
            case 'Top Up':
                $this->table_query =
                    [
                        'table'      => DB_COIN_TRANSACTIONS,
                        'id'         => 'ct_id',
                        'conditions' => "base.ct_user='$user_id' AND base.ct_item_type='COINS'",
                        'limit'      => 24,
                    ];
                $this->header_content = 'Top Up Transactions';
                break;
            case 'Refund':
                $this->table_query =
                    [
                        'table'      => DB_COIN_TRANSACTIONS,
                        'id'         => 'ct_id',
                        'conditions' => "base.ct_user='$user_id' AND base.ct_item_type='REFUND'",
                        'limit'      => 24,
                    ];
                $this->header_content = 'Refund Transactions';
                break;
            default:
                $this->table_query = [
                    'table'      => DB_COIN_TRANSACTIONS,
                    'id'         => 'ct_id',
                    'conditions' => "ct_user='$user_id'",
                    'limit'      => 24,
                ];
                $this->header_content = 'Recent Transactions';
                break;
        }
    }

    public function data() {
        return $this->table_query;
    }

    public function column() {
        return [
            'ct_ref'            => [
                "title"       => "Order No.",
                "title_class" => "col-xs-1",
                'visibility'  => FALSE,
                'delete_link' => FALSE,
            ],
            'ct_datestamp'      => [
                'title'       => "Order Date",
                'title_class' => 'col-xs-1',
                'date'        => TRUE,
                'date_format' => 'shortdate'
            ],
            'ct_title'          => [
                'title'       => "Transaction",
                "title_class" => "col-xs-4",
                'callback' => ['PHPFusion\\Infusions\\Wallet\\Classes\\Account\\Wallet_OVW', 'formatCoinTitle', WALLET.'classes/account/wallet_ovw.php']
            ],
            // Particulars
            // 'ct_description'    => [
            //     'title'       => "Product",
            //     'value_class' => 'col-xs-5',
            // ],
            'ct_completed'      => [
                "title"       => "Delivery",
                'title_class' => 'col-xs-1',
                'options'     => [
                    1 => "Delivered",
                    0 => "Undelivered"
                ]
            ],
            'ct_paid'           => [
                "title"       => "Payment",
                'title_class' => 'col-xs-1',
                'options'     => [
                    1 => "Paid",
                    0 => "Unpaid"
                ]
            ],
            'ct_paid_datestamp' => [
                'title'       => 'Payment Date',
                'title_class' => 'col-xs-1',
                'date'        => TRUE,
                'date_format' => 'shortdate'
            ],
            'ct_total_in'       => [
                'title'       => "Credit (Coins)",
                'title_class' => 'col-xs-1 no-break',
                'number'      => TRUE,
                'delimiter'   => 1,
                'value_class' => 'text-right',
                //"format"      => ":ct_total_in coin(s)"
            ],
            'ct_total_out'      => [
                'title'       => "Debit (Coins)",
                'title_class' => 'col-xs-1 no-break',
                'number'      => TRUE,
                'delimiter'   => 1,
                'value_class' => 'text-right',
                //"format"      => ":ct_total_out coin(s)"
            ],
        ];
    }

    public function properties() {
        return [
            'table_id'           => 'wallet-transactions',
            'header_content'     => $this->header_content,
            'date_col'           => 'ct_datestamp',
            'no_record'          => "There are no transactions found.",
            'search_placeholder' => "Enter Order Number",
            'search_col'         => 'ct_ref',
            'search_label'       => fusion_get_locale('search'),
            'date_col'           => 'ct_datestamp',
            'delete_link'        => FALSE,
            'edit_link'          => FALSE,
            'order_col'          => [
                'ct_datestamp'      => 'date',
                'ct_paid_datestamp' => 'payment_date',
                'ct_title'          => 'type',
                'ct_description'    => 'description',
                'ct_completed'      => 'delivered',
                'ct_paid'           => 'payment',
                'ct_total_in'       => 'credit',
                'ct_total_out'      => 'debit',
                'ct_ref'            => 'order_number',
            ]
        ];

    }

    /**
     * @return array|void
     */
    public function quickEdit() {
        // TODO: Implement quickEdit() method.
    }
}