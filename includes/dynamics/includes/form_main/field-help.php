<?php

/** Return the project-wide, dependency-free tip icon. */
function showtip($message): string {
return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-help-octagon"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12.802 2.165l5.575 2.389c.48 .206 .863 .589 1.07 1.07l2.388 5.574c.22 .512 .22 1.092 0 1.604l-2.389 5.575c-.206 .48 -.589 .863 -1.07 1.07l-5.574 2.388c-.512 .22 -1.092 .22 -1.604 0l-5.575 -2.389a2.036 2.036 0 0 1 -1.07 -1.07l-2.388 -5.574a2.036 2.036 0 0 1 0 -1.604l2.389 -5.575c.206 -.48 .589 -.863 1.07 -1.07l5.574 -2.388a2.036 2.036 0 0 1 1.604 0" /><path d="M12 16v.01" /><path d="M12 13a2 2 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .483" /></svg>';
}

/** Shared, progressively enhanced help for Dynamics fields. Extended help stays visible. */
function dynamics_field_help($text, bool $extended = FALSE): string {
    if (trim((string)$text) === '') {
        return '';
    }

    fusion_load_script(DYNAMICS.'includes/form_main/field-help.css', 'css');
    fusion_load_script(BASEDIR.'assets/libs/@popperjs/core/dist/umd/popper.min.js');
    fusion_load_script(DYNAMICS.'includes/form_main/field-help.js');
    $plain = htmlspecialchars(html_entity_decode(strip_tags((string)$text), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    $button = "<button type='button' class='dynamics-field-help__trigger' data-dynamics-help='{$plain}' aria-label='{$plain}' title='{$plain}'>".showtip($plain)."</button>";

    return $extended ? "<span class='dynamics-field-help dynamics-field-help--extended'><span>{$text}</span> {$button}</span>" : $button;
}
