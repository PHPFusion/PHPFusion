Task to do:

1. Register all the update and saves into routes.php, this is the main RESTful endpoints.
2. Centralize all endpoints and execute inline scripts with the same endpoints as well.

For example: 
in ``settings_main.php`` - we can handle update like this:
````php
if (check_post('save_settings')) :
	$service = new SettingsService();
	$status = $service->updateMainSettings([
		'sitename' => 'You can change value like this'
	]);
endif;
````

3. Work the pf_post function structure. Each section should have its own updater.
4. Add index for admin global search - admin spotlight (**In Development**)