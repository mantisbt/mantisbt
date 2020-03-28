MantisBT external libraries
===========================

This directory contains a copy the 3rd-party libraries used by MantisBT.

The version and status of each is summarized below:

## PHP libraries

directory       | project         | version   | status
----------------|-----------------|-----------|---------------
rssbuilder      | RSSBuilder      | 2.2.1     | patched [1][2]


## Javascript/CSS libraries

library / plugin                  | version   | status
----------------------------------|-----------|---------------
jquery                            | 2.2.4     | unpatched
bootstrap                         | 3.3.6     | unpatched
fontawesome                       | 4.7.0     | unpatched
ace-admin theme                   | 1.4.0     | customized
moment.js                         | 2.24.0    | unpatched
bootstrap-datetimepicker          | 4.17.47   | unpatched
dropzone.js                       | 5.5.0     | unpatched
chart.js                          | 2.8.0     | unpatched
chartjs-plugin-colorschemes       | 0.4.0     | unpatched
typeahead.js                      | 1.3.0     | unpatched 
list.js                           | 1.5.0     | unpatched

  
**Notes**

1. removed `__autoload` function
2. fixed SYSTEM NOTICE 'Only variables should be passed by reference'

Upstream projects
-----------------

project         | URL
----------------|--------------------------------------------------------------------
rssbuilder      | http://code.google.com/p/flaimo-php/
jquery          | https://jquery.com/
bootstrap       | http://getbootstrap.com/
fontawesome     | https://fontawesome.com/v4.7.0/
moment.js       | https://momentjs.com/ - https://github.com/moment/moment/
datetimepicker  | https://github.com/Eonasdan/bootstrap-datetimepicker
dropzone.js     | http://www.dropzonejs.com/ - https://github.com/enyo/dropzone
chart.js        | http://www.chartjs.org/ - https://github.com/chartjs/Chart.js
chartjs-plugin-colorschemes | https://github.com/nagix/chartjs-plugin-colorschemes/
typeahead.js    | https://github.com/corejavascript/typeahead.js
list.js         | http://listjs.com/ - https://github.com/javve/list.js
