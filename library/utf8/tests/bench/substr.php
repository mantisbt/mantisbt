<?php
require_once dirname(__FILE__).'/../benchconfig.php';
$page = file_get_contents(UTF8DATA . '/utf8.html' );
for($i = 0; $i < 37919; $i+= 1000) {
    utf8_substr($page, $i);
}
print "Done\n";
?>
