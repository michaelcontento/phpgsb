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
class Google_Safebrowsing_Url
{
	/**
	 * @var string
	 */
	private $_orginUrl;

	/**
	 * @var string
	 */
	private $_canonicalizedUrl;

	/**
	 * @var array
	 */
	private $_splittedUrl = array();

    /**
     * @return void
     */
    private function _removeLeadingAndTrailingSpaces()
    {
        $this->_canonicalizedUrl = trim($this->_canonicalizedUrl);
    }

	/**
	 * @return void
	 */
	private function _removeTabCrAndLf()
	{
        $this->_canonicalizedUrl = str_replace(
            array(
                "\t", "\n", "\r", '\t', '\n', '\r'
            ),
            '',
            $this->_canonicalizedUrl
        );
	}

    /**
     * @return void
     */
    private function _repeatedlyDecode()
    {
    	do {
	        $oldUrl = preg_replace(
	            '#(.*)\\\x([0-9]{2,2})(.*)#iU',
	            '\1%\2\3',
	            $this->_canonicalizedUrl
	        );
	        $this->_canonicalizedUrl = urldecode($oldUrl);
    	} while ($oldUrl != $this->_canonicalizedUrl);
    }

    /**
     * @return void
     */
    private function _removeFragment()
    {
    	$fragmentPos = strpos($this->_canonicalizedUrl, '#');
    	if ($fragmentPos !== false) {
            $this->_canonicalizedUrl = substr($this->_canonicalizedUrl, 0, $fragmentPos);
    	}
    }

    /**
     * @return void
     */
    private function _lowercase()
    {
        $this->_canonicalizedUrl = strtolower($this->_canonicalizedUrl);
    }

    /**
     * @return void
     */
    private function _splitUrlIntoParts()
    {
        $this->_splittedUrl = parse_url($this->_canonicalizedUrl);

        if (empty($this->_splittedUrl['scheme'])) {
        	$this->_splittedUrl['scheme'] = 'http';
        }

        if (!isset($this->_splittedUrl['query'])
        && substr($this->_canonicalizedUrl, -1) == '?') {
        	$this->_splittedUrl['query'] = '';
        }

        if (!isset($this->_splittedUrl['path'])) {
        	$this->_splittedUrl['path'] = '/';
        }

        if (empty($this->_splittedUrl['host'])) {
        	$this->_splittedUrl['host'] = rtrim($this->_splittedUrl['path'], '/');
        	$this->_splittedUrl['path'] = '/';
        }

        var_dump($this->_splittedUrl);
    }

    /**
     * @return void
     */
    private function _hostRemoveLeadingAndTrailingDots()
    {
        $this->_splittedUrl['host'] = trim($this->_splittedUrl['host'], '.');
    }

    /**
     * @return void
     */
    private function _hostReplaceConsecutiveDots()
    {
    	do {
    		$oldHost = $this->_splittedUrl['host'];
            $this->_splittedUrl['host'] = str_replace('..', '.', $oldHost);
    	} while ($oldHost != $this->_splittedUrl['host']);
    }

    /**
     * @return void
     */
    private function _hostNormalizeIpAddress()
    {
        // TODO Implement ip address normalization
    }

    /**
     * @return void
     */
    private function _pathNormalizeDotPaths()
    {
    	$this->_splittedUrl['path'] = str_replace('/./', '/', $this->_splittedUrl['path']);

    	// removing "/../" along with the preceding path component
        $this->_splittedUrl['path'] = preg_replace(
            '#(.*)/([^/]+)/\.\.(/|)(.*)#i',
            '\1/\4',
            $this->_splittedUrl['path']
        );

        // the regex does not match '/../foo/bar' -> fix this with str_replace
        $this->_splittedUrl['path'] = str_replace('/../', '', $this->_splittedUrl['path']);
    }

    /**
     * @return void
     */
    private function _pathReplaceConsecutiveSlashes()
    {
        do {
            $oldPath = $this->_splittedUrl['path'];
            $this->_splittedUrl['path'] = str_replace('//', '/', $oldPath);
        } while ($oldPath != $this->_splittedUrl['path']);
    }

