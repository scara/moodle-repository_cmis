CMIS repository plug-in
=======================
This is a CMIS repository plug-in for Moodle.
Description
-----------
It allows you to browse, search, download and use public files from a server,
tipically a CMS, exposing its content by means of CMIS 1.0.  

It also gets benefits from the [MUC framework](http://docs.moodle.org/dev/The_Moodle_Universal_Cache_%28MUC%29) to implement two levels of cache,
at the folder level:  
1. CMIS identifiers;  
2. children and search result sets.  

Cache can be purged perfoming a search with the special keyword: `*purgecache*`.

TODO
----
* Verify/Implement the correct support for Microsoft Sharepoint 2013 via CMIS.
* Restrict cache purging to administrators.
* Make search configurable: none, within the folder, down the directory tree.
* Evaluate the need for FILE_REFERENCE support.
* Code cleanup.


Known issues
------------
* It supports only RESTful AtomPub endpoints (Atom Publishing Protocol).
* It supports only CMIS 1.0 compliant repositories.
* Tested just with Alfresco Community Edition.


License
-------
GNU GPL v3  
Copyright (c) 2013 Matteo Scaramuccia

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


---


External libraries
------------------
### [Apache Chemistry CMIS PHP Client](http://chemistry.apache.org/php/phpclient.html)
Apache Chemistry CMIS PHP Client is a CMIS client library for PHP.  
The code is available at: https://svn.apache.org/repos/asf/chemistry/phpclient/.  

More information can be found on the following pages:  
* [Current Project Status](http://chemistry.apache.org/php/currentprojectstatus.html)  
* [Function Coverage](http://chemistry.apache.org/php/phpfunctioncoverage.html)  
* [Test Suite Description](http://chemistry.apache.org/php/testsuitedescription.html)  

[Apache Chemistry](http://chemistry.apache.org/) provides open source implementations
of the [Content Management Interoperability Services (CMIS) specification](http://docs.oasis-open.org/cmis/CMIS/v1.0/cmis-spec-v1.0.html).  

#### License
Apache License
Version 2.0, January 2004
http://www.apache.org/licenses/
##### Apache License v2.0 and GPL Compatibility
http://www.apache.org/licenses/GPL-compatibility.html
