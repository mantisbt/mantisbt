<?php
/**
* @version $Id: utf8_position.test.php,v 1.1 2006/09/30 22:49:43 harryf Exp $
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
require_once UTF8 . '/utils/position.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_position extends UnitTestCase {
    
    function test_ascii_char_to_byte() {
        $str = 'testing';
        $this->assertIdentical(utf8_byte_position($str, 3), 3);
        $this->assertIdentical(utf8_byte_position($str, 3, 4), array(3,4));
        $this->assertIdentical(utf8_byte_position($str, -1), 0);
        $this->assertIdentical(utf8_byte_position($str, 8), 7);
    }
    
    function test_multibyte_char_to_byte() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertIdentical(utf8_byte_position($str, 3), 4);
        $this->assertIdentical(utf8_byte_position($str, 3, 5), array(4,7));
        $this->assertIdentical(utf8_byte_position($str, -1), 0);
        $this->assertIdentical(utf8_byte_position($str, 28), 27);
    }
    
    // tests for utf8_locate_current_chr & utf8_locate_next_chr
    function test_singlebyte(){
        $tests   = array();
        
        // single byte, should return current index
        $tests[] = array('aaживπά우리をあöä',0,0);
        $tests[] = array('aaживπά우리をあöä',1,1);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_current_chr($test[0],$test[1]),$test[2]);
        }
        
        $tests   = array();
        $tests[] = array('aaживπά우리をあöä',1,1);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_next_chr($test[0],$test[1]),$test[2]);
        }
        
    }
    
    function test_twobyte(){
        // two byte, should move to boundary, expect even number
        $tests   = array();
        $tests[] = array('aaживπά우리をあöä',2,2);
        $tests[] = array('aaживπά우리をあöä',3,2);
        $tests[] = array('aaживπά우리をあöä',4,4);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_current_chr($test[0],$test[1]),$test[2]);
        }
        
        $tests   = array();
        $tests[] = array('aaживπά우리をあöä',2,2);
        $tests[] = array('aaживπά우리をあöä',3,4);
        $tests[] = array('aaживπά우리をあöä',4,4);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_next_chr($test[0],$test[1]),$test[2]);
        }
    }

    function test_threebyte(){
        // three byte, should move to boundary 10 or 13
        $tests   = array();
        $tests[] = array('aaживπά우리をあöä',10,10);
        $tests[] = array('aaживπά우리をあöä',11,10);
        $tests[] = array('aaживπά우리をあöä',12,10);
        $tests[] = array('aaживπά우리をあöä',13,13);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_current_chr($test[0],$test[1]),$test[2]);
        }
        
        $tests   = array();
        $tests[] = array('aaживπά우리をあöä',10,10);
        $tests[] = array('aaживπά우리をあöä',11,13);
        $tests[] = array('aaживπά우리をあöä',12,13);
        $tests[] = array('aaживπά우리をあöä',13,13);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_next_chr($test[0],$test[1]),$test[2]);
        }
    }

    function test_bounds(){
        // bounds checking
        $tests   = array();
        $tests[] = array('aaживπά우리をあöä',-2,0);
        $tests[] = array('aaживπά우리をあöä',128,29);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_current_chr($test[0],$test[1]),$test[2]);
        }
        
        $tests[] = array('aaживπά우리をあöä',-2,0);
        $tests[] = array('aaживπά우리をあöä',128,29);
        
        foreach($tests as $test){
            $this->assertIdentical(utf8_locate_next_chr($test[0],$test[1]),$test[2]);
        }
    }
    
    
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_position();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
