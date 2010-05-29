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
 * @see http://code.google.com/apis/safebrowsing/
 * @author Michael Contento <michael.contento@gmail.com>
 */
class Google_Safebrowsing_Api
{
	/**
	 * @var string
	 */
	const CLIENT_VERSION = '2.0';

	/**
	 * @var string
	 */
	const PROTOCOL_VERSION = '2.2';

	/**
	 * @var string
	 */
	const BASE_URL = 'http://safebrowsing.clients.google.com/safebrowsing/';

	/**
	 * @var string
	 */
	private $_apiKey;

	/**
	 * @var Zend_Http_Client
	 */
	private $_httpClient;

	/**
	 * @param string $method
	 * @param array $params
	 * @return string
	 */
	private function _createUrl($method, array $params = array())
	{
		$params['client'] = 'api';
		$params['apikey'] = $this->_apiKey;
		$params['appver'] = self::CLIENT_VERSION;
		$params['pver'] = self::PROTOCOL_VERSION;

        return self::BASE_URL . $method . '?' . http_build_query($params);
	}

	/**
	 * @param string $url
	 * @param string $rawPostData
	 * @return string
	 */
	private function _fetch($url, $rawPostData = '')
	{
        $this->_httpClient
            ->resetParameters()
            ->setMethod(Zend_Http_Client::GET)
            ->setUri($url);

        if (!empty($rawPostData)) {
        	$this->_httpClient
                ->setRawData($rawPostData)
                ->setMethod(Zend_Http_Client::POST);
        }

        $response = $this->_httpClient->request();
        if (!$response->isSuccessful()) {
        	echo "----[ REQUEST ]----\n";
        	echo $this->_httpClient->getLastRequest();
        	echo "----[ RESPONSE ]----\n";
            echo $response->getHeadersAsString();
            throw new Google_Exception('Error while reading from google!');
        }

        return $response->getBody();
	}

	/**
	 * @param string $apiKey
	 */
	public function __construct($apiKey)
	{
        $this->_apiKey = $apiKey;
        $this->_httpClient = new Zend_Http_Client();
	}

	/**
	 * @param Zend_Http_Client $httpClient
	 * @return Google_Safebrowsing_Requester
	 */
	public function setHttpClient(Zend_Http_Client $httpClient)
	{
		$this->_httpClient = $httpClient;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLists()
	{
		return $this->_fetch($this->_createUrl('list'));
	}

	/**
	 * @param string $rawPostBody
	 * @return string
	 */
	public function getDownloads($rawPostBody)
	{
        return $this->_fetch($this->_createUrl('downloads'), $rawPostBody);
	}
}