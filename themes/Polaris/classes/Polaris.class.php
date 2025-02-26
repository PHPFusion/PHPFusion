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

    /**
     * Display theme
     * @return void
     */
    public function renderPage()
    {
        $theme_settings = get_theme_settings('Polaris');
        $settings = fusion_get_settings();
        $locale = fusion_get_locale('', POLARIS_LOCALE);

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
