<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumAlias.php
| Author: Frederick MC Chan (Chan)
| Co-Author: Chris Smith <code+php@chris.cs278.org>,
| Co-Author: Frank Bültge <frank@bueltge.de>
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Quantum;

/**
 * Class QuantumFactory
 *
 * Aliases function and instances
 *
 * @package PHPFusion\Quantum
 */
class QuantumFactory extends \SqlHandler {

    /**
     * @param array  $data
     * @param string $input_name
     *
     * @return array|bool|mixed|string|null
     */
    public static function fusionGetLocale($data, $input_name) {
        return QuantumHelper::fusionGetLocale($data, $input_name);
    }

    /**
     * Short serialization function.
     *
     * @param string $input_name
     *
     * @return bool|string
     */
    public static function serializeFields($input_name) {
        return QuantumHelper::serializeFields($input_name);
    }

    /**
     * Parse the correct label language. Requires to be serialized $value.
     *
     * @param string $value Serialized
     *
     * @return string
     * @deprecated use parseLabel()
     */
    public static function parse_label($value) {
        return self::parseLabel($value);
    }

    /**
     * Parse the correct label language. Requires to be serialized $value.
     *
     * @param string $value Serialized
     *
     * @return string
     * NOTE: If your field does not parse properly, check your column length. Set it to TEXT NOT NULL.
     */
    public static function parseLabel($value) {
        return QuantumHelper::parseLabel($value);
    }

    /**
     * Multiple locale fields input
     *
     * @param string $input_name
     * @param string $title
     * @param mixed  $input_value
     * @param array  $options
     *
     * @return string
     *
     * @deprecated use quantumMultilocaleFields()
     */
    public static function quantum_multilocale_fields($input_name, $title, $input_value, array $options = []) {
        return QuantumHelper::quantumMultilocaleFields($input_name, $title, $input_value, $options);
    }

    /**
     * @param string    $value
     * @param bool|null $result
     *
     * @return bool
     */
    public static function isSerialized($value, &$result = NULL) {
        return QuantumHelper::isSerialized($value, $result);
    }

    /**
     * Get the field title from filename
     *
     * @param string $filename
     *
     * @return string
     */
    public static function filenameToTitle($filename) {
        return QuantumHelper::filenameToTitle($filename);
    }

    public function invokeAdminInterfaceActions($page, $page_list) {

        (new QuantumFieldInterface($page, $page_list))->invokeListener();

        (new QuantumCategoryInterface($page, $page_list))->invokeListener();
    }

    /**
     * Array of available field types
     *
     * @return array
     */
    public function getDynamicsType() {
        return QuantumHelper::getDynamicsType();
    }

}
