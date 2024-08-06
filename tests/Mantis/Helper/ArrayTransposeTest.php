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
 * MantisBT Core Unit Tests
 * @package Tests
 * @subpackage Helper
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 */

declare( strict_types = 1 );

use Mantis\Exceptions\ClientException;

require_once dirname( __DIR__ ) . '/MantisCoreBase.php';

/**
 * Test for helper_api::helper_array_transpose
 *
 * @see helper_array_transpose()
 */
class ArrayTransposeTest extends MantisCoreBase {

	/**
	 * Tests helper_array_transpose() with good values.
	 *
	 * @param array $p_in  Input array.
	 * @param array $p_out Output array.
	 *
	 * @dataProvider providerArrayTransposeValid
	 * @throws ClientException
	 */
	public function testArrayTransposeValid( array $p_in, array $p_out ): void {
		$this->assertSame( $p_out, helper_array_transpose( $p_in ) );
	}

	/**
	 * Tests helper_array_transpose() with invalid values.
	 *
	 * @param array $p_in Input value.
	 *
	 * @dataProvider providerArrayTransposeInvalid
	 */
	public function testArrayTransposeInvalid( array $p_in ): void {
		$this->expectException( ClientException::class );
		$this->expectExceptionMessage( 'helper_array_transpose can only handle bidimensional arrays' );

		helper_array_transpose( $p_in );
	}

	/**
	 * Provides a series of "Good" test cases.
	 *
	 * Test case structure:
	 *   <case> => array( <test matrix>, <expected transposition> )
	 *
	 * helper_array_transpose() should successfully transpose <test matrix>
	 * into <expected transposition>.
	 */
	public function providerArrayTransposeValid(): Generator {
		yield 'Bidimensional simple array' => [
			[['a'], ['b']],
			[['a', 'b']]
		];

		yield 'Bidimensional array with numeric indices' => [
			[10 => [100 => 'a'], 20 => [100 => 'b']],
			[100 => [10 => 'a', 20 => 'b']]
		];

		yield 'Bidimensional array with numeric indices and missing keys' => [
			#    |  0  |  1  |  2  |            |  0  |  1  |
			# ---+-----+-----+-----+         ---+-----+-----+
			#  0 | 111 | 222 |  -  |          0 | 111 | 333 |
			# ---+-----+-----+-----+   ==>   ---+-----+-----+
			#  1 | 333 |  -  | 444 |          1 | 222 |  -  |
			# ---+-----+-----+-----+         ---+-----+-----+
			#                                 2 |  -  | 444 |
			#                                ---+-----+-----+
			[[111, 222], [333, 2 => 444]],
			[[111, 333], [222], [1 => 444]],
		];

		yield 'Bidimensional associative array' => [
			['a' => ['k1' => 1, 'k2' => 2], 'b' => ['k1' => 3, 'k2' => 4]],
			['k1' => ['a' => 1, 'b' => 3], 'k2' => ['a' => 2, 'b' => 4]]
		];

		yield 'Bidimensional array with arrays as data' => [
			[
				'a' => ['k1' => [1, 2, 3], 'k2' => 2],
				'b' => ['k1' => [4, 5, 6], 'k2' => 4]
			],
			[
				'k1' => ['a' => [1, 2, 3], 'b' => [4, 5, 6]],
				'k2' => ['a' => 2, 'b' => 4]
			],
		];
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
	 * @return Generator List of test cases
	 */
	public function providerArrayTransposeInvalid(): Generator {
		yield 'Simple array' => [
			[1, 2]
		];

		# 1st element array, 2nd scalar
		yield 'Mixed, "non-square" array' => [
			['a' => ['k1' => 1, 'k2' => 2], 'b' => 123]
		];
	}
}
