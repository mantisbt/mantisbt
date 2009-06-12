<?php
/**
* @version $Id: utf8_str_pad.test.php,v 1.1 2006/09/03 09:25:13 harryf Exp $
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
require_once UTF8 . '/str_pad.php';

//--------------------------------------------------------------------
/**
* @author Oliver Saunders <oliver (a) osinternetservices.com>
* @package utf8
* @subpackage Tests
*/
class test_utf8_str_pad extends UnitTestCase {
    public function test(){
        $toPad = '<IñtërnëT>'; // 10 characters
        $padding = 'ø__'; // 4 characters
        
        $this->assertEqual(utf8_str_pad($toPad, 20), $toPad . '          ');
        $this->assertEqual(utf8_str_pad($toPad, 20, ' ', STR_PAD_LEFT), '          ' . $toPad);
        $this->assertEqual(utf8_str_pad($toPad, 20, ' ', STR_PAD_BOTH), '     ' . $toPad . '     ');
        
        $this->assertEqual(utf8_str_pad($toPad, 10), $toPad);
        $this->assertEqual(str_pad('5char', 4), '5char'); // str_pos won't truncate input string
        $this->assertEqual(utf8_str_pad($toPad, 8), $toPad);
        
        $this->assertEqual(utf8_str_pad($toPad, 20, $padding, STR_PAD_RIGHT), $toPad . 'ø__ø__ø__ø');
        $this->assertEqual(utf8_str_pad($toPad, 20, $padding, STR_PAD_LEFT), 'ø__ø__ø__ø' . $toPad);
        $this->assertEqual(utf8_str_pad($toPad, 20, $padding, STR_PAD_BOTH), 'ø__ø_' . $toPad . 'ø__ø_');
    }
}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new test_utf8_str_pad ();
    $reporter = & getTestReporter();
    $test->run($reporter);
}
