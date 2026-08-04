<?php

use PHPFusion\Administration\Page\AdminPage;

$tabs = ['title' => [], 'id' => [], 'icon' => []];
foreach ($model['tabs'] as $tab) {
    $tabs['title'][] = $tab['title'];
    $tabs['id'][] = $tab['id'];
    $tabs['icon'][] = '';
}
$active = tab_active($tabs, 0);

echo opentab($tabs, $active, 'main-settings-tabs', remember: TRUE);
foreach ($model['tabs'] as $tab) {
    echo opentabbody('main-settings-tabs', $tab['id'], $active);
    echo '<div class="'.framework_css('d-flex flex-column gap-4').'">';
    foreach ($tab['sections'] as $sectionId) {
        if (!empty($model['sections'][$sectionId])) {
            echo AdminPage::template(__DIR__.'/section.php', [
                'page' => $page,
                'section' => $model['sections'][$sectionId] + ['id' => $sectionId],
            ]);
        }
    }
    echo '</div>';
    echo closetabbody();
}
echo closetab();
