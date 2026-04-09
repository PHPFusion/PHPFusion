<?php
function admin_login()
{

//	return '<div class="pf-admin">
//		<div class="pf-login-viewport">
//			<div class="login">
//				<div>
//					<img alt="{%__SITENAME__%}" class="icon" src="{%__SITEBANNER__%}">
//					<h1>Sign in to {%__SITENAME__%}</h1>
//					{%__OPENFORM__%}
//					{%__PASSWORD__%}
//					{%__BUTTON__%}
//					{%__CLOSEFORM__%}
//				</div>
//			</div>
//		</div>
//	</div>';
	
	
	return '<div class="container-fluid vh-100 p-0 overflow-hidden" data-bs-theme="dark">
  <div class="row g-0 h-100">
    
    <div class="col-12 col-lg-6 d-flex flex-column" style="background:#0D1117;">
      
      <div class="container mt-4 mb-auto" style="max-width: 448px;">
        <a href="'.BASEDIR.'index.php" class="text-decoration-none text-secondary small d-inline-flex align-items-center">
          <svg class="me-1" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
            <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
          Back to dashboard
        </a>
      </div>

      <div class="container my-auto py-5" style="max-width: 448px;">
        <div class="mb-4">
          <h1 class="h3 fw-semibold text-white mb-2">Sign In</h1>
          <p class="text-secondary small">Enter your administrators password to sign in</p>
        </div>
		{%__OPENFORM__%}
          <div class="mb-3">
            {%__PASSWORD_INPUT__%}
          </div>
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="keepLoggedIn">
              <label class="form-check-label small text-secondary" for="keepLoggedIn">Keep me logged in</label>
            </div>
            <a href="/reset-password.html" class="text-primary text-decoration-none small">Forgot password?</a>
          </div>
          {%__BUTTON__%}
        {%__CLOSEFORM__%}
      </div>
    </div>
    <div class="col-lg-6 d-none d-lg-flex flex-column align-items-center justify-content-center position-relative" style="background:#141A22;">
      <div class="z-1 text-center">
        <img src="'.IMAGES.'phpfusion-logo.svg" alt="{%__SITENAME__%}" class="mb-3 w-50">
        <p class="text-secondary small px-5">Free and Open-Source Content Management System</p>
      </div>
    </div>
  </div>
</div>';

}
