<?php

use PHPFusion\Administration\Page\AdminPage;

$formId = (string)$section['form_id'];
echo $page->openApiForm($formId, (string)$section['endpoint'], [
    'class' => 'm-0',
    'validate_on_change' => !empty($section['validate_on_change']),
]);
?>
<section class="<?= framework_css('card') ?>" aria-labelledby="<?= htmlspecialchars($section['id'], ENT_QUOTES) ?>-title">
    <header class="<?= framework_css('card-header d-flex align-items-start justify-content-between gap-3') ?>">
        <div>
            <h2 id="<?= htmlspecialchars($section['id'], ENT_QUOTES) ?>-title" class="<?= framework_css('card-title mb-1') ?>">
                <?= htmlspecialchars((string)$section['title']) ?>
            </h2>
            <?php if (!empty($section['description'])) : ?>
                <p class="<?= framework_css('small text-muted m-0') ?>"><?= htmlspecialchars((string)$section['description']) ?></p>
            <?php endif; ?>
        </div>
    </header>
    <div class="<?= framework_css('card-body') ?>">
        <div class="<?= framework_css(!empty($section['aside_template']) ? 'row' : 'd-flex flex-column gap-3') ?>">
            <div class="<?= framework_css(!empty($section['aside_template']) ? 'col-xl-8' : 'w-100') ?>">
                <?php foreach ((array)$section['fields'] as $field) : ?>
                    <?php
                    $name = (string)$field['name'];
                    $label = (string)($field['label'] ?? '');
                    $value = $field['value'] ?? '';
                    $options = (array)($field['options'] ?? []);
                    $options += [
                        'required' => !empty($field['required']),
                        'max_length' => (int)($field['max_length'] ?? 200),
                        'class' => framework_css('mb-3'),
                    ];
                    switch ($field['type']) {
                        case 'textarea':
                            echo form_textarea($name, $label, $value, $options);
                            break;
                        case 'select':
                            $options['options'] = (array)($field['choices'] ?? []);
                            echo form_select($name, $label, $value, $options);
                            break;
                        default:
                            $options['type'] = $field['type'] === 'email' ? 'email' : ($field['type'] === 'number' ? 'number' : 'text');
                            echo form_text($name, $label, $value, $options);
                    }
                    ?>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($section['aside_template'])) : ?>
                <aside class="<?= framework_css('col-xl-4') ?>">
                    <?= AdminPage::template((string)$section['aside_template'], ['section' => $section]) ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
    <footer class="<?= framework_css('card-footer d-flex flex-wrap align-items-center justify-content-between gap-3') ?>">
        <p class="<?= framework_css('small text-muted m-0') ?>" data-fusion-feedback role="status" aria-live="polite"></p>
        <?= form_button(
            'save_'.$section['id'],
            (string)$section['submit'],
            '1',
            ['type' => 'submit', 'class' => framework_css('btn-primary ms-auto')]
        ) ?>
    </footer>
</section>
<?= closeform() ?>
