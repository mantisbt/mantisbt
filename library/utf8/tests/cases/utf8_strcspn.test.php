<?php
/**
* @version $Id: utf8_strcspn.test.php,v 1.2 2006/02/25 14:52:18 harryf Exp $
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
require_once UTF8 . '/strcspn.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strcspn extends UnitTestCase {

    function test_utf8_strcspn () {
        $this->UnitTestCase('test_utf8_strcspn()');
    }
    
    function testNoMatchSingleByteSearch() {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strcspn($str,'t'),2);
    }
    
    function testNoMatchMultiByteSearch() {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_strcspn($str,'â'),6);
    }
    
    function testCompareStrspn() {
        $str = 'aeioustr';
        $this->assertEqual(utf8_strcspn($str,'tr'),strcspn($str,'tr'));
    }
    
    function testMatchAscii() {
        $str = 'internationalization';
        $this->assertEqual(utf8_strcspn($str,'a'),strcspn($str,'a'));
    }
    
    function testLinefeed() {
        $str = "i\nñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_strcspn($str,'t'),3);
    }
    
    function testLinefeedMask() {
        $str = "i\nñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_strcspn($str,"\n"),1);
    }
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_strcspn ();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
