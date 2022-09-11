<?php
/** @noinspection ALL */
require_once __DIR__."/../maincore.php";
require_once FUSION_HEADER;

echo fusion_render(__DIR__.'/twig_template/', "test.twig", [
    'print_this' => lorem_ipsum(300)
], TRUE);

require_once FUSION_FOOTER;
