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

    private function searchbar()
    {
        // make a dynamic loading searchbar with dropdown
        return form_text('searchtext', '', '', ['placeholder' => 'Search ' . fusion_get_settings('sitename'), 'prepend' => TRUE, 'prepend_value' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.26686 7.53301C3.26686 5.25023 5.11742 3.39967 7.4002 3.39967C9.68297 3.39967 11.5335 5.25023 11.5335 7.53301C11.5335 9.81579 9.68297 11.6663 7.4002 11.6663C5.11742 11.6663 3.26686 9.81579 3.26686 7.53301ZM7.4002 2.33301C4.52831 2.33301 2.2002 4.66113 2.2002 7.53301C2.2002 10.4049 4.52831 12.733 7.4002 12.733C8.64404 12.733 9.78588 12.2963 10.6808 11.5678L13.4398 14.3269L14.1941 13.5726L11.435 10.8136C12.1635 9.9187 12.6002 8.77685 12.6002 7.53301C12.6002 4.66113 10.2721 2.33301 7.4002 2.33301Z" class="fill"></path></svg>']);
    }

    private function uip()
    {
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
        // $theme_settings['github_url'] = 'xxx';
        // $theme_settings['facebook_url'] = 'xxx';
        // $theme_settings['x_url'] = 'xxx';
        // $theme_settings['instagram_url'] = 'xxx';
        // $theme_settings['discord_url'] = 'xxxx';

        $settings = fusion_get_settings();

        $locale = fusion_get_locale('', POLARIS_LOCALE);

        $logo = '<img src="' . IMAGES . 'assets/phpfusion-logo-d.png' . '" alt="' . $settings['sitename'] . '" class="img-responsive"/>';

        $this->setSiteLinksOptions(
            show_header: '<a class="navbar-brand" href="' . BASEDIR . $settings['opening_page'] . '">' . $logo . '</a>',
            html_post_content: $this->searchbar() . $this->uip()
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
            <div class="container">
                <?php
                $layout_users = ['USER1', 'USER2', 'USER3', 'USER4'];
                $validLayoutUsers = [];
                foreach ($layout_users as $user) {
                    if (is_constant_filled($user)) {
                        $validLayoutUsers[$user] = constant($user);
                    }
                }
                if (!empty($validLayoutUsers)) :
                ?>
                    <div class="<?php echo $this->getLayoutClass() ?>">
                        <div class="row">
                            <?php foreach ($validLayoutUsers as $name => $value) : ?>
                                <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3"><?php echo $value; ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <div class="site-footer-upper">
                    <ul class="site-footer-links">
                        <li><a href="">For developers</a></li>
                        <li><a href="">For designers</a></li>
                        <li><a href="">Hire talent</a></li>
                        <li><a href="">PHPFusion Communities</a></li>
                    </ul>
                    <div class="site-footer-social">

                        <?php if (!empty($theme_settings['github_url'])) : ?>
                            <a href="<?php echo $theme_settings['github_url'] ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16">
                                    <path d="M8 0c4.42 0 8 3.58 8 8a8.013 8.013 0 0 1-5.45 7.59c-.4.08-.55-.17-.55-.38 0-.27.01-1.13.01-2.2 0-.75-.25-1.23-.54-1.48 1.78-.2 3.65-.88 3.65-3.95 0-.88-.31-1.59-.82-2.15.08-.2.36-1.02-.08-2.12 0 0-.67-.22-2.2.82-.64-.18-1.32-.27-2-.27-.68 0-1.36.09-2 .27-1.53-1.03-2.2-.82-2.2-.82-.44 1.1-.16 1.92-.08 2.12-.51.56-.82 1.28-.82 2.15 0 3.06 1.86 3.75 3.64 3.95-.23.2-.44.55-.51 1.07-.46.21-1.61.55-2.33-.66-.15-.24-.6-.83-1.23-.82-.67.01-.27.38.01.53.34.19.73.9.82 1.13.16.45.68 1.31 2.69.94 0 .67.01 1.3.01 1.49 0 .21-.15.45-.55.38A7.995 7.995 0 0 1 0 8c0-4.42 3.58-8 8-8Z"></path>
                                </svg>
                            </a>
                        <?php endif ?>
                        <!-- Facebook -->
                        <?php if (!empty($theme_settings['facebook_url'])) : ?>
                            <a href="<?php echo $theme_settings['facebook_url'] ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" aria-labelledby="a2uowak97i88vmtimon1314i5rge9j6" role="img" viewBox="0 0 24 24" class="icon ">
                                    <path d="M22.676 0H1.324C.593 0 0 .593 0 1.324v21.352C0 23.408.593 24 1.324 24h11.494v-9.294H9.689v-3.621h3.129V8.41c0-3.099 1.894-4.785 4.659-4.785 1.325 0 2.464.097 2.796.141v3.24h-1.921c-1.5 0-1.792.721-1.792 1.771v2.311h3.584l-.465 3.63H16.56V24h6.115c.733 0 1.325-.592 1.325-1.324V1.324C24 .593 23.408 0 22.676 0"></path>
                                </svg>
                            </a>
                        <?php endif ?>
                        <?php if (!empty($theme_settings['x_url'])) : ?>
                            <a href="<?php echo $theme_settings['x_url'] ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
                                    <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
                                </svg>
                            </a>
                        <?php endif ?>
                        <!-- Instagram -->
                        <?php if (!empty($theme_settings['instagram_url'])) : ?>
                            <a href="<?php echo $theme_settings['instagram_url'] ?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414" role="img" class="icon ">
                                    <path d="M8 0C5.827 0 5.555.01 4.702.048 3.85.088 3.27.222 2.76.42c-.526.204-.973.478-1.417.923-.445.444-.72.89-.923 1.417-.198.51-.333 1.09-.372 1.942C.008 5.555 0 5.827 0 8s.01 2.445.048 3.298c.04.852.174 1.433.372 1.942.204.526.478.973.923 1.417.444.445.89.72 1.417.923.51.198 1.09.333 1.942.372.853.04 1.125.048 3.298.048s2.445-.01 3.298-.048c.852-.04 1.433-.174 1.942-.372.526-.204.973-.478 1.417-.923.445-.444.72-.89.923-1.417.198-.51.333-1.09.372-1.942.04-.853.048-1.125.048-3.298s-.01-2.445-.048-3.298c-.04-.852-.174-1.433-.372-1.942-.204-.526-.478-.973-.923-1.417-.444-.445-.89-.72-1.417-.923-.51-.198-1.09-.333-1.942-.372C10.445.008 10.173 0 8 0zm0 1.44c2.136 0 2.39.01 3.233.048.78.036 1.203.166 1.485.276.374.145.64.318.92.598.28.28.453.546.598.92.11.282.24.705.276 1.485.038.844.047 1.097.047 3.233s-.01 2.39-.048 3.233c-.036.78-.166 1.203-.276 1.485-.145.374-.318.64-.598.92-.28.28-.546.453-.92.598-.282.11-.705.24-1.485.276-.844.038-1.097.047-3.233.047s-2.39-.01-3.233-.048c-.78-.036-1.203-.166-1.485-.276-.374-.145-.64-.318-.92-.598-.28-.28-.453-.546-.598-.92-.11-.282-.24-.705-.276-1.485C1.45 10.39 1.44 10.136 1.44 8s.01-2.39.048-3.233c.036-.78.166-1.203.276-1.485.145-.374.318-.64.598-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276C5.61 1.45 5.864 1.44 8 1.44zm0 2.452c-2.27 0-4.108 1.84-4.108 4.108 0 2.27 1.84 4.108 4.108 4.108 2.27 0 4.108-1.84 4.108-4.108 0-2.27-1.84-4.108-4.108-4.108zm0 6.775c-1.473 0-2.667-1.194-2.667-2.667 0-1.473 1.194-2.667 2.667-2.667 1.473 0 2.667 1.194 2.667 2.667 0 1.473-1.194 2.667-2.667 2.667zm5.23-6.937c0 .53-.43.96-.96.96s-.96-.43-.96-.96.43-.96.96-.96.96.43.96.96z"></path>
                                </svg>
                            </a>
                        <?php endif ?>
                        <!-- Discord -->
                        <?php if (!empty($theme_settings['discord_url'])) : ?>
                            <a href="<?php echo $theme_settings['discord_url'] ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-discord" viewBox="0 0 16 16">
                                    <path d="M13.545 2.907a13.2 13.2 0 0 0-3.257-1.011.05.05 0 0 0-.052.025c-.141.25-.297.577-.406.833a12.2 12.2 0 0 0-3.658 0 8 8 0 0 0-.412-.833.05.05 0 0 0-.052-.025c-1.125.194-2.22.534-3.257 1.011a.04.04 0 0 0-.021.018C.356 6.024-.213 9.047.066 12.032q.003.022.021.037a13.3 13.3 0 0 0 3.995 2.02.05.05 0 0 0 .056-.019q.463-.63.818-1.329a.05.05 0 0 0-.01-.059l-.018-.011a9 9 0 0 1-1.248-.595.05.05 0 0 1-.02-.066l.015-.019q.127-.095.248-.195a.05.05 0 0 1 .051-.007c2.619 1.196 5.454 1.196 8.041 0a.05.05 0 0 1 .053.007q.121.1.248.195a.05.05 0 0 1-.004.085 8 8 0 0 1-1.249.594.05.05 0 0 0-.03.03.05.05 0 0 0 .003.041c.24.465.515.909.817 1.329a.05.05 0 0 0 .056.019 13.2 13.2 0 0 0 4.001-2.02.05.05 0 0 0 .021-.037c.334-3.451-.559-6.449-2.366-9.106a.03.03 0 0 0-.02-.019m-8.198 7.307c-.789 0-1.438-.724-1.438-1.612s.637-1.613 1.438-1.613c.807 0 1.45.73 1.438 1.613 0 .888-.637 1.612-1.438 1.612m5.316 0c-.788 0-1.438-.724-1.438-1.612s.637-1.613 1.438-1.613c.807 0 1.451.73 1.438 1.613 0 .888-.631 1.612-1.438 1.612" />
                                </svg>
                            </a>
                            <?php endif ?>
                    </div>
                </div>
                <div class="site-footer-lower">
                    <ul class="site-footer-links">
                        <li><a href="">Terms</a></li>
                        <li><a href="">Privacy</a></li>
                        <li><a href="">Cookies</a></li>
                    </ul>
                    <span>Polaris &copy; <?php echo date('Y') . ' ' . $locale['POLARIS_101'] ?> <a href="https://phpfusion.com" target="_blank">PHPFusion</a></span>
                    <ul class="site-footer-links">
                        <li><a href=""><?php showcopyright('', TRUE) ?></a></li>
                        <li><?php echo showcounter(); ?></li>
                        <?php if ($settings['footer']) : ?>
                            <?php echo parse_text($settings['footer'], ['parse_smileys' => FALSE, 'add_line_breaks' => FALSE]); ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <?php if (iADMIN) : ?>
                <div class="text-center"><?php echo showfootererrors() ?></div>
                <div class="m-t-20">
                    <div class="text-center" style="margin-top: 30px;"><?php showprivacypolicy() ?></div>
                    <?php if ($settings['rendertime_enabled'] == 1 || $settings['rendertime_enabled'] == 2): ?>
                        <div class="text-center">
                            <?php
                            echo showrendertime();
                            echo showmemoryusage();
                            ?>
                        </div>
                    <?php endif ?>
                </div>
                </div>
            <?php endif ?>
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
