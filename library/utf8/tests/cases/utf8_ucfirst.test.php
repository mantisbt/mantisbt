<?php
/**
* @version $Id: utf8_ucfirst.test.php,v 1.3 2006/02/26 13:07:42 harryf Exp $
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
require_once UTF8 . '/ucfirst.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_ucfirst extends UnitTestCase {

    function test_utf8_ucfirst() {
        $this->UnitTestCase('test_utf8_ucfirst()');
    }
    
    function testUcfirst() {
        $str = 'ñtërnâtiônàlizætiøn';
        $ucfirst = 'Ñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ucfirst($str),$ucfirst);
    }
    
    function testUcfirstSpace() {
        $str = ' iñtërnâtiônàlizætiøn';
        $ucfirst = ' iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ucfirst($str),$ucfirst);
    }
    
    function testUcfirstUpper() {
        $str = 'Ñtërnâtiônàlizætiøn';
        $ucfirst = 'Ñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ucfirst($str),$ucfirst);
    }
    
    function testEmptyString() {
        $str = '';
        $this->assertEqual(utf8_ucfirst($str),'');
    }
    
    function testOneChar() {
        $str = 'ñ';
        $ucfirst = "Ñ";
        $this->assertEqual(utf8_ucfirst($str),$ucfirst);
    }
    
    function testLinefeed() {
        $str = "ñtërn\nâtiônàlizætiøn";
        $ucfirst = "Ñtërn\nâtiônàlizætiøn";
        $this->assertEqual(utf8_ucfirst($str),$ucfirst);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_ucfirst();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
