<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: orders.php
| Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) die("Access Denied");


class Orders {

	public function __construct() {
		$_GET['orderid'] = isset($_GET['orderid']) && isnum($_GET['orderid']) ? $_GET['orderid'] : 0;
		$_GET['o_page'] = isset($_GET['o_page']) ? $_GET['o_page'] : 'orders';
		$_GET['sortby'] = !isset($_GET['sortby']) || !preg_match("/^[0-9A-Z]$/", $_GET['sortby']) ? "all" : $_GET['sortby'];

	}

	public static function delete_order() {
		global $aidlink;
		$odata = dbarray(dbquery("SELECT oitems FROM ".DB_ESHOP_ORDERS." WHERE oid='".$_GET['orderid']."'"));
		echo "<div class='admin-message' align='center' style='margin-top:5px;'>".$locale['ESHP303']."<br /></div>\n";
		echo "<table align='center' cellspacing='2' cellpadding='2 width='99%'>";
		$items = $odata['oitems'];
		$items = explode(".", substr($items, 1));
		for ($i = 0; $i < count($items); $i++) {
			//update sellcount
			dbquery("UPDATE ".DB_ESHOP." SET sellcount=sellcount-1 WHERE id = '".$items[$i]."'");
			//update stock count.
			dbquery("UPDATE ".DB_ESHOP." SET instock=instock+1 WHERE id = '".$items[$i]."'");
			echo "<tr><td class='tbl2' width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=Main&action=edit&id=".$items[$i]."'> ".$locale['ESHP304']." ".$items[$i]." </a> ".$locale['ESHP305']." </td></tr>";
		}
		echo "</table>";
		$result = dbquery("DELETE FROM ".DB_ESHOP_ORDERS." WHERE oid='".$_GET['orderid']."'");
	}

	public static function update_order() {
		global $aidlink;
		if (isset($_POST['ocompleted'])) {
			$ocompleted = stripinput($_POST['ocompleted']);
		}
		if (isset($_POST['opaid'])) {
			$opaid = stripinput($_POST['opaid']);
		}
		if (isset($_POST['oamessage'])) {
			$oamessage = stripinput($_POST['oamessage']);
		}
		dbquery("UPDATE ".DB_ESHOP_ORDERS." SET opaid='$opaid',ocompleted='$ocompleted',oamessage='$oamessage' WHERE oid='".$_GET['orderid']."'");
		redirect(FUSION_SELF.$aidlink."&amp;a_page=orders");
	}

	public static function view_order($orderid) {
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
			echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=orders&amp;updateorder", array('downtime'=>1));
			echo form_select($locale['ESHP309'], 'opaid', 'opaid', array($locale['no'], $locale['yes']), $odata['opaid'], array('inline'=>1));
			echo form_select($locale['ESHP310'], 'ocompleted', 'ocompleted', array($locale['no'], $locale['yes']), $odata['ocompleted'], array('inline'=>1));
			echo form_textarea($locale['ESHP308'], 'oamessage', 'oamessage', $odata['oamessage']);
			echo form_hidden('', 'oid', 'oid', $odata['oid']);
			echo form_button($locale['save_changes'], 'save', 'save', $locale['save_changes'], array('class'=>'btn btn-sm btn-primary'));
			echo closeform();
			closeside();
			echo "</div>\n";
			echo closetabbody();
			echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
			?>
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
	public	function list_order() {
		global $locale, $aidlink;
		$orderby = ($_GET['sortby'] == "all" ? "" : " AND oname LIKE '".$_GET['sortby']."%'");
		$rows = dbcount("('oid')", DB_ESHOP_ORDERS, "ocompleted='0' $orderby");

		if (isset($_GET['action']) && $_GET['action'] == 'vieworder' && isset($_GET['orderid']) && isnum($_GET['orderid'])) {
			echo openmodal('bill', "Invoice ".$_GET['orderid']."");
			self::view_order($_GET['orderid']);
			echo closemodal();
		}

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
							WHERE orders.ocompleted = '0' ".$orderby." ORDER BY orders.oid ASC  LIMIT ".$_GET['rowstart']." , 15");
			?>
			<tbody>
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
					<td><?php echo number_format($data['ototal'],2)." ".fusion_get_settings('eshop_currency') ?></td>
					<td><?php echo $data['opaid'] ? $locale['ESHP309'] : $locale['ESHP320'] ?></td>
					<td><?php echo $data['ocompleted'] ? $locale['ESHP321b'] : $locale['ESHP321'] ?></td>
					<td>
						<div class='btn-group'>
							<a class='btn button btn-sm btn-default' href='<?php echo ADMIN."eshop.php".$aidlink."&amp;a_page=orders&amp;section=orders&amp;action=vieworder&amp;orderid=".$data['oid'] ?>'><?php echo $locale['ESHP322'] ?></a>
							<a class='btn button btn-sm btn-default' href='<?php echo ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$data['oid'] // we can execute modal here ?>'><?php echo $locale['ESHP314'] ?></a>
							<a class='btn button btn-sm btn-danger' onClick='confirmdelete();' href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=orders&amp;step=delete&amp;orderid=".$data['oid'] ?>'><?php echo $locale['delete'] ?></a>
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

$orders = new Orders();
$tab_title['title'][] = $locale['ESHP301'];
$tab_title['id'][] = 'orders';
$tab_title['icon'][] = '';
$tab_title['title'][] = $locale['ESHP302'];
$tab_title['id'][] = 'history';
$tab_title['icon'][] = '';
$tab_active = tab_active($tab_title, $_GET['o_page'], 1);
echo opentab($tab_title, $tab_active, 'pageorders', 1);
echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active, 1);
$orders->list_order();
echo closetabbody();
echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active, 1);
//include "orderhistory.php";
echo closetabbody();
echo closetab();


/*
if (isset($_POST['osrchtext'])) {
	$searchtext = stripinput($_POST['osrchtext']);
} else {
	$searchtext = $locale['SRCH161'];
}

echo "<div style='float:right;margin-top:5px;'><form id='search_form'  name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;osearch'>
<span style='vertical-align:middle;font-size:14px;'>".$locale['ESHP328']."</span>";
echo "<input type='text' name='osrchtext' class='textbox' style='margin-left:1px; margin-right:1px; margin-bottom:5px; width:160px;'  value='".$searchtext."' onblur=\"if(this.value=='') this.value='".$searchtext."';\" onfocus=\"if(this.value=='".$searchtext."') this.value='';\" />";
echo "<input type='image' id='search_image' src='".BASEDIR."eshop/img/search_icon.png' alt='".$locale['SRCH161']."' />";
echo "</form></div>";
echo "</td></tr></table>";
*/
