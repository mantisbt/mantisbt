<?php
require_once dirname(__FILE__).'/../benchconfig.php';
print utf8_strlen(file_get_contents(UTF8DATA . '/utf8.html' ))."\n";
?>