    /**
     * @return void
     */
    private function _pathTrailingSlashes()
    {
        //$this->_splittedUrl['path'] = rtrim($this->_splittedUrl['path'], '/');

        if (empty($this->_splittedUrl['path'])) {
        	$this->_splittedUrl['path'] = '/';
        }
    }

    /**
     * @return void
     */
    private function _mergeUrlParts()
    {
    	$userAndPass = '';
    	if (!empty($this->_splittedUrl['user'])) {
    		$userAndPass .= $this->_splittedUrl['user'];
    	}
        if (!empty($this->_splittedUrl['pass'])) {
            $userAndPass .= ':' . $this->_splittedUrl['pass'];
        }
        if (!empty($userAndPass)) {
            $userAndPass .= '@';
        }

        $port = '';
        if (!empty($this->_splittedUrl['port'])) {
            $port .= ':' . $this->_splittedUrl['port'];
        }

    	$this->_canonicalizedUrl = $this->_splittedUrl['scheme']
    	                         . '://'
                                 . $userAndPass
                                 . $this->_splittedUrl['host']
                                 . $port
                                 . $this->_splittedUrl['path'];

        if (isset($this->_splittedUrl['query'])) {
            $this->_canonicalizedUrl .= '?' . $this->_splittedUrl['query'];
        }
    }

    /**
     * @return void
     */
    private function _percentEscape()
    {
    	foreach (array_keys($this->_splittedUrl) as $key) {
	        $this->_splittedUrl[$key] = urlencode($this->_splittedUrl[$key]);
	        $this->_splittedUrl[$key] = str_replace(
	            array('%3A', '%2F', '%3F', '%3B', '+', '%3D'),
	            array(':', '/', '?', ';', '%20', '='),
	            $this->_splittedUrl[$key]
	        );
    	}
    }

	/**
	 * @return void
	 */
	private function _parse()
	{
		echo "--[ PARSE ]--\n";
		$this->_canonicalizedUrl = $this->_orginUrl;
        echo "original: $this->_canonicalizedUrl\n";

        // Global
        $this->_removeLeadingAndTrailingSpaces();
        echo "_removeLeadingAndTrailingSpaces: $this->_canonicalizedUrl\n";
        $this->_removeTabCrAndLf();
        echo "_removeTabCrAndLf: $this->_canonicalizedUrl\n";
        $this->_repeatedlyDecode();
        echo "_repeatedlyDecode: $this->_canonicalizedUrl\n";
        $this->_lowercase();
        echo "_lowercase: $this->_canonicalizedUrl\n";

        // Split the url into parts for further processing
        $this->_splitUrlIntoParts();

        // Canonicalize host
        echo "\norignalHost: " . $this->_splittedUrl['host'] . "\n";
        $this->_hostRemoveLeadingAndTrailingDots();
        echo "_hostRemoveLeadingAndTrailingDots: " . $this->_splittedUrl['host'] . "\n";
        $this->_hostReplaceConsecutiveDots();
        echo "_hostReplaceConsecutiveDots: " . $this->_splittedUrl['host'] . "\n";
        $this->_hostNormalizeIpAddress();
        echo "_hostNormalizeIpAddress: " . $this->_splittedUrl['host'] . "\n";

        // Canonicalze path
        echo "\norignalPath: " . $this->_splittedUrl['path'] . "\n";
        $this->_pathNormalizeDotPaths();
        echo "_pathNormalizeDotPaths: " . $this->_splittedUrl['path'] . "\n";
        $this->_pathReplaceConsecutiveSlashes();
        echo "_pathReplaceConsecutiveSlashes: " . $this->_splittedUrl['path'] . "\n";
        $this->_pathTrailingSlashes();
        echo "_pathTrailingSlashes: " . $this->_splittedUrl['path'] . "\n";

        // Finally we ecape everything and merge everything
        echo "\n";
        $this->_percentEscape();
        $this->_mergeUrlParts();
        echo "_percentEscape + _mergeUrlParts: $this->_canonicalizedUrl\n";
        $this->_splitUrlIntoParts();
	}

	/**
	 * @param string $url
	 */
    public function __construct($url)
    {
    	$this->_orginUrl = $url;
    	$this->_parse();
    }

    /**
     * @return string
     */
    public function getCanonicalized()
    {
  		return $this->_canonicalizedUrl;
    }
}