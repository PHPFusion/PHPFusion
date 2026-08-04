<?php

use PHPFusion\ProfileGlobal\ProfileRegistrationFields;
use PHPFusion\ProfileGlobal\ProfileUi;

defined('IN_FUSION') || exit;

$escape = static fn(mixed $value): string => ProfileUi::escape($value);
$slug = static fn(mixed $value): string => trim((string)preg_replace('/[^a-z0-9_-]+/i', '-', (string)$value), '-');
$fieldRenderer = new ProfileRegistrationFields();
?>
<main class="core-profile-page py-4 py-lg-5" data-profile-settings>
    <div class="mb-4">
        <div class="text-secondary small mb-2">Settings <span class="mx-2" aria-hidden="true">/</span> Edit profile</div>
        <h1 class="h2 mb-1">Edit profile</h1>
        <p class="text-secondary mb-0">Manage your personal information and preferences.</p>
    </div>

    <?php if (!$categories) : ?>
        <div class="card">
            <div class="empty py-5">
                <p class="empty-title">No profile categories are available</p>
                <p class="empty-subtitle text-secondary">Add a category folder under apps/user_fields to begin.</p>
            </div>
        </div>
    <?php else : ?>
        <div class="row g-4 align-items-start">
            <aside class="col-12 col-md-3 col-xl-2 core-profile-settings-sidebar">
                <nav
                    class="nav nav-pills flex-row flex-lg-column flex-nowrap overflow-auto gap-1 core-profile-settings-nav"
                    aria-label="Profile settings categories"
                    role="tablist"
                >
                    <?php foreach ($categories as $categoryIndex => $category) :
                        $categoryKey = (string)$category['key'];
                        $categorySlug = $slug($categoryKey);
                        $active = $categoryIndex === array_key_first($categories);
                        ?>
                        <a
                            id="profile-setting-tab-<?= $escape($categorySlug) ?>"
                            class="nav-link d-flex align-items-center gap-2 flex-shrink-0 <?= $active ? 'active' : '' ?>"
                            href="#profile-setting-category-<?= $escape($categorySlug) ?>"
                            role="tab"
                            aria-controls="profile-setting-category-<?= $escape($categorySlug) ?>"
                            aria-selected="<?= $active ? 'true' : 'false' ?>"
                            tabindex="<?= $active ? '0' : '-1' ?>"
                            data-profile-settings-tab="<?= $escape($categoryKey) ?>"
                        >
                            <span class="d-inline-flex" aria-hidden="true"><?= ProfileUi::icon((string)$category['icon'], 18) ?></span>
                            <span><?= $escape($category['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <a class="btn btn-outline-secondary w-100 mt-3" href="<?= $escape($publicEditorUrl) ?>">
                    <span>Edit public profile</span>
                </a>
            </aside>
            <div class="col-12 col-md-9 core-profile-settings-content">
                <?php foreach ($categories as $categoryIndex => $category) :
                    $categoryKey = (string)$category['key'];
                    $categorySlug = $slug($categoryKey);
                    $active = $categoryIndex === array_key_first($categories);
                    $categoryModules = $modules[$categoryKey] ?? [];
                    ?>
                    <section
                        id="profile-setting-category-<?= $escape($categorySlug) ?>"
                        class="<?= $active ? '' : 'd-none' ?>"
                        role="tabpanel"
                        aria-labelledby="profile-setting-tab-<?= $escape($categorySlug) ?>"
                        data-profile-settings-panel="<?= $escape($categoryKey) ?>"
                    >

                        <?php if (!$categoryModules) : ?>
                            <div class="card">
                                <div class="empty py-5">
                                    <div class="empty-icon text-secondary" aria-hidden="true"><?= ProfileUi::icon((string)$category['icon'], 28) ?></div>
                                    <p class="empty-title">No settings available</p>
                                    <p class="empty-subtitle text-secondary">This category is ready when an administrator enables a module.</p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($categoryModules as $module) :
                            $definition = $module['definition'];
                            $moduleId = (string)$definition['id'];
                            $moduleSlug = $slug($moduleId);
                            $formId = 'profile-setting-' . $moduleSlug;
                            $hasAvatar = FALSE;
                            foreach ($module['schema'] as $field) {
                                $hasAvatar = $hasAvatar || ($field['type'] ?? '') === 'avatar';
                            }
                            ?>
                            <section class="mb-4" aria-labelledby="profile-setting-title-<?= $escape($moduleSlug) ?>">
                                <?php
                                $open = openform($formId, 'post', $module['endpoint'], [
                                    'class' => 'card m-0',
                                    'enctype' => $hasAvatar,
                                    'honeypot' => FALSE,
                                ]);
                                echo preg_replace('/<form\b/', '<form data-profile-module-form', $open, 1);
                                ?>
                                <?= form_hidden('module', '', $moduleId, ['input_id' => $formId . '-module']) ?>

                                <div class="card-header d-flex align-items-start gap-3 p-4">
                                    <span class="avatar bg-secondary-subtle text-secondary" aria-hidden="true">
                                        <?= ProfileUi::icon((string)$definition['icon'], 20) ?>
                                    </span>
                                    <div>
                                        <h3 id="profile-setting-title-<?= $escape($moduleSlug) ?>" class="h4 mb-1"><?= $escape($definition['label']) ?></h3>
                                        <?php if (!empty($definition['description'])) : ?>
                                            <p class="text-secondary mb-0"><?= $escape($definition['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <?php if (($definition['field_layout'] ?? '') === 'row') : ?><div class="row g-3"><?php endif; ?>
                                    <?php foreach ($module['schema'] as $field) :
                                        $name = (string)($field['name'] ?? '');
                                        $type = (string)($field['type'] ?? 'text');
                                        $value = $module['values'][$name] ?? ($field['value'] ?? ($field['default'] ?? ''));
                                        $fieldId = 'profile-setting-' . $moduleSlug . '-' . $slug($name);
                                        $errorId = $fieldId . '-error';
                                        $fieldWrapperClass = ($field['layout'] ?? '') === 'half'
                                            ? 'col-12 col-sm-6'
                                            : 'mb-3';
                                        ?>
                                        <div class="<?= $fieldWrapperClass ?>" data-profile-field="<?= $escape($name) ?>">
                                            <?php if ($type === 'avatar') : ?>
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                                                    <img
                                                        class="avatar avatar-xl rounded-circle"
                                                        src="<?= $escape($module['values']['avatar_url'] ?? $avatarUrl) ?>"
                                                        alt="Current profile photo"
                                                        data-profile-avatar-preview
                                                    >
                                                    <div class="flex-fill">
                                                        <?php
                                                        $field['value'] = $value;
                                                        $field['input_id'] = $fieldId;
                                                        $field['aria_describedby'] = $errorId;
                                                        $field['data'] = ['profile-avatar-input' => ''];
                                                        echo $fieldRenderer->renderField($field);
                                                        ?>
                                                        <?php if (!empty($module['values']['avatar_name'])) : ?>
                                                            <button class="btn btn-ghost-danger btn-sm mt-2" type="submit" name="avatar_action" value="delete">
                                                                <span>Remove photo</span>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else :
                                                $field['value'] = $value;
                                                $field['input_id'] = $fieldId;
                                                $field['aria_describedby'] = $errorId;
                                                echo $fieldRenderer->renderField($field);
                                            endif; ?>
                                            <div
                                                class="invalid-feedback d-block"
                                                id="<?= $escape($errorId) ?>"
                                                data-profile-field-error="<?= $escape($name) ?>"
                                                role="status"
                                            ></div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (($definition['field_layout'] ?? '') === 'row') : ?></div><?php endif; ?>
                                </div>

                                <div class="card-footer d-flex flex-column-reverse flex-sm-row align-items-sm-center justify-content-between gap-2 p-3 px-4">
                                    <div class="small" data-profile-save-status role="status" aria-live="polite"></div>
                                    <div class="d-flex flex-column-reverse flex-sm-row gap-2">
                                        <button class="btn btn-outline-secondary" type="reset"><span>Cancel</span></button>
                                        <button class="btn btn-dark" type="submit" name="avatar_action" value="<?= $hasAvatar ? 'upload' : '' ?>">
                                            <span data-profile-button-label><?= $hasAvatar ? 'Save photo' : 'Save changes' ?></span>
                                        </button>
                                    </div>
                                </div>
                                <?= closeform() ?>
                            </section>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</main>
