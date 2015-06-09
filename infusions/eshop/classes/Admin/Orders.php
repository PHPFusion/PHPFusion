<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Admin/Orders.php
| Author: Frederick MC Chan (hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Eshop\Admin;
if (!defined("IN_FUSION")) { die("Access Denied"); }

class Orders {
	private $filter_Sql = '';
	private $months = array();
	private $showfilter = true;

	public function __construct() {
		global $locale;
		$_GET['orderid'] = isset($_GET['orderid']) && isnum($_GET['orderid']) ? $_GET['orderid'] : 0;
		$_GET['section'] = isset($_GET['section']) ? $_GET['section'] : 'orders';
		$_GET['sortby'] = !isset($_GET['sortby']) || !preg_match("/^[0-9A-Z]$/", $_GET['sortby']) ? "all" : $_GET['sortby'];
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$_GET['orderid'] = isset($_GET['orderid']) && isnum($_GET['orderid']) ? $_GET['orderid'] : 0;

		switch($_GET['action']) {
			case 'updateorder':
				self::update_order();
				break;
			case 'vieworder':
				if (self::validate_order($_GET['orderid'])) {
					echo openmodal('bill', "Invoice ".$_GET['orderid']."");
					self::view_order($_GET['orderid']);
					echo closemodal();
				}
				break;
			case 'delete':
				self::delete_order();
				break;
		}
	}

	/**
	 * SQL delete a specific order ID
	 */
	public static function delete_order() {
		global $aidlink, $locale;
		if (self::validate_order($_GET['orderid'])) {
			$oquery = dbarray(dbquery("SELECT oitems FROM ".DB_ESHOP_ORDERS." WHERE oid='".intval($_GET['orderid'])."'"));
			$items = unserialize($oquery['oitems']);
			echo form_alert($locale['ESHP303'], '', array('class'=>'info'));
			foreach($items as $itemData) {
				$item_count = $itemData['cqty'];
				dbquery("UPDATE ".DB_ESHOP." SET sellcount=sellcount-$item_count WHERE id = '".$itemData['prid']."'");
				dbquery("UPDATE ".DB_ESHOP." SET instock=instock+$item_count WHERE id = '".$itemData['prid']."'");
			}
			$result = dbquery("DELETE FROM ".DB_ESHOP_ORDERS." WHERE oid='".intval($_GET['orderid'])."'");
			if ($result) redirect(FUSION_SELF.$aidlink."&amp;a_page=orders");
		}
	}

	/**
	 * Check if a order id exist
	 * @param $orderid
	 * @return bool|string
	 */
	public static function validate_order($orderid) {
		if (isnum($orderid)) {
			return dbcount("('oid')", DB_ESHOP_ORDERS, "oid='".intval($orderid)."'");
		}
		return false;
	}

	/**
	 * SQL update order from Admin via the bill viewer
	 */
	public static function update_order() {
		global $aidlink;
		if (isset($_POST['save'])) {
			$oid = form_sanitizer($_POST['oid'], '0', 'oid');
			if (self::validate_order($oid)) {
				$opaid = form_sanitizer($_POST['opaid'], '0', 'opaid');
				$ocompleted = form_sanitizer($_POST['ocompleted'], '0', 'ocompleted');
				$oamessage = form_sanitizer($_POST['oamessage'], '0', 'oamessage');
				if (!defined('FUSION_NULL')) {
					dbquery("UPDATE ".DB_ESHOP_ORDERS." SET opaid='$opaid',ocompleted='$ocompleted',oamessage='$oamessage' WHERE oid='".intval($oid)."'");
					redirect(FUSION_SELF.$aidlink."&amp;a_page=orders");
				}
			}
		}
	}

	private function orders_view_filters() {
		global $locale, $aidlink;
		$item_status = isset($_GET['status']) && $_GET['status'] ? $_GET['status'] : 0;
		switch($item_status) {
			case '1':
				$this->filter_Sql = "opaid='0'";
				break;
			case '2':
				$this->filter_Sql = "opaid='1'";
				break;
			case '3':
				$this->filter_Sql = "ocompleted='0'";
				break;
			case '4':
				$this->filter_Sql = "ocompleted='1'";
				break;
			default :
				$this->filter_Sql = '';
		}


		echo "<div class='m-t-20 m-b-20 display-block' style='height:40px;'>\n";
		echo "<div class='display-inline-block search-align m-r-10'>\n";
		echo form_text('srch_text', '', '', array('placeholder'=>$locale['ESHP328'], 'inline'=>1, 'class'=>'m-b-0 m-r-10', 'width'=>'350px'));
		echo form_button('search', $locale['SRCH164'], $locale['SRCH158'], array('class'=>'btn-primary m-b-20 m-t-0'));
		echo "</div>\n";

		echo "<div class='display-inline-block m-r-10'>\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;status=0' ".(!$item_status ? "class='text-dark'" : '').">All (".number_format(dbcount("(oid)", DB_ESHOP_ORDERS)).")</a>\n - ";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;status=1' ".($item_status ==1 ? "class='text-dark'" : '').">Unpaid (".number_format(dbcount("(oid)", DB_ESHOP_ORDERS, "opaid='0'")).")</a>\n - ";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;status=2' ".($item_status ==2 ? "class='text-dark'" : '').">Paid (".number_format(dbcount("(oid)", DB_ESHOP_ORDERS, "opaid='1'")).")</a>\n - ";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;status=3' ".($item_status ==3 ? "class='text-dark'" : '').">Not Completed (".number_format(dbcount("(oid)", DB_ESHOP_ORDERS, "ocompleted='0'")).")</a>\n - ";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;status=4' ".($item_status ==4 ? "class='text-dark'" : '').">Completed (".number_format(dbcount("(oid)", DB_ESHOP_ORDERS, "ocompleted='1'")).")</a>\n";
		echo "</div>\n";

		echo "</div>\n";
		add_to_jquery("
		$('#search-order').bind('click', function(e) {
			$.ajax({
				url: '".ADMIN."includes/eshop_ordersearch.php',
				dataType: 'html',
				type: 'post',
				beforeSend: function(e) { $('#eshopitem-links').html('<tr><td class=\"text-center\"colspan=\'12\'><img src=\"".IMAGES."loader.gif\"/></td></tr>'); },
				data: { q: $('#srch_ortext').val(), token: '".$aidlink."' },
				success: function(e) {
					// append html
					$('#eshopitem-links').html(e);
				},
				error : function(e) {
				console.log(e);
				}
			});
		});
		");
	}

	/**
	 * View a specified order bill.
	 * @param $orderid
	 */
	private static function view_order($orderid) {
		global $aidlink, $locale;
		$order_query = dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE oid='".intval($orderid)."'");
		if (dbrows($order_query) > 0) {
			$odata = dbarray($order_query);
			$tab_title['title'][] = 'Administration';
			$tab_title['id'][] = 'oadmin';
			$tab_title['icon'][] = '';

			$tab_title['title'][] = 'Invoice Details';
			$tab_title['id'][] = 'oadmins';
			$tab_title['icon'][] = '';

			$tab_active = tab_active($tab_title, '0');
			echo opentab($tab_title, $tab_active, 'orders_admin');
			echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
			echo "<div class='m-t-20'>";
			openside($locale['ESHP306']." : ".$odata['oid']." - ".$locale['ESHP307']." ".$odata['oname']." - ".showdate("longdate", $odata['odate']));
			echo openform('inputform', 'post', FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=orders&amp;action=updateorder", array('max_tokens' => 1));
			echo form_select('opaid', $locale['ESHP309'],  array($locale['no'], $locale['yes']), $odata['opaid'], array('inline'=>1));
			echo form_select('ocompleted', $locale['ESHP310'], array($locale['no'], $locale['yes']), $odata['ocompleted'], array('inline'=>1));
			echo form_textarea('oamessage', $locale['ESHP308'], $odata['oamessage']);
			echo form_hidden('', 'oid', 'oid', $odata['oid']);
			echo form_button('save', $locale['save_changes'], $locale['save_changes'], array('class'=>'btn btn-success', 'icon'=>'fa fa-check-square-o'));
			echo closeform();
			closeside();
			echo "</div>\n";
			echo closetabbody();
			echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active); ?>

			<a class='btn button printorder btn-sm pull-right btn-success' href='<?php echo ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$_GET['orderid'] ?>'><?php echo $locale['ESHP314'] ?></a>
			<?php echo stripslashes($odata['oorder']);
			echo closetabbody();
			echo closetab();
		} else { 
		?>
			<div class='text-center alert alert-warning'><?php echo $locale['ESHP315'] ?></div>
		<?php
		}
	}

	/**
	 * List down all paid but not delivered. and unpaid and not delivered.
	 */
	public function list_order() {
		global $locale, $aidlink;

		$rows = dbcount("('oid')", DB_ESHOP_ORDERS, $this->filter_Sql);
		if ($this->showfilter) self::orders_view_filters();
		?>	
		<table class='table table-responsive table-striped m-t-20'>
		<tr>
			<th><?php echo $locale['ESHP317'] ?></th>
			<th><?php echo $locale['ESHP318'] ?></th>
			<th><?php echo $locale['ESHP319'] ?></th>
			<th><?php echo $locale['ESHPF126'] ?></th>
			<th><?php echo $locale['ESHPSS100'] ?></th>
			<th><?php echo $locale['ESHPPRO196'] ?></th>
			<th><?php echo $locale['ESHP309'] ?></th>
			<th><?php echo $locale['ESHP321'] ?></th>
			<th><?php echo $locale['ESHPCATS135'] ?></th>
		</tr>
		<?php
		if ($rows >0) {
			$result = dbquery("
							SELECT orders.*, payment.method as payment_method, delivery.method as delivery_method
							FROM ".DB_ESHOP_ORDERS." orders
							INNER JOIN ".DB_ESHOP_PAYMENTS." payment on orders.opaymethod = payment.pid
							INNER JOIN ".DB_ESHOP_SHIPPINGITEMS." delivery on orders.oshipmethod = delivery.sid
							".($this->filter_Sql ? "WHERE $this->filter_Sql" : '')." ORDER BY orders.oid ASC  LIMIT ".$_GET['rowstart']." , 15");
			?>	
			<tbody id='eshopitem-links' class='connected'>
			<?php
			while ($data = dbarray($result)) {
				$usercheck = dbquery("SELECT user_id FROM  ".DB_USERS." WHERE user_id = '".$data['ouid']."'");
				$urows = dbrows($usercheck);
				if ($urows != 0) {
					$customer = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;section=customerform&amp;action=edit&amp;cuid=".$data['ouid']."'>".$data['oname']."</a>";
				} else {
					$customer = $data['oname'];
				}
			?>			
			<tr>
				<td><?php echo $data['oid'] ?></td>
				<td><?php echo $customer ?></td>
				<td><?php echo $data['oemail'] ?></td>
				<td><?php echo $data['payment_method'] ?></td>
				<td><?php echo $data['delivery_method'] ?></td>
				<td><?php echo number_format($data['ototal'],2)." ".get_settings('eshop_currency') ?></td>
				<td><?php echo $data['opaid'] ? $locale['ESHP309'] : $locale['ESHP320'] ?></td>
				<td><?php echo $data['ocompleted'] ? $locale['ESHP321b'] : $locale['ESHP321'] ?></td>
				<td>
					<div class='btn-group'>
						<a class='btn button btn-sm btn-default' href='<?php echo ADMIN."eshop.php".$aidlink."&amp;a_page=orders&amp;section=orders&amp;action=vieworder&amp;orderid=".$data['oid'] ?>'><?php echo $locale['ESHP322'] ?></a>
						<a class='btn button btn-sm btn-default' href='<?php echo ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$data['oid'] // we can execute modal here ?>'><?php echo $locale['ESHP314'] ?></a>
						<a class='btn button btn-sm btn-danger' onClick='confirmdelete();' href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=orders&amp;action=delete&amp;orderid=".$data['oid'] ?>'><?php echo $locale['delete'] ?></a>
					</div>
				</td>
			</tr>
			<?php
			}
			?>			
			</tbody>
			</table>
			<div class='text-right m-t-5'>
				<?php echo makePageNav($_GET['rowstart'], 15, $rows, 3, FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=orders&amp;sortby=".$_GET['sortby']."&amp;") ?>
			</div>
		<?php
		} else {
		?>			
			<tr><td colspan='8' class='text-center'><?php echo $locale['ESHP325'] ?></td></tr>
		<?php
		}
		?>
		</table>
	<?php
	}

	public function list_history() {
		global $aidlink, $locale;

		$allowed_modes = array('m','d','y');
		$_GET['mode'] = isset($_GET['mode']) && in_array($_GET['mode'], $allowed_modes) ? $_GET['mode'] : '';
		$_GET['month'] = isset($_GET['month']) && isnum($_GET['month']) && $_GET['month'] >= 1 && $_GET['month'] <= 12 ? $_GET['month'] : '';
		$_GET['year'] = isset($_GET['year']) && isnum($_GET['year']) && strlen($_GET['year']) == 4 ? $_GET['year'] : '';
		$this->months = $months = explode("|", $locale['months']);

		switch ($_GET['mode']) {

			case 'y':
				add_to_title(" - ".$_GET['year']); ?>
				<p class='m-t-20'><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history" ?>'><?php echo $locale['ESHP329'] ?></a></p>
				<table class='table table-responsive table-striped'>
					<tr>
						<th>Year <?php echo $_GET['year'] ?></th>
						<th>Orders</th>
						<th>Total Sales</th>
					</tr>
					<?php
					$m_min = mktime(0, 0, 0, 1, 1, $_GET['year']);
					$m_max = mktime(0, 0, 0, 12, 31, $_GET['year']);
					$dates = dbquery("SELECT odate, count(oid) as orders,
						sum(ototal) as total_sales,
						MONTH(from_unixtime(odate)) as month
						FROM ".DB_ESHOP_ORDERS." WHERE odate > '".$m_min."' && odate < '".$m_max."' GROUP BY month ASC ORDER BY oid ASC");
					if (dbrows($dates)) {
						while ($id = dbarray($dates)) {
						?>							
							<tr>
								<td><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history&amp;mode=m&amp;year=".$_GET['year']."&amp;month=".$id['month'] ?>'>
										<?php echo $months[$id['month']] ?>
									</a>
								</td>
								<td><?php echo $id['orders'] ?></td>
								<td><?php echo $id['total_sales'].' '.get_settings('eshop_currency') ?></td>
							</tr>
						<?php
						}
					} else {
					?>						
						<tr><td class='text-center' colspan='4'><?php echo $locale['ESHP331'] ?></td></tr>
					<?php
					}
					?>					
				</table>
				<?php
				break;

			case 'm':
				add_to_title(" - ".$months[$_GET['month']]." ".$_GET['year']); ?>
				<p class='m-t-20'><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history" ?>'><?php echo $locale['ESHP329'] ?></a></p>
				<table class='table table-responsive table-striped'>
					<tr>
						<th>Month of <?php echo $months[$_GET['month']]." ".$_GET['year'] ?></th>
						<th>Day</th>
						<th>Orders</th>
						<th>Total Sales</th>
					</tr>
					<?php
					$m_stats = array();
					$m_days = cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year']);
					if ($_GET['year'] == date("Y", time()) && $_GET['month'] == date("m", time())) $m_days = date("d", time());
					$m_min = mktime(0, 0, 0, $_GET['month'], 1, $_GET['year']) - 1;
					$m_max = $m_min + ($m_days * 24 * 60 * 60);
					$dates = dbquery("SELECT odate, count(oid) as orders,
						sum(ototal) as total_sales,
						DAY(from_unixtime(odate)) as day
						FROM ".DB_ESHOP_ORDERS." WHERE odate > '".$m_min."' && odate < '".$m_max."' GROUP BY day ASC ORDER BY oid ASC");
					if (dbrows($dates)) {
						while ($id = dbarray($dates)) {
?>							
							<tr>
								<td><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history&amp;mode=d&amp;year=".$_GET['year']."&amp;month=".$_GET['month']."&amp;day=".$id['day'] ?>'>
										<?php echo $locale['ESHP334'][date("w", mktime(1, 1, 1, $_GET['month'], $id['day'], $_GET['year']))] ?>
									</a>
								</td>
								<td><?php echo $id['day'] ?></td>
								<td><?php echo $id['orders'] ?></td>
								<td><?php echo $id['total_sales'].' '.get_settings('eshop_currency') ?></td>
							</tr>
						<?php
						}
					} else {
						?>						
						<tr><td class='text-center' colspan='4'><?php echo $locale['ESHP331'] ?></td></tr>
					<?php
					}
					?>
				</table>
				<?php
				break;
			case 'd':
				add_to_title(" - ".$_GET['day']." ".$months[$_GET['month']]." ".$_GET['year']);
				$d_min = mktime(23, 59, 59, $_GET['month'], $_GET['day']-1, $_GET['year']);
				$d_max = $d_min + (24 * 60 * 60);
				$this->filter_Sql = "odate > '".$d_min."' AND odate < '".$d_max."' "; // just set another variety of listing filter and use list_order() function.
				$this->showfilter = false; 
				?>
				<p class='m-t-20'>
					<a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history" ?>'><?php echo $locale['ESHP329'] ?></a> -
					<a href='<?Php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history&amp;mode=m&amp;year=".$_GET['year']."&amp;month=".$_GET['month'] ?>'> <?php echo $months[$_GET['month']]." ".$_GET['year'] ?> </a>
				</p>
				<?php
				self::list_order();
				break;
			default:
				$dates = dbquery("SELECT YEAR(from_unixtime(odate)) as year, MONTH(from_unixtime(odate)) as month, SUM(ototal) as total_sales, count(oid) as orders
						 FROM ".DB_ESHOP_ORDERS."
						 GROUP BY YEAR(from_unixtime(odate)) DESC, MONTH(from_unixtime(odate)) DESC
						 ORDER BY year DESC, month DESC");
				?>
					<table class='table table-responsive table-striped m-t-20'>
					<tr>
						<th>Year</th>
						<th>Month</th>
						<th>Number of Orders</th>
						<th>Total Sales</th>
					</tr>
				<?php

				while ($id = dbarray($dates)) {
					?>
					<tr>
						<td><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history&amp;mode=y&amp;year=".$id['year'] ?>'><?php echo $id['year'] ?></td>
						<td><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=history&amp;mode=m&amp;year=".$id['year']."&amp;month=".$id['month'] ?>'><?php echo $months[$id['month']] ?></a></td>
						<td><?php echo $id['orders'] ?></td>
						<td><?php echo $id['total_sales']." ".get_settings('eshop_currency') ?></td>
					</tr>
					<?php
				}
				?>
				</table>
				<?php
		}
	}


	public function searchbox() {
		$search = array("A",
			"B",
			"C",
			"D",
			"E",
			"F",
			"G",
			"H",
			"I",
			"J",
			"K",
			"L",
			"M",
			"N",
			"O",
			"P",
			"Q",
			"R",
			"S",
			"T",
			"U",
			"V",
			"W",
			"X",
			"Y",
			"Z",
			"0",
			"1",
			"2",
			"3",
			"4",
			"5",
			"6",
			"7",
			"8",
			"9");
		echo "<hr /><table align='center' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
		echo "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=all'>".$locale['ESHP425']."</a></td>";
		for ($i = 0; $i < 36 != ""; $i++) {
			echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=".$search[$i]."'>".$search[$i]."</a></div></td>";
			echo($i == 17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=all'>".$locale['ESHP426']."</a></td>\n</tr>\n<tr>\n" : "\n");
		}
		echo "</table><hr />\n";
	}
}
