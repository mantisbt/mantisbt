<?php
/**
* @version $Id: utf8_str_ireplace.test.php,v 1.3 2007/08/12 01:20:46 harryf Exp $
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
require_once UTF8 . '/str_ireplace.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_str_ireplace extends UnitTestCase {

    function test_utf8_str_ireplace() {
        $this->UnitTestCase('test_utf8_str_ireplace()');
    }
    
    function testReplace() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñtërnâtiônàlisetiøn';
        $this->assertEqual(utf8_ireplace('lIzÆ','lise',$str),$replaced);
    }
    
    function testReplaceNoMatch() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ireplace('foo','bar',$str),$replaced);
    }
    
    function testEmptyString() {
        $str = '';
        $replaced = '';
        $this->assertEqual(utf8_ireplace('foo','bar',$str),$replaced);
    }
    
    function testEmptySearch() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_ireplace('','x',$str),$replaced);
    }
    
    function testReplaceCount() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'IñtërXâtiôXàlizætiøn';
        $this->assertEqual(utf8_ireplace('n','X',$str,2),$replaced);
    }
    
    function testReplaceDifferentSearchReplaceLength() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'IñtërXXXâtiôXXXàlizætiøXXX';
        $this->assertEqual(utf8_ireplace('n','XXX',$str),$replaced);
    }
    
    function testReplaceArrayAsciiSearch() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñyërxâyiôxàlizæyiøx';
        $this->assertEqual(
            utf8_ireplace(
                array('n','t'),
                array('x','y'),
                $str
                ),$replaced);
    }
    
    function testReplaceArrayUTF8Search() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâti??nàliz????ti???n';
        $this->assertEqual(
            utf8_ireplace(
                array('Ñ','ô','ø','Æ'),
                array('?','??','???','????'),
                $str
                ),$replaced);
    }
    
    function testReplaceArrayStringReplace() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâti?nàliz?ti?n';
        $this->assertEqual(
            utf8_ireplace(
                array('Ñ','ô','ø','Æ'),
                '?',
                $str
                ),$replaced);
    }
    
    function testReplaceArraySingleArrayReplace() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâtinàliztin';
        $this->assertEqual(
            utf8_ireplace(
                array('Ñ','ô','ø','Æ'),
                array('?'),
                $str
                ),$replaced);
    }
    
    function testReplaceLinefeed() {
        $str =      "Iñtërnâti\nônàlizætiøn";
        $replaced = "Iñtërnâti\nônàlisetiøn";
        $this->assertEqual(utf8_ireplace('lIzÆ','lise',$str),$replaced);
    }
    
    function testReplaceLinefeedSearch() {
        $str =      "Iñtërnâtiônàli\nzætiøn";
        $replaced = "Iñtërnâtiônàlisetiøn";
        $this->assertEqual(utf8_ireplace("lI\nzÆ",'lise',$str),$replaced);
    }
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = & new test_utf8_str_ireplace();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
