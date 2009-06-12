<?php
header('content-type: text/html; charset=utf-8');

# Require the main "loader" script...
require_once '../../utf8.php';

# UTF8 constant can now be used for directory
# Follow functions automatically available;
# utf8_strlen, utf8_strpos, utf8_strrpos, utf8_substr,
# utf8_strtolower, utf8_strtoupper

# A UTF-8 string...
$str = 'Iñtërnâtiônàlizætiøn';

print "String is: $str<br>\n";

# Check it's a well formed UTF-8 string
require_once UTF8 . '/utils/validation.php';
if ( utf8_is_valid($str) ) {
    print "It's well formed UTF-8<br>\n";
} else {
    print "It's badly formed UTF-8 - this shouldn't happen<br>\n";
}

print "Num chars: ".utf8_strlen($str)."<br>\n";

print "Uppercase: ".utf8_strtoupper($str)."<br>\n";

# Load an additional function;
require_once UTF8 . '/strrev.php';

print "Reversed: ".utf8_strrev($str)."<br>\n";
?>