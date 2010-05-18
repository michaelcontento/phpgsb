<?php

/**
 * Load the test configuration
 */
require_once dirname(__FILE__) . '/configuration.php';

/**
 * Configure PHP
 */
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}
ini_set('error_reporting', E_ALL | E_STRICT | E_DEPRECATED);
ini_set('display_errors', true);

set_include_path(
    dirname(__FILE__) . '/../library/' . PATH_SEPARATOR .
    TESTS_ZF_PATH . PATH_SEPARATOR .
    get_include_path()
);

PHPUnit_Util_Filter::addDirectoryToFilter(TESTS_ZF_PATH);

/**
 * PHP class loader
 *
 * @param string $classname
 */
function __autoload($classname)
{
    require str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';
}

/**
 * Clear the used temporary directory
 */
if (file_exists(TESTS_TEMP_DIRECTORY)) {
    system('rm -rfd ' . TESTS_TEMP_DIRECTORY);
}
mkdir(TESTS_TEMP_DIRECTORY, 0777, true);
