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
	public static function getContainerClass() {
		return "container";
	}

    /**
     * @param $percent - 1-100
     *
     * @return string
     */
    public static function getColumnClass($percent) {
        if (is_array($percent)) {
            $default_options = [100];
            $percent += $default_options;
            $arr = ['xs','sm', 'md', 'lg'];
            $val = [];

            foreach($percent as $index => $value) {
                $calculated = ($value * 12 ) / 100;
                if ($calculated < 5) {
                    $calculated = ceil(  $calculated );
                } else {
                    $calculated = floor( $calculated );
                }

                if (!$calculated) {
                    $calculated = 'hidden';
                }
                $val[$arr[$index]] =  $calculated;
            }

            return (string) implode(' ', array_map(function($i, $e) {
                if ($e == 'hidden') {
                    return "hidden-$i";
                }
                return "col-$i-$e";
                }, array_keys($val), array_values($val)));
        } else {
            return (string)"col-xs='".floor( ($percent * 12 ) / 100)."'";
        }
    }


}