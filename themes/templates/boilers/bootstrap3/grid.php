<?php

/**
 * Class Grid
 */
class Grid {

    /**
     * @return string
     */
    public static function getRowClass() {
        return "row";
    }

    /**
     * @param $percent - 1-100
     *
     * @return string
     */
    public static function getColumnClass($percent) {
        if (is_array($percent)) {
            $default_options = [100,100,100,100];
            $percent += $default_options;
            $arr = ['xs','sm', 'md', 'lg'];
            $val = [];
            foreach($percent as $index => $value) {
                $val[$arr[$index]] =  floor( ($value * 12 ) / 100 );
            }
            return (string) implode(' ', array_map(function($i, $e) { return "col-$i-$e"; }, array_keys($val), array_values($val)));
        } else {
            return (string)"col-xs='".floor( ($percent * 12 ) / 100)."'";
        }
    }


}