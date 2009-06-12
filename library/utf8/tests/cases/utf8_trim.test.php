<?php
/**
* @version $Id: utf8_trim.test.php,v 1.2 2006/02/25 14:52:18 harryf Exp $
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
require_once UTF8 . '/trim.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_ltrim extends UnitTestCase {

    function test_utf8_ltrim() {
        $this->UnitTestCase('test_utf8_ltrim()');
    }
    
    function testTrim() {
        $str = 'ñtërnâtiônàlizætiøn';
        $trimmed = 'tërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ltrim($str,'ñ'),$trimmed);
    }
    
    function testNoTrim() {
        $str = ' Iñtërnâtiônàlizætiøn';
        $trimmed = ' Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ltrim($str,'ñ'),$trimmed);
    }
    
    function testEmptyString() {
        $str = '';
        $trimmed = '';
        $this->assertEqual(utf8_ltrim($str),$trimmed);
    }
    
    function testForwardSlash() {
        $str = '/Iñtërnâtiônàlizætiøn';
        $trimmed = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ltrim($str,'/'),$trimmed);
    }
    
    function testNegateCharClass() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $trimmed = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ltrim($str,'^s'),$trimmed);
    }
    
    function testLinefeed() {
        $str = "ñ\nñtërnâtiônàlizætiøn";
        $trimmed = "\nñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_ltrim($str,'ñ'),$trimmed);
    }
    
    function testLinefeedMask() {
        $str = "ñ\nñtërnâtiônàlizætiøn";
        $trimmed = "tërnâtiônàlizætiøn";
        $this->assertEqual(utf8_ltrim($str,"ñ\n"),$trimmed);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_rtrim extends UnitTestCase {

    function test_utf8_rtrim() {
        $this->UnitTestCase('test_utf8_rtrim()');
    }
    
    function testTrim() {
        $str = 'Iñtërnâtiônàlizætiø';
        $trimmed = 'Iñtërnâtiônàlizæti';
        $this->assertEqual(utf8_rtrim($str,'ø'),$trimmed);
    }
    
    function testNoTrim() {
        $str = 'Iñtërnâtiônàlizætiøn ';
        $trimmed = 'Iñtërnâtiônàlizætiøn ';
        $this->assertEqual(utf8_rtrim($str,'ø'),$trimmed);
    }
    
    function testEmptyString() {
        $str = '';
        $trimmed = '';
        $this->assertEqual(utf8_rtrim($str),$trimmed);
    }
    
    function testLinefeed() {
        $str = "Iñtërnâtiônàlizætiø\nø";
        $trimmed = "Iñtërnâtiônàlizætiø\n";
        $this->assertEqual(utf8_rtrim($str,'ø'),$trimmed);
    }
    
    function testLinefeedMask() {
        $str = "Iñtërnâtiônàlizætiø\nø";
        $trimmed = "Iñtërnâtiônàlizæti";
        $this->assertEqual(utf8_rtrim($str,"ø\n"),$trimmed);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_trim extends UnitTestCase {

    function test_utf8_trim() {
        $this->UnitTestCase('test_utf8_trim()');
    }
    
    function testTrim() {
        $str = 'ñtërnâtiônàlizætiø';
        $trimmed = 'tërnâtiônàlizæti';
        $this->assertEqual(utf8_trim($str,'ñø'),$trimmed);
    }
    
    function testNoTrim() {
        $str = ' Iñtërnâtiônàlizætiøn ';
        $trimmed = ' Iñtërnâtiônàlizætiøn ';
        $this->assertEqual(utf8_trim($str,'ñø'),$trimmed);
    }
    
    function testEmptyString() {
        $str = '';
        $trimmed = '';
        $this->assertEqual(utf8_trim($str),$trimmed);
    }
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = & new GroupTest('utf8_trim tests');
    $test->addTestCase(new test_utf8_ltrim());
    $test->addTestCase(new test_utf8_rtrim());
    $test->addTestCase(new test_utf8_trim());
    $reporter = & getTestReporter();
    $test->run($reporter);
}
