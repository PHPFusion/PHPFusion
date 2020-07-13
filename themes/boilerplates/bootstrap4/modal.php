<?php

/**
 * Class Modal
 */
class Modal {
    /**
     * @param $id
     * @param $title
     * @param $options
     *
     * @return string
     */
    public function openmodal($id, $title, $options) {

        $default_options = array(
            'class'        => '',
            'class_dialog' => '',
            'button_id'    => '',
            'button_class' => '',
            'static'       => FALSE,
            'dismissable'  => TRUE, // set default dismissable on modal header as we might not even have a footer
            'hidden'       => FALSE,
            'animate'      => FALSE,
        );

        $options += array('openmodal' => TRUE, 'id' => $id, 'title' => strip_tags($title)) + $default_options;

        if (in_array($options['class'], ['modal-sm', 'modal-md', 'modal-lg'])) {
            add_notice('warning', 'Modal sizing should be set to class_dialog properties');
        }

        $this->addJquery($options);
        return fusion_render(__DIR__.'/html/', 'modal.twig', $options);
    }

    /**
     * @param string $content
     * @param bool   $dismiss
     *
     * @return string
     */
    public function modalfooter($content = '', $dismiss = FALSE) {
        $options = [
            'modalfooter' => TRUE,
            'content'     => $content,
            'dismissable' => $dismiss,
        ];
        return fusion_render(__DIR__.'/html/', 'modal.twig', $options);
    }

    /**
     * @return string
     */
    public function closemodal() {
        return fusion_render(__DIR__.'/html/', 'modal.twig', array('closemodal' => TRUE));
    }

    /**
     * @param $options
     */
    private function addJquery($options) {
        /** Handles the default modal state */
        if (!$options['hidden']) {
            /** If external modal callback button exists */
            if (!empty($options['button_id']) || !empty($options['button_class'])) {
                $modal_trigger = !empty($options['button_id']) ? "#".$options['button_id'] : ".".$options['button_class'];
            }
            if (isset($modal_trigger)) {
                /** Always show the modal on static set to true */
                if ($options['static'] === TRUE) {
                    $jquery = /** @lang JavaScript */
                        "$('$modal_trigger').bind('click', function(e){ $('#".$options['id']."-Modal').modal({backdrop: 'static', keyboard: false}).modal('show'); e.preventDefault(); });";

                } else {
                    /** registers external trigger click event */
                    $jquery = /** @lang JavaScript */
                        "$('$modal_trigger').bind('click', function(e){
                            e.preventDefault();
                            $('#".$options['id']."-Modal').modal('show');
                        });";
                }

            } else {
                /** always show modal on static to true */
                if ($options['static'] === TRUE) {
                    $jquery = /** @lang JavaScript */
                        "$('#".$options['id']."-Modal').modal({backdrop: 'static',	keyboard: false }).modal('show');";
                } else {
                    /** Show onload */
                    $jquery = /** @lang JavaScript */
                        "$('#".$options['id']."-Modal').modal('show');";
                }
            }
            add_to_jquery($jquery);
        }
    }

}
