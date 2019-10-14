<?php
namespace PHPFusion\Form;

use PHPFusion\Interfaces\AdminFormSDK;

/**
 * Class FormFactory
 *
 * @package PHPFusion\Form
 */
class FormFactory {

    /**
     * @var null|AdminFormSDK
     */
    protected $api = NULL;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    public $form_properties = [
        'form_name' => 'input_form',
        'back_link' => '',
    ];

    /**
     * @var \Defender|null
     */
    public $defender = NULL;

    /**
     * @var array|mixed
     */
    public $field = [];

    /**
     * @var array
     */
    public $tags = [];

    /**
     * @var array
     */
    public $categories = [];

    /**
     * FormFactory constructor.
     *
     * @param $api
     */
    public function __construct(AdminFormSDK $api) {

        if ($api instanceof AdminFormSDK) {
            $this->api = $api;
            $this->defender = \Defender::getInstance();
            $this->form_properties = $this->getFormProperties();
            $this->data = $this->api->data();
            $this->field = $this->api->fields($this->data);
            $this->tags = $this->api->tags($this->data);
            $this->categories = $this->api->categories($this->data);
        } else {
            echo 'Your current object requires the implementation of the \PHPFusion\Interfaces\AdminFormSDK interface.';
        }
    }

    /**
     * Check and assigns keys for form_sanitizer for UI API assignments and keys during $_POST
     * @throws \Exception
     */
    public function process_request() {

        if (!empty($_POST)) {

            // process remove request.
            if (!$this->process_remove_action()) {
                foreach ($_POST as $keys => $val) {


                    if ($keys == 'startdate' || $keys == 'enddate') {
                        $keys = 'af_startdate';
                    } elseif ($keys == 'parent') {
                        $keys = 'ui_cat_parent';
                    }

                    $data_keys = $this->field_prop($keys, 'name', '');

                    $excluded_keys = ['ui_cat_parent', 'af_startdate', 'af_enddate'];

                    if (in_array($keys, ['form_id', 'fusion_token'])) {

                        $this->data[$keys] = $val;

                    } else if ($data_keys) {

                        $this->data[$data_keys] = form_sanitizer($val, '', $keys);

                    } elseif (!in_array($keys, $excluded_keys)) {

                        $this->data[$keys] = form_sanitizer($val, '', $keys);
                    }
                }

                $this->unsetAPIFields();
            }
        }

    }


    /**
     * @return bool
     * @throws \Exception
     */
    public function process_remove_action() {


        if (isset($_POST[$this->get_remove_prop('name')])) {

            $field_name = $this->field_prop('id', 'name');

            $field_value = $this->field_value('id');

            if (!empty($field_name) && !empty($field_value)) {

                $this->data[$this->field_prop('id', 'name')] = form_sanitizer($field_value, '0', 'id');

                $this->api->remove($this->data);
            }

            if (fusion_safe()) {

                $redirect_link = FUSION_REQUEST; // this is bound to have error in most use case

                if ($this->form_properties['back_link']) { // we must enforce a requirement for the trash button.

                    $redirect_link = $this->form_properties['back_link'];
                }

                redirect($redirect_link);
            }

        }
        return FALSE;
    }

    /**
     * Executes Save, Save & Close & Trash Action
     */
    public function process_form_actions() {

        // Save and Update
        if (!isset($_POST[$this->get_remove_prop('name')])) {

            $post_save = !empty($_POST[$this->get_save_prop('name')]) ? TRUE : FALSE;
            $post_save_close = FALSE;
            if ($post_save === FALSE) {
                $post_save_close = !empty($_POST[$this->get_save_close_prop('name')]) ? TRUE : FALSE;
            }
            //print_P($post_save);
            //print_P($post_save_close);

            if (($post_save or $post_save_close) && !empty($this->data)) {

                $id_field = $this->field_prop('id', 'name', '');

                if (!empty($id_field)) {

                    //@todo: Tags check must be required or not.
                    TagsMeta::getInstance($this)->process_tags();

                    //@todo: Categories check must be required or not.
                    $id_value = (!empty($this->data[$id_field])) ? $this->api->update($this->data) : $this->api->save($this->data);

                    // handle redirection
                    if (fusion_safe()) {
                        $redirect_uri = FUSION_REQUEST;
                        if ($post_save) {
                            redirect($redirect_uri);
                        } elseif ($post_save_close) {
                            if (!empty($this->form_properties['back_link'])) {
                                $redirect_uri = $this->form_properties['back_link'];
                            }
                            redirect($redirect_uri);
                        }
                    }

                } else {
                    addNotice('danger', 'Error. Primary id field has to be defined.');
                }
            }
        }

    }

