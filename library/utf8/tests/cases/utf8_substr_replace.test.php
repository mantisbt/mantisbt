<?php
/**
* @version $Id: utf8_substr_replace.test.php,v 1.3 2006/10/17 09:22:14 harryf Exp $
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
require_once UTF8 . '/substr_replace.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_native_substr_replace extends UnitTestCase {

    function test_utf8_native_substr_replace() {
        $this->UnitTestCase('utf8_str_split()');
    }
    
    function testReplaceStart() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'IñtërnâtX';
        $this->assertEqual(utf8_substr_replace($str,'X',8),$replaced);
    }
    
    function testEmptyString() {
        $str = '';
        $replaced = 'X';
        $this->assertEqual(utf8_substr_replace($str,'X',8),$replaced);
    }
    
    function testNegative() {
        $str = 'testing';
        $replaced = substr_replace($str,'foo',-2,-2);
        $this->assertEqual(utf8_substr_replace($str,'foo',-2,-2),$replaced);
    }
    
    function testZero() {
        $str = 'testing';
        $replaced = substr_replace($str,'foo',0,0);
        $this->assertEqual(utf8_substr_replace($str,'foo',0,0),$replaced);
    }
    
    function testLinefeed() {
        $str = "Iñ\ntërnâtiônàlizætiøn";
        $replaced = "Iñ\ntërnâtX";
        $this->assertEqual(utf8_substr_replace($str,'X',9),$replaced);
    }
    
    function testLinefeedReplace() {
        $str = "Iñ\ntërnâtiônàlizætiøn";
        $replaced = "Iñ\ntërnâtX\nY";
        $this->assertEqual(utf8_substr_replace($str,"X\nY",9),$replaced);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_native_substr_replace();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
