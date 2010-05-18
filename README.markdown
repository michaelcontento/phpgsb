PHP Google Safe Browsing
========================

**phpgsb** is the PHP implementation of the [Goolge Safe Browsing](http://code.google.com/apis/safebrowsing/) (GSB) for PHP.

Requirements
------------

    TBD

Installation
------------

    TBD

Current ToDo-List
-----------------

* Upgrade to GSB verion 2
* MAC validation
* Canonicalized IP
* Check: "remove any tab (0x09), CR (0x0d), and LF (0x0a) characters from the URL"
* Check: "If the URL ends in a fragment, the fragment should be removed"
* Add all tests from the Google documentation [link](http://code.google.com/intl/de-DE/apis/safebrowsing/developers_guide_v2.html#Canonicalization)
* Add default end user warning as [defined](http://code.google.com/intl/de-DE/apis/safebrowsing/developers_guide_v2.html#UserWarnings)
* Remove some slow regular expressions
* Performance profiling
