<?php
namespace PHPFusion;

class Profile
{
	private $info = [];
	
	public function setSections()
	{
		$this->info['section'] = [
			'account'      => 'My Account',
			'notification' => 'My Notifications',
			'apps'         => 'Connected Apps',
			'plans'        => 'Plans',
			'billing'      => 'Billing & Invoices',
		];
		
	}
	
	
	public function viewForm()
	{
		$this->setSections();
		
		display_profile_form($this->info);
	}
	
}