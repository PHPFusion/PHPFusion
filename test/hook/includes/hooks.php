<?php

/**
 * In this file, we don't even need maincore, if we don't need any core functions.
 * For simplicity, we won't be doing anything fancy.
 */
function orange() {
    return "<h1>This is an orange</h1>";
}

function apple() {
    return "<h1>This is an apple. </h1>";
}
function apple_link() {
    return "<h4>Click here to go to <a href='test.php?test_view'>extended view</a></h4>";
}

function strawberry() {
    return "<h1>This is a strawberry. Click here to go <a href='test.php'>back</a></h1>";
}

