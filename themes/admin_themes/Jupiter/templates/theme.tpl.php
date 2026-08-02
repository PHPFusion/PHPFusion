<?php

function admin_theme_tpl($info)
{
	$userdata = fusion_get_userdata();
	$user_level_label = (string)(getuserlevel((int)($userdata['user_level'] ?? 0)) ?? '');
	$admin_page_nav = $info['admin_page_nav'];
	$admin_buttons = $info['admin_buttons'];
	$admin_pages = $info['admin_pages'];
	$dashboard_uri = $info['dashboard_uri'];
	$admin_sections = $info['admin_sections'];
	$admin_avatar = $info['admin_avatar'];
	$settings_uri = $info['settings_uri'];
	$admin_notices = $info['admin_notices'];
	$content = $info['content'];
	$footer_errors = $info['footer_errors'];
	$api_url = $info['api_url'];
	$settings = fusion_get_settings();
	$vfilter_class = empty($admin_page_nav) ? 'nav-content' : '';
	$main_width_class = $info['main_width_class'];
	$admin_breadcrumbs = $info['admin_breadcrumbs'] ?? [];
	$current_page_title = 'Administration';
	if (!empty($admin_breadcrumbs)) {
		$last_breadcrumb = end($admin_breadcrumbs);
		$current_page_title = strip_tags((string)($last_breadcrumb['title'] ?? $current_page_title));
		reset($admin_breadcrumbs);
	}
	$command_items = [
		[
			'title' => 'Home',
			'link' => $dashboard_uri,
			'icon' => iconify('home', class: 'admin-ico'),
			'section' => 'Platform',
		],
		[
			'title' => 'View Site',
			'link' => BASEDIR.'index.php',
			'icon' => iconify('square-2-stack', class: 'admin-ico'),
			'section' => 'General',
		],
	];
	foreach ($admin_pages as $section_key => $pages) {
		if (!is_array($pages)) {
			continue;
		}
		foreach ($pages as $page) {
			if (empty($page['admin_link']) || empty($page['admin_title'])) {
				continue;
			}
			$command_items[$page['admin_link']] = [
				'title' => $page['admin_title'],
				'link' => $page['admin_link'],
				'icon' => $page['admin_icon'] ?? '',
				'section' => $admin_sections[$section_key]['title'] ?? 'Administration',
			];
		}
	}
	
	?>
	<div class="pf-admin">
		<a class="jupiter-skip-link" href="#jupiter-admin-content">Skip to content</a>
		<div class="pf-viewport">
			<nav class="pf-nav <?= $vfilter_class ?>">
				<div class="pf-nav-column">
					<header class="pf-nav-menu">
						<div class="pf-nav-details">
							<div class="brand">
								<img src="<?= IMAGES.'phpfusion-icon.png'; ?>" alt="">
								<span class="jupiter-brand-name">
									<?= htmlspecialchars((string)$settings['sitename']); ?>
								</span>
							</div>
						</div>
						<div class="pf-nav-search">
							<span class="jupiter-brand-switch" aria-hidden="true">
								<?= iconify('chevron-up-down', 'heroicons-outline') ?>
							</span>
							<button class="search-btn" type="button"
							        title="Search administration (/ or Ctrl + K)"
							        aria-label="Search administration"
							        aria-controls="jupiter-command-palette"
							        aria-expanded="false"
							        aria-haspopup="dialog"
							        aria-keyshortcuts="/ Control+K Meta+K"
							        data-action="search">
								<?= iconify('magnifying-glass', 'heroicons-outline') ?>
							</button>
							<button class="jupiter-mobile-nav-toggle" type="button" title="Open navigation"
							        aria-label="Open navigation" aria-expanded="false"
							        aria-controls="jupiter-admin-navigation" data-action="mobile-nav">
								<?= iconify('bars-3', 'heroicons-outline') ?>
							</button>
						</div>
					</header>
					<section class="nav-body" id="jupiter-admin-navigation">
						<?php
						if (!empty($admin_page_nav)): ?>
							<div class="nav-top">
								<?= $admin_page_nav; ?>
							</div>
						<?php else: ?>
							<div class="nav-top">
								<div class="jupiter-nav-label">Platform</div>
								<ul class="main">
									<li>
										<a href="<?= htmlspecialchars($dashboard_uri, ENT_QUOTES); ?>"
										   class="<?= FUSION_SELF === 'index.php' ? 'active' : ''; ?>" title="Home">
											<?= iconify('home', class:'admin-ico me-2') ?>
											Home
										</a>
									</li>
									<li>
										<a href="<?= BASEDIR.'index.php' ?>" title="View Site">
											<?= iconify('square-2-stack', class:'admin-ico me-2') ?>
											View Site
										</a>
									</li>
								</ul>
								<ul class="sections">
									<?php
									if (isset($admin_pages[1]) && is_array($admin_pages[1])): ?>
										<?php
										foreach ($admin_pages[1] as $pages): ?>
											<li>
												<a class="d-flex align-items-center gap-2"
												   href="<?= htmlspecialchars($pages['admin_link'], ENT_QUOTES); ?>">
													<?= $pages['admin_icon']; ?>
													<?= htmlspecialchars($pages['admin_title']); ?>
												</a>
											</li>
										<?php
										endforeach; ?>
									<?php
									endif; ?>
									
									<?php
						
									foreach ($admin_sections as $section_key => $section):
										$section_active = !empty($section['is_active']);
										?>
										<li data-section="<?= $section_key; ?>"
										    class="<?= $section_active ? 'is-open is-current' : ''; ?>">
											<button type="button" class="section-view"
											   aria-expanded="<?= $section_active ? 'true' : 'false'; ?>"
											   aria-controls="jupiter-section-<?= $section_key; ?>"
											   aria-label="Toggle <?= htmlspecialchars($section['title'], ENT_QUOTES); ?>">
												<?= iconify('chevron-right', class:'ico') ?>
											</button>
											<a class="section-menu" href="#s<?= $section_key; ?>">
												<span class="admin-ico me-2"><?= $section['icon']; ?></span>
												<?= htmlspecialchars($section['title']); ?>
											</a>
											<?php if (!empty($admin_pages[$section_key])): ?>
												<ul class="menu-container" id="jupiter-section-<?= $section_key; ?>"
												    style="display: <?= $section_active ? 'block' : 'none'; ?>;">
													<?php foreach ($admin_pages[$section_key] as $pages):
														$page_active = !empty($pages['is_active']);
														?>
														<li>
															<a href="<?= htmlspecialchars($pages['admin_link'], ENT_QUOTES); ?>"
															   class="<?= $page_active ? 'active' : ''; ?>"
															   <?= $page_active ? 'aria-current="page"' : ''; ?>>
																<?= htmlspecialchars($pages['admin_title']); ?>
															</a>
														</li>
													<?php endforeach; ?>
												</ul>
											<?php endif; ?>
										</li>
									<?php
									endforeach; ?>
								</ul>
							</div>
							<div class="nav-bottom">
								<div class="nav-bottom-wrapper">
									<ul class="jupiter-resource-links">
										<li>
											<a href="<?= htmlspecialchars($api_url['doc_uri'], ENT_QUOTES); ?>">
												<?= iconify('book-open', class: 'admin-ico') ?> Docs
											</a>
										</li>
										<li>
											<a href="<?= htmlspecialchars($api_url['support_uri'], ENT_QUOTES); ?>">
												<?= iconify('lifebuoy', class: 'admin-ico') ?> Support
											</a>
										</li>
										<li>
											<a href="<?= BASEDIR.'index.php'; ?>">
												<?= iconify('globe-alt', class: 'admin-ico') ?> View site
											</a>
										</li>
										<li>
											<a href="<?= htmlspecialchars($settings_uri, ENT_QUOTES); ?>">
												<?= iconify('adjustments-horizontal', class: 'admin-ico') ?> Settings
											</a>
										</li>
									</ul>
									<div class="nav-bottom-items">
										<div class="tw-dropdown">
											<button class="pf-admin-flex" type="button" data-action="account-menu"
											        aria-expanded="false" aria-controls="jupiter-admin-account"
											        aria-label="Open account menu for <?= htmlspecialchars(
												        (string)$userdata['user_name'].' — '.$user_level_label,
												        ENT_QUOTES
											        ); ?>">
												<div class="pf-admin-avatar">
													<?= $admin_avatar; ?>
													<span class="status-badge on" aria-hidden="true"></span>
													<span class="tw-sr-only">Online</span>
												</div>
												<span class="jupiter-account-copy">
													<strong><?= htmlspecialchars((string)$userdata['user_name']); ?></strong>
													<small class="jupiter-account-rank"><?= htmlspecialchars($user_level_label); ?></small>
												</span>
												<span class="jupiter-account-action" aria-hidden="true">
													<?= iconify(
														'chevron-up-down',
														'heroicons-outline',
														'jupiter-account-chevron'
													) ?>
												</span>
											</button>
										</div>
									</div>
								</div>
							</div>
						<?php
						endif; ?>
					</section>
				</div>
			</nav>
			<main class="pf-main" id="jupiter-admin-content" role="main" tabindex="-1">
				<div class="pf-canvas <?= $main_width_class ?>">
					<header class="pf-canvas-header">
						<div class="jupiter-topbar-context">
							<span class="jupiter-context-icon"><?= iconify('list-bullet', 'heroicons-outline') ?></span>
							<span>All sections</span>
							<?= iconify('chevron-up-down', 'heroicons-outline') ?>
						</div>
						<h2 class="jupiter-page-title"><?= htmlspecialchars($current_page_title); ?></h2>
						<div class="jupiter-topbar-actions">
							<button class="jupiter-command-search" type="button" data-action="search"
							        title="Search administration (/)"
							        aria-label="Search administration"
							        aria-controls="jupiter-command-palette"
							        aria-expanded="false"
							        aria-haspopup="dialog"
							        aria-keyshortcuts="/ Control+K Meta+K">
								<?= iconify('magnifying-glass', 'heroicons-outline') ?>
								<span class="jupiter-search-label">Search administration</span>
								<kbd aria-hidden="true">/</kbd>
							</button>
						<?php
						if ($admin_buttons):
							?>
							<noscript>
								<style>
                                    .admin-buttons {
                                        display: none;
                                    }
                                    .admin-buttons-legacy {
                                        display: block;
                                    }
								</style>
							</noscript>
							<div class="admin-buttons">
								<?php
								if (is_array($admin_buttons)): ?>
									<?php foreach ($admin_buttons as $elem): ?>
										<?= $elem; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						<?php
						endif; ?>
						</div>
					</header>
					<section class="pf-view-container">
						<?php
						if (!empty($admin_notices)): ?>
							<div class="pf-view-notices">
								<?= $admin_notices; ?>
							</div>
						<?php
						endif; ?>
						<div class="jupiter-content-breadcrumbs">
							<?= render_breadcrumbs(); ?>
						</div>
						<?= $content; ?>
					</section>
					<?php
					if (!empty($footer_errors)): ?>
						<div class="footer-errors">
							<?= $footer_errors; ?>
						</div>
					<?php
					endif; ?>
				</div>
			</main>
			<ul class="pf-admin-dropup" id="jupiter-admin-account" role="menu" aria-hidden="true">
				<li role="presentation">
					<div class="group-heading">
						<div class="avatar"><?php
							echo $admin_avatar; ?></div>
						<div class="user-info">
							<h4><?php
								echo $userdata['user_name']; ?></h4>
							<span class="email"><?php
								echo $userdata['user_email']; ?></span>
						</div>
					</div>
				</li>
				<li role="separator" class="divider"></li>
				<li role="presentation">
					<a href="<?php
					echo $api_url['doc_uri']; ?>">What's new?</a>
				</li>
				<li role="presentation">
					<a href="<?php
					echo $info['profile_uri']; ?>">Your profile</a>
				</li>
				<li role="separator" class="divider"></li>
				<li role="presentation">
					<a href="<?php
					echo $api_url['support_uri']; ?>">PHPFusion support</a>
				</li>
				<li role="presentation">
					<a href="<?php
					echo $api_url['how_uri']; ?>">How to use PHPFusion</a>
				</li>
				<li role="separator" class="divider"></li>
				<li role="presentation">
					<a href="<?php
					echo $info['signout_uri']; ?>">Sign out</a>
				</li>
			</ul>
			<div class="jupiter-command-palette" id="jupiter-command-palette" aria-hidden="true">
				<div class="jupiter-command-dialog" role="dialog" aria-modal="true"
				     aria-labelledby="jupiter-command-title">
					<h2 class="tw-sr-only" id="jupiter-command-title">Search administration</h2>
					<div class="jupiter-command-field">
						<?= iconify('magnifying-glass', 'heroicons-outline') ?>
						<input type="search" id="jupiter-command-input" autocomplete="off"
						       placeholder="Search pages and settings"
						       aria-label="Search pages and settings">
						<button class="jupiter-command-close" type="button" data-action="close-search"
						        aria-label="Close search">Esc</button>
					</div>
					<div class="jupiter-command-results" id="jupiter-command-results">
						<?php foreach ($command_items as $item): ?>
							<a class="jupiter-command-item"
							   href="<?= htmlspecialchars($item['link'], ENT_QUOTES); ?>"
							   data-search="<?= htmlspecialchars(
								   strtolower($item['title'].' '.$item['section']),
								   ENT_QUOTES
							   ); ?>">
								<?= $item['icon']; ?>
								<span><?= htmlspecialchars($item['title']); ?></span>
								<span class="ms-auto text-muted"><?= htmlspecialchars($item['section']); ?></span>
							</a>
						<?php endforeach; ?>
						<div class="jupiter-command-empty" id="jupiter-command-empty">No administration pages found.</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	
}
