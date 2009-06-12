<?php
/**
* @version $Id: utf8_ord.test.php,v 1.1 2006/03/26 22:26:16 harryf Exp $
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
require_once UTF8 . '/ord.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_ord extends UnitTestCase {

    function test_utf8_ord() {
        $this->UnitTestCase('utf8_ord()');
    }
    
    function testEmptyStr() {
        $str = '';
        $this->assertEqual(utf8_ord($str),0);
    }
    
    function testAsciiChar() {
        $str = 'a';
        $this->assertEqual(utf8_ord($str),97);
    }
    
    function test2ByteChar() {
        $str = 'ñ';
        $this->assertEqual(utf8_ord($str),241);
    }
    
    function test3ByteChar() {
        $str = '₧';
        $this->assertEqual(utf8_ord($str),8359);
    }
    
    function test4ByteChar() {
        $str = "\xf0\x90\x8c\xbc";
        $this->assertEqual(utf8_ord($str),66364);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_ord();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
