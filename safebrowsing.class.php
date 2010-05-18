<?php

// TODO Add some comments
// TODO Write some unittests for this

class GoogleSafeBrowsing 
{
    private $apiKey = null;
    private $secureConnection = false;
    private $blackListUrl = "http://sb.google.com/safebrowsing/update?client=api&apikey=%APIKEY%&version=goog-black-hash:%FROM%:%TO%";
    private $malwareListUrl = "http://sb.google.com/safebrowsing/update?client=api&apikey=%APIKEY%&version=goog-malware-hash:%FROM%:%TO%";
    private $secureUrl = "https://sb-ssl.google.com/safebrowsing/getkey?client=api"; 
    private $clientKey = null;
    private $wrappedKey = null;
    private $secureHashSeperator = ':coolgoog:';
    
    public function __construct($apiKey, $secureConnection = False) 
    {
        $this->apiKey = $apiKey;
        $this->secureConnection = $secureConnection;
    }
    
    private function _initiatePrivateSession() {
        // Retreive the data        
        if (($data = @file_get_contents($this->secureUrl)) == false) {
            return false;
        }
        
        // Split it, baby!        
        if (($data = explode("\n", $data)) == false) {
            return false;
        }
        
        $matches = array();
        foreach ($data as $row) {  
            
            // We irgnore empty lines         
            if ($row == '') {
                continue;
            }
            
            // Check (and split) the information we got
            if (!preg_match('/([^:]+):([^:]+):([^:]+)/i', $row, $matches)) {
                return false;
            }
            
            // Incomplete data detected!
            if ($matches[2] != strlen($matches[3])) {
                return false;
            }
            
            // Match to the right variables
            if ($matches[1] == 'clientkey') {
                $this->clientKey = $matches[3];
            }
            if ($matches[1] == 'wrappedkey') {
                $this->wrappedKey = $matches[3];
            }
        }
        
        return true;
    }
    
    private function _canonicalizeIp($ip) 
    {
        // TODO Implement IP canonicalization
        
        return $ip;
    }

    private function _canonicalizePath($path) 
    {
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
                
                $startPos -= 1;
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
    
    private function _canonicalizeHost($host) 
    {
        // collapse multiple dots
        $count = 0;        
        $host = str_replace('..', '.', $host, $count);
        while ($count > 0) {
            $host = str_replace('..', '.', $host, $count);
        }
        
        // strip leading and trailing dots
        if (substr($host, 0, 1) == '.') {
            $host = substr($host, 1);
        }
        if (substr($host, strlen($host) - 1, 1) == '.') {
            $host = substr($host, 0, -1);
        }
        
        return $host;
    }
    
    public function canonicalize($url) 
    {
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
    
    public function lookupsFor($url, $md5 = true) 
    {
        $url = $this->canonicalize($url);        
        $lookups = array();
        
        // Split the url into 2 parts to do host-specific stuff
        $matches = array();
        preg_match('#^([^/]+)([^?]*)(.*)#i', $url, $matches);
        $host = $matches[1];
        $path = $matches[2];
        $query = $matches[3];
        
        // Split the hostname into components
        $hostComponents = array();
        preg_match('/([^.]*\.?([^.]*\.?([^.]*\.?([^.]+\.[^.]+))))$/i', $host, $hostComponents);
        $hostComponents = array_unique($hostComponents);
        $hostComponents[0] = $host;

        // Split the path into components        
        if (!empty($path)) {            
            $pathComponents = array();
            $pathComponents = explode('/', $path);
            unset($pathComponents[0]);
            unset($pathComponents[count($pathComponents)]);
            $pathComponents = array_chunk($pathComponents, 4);
            $pathComponents = $pathComponents[0];
        }
        
        // Build the hostname + path mixes we need to lookup
        foreach ($hostComponents as $host) {
            $lookups[] = $host;
            
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
        if ($md5) {
            $temp = array();
            foreach ($lookups as $entry) {
                $temp[] = md5($entry);
            }
            $lookups = $temp;
        }
        
        return $lookups;
    }
    
    private function _fetchData($url, $baseVersion) 
    {
        // Initiate the private session
        if ($this->secureConnection) {
            if (!$this->_initiatePrivateSession()) {
                return false;
            }
        }
        
        // Generate a valid URL to fetch the data
        $url = str_replace('%APIKEY%', $this->apiKey, $url);
        $url = str_replace('%FROM%', $baseVersion, $url);
        $url = str_replace('%TO%', '-1', $url);        

        if ($this->secureConnection) {
            $url = "{$url}&wrkey={$this->wrappedKey}";
        }
        
        echo ">> $url\n";
        
        // Retreive the data
        if (($rawdata = @file_get_contents($url)) == false) {
            return false;
        }        
        
        // Split it, baby!
        if (($data = explode("\t", $rawdata)) == false) {
            return false;
        }
        
        // Split the header
        $matches = array();
        if (!preg_match('/\[goog-(black|malware)-hash ([0-9]+\.[0-9]+) ?(update|)\](\[mac=([^\]]*)\]|)/i', $data[0], $matches)) {
            return false;
        }

        if ($this->secureConnection) {
            // TODO Validate received data with received mac!
            
            /*print_r($matches[6]);
            echo "\n";
            $data = md5("{$this->clientKey}{$this->secureHashSeperator}{$rawdata}{$this->secureHashSeperator}{$this->clientKey}");
            print_r(base64_encode(md5($data, true)));
            echo "\n";
            return;*/
        }
        
        // Structure (+ some data) of the stuff we return
        $return = array();        
        $return['version'] = $matches[2];        
        $return['update'] = ($matches[3] == 'update') ? true : false;
        $return['add'] = array();
        $return['drop'] = array();
        
        // Sort the hashes into the return-array
        foreach ($data as $index => $row) {
            // We ignore the headerline 
            if ($index == 0) {
                continue;
            }
            
            if (substr($row, 1, 1) == '+') {
                $return['add'][] = substr($row, 2);
            }
            if (substr($row, 1, 1) == '-') {
                $return['drop'][] = substr($row, 2);
            }
        }
        
        // We are done! 
        return $return;
    }
    
    public function getBlackList($baseVersion = 1) 
    {
        return $this->_fetchData($this->blackListUrl, $baseVersion);
    }
    
    public function getMalwareList($baseVersion = 1) 
    {
        return $this->_fetchData($this->malwareListUrl, $baseVersion);
    }
}