PHP Google Safe Browsing
========================

**phpgsb** is the PHP implementation of the [Goolge Safe Browsing](http://code.google.com/apis/safebrowsing/) (GSB) for PHP.

The implementation is not 100% feature complete and some special URIs are not processed (see the todo section). 
But this implementation is working and can be used to extend and / or play with the Google Safe Browsing API :)

Requirements
------------

Only PHP and the Zend Framework - i think this is feasible ;)

* [PHP](http://www.php.net/) >= 5.2.0
* [Zend Framework](http://framework.zend.com/) >= 1.9.0

Installation and usage
----------------------

    <?php

    set_include_path(
        '[ZEND FRAMEWORK LIBRARY PATH]' 
        . PATH_SEPARATOR . get_include_path()
    );

    function __autoload($classname) {
        require str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';
    }

    // Setup the Google_Safebrowsing object
    $blacklistCache = Zend_Cache::factory('[CACHE SETUP]');
    $malwareCache = Zend_Cache::factory('[CACHE SETUP]');
    $goog = new Google_Safebrowsing($blacklistCache, $malwareCache);

    // Update the internal lists every hour per cron! NOT EVERY TIME!
    $goog->update('[GOOGLE APIKEY]');

    // Do some lookups
    if ($goog->isListed('[URL TO CHECK]')) {
        echo "URL is blacklisted or points to malware!";
    }

    if ($goog->isMalware('[URL TO CHECK]')) {
        echo "URL points to malware!";
    }

    if ($goog->isBlacklisted('[URL TO CHECK]')) {
        echo "URL is blacklisted!";
    }
    ?>

Current ToDo-List
-----------------

* Fix the half-implemented MAC validation
* "resolve and lookup" for IP adresses
* Performance profiling and remove some slow regular expressions

License
-------

    Copyright 2009-2010 Michael Contento <michaelcontento@gmail.com>

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

        http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
