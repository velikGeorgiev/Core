<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */

class AutoLoad { 
    /**
     * 
     * @param String $classNameSpace Classname
     * @throws Exception
     */
    public function load($classNameSpace) {
        /**
         * Set real file name and path
         */
        $classPath = str_replace("\\", "/", $classNameSpace);
        
        /**
         * Class name
         */
        $className = substr($classNameSpace, strrpos($classNameSpace, "\\") + 1);
        
        /**
         * Include the file supressing the error
         */
        if(!@include_once $classPath . ".php") {
            throw new Exception("Can't load $classPath");
        }

        /**
         * Check if the class is loaded
         */
        if (!class_exists($classNameSpace, false) && !interface_exists($classNameSpace, false)) {
            throw new Exception('Class ' . $classNameSpace . ' not found');
        }
    }
    
    /**
     * Register new auto load method
     */
    public function registerAutoLoad() {
        spl_autoload_register(array($this, "load"), true, true);
    }
}

$autoLoad = new AutoLoad();
$autoLoad->registerAutoLoad();

?>