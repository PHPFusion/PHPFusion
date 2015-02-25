<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: functions.php
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

if (!defined("ADMIN_PANEL")) add_to_head("<link rel='stylesheet' type='text/css' href='".THEMES."templates/global/css/eshop.css' />");
add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
add_to_head("<script type='text/javascript' src='".SHOP."jquery-ui.min.js'></script>");
add_to_head("<script type='text/javascript' src='".SHOP."jquery.carouFredSel-6.0.4-packed.js'></script>");
$username = "";
echo '<script type="text/javascript">
<!--

function cartaction(id) {
    var id = parseInt(+id,10);
	var color =  $("#color_"+id+"").val();
    var dyncs =  $("#dyncs_"+id+"").val();
    var dynct =  $("#dynct_"+id+"").val();
    var qty =    $("#quantity_"+id+"").val();
    var prod =   $("#prod_"+id+"").val();
    var image =  $("#image_"+id+"").val();
	var weight = $("#weight_"+id+"").val();
	var artno =  $("#artno_"+id+"").val();
	var cprice =  $("#cprice_"+id+"").val();
    var cupon = parseInt($("#cupon_"+id+"").val(),10);
    var data = "id="+ id+"&color="+color+"&dync="+ dyncs+"&qty="+qty+"&prod="+prod+"&image="+image+"&dynct="+dynct+"&cprice="+cprice+"&weight="+weight+"&artno="+artno+"&cupon="+cupon;
    if (dyncs == "") { alert("Please select a "+dynct+""); } 
	else if (color == "") { alert("Please select a color"); } 
	else if (qty <=0) { alert("Please select quantity"); } 	else {
    $("html, body").animate({scrollTop:0}, "slow");
//    $(".product_slot_"+id+"").effect("transfer", { to: "#shoppingcart", className: "myTrans" }, 900 );	
    $(".product_slot_"+id+"").effect("transfer", { to: "#shoppingcart", className: "myTrans" }, 1000);
	$("#cartimg").attr("src","'.($settings['site_seo'] == '1' ? FUSION_ROOT : '').SHOP.'img/fullcart.png")
    $.ajax({
        type: "GET", 
        url:"'.$settings['siteurl'].'eshop/cartaction.php",
        data: data,
        beforeSend: function(result) { 
        $("#cart").html("<div style=\"height: 30px;  margin: 0px auto;\" align=\"center\"><img src=\"img/loading.gif\"></div>"); },
        success: function(result){ 
	    $("#cart").empty();
        $("#cart").show().fadeIn(1000);
        $("#cart").append(result); },timeout: 235000,
        error:function() {
        $("#cart").html("'.$locale['ESHPF100'].'");
        }
      });
    }
}

function delcitem(id) {
		var id = parseInt(+id,10);
		var data = "delete="+ id;
		var prod = $("#prod_"+id+"").val();
    $.ajax({
       type: "GET",
       url:"'.$settings['siteurl'].'eshop/cartaction.php",
       data: data,
       beforeSend: function(result) { 
       $("#incart").html("<div style=\"height: 30px;  margin: 0px auto;\" align=\"center\"><img src=\"img/loading.gif\"></div>"); },
       success: function(result){ 
	   $(".notify-bar").html(" "+prod+" have been removed").slideDown();
	   setTimeout(function () {
	   $(".notify-bar").slideUp();
	   },5000)
       $("#incart").empty();
       $("#incart").show();
       $("#incart").append(result); },timeout: 235000,
       error:function() {
       $("#incart").html("'.$locale['ESHPF100'].'");
       }
   });
}

function payment(id) {
		var id = parseInt(+id,10);
		var shipping = $("input[name=shipping]");
		var sval = shipping.filter(":checked").val();
		var cupon = $("#cupon").val();	
 		if (sval && cupon) { var data = "payment="+id+"&shipment="+sval+"&cupon="+cupon; } 
		else if (sval) { var data = "payment="+id+"&shipment="+sval; }
		else if (cupon) { var data = "payment="+id+"&cupon="+cupon; } 		
		else { var data = "payment="+id; }
		
    $.ajax({
       type: "GET",
       url:"'.$settings['siteurl'].'eshop/checkoutaction.php",
       data: data,
       beforeSend: function(result) { 
       $("#subtotal").html(""); },
       success: function(result){ 
       setTimeout(function () {
	   },5000)
       $("#subtotal").empty();
       $("#subtotal").show();
       $("#subtotal").append(result); },timeout: 235000,
       error:function() {
       $("#subtotal").html("'.$locale['ESHPF100'].'");
       }
   });
}

function shipment(id) {
		var id = parseInt(+id,10);
		var paymethod = $("input[name=paymethod]");
		var pval = paymethod.filter(":checked").val();
		var cupon = $("#cupon").val();
		if (pval && cupon) { var data = "shipment="+id+"&payment="+pval+"&cupon="+cupon; } 
		else if (pval) { var data = "shipment="+id+"&payment="+pval; } 
		else if (cupon) { var data = "shipment="+id+"&cupon="+cupon; } 
		else { 	var data = "shipment="+id; }
		
    $.ajax({
       type: "GET",
       url:"'.$settings['siteurl'].'eshop/checkoutaction.php",
       data: data,
       beforeSend: function(result) { 
       $("#subtotal").html(""); },
       success: function(result){ 
       setTimeout(function () {
	   },5000)
       $("#subtotal").empty();
       $("#subtotal").show();
       $("#subtotal").append(result); },timeout: 235000,
       error:function() {
       $("#subtotal").html("'.$locale['ESHPF100'].'");
       }
   });
}

function cuponcheck() {
	var id = $("#cupon").val();
	var paymethod = $("input[name=paymethod]");
	var pval = paymethod.filter(":checked").val();
	var shipping = $("input[name=shipping]");
	var sval = shipping.filter(":checked").val();

	if (pval && sval) { 
	var data = "shipment="+sval+"&payment="+pval+"&cupon="+id;  } 
	else if (pval)  { var data = "payment="+pval+"&cupon="+id;  } 
	else if (sval) { var data = "shipment="+sval+"&cupon="+id;  } 
	else { var data = "cupon="+ id; }
	
    $.ajax({
       type: "GET",
       url:"'.$settings['siteurl'].'eshop/checkoutaction.php",
       data: data,
       beforeSend: function(result) { 
       $("#subtotal").html(""); },
       success: function(result){ 
       setTimeout(function () {
	   },5000)
       $("#subtotal").empty();
       $("#subtotal").show();
       $("#subtotal").append(result); },timeout: 235000,
       error:function() {
       $("#subtotal").html("'.$locale['ESHPF100'].'");
       }
   });
}

