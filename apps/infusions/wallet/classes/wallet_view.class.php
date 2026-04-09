<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: wallet.php
| Author: Frederick Chan Meang Czac (PHP Fusion)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Infusions\Wallet\Classes;

use PHPFusion\Infusions\Wallet\Classes\Account\Wallet_Account;
use PHPFusion\Infusions\Wallet\Classes\Account\Wallet_OVW;

//use PHPFusion\Infusions\Wallet\Classes\Account\Wallet_Index;
//
//use PHPFusion\Infusions\Wallet\Classes\Account\Wallet_OVW;

/**
 * Class Wallet_View
 * Main Controller for Wallet User Interface
 *
 * @package PHPFusion\Infusions\Wallet\Classes
 */
class Wallet_View {

    private static $instance = NULL;

    private $pocket = [];

    private static $pages = [
        'section' => [
            [
                'id'    => 'overview',
                'title' => 'Overview',
            ],
            [
                'id'    => 'topup',
                'title' => 'Top Up Coins',
            ],
            [
                'id'    => 'account_settings',
                'title' => 'Manage Account',
            ]
        ]
    ];

    /**
     * Static instance
     *
     * @return null|static
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }


    public function __construct() {
        if (!iMEMBER) {
            redirect(BASEDIR.'index.php');
        }
        // DO NOT TURN THIS ON BECAUSE THIS WILL DISTORT EDIT_PROFILE
        //add_to_footer("<script src='".WALLET."wallet.js'></script>");
        //include "wallet_functions.php";
        // everyone has a wallet coin account. but their information is not updated in the billing section.
        if (infusion_exists('wallet')) {
            $this->pocket = fusion_get_user_wallet(fusion_get_userdata('user_id'));
        }

    }

    /**
     * Viewer
     */
    public function View() {

        // check wallet. if there are no wallet account, go to create form.
        if (!$this->pocket['wallet_id']) {
            return $this->wallet_profile();
        }

        $section = get('section');
        $section = in_array($section, ['topup', 'account_settings', 'overview', 'ovw']) ? $section : 'overview';

        switch ($section) {
            case 'topup':
                return $this->topup();
                break;
            /*case 'transfer':
                echo wallet_transfer();
                break;
            case 'withdraw': // no merchant for adb earnings will be in USD, not in coins.
                echo wallet_withdraw();
                break;*/
            case 'account_settings':
                return $this->wallet_profile();
                break;
            default:
            case 'ovw':
                return $this->overview();
                break;
        }
    }

    public function getPages() {
        $pages = [];
        if (isset(self::$pages['section'])) {
            foreach (self::$pages['section'] as $id => $pagedata) {
                $pages['title'][] = $pagedata['title'];
                $pages['id'][] = $pagedata['id'];
            }
        }
        return $pages;
    }

    // Additional Navigation that works only on FusionTheme.
    public function getPageNav() {

        if (empty($this->pocket['wallet_id'])) {
            $tab['title'][] = 'First Time Activation';
            $tab['id'][] = 'registration';

            return $tab;
        }

        if (get('section') == 'account_settings') {

            $tab['title'][] = 'Basic Information';
            $tab['title'][] = 'Security Settings';
            $tab['title'][] = 'Real Identity Verification';
            $tab['title'][] = 'Privacy Settings';
            $tab['id'][] = 'basic';
            $tab['id'][] = 'security';
            $tab['id'][] = 'identity';
            $tab['id'][] = 'privacy';

            return $tab;
        }
        return [];
    }

    /**
     * Overview Page
     *
     * @return string
     */
    public function overview() {
        $obj = new Wallet_OVW();
        $obj->pocket = $this->pocket;
        return $obj->overview();
    }

    /**
     * Account Page
     *
     * @return string
     * @throws \ReflectionException
     */
    function wallet_profile() {
        $obj = new Wallet_Account();
        $obj->pocket = $this->pocket;
        return $obj->viewPage();
    }

    public function topup() {
        $obj = new TopUp();
        $obj->pocket = $this->pocket;
        return $obj->topup();
    }
    //
    //    /**
    //     * Top up page
    //     * @return string
    //     */
    //    function wallet_topup() {
    //        $obj = new TopUp();
    //        $obj->pocket = $this->pocket;
    //        return $obj->viewPage();
    //    }

}


require_once INFUSIONS.'wallet/wallet_include.php';
