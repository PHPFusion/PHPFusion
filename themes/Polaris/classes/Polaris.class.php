<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Polaris/classes/Polaris.class.php
| Author: Meangczac (Chan), Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Polaris Theme
 *
 * @package Polaris
 */
class Polaris extends PolarisThemeFactory
{
    private static $instance;

    /**
     * Get instance of Polaris theme
     * @return Polaris
     */
    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    private function searchbar() {
        // make a dynamic loading searchbar with dropdown
        return form_text('searchtext', '', '', ['placeholder'=>'Search '.fusion_get_settings('sitename'), 'prepend'=>TRUE, 'prepend_value'=> '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.26686 7.53301C3.26686 5.25023 5.11742 3.39967 7.4002 3.39967C9.68297 3.39967 11.5335 5.25023 11.5335 7.53301C11.5335 9.81579 9.68297 11.6663 7.4002 11.6663C5.11742 11.6663 3.26686 9.81579 3.26686 7.53301ZM7.4002 2.33301C4.52831 2.33301 2.2002 4.66113 2.2002 7.53301C2.2002 10.4049 4.52831 12.733 7.4002 12.733C8.64404 12.733 9.78588 12.2963 10.6808 11.5678L13.4398 14.3269L14.1941 13.5726L11.435 10.8136C12.1635 9.9187 12.6002 8.77685 12.6002 7.53301C12.6002 4.66113 10.2721 2.33301 7.4002 2.33301Z" class="fill"></path></svg>']);
    }

