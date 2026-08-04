<?php

use PHPFusion\ProfileGlobal\ProfileUi;

defined('IN_FUSION') || exit;

$escape = static fn(mixed $value): string => ProfileUi::escape($value);
$basic = $blocks[0]['data'] ?? [];
$links = $blocks[1]['data'] ?? [];
$open = openform('public-profile-editor', 'post', $endpoint, [
    'class' => 'core-public-profile-form',
    'enctype' => TRUE,
    'honeypot' => FALSE,
]);
echo preg_replace('/<form\b/', '<form data-public-profile-form', $open, 1);
?>
<main class="core-profile-page py-4 py-lg-5">
    <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-3 mb-4">
        <div>
            <div class="text-secondary small mb-2">Settings <span class="mx-2" aria-hidden="true">/</span> Public profile</div>
            <h1 class="h2 mb-1">Public profile</h1>
            <p class="text-secondary mb-0">Manage the information other people can see.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?= $escape($publicUrl) ?>"><span>View public profile</span></a>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-7">
            <?php foreach ($blocks as $block) {
                echo ($block['definition']['editor'])($user, $block['data']);
            } ?>

            <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2" data-public-profile-actions>
                <a class="btn btn-outline-secondary" href="<?= BASEDIR ?>profile_view.php?lookup=<?= (int)$user['user_id'] ?>"><span>Cancel</span></a>
                <button class="btn btn-dark" type="submit">
                    <span data-profile-button-label>Save changes</span>
                </button>
            </div>
            <div class="small mt-2 text-end" role="status" aria-live="polite" data-public-profile-status></div>
        </div>

        <aside class="col-12 col-xl-5 core-profile-preview-column" aria-label="Live public profile preview">
            <section class="card core-profile-preview-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <h2 class="h4 mb-0">Live preview</h2>
                        <span class="badge bg-secondary-subtle text-secondary">Public view</span>
                    </div>
                    <div class="border rounded p-4 text-center">
                        <img class="avatar avatar-xl rounded-circle mb-3" src="<?= $escape($avatarUrl) ?>" alt="" data-public-preview-avatar>
                        <h3 class="h3 mb-1" data-public-preview-output="name"><?= $escape($basic['display_name'] ?? $user['user_name']) ?></h3>
                        <div class="text-secondary mb-2">@<span data-public-preview-output="username"><?= $escape($basic['username'] ?? $user['user_name']) ?></span></div>
                        <div class="mb-2" data-public-preview-output="role"><?= $escape($basic['role'] ?? 'Member') ?></div>
                        <div class="text-secondary mb-3" data-public-preview-line="location">
                            <?= ProfileUi::icon('map-pin', 16) ?>
                            <span data-public-preview-output="location"><?= $escape($basic['location'] ?? '') ?></span>
                        </div>
                        <hr>
                        <p class="text-secondary mb-0" data-public-preview-output="bio"><?= nl2br($escape($basic['bio'] ?? 'Add a short bio to introduce yourself.')) ?></p>
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-3" data-public-preview-links>
                            <?php foreach (['website' => 'Website', 'twitter' => 'X (Twitter)', 'linkedin' => 'Linkedin', 'discord' => 'Discord'] as $name => $label) : ?>
                                <?php if (!array_key_exists($name, $links)) { continue; } ?>
                                <span class="btn btn-outline-secondary btn-sm <?= empty($links[$name]) ? 'd-none' : '' ?>" data-public-preview-link="<?= $escape($name) ?>"><span><?= $escape($label) ?></span></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <p class="small text-secondary text-center mb-0 mt-3">This is how your profile appears to others.</p>
                </div>
            </section>
        </aside>
    </div>
</main>
<?= closeform() ?>
