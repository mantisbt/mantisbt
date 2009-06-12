<?php
/**
* @version $Id: utf8_strtoupper.test.php,v 1.4 2006/10/17 09:22:14 harryf Exp $
* @package utf8
* @subpackage Tests
*/

//--------------------------------------------------------------------
/**
* Includes
* @package utf8
* @subpackage Tests
*/
require_once(dirname(__FILE__).'/../config.php');

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strtoupper extends UnitTestCase {

    function test_utf8_strtoupper() {
        $this->UnitTestCase('utf8_strtoupper()');
    }
    
    function testUpper() {
        $str = 'iñtërnâtiônàlizætiøn';
        $upper = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertEqual(utf8_strtoupper($str),$upper);
    }
    
    function testEmptyString() {
        $str = '';
        $upper = '';
        $this->assertEqual(utf8_strtoupper($str),$upper);
    }
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_strtoupper();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
