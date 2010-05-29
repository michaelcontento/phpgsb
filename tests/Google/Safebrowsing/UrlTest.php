<?php

/**
 *  Copyright 2009-2010 Michael Contento <michaelcontento@gmail.com>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License');
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
class Google_Safebrowsing_UrlTest extends PHPUnit_Framework_TestCase
{
    public function testGoogleIpHostTests()
    {
    	$this->markTestSkipped('ip normalization not implemented yet');

        $url = new Google_Safebrowsing_Url('http://3279880203/blah');
        $this->assertEquals('http://195.127.0.11/blah', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http://%31%36%38%2e%31%38%38%2e%39%39%2e%32%36/%2E%73%65%63%75%72%65/%77%77%77%2E%65%62%61%79%2E%63%6F%6D/');
        $this->assertEquals('http://168.188.99.26/.secure/www.ebay.com/', $url->getCanonicalized());
    }

	public function testGoogleAlnumHostTests()
	{
        $url = new Google_Safebrowsing_Url('http://www.google.com/');
        $this->assertEquals('http://www.google.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('www.google.com/');
        $this->assertEquals('http://www.google.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('www.google.com');
        $this->assertEquals('http://www.google.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http://www.GOOgle.com/');
        $this->assertEquals('http://www.google.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http://www.google.com.../');
        $this->assertEquals('http://www.google.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http://\x81\x80.com/');
        $this->assertEquals('http://%81%80.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http://notrailingslash.com');
        $this->assertEquals('http://notrailingslash.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http://www.gotaport.com:1234/');
        $this->assertEquals('http://www.gotaport.com:1234/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('  http://www.google.com/  ');
        $this->assertEquals('http://www.google.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http:// leadingspace.com/');
        $this->assertEquals('http://%20leadingspace.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('http://%20leadingspace.com/');
        $this->assertEquals('http://%20leadingspace.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('%20leadingspace.com/');
        $this->assertEquals('http://%20leadingspace.com/', $url->getCanonicalized());

        $url = new Google_Safebrowsing_Url('https://www.securesite.com/');
        $this->assertEquals('https://www.securesite.com/', $url->getCanonicalized());
	}

    public function testGooglePathTests()
    {
    	$url = new Google_Safebrowsing_Url('http://host/%25%32%35');
		$this->assertEquals('http://host/%25', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://host/%25%32%35%25%32%35');
		$this->assertEquals('http://host/%25%25', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://host/%2525252525252525');
		$this->assertEquals('http://host/%25', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://host/asdf%25%32%35asd');
		$this->assertEquals('http://host/asdf%25asd', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://host/%%%25%32%35asd%%');
		$this->assertEquals('http://host/%25%25%25asd%25%25', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://195.127.0.11/uploads/%20%20%20%20/.verify/.eBaysecure=updateuserdataxplimnbqmn-xplmvalidateinfoswqpcmlx=hgplmcx/');
		$this->assertEquals('http://195.127.0.11/uploads/%20%20%20%20/.verify/.ebaysecure=updateuserdataxplimnbqmn-xplmvalidateinfoswqpcmlx=hgplmcx/', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://www.google.com/blah/..');
		$this->assertEquals('http://www.google.com/', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://www.evil.com/blah#frag');
		$this->assertEquals('http://www.evil.com/blah', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://www.google.com/foo\tbar\rbaz\n2');
		$this->assertEquals('http://www.google.com/foobarbaz2', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://www.google.com/q?');
		$this->assertEquals('http://www.google.com/q?', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://www.google.com/q?r?');
		$this->assertEquals('http://www.google.com/q?r?', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://www.google.com/q?r?s');
		$this->assertEquals('http://www.google.com/q?r?s', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://evil.com/foo#bar#baz');
		$this->assertEquals('http://evil.com/foo', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://evil.com/foo;');
		$this->assertEquals('http://evil.com/foo;', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://evil.com/foo?bar;');
		$this->assertEquals('http://evil.com/foo?bar;', $url->getCanonicalized());

		$url = new Google_Safebrowsing_Url('http://host.com//twoslashes?more//slashes');
		$this->assertEquals('http://host.com/twoslashes?more//slashes', $url->getCanonicalized());
    }
}
