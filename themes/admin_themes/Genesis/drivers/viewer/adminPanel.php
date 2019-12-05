<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: adminPanel.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Genesis\Viewer;

use Genesis\Model\resource;
use PHPFusion\Admins;

class adminPanel extends resource {
    
    private static $breadcrumb_shown = FALSE;
    
    public function __construct() {
        
        parent::__construct();
        $this->do_interface_js();
        
        $collapsed = isset( $_COOKIE['acpState'] ) && $_COOKIE['acpState'] == 0 ? ' collapsed' : '';
        ?>
		<section id="devlpr" class="adminPanel">
			<div class="left_menu collapsed">
				<div>
					<div class="menu-icon">
					</div>
				</div>
				<div class="menu">
                    <?php $this->left_nav(); ?>
				</div>
			</div>
			<div class="app_menu<?php echo $collapsed; ?>">
				<div class="app_list">
                    <?php $this->app_nav() ?>
				</div>
			</div>

			<div id="main_content" class="content<?php echo $collapsed; ?>">
				<div class="sub_menu">
                    <?php $this->display_admin_pages() ?>
				</div>
				<!--				<header class="header affix mm-collapsed" data-spy="affix" data-offset-top="10">-->
				<header class="header mm-collapsed">
                    <?php $this->adminHeader() ?>
				</header>
				<div class="content mm-collapsed">
                    <?php
                    $notices = getNotices();
                    if ( !empty( $notices ) ) :
                        ?>
	                    <div class="admin-notices">
                            <?php echo renderNotices( $notices ); ?>
	                    </div>
                    <?php
                    endif;
                    echo CONTENT; ?>
					<div class="copyright-wrapper">
						<div class="copyright">
                            <?php echo showcopyright( '', TRUE ) ?>
						</div>
					</div>
				</div>
				<span class="main_content_overlay"></span>
			</div>
		</section>
		<footer>
			<ul>
				<li>Genesis Admin Theme by PHPFusion CMS.</li>
                <?php
                $errors = showFooterErrors();
                if ( $errors ) {
                    echo "<li>".$errors."</li>\n";
                }
                ?>
                <?php
                if ( fusion_get_settings( "rendertime_enabled" ) ) : ?>
	                <li><?php echo showrendertime() ?></li>
	                <li><?php echo showMemoryUsage() ?></li>
	                <li><?php echo self::$locale['copyright'].showdate( "%Y", time() )." - ".fusion_get_settings( "sitename" ) ?></li>
                <?php endif; ?>
				<li class="pull-right">PHP-Fusion CMS v.<?php echo fusion_get_settings( 'version' ) ?></li>
			</ul>
		</footer>
        <?php
    }
    
