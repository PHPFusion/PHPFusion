<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: BreaCrumbs.php
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
 * Get instances by keys:
 * $bcDefault = BreadCrumbs::getInstance();
 * $bcCustom = BreadCrumbs::getInstance('custom');
 *
 * Hide the home of $bcCustom->hideHome();
 *
 * Set the last breadcrumb clickable (it is rendered as a simple text by default)
 * $bcCustom->setLastClicklable();
 * You can override the default CSS classes of the wrapper HTML node
 * $bcCustom->setCssClasses('breadcrumb-custom');
 *
 * Be careful! If you want to add a new class, use this method
 * $bcCustom->addCssClasses('additional-class1 additional-class2');
 *
 * Get the items as associative arrays
 * $itemsDefault = $bcDefault->toArray();
 * $itemsCustom = $bcCustom->toArray();
 *
 * @package PHPFusion
 */
class BreadCrumbs {

    /**
     * @var static[]
     */
    private static $instances = [];

    /**
     * @var array
     */
    private $breadcrumbs = [];

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
        $this->addBreadCrumb([
            'link'  => BASEDIR.'index.php',
            'title' => fusion_get_locale('home'),
            'class' => 'home-link crumb'
        ]);
    }

    /**
     * Add a link to the breadcrumb
     *
     * @param array $link Keys: link, title
     *
     * @return static
     */
    public function addBreadCrumb(array $link) {
        $link += [
            'title' => '',
            'link'  => '',
            'class' => 'crumb'
        ];
        //$link['title'] = $link['title']; //trim(parse_textarea($link['title'], TRUE, TRUE, FALSE));
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
     *
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
     *
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
