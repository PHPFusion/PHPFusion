<?php

function notification_menu()
{
	$userdata = fusion_get_userdata();
	
	return [
		'n1' => [
			'link_id'         => 'n1',
			'link_item_class' => 'p0',
			// Add new method to super-menu rendering
			'link_content'    => '<div class="card" style="min-width:350px;">' .
				'<div class="card-header">' .
				'<div class="d-flex flex-row"><strong>Notifications</strong><span class="badge bg-danger bg-opacity-10 text-danger ms-2">4 new</span>
        <span class="ms-auto"><a class="small" href="">Clear all</a></span></div>' .
				'</div><div class="card-body p-0">' .
				'<ul class="list-group list-group-flush list-unstyled p-2">' .
				'<li>
            <div class="list-group-item list-group-item-action unread rounded d-flex border-0 mb-1 p-3">
                <div class="avatar -text-center d-none d-sm-inline-block">' . display_avatar($userdata, '48px') . '</div>
                <div class="ms-sm-3">
                    <div class="d-flex">
                        <p class="small mb-2"><strong>Judy Ngyuen</strong> sent you a friend request.</p><p class="small ms-3 text-nowrap">Just now</p>
                    </div>
                    <div class="d-flex">
                        <button class="btn btn-sm py-1 btn-primary me-2">Accept</button>
                        <button class="btn btn-sm py-1 btn-danger-soft">Delete </button>
                    </div>                   
                </div>
            </div>
        </li>' .
				'<li>
            <a href="#" class="list-group-item list-group-item-action unread rounded d-flex border-0 mb-1 p-3">
              <div class="avatar text-center d-none d-sm-inline-block">
                ' . display_avatar($userdata, '48px', 'rounded-circle', FALSE, 'rounded-circle') . '
              </div>
              <div class="ms-sm-3">
                <div class="d-flex">
                  <p class="small mb-2">Webestica has 15 like and 1 new activity</p>
                  <p class="small ms-3">1hr</p>
                </div>
              </div>
            </a>
        </li>' .
				'<li>
            <a href="#" class="list-group-item rounded d-flex border-0 p-3 mb-1">
        ' . display_avatar($userdata, '48px', 'rounded-circle', FALSE, 'rounded-circle overflow-hide') . '      
              <div class="ms-sm-3 d-flex">
                <p class="small mb-2"><b>Bootstrap in the news:</b> The search giant’s parent company, Alphabet, just joined an exclusive club of tech stocks.</p>
                <p class="small ms-3">4hr</p>
              </div>
            </a>
          </li></ul>' .
				'</div><div class="card-footer">' .
				'<div class="text-center"><a href="#" class="btn btn-sm btn-primary-soft">See all incoming activity</a></div>' .
				'</div></div>',
		],
	];
	
	
}

function uip_menu()
{
	$userdata = fusion_get_userdata();
	
	add_to_jquery("
    $('.btn-theme-options').on('click', function(e) {
        e.preventDefault();
        let val = $(this).data('bs-theme-value');        
        toggleColorScheme(val);        
    });
    ");
	fusion_load_script(THEME . 'styles.js');
	
	$nav_link = [
		'n2' => [
			'link_id'         => 'n2',
			'link_item_class' => 'p0 px-3',
			// Add new method to super-menu rendering
			'link_content'    => '<div class="uip-menu w-100"><div class="d-flex align-items-center position-relative">
                <!-- Avatar -->
                ' . display_avatar($userdata, '50px', 'rounded-circle me-3', FALSE, 'rounded-circle overflow-hide') . '
                <div>
                <a class="h6" href="#">Lori Ferguson</a>
                  <p class="small">Web Developer</p>
                </div>
              </div>
              <a class="dropdown-item btn btn-primary-soft my-2 py-1 text-center" href="' . BASEDIR . 'profile.php?lookup=' . $userdata['user_id'] . '">View profile</a>
              </div>
              ',
		],
		'n3' => [
			'link_id'         => 'n3',
			'link_item_class' => 'px-3',
			'link_class'      => 'nav-link lh-1 py-2 d-flex align-items-center',
			'link_name'       => '<iconify-icon icon="solar:settings-linear" class="me-2 fs-5"></iconify-icon>Settings & Privacy',
			'link_url'        => BASEDIR . 'edit_profile.php',
		],
		'n4' => [
			'link_id'         => 'n4',
			'link_item_class' => 'px-3',
			'link_class'      => 'nav-link lh-1 py-2 d-flex align-items-center',
			'link_name'       => '<iconify-icon icon="solar:question-square-linear" class="me-2 fs-5"></iconify-icon>Support',
			'link_url'        => BASEDIR . 'contact.php',
		],
		'n5' => [
			'link_id'   => 'n5',
			'link_name' => '---',
		],
	];
	
	if (iADMIN) :
		$nav_link['n6'] = [
			'link_id'         => 'n6',
			'link_item_class' => 'px-3',
			'link_class'      => 'nav-link lh-1 py-2 d-flex align-items-center',
			'link_name'       => '<iconify-icon icon="solar:shield-keyhole-linear" class="me-2 fs-5"></iconify-icon>Administration',
			'link_url'        => ADMIN . fusion_get_aidlink(),
		];
	endif;
	
	$nav_link['n7'] = [
		'link_id'         => 'n7',
		'link_item_class' => 'px-3',
		'link_class'      => 'nav-link lh-1 py-2',
		'link_name'       => '<iconify-icon icon="solar:logout-2-linear" class="me-2 fs-5"></iconify-icon>Sign Out',
		'link_url'        => clean_request('logout=yes', [], TRUE),
	];
	$nav_link['n8'] = [
		'link_id'   => 'n8',
		'link_name' => '---',
	];
	$nav_link['n9'] = [
		'link_id'      => 'n9',
		'link_content' => '<div class="d-flex w-100 px-3 justify-content-end gap-1 align-items-center" role="group">
                  <span class="me-3">Mode:</span>                  
                  <button type="button" class="btn btn-outline-secondary btn-theme-options nav-link text-primary-hover mb-0" data-theme="auto" data-bs-theme-value="auto" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Auto">
                       <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brightness"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 3l0 18" /><path d="M12 9l4.65 -4.65" /><path d="M12 14.3l7.37 -7.37" /><path d="M12 19.6l8.85 -8.85" /></svg>
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-theme-options nav-link text-primary-hover mb-0" data-theme="light" data-bs-theme-value="light" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Light">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-brightness-down"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8a4 4 0 1 1 -3.995 4.2l-.005 -.2l.005 -.2a4 4 0 0 1 3.995 -3.8z" /><path d="M12 4a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /><path d="M17 6a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /><path d="M19 11a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /><path d="M17 16a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /><path d="M12 18a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /><path d="M7 16a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /><path d="M5 11a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /><path d="M7 6a1 1 0 0 1 .993 .883l.007 .127a1 1 0 0 1 -1.993 .117l-.007 -.127a1 1 0 0 1 1 -1z" /></svg>
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-theme-options nav-link text-primary-hover mb-0" data-theme="dark" data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Dark">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-moon-stars"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2" /><path d="M19 11h2m-1 -1v2" /></svg>
                  </button>
                </div>',
	];
	
	return $nav_link;
}
