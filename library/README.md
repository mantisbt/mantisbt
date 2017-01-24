MantisBT external libraries
===========================

This directory contains a copy the 3rd-party libraries used by MantisBT.

The version and status of each is summarized below:

directory       | project         | version   | status
----------------|-----------------|-----------|---------------
adodb           | adodb           | 5.20.9    | unpatched [1]
disposable      | disposable      | 2.1.1     | unpatched [1]
parsedown       | parsedown       | 1.6.1     | unpatched [1]
phpmailer       | PHPMailer       | 5.2.22    | unpatched [1]
rssbuilder      | RSSBuilder      | 2.2.1     | patched [2]
utf8            | phputf8         | 0.5       | unpatched
securimage      | PHP Captcha     | 3.6.5     | patched [1]
list.js         | List.js         | 1.4.1     | unpatched [3]

**Notes**

1. Library is tracked as a *GIT submodule*; refer to the corresponding
   repository for details
2. removed `__autoload` function
3. Javascript or JQuery plugin


Upstream projects
-----------------

project     | URL
------------|--------------------------------------------------------------------
adodb       | http://adodb.sourceforge.net/ - https://github.com/ADOdb/ADOdb
disposable  | http://github.com/vboctor/disposable_email_checker
parsedown   | https://github.com/erusev/parsedown
phpmailer   | https://github.com/PHPMailer/PHPMailer
rssbuilder  | http://code.google.com/p/flaimo-php/
utf8        | http://sourceforge.net/projects/phputf8
secureimage | http://www.phpcaptcha.org/ - https://github.com/mantisbt/securimage
listjs      | http://listjs.com/ - https://github.com/javve/list.js
