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
 * This class represents a list used for Google2_Safebrowsing and
 * can store the malware- or blacklist.
 *
 * @author Michael Contento <michael.contento@gmail.com>
 */
class Google2_Safebrowsing_List
{
    /**
     * Used instance of Zend_Cache
     *
     * @var Zend_Cache_Core
     */
    private $_cache = null;

    /**
     * Internal array to reduce the Zend_Cache calls
     *
     * @var array
     */
    private $_writeCache = array();

    /**
     * Id to store the major number within Zend_Cache
     *
     * @var string
     */
    const CACHE_ID_MAJOR = 'major';

    /**
     * Id to store the minor number within Zend_Cache
     *
     * @var string
     */
    const CACHE_ID_MINOR = 'minor';

    /**
     * Returns the canonicalized path
     *
     * @param string $path
     * @return string
     */
    protected function _canonicalizePath($path)
    {
        if (empty($path)) {
            return '/';
        }

        // Remove some anchors from the url
        $path = preg_replace('/#.*$/i', '', $path);

        // Need trailing slash
        if (strpos($path, '?') == 0 and substr($path, -1) != '/' and preg_match('/\.[a-z0-9]{1,4}$/i', $path) == 0) {
            $path .= '/';
        }

        // Replacing "/./" with "/"
        $count = 0;
        $path = str_replace('/./', '/', $path, $count);
        while ($count > 0) {
            $path = str_replace('/./', '/', $path, $count);
        }

        // Removing "/../" along with the preceding path component
        $pos = strpos($path, '/../');
        while ($pos > 0) {
            $startPos = $pos - 1;

            while ($startPos >= 0) {
                if (substr($path, $startPos, 1) == '/') {
                    break;
                }

                --$startPos;
            }

            $path = substr($path, 0, $startPos + 1) . substr($path, ($pos + 4));
            $pos = strpos($path, '/../');
        }

        // Fully escape
        while (strpos($path, '%') > 0 ) {
            $path = urldecode($path);
        }

        // Re-Escape once
        $path = urlencode($path);
        $path = str_replace('%2F', '/', $path);
        $path = str_replace('%3F', '?', $path);
        $path = str_replace('%3D', '=', $path);

        return $path;
    }

    /**
     * Returns the canonicalized host
     *
     * @param string $host
     * @return string
     */
    protected function _canonicalizeHost($host)
    {
        // collapse multiple dots
        $count = 0;
        $host = str_replace('..', '.', $host, $count);
        while ($count > 0) {
            $host = str_replace('..', '.', $host, $count);
        }

        // strip leading and trailing dots
        $host = trim($host, ' .');

        return $host;
    }

    /**
     * Returns the canonicalized ip
     *
     * @param string $ip
     * @return string
     */
    protected function _canonicalizeIp($ip)
    {
        if (!preg_match('/^[0-9xX\.]+$/', $ip)) {
            return $ip;
        }

        $ipParts = explode('.', $ip);

        foreach ($ipParts as $idx => $part) {
            if (substr($part, 0, 2) == '0x' || substr($part, 0, 2) == '0X') {
                $ipParts[$idx] = intval($part, 16);
            } else if (substr($part, 0, 1) == '0') {
                $ipParts[$idx] = intval($part, 8);
            } else {
                $ipParts[$idx] = intval($part, 10);
            }
        }

        return implode('.', $ipParts);
    }

    /**
     * Returns the canonicalized url
     *
     * @param string $url
     * @return string
     */
    protected function _canonicalizeUrl($url)
    {
        // Unescape all escaped characters
        while (preg_match('/%[0-9]{,2}/', $url)) {
            $url = urldecode($url);
        }

        // Remove the protocol-part!
        $url = preg_replace('#^http://#i', '', $url);

        // Remove the trailing slash
        if (substr($url, strlen($url) - 1, 1) == '/') {
            $url = substr($url, 0, -1);
        }

        // Split the url into 2 parts to do host-specific stuff
        $matches = array();
        $url = preg_match('#^([^/]+)(.*)#i', $url, $matches);

        // Lowercase the host
        $host = strtolower($matches[1]);
        $host = $this->_canonicalizeIp($host);
        $host = $this->_canonicalizeHost($host);
        $path = $this->_canonicalizePath($matches[2]);

        // Merge the two url-parts
        $url = urlencode($host) . $path;

        // Replacing "//" with "/" in front of "?"
        $count = 0;
        $subUrl = substr($url, 0, strpos($url, '?'));

        $subUrl = str_replace('//', '/', $subUrl, $count);
        while ($count > 0) {
            $subUrl = str_replace('//', '/', $subUrl, $count);
        }

        $url = $subUrl . substr($url, strpos($url, '?'));

        return $url;
    }

