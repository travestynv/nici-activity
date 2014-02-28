This package provides LTI support for the NICI course participation report.  

PREREQS:

PHP 5+ with CURL and GD libs.  Open source Basic LTI and phPlot libraries are included in this package.

INSTALL:

Uncompress in web accessible directory and fill in uncommented values in /lib/config.php.

Configure canvas external tool using the included XML and the defined key and secret values.

Access report from course navigation bar.

NOTES: 

This report ignores faculty roles. Threshholds for color codes can be redifined in the function in the checkDateException function.  Actual user avatar images are not used as it would effectively double the api calls.  As it is, it takes about 15-30 secs before any results are returned. The script currently handles pagination for large enrollment sections (50+ students) and will iterate through any number of popluated sections within the course. Sections without a SIS ID value are ignored.