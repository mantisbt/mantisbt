<?php
/**
* @version $Id: utf8_strpos.test.php,v 1.2 2006/02/24 23:33:10 harryf Exp $
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
class test_utf8_strpos extends UnitTestCase {

    function test_utf8_strpos() {
        $this->UnitTestCase('utf8_strpos()');
    }
    
    function testUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strpos($str,'â'),6);
    }
    
    function testUtf8Offset() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strpos($str,'n',11),19);
    }
    
    function testUtf8Invalid() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEqual(utf8_strpos($str,'æ'),15);
    }
    
    function testAscii() {
        $str = 'ABC 123';
        $this->assertEqual(utf8_strpos($str,'B'),1);
    }
    
    function testVsStrpos() {
        $str = 'ABC 123 ABC';
        $this->assertEqual(utf8_strpos($str,'B',3),strpos($str,'B',3));
    }
    
    function testEmptyStr() {
        $str = '';
        $this->assertFalse(utf8_strpos($str,'x'));
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_strpos();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
