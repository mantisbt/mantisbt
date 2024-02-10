<?php
include 'vendor/autoload.php';

$f_db_type = getenv( 'DB_TYPE' );
$f_hostname = getenv( 'DB_HOSTNAME' );
$f_admin_username = getenv( 'DB_USER' );
$f_admin_password = getenv( 'DB_PASSWORD' );

printf("type=%s\nhost=%s\nuser=%s\npass=%s\n\n",
	$f_db_type, $f_hostname, $f_admin_username, $f_admin_password );

$g_db = ADONewConnection( $f_db_type );
$g_db->debug = true;
$t_result = $g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password );

var_dump($t_result);
echo $g_db->ErrorMsg();
die(1);
