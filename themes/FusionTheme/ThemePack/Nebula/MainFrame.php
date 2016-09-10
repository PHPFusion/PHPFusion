<?php
namespace ThemePack\Nebula;

use ThemeFactory\Core;

class MainFrame extends Core {

    public function __construct($license = FALSE) {

        if ((defined('RIGHT') && RIGHT) || self::getParam('right_pre_content') || self::getParam('right_post_content')) {
            self::replaceParam('main_span', self::getParam('main_span') - self::getParam('right_span'));
        }

        if ($this->getParam('header') === TRUE) {

            $this->NebulaHeader();


            add_to_footer("<script src='".THEME."ThemeFactory/Lib/js/wow.min.js'></script>");
            //add_to_footer("<script src='".THEME."ThemeFactory/Lib/js/jquery.nicescroll.min.js'></script>");
            /*add_to_jquery("
            $('html').niceScroll({
                touchbehavior: false,
                cursorborder: 'none',
                cursorwidth: '8px',
                background: '#666',
                zindex: '999'
            });
            ");*/
        }

        $this->NebulaBody();
        if ($this->getParam('footer') === TRUE) {
            $this->NebulaFooter();
        }
    }

    private function NebulaHeader() {
        echo renderNotices(getNotices(array('all', FUSION_SELF)));
        $defaultBg = ($this->getParam('headerBg') === TRUE ? " class=\"headerBg\"" : "");
        $headerBg = ($this->getParam('headerBg_class') ? " class=\"".$this->getParam('headerBg_class')."\"" : $defaultBg);
        echo "<header ".$headerBg.">\n";
        echo "<div class='headerInner'>\n";
        echo "<div class='container'>\n";
		echo "<div id='headerBar' class='row hidden-print hidden-xs'>\n";
		echo "<div class='col-xs-12 col-sm-3 center-y'>\n";
		echo "<div class='logo'><a href='".BASEDIR.fusion_get_settings('opening_page')."' title='".fusion_get_settings('site_name')."'><img src='".BASEDIR.fusion_get_settings('sitebanner')."' alt=''/></a></div>\n";
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-9 center-y'>\n";
		echo "<div class='navbar-header navbar-right'>\n";
		echo "<ul class='navbar-nav'>\n";
		if (iMEMBER) :
			if (iADMIN) :
				echo "<li><a href='".ADMIN."index.php".fusion_get_aidlink()."'>".fusion_get_locale('global_123')."</a></li>\n";
            endif;
			echo "<li><a href='".BASEDIR."members.php"."'>".fusion_get_locale('UM082')."</a></li>\n";
			echo "<li><a href='".FUSION_SELF."?logout=yes"."'>".fusion_get_locale('logout')."</a></li>\n";
        else:
			echo "<li><a href='".BASEDIR."register.php"."'>".fusion_get_locale('register')."</a></li>\n";
            echo "<li><a href='".BASEDIR."login.php'>".fusion_get_locale('login')."</a></li>\n";
        endif;
        echo "</ul>\n";

        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";

        echo showsublinks('', '',
							array(
								'id' => 'NebulaMenu',
								'container' => TRUE,
								'class' => 'navbar-default',
								'links_per_page' => 8,
								'links_grouping' => TRUE,
								'language_switcher' => TRUE,
								'searchbar' => TRUE,
							)
						).
		// do affix
		add_to_jquery("
			$('#NebulaMenu').affix({
				offset: {
					top: 100,
					bottom: function () {
						return (this.bottom = $('.footer').outerHeight(true))
					}
				}
			})
		");
		if (AU_CENTER || ($this->getParam('header_content'))) :
            echo "<div class='nebulaHeader'>\n";
			echo ($this->getParam('header_content') ?: "");
            echo AU_CENTER;
            echo "</div>\n";
        endif;
		echo "</div>\n";
        echo "</header>\n";
    }

    private function NebulaBody() {
        if ($this->getParam('subheader_content') || $this->getParam('breadcrumbs') === TRUE) :
            echo "<div class='nebulaSubheader'>\n";
            echo "<div class='container'>\n";
			if ($this->getParam('subheader_content')) :
				echo "<h4 class='display-inline-block'>".$this->getParam('subheader_content')."</h4>\n";
			endif;
			if ($this->getParam('breadcrumbs') === TRUE) :
				echo render_breadcrumbs();
			endif;
            echo "</div>\n";
            echo "</div>\n";
        endif;
		if (U_CENTER) :
            echo "<section class='nebulaContentTop'>\n";
			echo "<div class='container'>\n";
			echo U_CENTER;
			echo "</div>\n";
            echo "</section>\n";
        endif;
        $side_span = 3;
        $main_span = 12;
        if (RIGHT) {
            if (RIGHT) {
                $main_span = $main_span - $side_span;
            }
        }
		if (LEFT) :
            echo "<div class='nebulaCanvas off'>\n";
			echo "<a class='canvas-toggle' href='#' data-target='nebulaCanvas'><i class='fa fa-bars fa-lg'></i></a>\n";
            echo LEFT;
            echo "</div>\n";
            add_to_jquery("
				$('.canvas-toggle').bind('click',function(e){
					e.preventDefault();
					var target = $(this).data('target');
					$('.'+target).toggleClass('off');
				});
			");
		endif;
        if ($this->getParam('container') == TRUE) :
			echo "<section class='nebulaBody'>\n";
            echo "<div class='container'>\n";
        endif;
		echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-$main_span'>\n";
		echo CONTENT;
		echo "</div>\n";
		if (RIGHT || $this->getParam('right_pre_content') || $this->getParam('right_post_content')) :
			echo "<div class='col-xs-12 col-sm-".$side_span."'>\n";
			echo $this->getParam('right_pre_content').RIGHT.$this->getParam('right_post_content');
			echo "</div>\n";
		endif;
		echo "</div>\n";
        if ($this->getParam('container') === TRUE) :
            echo "</div>\n";
			echo "</section>\n";
        endif;
    }

    private function NebulaFooter() {
        echo "<section class='nebulaFooter'>\n";
        echo "<div class='container'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-4'>\n";
        echo "<div class='about_theme' style='margin-bottom: 60px;'>\n";
		echo "<div class='nebulaLogo' style='margin-bottom:30px;'>\n";
		echo "<div class='pull-left'><i class='fa fa-cloud' style='font-size:50px; margin-right:10px;'></i></div>\n";
		echo "<div class='overflow-hide'><h1 class='m-0 text-white'>Nebula</h1>\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "The Nebula is a PHP-Fusion 9's first FusionTheme Theme Framework made offering many content elements, styles and features and to better understand and learn to build content using the PHP-Fusion 9, without coding.\n";
		echo "</div>\n";
		echo "<h4>About Us</h4>\n";
		echo "<p>".fusion_get_settings('description')."</p>\n";
		echo stripslashes(strip_tags(fusion_get_settings('footer')));
		echo "<p>".showcopyright()."</p>\n";
		if (fusion_get_settings('visitorcounter_enabled')) :
			echo "<p>".showcounter()."</p>\n";
		endif;
		echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-4'>\n";
		// News Module
		$this->get_Modules('Footer\\News');
		echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-4'>\n";
        $this->get_Modules('Footer\\Contact');
		echo "</div>\n";
		echo "<a href='#top' class='pull-right'><i class='fa fa-chevron-up fa-3x'></i></a>\n";
        echo "</div>\n";
        echo "</section>\n";
        echo "<section class='nebulaCopyright'>\n";
        echo "<div class='container'>\n";
        echo "<div class='col-xs-12 col-sm-4'><h4 class='text-white'>Nebula Theme by <a href='https://www.php-fusion.co.uk/profile.php?lookup=16331' target='_blank'>PHP-Fusion Inc</a></h4></div>\n";
		echo "<p>\n";
		if (fusion_get_settings('rendertime_enabled') == '1' || fusion_get_settings('rendertime_enabled') == '2') :
			echo showrendertime();
			echo showMemoryUsage();
		endif;
		$footer_errors = showFooterErrors();
		if (!empty($footer_errors)) :
			echo $footer_errors;
		endif;
		echo "</p>\n";
		echo "</div>\n";
		echo "</section>\n";
    }

}
