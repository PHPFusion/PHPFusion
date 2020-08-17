<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: lollipop.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Lollipop Captcha
 *
 * Class Lollipop
 */
class Lollipop {

    /**
     * Default number of options
     *
     * @var int
     */
    private $list_num = 9;

    /**
     * Current captcha choice
     *
     * @var int
     */
    private $current_choice = 0;

    /**
     * Current captcha instance embedded form name
     *
     * @var string
     */
    private $form_name = '';
    private $session_opts_id = '';
    private $session_id = '';


    /**
     * Lollipop constructor.
     *
     * @param $form_name
     */
    public function __construct($form_name) {
        $this->form_name = $form_name;
        $this->session_opts_id = 'lollipop_opts-'.$form_name;
        $this->session_id = 'lollipop-'.$form_name;
        $this->current_choice = $this->setSession();

    }

    private function setSession() {
        if (empty(session_get($this->session_id))) {
            return session_add($this->session_id, $this->randChoice());
        }

        return session_get($this->session_id);
    }

    /**
     * Generate random choice
     *
     * @return int
     */
    public function randChoice() {
        return rand(1, 4);
    }

    /**
     * Personal request, please avoid the questions that requires one to google or calculate for an answer.
     */
    public function getQuestions() {
        $q_arr = [
            1 => 'Check all options with single letter only',
            2 => 'Check all options with numbers only',
            3 => 'Check all options with words only',
            4 => 'Check all options that has a mix of both numbers and letters only',
        ];
        return (string)$q_arr[$this->current_choice];
    }

    /**
     * Validates Captcha
     *
     * @return bool
     * @throws Exception
     */
    public function validateCaptcha() {

        $values = sanitizer(['lollipop'], [], 'lollipop');

        if (!empty($values)) {
            $values = explode(',', $values);
        }

        $validate = NULL;

        switch ($this->current_choice) {
            case 1:
                $validate = $this->lengthCheck($values);
                break;
            case 2:
                $validate = $this->numCheck($values);
                break;
            case 3:
                $validate = $this->wordCheck($values);
                break;
            case 4:
                $validate = $this->mixedCheck($values);
                break;
        }

        if ($validate === TRUE) {
            session_remove($this->session_opts_id);
            session_remove($this->session_id);
        }

        return (boolean)$validate;
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    private function lengthCheck(array $value) {
        $session_options = session_get($this->session_opts_id);
        $session_arr = [];

        if (is_array($session_options)) {
            foreach ($session_options as $index => $val) {
                if (strlen($val) == 1 && !isnum($val)) {
                    $session_arr[] = $index;
                }
            }
        }

        if (empty(array_diff($session_arr, $value)) && empty(array_diff($value, $session_arr))) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Validate numbers
     *
     * @param array $value
     *
     * @return bool
     */
    private function numCheck(array $value) {
        $session_options = session_get($this->session_opts_id);
        $session_arr = [];

        if (is_array($session_options)) {
            foreach ($session_options as $index => $val) {
                if (isnum($val)) {
                    $session_arr[] = $index;
                }
            }
        }

        if (empty(array_diff($session_arr, $value)) && empty(array_diff($value, $session_arr))) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Validate words
     *
     * @param $value
     *
     * @return bool
     */
    private function wordCheck($value) {
        $session_options = session_get($this->session_opts_id);
        $session_arr = [];

        if (is_array($session_options)) {
            foreach ($session_options as $index => $val) {
                if (strlen($val) > 1 && !isnum($val)) {
                    $session_arr[] = $index;
                }
            }
        }

        if (empty(array_diff($session_arr, $value)) && empty(array_diff($value, $session_arr))) {
            return TRUE;
        }
        return FALSE;

    }

    /**
     * Validate mixed number of words
     *
     * @param $value
     *
     * @return bool
     */
    private function mixedCheck($value) {
        $session_options = session_get($this->session_opts_id);
        $session_arr = [];

        if (is_array($session_options)) {
            foreach ($session_options as $index => $session_val) {
                if (preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $session_val)) {
                    $session_arr[] = $index;
                }
            }
        }

        if (empty(array_diff($session_arr, $value)) && empty(array_diff($value, $session_arr))) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get captcha answers
     *
     * @return array
     */
    public function getAnswers() {
        // $session_options = $this->getSessionOptions();
        $session_options = session_get($this->session_opts_id);

        if (empty($session_options)) {
            $answers = [
                1 => $this->getLetters(),
                2 => $this->getNumbers(),
                3 => $this->getWords(),
                4 => $this->getBoth()
            ];
            $array = flatten_array($answers); //3 possible ones everytime. (Nope, better for impossible guess without rule - i.e. Mix them all)
            $array = $this->shuffle($array); //ones everytime with a twist
            $array = array_chunk($array, $this->list_num);
            return session_add($this->session_opts_id, array_combine(
                array_map(function ($key) {
                    return ++$key;
                }, array_keys($array[0])),
                $array[0]
            ));
        }

        return (array)session_get($this->session_opts_id);
    }

    /**
     * Shuffles an array
     *
     * @param $list
     *
     * @return array
     */
    private function shuffle($list) {
        if (!is_array($list))
            return $list;

        $keys = array_keys($list);
        shuffle($keys);
        $random = [];
        foreach ($keys as $key)
            $random[$key] = $list[$key];

        return array_values($random);
    }

    /**
     * Return array of random letter
     *
     * @return array
     */
    private function getLetters() {
        $letters = 'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
        $letter_arr = explode(',', $letters);
        $capital_letters = strtoupper($letters);
        $cap_letter_arr = explode(',', $capital_letters);
        $merged_letters = $this->shuffle(array_merge($letter_arr, $cap_letter_arr));
        $array = array_chunk($merged_letters, $this->list_num);
        return (array)$array[0];
    }

    /**
     * Return array of words
     *
     * @return array
     */
    private function getWords() {
        //1) Make a randomized output , maybe even a algo.. shuffling a lorem_ipsum is your 'algo'
        $lipsum = $this->shuffle(explode(' ', str_replace([',', '.'], [], lorem_ipsum(300))));
        $array = array_chunk($lipsum, $this->list_num);
        return (array)$array[0];
    }

    /**
     * Return array of numbers
     *
     * @return array
     */
    private function getNumbers() {
        $numbers = range(1, 300, 3);
        $numbers = array_chunk($numbers, $this->list_num);
        return (array)$numbers[0];
    }

    /**
     * Return array of mixed words and numbers
     *
     * @return array
     */
    private function getBoth() {
        $words_arr = $this->getWords();
        $numbers_arr = $this->getNumbers();
        $options = [];
        foreach ($words_arr as $index => $words) {
            $options[] = str_shuffle($words.$numbers_arr[$index]);
        }
        return (array)array_filter($options);
    }

}
