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
class Google_Safebrowsing_Client
{
    /**
     * @var Google_Safebrowsing_Backend_Interface
     */
    private $_backend;

    /**
     * @param Google_Safebrowsing_Backend_Interface $backend
     */
	public function __construct(Google_Safebrowsing_Backend_Interface $backend)
	{
		$this->_backend = $backend;
	}

	/**
	 * @return Google_Safebrowsing_Backend_Interface
	 */
	public function getBackend()
	{
		return $this->_backend;
	}
}
