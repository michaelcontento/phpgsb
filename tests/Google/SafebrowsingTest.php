<?php

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
