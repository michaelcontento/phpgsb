<?php

/**
 * Class to talk with the Google Safebrowsing API
 *
 * @see http://code.google.com/apis/safebrowsing/
 * @author Michael Contento <michael.contento@gmail.com>
 */
class Google_Safebrowsing
{
    /**
     * Used http client
     *
     * @var Zend_Http_Client
     */
    private $_httpClient = null;

    /**
     * Used client key for the secure connection
     *
     * @var string
     */
    private $_clientKey = '';

    /**
     * Used wrapped key for the secure connection
     *
     * @var string
     */
    private $_wrappedKey = '';

    /**
     * Contains the blacklist object
     *
     * @var Google_Safebrowsing_List
     */
    private $_blacklist = null;

    /**
     * Contains the malware list object
     *
     * @var Google_Safebrowsing_List
     */
    private $_malware = null;

    /**
     * Url to fetch the list updates from
     *
     * @var string
     */
    const URL_UPDATE = 'http://sb.google.com/safebrowsing/update?client=api&apikey=%AKEY%&version=%VER%&wrkey=%WKEY%';

    /**
     * Url to fetch the client- and wrappedkey for the secure connection
     *
     * @var string
     */
    const URL_GETKEY = 'https://sb-ssl.google.com/safebrowsing/getkey?client=api';

    /**
     * Google value to specify the blacklist
     *
     * @var string
     */
    const TYPE_BLACKLIST = 'goog-black-hash';

    /**
     * Google value to specify the malware list
     *
     * @var string
     */
    const TYPE_MALWARE = 'goog-malware-hash';

    /**
     * Separator for the mac
     *
     * @var string
     */
    const MAC_SEPARATOR = ':coolgoog:';

    /**
     * Fetches the client- and wrappedkey to create the secure connection
     *
     * When the secure connection is used, google will create and transmit
     * an special "mac-value". With this mac an our private clientkey we
     * can validate the received data.
     *
     * @return Google_Safebrowsing
     */
    private function _initializeSecureConnection()
    {
        $this->_clientKey = '';
        $this->_wrappedKey = '';

        $request = $this->_httpClient->setUri(self::URL_GETKEY)->request();
        if (!$request->isSuccessful()) {
            throw new Google_Exception('Error while reading from google!');
        }
        $result = $request->getBody();

        if (!preg_match('/clientkey:(.*):(.*)\nwrappedkey:(.*):(.*)/', $result, $matches)) {
            throw new Google_Exception('Invalid data received!');
        }

        if (strlen($matches[2]) != $matches[1]) {
            throw new Google_Exception('Invalid data received');
        }

        if (strlen($matches[4]) != $matches[3]) {
            throw new Google_Exception('Invalid data received');
        }

        $this->_clientKey = $matches[2];
        $this->_wrappedKey = $matches[4];

        return $this;
    }

    /**
     * Split the received string from google into a clean php-array
     *
     * @param string $header
     * @return array
     */
    private function _splitString($header)
    {
        $regex = '/'
                 . '\['
                   . '(?<type>' . self::TYPE_BLACKLIST . '|' . self::TYPE_MALWARE . ')'
                   . ' '
                   . '(?<major>[0-9]+)'
                   . '\.'
                   . '(?<minor>[0-9]+)'
                   . '(?<update>| update)'
                 . '\]'
                 . '(?:'
                   . '\[mac=(?<mac>[^\]]+)\]'
                   . '|'
                 . ')'
                 . '(?<data>[^\[]*)'
                 . '/s';

        if (!preg_match_all($regex, $header, $matches, PREG_SET_ORDER)) {
            throw new Google_Exception('Invalid data received');
        }

        $return = array();
        foreach ($matches as $index => $match) {

            // TODO Implement the MAC-Check - but it seems to be wrong! :(

            $return[$index] = array(
                'type' => $match['type'],
                'major' => $match['major'],
                'minor' => $match['minor'],
                'update' => (bool) $match['update'],
                'mac' => @$match['mac'],
                'data' => explode("\n", $match['data'])
            );
        }

        return $return;
    }

    /**
     * Constructor
     *
     * @param Zend_Cache_Core $blacklistCache
     * @param Zend_Cache_Core $malwareCache
     * @return Google_Safebrowsing
     */
    public function __construct(Zend_Cache_Core $blacklistCache, Zend_Cache_Core $malwareCache)
    {
        $this->_blacklist = new Google_Safebrowsing_List($blacklistCache);
        $this->_malware = new Google_Safebrowsing_List($malwareCache);
        $this->_httpClient = new Zend_Http_Client();
    }

    /**
     * Updates the black- and malwarelist
     *
     * @param string $apiKey
     * @param bool $secureConnection
     * @return Google_Safebrowsing
     */
    public function update($apiKey, $secureConnection = true)
    {
        // Build the basic url
        $url = str_replace('%AKEY%', $apiKey, self::URL_UPDATE);

        // Add the secure key
        if ($secureConnection) {
            $this->_initializeSecureConnection();
            $url = str_replace('%WKEY%', $this->_wrappedKey, $url);
        } else {
            $url = str_replace('%WKEY%', '', $url);
        }

        // Build the version string
        $version = self::TYPE_BLACKLIST
                 . ':'
                 . $this->_blacklist->getMajor()
                 . ':'
                 . $this->_blacklist->getMinor()
                 . ','
                 . self::TYPE_MALWARE
                 . ':'
                 . $this->_malware->getMajor()
                 . ':'
                 . $this->_malware->getMinor();
        $url = str_replace('%VER%', $version, $url);

        // And fetch the data!
        $request = $this->_httpClient->setUri($url)->request();
        if (!$request->isSuccessful()) {
            throw new Google_Exception('Error while reading from google!');
        }

        $result = $request->getBody();
        if (empty($result)) {
            return $this;
        }

        // Parse and process the data
        $data = $this->_splitString($result);
        foreach ($data as $row) {
            if ($row['type'] == self::TYPE_BLACKLIST) {
                $list = &$this->_blacklist;
            } else {
                $list = &$this->_malware;
            }

            if (!$row['update']) {
                $list->clear();
            }

            $list->setMajor($row['major']);
            $list->setMinor($row['minor']);

            foreach ($row['data'] as $index => $hash) {
                if (empty($hash)) {
                    continue;
                }

                if (substr($hash, 0, 1) == '+') {
                    $list->add(trim(substr($hash, 1)));
                } else {
                    $list->remove(trim(substr($hash, 1)));
                }
            }
        }

        return $this;
    }

    /**
     * Check if the url is listed on the blacklist
     *
     * @param string $url
     * @return bool
     */
    public function isBlacklisted($url)
    {
        return $this->_blacklist->isListed($url);
    }

    /**
     * Check if the url is marked as malware
     *
     * @param string $url
     * @return bool
     */
    public function isMalware($url)
    {
        return $this->_malware->isListed($url);
    }

    /**
     * Check wether the url is on the black- or malwarelist
     *
     * @param string $url
     * @return bool
     */
    public function isListed($url)
    {
        if ($this->isMalware($url) || $this->isBlacklisted($url)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the used blacklist list
     *
     * @return Google_Safebrowsing_List
     */
    public function getBlacklist()
    {
        return $this->_blacklist;
    }

    /**
     * Returns the used malware list
     *
     * @return Google_Safebrowsing_List
     */
    public function getMalware()
    {
        return $this->_malware;
    }

    /**
     * Override the used http client for testing
     *
     * @param Zend_Http_Client $client
     * @return Google_Safebrowsing
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_httpClient = $client;
        return $this;
    }
}