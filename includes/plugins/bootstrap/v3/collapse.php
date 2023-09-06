<?php
/**
 * Collapse Template
 * @return string
 */
function collapse( $options ) {

    if ($options['callback'] == 'opencollapse') {

        return '<div class="panel-group" id="' . $options['id'] . '-accordion" role="tablist" aria-multiselectable="true">';

    } elseif ($options['callback'] == 'closecollapse') {

        return '</div>';

    } elseif ($options['callback'] == 'closecollapsebody') {

        return '</div></div></div>';

    } elseif ($options['callback'] == 'opencollapsebody') {

        $html = '<div class="panel panel-default ' . $options['class'] . '">';
        $html .= '<div class="panel-heading" role="tab" id="' .  $options['id'] . '-collapse-heading">';
        $html .= '<h4 class="panel-title">';
        $html .= '<a role="button" data-toggle="collapse" data-parent="#' . $options['group_id'] . '-accordion" href="#' . $options['id'] . '-collapse" aria-expanded="true" aria-controls="' . $options['id'] . '-collapse">' . $options['title'] . '</a>';
        $html .= '</h4>';
        $html .= '</div>';
        $html .= '<div id="' . $options['id'] . '-collapse" class="panel-collapse collapse' . ($options['active'] ? ' in' : '') . '" role="tabpanel" aria-labelledby="' . $options['id'] . '-collapse-heading">';
        $html .= '<div class="panel-body">';

        return $html;
    }

    return 'PHP rendering error';
}