function plusonecart(id) {
		var id = parseInt(+id,10);
		var data = "plusone="+ id;
		var prod = $("#prod_"+id+"").val();
	
	$.ajax({
       type: "GET",
       url:"'.$settings['siteurl'].'eshop/cartaction.php",
       data: data,
       beforeSend: function(result) { 
       $("#incart").html("<div style=\"height: 30px;  margin: 0px auto;\" align=\"center\"><img src=\"img/loading.gif\"></div>"); },
       success: function(result){ 
	   $(".notify-bar").html(" "+prod+" have been updated").slideDown();
	   setTimeout(function () {
	   $(".notify-bar").slideUp();
	   },5000)
       $("#incart").empty();
       $("#incart").show();
       $("#incart").append(result); },timeout: 235000,
       error:function() {
       $("#incart").html("'.$locale['ESHPF100'].'");
       }
	});
}

function minusonecart(id) {
		var id = parseInt(+id,10);
		var qty =   $("#quantity_"+id+"").val();
		var data = "minusone="+ id;
		var prod = $("#prod_"+id+"").val();
    if (qty <=1) { alert("'.$locale['ESHPF101'].'"); } else {
	$.ajax({
       type: "GET",
       url:"'.$settings['siteurl'].'eshop/cartaction.php",
       data: data,
       beforeSend: function(result) { 
       $("#incart").html("<div style=\"height: 30px;  margin: 0px auto;\" align=\"center\"><img src=\"img/loading.gif\"></div>"); },
       success: function(result){ 
	   $(".notify-bar").html(" "+prod+" have been updated").slideDown();
	   setTimeout(function () {
	   $(".notify-bar").slideUp();
	   },5000)
       $("#incart").empty();
       $("#incart").show();
       $("#incart").append(result); },timeout: 235000,
       error:function() {
       $("#incart").html("'.$locale['ESHPF100'].'");
       }
    });
  }
}
function qtyminus(id){
var id = +id;
var input = $("#quantity_"+id+"");
var qty =   $("#quantity_"+id+"").val();
if (qty <=1) { alert("'.$locale['ESHPF102'].'"); } 
else { input.val((parseInt(input.val()) - 1),10); }
}

function qtyplus(id){
var id = +id;
var input = $("#quantity_"+id+"");
input.val((parseInt(input.val()) + 1),10);
}
function closeDiv(){$("#close-message").fadeTo("slow",0.01,function(){$(this).slideUp("slow",function(){$(this).hide()})})}window.setTimeout("closeDiv();",5000);
$(document).ready(function() {

$(".eshopphotooverlay").colorbox({rel:"eshopphotooverlay",height:"100%",width:"100%",maxWidth:"1280px",maxHeight:"1024px",scrolling:false,transition:"elastic"});

$(".eshopphotooverlaysingle").colorbox({
    transition: "elasic", 
    height:"97%",
    width:"97%",
    maxWidth:"1280px",
    maxHeight:"1024px",
    scrolling:false,
    overlayClose:true,
    close:false,
	photo:true,
    onComplete: function(result) {
    $("#colorbox").live("click", function(){
           $(this).unbind("click");
	   $.fn.colorbox.close();
       });
    }
 });

$(".printorder").colorbox({iframe:true,height:"100%",width:"100%",maxWidth:"800px",maxHeight:"100%",transition:"none"});

$(".terms").colorbox({inline:true, width:"640px",maxWidth:"1280px",transition:"elastic"});

});
-->
</script>';


function eshopitems() {
	global $data, $locale, $settings, $aidlink;
	echo "<fieldset class='rib-wrap' style='width:".$settings['eshop_itembox_w']." !important; height:".$settings['eshop_itembox_h']." !important;'>";
	if (!$data['status'] == "1") {
		echo "<div class='ribbon-wrapper-green'><div class='ribbon-green'>".$locale['ESHPF147']."</div></div>";
	} else if ($data['campaign'] == "1") {
		echo "<div class='ribbon-wrapper-red'><div class='ribbon-red'>".$locale['ESHPF146']."</div></div>";
	} else {
		if ($data['dateadded']+$settings['eshop_newtime'] >= time()) {
			echo "<div class='ribbon-wrapper-blue'><div class='ribbon-blue'>".$locale['ESHPF145']."</div></div>";
		}
	}
	echo "<legend style='width:85% !important;text-align:center;word-break:normal;'>";
	if (checkrights("ESHP")) {
		echo "<a href='".ADMIN."eshop.php".$aidlink."&amp;a_page=Main&action=edit&id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$data['cid']."" : "")."'><img style='float:left;width:10px;height:10px;margin-top:3px;' src='".IMAGES."edit.png' border='0' /></a>";
	}
	echo "<a href='".BASEDIR."eshop.php?product=".$data['id']."'><b> ".$data['title']." </b></a></legend>";
	echo "<table width='100%' cellspacing='0' cellpadding='0' border='0' class='product_slot_".$data['id']."'>";
	echo "<tr><td ".($settings['eshop_pretext'] == "1" ? "align='left' style='width:100%;padding-top:6px;'" : "align='center' style='width:100%;padding-top:6px;'").">";
	if ($settings['eshop_ratios'] == "1") {
		echo "<a href='".BASEDIR."eshop.php?product=".$data['id']."'><img src='".($data['thumb'] ? "".checkeShpImageExists(SHOP."pictures/".$data['thumb']."")."" : "".SHOP."img/nopic_thumb.gif")."' alt='' border='0' style='height:100%;padding:4px;' /></a>";
	} else {
		echo "<a href='".BASEDIR."eshop.php?product=".$data['id']."'><img src='".($data['thumb'] ? "".checkeShpImageExists(SHOP."pictures/".$data['thumb']."")."" : "".SHOP."img/nopic_thumb.gif")."' alt='' border='0' style='height:".$settings['eshop_idisp_h']."px;width:".$settings['eshop_idisp_w']."px;padding:4px;' /></a>";
	}
	echo "</td>";
	if ($settings['eshop_pretext'] == "1") {
		echo "<td valign='top' align='left' width='100%'><div style='margin-top:15px;padding:4px;word-wrap: break-word;vertical-align:middle;width:".$settings['eshop_pretext_w'].";'>".parseubb(nl2br($data['introtext']))."</div></td>";
	}
	if ($settings['eshop_listprice'] == "1") {
		if ($settings['eshop_pretext'] == "1") {
			echo "</tr><tr><td colspan='2' valign='top' align='center' width='100%'><div style='display:block;margin-top:4px;margin-bottom:4px;'> ".$locale['ESHPF107']." ".($data['xprice'] ? "<s> ".$data['price']." </s> <b><font color='red'>".$data['xprice']."</font> </b>" : "".$data['price']."")." ".$settings['eshop_currency']."</div></td>";
		} else {
			echo "</tr><tr><td valign='top' align='center' width='100%'><div style='display:block;margin-top:4px;margin-bottom:4px;'> ".$locale['ESHPF107']." ".($data['xprice'] ? "<s> ".$data['price']." </s> <b><font color='red'>".$data['xprice']."</font> </b>" : "".$data['price']."")." ".$settings['eshop_currency']."</div></td>";
		}
	}
	echo "</tr>";
	if ($data['status'] == "1") {
		ppform();
	} else {
		if ($settings['eshop_shopmode'] == "1") {
			echo "<tr><td ".($settings['eshop_pretext'] == "1" ? "colspan='2' style='height:50px;padding:6px;'" : "")." align='center' style='height:77px;padding:6px;'>";
			echo "&nbsp;&nbsp;<a class='".($settings['eshop_info_color'] == "default" ? "button" : "eshpbutton ".$settings['eshop_info_color']."")."' href='".BASEDIR."eshop.php?product=".$data['id']."'>".$locale['ESHPF108']."</a>";
			echo "</td></tr>";
		}
	}
	echo "</table></fieldset>";
}



