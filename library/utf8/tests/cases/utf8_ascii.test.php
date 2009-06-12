<?php
/**
* @version $Id: utf8_ascii.test.php,v 1.9 2006/10/17 08:53:37 harryf Exp $
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
require_once UTF8 . '/utils/ascii.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_is_ascii extends UnitTestCase {

    function test_utf8_is_ascii() {
        $this->UnitTestCase('test_utf8_is_ascii()');
    }
    
    function testUtf8() {
        $str = 'testiñg';
        $this->assertFalse(utf8_is_ascii($str));
    }
    
    function testAscii() {
        $str = 'testing';
        $this->assertTrue(utf8_is_ascii($str));
    }
    
    function testInvalidChar() {
        $str = "tes\xe9ting";
        $this->assertFalse(utf8_is_ascii($str));
    }
    
    function testEmptyStr() {
        $str = '';
        $this->assertTrue(utf8_is_ascii($str));
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strip_non_ascii extends UnitTestCase {

    function test_utf8_strip_non_ascii() {
        $this->UnitTestCase('test_utf8_strip_non_ascii()');
    }
    
    function testUtf8() {
        $str = 'testiñg';
        $this->assertEqual(utf8_strip_non_ascii($str),'testig');
    }
    
    function testAscii() {
        $str = 'testing';
        $this->assertEqual(utf8_strip_non_ascii($str),'testing');
    }
    
    function testInvalidChar() {
        $str = "tes\xe9ting";
        $this->assertEqual(utf8_strip_non_ascii($str),'testing');
    }
    
    function testEmptyStr() {
        $str = '';
        $this->assertEqual(utf8_strip_non_ascii($str),'');
    }
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strip_non_ascii_ctrl extends UnitTestCase{

    function test_utf8_strip_non_ascii_ctrl() {
        $this->UnitTestCase('test_utf8_strip_non_ascii_ctrl');
    }
    
    function testNulAndNon7Bit() {
        $str = "a\x00ñ\x00c";
        $this->assertEqual(utf8_strip_non_ascii_ctrl($str),'ac');
    }

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strip_ascii_ctrl extends UnitTestCase{

    function test_utf8_strip_ascii_ctrl() {
        $this->UnitTestCase('test_utf8_strip_ascii_ctrl');
    }
    
    function testNul() {
        $str = "a\x00b\x00c";
        $this->assertEqual(utf8_strip_ascii_ctrl($str),'abc');
    }

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_accents_to_ascii extends UnitTestCase{

    function test_utf8_accents_to_ascii() {
        $this->UnitTestCase('test_utf8_accents_to_ascii');
    }
    
    function testEmptyStr() {
        $this->assertEqual(utf8_accents_to_ascii(''),'');
    }
    
    function testLowercase() {
        $str = "ô";
        $this->assertEqual(utf8_accents_to_ascii($str,-1),'o');
    }
    
    function testUppercase() {
        $str = "Ô";
        $this->assertEqual(utf8_accents_to_ascii($str,1),'O');
    }
    
    function testBoth() {
        $str = "ôÔ";
        $this->assertEqual(utf8_accents_to_ascii($str,0),'oO');
    }

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new GroupTest('utf8_ascii');
    $test->addTestCase(new test_utf8_is_ascii());
    $test->addTestCase(new test_utf8_strip_non_ascii());
    $test->addTestCase(new test_utf8_strip_non_ascii_ctrl());
    $test->addTestCase(new test_utf8_strip_ascii_ctrl());
    $test->addTestCase(new test_utf8_accents_to_ascii());
    $reporter = & getTestReporter();
    $test->run($reporter);
}
