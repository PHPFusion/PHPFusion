<?php

namespace ThemeFactory\Lib\Modules\Menu;

class Sitemap {

    public static function display_menu() {

        $html = "
        <ul class='nav navbar-nav primary'>
            <li class='dropdown'>
                <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='lnr lnr-earth fa fa-lg m-r-5'></i> Browse <span class='fa fa-angle-down'></span></a>
                <ul class='dropdown-menu mega-menu'>
                    <li>
                        <div class='container'>
                        <h4>Browse PHP-Fusion</h4>
                        <div class='row'>
                            <div class='col-xs-12 col-sm-3'>
                            <div class='title'>Account</div>
                                <ul class='block'>
                                <li><a href='".INFUSIONS."social/activity.php'>Latest Feeds</a></li>
                                <li><a href='".BASEDIR."profile.php?lookup=".fusion_get_userdata('user_id')."'>My Profile</a></li>
                                <li><a href='".BASEDIR."wallet/wallet.php'>My Wallet</a></li>
                                <li><a href='".BASEDIR."edit_profile.php'>Edit Profile</a></li>                                
                            </ul>
                            </div>
                            <div class='col-xs-12 col-sm-3'>
                            <div class='title'>Releases</div>
                            <ul class='block'>
                                <li><a href='".INFUSIONS."news/news.php'>Press Releases</a></li>
                                <li><a href='".INFUSIONS."blog/blog.php'>Our Official Blog</a></li>
                                <li><a href=''>Example URL#</a></li>
                                <li><a href=''>Example URL#</a></li>
                            </ul>
                            </div>
                            <div class='col-xs-12 col-sm-3'>
                            <ul class='block'>
                                <div class='title'>Products & Addons</div>
                                <li><a href=''>Hosting Packages</a></li>
                                <li><a href=''>Domains</a></li>
                                <li><a href=''>Addon Store</a></li>
                                <li><a href='".BASEDIR."search.php'>Search</a></li>
                            </ul>                            
                            </div>
                            <div class='col-xs-12 col-sm-3'>
                            <ul class='block'>
                                <div class='title'>Support & Documentation</div>
                                <li><a href='".INFUSIONS."forum/index.php'>Community Support Forum</a></li>
                                <li><a href='".INFUSIONS."wiki/index.php'>Support Documentation</a></li>
                                <li><a href=''>Example URL#</a></li>
                                <li><a href='".BASEDIR."search.php'>Search</a></li>
                            </ul>                            
                            </div>   
                        </div>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
        ";

        return $html;
    }

}