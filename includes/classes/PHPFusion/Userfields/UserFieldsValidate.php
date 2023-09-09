<?php

namespace PHPFusion\Userfields;

use PHPFusion\UserFieldsInput;

abstract class UserFieldsValidate {

    protected UserFieldsInput $userFieldsInput;

    /**
     * UserFieldsValidate constructor.
     *
     * @param UserFieldsForm $userFieldsInput
     */
    public function __construct( UserFieldsInput $userFieldsInput ) {
        $this->userFieldsInput = $userFieldsInput;
    }


}