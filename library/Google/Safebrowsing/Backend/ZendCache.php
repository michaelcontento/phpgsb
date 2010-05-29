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
class Google_Safebrowsing_Backend_ZendCache implements Google_Safebrowsing_Backend_Interface
{
    /**
     * @var string
     */
    const LAST_UPDATE_CACHE_KEY = '__lastUpdate__';

    /**
     * @var Zend_Cache_Core
     */
    private $_cache;

    /**
     * @param Zend_Cache_Core $cache
     */
    public function __construct(Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
        $this->_cache->setLifetime(self::HASH_TTL_IN_SEC);
    }

    /**
     * @return int
     */
    public function getLastUpdate()
    {
        return (int) $this->_cache->load(self::LAST_UPDATE_CACHE_KEY);
    }

    /**
     * @param int $time
     */
    public function setLastUpdate($time)
    {
        $this->_cache->save($time, self::LAST_UPDATE_CACHE_KEY);
    }

    /**
     * @param int $chunknum
     * @param string $hostkey
     * @param string $hash
     * @return void
     */
    public function add($chunknum, $hostkey, $hash)
    {
        $this->_cache->save(true, $hash, array((string) $chunknum, $hostkey));
    }

    /**
     * @param int $chunknum
     * @param string|null $hostkey
     * @param string|null $hash
     * @return void
     */
    public function remove($chunknum, $hostkey = null, $hash = null)
    {
        if ($hash !== null) {
            $this->_cache->remove($hash);
            return;
        }

        if ($hostkey === null) {
            $tags = array((string) $chunknum);
        } else {
            $tags = array((string) $chunknum, $hostkey);
        }
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function contains($hash)
    {
        return (bool) $this->_cache->load($hash);
    }
}