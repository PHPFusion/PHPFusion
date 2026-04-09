<?php
namespace Wallet;

/**
 * Class Product
 * A class that interacts with the Wallet Item/Product Register
 * @package Wallet
 */
class Product {

    private static $product = array();

    /**
     * API to update product. Just pass the array in and run
     * @param $item - requires item_type and item_ref_id specified - comment_item_id, and comment_item_type
     * @throws \Exception
     */
    public static function update_product($item) {
        // Tax rates follow the system settings
        $default_info = [
            'item_id' => 0,
            'item_ref_id' => 0,
            'item_title' => '',
            'item_type' => '',
            'item_tangible' => '',
            'item_quantity' => '',
            'item_price' => 0,
            'item_tax_rate' => Model::walletSettings('wallet_tax_rate'),
            'item_tax' => 0,
            'item_shipping' => 0,
            'item_datestamp' => TIME,
            'item_user' => '',
            'item_status' => 1,
        ];
        if (!empty($item['item_type']) && !empty($item['item_ref_id']) && isnum($item['item_ref_id'])) {
            $item = \Defender::sanitize_array($item);
            $result = dbquery("SELECT * FROM ".DB_WALLET_ITEMS." WHERE item_type='".$item['item_type']."' AND item_ref_id='".$item['item_ref_id']."'");
            if (dbrows($result)>0) {
                $data = dbarray($result);
                $item += $data;
                dbquery_insert(DB_WALLET_ITEMS, $item, 'update', ['keep_session' => TRUE]);
                self::$product[$item['item_id']] = $item;
            } else {
                $item += $default_info;
                dbquery_insert(DB_WALLET_ITEMS, $item, 'save', ['keep_session' => TRUE]);
                self::$product[$item['item_id']] = $item;
            }
        } else {
            throw new \Exception('Invalid Product ID');
        }
    }

    /**
     * Given a specific product ID, get all data
     * @param $item - item array
     * @return array
     */
    public function get_product($item){
        if (empty(self::$product[$item['item_id']])) {
            if (isset($item['item_id']) && isnum($item['item_id'])) {
                $item = \Defender::sanitize_array($item);
                $result = dbquery("SELECT * FROM ".DB_WALLET_ITEMS." WHERE item_id='".$item['item_id']."'");
                if (dbrows($result) > 0) {
                    self::$product[$item['item_id']] = dbarray($result);
                }
            } elseif (!empty($item['item_type']) && !empty($item['item_ref_id']) && isnum($item['item_ref_id'])) {
                $item = \Defender::sanitize_array($item);
                $result = dbquery("SELECT * FROM ".DB_WALLET_ITEMS." WHERE item_type='".$item['item_type']."' AND item_ref_id='".$item['item_ref_id']."'");
                if (dbrows($result) > 0) {
                    self::$product[$item['item_id']] = dbarray($result);
                }
            }
        }
        return (!empty(self::$product[$item['item_id']])) ? self::$product[$item['item_id']] : array();
    }


}