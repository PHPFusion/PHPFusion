<?php

function admin_theme_tpl($info)
{
	fusion_load_script(THEMES.'admin_themes/Pro/pfpro.js');
	
	$userdata = fusion_get_userdata();
	$sitebanner = '<img src="' . IMAGES . 'phpfusion-logo.svg" alt="">';
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
	$vfilter_class = (!empty($admin_page_nav) ?: 'nav-content');
	$main_width_class = $info['main_width_class'];
	
	?>
	<div class="pf-admin">
		<div class="pf-viewport">
			<nav class="pf-nav <?= $vfilter_class ?>">
				<div class="pf-nav-column">
					<header class="pf-nav-menu">
						<div class="pf-nav-details">
							<?php
							
							if (!empty($sitebanner)): ?>
								<div class="brand"><?= $sitebanner ?></div>
							<?php
							else: ?>
								<div class="title"><?= $settings['sitename']; ?></div>
							<?php
							endif; ?>
						</div>
						<div class="pf-nav-search">
							<button class="search-btn" title="Search site (CTRL + K)" data-action="search">
								<?= iconify('magnifying-glass', 'heroicons-outline', 'me-2') ?>
							</button>
						</div>
					</header>
					<section class="nav-body">
						<?php
						if (!empty($admin_page_nav)): ?>
							<div class="nav-top">
								<?= $admin_page_nav; ?>
							</div>
						<?php else: ?>
							<div class="nav-top">
								<ul class="main">
									<li>
										<a href="<?= $dashboard_uri; ?>" class="active" title="Dashboard">
											<?= iconify('squares-2x2', class:'admin-ico me-2') ?>
											Dashboard
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
												<a class="d-flex align-items-center gap-2" href="<?= $pages['admin_link']; ?>">
													<?= iconify($pages['admin_icon'], class:'admin-ico') ?>
													<?= $pages['admin_title']; ?>
												</a>
											</li>
										<?php
										endforeach; ?>
									<?php
									endif; ?>
									
									<?php
						
									foreach ($admin_sections as $section_key => $section): ?>
										<li data-section="<?= $section_key; ?>">
											<a href="#limenu_<?= $section_key; ?>" class="section-view">
												<?= iconify('chevron-right', class:'ico') ?>
											</a>
											<a class="section-menu" href="#s<?= $section_key; ?>">
												<?= iconify($section['icon'], class:'admin-ico me-2') ?>
												<?= $section['title']; ?>
											</a>
											<?php if (!empty($admin_pages[$section_key])): ?>
												<ul class="menu-container" style="display: none;">
													<?php foreach ($admin_pages[$section_key] as $pages): ?>
														<li>
															<a href="<?= $pages['admin_link']; ?>">
																<?= iconify($pages['admin_icon'], class:'admin-ico me-2') ?>
																<?= $pages['admin_title']; ?>
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
									<div class="nav-bottom-items">
										<div class="dropdown">
											<div class="pf-admin-flex">
												<div class="pf-admin-avatar" aria-expanded="false">
													<?= $admin_avatar; ?>
													<span class="status-badge on"></span>
												</div>
												<?= iconify('angle-up', 'uil') ?>
											</div>
										</div>
										<div>
											<a href="<?= $settings_uri; ?>" class="pf-admin-settings">
												<?= iconify('settings-line', 'ri', 'text-white') ?>
											</a>
										</div>
									</div>
								</div>
							</div>
						<?php
						endif; ?>
					</section>
				</div>
			</nav>
			<main class="pf-main" role="main">
				<div class="pf-canvas <?= $main_width_class ?>">
					<header class="pf-canvas-header">
						<h2>
							<?php
							$admin_breadcrumbs = $info['admin_breadcrumbs'];
							
							if (!empty($admin_breadcrumbs)): ?>
								<?php
								$index = 0;
								$total_items = count($admin_breadcrumbs);
								foreach ($admin_breadcrumbs as $breadcrumbs):
									?>
									<?php
									if ($index > 0): ?>
										<span>
											<svg width="20" height="20" fill="none" viewBox="0 0 24 26">
												<path fill-rule="evenodd" clip-rule="evenodd"
													  d="M4.793 1.043a1 1 0 011.414 0l10.72 10.72a1.748 1.748 0 010 2.475l-10.72 10.72a1 1 0 01-1.414-1.415L15.336 13 4.793 2.457a1 1 0 010-1.414z"
													  fill="#e8ebed">
												</path>
											</svg>
										</span>
									<?php
									endif;
									// Check if it's the last item or if the link is empty
									if (($index === $total_items - 1) || empty($breadcrumbs['link'])):
										echo $breadcrumbs['title'];
									else:
										?>
										<a href="<?= $breadcrumbs['link']; ?>">
											<?= $breadcrumbs['title']; ?>
										</a>
									<?php endif;
									$index++;
								endforeach;
							endif; ?>
						</h2>
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
					</header>
					<section class="pf-view-container">
						<?php
						if (!empty($admin_notices)): ?>
							<div class="pf-view-notices">
								<?= $admin_notices; ?>
							</div>
						<?php
						endif; ?>
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
			<ul class="pf-admin-dropup">
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
		</div>
	</div>
	<?php
	
}