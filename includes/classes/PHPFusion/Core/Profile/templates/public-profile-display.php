<?php

use PHPFusion\ProfileGlobal\ProfileUi;

defined('IN_FUSION') || exit;

$escape = static fn(mixed $value): string => ProfileUi::escape($value);
$basic = $blocks[0]['data'] ?? [];
?>
<main class="core-profile-page py-4 py-lg-5">
    <section class="card mb-4">
        <div class="card-body p-4 p-lg-5 d-flex flex-column flex-md-row align-items-md-center gap-4">
            <img class="avatar avatar-2xl rounded-circle" src="<?= $escape($avatarUrl) ?>" alt="<?= $escape($basic['display_name'] ?? $user['user_name']) ?>">
            <div class="flex-fill">
                <h1 class="h2 mb-1"><?= $escape($basic['display_name'] ?? $user['user_name']) ?></h1>
                <div class="text-secondary mb-2">@<?= $escape($user['user_name']) ?></div>
                <div class="d-flex flex-wrap gap-3 text-secondary small">
                    <span><?= $escape($basic['role'] ?? 'Member') ?></span>
                    <?php if (!empty($basic['location'])) : ?><span><?= ProfileUi::icon('map-pin', 16) ?> <?= $escape($basic['location']) ?></span><?php endif; ?>
                </div>
            </div>
            <?php if ($isOwner) : ?><a class="btn btn-primary" href="<?= BASEDIR ?>profile_edit.php"><span>Edit public profile</span></a><?php endif; ?>
        </div>
    </section>

    <div class="row g-4">
        <?php $rendered = 0; ?>
        <?php foreach ($blocks as $block) :
            $html = ($block['definition']['public'])($user, $block['data']);
            if ($html === '') { continue; }
            $rendered++;
            ?>
            <div class="col-12 col-lg-6"><?= $html ?></div>
        <?php endforeach; ?>
        <?php if ($rendered === 0) : ?>
            <div class="col-12"><div class="card"><div class="empty py-5"><p class="empty-title">This profile is taking shape</p><p class="empty-subtitle text-secondary">Public details will appear here when they are added.</p></div></div></div>
        <?php endif; ?>
    </div>
</main>
