<?php
/**
* @version $Id: utf8_str_split.test.php,v 1.2 2006/02/25 14:52:18 harryf Exp $
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
require_once UTF8 . '/str_split.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_str_split extends UnitTestCase {

    function test_utf8_str_split() {
        $this->UnitTestCase('utf8_str_split()');
    }
    
    function testSplitOneChar() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'I','ñ','t','ë','r','n','â','t','i','ô','n','à','l','i',
            'z','æ','t','i','ø','n',
        );
        $this->assertEqual(utf8_str_split($str),$array);
    }
    
    function testSplitFiveChars() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'Iñtër','nâtiô','nàliz','ætiøn',
        );
        $this->assertEqual(utf8_str_split($str,5),$array);
    }
    
    function testSplitSixChars() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'Iñtërn','âtiônà', 'lizæti','øn',
        );
        $this->assertEqual(utf8_str_split($str,6),$array);
    }
    
    function testSplitLong() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'Iñtërnâtiônàlizætiøn',
        );
        $this->assertEqual(utf8_str_split($str,40),$array);
    }
    
    function testSplitNewline() {
        $str = "Iñtërn\nâtiônàl\nizætiøn\n";
        $array = array(
            'I','ñ','t','ë','r','n',"\n",'â','t','i','ô','n','à','l',"\n",'i',
            'z','æ','t','i','ø','n',"\n",
        );
        $this->assertEqual(utf8_str_split($str),$array);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_str_split();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
