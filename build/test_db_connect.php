<?php
include 'vendor/autoload.php';

$f_db_type = getenv( 'DB_TYPE' );
$f_hostname = getenv( 'DB_HOSTNAME' );
$f_admin_username = getenv( 'DB_USER' );
$f_admin_password = (string)getenv( 'DB_PASSWORD' );

[$host, $port] = explode(':', $f_hostname);
$str = sprintf("host='%s' port='%d' user='%s' password='%s'",
	addcslashes($host, "'\\"),
	$port,
	addcslashes($f_admin_username, "'\\"),
	addcslashes($f_admin_password, "'\\")
);
var_dump($str, pg_connect( $str ) );

$g_db = ADONewConnection( $f_db_type );
$g_db->debug = true;
$t_result = $g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password );

var_dump($t_result);
echo $g_db->ErrorMsg();
die(1);