function buildeshopheader() {
	global $data, $locale, $settings, $username, $items, $sum, $vat, $price, $totalincvat, $rowstart, $filter, $searchtext, $category;
	$searchtext = "";
	echo "<div class='notify-bar'></div>";
	echo "<table align='center' cellspacing='0' cellpadding='0' class='tbl-border' width='100%'>";
	echo "<tr><td align='center'><div class='prodthreecol' style='display:inline;'>";
	echo "<div class='col' style='display:inline;'>";
	echo "<div style='float:left;margin-top:5px;'><a href='".BASEDIR."eshop.php' title=''><img src='".SHOP."img/home.png' style='height:40px; width:40px;' alt='' /></a></div>";
	echo "</div>"; //col 1 end
	if (!preg_match('/buynow.php/i', $_SERVER['PHP_SELF'])) {
		if (!preg_match('/cart.php/i', $_SERVER['PHP_SELF'])) {
			if (!isset($_POST['checkout'])) {
				if (!preg_match('/checkout.php/i', $_SERVER['PHP_SELF']) && (!preg_match('/checkedout.php/i', $_SERVER['PHP_SELF']))) {
					echo "<div class='col' style='display:inline;'>";
					//Middle col space
					echo "</div>"; //col 2 end
				}
				$items = "";
				$sum = "";
				$items = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
				$sum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'"));
				$vat = $settings['eshop_vat'];
				$price = $sum['totals'];
				$vat = ($price/100)*$vat;
				if ($settings['eshop_vat_default'] == "0") {
					$totalincvat = $price+$vat;
				} else {
					$totalincvat = $price;
				}
				echo "<div class='col' style='float:right;display:inline;margin-top:4px;'>";
				echo "<div style='float:left;vertical-align:middle;' id='shoppingcart'>";
				if ($items['count']) {
					echo "<img src ='".($settings['site_seo'] == "1" ? FUSION_ROOT : '').SHOP."img/fullcart.png' alt='' border='0' style='height:35px;' id='cartimg' />";
				} else {
					echo "<img src ='".($settings['site_seo'] == "1" ? FUSION_ROOT : '').SHOP."img/emptycart.png' alt='' border='0' style='height:35px;' id='cartimg' />";
				}
				echo "</div><div style='float:left;margin-left:4px;margin-top:1px;'><div id='cart' style='float:left;margin-top:8px;'>";
				echo "".($items['count'] ? $items['count'] : 0)." ".$locale['ESHPF104']." ".($settings['eshop_vat_default'] == "1" ? "".number_format($totalincvat, 2)."" : "".number_format($sum['totals'], 2)."")." ".$settings['eshop_currency']."";
				echo "</div>";
				echo "</div>";
				echo "<div style='float:left;margin-left:3px;margin-right:3px;margin-top:5px;'><a href='".SHOP."cart.php' title='cart' class='".($settings['eshop_cart_color'] == "default" ? "button" : "eshpbutton ".$settings['eshop_cart_color']."")."'>".$locale['ESHPF105']."</a></div>";
			}
			if (!preg_match('/checkout.php/i', $_SERVER['PHP_SELF']) && (!preg_match('/checkedout.php/i', $_SERVER['PHP_SELF']))) {
				echo "<div style='float:left;margin-left:3px;margin-right:3px;margin-top:5px;'><a href='".SHOP."checkout.php' title='checkout' class='".($settings['eshop_checkout_color'] == "default" ? "button" : "eshpbutton ".$settings['eshop_checkout_color']."")."'>".$locale['ESHPF106']."</a></div>";
			}
			echo "</div>"; //col 3 end
		}
	}
	echo "</div>"; //threecol end
	echo "</td></tr></table>";
	echo "<div class='clear'></div>";
}







