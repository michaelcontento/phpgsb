<?php

/**
 *  Copyright 2009-2010 Michael Contento <michaelcontento@gmail.com>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

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
