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

class Test_Google2_Safebrowsing_List extends Google2_Safebrowsing_List
{
    public function canonicalizePath($path)
    {
        return $this->_canonicalizePath($path);
    }

    public function canonicalizeHost($host)
    {
        return $this->_canonicalizeHost($host);
    }

    public function canonicalizeIp($ip)
    {
        return $this->_canonicalizeIp($ip);
    }

    public function canonicalizeUrl($url)
    {
        return $this->_canonicalizeUrl($url);
    }

    public function getLookups($url, $readable = false)
    {
        return $this->_getLookups($url, $readable);
    }
}

/**
 * @author Michael Contento <michael.contento@gmail.com>
 */
class Google2_Safebrowsing_ListTest extends PHPUnit_Framework_TestCase
{
    private $_storagePath = '';
    private $_cache = null;

    public function setUp()
    {
        $this->_storagePath = TESTS_TEMP_DIRECTORY . __CLASS__;

        if (!file_exists($this->_storagePath)) {
            mkdir($this->_storagePath, 0777, true);
        }

        $this->_cache = Zend_Cache::factory(
            'Core',
            'File',
            array(
                'automatic_serialization' => true
            ),
            array(
                'cache_dir' => $this->_storagePath
            )
        );
    }

    public function tearDown()
    {
        if (file_exists($this->_storagePath)) {
            system('rm -rf ' . $this->_storagePath);
        }
    }

    public function testSetMinorNumber()
    {
        $list = new Google2_Safebrowsing_List($this->_cache);
        $list->setMinor(123);
        unset($list);
        $list = new Google2_Safebrowsing_List($this->_cache);

        $this->assertEquals(123, $list->getMinor());
    }

    public function testDefaultMinorNumber()
    {
        $list = new Google2_Safebrowsing_List($this->_cache);
        $this->assertEquals(-1, $list->getMinor());
    }

    public function testDefaultMajorNumber()
    {
        $list = new Google2_Safebrowsing_List($this->_cache);
        $this->assertEquals(1, $list->getMajor());
    }

    public function testSetMajorNumber()
    {
        $list = new Google2_Safebrowsing_List($this->_cache);
        $list->setMajor(123);
        unset($list);
        $list = new Google2_Safebrowsing_List($this->_cache);

        $this->assertEquals(123, $list->getMajor());
    }

    public function testAddHash()
    {
        $list = new Google2_Safebrowsing_List($this->_cache);

        $list->add('myHash');
        $this->assertTrue($list->containsHash('myHash'));
        $list->add('myHash');
    }

    public function testClearList()
    {
        $list = new Google2_Safebrowsing_List($this->_cache);

        $list->add('myHash');
        $list->clear();
        $this->assertFalse($list->containsHash('myHash'));
    }

    public function testRemoveHash()
    {
        $list = new Google2_Safebrowsing_List($this->_cache);

        $list->add('myHash');
        $list->remove('myHash');
        $this->assertFalse($list->containsHash('myHash'));
        $list->remove('myHash');
    }

    public function testcanonicalizeUrl()
    {
        $list = new Test_Google2_Safebrowsing_List($this->_cache);

        $this->assertEquals('google.com/', $list->canonicalizeUrl('http://google.com/'));
        $this->assertEquals('google.com/', $list->canonicalizeUrl('http://gOOgle.com'));
        $this->assertEquals('google.com/', $list->canonicalizeUrl('http://..google..com../'));
        $this->assertEquals('google.com/A%1F/', $list->canonicalizeUrl('http://google.com/%25%34%31%25%31%46'));
        $this->assertEquals('google%5E.com/', $list->canonicalizeUrl('http://google^.com/'));
        $this->assertEquals('google.com/2/', $list->canonicalizeUrl('http://google.com/1/../2/././'));
        $this->assertEquals('google.com/1/2?3//4', $list->canonicalizeUrl('http://google.com/1//2?3//4'));
        $this->assertEquals('1.2.3.4/', $list->canonicalizeUrl('1.2.3.4'));
        $this->assertEquals('10.28.1.45/', $list->canonicalizeUrl('012.034.01.055'));
        $this->assertEquals('18.67.68.1/', $list->canonicalizeUrl('0x12.0x43.0x44.0x01'));
    }

    public function testIsListed()
    {
        $list = new Test_Google2_Safebrowsing_List($this->_cache);
        $list->add('99cefcc1a924599f2648f09f82f5c09c');
        $this->assertTrue($list->isListed('http://97ai.com'));
    }

    public function testGetLookups()
    {
        $list = new Test_Google2_Safebrowsing_List($this->_cache);

        $expected = array(
            'a.b.c/',
            'a.b.c/1/2.html',
            'a.b.c/1/',
            'b.c/',
            'b.c/1/2.html',
            'b.c/1/'
        );
        $this->assertEquals($expected, $list->getLookups('http://a.b.c/1/2.html', true));

        $expected = array(
            'a.b.c.d.e.f.g/',
            'a.b.c.d.e.f.g/1.html',
            'c.d.e.f.g/',
            'c.d.e.f.g/1.html',
            'd.e.f.g/',
            'd.e.f.g/1.html',
            'e.f.g/',
            'e.f.g/1.html',
            'f.g/',
            'f.g/1.html'
        );
        $this->assertEquals($expected, $list->getLookups('http://a.b.c.d.e.f.g/1.html', true));
    }
}
