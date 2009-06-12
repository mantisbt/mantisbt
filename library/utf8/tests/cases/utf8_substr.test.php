<?php
/**
* @version $Id: utf8_substr.test.php,v 1.5 2006/09/30 22:51:00 harryf Exp $
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
class test_utf8_substr extends UnitTestCase {

    function test_utf8_substr() {
        $this->UnitTestCase('utf8_substr()');
    }
    
    function testUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,0,2),'Iñ');
    }
    
    function testUtf8Two() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,2,2),'të');
    }
    
    function testUtf8Zero() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,0),'Iñtërnâtiônàlizætiøn');
    }
    
    function testUtf8ZeroZero() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,0,0),'');
    }
    
    function testStartGreatThanLength() {
        $str = 'Iñt';
        $this->assertFalse(utf8_substr($str,4));
    }
    
    function testCompareStartGreatThanLength() {
        $str = 'abc';
        $this->assertEqual(utf8_substr($str,4),substr($str,4));
    }
    
    function testLengthBeyondString() {
        $str = 'Iñt';
        $this->assertEqual(utf8_substr($str,1,5),'ñt');
    }
    
    function testCompareLengthBeyondString() {
        $str = 'abc';
        $this->assertEqual(utf8_substr($str,1,5),substr($str,1,5));
    }
    
    function testStartNegative() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,-4), 'tiøn');
    }
    
    function testLengthNegative() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,10,-2), 'nàlizæti');
    }
    
    function testStartLengthNegative() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,-4,-2), 'ti');
    }
    
    function testLinefeed() {
        $str = "Iñ\ntërnâtiônàlizætiøn";
        $this->assertEqual(utf8_substr($str,1,5),"ñ\ntër");
    }
    
    function testLongLength() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_substr($str,0,15536),'Iñtërnâtiônàlizætiøn');
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = & new test_utf8_substr();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
