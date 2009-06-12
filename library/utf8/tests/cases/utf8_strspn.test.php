<?php
/**
* @version $Id: utf8_strspn.test.php,v 1.2 2006/02/25 14:52:18 harryf Exp $
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
require_once UTF8 . '/strspn.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strspn extends UnitTestCase {

    function test_utf8_strspn () {
        $this->UnitTestCase('test_utf8_strspn()');
    }
    
    function testMatch() {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strspn($str,'âëiônñrt'),11);
    }
    
    function testMatchTwo() {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strspn($str,'iñtë'),4);
    }
    
    function testCompareStrspn() {
        $str = 'aeioustr';
        $this->assertEqual(utf8_strspn($str,'saeiou'),strspn($str,'saeiou'));
    }
    
    function testMatchAscii() {
        $str = 'internationalization';
        $this->assertEqual(utf8_strspn($str,'aeionrt'),strspn($str,'aeionrt'));
    }
    
    function testLinefeed() {
        $str = "iñtërnât\niônàlizætiøn";
        $this->assertEqual(utf8_strspn($str,'âëiônñrt'),8);
    }
    
    function testLinefeedMask() {
        $str = "iñtërnât\niônàlizætiøn";
        $this->assertEqual(utf8_strspn($str,"âëiônñrt\n"),12);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_strspn ();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
