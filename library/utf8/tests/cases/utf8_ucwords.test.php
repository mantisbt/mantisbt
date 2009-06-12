<?php
/**
* @version $Id: utf8_ucwords.test.php,v 1.3 2006/02/26 13:07:42 harryf Exp $
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
require_once UTF8 . '/ucwords.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_ucwords extends UnitTestCase {

    function test_utf8_ucwords () {
        $this->UnitTestCase('test_utf8_ucwords ()');
    }
    
    function testUcword() {
        $str = 'iñtërnâtiônàlizætiøn';
        $ucwords = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ucwords($str),$ucwords);
    }
    
    function testUcwords() {
        $str = 'iñt ërn âti ônà liz æti øn';
        $ucwords = 'Iñt Ërn Âti Ônà Liz Æti Øn';
        $this->assertEqual(utf8_ucwords($str),$ucwords);
    }
    
    function testUcwordsNewline() {
        $str = "iñt ërn âti\n ônà liz æti  øn";
        $ucwords = "Iñt Ërn Âti\n Ônà Liz Æti  Øn";
        $this->assertEqual(utf8_ucwords($str),$ucwords);
    }
    
    function testEmptyString() {
        $str = '';
        $ucwords = '';
        $this->assertEqual(utf8_ucwords($str),$ucwords);
    }
    
    function testOneChar() {
        $str = 'ñ';
        $ucwords = 'Ñ';
        $this->assertEqual(utf8_ucwords($str),$ucwords);
    }
    
    function testLinefeed() {
        $str = "iñt ërn âti\n ônà liz æti øn";
        $ucwords = "Iñt Ërn Âti\n Ônà Liz Æti Øn";
        $this->assertEqual(utf8_ucwords($str),$ucwords);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_ucwords ();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