function ppform() { // shop/buynow.php -- is this the checkout?
	global $locale, $data, $settings, $settings, $ESHPCLRS;
	if ($settings['eshop_shopmode'] == "1") {
		//options
		echo "<tr><td ".($settings['eshop_pretext'] == "1" ? "colspan='2' style='height:32px;padding:6px;'" : "")." align='center' style='height:32px;padding:6px;'>";
		echo "<div class='prodthreecol'>";
		echo "<div class='col'>";
		if ($data['dync']) {
			$dync = str_replace('"', '', $data['dync']);
			echo "<select name='dyncs_".$data['id']."' id='dyncs_".$data['id']."' class='textbox' style='width:90px !important;'>
     <option value=''>".$data['dynf']."</option>";
			$dync = explode(".", substr($dync, 1));
			for ($i = 0; $i < count($dync); $i++) {
				echo "<option value='$dync[$i]'>$dync[$i]</option>";
			}
			echo "</select>";
			echo "<input name='dynct' id='dynct_".$data['id']."' value='".$data['dynf']."' type='hidden' />";
		} else {
			echo "<input name='dyncs_".$data['id']."' id='dyncs_".$data['id']."' value='0' type='hidden' />";
			echo "<input name='dynct_".$data['id']."' id='dynct_".$data['id']."' value='0' type='hidden' />";
		}
		echo "</div>"; //col 1 end
		echo "<div class='col'>";
		if ($data['icolor']) {
			$colors = str_replace('"', '', $data['icolor']);
			echo "<select name='color_".$data['id']."' id='color_".$data['id']."' class='textbox' style='width:75px !important;'>
     <option value=''>".$locale['ESHPF109']."</option>";
			$colors = explode(".", substr($colors, 1));
			for ($i = 0; $i < count($colors); $i++) {
				if ($colors[$i] == "1") {
					echo "<option value='1' style='background-color:#F0F8FF;'>".$ESHPCLRS['1']."</option>";
				}
				if ($colors[$i] == "2") {
					echo "<option value='2' style='background-color:#FAEBD7;'>".$ESHPCLRS['2']."</option>";
				}
				if ($colors[$i] == "3") {
					echo "<option value='3' style='background-color:#00FFFF;'>".$ESHPCLRS['3']."</option>";
				}
				if ($colors[$i] == "4") {
					echo "<option value='4' style='background-color:#7FFFD4;'>".$ESHPCLRS['4']."</option>";
				}
				if ($colors[$i] == "5") {
					echo "<option value='5' style='background-color:#F0FFFF;'>".$ESHPCLRS['5']."</option>";
				}
				if ($colors[$i] == "6") {
					echo "<option value='6' style='background-color:#F5F5DC;'>".$ESHPCLRS['6']."</option>";
				}
				if ($colors[$i] == "7") {
					echo "<option value='7' style='background-color:#FFE4C4;'>".$ESHPCLRS['7']."</option>";
				}
				if ($colors[$i] == "8") {
					echo "<option value='8' style='background-color:#000000;'>".$ESHPCLRS['8']."</option>";
				}
				if ($colors[$i] == "9") {
					echo "<option value='9' style='background-color:#FFEBCD;'>".$ESHPCLRS['9']."</option>";
				}
				if ($colors[$i] == "10") {
					echo "<option value='10' style='background-color:#0000FF;'>".$ESHPCLRS['10']."</option>";
				}
				if ($colors[$i] == "11") {
					echo "<option value='11' style='background-color:#8A2BE2;'>".$ESHPCLRS['11']."</option>";
				}
				if ($colors[$i] == "12") {
					echo "<option value='12' style='background-color:#A52A2A;'>".$ESHPCLRS['12']."</option>";
				}
				if ($colors[$i] == "13") {
					echo "<option value='13' style='background-color:#DEB887;'>".$ESHPCLRS['13']."</option>";
				}
				if ($colors[$i] == "14") {
					echo "<option value='14' style='background-color:#5F9EA0;'>".$ESHPCLRS['14']."</option>";
				}
				if ($colors[$i] == "15") {
					echo "<option value='15' style='background-color:#7FFF00;'>".$ESHPCLRS['15']."</option>";
				}
				if ($colors[$i] == "16") {
					echo "<option value='16' style='background-color:#D2691E;'>".$ESHPCLRS['16']."</option>";
				}
				if ($colors[$i] == "17") {
					echo "<option value='17' style='background-color:#FF7F50;'>".$ESHPCLRS['17']."</option>";
				}
				if ($colors[$i] == "18") {
					echo "<option value='18' style='background-color:#6495ED;'>".$ESHPCLRS['18']."</option>";
				}
				if ($colors[$i] == "19") {
					echo "<option value='19' style='background-color:#FFF8DC;'>".$ESHPCLRS['19']."</option>";
				}
				if ($colors[$i] == "20") {
					echo "<option value='20' style='background-color:#DC143C;'>".$ESHPCLRS['20']."</option>";
				}
				if ($colors[$i] == "21") {
					echo "<option value='21' style='background-color:#00FFFF;'>".$ESHPCLRS['21']."</option>";
				}
				if ($colors[$i] == "22") {
					echo "<option value='22' style='background-color:#00008B;'>".$ESHPCLRS['22']."</option>";
				}
				if ($colors[$i] == "23") {
					echo "<option value='23' style='background-color:#008B8B;'>".$ESHPCLRS['23']."</option>";
				}
				if ($colors[$i] == "24") {
					echo "<option value='24' style='background-color:#B8860B;'>".$ESHPCLRS['24']."</option>";
				}
				if ($colors[$i] == "25") {
					echo "<option value='25' style='background-color:#A9A9A9;'>".$ESHPCLRS['25']."</option>";
				}
				if ($colors[$i] == "26") {
					echo "<option value='26' style='background-color:#BDB76B;'>".$ESHPCLRS['26']."</option>";
				}
				if ($colors[$i] == "27") {
					echo "<option value='27' style='background-color:#8B008B;'>".$ESHPCLRS['27']."</option>";
				}
				if ($colors[$i] == "28") {
					echo "<option value='28' style='background-color:#556B2F;'>".$ESHPCLRS['28']."</option>";
				}
				if ($colors[$i] == "29") {
					echo "<option value='29' style='background-color:#FF8C00;'>".$ESHPCLRS['29']."</option>";
				}
				if ($colors[$i] == "30") {
					echo "<option value='30' style='background-color:#9932CC;'>".$ESHPCLRS['30']."</option>";
				}
				if ($colors[$i] == "31") {
					echo "<option value='31' style='background-color:#8B0000;'>".$ESHPCLRS['31']."</option>";
				}
				if ($colors[$i] == "32") {
					echo "<option value='32' style='background-color:#E9967A;'>".$ESHPCLRS['32']."</option>";
				}
				if ($colors[$i] == "33") {
					echo "<option value='33' style='background-color:#8FBC8F;'>".$ESHPCLRS['33']."</option>";
				}
				if ($colors[$i] == "34") {
					echo "<option value='34' style='background-color:#483D8B;'>".$ESHPCLRS['34']."</option>";
				}
				if ($colors[$i] == "35") {
					echo "<option value='35' style='background-color:#2F4F4F;'>".$ESHPCLRS['35']."</option>";
				}
				if ($colors[$i] == "36") {
					echo "<option value='36' style='background-color:#00CED1;'>".$ESHPCLRS['36']."</option>";
				}
				if ($colors[$i] == "37") {
					echo "<option value='37' style='background-color:#9400D3;'>".$ESHPCLRS['37']."</option>";
				}
				if ($colors[$i] == "38") {
					echo "<option value='38' style='background-color:#FF1493;'>".$ESHPCLRS['38']."</option>";
				}
				if ($colors[$i] == "39") {
					echo "<option value='39' style='background-color:#00BFFF;'>".$ESHPCLRS['39']."</option>";
				}
				if ($colors[$i] == "40") {
					echo "<option value='40' style='background-color:#696969;'>".$ESHPCLRS['40']."</option>";
				}
				if ($colors[$i] == "41") {
					echo "<option value='41' style='background-color:#1E90FF;'>".$ESHPCLRS['41']."</option>";
				}
				if ($colors[$i] == "42") {
					echo "<option value='42' style='background-color:#B22222;'>".$ESHPCLRS['42']."</option>";
				}
				if ($colors[$i] == "43") {
					echo "<option value='43' style='background-color:#FFFAF0;'>".$ESHPCLRS['43']."</option>";
				}
				if ($colors[$i] == "44") {
					echo "<option value='44' style='background-color:#228B22;'>".$ESHPCLRS['44']."</option>";
				}
				if ($colors[$i] == "45") {
					echo "<option value='45' style='background-color:#FF00FF;'>".$ESHPCLRS['45']."</option>";
				}
				if ($colors[$i] == "46") {
					echo "<option value='46' style='background-color:#DCDCDC;'>".$ESHPCLRS['46']."</option>";
				}
				if ($colors[$i] == "47") {
					echo "<option value='47' style='background-color:#F8F8FF;'>".$ESHPCLRS['47']."</option>";
				}
				if ($colors[$i] == "48") {
					echo "<option value='48' style='background-color:#FFD700;'>".$ESHPCLRS['48']."</option>";
				}
				if ($colors[$i] == "49") {
					echo "<option value='49' style='background-color:#DAA520;'>".$ESHPCLRS['49']."</option>";
				}
				if ($colors[$i] == "50") {
					echo "<option value='50' style='background-color:#808080;'>".$ESHPCLRS['50']."</option>";
				}
				if ($colors[$i] == "51") {
					echo "<option value='51' style='background-color:#008000;'>".$ESHPCLRS['51']."</option>";
				}
				if ($colors[$i] == "52") {
					echo "<option value='52' style='background-color:#ADFF2F;'>".$ESHPCLRS['52']."</option>";
				}
				if ($colors[$i] == "53") {
					echo "<option value='53' style='background-color:#F0FFF0;'>".$ESHPCLRS['53']."</option>";
				}
				if ($colors[$i] == "54") {
					echo "<option value='54' style='background-color:#FF69B4;'>".$ESHPCLRS['54']."</option>";
				}
				if ($colors[$i] == "55") {
					echo "<option value='55' style='background-color:#CD5C5C;'>".$ESHPCLRS['55']."</option>";
				}
				if ($colors[$i] == "56") {
					echo "<option value='56' style='background-color:#4B0082;'>".$ESHPCLRS['56']."</option>";
				}
				if ($colors[$i] == "57") {
					echo "<option value='57' style='background-color:#F0E68C;'>".$ESHPCLRS['57']."</option>";
				}
				if ($colors[$i] == "58") {
					echo "<option value='58' style='background-color:#E6E6FA;'>".$ESHPCLRS['58']."</option>";
				}
				if ($colors[$i] == "59") {
					echo "<option value='59' style='background-color:#FFF0F5;'>".$ESHPCLRS['59']."</option>";
				}
				if ($colors[$i] == "60") {
					echo "<option value='60' style='background-color:#7CFC00;'>".$ESHPCLRS['60']."</option>";
				}
				if ($colors[$i] == "61") {
					echo "<option value='61' style='background-color:#FFFACD;'>".$ESHPCLRS['61']."</option>";
				}
				if ($colors[$i] == "62") {
					echo "<option value='62' style='background-color:#ADD8E6;'>".$ESHPCLRS['62']."</option>";
				}
				if ($colors[$i] == "63") {
					echo "<option value='63' style='background-color:#F08080;'>".$ESHPCLRS['63']."</option>";
				}
				if ($colors[$i] == "64") {
					echo "<option value='64' style='background-color:#E0FFFF;'>".$ESHPCLRS['64']."</option>";
				}
				if ($colors[$i] == "65") {
					echo "<option value='65' style='background-color:#FAFAD2;'>".$ESHPCLRS['65']."</option>";
				}
				if ($colors[$i] == "66") {
					echo "<option value='66' style='background-color:#D3D3D3;'>".$ESHPCLRS['66']."</option>";
				}
				if ($colors[$i] == "67") {
					echo "<option value='67' style='background-color:#90EE90;'>".$ESHPCLRS['67']."</option>";
				}
				if ($colors[$i] == "68") {
					echo "<option value='68' style='background-color:#FFB6C1;'>".$ESHPCLRS['68']."</option>";
				}
				if ($colors[$i] == "69") {
					echo "<option value='69' style='background-color:#FFA07A;'>".$ESHPCLRS['69']."</option>";
				}
				if ($colors[$i] == "70") {
					echo "<option value='70' style='background-color:#20B2AA;'>".$ESHPCLRS['70']."</option>";
				}
				if ($colors[$i] == "71") {
					echo "<option value='71' style='background-color:#87CEFA;'>".$ESHPCLRS['71']."</option>";
				}
				if ($colors[$i] == "72") {
					echo "<option value='72' style='background-color:#778899;'>".$ESHPCLRS['72']."</option>";
				}
				if ($colors[$i] == "73") {
					echo "<option value='73' style='background-color:#B0C4DE;'>".$ESHPCLRS['73']."</option>";
				}
				if ($colors[$i] == "74") {
					echo "<option value='74' style='background-color:#FFFFE0;'>".$ESHPCLRS['74']."</option>";
				}
				if ($colors[$i] == "75") {
					echo "<option value='75' style='background-color:#00FF00;'>".$ESHPCLRS['75']."</option>";
				}
				if ($colors[$i] == "76") {
					echo "<option value='76' style='background-color:#FF00FF;'>".$ESHPCLRS['76']."</option>";
				}
				if ($colors[$i] == "77") {
					echo "<option value='77' style='background-color:#800000;'>".$ESHPCLRS['77']."</option>";
				}
				if ($colors[$i] == "78") {
					echo "<option value='78' style='background-color:#66CDAA;'>".$ESHPCLRS['78']."</option>";
				}
				if ($colors[$i] == "79") {
					echo "<option value='79' style='background-color:#0000CD;'>".$ESHPCLRS['79']."</option>";
				}
				if ($colors[$i] == "80") {
					echo "<option value='80' style='background-color:#BA55D3;'>".$ESHPCLRS['80']."</option>";
				}
				if ($colors[$i] == "81") {
					echo "<option value='81' style='background-color:#9370DB;'>".$ESHPCLRS['81']."</option>";
				}
				if ($colors[$i] == "82") {
					echo "<option value='82' style='background-color:#3CB371;'>".$ESHPCLRS['82']."</option>";
				}
				if ($colors[$i] == "83") {
					echo "<option value='83' style='background-color:#7B68EE;'>".$ESHPCLRS['83']."</option>";
				}
				if ($colors[$i] == "84") {
					echo "<option value='84' style='background-color:#00FA9A;'>".$ESHPCLRS['84']."</option>";
				}
				if ($colors[$i] == "85") {
					echo "<option value='85' style='background-color:#48D1CC;'>".$ESHPCLRS['85']."</option>";
				}
				if ($colors[$i] == "86") {
					echo "<option value='86' style='background-color:#C71585;'>".$ESHPCLRS['86']."</option>";
				}
				if ($colors[$i] == "87") {
					echo "<option value='87' style='background-color:#191970;'>".$ESHPCLRS['87']."</option>";
				}
				if ($colors[$i] == "88") {
					echo "<option value='88' style='background-color:#F5FFFA;'>".$ESHPCLRS['88']."</option>";
				}
				if ($colors[$i] == "89") {
					echo "<option value='89' style='background-color:#FFE4E1;'>".$ESHPCLRS['89']."</option>";
				}
				if ($colors[$i] == "90") {
					echo "<option value='90' style='background-color:#FFE4B5;'>".$ESHPCLRS['90']."</option>";
				}
				if ($colors[$i] == "91") {
					echo "<option value='91' style='background-color:#FFDEAD;'>".$ESHPCLRS['91']."</option>";
				}
				if ($colors[$i] == "92") {
					echo "<option value='92' style='background-color:#000080;'>".$ESHPCLRS['92']."</option>";
				}
				if ($colors[$i] == "93") {
					echo "<option value='93' style='background-color:#FDF5E6;'>".$ESHPCLRS['93']."</option>";
				}
				if ($colors[$i] == "94") {
					echo "<option value='94' style='background-color:#808000;'>".$ESHPCLRS['94']."</option>";
				}
				if ($colors[$i] == "95") {
					echo "<option value='95' style='background-color:#6B8E23;'>".$ESHPCLRS['95']."</option>";
				}
				if ($colors[$i] == "96") {
					echo "<option value='96' style='background-color:#FFA500;'>".$ESHPCLRS['96']."</option>";
				}
				if ($colors[$i] == "97") {
					echo "<option value='97' style='background-color:#FF4500;'>".$ESHPCLRS['97']."</option>";
				}
				if ($colors[$i] == "98") {
					echo "<option value='98' style='background-color:#DA70D6;'>".$ESHPCLRS['98']."</option>";
				}
				if ($colors[$i] == "99") {
					echo "<option value='99' style='background-color:#EEE8AA;'>".$ESHPCLRS['99']."</option>";
				}
				if ($colors[$i] == "100") {
					echo "<option value='100' style='background-color:#98FB98;'>".$ESHPCLRS['100']."</option>";
				}
				if ($colors[$i] == "101") {
					echo "<option value='101' style='background-color:#AFEEEE;'>".$ESHPCLRS['101']."</option>";
				}
				if ($colors[$i] == "102") {
					echo "<option value='102' style='background-color:#DB7093;'>".$ESHPCLRS['102']."</option>";
				}
				if ($colors[$i] == "103") {
					echo "<option value='103' style='background-color:#FFEFD5;'>".$ESHPCLRS['103']."</option>";
				}
				if ($colors[$i] == "104") {
					echo "<option value='104' style='background-color:#FFDAB9;'>".$ESHPCLRS['104']."</option>";
				}
				if ($colors[$i] == "105") {
					echo "<option value='105' style='background-color:#CD853F;'>".$ESHPCLRS['105']."</option>";
				}
				if ($colors[$i] == "106") {
					echo "<option value='106' style='background-color:#FFC0CB;'>".$ESHPCLRS['106']."</option>";
				}
				if ($colors[$i] == "107") {
					echo "<option value='107' style='background-color:#DDA0DD;'>".$ESHPCLRS['107']."</option>";
				}
				if ($colors[$i] == "108") {
					echo "<option value='108' style='background-color:#B0E0E6;'>".$ESHPCLRS['108']."</option>";
				}
				if ($colors[$i] == "109") {
					echo "<option value='109' style='background-color:#800080;'>".$ESHPCLRS['109']."</option>";
				}
				if ($colors[$i] == "110") {
					echo "<option value='110' style='background-color:#FF0000;'>".$ESHPCLRS['110']."</option>";
				}
				if ($colors[$i] == "111") {
					echo "<option value='111' style='background-color:#BC8F8F;'>".$ESHPCLRS['111']."</option>";
				}
				if ($colors[$i] == "112") {
					echo "<option value='112' style='background-color:#8B4513;'>".$ESHPCLRS['112']."</option>";
				}
				if ($colors[$i] == "113") {
					echo "<option value='113' style='background-color:#FA8072;'>".$ESHPCLRS['113']."</option>";
				}
				if ($colors[$i] == "114") {
					echo "<option value='114' style='background-color:#F4A460;'>".$ESHPCLRS['114']."</option>";
				}
				if ($colors[$i] == "115") {
					echo "<option value='115' style='background-color:#2E8B57;'>".$ESHPCLRS['115']."</option>";
				}
				if ($colors[$i] == "116") {
					echo "<option value='116' style='background-color:#FFF5EE;'>".$ESHPCLRS['116']."</option>";
				}
				if ($colors[$i] == "117") {
					echo "<option value='117' style='background-color:#A0522D;'>".$ESHPCLRS['117']."</option>";
				}
				if ($colors[$i] == "118") {
					echo "<option value='118' style='background-color:#C0C0C0;'>".$ESHPCLRS['118']."</option>";
				}
				if ($colors[$i] == "119") {
					echo "<option value='119' style='background-color:#87CEEB;'>".$ESHPCLRS['119']."</option>";
				}
				if ($colors[$i] == "120") {
					echo "<option value='120' style='background-color:#6A5ACD;'>".$ESHPCLRS['120']."</option>";
				}
				if ($colors[$i] == "121") {
					echo "<option value='121' style='background-color:#708090;'>".$ESHPCLRS['121']."</option>";
				}
				if ($colors[$i] == "122") {
					echo "<option value='122' style='background-color:#FFFAFA;'>".$ESHPCLRS['122']."</option>";
				}
				if ($colors[$i] == "123") {
					echo "<option value='123' style='background-color:#00FF7F;'>".$ESHPCLRS['123']."</option>";
				}
				if ($colors[$i] == "124") {
					echo "<option value='124' style='background-color:#4682B4;'>".$ESHPCLRS['124']."</option>";
				}
				if ($colors[$i] == "125") {
					echo "<option value='125' style='background-color:#D2B48C;'>".$ESHPCLRS['125']."</option>";
				}
				if ($colors[$i] == "126") {
					echo "<option value='126' style='background-color:#008080;'>".$ESHPCLRS['126']."</option>";
				}
				if ($colors[$i] == "127") {
					echo "<option value='127' style='background-color:#D8BFD8;'>".$ESHPCLRS['127']."</option>";
				}
				if ($colors[$i] == "128") {
					echo "<option value='128' style='background-color:#FF6347;'>".$ESHPCLRS['128']."</option>";
				}
				if ($colors[$i] == "129") {
					echo "<option value='129' style='background-color:#40E0D0;'>".$ESHPCLRS['129']."</option>";
				}
				if ($colors[$i] == "130") {
					echo "<option value='130' style='background-color:#EE82EE;'>".$ESHPCLRS['130']."</option>";
				}
				if ($colors[$i] == "131") {
					echo "<option value='131' style='background-color:#F5DEB3;'>".$ESHPCLRS['131']."</option>";
				}
				if ($colors[$i] == "132") {
					echo "<option value='132' style='background-color:#FFFFFF;'>".$ESHPCLRS['132']."</option>";
				}
				if ($colors[$i] == "133") {
					echo "<option value='133' style='background-color:#F5F5F5;'>".$ESHPCLRS['133']."</option>";
				}
				if ($colors[$i] == "134") {
					echo "<option value='134' style='background-color:#FFFF00;'>".$ESHPCLRS['134']."</option>";
				}
				if ($colors[$i] == "135") {
					echo "<option value='135' style='background-color:#9ACD32;'>".$ESHPCLRS['135']."</option>";
				}
			}
			echo "</select>";
		} else {
			echo "<input name='color_".$data['id']."' id='color_".$data['id']."' type='hidden' value='0' />";
		}
		echo "</div>"; //col 2 end
		echo "<div class='col'>";
		if ($data['qty'] == "1") {
			echo "<a href='javascript:;' onclick='javascript:qtyminus(".$data['id']."); return false;'><img src='".SHOP."img/minus.png' border='0' alt='' style='vertical-align:middle !important;' /></a><input type='text' name='quantity_".$data['id']."' id='quantity_".$data['id']."' value='".($data['dmulti'] >= "1" ? "".$data['dmulti']."" : "1")."' class='textbox' style='width:18px !important;' /><a href='javascript:;' onclick='javascript:qtyplus(".$data['id']."); return false;'><img src='".SHOP."img/plus.png' border='0' alt='' style='vertical-align:middle !important;' /></a>";
		} else {
			echo "<input name='quantity_".$data['id']."' id='quantity_".$data['id']."' type='hidden' value='1' />";
		}
		echo "</div>"; //col 3 end
		echo "</div>"; //threecold end
		echo "<div style='clear:both;'></div>";
		echo "</td></tr>";
		//buttons
		echo "<tr><td ".($settings['eshop_pretext'] == "1" ? "colspan='2'" : "")." align='center' style='padding:6px;'>";
		echo "<div class='prodthreecol'>";
		if ($data['cart_on'] == "1") {
			echo "<input name='prod_".$data['id']."' id='prod_".$data['id']."' value='".$data['title']."' type='hidden' />";
			echo "<input name='artno_".$data['id']."' id='artno_".$data['id']."' value='".($data['artno'] ? $data['artno'] : $data['id'])."' type='hidden' />";
			echo "<input name='image_".$data['id']."' id='image_".$data['id']."' value='".($data['thumb'] ? $data['thumb'] : "0")."' type='hidden' />";
			echo "<input name='weight_".$data['id']."' id='weight_".$data['id']."' value='".($data['weight'] ? $data['weight'] : "0")."' type='hidden' />";
			echo "<input name='cprice_".$data['id']."' id='cprice_".$data['id']."' value='".($data['xprice'] ? $data['xprice'] : $data['price'])."' type='hidden' />";
			echo "<input name='cupon_".$data['id']."' id='cupon_".$data['id']."' value='".$data['cupons']."' type='hidden' />";
			echo "<div class='col'>&nbsp;&nbsp;<a class='".($settings['eshop_addtocart_color'] == "default" ? "button" : "eshpbutton ".$settings['eshop_addtocart_color']."")."' href='javascript:;' onclick='javascript:cartaction(".$data['id']."); return false;'>".$locale['ESHPF110']."</a></div>";
		}
		if ($data['buynow'] == "1") {
			echo "<div class='col'>&nbsp;&nbsp;<a class='".($settings['eshop_buynow_color'] == "default" ? "button" : "eshpbutton ".$settings['eshop_buynow_color']."")."' href='".($settings['site_seo'] ? FUSION_ROOT : "").SHOP."buynow.php?id=".$data['id']."'>".$locale['ESHPF111']."</a></div>";
		}
		echo "<div class='col'>&nbsp;&nbsp;<a class='".($settings['eshop_info_color'] == "default" ? "button" : "eshpbutton ".$settings['eshop_info_color']."")."' href='".BASEDIR."eshop.php?product=".$data['id']."'>".$locale['ESHPF108']."</a></div>";
		echo "</div>";
		echo "<div style='clear:both;'></div>";
		echo "</td></tr>";
	}
}

