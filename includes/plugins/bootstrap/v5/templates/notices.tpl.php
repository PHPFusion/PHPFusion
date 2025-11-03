<?php

function render_notices(array $notices): string
{
    $html = '';

    foreach ($notices as $status => $notice) {
        // Determine dismiss class (Bootstrap 5)
        $dismissClass = ($status !== 'danger') ? 'alert-dismissible fade show' : '';

        // Wrap success in #close-message
        if ($status === 'success') {
            $html .= '<div id="close-message">';
        }

        // Build alert
        $html .= '<div class="alert alert-' . htmlspecialchars($status) . ' d-flex align-items-center ' . $dismissClass . '" role="alert">';
        $html .= '<ul class="alert-item block list-unstyled mb-0">';
        $html .= '<li>' . implode('</li><li>', (array)$notice) . '</li>';
        $html .= '</ul>';

        // Add dismiss button if applicable
        if ($dismissClass) {
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }

        $html .= '</div>';

        // Close success wrapper
        if ($status === 'success') {
            $html .= '</div>';
        }
    }

    return $html;
}
