<?php
//--------------------------------------------------------------------
if ( !defined('UTF8') ) {
    define('UTF8',realpath(dirname(__FILE__).'/../'));
}

if ( !defined('UTF8DATA') ) {
    define('UTF8DATA',dirname(__FILE__).'/data');
}



if ( !isset($_GET['engine']) ) {
    $_GET['engine'] = 'auto';
}

/**
* Testing against a particular "engine"
*/
switch ( $_GET['engine'] ) {
    case 'mbstring';
        if ( !function_exists('mb_strtolower') ) {
            trigger_error("mbstring extension not detected on your system!", E_USER_ERROR );
        }
        mb_internal_encoding('UTF-8');
        require_once UTF8 . '/mbstring/core.php';
    break;
    case 'native':
        require_once UTF8 . '/utils/unicode.php';
        require_once UTF8 . '/native/core.php';
    break;
}

require_once UTF8 . '/utf8.php';
