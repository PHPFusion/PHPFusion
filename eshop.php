<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
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
require_once dirname(__FILE__)."/maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."eshop.php";
require_once THEMES."templates/global/eshop.php";
//include INCLUDES."eshop_functions_include.php";

//Close the tree when eShop home have been clicked.
/*
if ($settings['eshop_cats'] == "1") {
echo '<script type="text/javascript"> 
	d.closeAll();
</script>';
}
*/
$eShop = new PHPFusion\Eshop();
$info = $eShop->get_category();
$info += $eShop->get_product();
$info += $eShop->get_featured();
$info += $eShop->get_title();
$info += $eShop->get_product_photos();
render_eshop_nav($info);
if ($_GET['category']) {
	// view category page
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
} elseif ($_GET['product']) {
	// view product page
	render_eshop_product($info);
} else {
	render_eshop_featured_url($info);
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
}

function render_eshop_product($info) {
	//print_p($info);
	/*
	 * [item] => Array
        (
            [16] => Array
                (
	[qty] - unrestricted quantity buy at a go
                    [id] => 16
                    [title] => Mobile 99
                    [cid] => 16
                    [picture] => ./eshop/pictures/cover_large.jpg
                    [thumb] => ./eshop/pictures/thumb/cover_large_t1.jpg
                    [thumb2] => cover_large_t2.jpg
                    [introtext] =>
                    [description] =>
                    [anything1] =>
                    [anything1n] =>
                    [anything2] =>
                    [anything2n] =>
                    [anything3] =>
                    [anything3n] =>
                    [weight] =>

                    [stock] => 1 // show stock?
                    [version] =>
                    [status] => 1 // in stock?
                    [active] => 1
                    [gallery_on] => 1
                    [delivery] => 0
                    [demo] =>
                    [cart_on] => 1
                    [buynow] => 1

	[rpage] => crl.php
                    [icolor] =>
                    [dynf] =>
                    [dync] =>


                    [dmulti] => 1
                    [cupons] => 1
                    [access] => 0
                    [campaign] => 0
                    [comments] => 1
                    [ratings] => 1
                    [linebreaks] => 1

                    [category_title] => Game
                    [category_link] => ./category=16
                    [link] => ./eshop.php?product=16
                )

        )
	 */
	global $locale;
	global $eShop;
	$data = $info['item'][$_GET['product']];
	echo "<div class='m-t-10'>\n";
	echo render_breadcrumbs();
	echo "</div>\n";

	echo "<div class='row product_slot_".$data['id']."'>\n<div class='col-xs-12 col-sm-5'>\n";

	// design an image carousel.


	// Images
	echo "<div class='rib-wrap itembox'>";
	if (!$data['status'] == "1") {
		echo "<div class='ribbon-wrapper-green'><div class='ribbon-green'>".$locale['ESHPF147']."</div></div>";
	} else if ($data['campaign'] == "1") {
		echo "<div class='ribbon-wrapper-red'><div class='ribbon-red'>".$locale['ESHPF146']."</div></div>";
	} else {
		if ($data['dateadded']+fusion_get_settings('eshop_newtime') >= time()) {
			echo "<div class='ribbon-wrapper-blue'><div class='ribbon-blue'>".$locale['ESHPF145']."</div></div>";
		}
	}
	// picture container
	echo "<img title='".$data['title']."' id='photo_container' ".(fusion_get_settings('eshop_ratios') ? "class='img-responsive'" : "style='width:".fusion_get_settings('eshop_idisp_w2')."px; height: ".fusion_get_settings('eshop_idisp_h2')."px;' ")." src='".$data['picture']."'>\n";
	echo "</div>\n";

	if ($data['gallery_on'] == "1") {
		// add a change source of photo_container is sufficient for default template.
		add_to_jquery("
		$('.imgclass').bind('click', function(e) {
			$('#photo_container').prop('src', $(this).data('url'));
		});
		");
		if (!empty($info['photos'])) {
			foreach($info['photos'] as $photos) {
				echo "<div class='pointer display-inline-block m-t-20 m-r-10 imgclass' style='width:20%' data-url='".$photos['photo_filename']."'>\n";
				echo thumbnail($photos['photo_thumb1'], '100%');
				echo "</div>\n";
			}
		}
	}
	echo "</div>\n<div class='col-xs-12 col-sm-7'>\n";
	echo "<h2 class='product-title m-b-0'>".$data['title']."</h2>";
	echo $eShop->display_social_buttons($data['id'], $data['picture'], $data['title']); // there is a wierd behavior in social buttons i cannot push this array into $info.
	// product basic information
	echo "<div class='text-smaller'>\n";
	echo "<span class='display-block'>Product-Serial: ".$data['artno']."</span>\n";
	echo "<span class='display-block'>".$data['stock_status']."</span>\n";
	echo "<span class='display-block'>".$data['version']."</span>";
	echo "<span class='display-block'>".$data['shipping']."</span>";

	if ($data['demo']) {
		echo "<span class='display-block'>";
		$urlprefix = !strstr($data['demo'], "http://") ? "http://" : "";
		echo $locale['ESHP013'].": <a href='".$urlprefix.$data['demo']."' target='_blank'>".$locale['ESHP015']."</a>";
		echo "</span>\n";
	}


	echo "</div>\n";
	// keywords
	$keywords = $data['keywords'] ? explode(',', $data['keywords']) : '';
	if (!empty($keywords)) {
		echo "<div class='text-smaller'>\n";
		echo "<span>Tags:</span> \n";
		foreach($keywords as $tag) {
			echo "<a class='display-inline m-r-10' href=''>".$tag."</a>";
		}
		echo "</div>\n";
	}
	// price
	if ($data['xprice']) {
		echo "<div class='m-t-20'>\n";
		echo "
		<div class='eshop-price'>
			<span><small>".fusion_get_settings('eshop_currency')."</small> ".number_format($data['xprice'],2)."</span>
			<span class='eshop-discount label label-danger'>".number_format(100-($data['xprice']/$data['price']*100))."% ".$locale['off']."</span>
		</div>
		<span class='eshop-xprice'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($data['price'],2)."</span>\n";
		echo "</div>\n";
	} else {
		echo "<div class='m-t-20'>\n";
		echo "<div class='eshop-price'><small>".fusion_get_settings('eshop_currency')."</small>".number_format($data['price'],2)."</div>\n";
		echo "</div>\n";
	}

	// ok now do the add cart thing. we need a form to push to cart or buynow.
	echo openform('productfrm', 'productfrm','post', BASEDIR."eshop.php?product=".$_GET['product']);
	echo "<div class='m-t-20'>\n";
	if (!empty($data['dync'])) {
		$title = $data['dynf'] ? $data['dynf'] : 'Category';
		$dync = str_replace('&quot;', '', $data['dync']);
		$dync_opts = array_filter(explode('.', $dync));
		echo form_select($title, 'product_type', 'product_type', $dync_opts, '', array('inline'=>1, 'class'=>'product-selector m-b-0'));
	}
	if ($data['icolor']) {
		echo "<div class='form-group m-t-10'>\n";
		echo "<label class='col-xs-12 col-sm-3 text-smaller p-l-0'>".$locale['ESHP017']."</label>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";
		$color = str_replace('&quot;', '', $data['icolor']);
		$full_colors = PHPFusion\Eshop::get_iColor();
		$current_colors = array_filter(explode('.', $color));
		foreach($current_colors as $val) {
			$color = $full_colors[$val]['hex'];
			$title = $full_colors[$val]['title'];
			echo "<div><input id='".$color."' type='radio' name='icolor' value='".$val."'>
			<span class='display-inline-block' style='background: $color; width:15px; height:15px; border-radius:50%; margin-left:5px;'>&nbsp;</span>
			<small class='p-l-10'><label for='".$color."'>$title</label></small>
			</div>";
		}
		echo "</div>\n";
		echo "</div>\n";
	}
	// qty
	echo form_hidden('', 'id', 'id', $data['id']);
	echo "</div>\n";
	echo closeform();


	/*
				if ($data['qty'] == "1") {
					echo "<tr><td class='tbl' align='center'><fieldset><legend align='center' style='margin-left:2px !important; width:85% !important;'>&nbsp; ".$locale['ESHP019']." &nbsp;</legend>";
					echo "<div style='padding:3px;'><a href='javascript:;' onclick='javascript:qtyminus(".$data['id']."); return false;'><img src='".BASEDIR."eshop/img/minus.png' border='0' alt='' style='vertical-align:middle;' /></a><input type='text' name='quantity_".$data['id']."' id='quantity_".$data['id']."' value='".($data['dmulti'] >= "1" ? "".$data['dmulti']."" : "1")."' class='textbox' style='width:70px !important;' /><a href='javascript:;' onclick='javascript:qtyplus(".$data['id']."); return false;'><img src='".BASEDIR."eshop/img/plus.png' border='0' alt='' style='vertical-align:middle;' /></a></div>";
					echo "</fieldset></td></tr>";
				} else {
					echo "<input name='quantity_".$data['id']."' id='quantity_".$data['id']."' type='hidden' value='1' />";
				}
				echo "<input name='prod_".$data['id']."' id='prod_".$data['id']."' value='".$data['title']."' type='hidden' />";
				echo "<input name='artno_".$data['id']."' id='artno_".$data['id']."' value='".($data['artno'] ? $data['artno'] : $data['id'])."' type='hidden' />";
				echo "<input name='image_".$data['id']."' id='image_".$data['id']."' value='".($data['thumb'] ? $data['thumb'] : "0")."' type='hidden' />";
				echo "<input name='weight_".$data['id']."' id='weight_".$data['id']."' value='".($data['weight'] ? $data['weight'] : "0")."' type='hidden' />";
				echo "<input name='cprice_".$data['id']."' id='cprice_".$data['id']."' value='".($data['xprice'] ? $data['xprice'] : $data['price'])."' type='hidden' />";
				echo "<input name='cupon_".$data['id']."' id='cupon_".$data['id']."' value='".$data['cupons']."' type='hidden' />";
			}
	 */




	// change buynow color.
	if ($data['status'] == "1") {
		echo "<div class='m-t-20'>\n";
		if ($data['buynow'] == "1") {
			echo "<a class='btn m-r-10 ".fusion_get_settings('eshop_buynow_color')."' href='".BASEDIR."eshop/buynow.php?id=".$data['id']."'>".$locale['ESHP020']."</a>";
		}

		if ($data['cart_on'] == "1") {
			echo "<a class='btn m-r-10 ".fusion_get_settings('eshop_addtocart_color')."' href='javascript:;' onclick='javascript:cartaction(".$data['id']."); return false;'><i class='fa fa-shopping-cart m-t-5 m-r-10'></i> ".$locale['ESHP021']."</a>";
		}
		echo "</div>\n";
	}

	echo "</div>\n</div>\n";

	$tab_title['title'][] = $locale['ESHP022'];
	$tab_title['id'][] = 'pdesc';
	$tab_title['icon'][] = '';
	$any = array();
	if ($data['anything1'] && $data['anything1n']) {
		$any['a1'] = array('title'=>'anything1n', 'data'=>'anything1');
		$tab_title['title'][] = $data['anything1n'];
		$tab_title['id'][] = 'a1';
		$tab_title['icon'][] = '';
	}
	if ($data['anything2'] && $data['anything2n']) {
		$any['a2'] = array('title'=>'anything2n', 'data'=>'anything2');
		$tab_title['title'][] = $data['anything2n'];
		$tab_title['id'][] = 'a2';
		$tab_title['icon'][] = '';
	}
	if ($data['anything3'] && $data['anything3n']) {
		$any['a3'] = array('title'=>'anything3n', 'data'=>'anything3');
		$tab_title['title'][] = $data['anything3n'];
		$tab_title['id'][] = 'a3';
		$tab_title['icon'][] = '';
	}

	$tab_active = tab_active($tab_title, 0);
	echo opentab($tab_title, $tab_active, 'product-tabs');
	echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
	echo "<span class='display-block m-t-10'>".stripslashes(nl2br($data['description']))."</span>";
	echo closetabbody();
	if (!empty($any)) {
		foreach($any as $id => $tab_data) {
			echo opentabbody($data[$tab_data['title']], $id, $tab_active);
			echo "<span class='display-block m-t-10'>".stripslashes(nl2br($data[$tab_data['data']]))."</span>";
			echo closetabbody();
		}
	}
	echo closetab();

	echo "<a class='btn ".fusion_get_settings('eshop_return_color')."' href='javascript:;' onclick='javascript:history.back(-1); return false;'>".$locale['ESHP030']."</a>";

	///// ----

	global $eShop, $locale;
	$eshop = $eShop;
	$settings = fusion_get_settings();
	if (isset($_GET['product'])) {
		$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_GET['product']."' AND ".groupaccess('access')." AND active = '1' LIMIT 0,1"));
		if ($data) {
			echo "<div style='width:19%; float:right;'>";
			echo "<table align='center' border='0' cellpadding='0' cellspacing='0' width='100%'>";
			echo "</table>";
			echo "</div>";
			//End item lookup
		} else {
			//echo "<div class='admin-message'>".$locale['ESHP024']."</div>";
		}
	}
}

