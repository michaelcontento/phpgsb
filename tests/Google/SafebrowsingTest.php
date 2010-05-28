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
	/**
	 * @var Google_Safebrowsing_Requester
	 */
	private $_requesterMock;

	/**
	 * @var Google_Safebrowsing
	 */
	private $_safebrowsing;

	public function setUp()
	{
        $this->_requesterMock = $this->getMock(
            'Google_Safebrowsing_Requester',
            array(),
            array('[APIKEY]')
        );

        $this->_safebrowsing = new Google_Safebrowsing('[APIKEY]');
        $this->_safebrowsing->setRequester($this->_requesterMock);
	}

    public function testGetLists()
    {
    	$this->_requesterMock->expects($this->once())
            ->method('getLists')
            ->will($this->returnValue("list1\nlist2\nlist3\n"));

    	$this->assertEquals(
            array(
                'list1',
                'list2',
                'list3'
            ),
            $this->_safebrowsing->getLists()
        );
    }

    public function test_LIVE_TEST_REMOVE_ME_LATER()
    {
#    	$goog = new Google_Safebrowsing('ABQIAAAAqkWYEbo3LIYtxwQNWk0RbhTm4_7vVQOTE_iGsfoCNq06amSRbA');
#    	print_r($goog->getLists());
    }
}
