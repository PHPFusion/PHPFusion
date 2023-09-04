<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: LegalDocs.php
| Author: meangczac (Chan)
| PHPFusion Lead Developer, PHPFusion Core Developer
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

/**
 * Class LegalDocs
 *
 * @package PHPFusion
 */
class LegalDocs {

    /**
     * @var false|mixed
     */
    private static $data;

    private static $policies = [
        'ups' => ['PHPFusion\\LegalDocs', 'policyUser'],
        'pps' => ['PHPFusion\\LegalDocs', 'policyPrivacy'],
        'cps' => ['PHPFusion\\LegalDocs', 'policyCookie'],
        'cms' => ['PHPFusion\\LegalDocs', 'policyCommunity'],
        'ccs' => ['PHPFusion\\LegalDocs', 'policyCopyright'],
    ];

    private static $instance;


    public function __construct() {
    }

    public static function getInstance() {
        if (self::$instance === NULL) {

            self::$instance = new static();

            if ($policy_get = get( 'type' )) {

                if ($policy_registrations = fusion_filter_hook( 'fusion_policies' )) {

                    foreach ($policy_registrations as $policy_registers) {
                        self::$policies += $policy_registers;
                    }
                }

                if (isset( self::$policies[$policy_get] )) {
                    /**
                     * @uses policy_user()
                     */
                    self::$data = call_user_func( self::$policies[$policy_get] );
                }
            }
        }

        return self::$instance;
    }

    /**
     * @return array
     */
    public function getPolicies( $return_num ) {

        $_policies_cnt = count( self::$policies );
        if ($return_num > $_policies_cnt) {
            $return_num = $_policies_cnt;
        }
        $_policies = array_chunk( self::$policies, $return_num );
        $_policies = $_policies[0];
        foreach ($_policies as $key => $callback_func) {
            $data = call_user_func( $callback_func );
            if (isset( $data['name'] )) {
                $policies_cache[$key] = $data['name'];
            }
        }

        return $policies_cache ?? [];
    }


    /**
     * void
     */
    public function view() {

        require_once THEMES . 'templates/global/legaldocs.tpl.php';

        if (!empty( self::$data )) {

            add_to_title( self::$data['title'] );
            set_meta( self::$data['meta'] );
            display_legal_docs( self::$data['title'], self::$data['date'], self::$data['content'] );

        } else {
            // no content
            redirect( BASEDIR . 'legal.php?type=ups' );
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function getDate( $value ) {
        $locale = fusion_get_locale();
        return sprintf( $locale['pol_101'], showdate( 'newsdate', $value ) );
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function getContent( $value ) {
        $settings = fusion_get_settings();

        $value = parse_text( $value, ['parse_smileys' => FALSE, 'add_line_breaks' => TRUE] );

        return strtr( $value, [
            '[SITENAME]'  => $settings['sitename'],
            '[CONTACT]'   => $settings['siteemail'],
            '[SITEEMAIL]' => $settings['siteemail'],
        ] );
    }

    /**
     * User Agreement
     *
     * @return array
     */
    private static function policyUser() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $result = dbquery( "SELECT * FROM " . DB_TOS . " WHERE policy_name=:name AND policy_language=:lang", [':name' => $locale['pol_200'], ':lang' => LANGUAGE] );

        if (dbrows( $result )) {

            $rows = dbarray( $result );

            return [
                'title'   => $locale['pol_201'] . ' ' . $settings['sitename'],
                'meta'    => $settings['sitename'] . ' ' . $locale['pol_200'],
                'name'    => $locale['pol_200'],
                'date'    => LegalDocs::getDate( $rows['policy_date'] ),
                'content' => LegalDocs::getContent( $rows['policy_content'] )
            ];
        }

        return [];
    }


    /**
     * User Agreement
     *
     * @return array
     */
    private static function policyPrivacy() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $result = dbquery( "SELECT * FROM " . DB_TOS . " WHERE policy_name=:name AND policy_language=:lang", [':name' => $locale['pol_300'], ':lang' => LANGUAGE] );

        if (dbrows( $result )) {

            $rows = dbarray( $result );

            return [
                'title'   => $locale['pol_301'] . ' ' . $settings['sitename'],
                'meta'    => $settings['sitename'] . ' ' . $locale['pol_300'],
                'name'    => $locale['pol_300'],
                'date'    => LegalDocs::getDate( $rows['policy_date'] ),
                'content' => LegalDocs::getContent( $rows['policy_content'] )
            ];
        }

        return [];
    }


    /**
     * User Agreement
     *
     * @return array
     */
    private static function policyCookie() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $result = dbquery( "SELECT * FROM " . DB_TOS . " WHERE policy_name=:name AND policy_language=:lang", [':name' => $locale['pol_400'], ':lang' => LANGUAGE] );

        if (dbrows( $result )) {

            $rows = dbarray( $result );

            return [
                'title'   => $locale['pol_401'] . ' ' . $settings['sitename'],
                'meta'    => $settings['sitename'] . ' ' . $locale['pol_400'],
                'name'    => $locale['pol_400'],
                'date'    => LegalDocs::getDate( $rows['policy_date'] ),
                'content' => LegalDocs::getContent( $rows['policy_content'] )
            ];
        }

        return [];
    }

    /**
     * Professional Community Policy
     *
     * @return array
     */
    private static function policyCommunity() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $result = dbquery( "SELECT * FROM " . DB_TOS . " WHERE policy_name=:name AND policy_language=:lang", [':name' => $locale['pol_500'], ':lang' => LANGUAGE] );

        if (dbrows( $result )) {

            $rows = dbarray( $result );

            return [
                'title'   => $locale['pol_501'] . ' ' . $settings['sitename'],
                'meta'    => $settings['sitename'] . ' ' . $locale['pol_500'],
                'name'    => $locale['pol_500'],
                'date'    => LegalDocs::getDate( $rows['policy_date'] ),
                'content' => LegalDocs::getContent( $rows['policy_content'] )
            ];
        }

        return [];
    }

    /**
     * Cookie policy
     *
     * @return array
     */
    private static function policyCopyright() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $result = dbquery( "SELECT * FROM " . DB_TOS . " WHERE policy_name=:name AND policy_language=:lang", [':name' => $locale['pol_600'], ':lang' => LANGUAGE] );

        if (dbrows( $result )) {

            $rows = dbarray( $result );

            return [
                'title'   => $locale['pol_601'] . ' ' . $settings['sitename'],
                'meta'    => $settings['sitename'] . ' ' . $locale['pol_600'],
                'name'    => $locale['pol_600'],
                'date'    => LegalDocs::getDate( $rows['policy_date'] ),
                'content' => LegalDocs::getContent( $rows['policy_content'] )
            ];
        }

        return [];
    }


}