function breadcrumb($cid) {
	global $locale;
	$bcq = dbquery("SELECT * from ".DB_ESHOP_CATS." WHERE status='1' AND cid=$cid");
	if (dbrows($bcq) != 0) {
		while ($bcd = dbarray($bcq)) {
			$title = getparentlink($bcd['parentid'], $bcd['title'], $bcd['cid']);
		}
		return $title;
	}
}

function breadseo($cid) {
	global $locale;
	$bcq = dbquery("SELECT * from ".DB_ESHOP_CATS." WHERE status='1' AND cid=$cid");
	if (dbrows($bcq) != 0) {
		while ($bcd = dbarray($bcq)) {
			$title = getparenttitle($bcd['parentid'], $bcd['title'], $bcd['cid']);
		}
		add_to_title(" - ".$title."");
	}
}

function getlink($title, $cid) {
	global $db_prefix, $locale;
	$data = dbarray(dbquery("select cid, title from ".DB_ESHOP_CATS." where cid=$cid"));
	$title = '<a href="'.INFUSIONS.'eshop.php?category='.$data['cid'].'"><b>'.$data['title'].'</b></a>';
	return $title;
}

function getparent($parentid, $title) {
	global $locale;
	$result = dbquery("select * from ".DB_ESHOP_CATS." where cid=$parentid");
	$data = dbarray($result);
	if ($data['title'] != "") $title = $data['title']." &raquo; ".$title;
	if ($data['parentid'] != 0) {
		$title = getparent($data['parentid'], $title);
	}
	return $title;
}

