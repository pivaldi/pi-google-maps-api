<?php

if (isset($_GET['source'])) {
    highlight_file(__FILE__);
    die;
}

/*
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 *  @authors            CERDAN Yohann <cerdanyohann@yahoo.fr>, Philippe Ivaldi
 *  @copyright          (c) 2009  CERDAN Yohann, All rights reserved
 *  @version            Fri Jul 24 00:16:26 CEST 2009
 */

// Check if APC extension is loaded
if(!defined('APC_IS_LOADED')) {
    define('APC_IS_LOADED', extension_loaded('apc'));
}

class Template {
    private $vars;    // Holds all the template variables
    private $file;    // The file name you want to load
    private $key;     // The cache variable name (if any)
    private $ttl;     // Time To Live; store returned content in the cache for ttl seconds.

    /**
     * Class constructor
     *
     * @param $file string the file name you want to load
     *
     * @return void
     */

    function Template($file, $key=NULL, $ttl=3600)
    {
        if (!$file || !@is_readable($file)) {
            trigger_error('Template(): \''.$file.'\' is not a valid or readable template file',
                          E_USER_ERROR);
            return false;
        }
        if($key != NULL && !APC_IS_LOADED) {
            trigger_error('Template(): key used but module APC not loaded...',
                          E_USER_NOTICE);
        }
        $this->file = $file;
        $this->key  = $key;
        $this->ttl  = $ttl;
    }

    function tryGetCache()
    {
        if ($this->key == NULL) {
            trigger_error('tryGetCache(): no cache variable name defined.', E_USER_ERROR);
            return false;
        }
        if(!APC_IS_LOADED) {
            trigger_error('tryGetCache(): module APC not loaded.', E_USER_ERROR);
        }
        return apc_fetch($this->key);
    }

    /**
     * Set a template variable
     *
     * @param $name string the name of the variable
     * @param $value string the value of the variable
     *
     * @return void
     */
    function assign($name, $value)
    {
        $this->vars[$name] = $value;
        return true;
    }

    /**
     * Open, parse, and return the template file
     *
     * @return string the content of the template file
     */
    function fetch()
    {
        extract($this->vars);           // Extract the vars to local namespace
        ob_start();                     // Start output buffering
        include($this->file);           // Include the file
        $contents = ob_get_contents();  // Get the contents of the buffer
        ob_end_clean();                 // End buffering and discard
        if($this->key != NULL && APC_IS_LOADED) {
            apc_store($this->key,$contents,$this->ttl);
        }
        return $contents;               // Return the contents
    }
}

// Local Variables:   **
// c-basic-offset : 4 **
// End:               **
