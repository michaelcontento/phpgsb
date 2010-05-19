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
 * @author Michael Contento <michael.contento@gmail.com>
 */
class Google_SafebrowsingTest extends PHPUnit_Framework_TestCase
{
    private $_blacklistPath = '';
    private $_blacklistCache = null;
    private $_malwarePath = '';
    private $_malwareCache = null;

    private function _createCache($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $cache = Zend_Cache::factory(
            'Core',
            'File',
            array(
                'automatic_serialization' => true
            ),
            array(
                'cache_dir' => $path
            )
        );

        return $cache;
    }

    public function setUp()
    {
        $this->_blacklistPath = TESTS_TEMP_DIRECTORY . __CLASS__ . '_blacklist';
        $this->_blacklistCache = $this->_createCache($this->_blacklistPath);

        $this->_malwarePath = TESTS_TEMP_DIRECTORY . __CLASS__ . '_malware';
        $this->_malwareCache = $this->_createCache($this->_malwarePath);
    }

    public function tearDown()
    {
        if (file_exists($this->_blacklistPath)) {
            system('rm -rf ' . $this->_blacklistPath);
        }

        if (file_exists($this->_malwarePath)) {
            system('rm -rf ' . $this->_malwarePath);
        }
    }

    public function testExceptionOnBrokenData()
    {
        $body = "[goog-unknown-hash 1.3";

        $adapter = new Zend_Http_Client_Adapter_Test();
        $client = new Zend_Http_Client(
            null,
            array(
                'adapter' => $adapter
            )
        );
        $adapter->setResponse(
            "HTTP/1.1 200 OK\r\n"
            . "\r\n"
            . $body
        );

        $api = new Google_Safebrowsing($this->_blacklistCache, $this->_malwareCache);
        $api->setHttpClient($client);

        $this->setExpectedException('Google_Exception');
        $api->update('myApiKey', false);
    }

    public function testUpdate()
    {
        $body = "[goog-black-hash 1.3]\n+hash1\n+hash2\n"
              . "[goog-malware-hash 1.3]\n+hash1\n+hash2\n-hash2\n";

        $adapter = new Zend_Http_Client_Adapter_Test();
        $client = new Zend_Http_Client(
            null,
            array(
                'adapter' => $adapter
            )
        );
        $adapter->setResponse(
            "HTTP/1.1 200 OK\r\n"
            . "\r\n"
            . $body
        );

        $api = new Google_Safebrowsing($this->_blacklistCache, $this->_malwareCache);
        $api->setHttpClient($client);
        $api->update('myApiKey', false);

        $this->assertTrue($api->getBlacklist()->containsHash("hash1"));
        $this->assertTrue($api->getBlacklist()->containsHash("hash2"));
        $this->assertTrue($api->getMalware()->containsHash("hash1"));
        $this->assertFalse($api->getMalware()->containsHash("hash2"));
    }
}
