<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: orderhistory.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

//Base functions here are courtesy by Kneeko

$years_per_row = 5;

$months = explode("|", $locale['months']);
$mode = "";
if (isset($_GET['mode']) && ($_GET['mode'] == 'm' || $_GET['mode'] == 'd')) $mode = $_GET['mode'];
if ($mode == 'm') {
	if (!isset($_GET['year']) || !isNum($_GET['year']) || !isset($_GET['month']) || !isNum($_GET['month'])) {
		$mode = "";
	} else {
		$get_year = $_GET['year'];
		$get_month = $_GET['month'];
	}
}
if ($mode == 'd') {
	if (!isset($_GET['year']) || !isNum($_GET['year']) || !isset($_GET['month']) || !isNum($_GET['month']) || !isset($_GET['day']) || !isNum($_GET['day'])) {
		$mode = "";
	} else {
		$get_year = $_GET['year'];
		$get_month = $_GET['month'];
		$get_day = $_GET['day'];
	}
}

switch ($mode) {
	case 'm': {
		add_to_title(" - ".$months[$get_month]." ".$get_year);
		echo "<div align='center'><br />\n";
		$m_stats = array();
		$m_days = cal_days_in_month(CAL_GREGORIAN, $get_month, $get_year);
		if ($get_year == date("Y", time()) && $get_month == date("m", time())) $m_days = date("d", time());
		$m_min = mktime(0, 0, 0, $get_month, 1, $get_year) - 1;
		$m_max = $m_min + ($m_days * 24 * 60 * 60);
		$dates = dbquery("SELECT odate FROM ".DB_ESHOP_ORDERS." WHERE odate > '".$m_min."' && odate < '".$m_max."' ORDER BY oid ASC");
		if (dbrows($dates)) {
			while ($id = dbarray($dates)) {
				$m_day = date("j", $id['odate']);
				if (!isset($m_stats[$m_day])) {
					$m_stats[$m_day] = 1;
				} else {
					$m_stats[$m_day]++;
				}
			}

			echo "<p>[<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=history'>".$locale['ESHP329']."</a>]</p><br />";
			echo "<table width='200' class='tbl-border' cellspacing='1' cellpadding='0'>\n";
			echo "  <tr>\n    <td class='scapmain' style='font-weight: bold'>$months[$get_month] $get_year</td>
							  <td class='scapmain' align='center' style='font-weight: bold'>#</td>\n  </tr>\n";
						  

			for ($i = 1; $i < $m_days + 1; $i++) {
				echo "  <tr>\n";
				echo "    <td align='left' class='tbl" . ($i % 2 ? "1" : "2") . "'>" . $i . ", <a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=history&amp;mode=d&amp;year=$get_year&amp;month=$get_month&amp;day=$i'>".$locale['ESHP334'][date("w", mktime(1, 1, 1, $get_month, $i, $get_year))]."</a></td>\n";
				echo "    <td align='center' class='tbl" . ($i % 2 ? "1" : "2") . "'>". (isset($m_stats[$i]) ? $m_stats[$i] : "-") . "</td>\n";
					
			echo "  </tr>\n";
			}
			echo "</table>\n</div>\n";
		} else {
			echo "<p>".$locale['ESHP331']."</p>\n";
		}
		unset($m_stats, $m_days, $m_min, $m_max, $dates, $id, $get_year, $get_month, $i);
		break;
	}

	case 'd': {
		add_to_title(" - ".$get_day." ".$months[$get_month]." ".$get_year);
		echo "<div align='center'><br />\n";
		echo "<p>[<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=history'>".$locale['ESHP330']."</a>] [<a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=history&amp;mode=m&amp;year=$get_year&amp;month=$get_month'> $months[$get_month] $get_year </a>]</p><br />";
		$d_min = mktime(23, 59, 59, $get_month, $get_day-1, $get_year);
		$d_max = $d_min + (24 * 60 * 60);
		$orders = dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE odate > '".$d_min."' && odate < '".$d_max."' ORDER BY oid ASC");
        echo "<div class='scapmain' style='font-weight: bold'>".$locale['ESHP332']." ".date("d.m.Y", $d_min+1)."</div><br />";
		echo "<table align='center' cellspacing='4' cellpadding='0' class='tbl-border' width='99%'>\n";
		if (dbrows($orders) > 0) {
		echo "<tr>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP317']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP318']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP319']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP320']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP321']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP322']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP323']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP335']."</b></td>
		<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP324']."</b></td>
		</tr>\n";
		while ($order = dbarray($orders)) {
		echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
		echo "<td width='1%' align='center'> <a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;vieworder&amp;orderid=".$order['oid']."'><b>".$order['oid']."</b></a></td>\n";
$usercheck= dbquery("SELECT user_id FROM  ".DB_USERS." WHERE user_id = '".$order['ouid']."'");
$urows = dbrows($usercheck);
if ($urows != 0) {
		echo "<td width='1%' align='center'> <a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=edit&amp;cuid=".$order['ouid']."'>".$order['oname']."</a></td>";
} else {
		echo "<td width='1%' align='center'> ".$order['oname']."</td>";
}
		echo "<td width='1%' align='center'>".$order['ototal']." ".$settings['eshop_currency']."</a></td>";
		echo "<td width='1%' align='center'>".($order['opaid'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_red.png' height='20' alt'' />")."</td>";
		echo "<td width='1%' align='center'>".($order['ocompleted'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_red.png' height='20' alt'' />")."</td>";
		echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;vieworder&amp;orderid=".$order['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/orderlist.png' alt='' border='0' /></a></td>";
		echo "<td width='1%' align='center'><a class='printorder' href='".ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$order['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/print.png' alt='' border='0' /></a></td>";
		echo "<td width='1%' align='center'>". date("H:i:s", $order['odate']) . "</td>";
		echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;step=delete&amp;orderid=".$order['oid']."'  onClick='return confirmdelete();'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/remove.png' border='0'  alt='' /></a></td>";
				echo "  </tr>\n";
			}
		} else {
			echo "  <tr>\n";
			echo "    <td colspan='2' class='tbl1 alt' align='center'>".$locale['ESHP333']."</td>\n";
			echo "  </tr>\n";
		}
		echo "</table>\n</div>\n";
		break;
	}
	default: {
		$stats = array(); 
		$dates = dbquery("SELECT YEAR(from_unixtime(odate)) as year, MONTH(from_unixtime(odate)) as month, SUM(ototal) as total_sales, count(oid) as orders
		 FROM ".DB_ESHOP_ORDERS."
		 GROUP BY YEAR(from_unixtime(odate)) DESC, MONTH(from_unixtime(odate)) DESC
		 ORDER BY year DESC, month DESC");
		while ($id = dbarray($dates)) {
			$stats[] = $id;
		}
		?>
		<table class='table table-responsive table-striped m-t-20'>
			<tr>
				<th>Month</th>
				<th>Year</th>
				<th>Number of Orders</th>
				<th>Total Sales</th>
			</tr>
			<?php
			foreach($stats as $sales) {
				?>
				<tr>
					<td><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=history&amp;mode=m&amp;year=".$sales['year']."&amp;month=".$sales['month'] ?>'><?php echo $months[$sales['month']] ?></a></td>
					<td><a href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=history&amp;mode=y&amp;year=".$sales['year'] ?>'><?php echo $sales['year'] ?></td>
					<td><?php echo $sales['orders'] ?></td>
					<td><?php echo $sales['total_sales']." ".fusion_get_settings('eshop_currency') ?></td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
	}
}

unset($months, $mode);
