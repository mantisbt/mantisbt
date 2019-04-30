<?php
# MantisBT - A PHP based bugtracking system

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
 * MantisBT Prepare API test cases
 *
 * @package    Tests
 * @subpackage Prepare
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';

# MantisBT Core API
require_mantis_core();

/**
 * MantisBT Prepare API test cases
 */
class MantisPrepareTest extends PHPUnit_Framework_TestCase {
	const EMAIL = 'test@example.com';

	/**
	 * Tests prepare_mailto_url()
	 *
	 * @dataProvider providerMailTo
	 * @param array $p_in  Input.
	 * @param string $p_out Expected output.
	 * @return void
	 */
	public function testMailTo( $p_in, $p_out ) {
		$t_result = call_user_func_array( 'prepare_mailto_url', $p_in );
		$this->assertEquals( $p_out, $t_result );
	}

	/**
	 * Data provider for prepare_mailto_url() test
	 * @return array
	 */
	public function providerMailTo() {
		$t_test_data = array(
			'Basic' => array( array( self::EMAIL, ''), 'mailto:' . self::EMAIL ),
			'Subject' => array( array( self::EMAIL, 'subject'), 'mailto:' . self::EMAIL . '?subject=subject' ),
			'SubjectWithSpace' => array( array( self::EMAIL, 'message subject'), 'mailto:' . self::EMAIL . '?subject=message%20subject' ),
			'SubjectWithQuestionAmp' => array( array( self::EMAIL, 'message?subject&matter'), 'mailto:' . self::EMAIL . '?subject=message%3Fsubject%26matter' ),
		);

		return $t_test_data;
	}

}
