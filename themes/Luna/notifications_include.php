<?php

function notification_menu() {
    $userdata = fusion_get_userdata();

    return [
        'n1' => [
            'link_id'         => 'n1',
            'link_item_class' => 'p0',
            // Add new method to super-menu rendering
            'link_content'    => '<div class="card" style="min-width:350px;">' .
                '<div class="card-header">' .
                '<div class="d-flex flex-row"><h6><strong>Notifications</strong></h6><span class="badge bg-danger bg-opacity-10 text-danger ms-2">4 new</span>
        <span class="ms-auto"><a class="small" href="">Clear all</a></span></div>' .
                '</div><div class="card-body p-0">' .
                '<ul class="list-group list-group-flush list-unstyled p-2">' .
                '<li>
            <div class="list-group-item list-group-item-action unread rounded d-flex border-0 mb-1 p-3">
                <div class="avatar -text-center d-none d-sm-inline-block">' . display_avatar( $userdata, '48px' ) . '</div>
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
                ' . display_avatar( $userdata, '48px', 'rounded-circle', FALSE, 'rounded-circle' ) . '
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
        ' . display_avatar( $userdata, '48px', 'rounded-circle', FALSE, 'rounded-circle overflow-hide' ) . '      
              <div class="ms-sm-3 d-flex">
                <p class="small mb-2"><b>Bootstrap in the news:</b> The search giant’s parent company, Alphabet, just joined an exclusive club of tech stocks.</p>
                <p class="small ms-3">4hr</p>
              </div>
            </a>
          </li></ul>' .
        '</div><div class="card-footer">' .
            '<div class="text-center"><a href="#" class="btn btn-sm btn-primary-soft">See all incoming activity</a></div>' .
        '</div></div>'
        ],
    ];


}

function uip_menu() {
    $userdata = fusion_get_userdata();

    add_to_jquery("
    $('.btn-theme-options').on('click', function(e) {
        e.preventDefault();
        let val = $(this).data('bs-theme-value');        
        toggleColorScheme(val);        
    });
    ");
    fusion_load_script(THEME.'styles.js');

    return [
        'n2' => [
            'link_id'         => 'n2',
            'link_item_class' => 'p0 px-3',
            // Add new method to super-menu rendering
            'link_content'    => '<div class="uip-menu w-100"><div class="d-flex align-items-center position-relative">
                <!-- Avatar -->
                ' . display_avatar( $userdata, '50px', 'rounded-circle me-3', FALSE, 'rounded-circle overflow-hide' ) . '
                <div>
                <a class="h6" href="#">Lori Ferguson</a>
                  <p class="small text-lighter m-0">Web Developer</p>
                </div>
              </div>
              <a class="dropdown-item btn btn-primary-soft my-2 text-center" href="' . BASEDIR . 'profile.php?lookup=' . $userdata['user_id'] . '">View profile</a>
              </div>
              ',
        ],
        'n3' => [
            'link_id'         => 'n3',
            'link_item_class' => 'px-3',
            // Add new method to super-menu rendering
            'link_name'       => '<span class="me-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                     <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                     <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                     </svg></span>Settings & Privacy',
            'link_url'        => BASEDIR . 'edit_profile.php',
        ],
        'n4' => [
            'link_id'         => 'n4',
            'link_item_class' => 'px-3',
            // Add new method to super-menu rendering
            'link_name'       => '<span class="me-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-life-preserver" viewBox="0 0 16 16">
                      <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm6.43-5.228a7.025 7.025 0 0 1-3.658 3.658l-1.115-2.788a4.015 4.015 0 0 0 1.985-1.985l2.788 1.115zM5.228 14.43a7.025 7.025 0 0 1-3.658-3.658l2.788-1.115a4.015 4.015 0 0 0 1.985 1.985L5.228 14.43zm9.202-9.202-2.788 1.115a4.015 4.015 0 0 0-1.985-1.985l1.115-2.788a7.025 7.025 0 0 1 3.658 3.658zm-8.087-.87a4.015 4.015 0 0 0-1.985 1.985L1.57 5.228A7.025 7.025 0 0 1 5.228 1.57l1.115 2.788zM8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg></span>Support',
            'link_url'        => BASEDIR . 'contact.php',
        ],
        'n5' => [
            'link_id'   => 'n5',
            'link_name' => '---'
        ],
        'n6' => [
            'link_id'         => 'n6',
            'link_item_class' => 'px-3',
            'link_name'       => '<span class="me-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-power" viewBox="0 0 16 16">
                      <path d="M7.5 1v7h1V1h-1z"/>
                      <path d="M3 8.812a4.999 4.999 0 0 1 2.578-4.375l-.485-.874A6 6 0 1 0 11 3.616l-.501.865A5 5 0 1 1 3 8.812z"/>
                    </svg></span>Sign Out',
            'link_url'        => clean_request( 'logout=yes', [], TRUE ),
        ],
        'n7' => [
            'link_id'   => 'n7',
            'link_name' => '---',
        ],
        'n8' => [
            'link_id'         => 'n8',
            'link_content'    => '<div class="theme-options">
								<span>Mode:</span>
								<button type="button" class="btn btn-theme-options nav-link text-primary-hover mb-0 active" data-bs-theme-value="light" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Light">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun fa-fw mode-switch" viewBox="0 0 16 16">
										<path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"></path>
										<use href="#"></use>
									</svg>
								</button>
								<button type="button" class="btn btn-theme-options nav-link text-primary-hover mb-0" data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Dark">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars fa-fw mode-switch" viewBox="0 0 16 16">
										<path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"></path>
										<path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"></path>
										<use href="#"></use>
									</svg>
								</button>								
							</div>'
        ]
    ];



}