function getparentlink($parentid, $title, $cid) {
	global $locale;
	$data = dbarray(dbquery("select cid, title, parentid from ".DB_ESHOP_CATS." where cid=$parentid"));
	if ($data['title'] != "") {
		$title = '<div class="crumbstart"><div class="crumbarrow"><a class="homeLink" href="'.INFUSIONS.'eshop.php?category='.$data['cid'].'"><b>'.$data['title'].'</b></a></div><div class="activecrumb">  <a href="'.INFUSIONS.'eshop.php?category='.$cid.'"><b>'.$title.'</b></a></div></div>';
	}
	if ($data['parentid'] != 0) {
		$title = getparentlink($data['parentid'], $title, $cid);
	}
	if ($data['parentid'] == 0) {
		$title = '<div class="activecrumb"><a href="'.INFUSIONS.'eshop.php?category='.$cid.'"><b>'.$title.'</b></a></div>';
	}
	return $title;
}

function getparenttitle($parentid, $title, $cid) {
	global $locale;
	$data = dbarray(dbquery("select cid, title, parentid from ".DB_ESHOP_CATS." where cid=$parentid"));
	if ($data['title'] != "") {
		$title = ''.$data['title'].' &raquo; '.$title.'';
	}
	if ($data['parentid'] != 0) {
		$title = getparenttitle($data['parentid'], $title, $cid);
	}
	if ($data['parentid'] == 0) {
		$title = $title;
	}
	return $title;
}

