<?php
/**
* @version $Id: utf8_unicode.test.php,v 1.1 2006/10/17 09:58:29 harryf Exp $
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

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_to_unicode extends UnitTestCase {

    function test_utf8_to_unicode() {
        $this->UnitTestCase('test_utf8_to_unicode()');
    }
    
    function testEmptyString() {
        $this->assertEqual(utf8_to_unicode(''),array());
    }
    
    function testString() {
        
        $unicode = array();
        $unicode[0] = 73;
        $unicode[1] = 241;
        $unicode[2] = 116;
        $unicode[3] = 235;
        $unicode[4] = 114;
        $unicode[5] = 110;
        $unicode[6] = 226;
        $unicode[7] = 116;
        $unicode[8] = 105;
        $unicode[9] = 244;
        $unicode[10] = 110;
        $unicode[11] = 224;
        $unicode[12] = 108;
        $unicode[13] = 105;
        $unicode[14] = 122;
        $unicode[15] = 230;
        $unicode[16] = 116;
        $unicode[17] = 105;
        $unicode[18] = 248;
        $unicode[19] = 110;
        
        $this->assertEqual(utf8_to_unicode('Iñtërnâtiônàlizætiøn'), $unicode);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_from_unicode extends UnitTestCase {

    function test_utf8_strip_specials() {
        $this->UnitTestCase('test_utf8_strip_specials()');
    }
    
    function testEmptyArray() {
        $this->assertEqual(utf8_from_unicode(array()),'');
    }
    
    function testArray() {
        
        $unicode = array();
        $unicode[0] = 73;
        $unicode[1] = 241;
        $unicode[2] = 116;
        $unicode[3] = 235;
        $unicode[4] = 114;
        $unicode[5] = 110;
        $unicode[6] = 226;
        $unicode[7] = 116;
        $unicode[8] = 105;
        $unicode[9] = 244;
        $unicode[10] = 110;
        $unicode[11] = 224;
        $unicode[12] = 108;
        $unicode[13] = 105;
        $unicode[14] = 122;
        $unicode[15] = 230;
        $unicode[16] = 116;
        $unicode[17] = 105;
        $unicode[18] = 248;
        $unicode[19] = 110;
        
        $this->assertEqual(utf8_from_unicode($unicode), 'Iñtërnâtiônàlizætiøn');
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
    $test->addTestCase(new test_utf8_to_unicode());
    $test->addTestCase(new test_utf8_from_unicode());
    $reporter = & getTestReporter();
    $test->run($reporter);
}
