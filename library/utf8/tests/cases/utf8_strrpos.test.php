<?php
/**
* @version $Id: utf8_strrpos.test.php,v 1.3 2006/02/24 23:33:10 harryf Exp $
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
class test_utf8_strrpos extends UnitTestCase {

    function test_utf8_strrpos() {
        $this->UnitTestCase('utf8_strrpos()');
    }
    
    function testUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strrpos($str,'i'),17);
    }
    
    function testUtf8Offset() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strrpos($str,'n',11),19);
    }
    
    function testUtf8Invalid() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEqual(utf8_strrpos($str,'æ'),15);
    }
    
    function testAscii() {
        $str = 'ABC ABC';
        $this->assertEqual(utf8_strrpos($str,'B'),5);
    }
    
    function testVsStrpos() {
        $str = 'ABC 123 ABC';
        $this->assertEqual(utf8_strrpos($str,'B'),strrpos($str,'B'));
    }
    
    function testEmptyStr() {
        $str = '';
        $this->assertFalse(utf8_strrpos($str,'x'));
    }
    
    function testLinefeed() {
        $str = "Iñtërnâtiônàlizætiø\nn";
        $this->assertEqual(utf8_strrpos($str,'i'),17);
    }
    
    function testLinefeedSearch() {
        $str = "Iñtërnâtiônàlizætiø\nn";
        $this->assertEqual(utf8_strrpos($str,"\n"),19);
    }
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_strrpos();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