    private function uip() {
        return '<ul class="uip nav navbar-nav"><li class="dropdown">
        <a href="#" class="uip dropdown-toggle" data-toggle="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.25" transform="rotate(90 12 12)" stroke-width="1.5" class="stroke"></circle> <path d="M14.75 10.5356C14.75 12.0544 13.5188 13.2856 12 13.2856C10.4812 13.2856 9.25 12.0544 9.25 10.5356C9.25 9.01686 10.4812 7.78564 12 7.78564C13.5188 7.78564 14.75 9.01686 14.75 10.5356Z" stroke-width="1.5" class="stroke"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M18.0627 18.6515C17.1211 16.2223 14.7617 14.5 12 14.5C9.23819 14.5 6.8787 16.2224 5.93713 18.6517C5.53341 18.2835 5.16332 17.879 4.83203 17.4435C6.14128 14.8098 8.85928 13 12 13C15.1406 13 17.8585 14.8097 19.1678 17.4432C18.8365 17.8788 18.4665 18.2833 18.0627 18.6515Z" class="fill"></path></svg></a>
        <ul class="dropdown-menu">
        <li>this is the dropdown menu</li>
        </ul></li></ul>';
    }
    /**
     * Display theme
     * @return void
     */
    public function renderPage()
    {
        $theme_settings = get_theme_settings('Polaris');
        
        $settings = fusion_get_settings();

        $locale = fusion_get_locale('', POLARIS_LOCALE);

        $this->setSiteLinksOptions(
            show_header : '<a class="navbar-brand" href="' . BASEDIR . $settings['opening_page'] . '"><img src="' . IMAGES .'assets/phpfusion_logo_d.png' . '" alt="' . $settings['sitename'] . '" class="img-responsive"/></a>',
            html_post_content: $this->searchbar().$this->uip()
        );

        echo PHPFusion\SiteLinks::setSubLinks($this->getSiteLinksOptions())->showSubLinks();

        ?>
        <main class="main-content">
            <div class="<?php echo $this->getLayoutClass() ?>">
                <?php echo defined('AU_CENTER') ? AU_CENTER : ''; ?>
                <?php echo showbanners(1) ?>
                <div class="row">
                    <?php if (defined('LEFT') && LEFT): ?>
                        <div class="<?php echo $this->getLeftLayoutClass() ?>">
                            <?php echo LEFT ?>
                        </div>
                    <?php endif; ?>
                    <div class="<?php echo $this->getContentLayoutClass() ?>">
                        <?php
                        echo rendernotices(getnotices(['all', FUSION_SELF]));
                        echo defined('U_CENTER') ? U_CENTER : '';
                        echo CONTENT;
                        echo defined('L_CENTER') ? L_CENTER : '';
                        echo showbanners(2);
                        ?>

                    </div>
                    <?php if (defined('RIGHT') && RIGHT): ?>
                        <div class="<?php echo $this->getRightLayoutClass() ?>">
                            <?php echo RIGHT ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php echo defined('BL_CENTER') ? BL_CENTER : ''; ?>
            </div>
        </main>
        <footer class="main-footer">
            <div class="<?php echo $this->getLayoutClass() ?>">
                <div class="row">
                    <?php if (defined('USER1') && USER1): ?>
                        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3"><?php echo USER1 ?></div>
                    <?php endif; ?>
                    <?php if (defined('USER2') && USER2): ?>
                        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3"><?php echo USER2 ?></div>
                    <?php endif; ?>
                    <?php if ((defined('USER3') && USER3)): ?>
                        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3"><?php echo USER3 ?></div>
                    <?php endif; ?>
                    <?php if ((defined('USER4') && USER4)): ?>
                        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3"><?php echo USER4 ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-center"><?php echo showfootererrors() ?></div>
            <div class="m-t-20">
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 visible-xs">
                        <div class="text-center"><img src="<?php echo BASEDIR . $settings['sitebanner'] ?>" alt="<?php echo $settings['sitename'] ?>" class="img-responsive" style="display: inline;" /></div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-left">
                        <?php echo parse_text($settings['footer'], ['parse_smileys' => FALSE, 'add_line_breaks' => FALSE]); ?>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 hidden-xs">
                        <div class="text-center"><img src="<?php echo BASEDIR . $settings['sitebanner'] ?>" alt="<?php echo $settings['sitename'] ?>" class="img-responsive" style="display: inline;" /></div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">

                        <div class="social-links text-right">
                            <?php if (!empty($theme_settings['github_url'])) : ?>
                                <a href="<?php echo $theme_settings['github_url'] ?>" target="_blank"><i class="fa fa-github"></i></a>
                            <?php endif ?>
                            <?php if (!empty($theme_settings['facebook_url'])) : ?>
                                <a href="<?php echo $theme_settings['facebook_url'] ?>" target="_blank"><i class="fa fa-facebook"></i></a>
                            <?php endif ?>
                            <?php if (!empty($theme_settings['twitter_url'])) : ?>
                                <a href="<?php echo $theme_settings['twitter_url'] ?>" target="_blank"><i class="fa fa-twitter"></i></a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="text-center" style="margin-top: 30px;"><?php echo showcopyright('', TRUE) . showprivacypolicy() ?></div>
                <?php if ($settings['rendertime_enabled'] == 1 || $settings['rendertime_enabled'] == 2): ?>
                    <div class="text-center">
                        <?php
                        echo showrendertime();
                        echo showmemoryusage();
                        ?>
                    </div>
                <?php endif ?>
                <div class="text-center strong">Polaris theme &copy; <?php echo date('Y') . ' ' . $locale['POLARIS_101'] ?> <a href="https://phpfusion.com" target="_blank">PHPFusion</a></div>
                <div class="text-center"><small class="text-muted"><?php echo showcounter(); ?></div>
            </div>
            </div>
        </footer>
    <?php
    }

    /**
     * Opentable component     
     * @param string $title Title of the table component
     * @param string $class Class name for the table component
     */
    public static function opentable(string $title = '', string $class = '')
    {
    ?>
        <div class="polaris-box">
            <?php if ($title) : ?>
                <div class="title"><?php echo $title ?></div>
            <?php endif ?>
            <div <?php echo (!empty($class) ? ' class="' . $class . '"' : '') ?>>
                <!--tablecontent-->
                <!--</div>-->
            <?php
        }

        /**
         * Openside component
         * @param string $title Title of the side component
         * @param string $class Class name for the side component
         * @return void
         */

        public static function openside(string $title = '', string $class = '')
        {
            ?>
                <div class="polaris-card<?php $class ? ' ' . $class : '' ?>">
            <?php
            echo $title ? '<div class="title">' . $title . '</div>' : '';
        }

        /**
         * Closeside component
         * @return void
         */
        public function closeComponents($length)
        {
            echo str_repeat('</div>', $length);
        }
    }
