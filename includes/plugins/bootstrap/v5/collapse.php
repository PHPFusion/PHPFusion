<?php

/**
 * Collapse Template
 *
 * @return string
 */
function collapse( $options ) {

    if ($options['callback'] == 'opencollapse') {

        return '<div class="accordion' . whitespace( $options['class'] ) . '" id="' . $options['id'] . 'Accordion" role="tablist" aria-multiselectable="true">';

    } elseif ($options['callback'] == 'closecollapse') {

        return '</div>';

    } elseif ($options['callback'] == 'closecollapsebody') {

        return '</div></div></div>';

    } elseif ($options['callback'] == 'opencollapsebody') {

        return
            '<div class="accordion-item' . whitespace( $options['class'] ) . '">'
            . '<h4 class="accordion-header">'
            . '<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#' . $options['id'] . 'Collapse"  aria-expanded="true" aria-controls="' . $options['id'] . 'Collapse"><div class="accordion-title">' . $options['title'] . '</div></button>'
            . '</h4>'
            . '<div id="' . $options['id'] . 'Collapse" class="accordion-collapse collapse' . ($options['active'] ? ' show' : '') . '" ' . ($options['group_id'] ? 'data-bs-parent="#' . $options['group_id'] . '-Accordion"' : '') . '>'
            . '<div class="accordion-body">';

    }

    return 'PHP rendering error';
}
