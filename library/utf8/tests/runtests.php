<?php
define('TEST_RUNNING',TRUE);

require_once 'config.php';

class AllTests extends GroupTest {

    function AllTests() {
        $this->GroupTest('All PHPUTF8 Tests');
        $this->loadGroups();
    }

    function loadGroups() {
        $path = dirname(__FILE__).'/cases';
        if ( $d = opendir($path) ) {
            while (($file = readdir($d)) !== false) {
                if ( is_file($path.'/'.$file) ) {
                    $farray = explode('.',$file);
                    if ( $farray[1] == 'test' ) {
                        $this->AddTestFile($path.'/'.$file);
                    }
                }
            }
            closedir($d);
        }
    }
}
/**
* Run the tests
*/
$test = &new AllTests();
$test->run(getTestReporter());
