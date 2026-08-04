<?php

namespace PHPFusion;

class SettingsForm
{

    private static $instance;

    private $options = [];

	private $default_parameters
		= [
			'access'      => '',
			'title'       => '',
			'description' => '',
			'callback'    => '',
			'content'     => '',
			'breadcrumb'  => ['url' => '', 'title' => ''],
			'tabs'        => [
				'instance' => '',
				'title'    => [],
				'id'       => [],
				'content'  => [],
				'icon'     => [],
				'active'   => 0,
				'remember' => TRUE,
			],

		];

    private $rows;

    public function __construct()
	{


	}

	public static function getInstance() {
	    if (self::$instance == NULL) {
	        self::$instance = new static();
        }
	    return self::$instance;
    }

    public function addTitle($value) {
	    $this->options['title'] .= $value;
    }

    public function setTitle($value) {
	    $this->options['title'] = $value;
    }

    public function setRows($rows) {
	    $this->rows = $rows;
    }

    public function setGlobals(array $parameters) {
        $this->options += $parameters + $this->default_parameters;

        if ($this->options['access']) {
            pageaccess($this->options['access']);
        }

        if ($this->options['callback']) {
            foreach ($this->options['callback'] as $callback) {
                $parts = explode('::', $callback);

                if (count($parts) === 2) {
                    $className = "PHPFusion\\Administration\\Controllers\\" . $parts[0];
                    $methodName = $parts[1];

                    // 2. Verify the class exists
                    if (class_exists($className)) {
                        // 3. Instantiate the object (this allows $this to work inside the method)
                        $controller = new $className();
                        // 4. Verify the method exists on that specific object
                        if (method_exists($controller, $methodName)) {
                            // 5. Call the method using the object instance
                            echo call_user_func([$controller, $methodName]);
                        } else {
                            trigger_error("Method $methodName does not exist in $className", E_USER_WARNING);
                        }
                    } else {
                        trigger_error("Class $className not found", E_USER_WARNING);
                    }
                }
            }

        }
    }

	public function render()
	{
		if ($this->options['title']) {
			opentable($this->options['title'] .
				($this->options['description'] ? "<div class='" . framework_css('small text-muted') . "'>" . $this->options['description'] . "</div>" : ""));

			if ($this->options['breadcrumb']) {
				add_breadcrumb(['link' => $this->options['breadcrumb']['link'], 'title' => $this->options['breadcrumb']['title']]);
			}
		}

		if ($this->options['tabs'] && !empty($this->options['tabs']['id'])) {

			$tab_active = tab_active($this->options['tabs'], $this->options['tabs']['active']);

			$tab_instance = $this->options['tabs']['instance'] ?? random_string(10);

			echo opentab($this->options['tabs'], $tab_active, $tab_instance, remember: $this->options['tabs']['remember']);

			for ($i = 0; count($this->options['tabs']['id']) > $i; $i++) {

				echo opentabbody($tab_instance, $this->options['tabs']['id'][$i], $tab_active);

				if (!empty($this->options['tabs']['content'][$i])) {
					// 1. Split the class name and method name
					// Example: "SettingsPage::generalForm" becomes ['SettingsPage', 'generalForm']
					$parts = explode('::', $this->options['tabs']['content'][$i]);

					if (count($parts) === 2) {
						$className = "PHPFusion\\Administration\\Controllers\\" . $parts[0];
						$methodName = $parts[1];

						// 2. Verify the class exists
						if (class_exists($className)) {
							// 3. Instantiate the object (this allows $this to work inside the method)
							$controller = new $className();

							// 4. Verify the method exists on that specific object
							if (method_exists($controller, $methodName)) {
								// 5. Call the method using the object instance
								echo call_user_func([$controller, $methodName]);
							} else {
								trigger_error("Method $methodName does not exist in $className", E_USER_WARNING);
							}
						} else {
							trigger_error("Class $className not found", E_USER_WARNING);
						}
					}
				}
				echo closetabbody();
			}

			echo closetab();
		}

		if ($this->options['content']) {
			$parts = explode('::', $this->options['content']);

			if (count($parts) === 2) {
				$className = "PHPFusion\\Administration\\Controllers\\" . $parts[0];
				$methodName = $parts[1];

				// 2. Verify the class exists
				if (class_exists($className)) {
					// 3. Instantiate the object (this allows $this to work inside the method)
					$controller = new $className();

					// 4. Verify the method exists on that specific object
					if (method_exists($controller, $methodName)) {
						// 5. Call the method using the object instance
						echo call_user_func([$controller, $methodName]);

					} else {
						trigger_error("Method $methodName does not exist in $className", E_USER_WARNING);
					}
				} else {
					trigger_error("Class $className not found", E_USER_WARNING);
				}
			}
		}

		if ($this->options['title']) {
			closetable();
		}
	}

}

