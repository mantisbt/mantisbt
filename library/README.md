MantisBT external libraries
===========================

This directory contains a copy the 3rd-party libraries used by MantisBT.

The version and status of each is summarized below:

## PHP libraries

directory       | project         | version   | status
----------------|-----------------|-----------|---------------
rssbuilder      | RSSBuilder      | 2.2.1     | patched [2]
utf8            | phputf8         | 0.5       | unpatched


## Javascript/CSS libraries

library / plugin                  | version   | status
----------------------------------|-----------|---------------
jquery                            | 2.2.4     | unpatched
bootstrap                         | 3.3.6     | unpatched
fontawesome                       | 4.6.3     | unpatched
ace-admin theme                   | 1.4.0     | customized
moment.js                         | 2.15.2    | unpatched
bootstrap-datetimepicker          | 4.17.47   | unpatched
dropzone.js                       | 4.3.0     | unpatched
chart.js                          | 2.1.6     | unpatched
typeahead.js                      | 1.1.1     | unpatched 
list.js                           | 1.4.1     | unpatched

  
**Notes**

1. Library is tracked as a *GIT submodule*; refer to the corresponding
   repository for details
2. removed `__autoload` function


Upstream projects
-----------------

project         | URL
----------------|--------------------------------------------------------------------
rssbuilder      | http://code.google.com/p/flaimo-php/
utf8            | http://sourceforge.net/projects/phputf8
jquery          | https://jquery.com/
bootstrap       | http://getbootstrap.com/
fontawesome     | http://fontawesome.io/
moment.js       | https://momentjs.com/ - https://github.com/moment/moment/
datetimepicker  | https://github.com/Eonasdan/bootstrap-datetimepicker
dropzone.js     | http://www.dropzonejs.com/ - https://github.com/enyo/dropzone
chart.js        | http://www.chartjs.org/ - https://github.com/chartjs/Chart.js
typeahead.js    | https://github.com/twitter/typeahead.js
list.js         | http://listjs.com/ - https://github.com/javve/list.js
