<?php

use PHPFusion\Interfaces\TableSDK;

class Packs_Table implements TableSDK {

	private static $walletSettings = [];

	public function __construct($wallet) {
		self::$walletSettings = $wallet;
	}

	/**
	 *  Returns the table data source structure configurations
	 *
	 *
	 * 'debug'                    => FALSE, // True to show the SQL query for the table.
	 * 'table'                    => '',
	 * 'id'                       => '', // if hierarchy
	 * 'parent'                   => '', // if hierarchy
	 * 'limit'                    => 24,
	 * 'true_limit'               => FALSE, // if true, the limit is true limit (only limited results will display without page nav)
	 * 'joins'                    => '',
	 * 'select'                   => '',
	 * 'conditions'               => '', // to match list to a condition. string value only
	 * 'group'                    => '', // group by column
	 * 'image_folder'             => '', // for deletion (i.e. IMAGES.'folder/') , use param for string match
	 * 'image_field'              => '', // to delete (i.e. news_image)
	 * 'file_field'               => '',  // to delete (i.e. news_attach)
	 * 'file_folder'              => '', // to delete files from the folder, use param for string match
	 * 'db'                       => [], // to delete other entries on delete -- use this key. Keys: 'select' => 'ratings_id', 'group' => 'ratings_item_id', 'custom' => "rating_type='CLS'"
	 * 'delete_function_callback' => '',
	 *
	 * @return array
	 */
	public function data() {
		// TODO: Implement data() method.
		return [
			'table' => DB_COIN_PACKS,
			'id'    => 'package_id',
			'title' => 'package_coin_quantity',
			'limit' => 15,
		];
	}

	/**
	 * Returns the table outlook/presentation configurations
	 *
	 * 'table_class'        => '',
	 * 'header_content'     => '',
	 * 'no_record'          => 'There are no records',
	 * 'search_label'       => 'Search',
	 * 'search_placeholder' => "Search",
	 * 'search_col'         => '', // set this value sql column name to have search input input filter
	 * 'delete_link' => TRUE,
	 * 'edit_link' => TRUE,
	 * 'edit_link_format'   => '', // set this to format the edit link
	 * 'delete_link_format' => '', // set this to format the delete link
	 * 'view_link_format' => '', // set this to format the view link
	 *
	 * 'edit_key'           => 'edit',
	 * 'del_key'            => 'del', // change this to invoke internal table delete function for custom delete link format
	 * 'view_key'           => 'view',
	 *
	 * 'date_col'           => '',  // set this value to sql column name to have date selector input filter
	 * 'order_col'          => '', // set this value to sql column name to have sorting column input filter
	 * 'multilang_col'      => '', // set this value to have multilanguage column filter
	 * 'updated_message'    => 'Entries have been updated', // set this value to have custom success message
	 * 'deleted_message'    => 'Entries have been deleted', // set this value to have the custom delete message,
	 * 'class'              => '', // table class
	 * 'show_count'         => TRUE // show table item count
	 *
	 * @return array
	 */
	public function properties() {
		// TODO: Implement properties() method.
		return array(
			'header_content' => "Current Coin Packages Listing",
			'no_record'      => 'There are no Coin Packages defined.',
			'search_label'   => 'Search Packages',
			'search_col'     => 'package_coin_quantity',
			'date_col'       => "package_datestamp",
			'order_col'      => array(
				'package_coin_quantity' => 'packages',
				'package_status'        => 'status',
				'package_id'            => 'id',
				'package_price'         => 'price',
				'package_datestamp'     => 'updated',
			),
		);
	}

	/**
	 * Returns the column structure configurations
	 *
	 * 'title'         => '',
	 * 'title_class'   => '',
	 * 'value_class'   => '',
	 * 'edit_link'     => FALSE,
	 * 'delete_link'   => FALSE,
	 * 'image'         => FALSE,
	 * 'image_folder'  => '', // set image folder (method2)
	 * 'default_image' => '',
	 * 'image_width'   => '', // set image width
	 * 'image_class'   => '', // set image class
	 * 'icon'          => '',
	 * 'empty_value'   => '',
	 * 'count'         => [],
	 * 'view_link'     => '',
	 * 'display'       => [], // API for display
	 * 'date'          => FALSE,
	 * 'options'       => [],
	 * 'user'          => FALSE,
	 * 'user_avatar'   => FALSE, // show avatar
	 * 'number'        => FALSE,
	 * 'format'        => FALSE, // for formatting using strtr
	 * 'callback'      => '', // for formatting using function
	 * 'debug'         => FALSE,
	 *
	 * @return array
	 */
	public function column() {
		// TODO: Implement column() method.
		return array(
			'package_coin_quantity' => array(
				'title'       => 'Package Coins',
				'title_class' => 'col-xs-4',
				'value_class' => 'no-break',
				'edit_link'   => TRUE,
				'delete_link' => TRUE,
				'format'      => ":package_coin_quantity Coins(s)",
			),

			"package_promotion_bonus"     => array(
				'title'       => "Promotion (".self::$walletSettings["coin_base_currency"].")",
				'title_class' => 'col-xs-3',
				'value_class' => 'no-break',
				"callback"    => array(
					"\\PHPFusion\\Infusions\\Wallet\\Classes\\Admin\\Compo\\WalletPacks",
					"__getPackagePromotion",
					WALLET."classes/admin/compo/packs.php"
				)
			),
			'package_status'        => array(
				'title'       => 'Package Status',
				'title_class' => 'col-xs-3',
				'value_class' => 'no-break',
				'array'       => [
					0 => 'Inactive',
					1 => 'Active'
				]
			),

		);
	}

	/**
	 * Every row of the array is a field input.
	 * @return array
	 */
	public function quickEdit() {
		// TODO: Implement quickEdit() method.
}}
