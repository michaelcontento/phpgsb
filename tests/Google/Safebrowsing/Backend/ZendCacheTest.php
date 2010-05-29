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
class Google_Safebrowsing_Backend_ZendCacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Cache_Core
     */
    private $_zendCache;

    /**
     * @var Google_Safebrowsing_Backend_ZendCache
     */
    private $_backend;

    public function setUp()
    {
        $this->_zendCache = Zend_Cache::factory(
            'Core',
            'File',
            array(
                'automatic_serialization' => true
            ),
            array(
                'cache_dir' => TESTS_TEMP_DIRECTORY
            )
        );

        $this->_backend = new Google_Safebrowsing_Backend_ZendCache($this->_zendCache);
    }

    public function tearDown()
    {
        $this->_zendCache->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    public function testReturnZeroIfLastUpdateNotSet()
    {
        $this->assertEquals(0, $this->_backend->getLastUpdate());
    }

    public function testSetLastUpdate()
    {
        $this->_backend->setLastUpdate(123456789);
        $this->assertEquals(123456789, $this->_backend->getLastUpdate());
    }

    public function testAdd()
    {
        $this->_backend->add('chunk', 'hostkey', 'hash');
        $this->assertTrue($this->_zendCache->load('hash'));
    }

    public function testRemoveWithChunknumAndHostkeyAndHash()
    {
        $this->_backend->add('chunk1', 'hostkey1', 'hash1');
        $this->_backend->add('chunk2', 'hostkey2', 'hash2');
        $this->_backend->remove('1', 'hostkey1', 'hash1');
        $this->assertFalse($this->_zendCache->load('hash1'));
        $this->assertTrue($this->_zendCache->load('hash2'));
    }

    public function testRemoveWithChunknumAndHostkey()
    {
        $this->_backend->add('chunk1', 'hostkey1', 'hash1');
        $this->_backend->add('chunk2', 'hostkey2', 'hash2');
        $this->_backend->remove('chunk1', 'hostkey1');
        $this->assertFalse($this->_zendCache->load('hash1'));
        $this->assertTrue($this->_zendCache->load('hash2'));
    }

    public function testRemoveWithChunknum()
    {
        $this->_backend->add('chunk1', 'hostkey1', 'hash1');
        $this->_backend->add('chunk2', 'hostkey2', 'hash2');
        $this->_backend->remove('chunk1');
        $this->assertFalse($this->_zendCache->load('hash1'));
        $this->assertTrue($this->_zendCache->load('hash2'));
    }

    public function testContains()
    {
        $this->_backend->add('chunk', 'hostkey', 'hash');
        $this->assertTrue($this->_backend->contains('hash'));
        $this->assertFalse($this->_backend->contains('invalid'));
    }
}
