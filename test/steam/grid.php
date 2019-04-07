<?php
/**
 * Test Sample Grid Boilerplate functionality
 *
 * {[container]}    boxed width
 * {[row]}          grid wrapper
 * {[col()]}        grid columns
 */
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';

// Start template instance
$tpl = \PHPFusion\Template::getInstance('test-grid');
// Set the template path to use
$tpl->set_template(__DIR__.'/html/grid-sample.html');
// No modifier needed.
// Template class finds the activated boiler and parse automatically.
$tpl->get_output();

require_once THEMES.'templates/footer.php';