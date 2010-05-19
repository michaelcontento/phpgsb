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

License
-------

    Copyright 2009-2010 Michael Contento <michaelcontento@gmail.com>

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

        http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
