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
require_once 'MantisCoreBase.php';

/**
 * MantisBT Prepare API test cases
 */
class MantisPrepareTest extends MantisCoreBase {
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

	/**
	 * Tests prepare_email_link()
	 *
	 * @dataProvider providerEmailLink
	 * @param array $p_in  Input.
	 * @param string $p_out Expected output.
	 * @return void
	 */
	public function testEmailLink( $p_param, $p_access_level, $p_out ) {
		# Make sure we have a DB connection and a logged-in user
		$this->dbConnect();
		$this->login();

		# Set threshold
		$t_config = 'show_user_email_threshold';
		config_set_cache( $t_config, $p_access_level, CONFIG_TYPE_INT );

		$t_result = call_user_func_array( 'prepare_email_link', $p_param );
		$this->assertEquals( $p_out, $t_result );
	}

	/**
	 * Data provider for prepare_email_link() test.
	 * No need to test 'subject' param, logic is already covered by testMailTo.
	 * @see testEmailLink
	 * @return array
	 */
	public function providerEmailLink() {
		$t_email = self::EMAIL;
		$t_text = 'Link Text';
		$t_tooltip = 'Tooltip';
		$t_button_classes = 'class="noprint blue zoom-130"';
		$t_button_text = icon_get( 'fa-envelope-o', 'bigger-115' ) . '&nbsp;' . $t_text;

		$t_test_data = array(
			'Basic' => array(
				array( $t_email, $t_text, '', '', false ),
				ANYBODY,
				"<a href=\"mailto:$t_email\">$t_text</a>"
			),
			'Basic, cannot see e-mails' => array(
				array( $t_email, $t_text, '', '', false ),
				NOBODY,
				$t_text
			),
			'With tooltip' => array(
				array( $t_email, $t_text, '', $t_tooltip, false ),
				ANYBODY,
				"<a href=\"mailto:$t_email\" title=\"$t_tooltip\">$t_text</a>"
			),
			'With tooltip, cannot see e-mails' => array(
				array( $t_email, $t_text, '', $t_tooltip, false ),
				NOBODY,
				"<a title=\"$t_tooltip\">$t_text</a>"
			),
			'With tooltip identical to text' => array(
				array( $t_email, $t_text, '', $t_text, false ),
				ANYBODY,
				"<a href=\"mailto:$t_email\">$t_text</a>"
			),
			'With tooltip identical to text, cannot see e-mails' => array(
				array( $t_email, $t_text, '', $t_text, false ),
				NOBODY,
				$t_text
			),

			'Button' => array(
				array( $t_email, $t_text, '', '', true ),
				ANYBODY,
				"<a href=\"mailto:$t_email\" $t_button_classes>$t_button_text</a>"
			),
			'Button, cannot see e-mails' => array(
				array( $t_email, $t_text, '', '', true ),
				NOBODY,
				$t_text
			),
			'Button with tooltip' => array(
				array( $t_email, $t_text, '', $t_tooltip, true ),
				ANYBODY,
				"<a href=\"mailto:$t_email\" title=\"$t_tooltip\" $t_button_classes>$t_button_text</a>"
			),
			'Button with tooltip, cannot see e-mails' => array(
				array( $t_email, $t_text, '', $t_tooltip, true ),
				NOBODY,
				"<a title=\"$t_tooltip\">$t_text</a>"
			),
			'Button with tooltip identical to text' => array(
				array( $t_email, $t_text, '', $t_text, true ),
				ANYBODY,
				"<a href=\"mailto:$t_email\" $t_button_classes>$t_button_text</a>"
			),
			'Button with tooltip identical to text, cannot see e-mails' => array(
				array( $t_email, $t_text, '', $t_text, true ),
				NOBODY,
				$t_text
			),
		);

		return $t_test_data;
	}

}
