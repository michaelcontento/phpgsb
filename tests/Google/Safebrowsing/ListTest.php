<?php

class Test_Google_Safebrowsing_List extends Google_Safebrowsing_List
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
class Google_Safebrowsing_ListTest extends PHPUnit_Framework_TestCase
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
        $list = new Google_Safebrowsing_List($this->_cache);
        $list->setMinor(123);
        unset($list);
        $list = new Google_Safebrowsing_List($this->_cache);

        $this->assertEquals(123, $list->getMinor());
    }

    public function testDefaultMinorNumber()
    {
        $list = new Google_Safebrowsing_List($this->_cache);
        $this->assertEquals(-1, $list->getMinor());
    }

    public function testDefaultMajorNumber()
    {
        $list = new Google_Safebrowsing_List($this->_cache);
        $this->assertEquals(1, $list->getMajor());
    }

    public function testSetMajorNumber()
    {
        $list = new Google_Safebrowsing_List($this->_cache);
        $list->setMajor(123);
        unset($list);
        $list = new Google_Safebrowsing_List($this->_cache);

        $this->assertEquals(123, $list->getMajor());
    }

    public function testAddHash()
    {
        $list = new Google_Safebrowsing_List($this->_cache);

        $list->add('myHash');
        $this->assertTrue($list->containsHash('myHash'));
        $list->add('myHash');
    }

    public function testClearList()
    {
        $list = new Google_Safebrowsing_List($this->_cache);

        $list->add('myHash');
        $list->clear();
        $this->assertFalse($list->containsHash('myHash'));
    }

    public function testRemoveHash()
    {
        $list = new Google_Safebrowsing_List($this->_cache);

        $list->add('myHash');
        $list->remove('myHash');
        $this->assertFalse($list->containsHash('myHash'));
        $list->remove('myHash');
    }

    public function testcanonicalizeUrl()
    {
        $list = new Test_Google_Safebrowsing_List($this->_cache);

        $this->assertEquals('google.com/', $list->canonicalizeUrl('http://google.com/'));
        $this->assertEquals('google.com/', $list->canonicalizeUrl('http://gOOgle.com/'));
        $this->assertEquals('google.com/', $list->canonicalizeUrl('http://..google..com../'));
        $this->assertEquals('google.com/A%1F/', $list->canonicalizeUrl('http://google.com/%25%34%31%25%31%46'));
        $this->assertEquals('google%5E.com/', $list->canonicalizeUrl('http://google^.com/'));
        $this->assertEquals('google.com/2/', $list->canonicalizeUrl('http://google.com/1/../2/././'));
        $this->assertEquals('google.com/1/2?3//4', $list->canonicalizeUrl('http://google.com/1//2?3//4'));
        $this->assertEquals('1.2.3.4/', $list->canonicalizeUrl('1.2.3.4'));
        #$this->assertEquals('10.28.1.45/', $list->canonicalizeUrl('012.034.01.055'));
        #$this->assertEquals('18.67.68.1/', $list->canonicalizeUrl('0x12.0x43.0x44.0x01'));
        #$this->assertEquals('10.1.2.3/', $list->canonicalizeUrl('167838211'));
        #$this->assertEquals('12.18.2.156/', $list->canonicalizeUrl('12.0x12.01234'));
        #$this->assertEquals('20.2.0.3/', $list->canonicalizeUrl('276.2.3'));
        #$this->assertEquals('0.0.0.11/', $list->canonicalizeUrl('0x10000000b'));
    }

    public function testIsListed()
    {
        $list = new Test_Google_Safebrowsing_List($this->_cache);
        $list->add('08bcccbf12ae8d1b77cfdeb16daf5bd1');
        $this->assertTrue($list->isListed('http://97ai.com'));
    }

    public function testGetLookups()
    {
        #$list = new Test_Google_Safebrowsing_List($this->_cache);
        #print_r($list->getLookups('http://www.google.com/'));
    }
}
