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
	// change buynow color.
	if ($data['status'] == "1") {
		echo "<div class='m-t-20'>\n";
		if ($data['buynow'] == "1") {
			echo "<a class='btn m-r-10 ".fusion_get_settings('eshop_buynow_color')."' href='".BASEDIR."eshop/buynow.php?id=".$data['id']."'>".$locale['ESHP020']."</a>";
		}
		if ($data['cart_on'] == "1") {
			echo "<a class='btn m-r-10 ".fusion_get_settings('eshop_addtocart_color')."' href='javascript:;' onclick='javascript:cartaction(".$data['id']."); return false;'>".$locale['ESHP021']."</a>";
		}
		echo "</div>\n";
	}



	echo "</div>\n</div>\n";

	// Do the descriptions and etc.
	// how many tabs?
	$tab_title['title'][] = $locale['ESHP023'];
	$tab_title['id'][] = 'pdesc';
	$tab_title['icon'][] = '';
	$tab_title['title'][] = $locale['ESHP022'];
	$tab_title['id'][] = 'pspecs';
	$tab_title['icon'][] = '';
	$tab_title['title'][] = 'What else?';
	$tab_title['id'][] = 'pspecs';
	$tab_title['icon'][] = '';

	$tab_active = tab_active($tab_title, 0);
	echo opentab($tab_title, $tab_active, 'product-tabs');
	echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
	echo "<div class='m-t-10'>\n";
	if ($data['demo']) {
		$urlprefix = !strstr($data['demo'], "http://") ? "http://" : "";
		echo "<span class='display-block'>".$locale['ESHP013']." <a href='".$urlprefix.$data['demo']."' target='_blank'>".$locale['ESHP015']."</a></span>";
	}
	echo stripslashes($data['description']);
	if ($data['anything']) {
		echo "<h4>".stripslashes($data['anything1n'])."</h4>";
		echo stripslashes($data['anything']);
	}
	if ($data['anything2']) {
		echo "<h4>".stripslashes($data['anything2n'])."</h4>";
		echo stripslashes($data['anything2']);
	}
	if ($data['anything3']) {
		echo "<h4>".stripslashes($data['anything3n'])."</h4>";
		echo stripslashes($data['anything3']);
	}
	echo "</div>\n";
	echo closetabbody();
	echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
	echo closetabbody();
	echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
	echo closetabbody();
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

			// dync stuff
			if ($data['status'] == "1") {
				if ($data['dync']) {
					$dync = str_replace('"', '', $data['dync']);
					echo "<tr><td class='tbl' align='center'><fieldset><legend align='center' style='margin-left:2px !important; width:85% !important;'>&nbsp; ".$data['dynf']." &nbsp;</legend><div style='padding:3px;'>";
					echo "<select name='dyncs_".$data['id']."' id='dyncs_".$data['id']."' class='textbox' style='width:135px !important;'>
    				 <option value=''>".$locale['ESHP016']."</option>";
					$dync = explode(".", substr($dync, 1));
					for ($i = 0; $i < count($dync); $i++) {
						echo "<option value='$dync[$i]'>$dync[$i]</option>";
					}
					echo "</select>";
					echo "<input name='dynct' id='dynct_".$data['id']."' value='".$data['dynf']."' type='hidden' />";
					echo "</div></fieldset></td></tr>";
				} else {
					echo "<input name='dyncs_".$data['id']."' id='dyncs_".$data['id']."' value='0' type='hidden' />";
					echo "<input name='dynct_".$data['id']."' id='dynct_".$data['id']."' value='0' type='hidden' />";
				}
				if ($data['icolor']) {
					$colors = str_replace('"', '', $data['icolor']);
					echo "<tr><td class='tbl' align='center'><fieldset><legend align='center' style='margin-left:2px !important; width:85% !important;'>&nbsp;".$locale['ESHP017']."&nbsp;</legend><div style='padding:3px;'>";
					echo "<select name='color_".$data['id']."' id='color_".$data['id']."' class='textbox' style='width:135px !important;'>
     				<option value=''>".$locale['ESHP018']."</option>";
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
					echo "</div></fieldset></td></tr>";
				} else {
					echo "<input name='color_".$data['id']."' id='color_".$data['id']."' type='hidden' value='0' />";
				}
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

			echo "</table>";
			echo "</div>";
			echo "<div style='clear:both;'></div>";

			//End item lookup
		} else {
			echo "<div class='admin-message'>".$locale['ESHP024']."</div>";
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