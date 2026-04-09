<?php

namespace PHPFusion\Infusions\Wallet\Classes\Admin;

use PHPFusion\Infusions\Wallet\Classes\Admin\Compo\Wallet_Bills;

use PHPFusion\Infusions\Wallet\Classes\Admin\Compo\Wallet_Overview;

use PHPFusion\Infusions\Wallet\Classes\Admin\Compo\Wallet_Packs;

use PHPFusion\Infusions\Wallet\Classes\Admin\Compo\Wallet_Settings;

use PHPFusion\Infusions\Wallet\Classes\Admin\Compo\Wallet_Users;

use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;

/**
 * Class Wallet_Admin
 *
 * @package PHPFusion\Infusions\Wallet\Classes\Admin
 */
class Wallet_Admin extends Wallet_Model {

	public function __construct() {
		pageAccess('WLT');
	}

	public function __view() {
		$aidlink = fusion_get_aidlink();

		$right_dropdown = "<div class='pull-right dropdown btn-group'>";
		$right_dropdown .= "<span class='btn btn-default btn-lg'><a href=''><i class='fas fa-calendar'></i></a></span>";
		$right_dropdown .= "<a href='#' class='dropdown-toggle btn btn-default btn-lg' data-toggle='dropdown'>Sept 1 - Sept 30, 2019<span class='caret m-l-15'></span></a>";
		$right_dropdown .= "<ul class='dropdown-menu'>";
		$right_dropdown .= "<li></li>";
		$right_dropdown .= "</ul>";
		$right_dropdown .= "</div>\n";

		echo opentable("Fusion Wallet".$right_dropdown);

		$tab_config = [
			"title" => ["Dashboard", "Users", "Billing", "Coin Store", "Settings"],
			"id"    => ["ovw_tab", "ovw_acc", "ovw_reports", "ovw_packs", "ovw_settings"],
		];

		$tab_active = tab_active($tab_config, 0, "section");

		echo opentab($tab_config, $tab_active, "walletAdminTab", TRUE, '', "section",  ["*"]);

		// do defender filter input
		$section = get('section') ?: 'ovw_tab';
		switch ($section) {
		    // Dashboard
			case 'ovw_tab':
				Wallet_Overview::getInstance()->__view();
				break;
				// Wallet Accounts
			case 'ovw_acc':
				Wallet_Users::getInstance()->__view();
				break;
                // Wallet Packs
			case 'ovw_packs':
				echo Wallet_Packs::getInstance()->__view();
				break;
				// Billing and Invoice
			case 'ovw_reports':
				Wallet_Bills::getInstance()->__view();
				break;
				// Settings
			case 'ovw_settings':
				Wallet_Settings::getInstance()->__view();
		}
		echo closetab();
		echo closetable();
	}
}

