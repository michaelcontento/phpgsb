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
class Google_Safebrowsing_RequesterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Zend_Http_Client_Adapter_Test
	 */
	private $_httpAdapter;

	/**
     * @var Google_Safebrowsing_Requester
	 */
	private $_requester;

	public function setUp()
	{
        $this->_httpAdapter = new Zend_Http_Client_Adapter_Test();

        $client = new Zend_Http_Client();
        $client->setAdapter($this->_httpAdapter);

        $this->_requester = new Google_Safebrowsing_Requester('[APIKEY]');
        $this->_requester->setHttpClient($client);
	}

    public function testGetLists()
    {
    	$expected = "list1\nlist2\nlist3";
    	$this->_httpAdapter->setResponse(
            "HTTP/1.1 200 OK\r\n"
            . "\r\n"
            . $expected
    	);
    	$this->assertEquals($expected, $this->_requester->getLists());
    }

    public function testDownload()
    {

    }

    public function test_LIVE_TEST_REMOVE_ME_LATER()
    {
    	$goog = new Google_Safebrowsing_Requester('ABQIAAAAqkWYEbo3LIYtxwQNWk0RbhTm4_7vVQOTE_iGsfoCNq06amSRbA');
    	//print_r($goog->getListData(array('goog-malware-shavar', 'goog-regtest-shavar')));
    	print_r(
    	   $goog->getListData(
    	        array(
                    'goog-malware-shavar' => array(
                        's' => '32641,32801,32641,32801',
                        'a' => '20601,20641,20721,20801,20961,20601,20641,20721,20801,20961',
                    )
                )
            )
        );
    }
}