    /**
     * Javascript for Interface
     */
    private function do_interface_js() {
        add_to_jquery( "
        $('#search_app').bind('keyup', function(e) {
            var data = {
                'appString' : $(this).val(),
                'mode' : 'html',
                'url' : '".$_SERVER['REQUEST_URI']."',
            };
            var sendData = $.param(data);
            $.ajax({
                url: '".THEMES."admin_themes/Genesis/acp_request.php".$this->get_aidlink()."',
                dataType: 'html',
                method : 'get',
                type: 'json',
                data: sendData,
                success: function(e) {
                    $('.app_page_list').hide();
                    $('#main_content').addClass('open');
                    $('ul#app_search_result').html(e).show();
                },
                error : function(e) {
                    console.log('fail');
                }
            });
        });
        
        
        " );
        
    }
    
    /**
     * Primary Sectional Menu
     */
    private function left_nav() {
    
        $aidlink = fusion_get_aidlink();
    
        $sections = Admins::getInstance()->getAdminSections();
    
        $this->admin_section_icons[] = "<i class='fa fa-chevron-circle-left'></i>\n";
    
        $pages = Admins::getInstance()->getAdminPages();
        //print_P($pages, 1);
        //print_P($sections, 1);
        $section_count = count( $sections );
        ?>
	    <ul>
            <?php
            $pagenum = get( 'pagenum', FILTER_VALIDATE_INT );
            foreach ( $sections as $i => $section_name ) :
                $active = ( $pagenum == $i ) || ( !$pagenum && $this->_isActive() == $i ) ? TRUE : FALSE;
                $is_menu_action = $i + 1 == $section_count ? TRUE : FALSE;
                $has_page = isset( $pages[ $i ] ) ? TRUE : FALSE;
                $href_src = "";
                if ( $has_page ) {
                    $href_src = "data-load=\"$i\"";
                } else if ( !$is_menu_action ) {
                    $href_src = "href=\"".ADMIN.$aidlink."&amp;pagenum=$i\"";
                }
                ?>
	            <li <?php echo( $active ? " class=\"active\"" : "" ) ?>>
		            <a class="pointer admin-menu-icon<?php echo $is_menu_action ? " menu-action " : "" ?>"
		               title="<?php echo $section_name ?>" <?php echo $href_src ?>>
                        <?php echo Admins::getInstance()->getAdminSectionIcons( $i ) ?>
		            </a>
	            </li>
            <?php
            endforeach;
            ?>
	    </ul>
        <?php
        add_to_footer( "<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>" );
        add_to_footer( "<script type='text/javascript' src='".THEMES."admin_themes/Genesis/drivers/js/leftMenu.js'></script>" );
    }
    
    /**
     * Applications List Menu
     * todo: find corresponding description of admin pages in model - maybe page section
     */
    private function app_nav() {
    
        $aidlink = parent::get_aidlink();
    
        $locale = parent::get_locale();
    
        $sections = Admins::getInstance()->getAdminSections();
    
        $pages = Admins::getInstance()->getAdminPages();
    
        $is_current_page = parent::_currentPage();
    
        echo "<ul id=\"app_search_result\"  class=\"app_page_list\" style=\"display:none;\"></ul>\n";
    
        foreach ( $sections as $i => $section_name ) :
        
            if ( !empty( $pages[ $i ] ) && is_array( $pages[ $i ] ) ) :
                
                echo "<ul id=\"ap-$i\" class=\"app_page_list\" style=\"display:none;\">\n";
            
                echo "<li><h4>$section_name</h4></li>\n";
            
                foreach ( $pages[ $i ] as $key => $data ) :
                
                    if ( checkrights( $data['admin_rights'] ) ) :
                        
                        $secondary_active = $data['admin_link'] == $is_current_page ? "class='active'" : '';
                    
                        $title = $data['admin_title'];
                    
                        $link = ADMIN.$data['admin_link'].$aidlink;
                    
                        if ( $data['admin_page'] !== 5 ) {
                            $title = isset( $locale[ $data['admin_rights'] ] ) ? $locale[ $data['admin_rights'] ] : $title;
                        }
                    
                        ?>
					    <li <?php echo $secondary_active ?>>
						    <a class="apps-lists" href="<?php echo $link ?>">
							    <div class="app_icon">
								    <img class="img-responsive" alt="<?php echo $title ?>"
								         src="<?php echo get_image( "ac_".$data['admin_rights'] ); ?>"/>
							    </div>
							    <div class="apps">
								    <h4><?php echo $title ?></h4>
							    </div>
						    </a>
					    </li>
                    <?php
                    endif;
            
                endforeach;
            
                echo "</ul>\n";
        
            endif;
    
        endforeach;
    }
    
    private function adminHeader() {
        
        $locale = self::get_locale();
        $aidlink = self::get_aidlink();
        $admin = Admins::getInstance();
        $sections = $admin->getAdminSections();
        $admin_pages = $admin->getAdminPages();
        $active_section = $admin->_isActive();
        $page_title = self::get_title();
        ?>
		<div class="head-title">
            <?php
            $header_text = "<h4>".$page_title['icon'].$page_title['title']."</h4>";
            if ( isset( $sections[ $active_section ] ) && !empty( $admin_pages[ $active_section ] ) ) { // the current active section is present.
                if ( isset( $admin_pages[ $this->active_rights ] ) ) {
                    $sections = $admin_pages[ $this->active_rights ]; // this is just the root of subpage. dropdown array is not present
                    if ( !empty( $sections ) ) {
                        $tab = $this->__tab( $admin_pages, $sections );
                    }
                }
            }
            echo( !empty( $tab ) ? $tab : $header_text );
            ?>
		</div>
		<nav role="navigation">
			<div class="search">
                <?php echo form_text( "search_app", "", "", [
                    'prepend'       => TRUE,
                    'prepend_value' => 'Search',
                    'append'        => TRUE,
                    'append_value'  => '<i class="fal fa-search"></i>',
                    'class'         => 'm-b-0', "placeholder" => $locale['spotlight_search'], 'width' => '100%' ] ); ?>
			</div>
			<ul class="nav">
				<li class="hidden-xs hidden-sm">
					<a title="<?php echo $locale['settings'] ?>" href="<?php echo ADMIN."settings_main.php".$aidlink ?>">
						<i class="fal fa-cog fa-lg"></i>
					</a>
				</li>
                <?php
                echo self::message_notification();
                echo self::admin_language_switcher();
                ?>
				<li>
					<a title="<?php echo fusion_get_settings( 'sitename' ) ?>" href="<?php echo BASEDIR."index.php" ?>">
						<i class="fal fa-home fa-lg"></i>
					</a>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown">
                        <?php echo display_avatar( fusion_get_userdata(), '50px', '', FALSE, 'img-circle' ) ?>
					</a>
					<ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <?php
                        $u_drop_links = resource::get_udrop();
                        if ( !empty( $u_drop_links ) ) {
                            foreach ( $u_drop_links as $link => $title ) {
                                if ( $link == "---" ) {
                                    echo "<li class=\"divider\"></li>\n";
                                } else {
                                    echo "<li><a href='$link'>$title</a></li>\n";
                                }
                            }
                        }
                        ?>
					</ul>
				</li>
			</ul>
		</nav>
        <?php
    }
    
    private function message_notification() {
        $locale = self::get_locale();
        $userdata = fusion_get_userdata();
        
        $messages = [];
        
        $msg_count_sql = "message_to = '".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'";
        
        $msg_search_sql = "
                        SELECT message_id, message_subject,
                        message_from 'sender_id', u.user_name 'sender_name', u.user_avatar 'sender_avatar', u.user_status 'sender_status',
                        message_datestamp
                        FROM ".DB_MESSAGES."
                        INNER JOIN ".DB_USERS." u ON u.user_id=message_from
                        WHERE message_to = '".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'
                        GROUP BY message_id
                        ";
        
        if ( dbcount( "(message_id)", DB_MESSAGES, $msg_count_sql ) ) {
            
            $msg_result = dbquery( $msg_search_sql );
            
            if ( dbrows( $msg_result ) > 0 ) {
                
                while ( $data = dbarray( $msg_result ) ) {
                    
                    $messages[] = [
                        "link"      => BASEDIR."messages.php?folder=inbox&amp;msg_read=".$data['message_id'],
                        "title"     => $data['message_subject'],
                        "sender"    => [
                            "user_id"     => $data['sender_id'],
                            "user_name"   => $data['sender_name'],
                            "user_avatar" => $data['sender_avatar'],
                            "user_status" => $data['sender_status'],
                        ],
                        "datestamp" => timer( $data['message_datestamp'] ),
                    ];
                    
                }
                
            }
            
        }
        
        $html = '<li class="dropdown hidden-xs hidden-sm">';
        if ( !empty( $messages ) ) {
            $html .= '
            <a class="dropdown-toggle" data-toggle="dropdown" title="'.$locale['message'].'" href="'.BASEDIR.'messages.php">
                <i class="fal fa-envelope fa-lg"></i>
                <span class="badge message_alert">'.count( $messages ).'</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">';
            foreach ( $messages as $message_data ) {
                $html .= '
                <li>
                    <a href="'.$message_data['link'].'">
                        <div class="pull-left">
                        '.display_avatar( $message_data['sender'], "30px", "", FALSE, "img-rounded m-t-5" ).'
                        </div>
                        <div class="overflow-hide">
                        <strong>'.$message_data['title'].'</strong>
                        <br/>
                        <small>'.$message_data['datestamp'].'</small>
                        </div>
                    </a>
                </li>
                ';
            }
            $html .= '</ul>';
        } else {
            $html .= '<a title="'.$locale['message'].'" href="'.BASEDIR.'messages.php">
                      <i class="fal fa-envelope fa-lg"></i>
            </a>
            ';
        }
        $html .= "</li>";
        
        return $html;
    }
    
    private static $current_url = [];
    
    /**
     * Given a url, check if currently have active.
     *
     * @param $url
     *
     * @return bool
     */
    private function checkCurrentActive( $url ) {
        
        if ( $url !== '#' or $url !== "---" ) {
            
            if ( empty( self::$current_url ) ) {
                
                self::$current_url = ( (array)parse_url( htmlspecialchars_decode( server( 'REQUEST_URI' ) ) ) ) + [ 'path' => '', 'query' => '' ];
                
                self::$current_url['path'] = str_replace( INFUSIONS, '/infusions/', self::$current_url['path'] );
                
                if ( self::$current_url['query'] ) {
                    parse_str( self::$current_url['query'], self::$current_url['current_query'] );
                }
            }
            
            $current_url = ( (array)parse_url( htmlspecialchars_decode( $url ) ) ) + [
                    'path'  => '',
                    'query' => ''
                ];
            $current_url['path'] = strtr( $current_url['path'], [
                INFUSIONS => '/infusions/',
                '..'      => ''
            ] );
            
            if ( self::$current_url['path'] == $current_url['path'] ) {
                
                if ( !empty( $current_url['query'] ) ) {
                    parse_str( $current_url['query'], $queries );
                }
                
                if ( isset( self::$current_url['current_query'] ) && isset( $queries ) ) {
                    if ( count( self::$current_url['current_query'] ) === count( $queries ) ) {
                        if ( empty( array_diff( self::$current_url['current_query'], $queries ) ) ) {
                            return TRUE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }
    
    /**
     * Checks if there is a child with current active.
     *
     * @param $admin_pages
     * @param $rights
     *
     * @return bool
     */
    private function checkParentActive( $admin_pages, $rights ) {
        if ( isset( $admin_pages[ $rights ] ) ) {
            foreach ( $admin_pages[ $rights ] as $c_rights => $c_arr ) {
                if ( $c_arr['admin_active'] ) {
                    return TRUE;
                }
                if ( $this->checkCurrentActive( $c_arr['admin_link'] ) ) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    /**
     * Recursive function to build the dropdown collapse
     *
     * @param     $admin_pages
     * @param     $array
     * @param int $i
     *
     * @return string
     */
    private function __li( $admin_pages, $array, $i = 0 ) {
        $html = &$html;
        
        foreach ( $array as $rights => $arr ) {
            $class = '';
            $caret = '';
            $toggle_class = '';
            $in = '';
            $data_attr = '';
            // child active
            $match_c = $this->checkCurrentActive( $arr['admin_link'] );
            //print_p($match_c);
            // parent active
            $match_p = $this->checkParentActive( $admin_pages, $rights );
            //print_p($match_p);
            
            if ( $match_c || $match_p || $arr['admin_active'] ) {
                $class .= " class='active'";
                if ( $match_p )
                    $in = " in";
            }
            if ( isset( $admin_pages[ $rights ] ) ) {
                $toggle_class = " data-toggle='collapse' data-parent='#sub_menu' href='#c-app-$rights'";
                $caret = " <b class='".( $i > 1 ? "fas fa-caret-right" : "fas fa-caret-down" )." pull-right m-t-5 m-l-10'></b>";
            }
            if ( !empty( $arr['data'] ) ) {
                foreach ( $arr['data'] as $key => $val ) {
                    $data_attr .= $key.'="'.$val.'" ';
                }
            }
            
            $html .= "<li".$class.">\n";
            $html .= "<a".$toggle_class." href='".$arr['admin_link']."' $data_attr>".$arr['admin_image'].$arr['admin_title'].$caret."</a>\n";
            if ( isset( $admin_pages[ $rights ] ) ) {
                // now we need to check how many keys this guy has.
                $html = &$html;
                $html .= "<!--dropdown--->\n";
                $html .= "<ul id='c-app-$rights' class='collapse".$in."'>".$this->__li( $admin_pages, $admin_pages[ $rights ], $i )."</ul>\n";
                $html .= "<!--//dropdown--->\n";
            }
            $html .= "</li>\n";
        }
        
        return (string)$html;
    }
    
    private function __tab( $admin_pages, $array, $i = 0 ) {
        $html = &$html;
        foreach ( $array as $rights => $arr ) {
            $match_c = $this->checkCurrentActive( $arr['admin_link'] );
            $match_p = $this->checkParentActive( $admin_pages, $rights );
            if ( $match_c || $match_p || $arr['admin_active'] ) {
                //$html .= "<li".$class.">\n";
                //$html .= "<a".$toggle_class." href='".$arr['admin_link']."' $data_attr>".$arr['admin_image'].$arr['admin_title'].$caret."</a>\n";
                if ( isset( $admin_pages[ $rights ] ) ) {
                    // now we need to check how many keys this guy has.
                    $html = &$html;
                    $html .= "<ul id='c-app-$rights' class='nav nav-tabs'>".$this->__li( $admin_pages, $admin_pages[ $rights ], $i )."</ul>\n";
                }
                //$html .= "</li>\n";
            }
        }
        return (string)$html;
    }
    
    private function display_admin_icon( $rights ) {
        $image = get_image( $rights );
        if ( !empty( $image ) ) {
            if ( preg_check( "/\<(i|span|b) class=(.*?)\><\/(i|span|b)>/im", $image ) ) {
                return $image;
            }
            return "<img class='icon-xs display-inline m-r-5' src='$image'/>";
        }
        return '';
    }
    
    private $active_rights = 0;
    
    private function display_admin_pages() {
        $aidlink = fusion_get_aidlink();
        $admin = Admins::getInstance();
        $sections = $admin->getAdminSections();
        //print_P($sections);
        $admin_pages = $admin->getAdminPages();
        //print_p($admin_pages, 1);
        $active_section = $admin->_isActive();
        //print_p($active_section);
        $current_page = $admin->_currentPage();
        
        echo "<div class='submenu-header'>";
        echo "<h4><i class='fal fa-dice-d20 m-r-5'></i> GENESIS <sup>9</sup></h4>";
        echo "</div>";
        
        echo "<nav role='navigation'>";
        echo "<ul role='presentation'>\n";
        if ( isset( $sections[ $active_section ] ) && !empty( $admin_pages[ $active_section ] ) ) { // the current active section is present.
            
            foreach ( $admin_pages[ $active_section ] as $key => $admin_data ) {
                if ( $current_page == $admin_data['admin_link'] ) {
                    $this->active_rights = $admin_data['admin_rights']; // is correct
                }
            }
            //print_P($active_rights);
            
            if ( isset( $admin_pages[ $this->active_rights ] ) ) {
                // get current section
                $sections = $admin_pages[ $this->active_rights ]; // this is just the root of subpage. dropdown array is not present.
                if ( !empty( $sections ) ) {
                    echo $this->__li( $admin_pages, $sections );
                }
            } else {
                if ( !empty( $sections ) ) {
                    $i = 0;
                    foreach ( $sections as $section_name ) {
                        echo "<li><a href='".ADMIN."index.php".$aidlink."&amp;pagenum=".$i."'>".$section_name."</a></li>\n";
                        $i++;
                    }
                }
            }
        } else {
            if ( !empty( $sections ) ) {
                $i = 0;
                foreach ( $sections as $section_name ) {
                    echo "<li><a href='".ADMIN."index.php".$aidlink."&amp;pagenum=".$i."'>".$section_name."</a></li>\n";
                    $i++;
                }
            }
        }
        echo "</ul>\n";
        echo "</nav>\n";
    }
    
    public static function opentable( $title, $class = NULL ) {
        if ( self::$breadcrumb_shown == FALSE ) :
            echo render_breadcrumbs();
            self::$breadcrumb_shown = TRUE;
        endif;
        if ( !empty( $title ) ) :
            $cur_title = "<h3>$title</h3>";
            if ( str_ireplace( [ '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>', '<div>', '<p>' ], '', $title ) != $title ) {
                $cur_title = $title;
            }
            ?>
			<header><?php echo $cur_title ?></header>
        <?php endif;
        
        echo '<div class="app_table '.$class.'">';
    }
    
    public static function closetable( $title = FALSE, $class = NULL ) {
        echo '</div>';
        
        if ( !empty( $title ) ) {
            echo "<footer".( $class ? " class=\"$class\"" : '' )."><h3>$title</h3></footer>";
        }
    }
    
    public static function openside( $title = FALSE, $class = NULL ) {
        ?>
		<aside class="app_aside">
        <?php
        if ( !empty( $title ) ) :
            // checks if there are any tags in $title, if no, append h5 tag
            $cur_title = "<h5>$title</h5>";
            if ( str_ireplace( [ '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>', '<div>', '<p>' ], '', $title ) != $title ) {
                $cur_title = $title;
            }
            ?>
			<div class="app_aside_head clearfix <?php echo " ".$class ?>"><?php echo $cur_title ?></div>
        <?php endif; ?>
		<div class="app_aside_body clearfix">
        <?php
    }
    
    public static function closeside() {
        ?>
		</div></aside>
        <?php
    }
}
