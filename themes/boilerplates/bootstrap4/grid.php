<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: grid.php
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
     * @param      $percent
     * @param bool $is_offset
     *
     * @return string
     */
    public static function getColumnClass($percent, $is_offset = FALSE) {
        if (is_array($percent)) {

            $default_options = [100];
            $percent += $default_options;

            $arr = ['xs', 'sm', 'md', 'lg'];
            $val = [];
            $offset = [];
            if ($is_offset) {
                $offset = [TRUE, TRUE, TRUE, TRUE];
            }

            foreach ($percent as $index => $value) {

                $calculated = ($value * 12) / 100;
                if ($calculated < 5) {
                    $calculated = ceil($calculated);
                } else {
                    $calculated = floor($calculated);
                }

                if (!$calculated) {
                    if ($is_offset === FALSE) {
                        $calculated = 'hidden';
                    }
                }

                $val[$arr[$index]] = $calculated;
            }

            return (string)ltrim(implode(' ', array_map(function ($i, $e, $offset) {
                if ($e == 'hidden') {
                    if ($offset === FALSE) {
                        return "hidden-$i";
                    }
                }

                $offset_prefix = '';
                if ($offset === TRUE) {
                    $offset_prefix = 'offset-';
                }
                return $e ? "col-$i-".$offset_prefix.$e : '';

            }, array_keys($val), array_values($val), $offset)));

        } else {
            $offset_prefix = '';
            if ($is_offset === TRUE) {
                $offset_prefix = 'offset-';
            }

            return (string)'col-xs-'.$offset_prefix.floor(($percent * 12) / 100);
        }
    }

    public static function getColumnOffsetClass($percent) {
        if (is_array($percent)) {
            $default_options = [100];
            $percent += $default_options;
            $arr = ['xs', 'sm', 'md', 'lg'];
            $val = [];

            foreach ($percent as $index => $value) {
                $calculated = ($value * 12) / 100;
                if ($calculated < 5) {
                    $calculated = ceil($calculated);
                } else {
                    $calculated = floor($calculated);
                }

                if (!$calculated) {
                    $calculated = 'hidden';
                }
                $val[$arr[$index]] = $calculated;
            }

            return (string)implode(' ', array_map(function ($i, $e) {
                if ($e == 'hidden') {
                    return "hidden-$i";
                }
                return "col-$i-$e";
            }, array_keys($val), array_values($val)));
        } else {
            return (string)"col-xs='".floor(($percent * 12) / 100)."'";
        }
    }
}
