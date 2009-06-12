<?php
/**
* @version $Id: utf8_strcasecmp.test.php,v 1.2 2006/02/25 14:52:18 harryf Exp $
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
require_once UTF8 . '/strcasecmp.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_strcasecmp extends UnitTestCase {

    function test_utf8_strcasecmp () {
        $this->UnitTestCase('test_utf8_strcasecmp()');
    }
    
    function testCompareEqual() {
        $strX = 'iñtërnâtiônàlizætiøn';
        $strY = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertEqual(utf8_strcasecmp($strX,$strY),0);
    }
    
    
    function testLess() {
        $strX = 'iñtërnâtiônàlizætiøn';
        $strY = 'IÑTËRNÂTIÔÀLIZÆTIØN';
        $this->assertTrue(utf8_strcasecmp($strX,$strY) < 0);
    }
    
    function testGreater() {
        $strX = 'iñtërnâtiôàlizætiøn';
        $strY = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertTrue(utf8_strcasecmp($strX,$strY) > 0);
    }
    
    function testEmptyX() {
        $strX = '';
        $strY = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertTrue(utf8_strcasecmp($strX,$strY) < 0);
    }
    
    function testEmptyY() {
        $strX = 'iñtërnâtiôàlizætiøn';
        $strY = '';
        $this->assertTrue(utf8_strcasecmp($strX,$strY) > 0);
    }
    
    function testEmptyBoth() {
        $strX = '';
        $strY = '';
        $this->assertTrue(utf8_strcasecmp($strX,$strY) == 0);
    }
    
    function testLinefeed() {
        $strX = "iñtërnâtiôn\nàlizætiøn";
        $strY = "IÑTËRNÂTIÔN\nÀLIZÆTIØN";
        $this->assertTrue(utf8_strcasecmp($strX,$strY) == 0);
    }

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_strcasecmp ();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
