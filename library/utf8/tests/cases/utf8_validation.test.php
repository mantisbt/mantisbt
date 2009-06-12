<?php
/**
* @version $Id: utf8_validation.test.php,v 1.6 2006/02/26 13:39:37 harryf Exp $
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
require_once UTF8 . '/utils/validation.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_is_valid extends UnitTestCase {

    function test_utf8_is_valid() {
        $this->UnitTestCase('utf8_is_valid()');
    }
    
    function testValidUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertTrue(utf8_is_valid($str));
    }
    
    function testValidUtf8Ascii() {
        $str = 'ABC 123';
        $this->assertTrue(utf8_is_valid($str));
    }
    
    function testInvalidUtf8() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testInvalidUtf8Ascii() {
        $str = "this is an invalid char '\xe9' here";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testEmptyString() {
        $str = '';
        $this->assertTrue(utf8_is_valid($str));
    }
    
    function testValidTwoOctetId() {
        $str = "\xc3\xb1";
        $this->assertTrue(utf8_is_valid($str));
    }
    
    function testInvalidTwoOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testInvalidIdBetweenTwoAndThree() {
        $str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testValidThreeOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_is_valid($str));
    }
    
    function testInvalidThreeOctetSequenceSecond() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testInvalidThreeOctetSequenceThird() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testValidFourOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_is_valid($str));
    }
    
    function testInvalidFourOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testInvalidFiveOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
    function testInvalidSixOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($str));
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_compliant extends UnitTestCase {

    function test_utf8_compliant() {
        $this->UnitTestCase('utf8_compliant()');
    }
    
    function testValidUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertTrue(utf8_compliant($str));
    }
    
    function testValidUtf8Ascii() {
        $str = 'ABC 123';
        $this->assertTrue(utf8_compliant($str));
    }
    
    function testInvalidUtf8() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertFalse(utf8_compliant($str));
    }
    
    function testInvalidUtf8Ascii() {
        $str = "this is an invalid char '\xe9' here";
        $this->assertFalse(utf8_compliant($str));
    }
    
    function testEmptyString() {
        $str = '';
        $this->assertTrue(utf8_compliant($str));
    }
    
    function testValidTwoOctetId() {
        $str = "\xc3\xb1";
        $this->assertTrue(utf8_compliant($str));
    }
    
    function testInvalidTwoOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_compliant($str));
    }
    
    function testInvalidIdBetweenTwoAndThree() {
        $str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_compliant($str));
    }
    
    function testValidThreeOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_compliant($str));
    }
    
    function testInvalidThreeOctetSequenceSecond() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_compliant($str));
    }
    
    function testInvalidThreeOctetSequenceThird() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_compliant($str));
    }
    
    function testValidFourOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_compliant($str));
    }
    
    function testInvalidFourOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_compliant($str));
    }
    
    function testInvalidFiveOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_compliant($str));
    }
    
    function testInvalidSixOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_compliant($str));
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new GroupTest('utf8_validation');
    $test->addTestCase(new test_utf8_is_valid());
    $test->addTestCase(new test_utf8_compliant());
    $reporter = & getTestReporter();
    $test->run($reporter);
}
