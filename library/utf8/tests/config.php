<?php
//--------------------------------------------------------------------
if (!defined('SIMPLE_TEST')) {
    // Should point at SimpleTest (absolute path required with trailing slash)
    define('SIMPLE_TEST', '/home/harryf/simpletest/'); // Use your include path
}

if ( !defined('UTF8') ) {
    define('UTF8',realpath(dirname(__FILE__).'/../'));
}

if ( !defined('UTF8DATA') ) {
    define('UTF8DATA',dirname(__FILE__).'/data');
}

//--------------------------------------------------------------------
// Load SimpleTest and main JPSpan
if ( @include_once SIMPLE_TEST . 'unit_tester.php' ) {
    require_once SIMPLE_TEST . 'mock_objects.php';
    require_once SIMPLE_TEST . 'reporter.php';
} else {
    trigger_error('Unable to load SimpleTest: configure SIMPLE_TEST in config.php');
}

function & getTestReporter() {
    if ( php_sapi_name() != 'cli' ) {
        $R = & new HtmlReporter('UTF-8');
    } else {
        require_once dirname(__FILE__) . '/cli_reporter.php';
        $R = & new CLIReporter();
    }
    return $R;
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
