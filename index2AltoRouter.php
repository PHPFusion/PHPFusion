<?php
/**
 * Test file for AltoRouter
 * Research & Development of PHP-Fusion Galaxy Core 9
 * Compatible: 9.00 and above
 * Test date - 15/11/15
 */
require_once "maincore.php";
$settings = fusion_get_settings();

/**
 * Have .htacess route to this page like this:
 * RewriteEngine on
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteRule . index2AltoRouter.php [L]
 *
 * Alto router does only routing page.
 * Routing is grabbing browser's URL and find the page to include from this file.
 * Routing serves file from a single file (i.e require, include, hence path is to root.)
 */

$router = new \PHPFusion\AltoRouter();
$router->setBasePath($settings['site_path']);

// Map 2 possibilities of fetches that route to home.php
/**
 * Parameter 1 - PULL|PUT|GET|POST
 * Parameter 2 - URL to route
 * Parameter 3 - File to load
 * Parameter 4 - Give the mapping an Id so the rules do not collide or merged
 */
$router->map('GET', 'home', 'home.php', 'first'); // this is the rule to fetch.
$router->map('/GET', 'index.php', 'home.php', 'second'); // this is the rule to fetch.

$match = $router->match();
if ($match) {
    require $match['target'];
} else {
    header("HTTP/1.0 404 Not Found");
    require 'error.php';
}
// This show you the rules
//$rr = $router->getRoutes();
// print_p($rr);
// WE STILL NEED A PERMALINK TO TRANSLATE RENDERED LINKS TO SEF URL LINK.