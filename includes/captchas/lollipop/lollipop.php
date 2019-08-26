<?php

/**
 * Lollipop Captcha
 *
 * Class Lollipop
 */
class Lollipop {

    private $list_num = 9; // For example 9 inputs options,

    private $current_choice = 0;

    private $form_name = '';

    public function randChoice() {
        return rand(1, 4);
    }

    public function __construct($form_name) {
        //unset($_SESSION['lollipop']);
        $_SESSION['lollipop'][$form_name] = !empty($_SESSION['lollipop'][$form_name]) ? $_SESSION['lollipop'][$form_name] : $this->randChoice();
        $this->current_choice = $_SESSION['lollipop'][$form_name];
        $this->form_name = $form_name;
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
     */
    /**
     * @return bool
     * @throws Exception
     */
    public function validateCaptcha() {
        $value = sanitizer(['lollipop'], [], 'lollipop');
        if (!empty($value)) {
            $value = explode(',', $value);

            $a_arr = [
                1 => 'lengthCheck',
                2 => 'numCheck',
                3 => 'wordCheck',
                4 => 'mixedCheck'
            ];
            $method = $a_arr[$this->current_choice];
            $validate = $this->$method($value);

            if ($validate === TRUE) {
                unset($_SESSION['lollipop_options']);
                unset($_SESSION['lollipop']);
            }

            return (boolean)$validate;
        }

        return NULL;
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    private function lengthCheck(array $value) {
        $session_options = $_SESSION['lollipop_options'][$this->form_name];
        $session_arr = [];
        foreach ($session_options as $index => $val) {
            if (strlen($val) == 1 && !isnum($val)) {
                $session_arr[] = $index;
            }
        }

        if (empty(array_diff($session_arr, $value))) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    private function numCheck(array $value) {
        $session_options = $_SESSION['lollipop_options'][$this->form_name];
        $session_arr = [];
        foreach ($session_options as $index => $val) {
            if (isnum($val)) {
                $session_arr[] = $index;
            }
        }
        if (empty(array_diff($session_arr, $value))) {
            return TRUE;
        }
        return FALSE;

    }

    private function wordCheck($value) {
        $session_options = $_SESSION['lollipop_options'][$this->form_name];
        $session_arr = [];
        foreach ($session_options as $index => $val) {
            if (strlen($val) > 1 && !isnum($val)) {
                $session_arr[] = $index;
            }
        }
        if (empty(array_diff($session_arr, $value))) {
            return TRUE;
        }
        return FALSE;

    }

    private function mixedCheck($value) {
        $session_options = $_SESSION['lollipop_options'][$this->form_name];
        $session_arr = [];
        foreach ($session_options as $index => $session_val) {
            if (preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $session_val)) {
                $session_arr[] = $index;
            }
        }
        if (empty(array_diff($session_arr,$value))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getAnswers() {

        if (empty($_SESSION['lollipop_options'][$this->form_name])) {

            $answers = [
                1 => $this->getLetters(),
                2 => $this->getNumbers(),
                3 => $this->getWords(),
                4 => $this->getBoth()
            ];
            $array = flatten_array($answers); //3 possible ones everytime. (Nope, better for impossible guess without rule - i.e. Mix them all)
            $array = $this->shuffle($array); //ones everytime with a twist
            $array = array_chunk($array, $this->list_num);
            $_SESSION['lollipop_options'][$this->form_name] = array_combine(
                array_map(function ($key) {
                    return ++$key;
                }, array_keys($array[0])),
                $array[0]
            );

        }

        return (array)$_SESSION['lollipop_options'][$this->form_name];
    }

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
     * Generate random letter
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

    private function getWords() {
        //1) Make a randomized output , maybe even a algo.. shuffling a lorem_ipsum is your 'algo'
        $lipsum = $this->shuffle(explode(' ', str_replace([',', '.'], [], lorem_ipsum(300))));
        $array = array_chunk($lipsum, $this->list_num);
        return $array[0];
    }

    private function getNumbers() {
        $numbers = range(1, 300, 3);
        $numbers = array_chunk($numbers, $this->list_num);
        return $numbers[0];
    }

    private function getBoth() {
        $words_arr = $this->getWords();
        $numbers_arr = $this->getNumbers();
        $options = [];
        foreach ($words_arr as $index => $words) {
            $options[] = str_shuffle($words.$numbers_arr[$index]);
        }
        return array_filter($options);
    }

}