//////////////--------- <3><  ------------------ ///////////////




// mvc functions
//buildeshopheader();

//item details start
/*
elseif (isset($_GET['category'])) {

//Expand selected category if we have folderlinks on.
if ($settings['eshop_folderlink'] == "1") {
	echo '<script type="text/javascript"> 
	d.openTo('.$_GET['category'].', true);
	</script>';
}

//Check if we have a maincat and if subcats are there.
$resultc = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='".$_GET['category']."'");
if (dbrows($resultc)) {
echo breadseo($_GET['category']);
echo "<div class='clear'></div>";

//check featured banners
buildeshopbanners();

//Check featured section first
$result= dbquery("SELECT ter.* FROM ".DB_ESHOP." ter
		LEFT JOIN ".DB_ESHOP_FEATITEMS." titm ON ter.id=titm.featitem_item
		WHERE featitem_cid = '".($_REQUEST['category'] ? $_REQUEST['category'] : "0")."' ORDER BY featitem_order");
$rows = dbrows($result);

if ($rows) {
$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($result)) {
	if ($counter != 0 && ($counter % $settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
    echo "<td align='center' class='tbl'>\n";
	eshopitems();
	echo "</td>\n";
	$counter++;
}
	echo "</tr>\n</table>\n";
echo "<hr />";
} 
	$counter = 0;
	echo "<table cellpadding='0' cellspacing='4' width='100%'>\n<tr>\n";
	while ($data = dbarray($resultc)) {
	if ($counter != 0 && ($counter % $settings['eshop_cipr'] == 0)) echo "</tr>\n<tr>\n";
	if ($settings['eshop_cat_disp'] == "1") {
		echo "<td align='center' valign='top' class='arealist' onclick=\"location='".BASEDIR."eshop/eshop.php?category=".$data['cid']."'\" style='cursor:pointer;'>\n";
	} else {
	echo "<td align='center' valign='top'><a href='".BASEDIR."eshop/eshop.php?category=".$data['cid']."'><img style='width:".$settings['eshop_catimg_w']."px; height:".$settings['eshop_catimg_h']."px;' src ='".BASEDIR."eshop/categoryimgs/".$data['image']."' alt='".$data['title']."' /></a><br />";
	}
	echo "<a href='".BASEDIR."eshop/eshop.php?category=".$data['cid']."'>".$data['title']."</a>";
	echo "</td>\n";
	$counter++; 
  }
	echo "</tr>\n</table>\n";
} else {

//add filters
buildfilters();

//Cat view start
$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid='".$_GET['category']."' AND active = '1' AND ".groupaccess('access')." ORDER BY ".$filter."");
if (dbrows($result)) {
$cdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid='".$_GET['category']."' AND ".groupaccess('access').""));
echo '<div style="margin-top:10px;"></div><div class="tbl-border" style="width:60%;float:left;padding-left: 7px;padding-top: 5px;padding-bottom: 5px;background-color:#f8f8f8;line-height:15px !important;height:15px !important;display:inline;">'.breadcrumb($_GET['category']).'</div>';
echo breadseo($_GET['category']);
echo "<div class='clear'></div>";
//check featured banners


//Check featured section first
	$resultfeat= dbquery("SELECT ter.* FROM ".DB_ESHOP." ter
	LEFT JOIN ".DB_ESHOP_FEATITEMS." titm ON ter.id=titm.featitem_item
	WHERE featitem_cid = '".($_REQUEST['category'] ? $_REQUEST['category'] : "0")."' ORDER BY featitem_order");
	$rows = dbrows($resultfeat);

if ($rows) {
	$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($resultfeat)) {
	if ($counter != 0 && ($counter % $settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
    echo "<td align='center' class='tbl'>\n";
	eshopitems();
	echo "</td>\n";
	$counter++;
}
	echo "</tr>\n</table>\n";

} 

$rows = dbrows($result);
$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid='".$_GET['category']."' AND active = '1' AND ".groupaccess('access')." ORDER BY ".$filter." LIMIT ".$_GET['rowstart'].",".$settings['eshop_nopp']."");
	$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($result)) {
	if ($counter != 0 && ($counter % $settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
	echo "<td align='center' class='tbl'>\n";
	eshopitems();
	echo "</td>\n";
	$counter++;
}
echo "</tr>\n</table>\n";
if ($rows > $settings['eshop_nopp']) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],$settings['eshop_nopp'],$rows,3,FUSION_SELF."?category=".$_GET['category']."&amp;".(isset($_COOKIE['Filter']) ? "FilterSelect=".$_COOKIE['Filter']."&amp;" : "" )."")."\n</div>\n";
echo "<div class='clear'></div>";
} else {
echo "<div class='clear'></div>";
echo "<br /><div class='admin-message'> ".$locale['ESHPP102']." </div>";
  }
 }
}

closetable();

//convert guest shopping to member when they visit eshop, this check is also made in the checkout.
if (iMEMBER) {
$usercartchk = dbarray(dbquery("SELECT puid FROM ".DB_ESHOP_CART." WHERE puid = '".$_SERVER['REMOTE_ADDR']."' LIMIT 0,1"));
if ($usercartchk['puid']) {
dbquery("UPDATE ".DB_ESHOP_CART." SET puid = '".$userdata['user_id']."' WHERE puid = '".$_SERVER['REMOTE_ADDR']."'");
 }
}

//Sanitize the cart from 1 month old orders.
dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE cadded < ".time()."-2592180");
*/
require_once THEMES."templates/footer.php";
?>