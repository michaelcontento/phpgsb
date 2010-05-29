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
class Google_Safebrowsing_ApiTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Zend_Http_Client_Adapter_Test
	 */
	private $_httpAdapter;

	/**
     * @var Google_Safebrowsing_Api
	 */
	private $_api;

	public function setUp()
	{
        $this->_httpAdapter = new Zend_Http_Client_Adapter_Test();

        $client = new Zend_Http_Client();
        $client->setAdapter($this->_httpAdapter);

        $this->_api = new Google_Safebrowsing_Api('[APIKEY]');
        $this->_api->setHttpClient($client);
	}

    public function testGetLists()
    {
    	$expected = "list1\nlist2\nlist3";
    	$this->_httpAdapter->setResponse(
            "HTTP/1.1 200 OK\r\n"
            . "\r\n"
            . $expected
    	);
    	$this->assertEquals($expected, $this->_api->getLists());
    }
}
