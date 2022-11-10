<?php
namespace App\Classes;


class DataHelper {

    public static function multiKeyExists(array $arr, $key) : bool {

        if (array_key_exists($key, $arr)) {
            return true;
        }

        foreach ($arr as $element) {
            if (is_array($element)) {
                if (self::multiKeyExists($element, $key)) {
                    return true;
                }
            }

        }

        return false;
    }

}