function getcolorname($id) {
	global $ESHPCLRS;
	$id = "{$ESHPCLRS[$id]}";
	return $id;
}

//$itemlist = dupedel($itemlist); //I made this to swap and sort array to delete all duplicated numbers in the array but we need them all to be in the array for the +sellcount and the -stock count. Let´s save it for popular products queries etc..
function dupedel($itemlist) {
	return implode('.', array_keys(array_flip(explode('.', $itemlist))));
}

/** Domi's original code calculations for vat/subtotal/grandtotal/
/* Domi's Calculation Codes
$vat = $settings['eshop_vat'];
if (isset($_POST['buynow'])) {
$itemdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_POST['id']."'"));
$price = ($itemdata['xprice'] ? $itemdata['xprice'] : $itemdata['price']);
} else {
$price = $sum['totals'];
}
$vat = ($price/100)*$vat;
if ($settings['eshop_vat_default'] == "0") {
$totalincvat = $price+$vat;
} else {
$totalincvat = $price;
}
$shippingsurcharge = $shipping['weightcost'];
$shippinginitial = $shipping['initialcost'];
$shippingsurcharge = $shippingsurcharge*$weight['weight'];
$shippingtotal = $shippingsurcharge+$shippinginitial;
$paymentsurcharge = $payment['surcharge'];
if (isset($_POST['cupon']) && $_POST['cupon'] !== $locale['ESHPCHK171']) {
$cupons = stripinput($_POST['cupon']);
if (iMEMBER) {
$verifycupon = dbquery("SELECT * FROM ".DB_ESHOP_CUSTOMERS." WHERE ccupons LIKE '%.".$cupons."' LIMIT 0,1");
if (!dbrows($verifycupon) != 0) {
$cupon = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$cupons."' AND active = '1' AND (custart='0'||custart<=".time().") AND (cuend='0'||cuend>=".time().") LIMIT 0,1"));
$cuponsum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='1'"));
$cuponexcluded = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' AND ccupons='0'"));
$cupons = ".".$cupon['cuid']."";
if ($cupon['cutype'] == "1") {
if ($cupon['cuvalue'] > $cuponsum['totals']) {
$discount = $locale['ESHPCHK177'];
$cupons = "";
} else {
$discvalue = $cupon['cuvalue'];
$discalc = $discvalue;
$discount = "".number_format($discvalue)." ".$settings['eshop_currency']."";
}
} else if ($cupon['cutype'] == "0") {
$discount = $cupon['cuvalue'];
$dvat = $settings['eshop_vat'];
$itemstocalc = $cuponsum['totals'];
if ($settings['eshop_vat_default'] == "0") {
$dvat = ($itemstocalc/100)*$dvat;
$discalc = $itemstocalc+$dvat;
} else {
$discalc = $itemstocalc;
}
$discalc = ($discalc/100)*$discount;
$discount = "".number_format($discalc)." ".$settings['eshop_currency']."";
} else {
$discount = $locale['ESHPCHK179'];
$cupons = "";
}
}
}
}
 */


?>