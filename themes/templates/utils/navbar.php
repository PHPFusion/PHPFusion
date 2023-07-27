<?php


//            $locale = (array)self::getMenuParam('locale');
//            if (empty($id)) {
//                self::setLinks();
//                $res = "<div id='" . self::getMenuParam('id') . "' class='navbar " . self::getMenuParam('navbar_class') . "' role='navigation'>\n";
//                $res .= self::getMenuParam('container') ? "<div class='container'>\n" : "";
//                $res .= self::getMenuParam('container_fluid') ? "<div class='container-fluid'>\n" : "";
//                if (self::getMenuParam('show_header')) {
//                    $res .= !defined('BOOTSTRAP4') ? "<div class='navbar-header'>\n" : '';
//                    $res .= "<!--Menu Header Start-->\n";
//                    if (self::getMenuParam('responsive') && !defined('BOOTSTRAP4')) {
//                        $res .= "<button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#" . self::getMenuParam('id') . "_menu' aria-expanded='false' aria-controls='#" . self::getMenuParam('id') . "_menu'>\n";
//                        $res .= "<span class='sr-only'>" . $locale['global_017'] . "</span>\n";
//                        $res .= "<span class='icon-bar top-bar'></span>\n";
//                        $res .= "<span class='icon-bar middle-bar'></span>\n";
//                        $res .= "<span class='icon-bar bottom-bar'></span>\n";
//                        $res .= "</button>\n";
//                    }
//                    if (self::getMenuParam('show_banner') === TRUE) {
//                        $res .= "<a class='navbar-brand' href='" . BASEDIR . fusion_get_settings('opening_page') . "'>" . self::getMenuParam('banner') . "</a>\n";
//                    } else if (self::getMenuParam('show_header') === TRUE) {
//                        $res .= "<a class='navbar-brand visible-xs hidden-sm hidden-md hidden-lg' href='" . BASEDIR . fusion_get_settings('opening_page') . "'>" . fusion_get_settings("sitename") . "</a>\n";
//                    } else {
//                        $res .= self::getMenuParam('show_header');
//                    }
//
//                    if (self::getMenuParam('responsive') && defined('BOOTSTRAP4')) {
//                        $res .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#' . self::getMenuParam('id') . '_menu" aria-controls="' . self::getMenuParam('id') . '_menu" aria-expanded="false">';
//                        $res .= '<span class="navbar-toggler-icon"></span>';
//                        $res .= '</button>';
//                    }
//
//                    $res .= "<!--Menu Header End-->\n";
//                    $res .= !defined('BOOTSTRAP4') ? "</div>\n" : '';
//                }
//
//                $res .= self::getMenuParam('custom_header');
//
//                if (self::getMenuParam('responsive')) {
//                    $res .= "<div class='navbar-collapse collapse' id='" . self::getMenuParam('id') . "_menu'>\n";
//                }
//                $class = ((defined('BOOTSTRAP') && BOOTSTRAP == TRUE) ? " class='nav navbar-nav primary'" : " id='main-menu' class='primary sm sm-simple'");
//                if (self::getMenuParam('nav_class')) {
//                    $class = " class='" . self::getMenuParam('nav_class') . "'";
//                }
//
//                $res .= self::getMenuParam('html_pre_content');
//
//                $res .= "<ul$class>\n";
//                $res .= "<!--Menu Item Start-->\n";
//
//                // Show primary links
//                $res .= $this->showMenuLinks($id, self::getMenuParam('callback_data'));
//                $res .= "<!--Menu Item End-->\n";
//                $res .= "</ul>\n";
//
//                $res .= self::getMenuParam('html_content');
//
//                if (self::getMenuParam('language_switcher') == TRUE || self::getMenuParam('searchbar') == TRUE || !empty(self::getMenuParam('additional_data'))) {
//                    $class = ((defined('BOOTSTRAP') && BOOTSTRAP == TRUE) ? "class='nav navbar-nav secondary navbar-right'" : "id='second-menu' class='secondary sm sm-simple'");
//                    if (self::getMenuParam('additional_nav_class')) {
//                        $class = "class='" . self::getMenuParam('additional_nav_class') . "'";
//                    }
//
//                    $res .= "<ul $class>\n";
//
//                    $res .= $this->showMenuLinks($id, self::getMenuParam('additional_data'));
//
//                    if (self::getMenuParam('language_switcher') == TRUE) {
//                        if (count(fusion_get_enabled_languages()) > 1) {
//                            $language_switch = fusion_get_language_switch();
//                            $current_language = $language_switch[LANGUAGE];
//                            $language_opts = "<li class='nav-item dropdown' role='presentation'>";
//                            $language_opts .= "<a id='ddlangs" . $id . "' href='#' class='nav-link dropdown-toggle pointer' role='menuitem' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' title='" . translate_lang_names(LANGUAGE) . "'><img class='m-r-5' src='" . $current_language['language_icon_s'] . "' alt='" . translate_lang_names(LANGUAGE) . "'/> <span class='" . self::getMenuParam('caret_icon') . "'></span></a>";
//                            $language_opts .= "<ul class='dropdown-menu dropdown-menu-right' aria-labelledby='ddlangs" . $id . "' role='menu'>\n";
//                            if (!empty($language_switch)) {
//                                foreach ($language_switch as $langData) {
//                                    $language_opts .= "<li class='text-left'><a href='" . $langData['language_link'] . "'>";
//                                    $language_opts .= "<img alt='" . $langData['language_name'] . "' class='m-r-5' src='" . $langData['language_icon_s'] . "'/>";
//                                    $language_opts .= $langData['language_name'];
//                                    $language_opts .= "</a></li>\n";
//                                }
//                            }
//                            $language_opts .= "</ul>\n";
//                            $language_opts .= "</li>\n";
//                            $res .= $language_opts;
//                        }
//                    }
//
//                    if (self::getMenuParam('searchbar') == TRUE) {
//                        $searchbar = "<li class='nav-item dropdown' role='presentation'>";
//                        $searchbar .= "<a id='ddsearch" . $id . "' href='#' class='nav-link dropdown-toggle pointer' role='menuitem' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' title='" . fusion_get_locale('search') . "'><i class='" . self::getMenuParam('search_icon') . "'></i></a>";
//                        $searchbar .= "<ul aria-labelledby='ddsearch" . $id . "' class='dropdown-menu dropdown-menu-right p-l-15 p-r-15 p-t-15' role='menu' style='min-width: 300px;'>\n";
//                        $searchbar .= "<li class='text-left'>";
//                        $searchbar .= openform('searchform', 'post', FUSION_ROOT . BASEDIR . 'search.php?stype=all', ['class' => 'm-b-10', 'remote_url' => fusion_get_settings('site_path') . 'search.php']);
//                        $searchbar .= form_text('stext', '', '', ['placeholder' => $locale['search'], 'append_button' => TRUE, 'append_type' => "submit", "append_form_value" => $locale['search'], "append_value" => "<i class='" . self::getMenuParam('search_icon') . "'></i> " . $locale['search'], "append_button_name" => "search", "append_class" => self::getMenuParam('searchbar_btn_class'), 'class' => 'm-0',]);
//                        $searchbar .= closeform();
//                        $searchbar .= "</li>\n";
//                        $searchbar .= "</ul>";
//                        $res .= $searchbar;
//                    }
//                    $res .= "</ul>\n";
//                }
//
//                $res .= self::getMenuParam('html_post_content');
//
//                $res .= (self::getMenuParam('responsive')) ? "</div>\n" : "";
//
//                $res .= self::getMenuParam('container_fluid') ? "</div>\n" : "";
//
//                $res .= self::getMenuParam('container') ? "</div>\n" : "";
//
//                $res .= "</div>\n";
//            }