<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mantis Unit Tests
 * @package Tests
 * MantisBT Core Unit Tests
 * @subpackage Helper
 * @copyright Copyright 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Includes
 */
require_once dirname( dirname(__FILE__) ) . '/TestConfig.php';

/**
 * MantisBT Core API
 */
require_mantis_core();

require_once 'core/utility_api.php';


/**
 * Utility API tests
 * @package Tests
 * @subpackage Utility
 */
class Mantis_UtilityTest extends PHPUnit_Framework_TestCase {

    public function testSpecialSplit() {

        self::assertEquals(array('a', ' b'), special_split('a, b'));
    }

    public function testSpecialSplit_CommaInParanthesis() {

        self::assertEquals(array('a(,) b'), special_split('a(,) b'));
    }

    public function testSpecialSplit_CommaInSingleQuotes() {
    
        self::assertEquals(array("a',' b"), special_split("a',' b"));
    }
    
    public function testProcessComplexValue_SimpleArray() {

        self::assertEquals(array(1,2,3), process_complex_value('array(1, 2, 3)'));
    }

    public function testProcessComplexValue_AssociativeArray() {

        self::assertEquals(array("a" => 1, "b" => 2), process_complex_value('array("a" => 1, "b"=> "2")'));
    }

    public function testProcessComplexValue_NumericValue() {
    
        self::assertEquals(1337 , process_complex_value('1337'));
    }

    public function testProcessComplexValue_StringValue() {
    
        self::assertEquals('string_value' , process_complex_value('string_value'));
    }
    
    public function testConstantReplace() {

        self::assertEquals(ON, constant_replace('ON'));
    }

    public function testConstantReplace_NotAConstant() {

        self::assertEquals('NotAConstant', constant_replace('NotAConstant'));
    }
}
