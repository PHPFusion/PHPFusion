<?php

namespace PHPFusion\Page;

/**
 * Interface WidgetInterface
 * This is the standard for the Widget Object
 */
interface WidgetAdminInterface {

    /**
     * Returns the exclude key of a clean_request of your widget when save or update redirects
     * @return array
     */
    public function exclude_return();

    /**
     * Validate all $_POST of your form and returns a page content serialized string from your form inputs
     * The post button value that reads this function is 'widget'
     * Return is saved into 'page_content' column of DB_PAGES_CONTENT table (i.e. $self::$colData)
     * @return string - serialized array
     */
    public function validate_input();

    /**
     * Validate all $_POST of your form and returns a page settings serialized string from your form inputs
     * The post button value that reads this function is 'settings'
     * Return is saved into 'page_options' column of DB_PAGES_CONTENT table (i.e. $self::$colData)
     * @return string - serialized array
     */
    public function validate_settings();

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
     * @return mixed
     */
    public function display_form_button();

}