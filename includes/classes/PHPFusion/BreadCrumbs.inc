<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: BreaCrumbs.inc
| Author: Takács Ákos (Rimelek)
| Co-Author: Dan C. (JoiNNN)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

/**
 * Class BreadCrumbs
 *
 * <p><strong>Get instances by keys:</strong>
 * <code>
 * <?php
 * $bcDefault = BreadCrumbs::getInstance();
 * $bcCustom = BreadCrumbs::getInstance('custom');
 * </code></p>
 *
 * <p><strong>Hide the home of $bcCustom</strong>
 * <code>
 * <?php
 * $bcCustom->hideHome();
 * </code></p>
 *
 * <p><strong>Set the last breadcrumb clickable (it is rendered as a simple text by default)</strong>
 * <code>
 * <?php
 * $bcCustom->setLastClicklable();
 * </code></p>
 *
 * <p><strong>You can override the default CSS classes of the wrapper HTML node</strong>
 * <code>
 * $bcCustom->setCssClasses('breadcrumb-custom');
 * </code></p>
 *
 * <p><strong>Be careful! If you want to add a new class, use this method</strong>
 * <code>
 * <?php
 * $bcCustom->addCssClasses('additional-class1 additional-class2');
 * </code></p>
 *
 * <p><strong>Get the items as associative arrays</strong>
 * <code>
 * <?php
 * $itemsDefault = $bcDefault->toArray();
 * $itemsCustom = $bcCustom->toArray();
 * </code></p>
 *
 * @package PHPFusion
 */
class BreadCrumbs {

    /**
     * @var static[]
     */
    private static $instances = array();

    /**
     * @var array
     */
    private $breadcrumbs = array();

    /**
     * Whether to add the 'Home' link
     *
     * @var bool
     */
    private $showHome = TRUE;

    /**
     * Whether to make last breadcrumb a link
     *
     * @var bool
     */
    private $isLastClickable = FALSE;

    /**
     * The class wrapping the breacrumbs
     *
     * @var string
     */
    private $cssClasses = 'breadcrumb';

    public function __construct() {
        $this->addBreadCrumb(array(
            'link'  => BASEDIR.'index.php',
            'title' => fusion_get_locale('home'),
            'class' => 'home-link crumb'
                             ));
    }

    /**
     * Add a link to the breadcrumb
     *
     * @param array $link Keys: link, title
     *
     * @return static
     */
    public function addBreadCrumb(array $link) {
        $link += array(
            'title' => '',
            'link' => '',
            'class' => 'crumb'
        );
        $link['title'] = $link['title']; //trim(parse_textarea($link['title'], TRUE, TRUE, FALSE));
        if (!empty($link['title']) && !empty($link['link'])) {
            $this->breadcrumbs[] = $link;
        }
        return $this;
    }

    /**
     * Get an instance by key
     *
     * @param string $key
     *
     * @return static
     */
    public static function getInstance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }

    /**
     * @param string $classes
     * @return static
     */
    public function addCssClasses($classes) {
        $this->setCssClasses($this->getCssClasses().' '.$classes);

        return $this;
    }

    /**
     * @return string
     */
    public function getCssClasses() {
        return $this->cssClasses;
    }

    /**
     * @param string $classes
     * @return static
     */
    public function setCssClasses($classes) {
        $this->cssClasses = $classes;

        return $this;
    }

    /**
     * Hide the link to home page
     *
     * @return static
     */
    public function hideHome() {
        return $this->showHome(FALSE);
    }

    /**
     * Show or hide the link to home page
     *
     * @param bool $state
     *
     * @return static
     */
    public function showHome($state = TRUE) {
        $this->showHome = (bool)$state;

        return $this;
    }

    /**
     * @param bool $clickable
     *
     * @return static
     */
    public function setLastClickable($clickable = TRUE) {
        $this->isLastClickable = (bool)$clickable;

        return $this;
    }

    /**
     * Get breadcrumbs
     *
     * @return array Keys of elements: title, link
     */
    public function toArray() {
        $breadcrumbs = $this->isHomeShown() ? $this->breadcrumbs : array_slice($this->breadcrumbs, 1);

        $count = count($breadcrumbs);
        if (!$this->isLastClickable() && $count) {
            $breadcrumbs[$count - 1]['link'] = '';
        }

        return $breadcrumbs;
    }

    /**
     * @return bool
     */
    public function isHomeShown() {
        return $this->showHome;
    }

    /**
     * @return bool
     */
    public function isLastClickable() {
        return $this->isLastClickable;
    }
}