    /**
     * Unset (Placeholder for now)
     */
    private function unsetAPIFields() {
        unset($_POST['af_status']);
        unset($this->data['af_status']);
        unset($_POST['af_visibility']);
        unset($this->data['af_visibility']);
        unset($_POST['af_password']);
        unset($this->data['af_password']);
        unset($_POST['af_startdate']);
        unset($this->data['af_startdate']);
        // tags
        unset($_POST['admin_ui_tags']);
        unset($this->data['admin_ui_tags']);
        // category
        unset($_POST['ui_cat_title']);
        unset($this->data['ui_cat_title']);
        unset($_POST['ui_cat_parent']);
        unset($this->data['ui_cat_parent']);
    }


    /**
     * @return array|mixed
     */
    public function get_field_config() {
        static $field_config;
        if (empty($field_config)) {
            $field_config = $this->field;
        }
        return $field_config;
    }


    /**
     * @return array
     */
    public function getFormProperties() {
        $prop = $this->api->properties(); // if this is null.
        if (!empty($prop) && is_array($prop)) {
            $this->form_properties = (array)$prop + (array)$this->form_properties;
        }
        return (array) $this->form_properties;
    }

    /**
     * Sanitizes the field on _POST or return default $data
     *
     * @param       $field_name
     * @param null  $key
     * @param array $default_output
     *
     * @return null
     */
    public function field_prop($field_name, $key = NULL, $default_output = []) {

        static $field;

        if (empty($field[$field_name])) {

            $field[$field_name]['label'] = '';
            $field[$field_name]['options'] = [
                'placeholder' => '',
                'required'    => TRUE,
                'type'        => 'text'
            ];

            $_fields = $this->get_field_config();

            if (!empty($_fields[$field_name])) {

                if (!empty($_fields[$field_name]['name'])) {
                    $field[$field_name]['name'] = $_fields[$field_name]['name'];
                }

                if (!empty($_fields[$field_name]['label'])) {
                    $field[$field_name]['label'] = $_fields[$field_name]['label'];
                }

                if (!empty($_fields[$field_name]['options'])) {
                    $field[$field_name]['options'] = $_fields[$field_name]['options'];
                }

                // Others such as value
                $field[$field_name] += $_fields[$field_name];

            }
        }

        return $key === NULL ? $field[$field_name] : (isset($field[$field_name][$key]) ? $field[$field_name][$key] : $default_output);

    }

    /**
     * Callback value of current field.
     * @param $field_name
     *
     * @return null|string
     */
    public function field_value($field_name) {

        $_field = $this->field;

        if ($field_name == 'tags') {
            //print_p($field_name);
        }

        if (isset($_field[$field_name]['name'])) {

            $_callback_ref = $_field[$field_name]['name'];

            if (isset($this->data[$_callback_ref])) {

                return (string)$this->data[$_callback_ref];

            }

        }

        return NULL;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    function get_remove_prop($key) {

        static $remove_prop;

        if (empty($remove_prop)) {

            $fields = $this->get_field_config();
            $remove_prop = [
                'name'    => 'remove_entry',
                'label'   => 'Trash',
                'value'   => 'remove_entry',
                'options' => [
                    'class' => 'text-danger'
                ]
            ];

            if (!empty($fields['remove'])) {
                $remove_prop = $fields['remove'] + $remove_prop;
            }

        }

        return $remove_prop[$key];

    }

    /**
     * @param $key
     *
     * @return mixed
     */
    function get_save_prop($key) {
        static $save_prop;

        if (empty($save_prop)) {

            $fields = $this->get_field_config();
            $save_options = [
                'name'    => 'save_entry',
                'label'   => 'Save',
                'value'   => 'save_entry',
                'options' => ['class' => 'btn-primary']
            ];

            if (!empty($fields['save'])) {
                $save_prop = $fields['save'] + $save_options;
            }

        }

        return $save_prop[$key];

    }

    /**
     * @param $key
     *
     * @return mixed
     */
    function get_save_close_prop($key) {
        static $saveClose_prop;

        if (empty($saveClose_prop)) {

            $fields = $this->get_field_config();
            $saveClose_prop = [
                'name'    => 'save_close_entry',
                'label'   => 'Save and Close',
                'value'   => 'save_close_entry',
                'options' => [
                    'class' => 'btn-primary'
                ]
            ];

            if (!empty($fields['save_close'])) {
                $saveClose_prop = $fields['save_close'] + $saveClose_prop;
            }

        }

        return $saveClose_prop[$key];
    }

}