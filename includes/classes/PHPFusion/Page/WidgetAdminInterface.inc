<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Page/WidgetAdminInterface.inc
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Page;

/**
 * Interface WidgetInterface
 * This is the standard for the Widget Object
 */
interface WidgetAdminInterface {

    public static function widgetInstance();

    /**
     * Returns the exclude key of a clean_request of your widget when save or update redirects
     *
     * @return array
     */
    public function exclude_return();

    /**
     * Validate all $_POST of your form and returns a page content serialized string from your form inputs
     * The post button value that reads this function is 'widget'
     * Return is saved into 'page_content' column of DB_PAGES_CONTENT table (i.e. $self::$colData)
     *
     * @return string - serialized array
     */
    public function validate_input();

    /**
     * Validate all $_POST of your form and returns a page settings serialized string from your form inputs
     * The post button value that reads this function is 'settings'
     * Return is saved into 'page_options' column of DB_PAGES_CONTENT table (i.e. $self::$colData)
     *
     * @return string - serialized array
     */
    public function validate_settings();

    /**
     * The execution of codes extra when delete column button is pressed
     * Use this to delete and prune tables if needed, if not leave body blank
     *
     * @return mixed
     */
    public function validate_delete();

    /**
     * This function displays your widget admin interface
     * Echo your designed HTML of the administration here.
     */
    public function display_form_input();

    /**
     * This function displays your widget save buttons
     * There are 2 acceptable button name - save_widget and save_and_close_widget
     * 'save_widget' will retains the same window after save/update execution
     * 'save_and_close_widget' will close the window after save/update execution
     * We strongly recommend that you make both available to your user
     *
     * There are 2 acceptable button values - widget and settings
     * 'widget' will pair with validate_input() function to return against 'page_content' column
     * 'settings' will pair with validate_settings() function to return against 'page_options' column
     *
     * @return mixed
     */
    public function display_form_button();

}
