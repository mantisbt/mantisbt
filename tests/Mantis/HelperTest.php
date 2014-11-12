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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';

# MantisBT Core API
require_mantis_core();

/**
 * Helper API tests
 * @package Tests
 * @subpackage String
 */
class Mantis_HelperTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests helper_array_transpose()
	 * @dataProvider providerArrayTranspose
	 * @param mixed $p_in  Input array.
	 * @param mixed $p_out Output array.
	 * @return void
	 */
	public function testArrayTranspose( $p_in, $p_out ) {
		$this->assertEquals( $p_out, helper_array_transpose( $p_in ) );
	}

	/**
	 * Returns test array
	 * @return array
	 */
	public function providerArrayTranspose() {
		return array(
			# simple array
			array( array(123,456), array(123,456) ),

			# mixed (1st element array, 2nd scalar)
			array(
				array('a'=>array('k1'=>1,'k2'=>2),'b'=>123),
				array('a'=>array('k1'=>1,'k2'=>2),'b'=>123),
			),

			# bidimentional array
			array(
				array('a'=>array('k1'=>1,'k2'=>2),'b'=>array('k1'=>3,'k2'=>4)),
				array('k1'=>array('a'=>1,'b'=>3),'k2'=>array('a'=>2,'b'=>4))
			),

			# bidimentional array with arrays as elements' data
			array(
				array('a'=>array('k1'=>array(1,2,3),'k2'=>2),'b'=>array('k1'=>array(4,5,6),'k2'=>4)),
				array('k1'=>array('a'=>array(1,2,3),'b'=>array(4,5,6)),'k2'=>array('a'=>2,'b'=>4))
			),
		);
	}
}
