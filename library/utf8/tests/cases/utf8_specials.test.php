<?php
/**
* @version $Id: utf8_specials.test.php,v 1.2 2006/10/17 09:09:13 harryf Exp $
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
require_once UTF8 . '/utils/unicode.php';
require_once UTF8 . '/utils/specials.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_is_word_chars extends UnitTestCase {

    function test_utf8_is_word_chars() {
        $this->UnitTestCase('test_utf8_is_word_chars()');
    }
    
    function testEmptyString() {
        $this->assertTrue(utf8_is_word_chars(''));
    }
    
    function testAllWordChars() {
        $this->assertTrue(utf8_is_word_chars('HelloWorld'));
    }
    
    function testSpecials() {
        $str = 'Hello ' .
            chr(0xe0 | (0x2234 >> 12)) .
            chr(0x80 | ((0x2234 >> 6) & 0x003f)) .
            chr(0x80 | (0x2234 & 0x003f)) .
            ' World';
        $this->assertFalse(utf8_is_word_chars($str));
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strip_specials extends UnitTestCase {

    function test_utf8_strip_specials() {
        $this->UnitTestCase('test_utf8_strip_specials()');
    }
    
    function testEmptyString() {
        $this->assertEqual(utf8_strip_specials(''),'');
    }
    
    function testStrip() {
        $str = 'Hello ' .
            chr(0xe0 | (0x2234 >> 12)) .
            chr(0x80 | ((0x2234 >> 6) & 0x003f)) .
            chr(0x80 | (0x2234 & 0x003f)) .
            ' World';
        $this->assertEqual(utf8_strip_specials($str),'HelloWorld');
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
    $test->addTestCase(new test_utf8_strip_specials());
    $test->addTestCase(new test_utf8_is_word_chars());
    $reporter = & getTestReporter();
    $test->run($reporter);
}
