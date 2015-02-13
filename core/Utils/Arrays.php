<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */

namespace core\Utils;

class Arrays {
    /**
     * Compares if an array contains a serie (array) of keys.
     * 
     * @param array $keys Keys to check if are contained by the array
     * @param array $arr Array that should containt the given keys
     * @return boolean TRUE if all the keys are in the array and FALSE if some key is missing
     */
    public static function keysExists(array $keys, array $arr, $allowEmpty = true) {
        foreach($keys as $value) {
            if(!array_key_exists($value, $arr)) {
                return false;
            } else if($allowEmpty == false && empty($arr[$value])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Join array keys with a string.
     * 
     * @param type $glue Array keys glue.
     * @param array $arr The array to implode
     * @return string Returns a string containing a string 
     *                representation of all the array keys 
     *                in the same order, with the glue string between each element. 
     */
    public static function implodeKeys($glue, array $arr) {
        return implode($glue, array_keys($arr));
    }

    /**
     * Add prefix to all array values.
     * 
     * @param array $data The array to which elements the prefix will be inserted
     * @param string $prefix The prefix
     * @return array
     */
    public static function addPrefix(array $data, $prefix) {
        return array_map(function($value) use($prefix) {
            return $prefix . $value;
        },$data);
    }
}
