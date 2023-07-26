<?php

/**
 * @param $part
 * @return string
 */
function get_bootstrap($part)
{

    $version = 'v5';

    $component = [
        'showsublinks' => ['dir' => __DIR__ . '/' . $version . '/', 'file' => 'navbar.twig']
    ];

    return $component[$part] ?? '';

}

function get_template($component, $info)
{

    if ($path = get_bootstrap($component)) {
        return fusion_render($path['dir'], $path['file'], $info, TRUE);
    }

    return 'This template '.$component.' is not supported';
}

