<?php

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

$adapt = $GLOBALS['adapt'];

/* Set the file path if it's not set */
$path = $adapt->setting('storage_file_system.file_store_path');

if (is_null($path)){
    $path = EXTENSION_PATH . 'storage_file_system/store/';
    $adapt->setting('storage_file_system.file_store_path', $path);
}


?>