    /**
     * Returns all valid hashes for the specified url
     *
     * @param string $url
     * @param bool $readable
     * @return array
     */
    protected function _getLookups($url, $readable = false)
    {
        $url = $this->_canonicalizeUrl($url);
        $lookups = array();

        // Split the url into 2 parts to do host-specific stuff
        $matches = array();
        preg_match('#^([^/]+)([^?]*)(.*)#i', $url, $matches);
        $host = $matches[1];
        $path = $matches[2];
        $query = $matches[3];

        // Split the hostname into components
        $hostComponents = array();
        preg_match('/([^.]*\.?([^.]*\.?([^.]*\.?([^.]*\.?([^.]+\.[^.]+)))))$/i', $host, $hostComponents);
        $hostComponents = array_unique($hostComponents);
        $hostComponents[0] = $host;

        // Split the path into components
        if (!empty($path)) {
            $pathComponents = array();
            $pathComponents = explode('/', $path);
            unset($pathComponents[0]);
            unset($pathComponents[count($pathComponents)]);

            if (!empty($pathComponents)) {
                $pathComponents = array_chunk($pathComponents, 5);
                $pathComponents = $pathComponents[0];
            }
        }

        // Build the hostname + path mixes we need to lookup
        foreach ($hostComponents as $host) {
            $lookups[] = $host . '/';

            if (!empty($path)) {
                $lookups[] = $host . $path;

                if (count($pathComponents) > 0) {
                    foreach ($pathComponents as $component) {
                        $tempString = '';

                        foreach ($pathComponents as $prefix) {
                            if ($prefix == $component) {
                                break;
                            }

                            $tempString .= '/' . $prefix;
                        }
                        $lookups[] = $host . $tempString . '/' . $component . '/';
                    }
                }
            }

            if (!empty($query)) {
                $lookups[] = $host . $path . $query;
            }
        }

        // Create the md5-sums
        if (!$readable) {
            $temp = array();
            foreach ($lookups as $entry) {
                $temp[] = md5($entry);
            }
            $lookups = $temp;
        }

        return $lookups;
    }

    /**
     * Check if this list contains the specified hash
     *
     * @param string $hash
     * @return bool
     */
    public function containsHash($hash)
    {
        $data = $this->_load($hash);
        return array_key_exists($hash, $data);
    }

    /**
     * Writes the internal cache to Zend_Cache
     *
     * @return Google2_Safebrowsing_List
     */
    private function _flushWriteCache()
    {
        foreach ($this->_writeCache as $key => $data) {
            $this->_cache->save($data, (string) $key);
        }

        return $this;
    }

    /**
     * Loads the "namespace" with the specified hash
     *
     * @param string $hash
     * @return array
     */
    private function _load($hash)
    {
        $cacheKey = substr($hash, 0, 2);

        if (array_key_exists($cacheKey, $this->_writeCache)) {
            return $this->_writeCache[$cacheKey];
        }

        $this->_writeCache[$cacheKey] = $this->_cache->load($cacheKey);
        if ($this->_writeCache[$cacheKey] === false) {
            $this->_writeCache[$cacheKey] = array();
        }

        return $this->_writeCache[$cacheKey];
    }

    /**
     * Constructor
     *
     * @param Zend_Cache_Core $cache
     * @return Google2_Safebrowsing_List
     */
    public function __construct(Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        $this->_flushWriteCache();
    }

    /**
     * Clears this list
     *
     * @return Google2_Safebrowsing_List
     */
    public function clear()
    {
        $this->_writeCache = array();
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        return $this;
    }

    /**
     * Update the major number
     *
     * @param int $version
     * @return Google2_Safebrowsing_List
     */
    public function setMajor($version)
    {
        $this->_cache->save($version, self::CACHE_ID_MAJOR);
        return $this;
    }

    /**
     * Returns the major number
     *
     * @return int
     */
    public function getMajor()
    {
        $version = $this->_cache->load(self::CACHE_ID_MAJOR);
        if ($version === false) {
            $version = 1;
        }

        return $version;
    }

    /**
     * Update the minor number
     *
     * @param int $version
     * @return Google2_Safebrowsing_List
     */
    public function setMinor($version)
    {
        $this->_cache->save($version, self::CACHE_ID_MINOR);
        return $this;
    }

    /**
     * Returns the minor number
     *
     * @return int
     */
    public function getMinor()
    {
        $version = $this->_cache->load(self::CACHE_ID_MINOR);
        if ($version === false) {
            $version = -1;
        }

        return $version;
    }

    /**
     * Adds a new hash to this list
     *
     * @param string $hash
     * @return Google2_Safebrowsing_List
     */
    public function add($hash)
    {
        $this->_load($hash);
        $cacheKey = substr($hash, 0, 2);

        if (array_key_exists($hash, $this->_writeCache[$cacheKey])) {
            return $this;
        }

        $this->_writeCache[$cacheKey][$hash] = null;

        return $this;
    }

    /**
     * Removes a hash from this list
     *
     * @param string $hash
     * @return Google2_Safebrowsing_List
     */
    public function remove($hash)
    {
        $this->_load($hash);
        $cacheKey = substr($hash, 0, 2);

        if (!array_key_exists($hash, $this->_writeCache[$cacheKey])) {
            return $this;
        }

        unset($this->_writeCache[$cacheKey][$hash]);

        return $this;
    }

    /**
     * Check if the specified hash ist listed on this list
     *
     * @param string $url
     * @return bool
     */
    public function isListed($url)
    {
        foreach ($this->_getLookups($url) as $hash) {
            if ($this->containsHash($hash)) {
                return true;
            }
        }
        return false;
    }
}
