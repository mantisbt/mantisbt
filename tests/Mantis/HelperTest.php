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
require_once 'MantisCoreBase.php';

/**
 * Helper API tests
 * @package Tests
 * @subpackage String
 */
class MantisHelperTest extends MantisCoreBase {

	/**
	 * Custom assertion to evaluate whether the given exception was a
	 * Mantis error of the specific type(s)
	 *
	 * @param Exception $e
	 * @param array     $p_expected_errors List of expected Mantis error codes.
	 */
	public function assertMantisError( Exception $e, array $p_expected_errors = array( ERROR_GENERIC ) ) {
		$this->assertEquals( E_USER_ERROR, $e->getCode(),
			'Unexpected error occurred: ' . $e->getMessage()
		);

		$t_code = $e->getMessage();
		$this->assertContains( $t_code, $p_expected_errors,
			"Unexpected Mantis error #$t_code occurred: " . error_string( $t_code )
		);
	}

	/**
	 * Tests helper_array_transpose() with good values.
	 *
	 * @param mixed $p_in  Input array.
	 * @param mixed $p_out Output array.
	 * @return void
	 *
	 * @dataProvider providerArrayTransposeValid
	 */
	public function testArrayTransposeValid( $p_in, $p_out ) {
		$this->assertEquals( $p_out, helper_array_transpose( $p_in ) );
	}

	/**
	 * Tests helper_array_transpose() with invalid values.
	 *
	 * @param mixed $p_in  Input value.
	 * @return void
	 *
	 * @dataProvider providerArrayTransposeInvalid
	 */
	public function testArrayTransposeInvalid( $p_in ) {
		try {
			helper_array_transpose( $p_in );
		}
		catch( PHPUnit_Framework_Error $e ) {
			$this->assertMantisError( $e );
			# This is the "normal" exit path as we expect all transpositions
			# to fail with an error
			return;
		}

		# Since the provider only contains invalid values, the transposition
		# should always fail and we should never get here
		$this->fail('The transposition was successful but should have failed.');
	}

	/**
	 * Provides a series of "Good" test cases.
	 *
	 * Test case structure:
	 *   <case> => array( <test matrix>, <expected transposition> )
	 *
	 * helper_array_transpose() should successfully transpose <test matrix>
	 * into <expected transposition>.
	 *
	 * @return array List of test cases
	 */
	public function providerArrayTransposeValid() {
		return array(
			'Bidimensional simple array' => array(
				array( array( 'a' ), array( 'b' ) ),
				array( array ( 'a', 'b', ) )
			),

			'Bidimensional array with numeric indices' => array(
				array( 10 => array( 100 => 'a' ), 20 => array( 100 => 'b' ) ),
				array( 100 => array ( 10 => 'a', 20 => 'b', ) )
			),

			'Bidimensional array with numeric indices and missing keys' => array(
				#    |  0  |  1  |  2  |            |  0  |  1  |
				# ---+-----+-----+-----+         ---+-----+-----+
				#  0 | 111 | 222 |  -  |          0 | 111 | 333 |
				# ---+-----+-----+-----+   ==>   ---+-----+-----+
				#  1 | 333 |  -  | 444 |          1 | 222 |  -  |
				# ---+-----+-----+-----+         ---+-----+-----+
				#                                 2 |  -  | 444 |
				#                                ---+-----+-----+
				array( array( 111, 222 ), array( 333, 2 => 444 ) ),
				array( array ( 111, 333 ), array( 222 ), array( 1 => 444 ) )
			),

			'Bidimensional associative array' => array(
				array( 'a' => array( 'k1' => 1, 'k2' => 2 ),'b' => array( 'k1' => 3,'k2' => 4) ),
				array( 'k1' => array( 'a' => 1, 'b' => 3 ), 'k2' => array( 'a' => 2, 'b' => 4) )
			),

			'Bidimensional array with arrays as data' => array(
				array(
					'a' => array( 'k1' => array( 1, 2, 3 ), 'k2' => 2),
					'b' => array( 'k1' => array( 4, 5, 6 ), 'k2' => 4)
				),
				array(
					'k1' => array( 'a' => array( 1, 2, 3 ), 'b' => array( 4, 5, 6 ) ),
					'k2' => array( 'a' => 2, 'b' => 4 )
				)
			),
		);
	}

	/**
	 * Provides a series of test cases that should fail transposition.
	 *
	 * Test case structure:
	 *   <case> => array( <test matrix> )
	 *
	 * helper_array_transpose() is expected to fail for each case .
	 * Note: we don't need to test non-array types as these would throw a
	 * TypeError exception or an E_RECOVERABLE_ERROR (depending on PHP version).
	 *
	 * @return array List of test cases
	 */
	public function providerArrayTransposeInvalid() {
		return array(
			'Simple array' => array(
				array( 1, 2 )
			),

			# 1st element array, 2nd scalar
			'Mixed, "non-square" array' => array(
				array( 'a' => array( 'k1' => 1, 'k2' => 2 ), 'b' => 123 )
			),
		);
	}
}
