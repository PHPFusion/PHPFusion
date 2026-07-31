<?php
/**
 * Render Bootstrap 5 modal in parts ("open", "footer", "close")
 * @param array $options
 * @return string
 */
function render_modal($options): string
{

    $locale = fusion_get_locale();
    $options += [
        'id' => '', //The modal ID (without "-Modal" suffix)
        "header_content" => '', //HTML header title (optional)
        "footer_content" => '', //HTML footer content (optional)
        "dismiss" => '', //Whether to show dismiss (close) button
        "modal" => '', //One of: "open", "footer", "close"
        /**
         * Settings:
         * - size: (1–5) integer, controls modal size
         * - centered: bool
         * - body_class: string
         * - sizeClass / modalClass overrides
         */
        "options" => $options,
        'class' => '', //Extra CSS class for .modal
    ];

    $id = $options['id'];
    $modal = $options['modal'];
    $header_content = $options['header_content'];
    $footer_content = $options['footer_content'];
    $options = $options['options'];
    $dismiss = !$options['static'];
    $class = $options['class'];


    $sizeClass = [
        1 => 'modal-fullscreen-sm-down',
        2 => '',
        3 => 'modal-fullscreen-lg-down',
        4 => 'modal-fullscreen-xl-down',
        5 => 'modal-fullscreen-xxl-down'
    ];

    $modalClass = [
        1 => 'modal-sm',
        2 => '',
        3 => 'modal-lg',
        4 => 'modal-xl'
    ];

    $size = $options['size'] ?? 2;
    $centeredClass = !empty($options['centered']) ? 'modal-dialog-centered' : '';
    $bodyClass = $options['body_class'] ?? '';

    $html = '';

    // OPEN MODAL
    if ($modal === 'open') {
        $html .= <<<HTML
<div class="modal fade {$class}" id="{$id}-Modal" tabindex="-1" aria-labelledby="{$id}-Modal" aria-modal="true" role="dialog" aria-hidden="false">
  <div class="modal-dialog {$modalClass[$size]} {$sizeClass[$size]} {$centeredClass}">
    <div class="modal-content">
HTML;

        // --- HEADER ---
        if ($header_content || $dismiss) {
            $html .= '<div class="modal-header">';
            if ($header_content) {
                $html .= '<h5 class="modal-title">' . $header_content . '</h5>';
            }
            if ($dismiss) {
                $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' . htmlspecialchars($locale['close']) . '"></button>';
            }
            $html .= '</div>';
        }

        // --- BODY OPEN ---
        $html .= '<div class="modal-body ' . $bodyClass . '">';
    } // FOOTER SECTION
    elseif ($modal === 'footer') {
        $html .= '</div><div class="modal-footer">';
        if ($dismiss) {
            $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . htmlspecialchars($locale['close']) . '</button>';
        }
        $html .= $footer_content;
    } // CLOSE MODAL
    elseif ($modal === 'close') {
        $html .= '</div></div></div></div>';
    }

    return $html;
}
