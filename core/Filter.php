<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */

namespace core;

class Filter {
    /**
     * Applys htmlspecialchars and addslshed to the given parameter.
     * If the parameter is an array walks throw it and apply the changes
     * to all elements.
     * 
     * @param mixed $data Variable that must be "filtered" by using htmlspecialchars and addslashes
     * @return mixed
     */
    public static function htmlChars(&$data) {
        if(is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::htmlChars($value);
            }
        } else {
            $data = htmlspecialchars(addslashes($data));
        }   
        
        return $data;
    }
    
    /**
     * Casts the given parameter to integer.
     * If array is passed walk the array and cast all
     * the elements recursive.
     * 
     * @param mixed $data Variable to be cast to int
     * @author Velik Georgiev Chelebiev <bulter2005@gmail.com>
     * @return int|array
     */
    public static function castNumber(&$data) {
        if(is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::castNumber($value);
            }
        } else {
            $data = (int)$data;
        }   
        
        return $data;
    }
}

?>