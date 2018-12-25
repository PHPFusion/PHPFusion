<?php
/*---------------------------------------------------------+
| PHP-Fusion Content Management System                     |
| Copyright (C) 2002 - 2010 Nick Jones                     |
| http://www.php-fusion.co.uk/                             |
+----------------------------------------------------------+
| Shop Theme Copyright (C) 2013 Joakim Falk (Domi)         |
+----------------------------------------------------------+
|                               /T /I/ /  /   /            |
|                              / |/ | .-~/.-~/		   |
|                          T\ Y  I  |/  /  _/		   |
|         /T               | \I  |  I  Y.-~/ 	           |
|        I l   /I       T\ |  |  l  |  T  /		   |
|     T\ |  \ Y l  /T   | \I  l   \ `  l Y		   |
| __  | \l   \l  \I l __l  l   \   `  _. |		   |
| \ ~-l  `\   `\  \  \ ~\  \   `. .-~   < 		   |
|  \   ~-. "-.  `  \  ^._ ^. "-.  /  \   |                 |
|.--~-._  ~-  `  _  ~-_.-"-." ._ /._ ." ./		   |
| >--.  ~-.   ._  ~>-"    "\   7   7   ]                   | 
|^.___~"--._    ~-{  .-~ .  `\ Y . /    |		   |
| <__ ~"-.  ~       /_/   \   \I  Y   : |		   |
|   ^-.__           ~(_/   \   >._:   | l______		   |
|       ^--.,___.-~"  /_/   !  `-.~"--l_ /     ~"-.	   |
|              (_/ .  ~(   /'     "~"--,Y    -=O- _)	   |
|               (_/ .  \  :           / l       "   \	   |
|                \ /    `.    .     .^   \_.-~"~--.  )     |
|                 (_/ .   `  /     /       !       )/      |
|                  / / _.   '.   .':      /        '	   |
|                  ~(_/ .   /    _  `  .-<_		   |
|                    /_/ . ' .-~" `.  / \  \          ,´=. |
|                    ~( /   '  :   | X   "-.~-.______//    |
|                      "-,.    l   I/ \_    __{--->._(==.  |
|                       //(     \  <    ~"~"     //        |
|                      /' /\     \  \     ,~=.  ((         |
|                    .^. / /\     "  }__ //===-  `	   |
|                   / / ' '  "-.,__ {---(==-		   |
|                 .^ '       :  ;  ~"   ll       	   |
|                / .  .  . : | :!        \		   |
|               (_/  /   | | j-"          ~^		   |
|                 ~-<_(_.^-~"				   |
+----------------------------------------------------------+
| Shop Theme Copyright (C) 2013 Joakim Falk (Domi)         |
+----------------------------------------------------------+
| This program is released as free software under the 	   |
| Affero GPL license. You can redistribute it and/or 	   |
| modify it under the terms of this license which you      |
| can read by viewing the included agpl.txt or online      |
| at www.gnu.org/licenses/agpl.html. Removal of this       |
| copyright header is strictly prohibited without  	   |
| written permission from the original author(s).	   |
+---------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }
require_once INCLUDES."theme_functions_include.php";
require_once THEME."functions.php";

define("THEME_BULLET", "<span class='bullet'>&middot;</span>");
define("THEME_WIDTH", "1024px;");

function render_page($license = false) {
	global $settings, $main_style, $locale; 
		
	add_handler("theme_output");
	
	echo "<script type='text/javascript' src='https://connect.facebook.net/en_US/all.js#xfbml=1'></script>\n";
	echo "<script type='text/javascript' src='https://platform.twitter.com/widgets.js'></script>\n";
	add_to_head("<script type='text/javascript' src='https://apis.google.com/js/plusone.js'>{ lang: 'en-GB' } </script>");

	//Wrapper
echo "<div style='width:".THEME_WIDTH."; margin: 0px auto;'>";
echo "<div style='width:".THEME_WIDTH."; margin: 0px auto;' id='header' class='header'>";


//Site banner
echo "<div style='float:left;margin-top:2px;padding:10px;'>";
echo showbanners();
echo "</div>";

//Site description
if (HSDESCRIPTION) {
	//Right header section with sitename and site description
	echo "<div style='float:right;margin-top:2px;padding:10px;'>";
	echo "<br /><h2>".$settings['sitename']."</h2>
	<span>".$settings['description']."</span></div>";
	}
	

echo "<div class='clear'></div>";

echo "<script type='text/javascript'>
$(document).ready(function() {
	$(window).scroll(function(){
if ($(this).scrollTop() > 100) {
$('.scrollup').fadeIn();
} else {
$('.scrollup').fadeOut();
}
}); 
$('.scrollup').click(function(){
$('html, body').animate({ scrollTop: 0 }, 600);
return false;
 });
});
</script>";
echo "<div id='menu' class='fixed'><ul class='main fixed'>";
navigation();

echo "</ul></div></div><div class='clear'></div>";

	if ($main_style == "") {
		$colspan = "";
	} elseif ($main_style == "side-both") {
		$colspan = "colspan='3'";
	} else {
		$colspan = "colspan='2'";
	}

	//Content
	echo "<table cellpadding='0' cellspacing='0' width='".THEME_WIDTH."' class='$main_style'>\n";
	echo "AU_CENTER." ? "<tr><td class='main-bg' ".$colspan." valign='top'>".AU_CENTER."</td>\n</tr>\n<tr>\n" : "<tr>\n";
	if (LEFT) { echo "<td class='side-border-left' valign='top'>".LEFT."</td>"; }
	echo "<td class='main-bg' valign='top'>".U_CENTER.CONTENT.L_CENTER."</td>";
	if (RIGHT) { echo "<td class='side-border-right' valign='top'>".RIGHT."</td>"; }
	echo "BL_CENTER." ? "</tr>\n<tr><td class='main-bg' ".$colspan." valign='top'>".BL_CENTER."</td>\n</tr>\n<tr>\n" : "";
	echo "</tr>\n</table>\n";
	
//Footer
	echo "<table cellpadding='0' cellspacing='0' width='100%' class='main-footer'>\n<tr>\n";
	echo "<td align='left' width='20%' style='padding:10px;'>&nbsp;Theme by <a href='http://www.venue.nu' target='_blank' title='Venue'>Domi</a> 2013</td>\n";
	echo "<td align='center' width='60%' style='padding:10px;'>".showcopyright()."</td>\n";
	echo "<td align='right' width='20%' style='padding:10px;'>".showcounter()."&nbsp;</td>\n";
	echo "</tr>";
    if ($settings['rendertime_enabled'] == 1 || ($settings['rendertime_enabled'] == 2 && iADMIN)) {
	echo "<tr><td align='center' colspan='3' width='100%' style='padding:10px;'><center>".showrendertime()."</center></td></tr>\n";
	}	
	echo "</table></div>\n";

echo "<a href='#' class='scrollup'>Scroll</a>";
}


function render_news($subject, $news, $info) {
global $settings;
	opentable($subject);
	echo "<table width='100%' cellpadding='0' cellspacing='0'>\n<tr>\n";
	echo "<td class='main-body'><div style='float:left;width:100%;'>".$info['cat_image'].$news."</div></td>\n";
	echo "</tr>\n<tr><td style='padding-top:1px;'><div style='float:right;font-size:9px;'>".newsposter($info)."</div></td></tr>\n";
	echo "<tr><td align='center' class='news-footer'>\n";
	echo "<div style='float:left;margin-top:2px;'>";
	echo newsopts($info," &middot; " ).itemoptions("N",$info['news_id']);
	echo " &middot; </div>";
if (SHAREING) {
//Share Buttons
echo "<div style='float:left;margin-top:3px;'>";
//FB Like button
echo "<div style='float:left;margin-left:15px;'>";
echo "<div id='FbCont".$info['news_id']."'>
<script type='text/javascript'>
<!--//--><![CDATA[//><!--
var fb = document.createElement('fb:like'); 
fb.setAttribute('href','".$settings['siteurl']."news.php?readmore=".$info['news_id']."'); 
fb.setAttribute('layout','button_count');
fb.setAttribute('show_faces','true');
fb.setAttribute('width','1');
document.getElementById('FbCont".$info['news_id']."').appendChild(fb);
//--><!]]>
</script>
</div>";
echo "</div>";

//Twitter
echo "<div style='float:left;;margin-left:30px;'>";
echo "<script type='text/javascript'>
//<![CDATA[
(function() {
    document.write('<a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-count=\"horizontal\" data-url=\"".$settings['siteurl']."news.php?readmore=".$info['news_id']."\" data-text=\"".$info['news_subject']."\" data-via=\"eShop\">Tweet</a>');
    var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
    s.type = 'text/javascript';
    s.async = true;
    s1.parentNode.insertBefore(s, s1);
})();
//]]>
</script>";
echo "</div>";

//Google+
echo "<div style='float:left;;margin-left:1px;'>";
echo "<div class='g-plusone' id='gplusone".$info['news_id']."'></div> 
<script type='text/javascript'> 
var Validplus=document.getElementById('gplusone".$info['news_id']."'); 
Validplus.setAttribute('data-size','medium'); 
Validplus.setAttribute('data-count','true'); 
Validplus.setAttribute('data-href','".$settings['siteurl']."news.php?readmore=".$info['news_id']."'); 
</script>";
echo "</div>";
//End share buttons
echo "</div>";
}
	echo "</td>\n";
	echo "</tr>\n</table>\n";
closetable();
}

function render_article($subject, $article, $info) {
global $settings;
	
	opentable($subject);
	echo "<table width='100%' cellpadding='0' cellspacing='0'>\n<tr>\n";
	echo "<td class='main-body middle-border'>".($info['article_breaks'] == "y" ? nl2br($article) : $article)."</td>\n";
		echo "</tr>\n<tr><td style='padding-top:1px;'><div style='float:right;font-size:9px;'>".articleposter($info)."</div></td></tr>\n";
	echo "<tr><td align='center' class='news-footer'>\n";
	echo "<div style='float:left;margin-top:2px;'>";
	echo articleopts($info," &middot; ").itemoptions("A",$info['article_id']);
	echo " &middot; </div>";
		
if (SHAREING) {
//Share Buttons
echo "<div style='float:left;margin-top:3px;'>";
//FB Like button
echo "<div style='float:left;margin-left:15px;'>";
echo "<div id='FbCont".$info['article_id']."'>
<script type='text/javascript'>
<!--//--><![CDATA[//><!--
var fb = document.createElement('fb:like'); 
fb.setAttribute('href','".$settings['siteurl']."articles.php?article_id=".$info['article_id']."'); 
fb.setAttribute('layout','button_count');
fb.setAttribute('show_faces','true');
fb.setAttribute('width','1');
document.getElementById('FbCont".$info['article_id']."').appendChild(fb);
//--><!]]>
</script>
</div>";
echo "</div>";

//Twitter
echo "<div style='float:left;;margin-left:30px;'>";
echo "<script type='text/javascript'>
//<![CDATA[
(function() {
    document.write('<a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-count=\"horizontal\" data-url=\"".$settings['siteurl']."articles.php?article_id=".$info['article_id']."\" data-text=\"".$subject ."\" data-via=\"PHPFusion\">Tweet</a>');
    var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
    s.type = 'text/javascript';
    s.async = true;
    s1.parentNode.insertBefore(s, s1);
})();
//]]>
</script>";
echo "</div>";

//Google+
echo "<div style='float:left;margin-left:1px;'>";
echo "<div class='g-plusone' id='gplusone".$info['article_id']."'></div> 
<script type='text/javascript'> 
var Validplus=document.getElementById('gplusone".$info['article_id']."'); 
Validplus.setAttribute('data-size','medium'); 
Validplus.setAttribute('data-count','true'); 
Validplus.setAttribute('data-href','".$settings['siteurl']."articles.php?article_id=".$info['article_id']."'); 
</script>";
echo "</div>";
//End share buttons
echo "</div>";
echo "<div class='clear'></div>";
}
echo "</td>\n</tr>\n</table>\n";
closetable();
}
	
	


function opentable($title) {
echo "
  <div class='global-shadow-container'>
  <div class='global-shadow-background'></div>
  <div class='global-shadow-content'>
    <div class='global-box-container'>
      <div class='global-box-top heading-box'>".$title."</div>
      <div class='global-box-line'></div>
      <div class='global-box-bottom'>
      <div class='global-box-content'>
\n";
}

function closetable() {
echo "  </div>
      </div>
    </div>
  </div>
</div>\n";
}

function openside($title, $collapse = false, $state = "on") {

	global $panel_collapse; $panel_collapse = $collapse;

echo "
  <div class='global-shadow-container'>
  <div class='global-shadow-background'></div>
  <div class='global-shadow-content'>
    <div class='global-box-container'>
      <div class='global-box-top heading-box'>".$title."";

	  if ($collapse == true) {
		$boxname = str_replace(" ", "", $title);
		echo "<div style='float:right;padding:5px;'>".panelbutton($state, $boxname)."</div>\n";
	}
	  	
	echo "</div>
      <div class='global-box-line'></div>
      <div class='global-box-bottom'>
      <div class='global-box-content'>\n";
	  if ($collapse == true) { echo panelstate($state, $boxname); }
}

function closeside() {
	global $panel_collapse;
	if ($panel_collapse == true) { echo "</div>\n"; }	
	
echo "  </div>
      </div>
    </div>
  </div>
</div>\n";
}

?>