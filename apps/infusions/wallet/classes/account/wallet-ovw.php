<?php

namespace PHPFusion\Infusions\Wallet\Classes\Account;

use FusionCharts\Charts;
use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;
use PHPFusion\Tables;

/**
 * Class Wallet_OVW
 * Overview Page
 *
 * @package PHPFusion\Infusions\Wallet\Classes\Account
 */
class Wallet_OVW extends Wallet_Model {

    public $pocket = array();

    /**
     * Coin Management Menu
     *
     * @return array
     */
    public function getMenu() {
        //http://php-fusion.test/edit_profile.php?ref=wallet
        return [
            'all'       => [
                'id'    => 'all',
                'title' => 'All Transaction',
                'link'  => BASEDIR.'edit_profile.php?ref=wallet&amp;sref=all',
                'icon'  => '',
            ],
            'purchases' => [
                'id'    => 'purchases',
                'title' => 'Purchases',
                'link'  => BASEDIR.'edit_profile.php?ref=wallet&amp;sref=purchases',
                'icon'  => '',
            ],
            'topup'     => [
                'id'    => 'topup',
                'title' => 'Top Up',
                'link'  => BASEDIR.'edit_profile.php?ref=wallet&amp;sref=topup',
                'icon'  => '',
            ],
            'refund'    => [
                'id'    => 'refund',
                'title' => 'Refunds',
                'link'  => BASEDIR.'edit_profile.php?ref=wallet&amp;sref=refund',
                'icon'  => '',
            ]
        ];
    }

    private function getUserTransactionTable($type = 'Default') {
        // do the table class.
        include __DIR__.'/wallet-overview-table.php';
        $table = new Tables(new \Wallet_Overview_Table($type, self::$user_id), FALSE);
        return $table->displayTable();
    }

    public function formatCoinTitle($data) {
        return "<span>".$data[':ct_title']."</span><br/><span class='text-lighter'>".$data[':ct_description']."</span>";
    }

    public function transferTable() {
        $table_id = fusion_table('wallet_transfers', [
            'remote_file' => fusion_get_settings('siteurl').'infusions/wallet/classes/ajax/recent_transfer.user.php?user_token='.cookie(COOKIE_USER),
        ]);
        $html = "<table id='".$table_id."' class='table'>";
        $html .= "<thead>";
        $html .= "<tr><th>Date</th><th style='width:60%;'>Transaction Reference</th><th>User</th><th>Send/Received</th><th>Amount</th></tr>";
        $html .= "<tbody></tbody>";
        $html .= "</table>";

        return $html;
    }

    public function transactionsTable() {
        $table_id = fusion_table('wallet_transactions', [
            'remote_file' => fusion_get_settings('siteurl').'infusions/wallet/classes/ajax/recent_transactions.user.php?user_token='.cookie(COOKIE_USER),
        ]);
        $html = "<table id='".$table_id."' class='table'>";
        $html .= "<thead>";
        $html .= "<tr><th>Date</th><th style='width:40%;'>Transaction Order</th><th>Type</th><th>Method</th><th>Send/Received</th><th>Amount</th></tr>";
        $html .= "<tbody></tbody>";
        $html .= "</table>";
        return $html;
    }

    public function getProfileCompletionScore() {
        $completion_score = 0;
        $arr = [
            'first_name'  => 'First Name',
            'last_name'   => 'Last Name',
            'identity_no' => 'ID No',
            'country'     => 'Country',
            'region'      => 'Region',
            'city'        => 'City',
            'address'     => 'Street',
            'postcode'    => 'Postcode/ZIP',
            'mobile'      => 'Mobile Number',
            'mobile_cc'   => 'Mobile Number Prefix',
            'email'       => 'Email Address',
        ];
        $completion_ratings = 1 / count($arr);
        foreach ($arr as $key => $value) {
            if (!empty($this->pocket[$key])) {
                $completion_score = $completion_score + $completion_ratings;
            }
        }
        return ceil($completion_score * 100);
    }

    public function overview() {
        if (!$this->pocket['wallet_id']) {
            redirect(BASEDIR.'edit_profile.php?ref=wallet&amp;dref=account_settings&amp;sref=first_time');
        }
        add_to_title('Wallet Overview');
        //require_once INCLUDES.'charts/charts_include.php';
        //
        //$completion_chart = new Charts('doughnut');
        //$completion_score = $this->getProfileCompletionScore();
        //$completion_chart->set_categories(['Completion']);
        //$completion_chart->set_data('Completion', [$completion_score], ['backgroundColor' => 'rgba(17,200,120,1)']);
        //$completion_chart->setOptions('doughnut', ['display' => FALSE, 'cutoutPercentage' => 80]);
        //$completion_chart->setOptions('legend', ['display' => FALSE]);
        //echo "<style>canvas#chart-completion_chart {
        //min-width:130px !important;
        //max-width:130px !important;
        //width:130px !important;
        //min-height:130px !important;
        //height:130px !important;
        //max-height:130px !important;
        //}</style>";

        $twig = twig_init(INFUSIONS.'wallet/templates', TRUE);
        $info = [
            'locale'              => fusion_get_locale(),
            'balance'             => $this->pocket['balance'],
            'wallet_data'         => Wallet::getInstance()->getUserWallet(fusion_get_userdata('user_id')),
            'top_up_link'         => 'https://www.php-fusion.co.uk/edit_profile.php?ref=wallet&section=charge',
            'account_link'        => 'https://www.php-fusion.co.uk/edit_profile.php?ref=wallet&section=account_settings',
            //'completion_score'    => $completion_score,
            //'completion_chart_id' => $completion_chart->display_chart('completion_chart', ['debug' => FALSE]),
            'table'               => [
                'transactions' => $this->transactionsTable(),
                'transfer'     => $this->transferTable(),
            ]
        ];
        return $twig->render('activity.twig', $info);

    }

}
