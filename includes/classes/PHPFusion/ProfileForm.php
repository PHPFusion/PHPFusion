<?php

namespace PHPFusion;

use PHPFusion\Profile\Accounts;
use PHPFusion\Profile\Notifications;
use PHPFusion\Profile\Plans;
use PHPFusion\Profile\PublicProfile;

class ProfileForm
{
	private $info = [];

	public function setSections()
	{
		$this->info['section'] = [
			'profile'      => 'Public profile',
			'accounts'     => 'Account',
			'notification' => 'My Notifications',
			'apps'         => 'Connected Apps',
			'plans'        => 'Plans',
			'billing'      => 'Billing & Invoices',
		];

	}

	public function __construct()
	{
		$locale = fusion_get_locale();
		if (!iMEMBER) {
			redirect("index.php");
		}

		$this->info['current'] = get('page');
		if (!get('page')) {
			$this->info['current'] = 'profile';
		}

		add_to_title($locale['u102']);
	}

	public function viewForm()
	{
		$this->setSections();
//		$this->setUserdata();
		$this->setContent();

		display_profile_form($this->info);
	}

	public function setContent()
	{
		// get the content
		ob_start();
		echo match ($this->info['current']) {
			'accounts' => $this->accounts(),
			'profile' => $this->publicProfile(),
            'plans' => $this->plans(),
            'notification' => $this->notification(),
			default => $this->publicProfile()
		};

		$this->info['content'] = ob_get_clean();
	}

	private function accounts()
	{
		$html = new Accounts();
		$html->__view();
	}

	private function publicProfile()
	{
		$html = new PublicProfile();
		$html->__view();

	}

	private function plans() {
	    $html = new Plans();
	    $html->__view();
    }

    private function notification() {
	    $html = new Notifications();
	    $html->__view();
    }

}

require_once THEMES."templates/global/profile_edit.tpl.php";
