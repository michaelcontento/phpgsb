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
interface Google_Safebrowsing_Backend_Interface
{
    /**
     * @var int
     */
    const HASH_TTL_IN_SEC = 2700;

    /**
     * @return int
     */
    public function getLastUpdate();

    /**
     * @param int $chunknum
     * @param string $hostkey
     * @param string $hash
     * @return void
     */
    public function add($chunknum, $hostkey, $hash);

    /**
     * @param int $chunknum
     * @param string|null $hostkey
     * @param string|null $hash
     * @return void
     */
    public function remove($chunknum, $hostkey = null, $hash = null);

    /**
     * @param string $hash
     * @return bool
     */
    public function contains($hash);
}