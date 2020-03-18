<?php
require_once BOILERPLATES.'bootstrap3/index.php';

// Add the script to head tag
fusion_add_hook('fusion_boiler_header', 'bootstrap3');

// Set the boilerpalte template files to system
fusion_add_hook('fusion_boiler_paths', 'change_to_bs3_twig');
