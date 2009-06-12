<?php
/**
* @version $Id: utf8_stristr.test.php,v 1.2 2006/02/25 14:52:18 harryf Exp $
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
require_once UTF8 . '/stristr.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_stristr extends UnitTestCase {

    function test_utf8_stristr () {
        $this->UnitTestCase('test_utf8_stristr()');
    }
    
    function testSubstr() {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = 'NÂT';
        $this->assertEqual(utf8_stristr($str,$search),'nâtiônàlizætiøn');
    }
    
    function testSubstrNoMatch() {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = 'foo';
        $this->assertFalse(utf8_stristr($str,$search));
    }
    
    function testEmptySearch() {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = '';
        $this->assertEqual(utf8_stristr($str,$search),'iñtërnâtiônàlizætiøn');
    }
    
    function testEmptyStr() {
        $str = '';
        $search = 'NÂT';
        $this->assertFalse(utf8_stristr($str,$search));
    }
    
    function testEmptyBoth() {
        $str = '';
        $search = '';
        $this->assertEqual(utf8_stristr($str,$search),'');
    }
    
    function testLinefeedStr() {
        $str = "iñt\nërnâtiônàlizætiøn";
        $search = 'NÂT';
        $this->assertEqual(utf8_stristr($str,$search),'nâtiônàlizætiøn');
    }
    
    function testLinefeedBoth() {
        $str = "iñtërn\nâtiônàlizætiøn";
        $search = "N\nÂT";
        $this->assertEqual(utf8_stristr($str,$search),"n\nâtiônàlizætiøn");
    }
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_stristr ();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
