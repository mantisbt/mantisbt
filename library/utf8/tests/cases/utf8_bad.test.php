<?php
/**
* @version $Id: utf8_bad.test.php,v 1.6 2006/02/26 13:39:37 harryf Exp $
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
require_once UTF8 . '/utils/bad.php';

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_bad_find extends UnitTestCase {

    function test_utf8_bad_find() {
        $this->UnitTestCase('utf8_bad_find()');
    }
    
    function testValidUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertFalse(utf8_bad_find($str));
    }
    
    function testValidUtf8Ascii() {
        $str = 'testing';
        $this->assertFalse(utf8_bad_find($str));
    }
    
    function testInvalidUtf8() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEqual(utf8_bad_find($str),15);
    }
    
    function testInvalidUtf8Ascii() {
        $str = "this is an invalid char '\xe9' here";
        $this->assertEqual(utf8_bad_find($str),25);
    }
    
    function testInvalidUtf8Start() {
        $str = "\xe9Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),0);
    }
    
    function testInvalidUtf8End() {
        $str = "Iñtërnâtiônàlizætiøn\xe9";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    
    function testValidTwoOctetId() {
        $str = "abc\xc3\xb1";
        $this->assertFalse(utf8_bad_find($str));
    }
    
    function testInvalidTwoOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),28);
    }
    
    function testInvalidIdBetweenTwoAndThree() {
        $str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    
    
    function testValidThreeOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_bad_find($str));
    }
    
    
    function testInvalidThreeOctetSequenceSecond() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    
    function testInvalidThreeOctetSequenceThird() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    
    function testValidFourOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_bad_find($str));
    }
    
    function testInvalidFourOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    
    function testInvalidFiveOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    
    function testInvalidSixOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_bad_findall extends UnitTestCase {

    function test_utf8_bad_findall() {
        $this->UnitTestCase('test_utf8_bad_findall()');
    }
    
    function testValidUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertFalse(utf8_bad_findall($str));
    }
    
    function testValidUtf8Ascii() {
        $str = 'testing';
        $this->assertFalse(utf8_bad_findall($str));
    }
    
    function testInvalidUtf8() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $test = array(15);
        $this->assertEqual(utf8_bad_findall($str),$test);
    }
    
    function testInvalidUtf8Ascii() {
        $str = "this is an invalid char '\xe9' here";
        $test = array(25);
        $this->assertEqual(utf8_bad_findall($str),$test);
    }
    
    function testInvalidUtf8Multiple() {
        $str = "\xe9Iñtërnâtiôn\xe9àlizætiøn\xe9";
        $test = array(0,16,29);
        $this->assertEqual(utf8_bad_findall($str),$test);
    }
    
    function testValidTwoOctetId() {
        $str = "abc\xc3\xb1";
        $this->assertFalse(utf8_bad_findall($str));
    }
    
    function testInvalidTwoOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_findall($str),array(28));
    }
    
    function testInvalidIdBetweenTwoAndThree() {
        $str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_findall($str),array(27,28));
    }
    
    function testValidThreeOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_bad_findall($str));
    }
    
    function testInvalidThreeOctetSequenceSecond() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_findall($str),array(27,29));
    }
    
    function testInvalidThreeOctetSequenceThird() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_find($str),27);
    }
    
    function testValidFourOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_bad_findall($str));
    }
    
    function testInvalidFourOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_findall($str),array(27,29,30));
    }
    
    function testInvalidFiveOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_findall($str),range(27,31));
    }
    
    function testInvalidSixOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_findall($str),range(27,32));
    }
    

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_bad_strip extends UnitTestCase {

    function test_utf8_bad_strip() {
        $this->UnitTestCase('test_utf8_bad_strip()');
    }
    
    function testValidUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_bad_strip($str),$str);
    }
    
    function testValidUtf8Ascii() {
        $str = 'testing';
        $this->assertEqual(utf8_bad_strip($str),$str);
    }
    
    function testInvalidUtf8() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),'Iñtërnâtiônàlizætiøn');
    }
    
    function testInvalidUtf8Ascii() {
        $str = "this is an invalid char '\xe9' here";
        $this->assertEqual(utf8_bad_strip($str),"this is an invalid char '' here");
    }
    
    function testInvalidUtf8Multiple() {
        $str = "\xe9Iñtërnâtiôn\xe9àlizætiøn\xe9";
        $this->assertEqual(utf8_bad_strip($str),'Iñtërnâtiônàlizætiøn');
    }
    
    function testValidTwoOctetId() {
        $str = "abc\xc3\xb1";
        $this->assertEqual(utf8_bad_strip($str),$str);
    }
    
    function testInvalidTwoOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $stripped = "Iñtërnâtiônàlizætiøn \x28 Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$stripped);
    }
    
    function testInvalidIdBetweenTwoAndThree() {
        $str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $stripped = "IñtërnâtiônàlizætiønIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$stripped);
    }
    
    function testValidThreeOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$str);
    }
    
    function testInvalidThreeOctetSequenceSecond() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $stripped = "Iñtërnâtiônàlizætiøn(Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$stripped);
    }
    
    function testInvalidThreeOctetSequenceThird() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $stripped = "Iñtërnâtiônàlizætiøn(Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$stripped);
    }
    
    function testValidFourOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$str);
    }
    
    function testInvalidFourOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $stripped = "Iñtërnâtiônàlizætiøn(Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$stripped);
    }
    
    function testInvalidFiveOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $stripped = "IñtërnâtiônàlizætiønIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$stripped);
    }
    
    function testInvalidSixOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $stripped = "IñtërnâtiônàlizætiønIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_strip($str),$stripped);
    }

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_bad_replace extends UnitTestCase {

    function test_utf8_bad_replace() {
        $this->UnitTestCase('test_utf8_bad_replace()');
    }
    
    function testValidUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEqual(utf8_bad_replace($str),$str);
    }
    
    function testValidUtf8Ascii() {
        $str = 'testing';
        $this->assertEqual(utf8_bad_replace($str),$str);
    }
    
    function testInvalidUtf8() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),'Iñtërnâtiôn?àlizætiøn');
    }
    
    function testInvalidUtf8WithX() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEqual(utf8_bad_replace($str,'X'),'IñtërnâtiônXàlizætiøn');
    }
    
    function testInvalidUtf8Ascii() {
        $str = "this is an invalid char '\xe9' here";
        $this->assertEqual(utf8_bad_replace($str),"this is an invalid char '?' here");
    }
    
    function testInvalidUtf8Multiple() {
        $str = "\xe9Iñtërnâtiôn\xe9àlizætiøn\xe9";
        $this->assertEqual(utf8_bad_replace($str),'?Iñtërnâtiôn?àlizætiøn?');
    }
    
    function testValidTwoOctetId() {
        $str = "abc\xc3\xb1";
        $this->assertEqual(utf8_bad_replace($str),$str);
    }
    
    function testInvalidTwoOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn ?( Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$replaced);
    }
    
    function testInvalidIdBetweenTwoAndThree() {
        $str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn??Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$replaced);
    }
    
    function testValidThreeOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$str);
    }
    
    function testInvalidThreeOctetSequenceSecond() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn?(?Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$replaced);
    }
    
    function testInvalidThreeOctetSequenceThird() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn??(Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$replaced);
    }
    
    function testValidFourOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$str);
    }
    
    function testInvalidFourOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn?(??Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$replaced);
    }
    
    function testInvalidFiveOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn?????Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$replaced);
    }
    
    function testInvalidSixOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn??????Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_replace($str),$replaced);
    }

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
class test_utf8_bad_identify extends UnitTestCase {

    function test_utf8_bad_identify() {
        $this->UnitTestCase('utf8_bad_identify()');
    }
    
    function testValidUtf8() {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertFalse(utf8_bad_identify($str,$i));
        $this->assertNull($i);
    }
    
    function testValidUtf8Ascii() {
        $str = 'testing';
        $this->assertFalse(utf8_bad_identify($str,$i));
        $this->assertNull($i);
    }
    
    function testInvalidUtf8() {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,15);
    }
    
    function testInvalidUtf8Ascii() {
        $str = "this is an invalid char '\xe9' here";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,25);
    }
    
    function testInvalidUtf8Start() {
        $str = "\xe9Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,0);
    }
    
    function testInvalidUtf8End() {
        $str = "Iñtërnâtiônàlizætiøn\xe9";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,27);
    }
    
    function testValidTwoOctetId() {
        $str = "abc\xc3\xb1";
        $this->assertFalse(utf8_bad_identify($str,$i));
        $this->assertNull($i);
    }
    
    function testInvalidTwoOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,28);
    }
    
    function testInvalidIdBetweenTwoAndThree() {
        $str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQID);
        $this->assertEqual($i,27);
    }
    
    
    function testValidThreeOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_bad_identify($str,$i));
        $this->assertNull($i);
    }
    
    
    function testInvalidThreeOctetSequenceSecond() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,27);
    }
    
    function testInvalidThreeOctetSequenceThird() {
        $str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,28);
    }
    
    function testValidFourOctetId() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_bad_identify($str,$i));
        $this->assertNull($i);
    }
    
    function testInvalidFourOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_SEQINCOMPLETE);
        $this->assertEqual($i,27);
    }
    
    function testInvalidFiveOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_5OCTET);
        $this->assertEqual($i,27);
    }
    
    function testInvalidSixOctetSequence() {
        $str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEqual(utf8_bad_identify($str,$i),UTF8_BAD_6OCTET);
        $this->assertEqual($i,27);
    }
    

}

//--------------------------------------------------------------------
/**
* @package utf8
* @subpackage Tests
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new GroupTest('utf8_bad');
    $test->addTestCase(new test_utf8_bad_find());
    $test->addTestCase(new test_utf8_bad_findall());
    $test->addTestCase(new test_utf8_bad_strip());
    $test->addTestCase(new test_utf8_bad_replace());
    $test->addTestCase(new test_utf8_bad_identify());
    $reporter = & getTestReporter();
    $test->run($reporter);
}
