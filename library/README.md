MantisBT external libraries
===========================

This directory contains a copy the 3rd-party libraries used by MantisBT.

The version and status of each is summarized below:

## PHP libraries

directory       | project         | version   | status
----------------|-----------------|-----------|---------------
adodb           | adodb           | 5.20.9    | unpatched [1]
disposable      | disposable      | 2.1.1     | unpatched [1]
parsedown       | parsedown       | 1.6.1     | unpatched [1]
phpmailer       | PHPMailer       | 5.2.22    | unpatched [1]
rssbuilder      | RSSBuilder      | 2.2.1     | patched [2]
utf8            | phputf8         | 0.5       | unpatched
securimage      | PHP Captcha     | 3.6.5     | patched [1]


## Javascript/CSS libraries

library / plugin                  | version   | status
----------------------------------|-----------|---------------
jquery                            | 2.2.4     | unpatched
bootstrap                         | 3.3.6     | unpatched
fontawesome                       | 4.6.3     | unpatched
ace-admin theme                   | 1.4.0     | customized
moment.js                         | 2.15.2    | unpatched
bootstrap-datetimepicker          | 4.17.43   | unpatched
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
adodb           | http://adodb.sourceforge.net/ - https://github.com/ADOdb/ADOdb
disposable      | http://github.com/vboctor/disposable_email_checker
parsedown       | https://github.com/erusev/parsedown
phpmailer       | https://github.com/PHPMailer/PHPMailer
rssbuilder      | http://code.google.com/p/flaimo-php/
utf8            | http://sourceforge.net/projects/phputf8
secureimage     | http://www.phpcaptcha.org/ - https://github.com/mantisbt/securimage
jquery          | https://jquery.com/
bootstrap       | http://getbootstrap.com/
fontawesome     | http://fontawesome.io/
moment.js       | https://momentjs.com/ - https://github.com/moment/moment/
datetimepicker  | https://github.com/Eonasdan/bootstrap-datetimepicker
dropzone.js     | http://www.dropzonejs.com/ - https://github.com/enyo/dropzone
chart.js        | http://www.chartjs.org/ - https://github.com/chartjs/Chart.js
typeahead.js    | https://github.com/twitter/typeahead.js
list.js         | http://listjs.com/ - https://github.com/javve/list.js
