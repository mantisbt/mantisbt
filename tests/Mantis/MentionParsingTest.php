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
 * Test cases for mention API
 *
 * @package    Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002-2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Includes
 */
require_once 'MantisCoreBase.php';
require_api( 'mention_api.php' );

/**
 * Test cases for parsing functionality in mention API.
 * @package    Tests
 * @subpackage Mention
 */
class MentionParsingTest extends MantisCoreBase {

	/**
	 * Tests user mentions
	 * @dataProvider provider
	 *
	 * @param string $p_input_text
	 * @param array  $p_expected   List of expected mentions
	 */
	public function testMentions( $p_input_text, array $p_expected ) {
		$t_actual = mention_get_candidates( $p_input_text );

		$this->assertEquals($p_expected, $t_actual );
	}

	/**
	 * Data provider function for mentions tests.
	 * Test case structure:
	 *   <test case> => array( <string to test>, <list of expected mentions>)
	 * @return array
	 */
	public function provider() {
		return array(
			'NoMention' => array(
				'some random string.',
				array()
			),
			'NoMentionWithAtSign' => array(
				'some random string with @ sign.',
				array()
			),
			'NoMentionWithMultipleAtSigns' => array(
				'some random string with @@vboctor sign.',
				array()
			),
			'JustMention' => array(
				'@vboctor',
				array( 'vboctor' )
			),
			'WithDotInMiddle' => array(
				'@victor.boctor',
				array( 'victor.boctor' )
			),
			'WithDotAtEnd' => array(
				'@vboctor.',
				array( 'vboctor' )
			),
			'MentionWithUnderscore' => array(
				'@victor_boctor',
				array( 'victor_boctor' )
			),
			'MentionAtStart' => array(
				'@vboctor will check',
				array( 'vboctor' )
			),
			'MentionAtEnd' => array(
				'Please assign to @vboctor',
				array( 'vboctor' )
			),
			'MentionAtEndWithFullstop' => array(
				'Please assign to @vboctor.',
				array( 'vboctor' )
			),
			'MentionSeparatedWithColon' => array(
				'@vboctor: please check.',
				array( 'vboctor' )
			),
			'MentionSeparatedWithSemiColon' => array(
				'@vboctor; please check.',
				array( 'vboctor' )
			),
			'MentionWithMultiple' => array(
				'Please check with @vboctor and @someone.',
				array( 'vboctor', 'someone' )
			),
			'MentionWithDuplicates' => array(
				'Please check with @vboctor and @vboctor.',
				array( 'vboctor' )
			),
			'MentionWithMultipleSlashSeparated' => array(
				'@vboctor/@someone, please check.',
				array( 'vboctor', 'someone' )
			),
			'MentionWithMultipleNewLineSeparated' => array(
				"Check with:\n@vboctor\n@someone.",
				array( 'vboctor', 'someone' )
			),
			'MentionNl2br' => array(
				string_nl2br( "Check with @vboctor\n" ) ,
				array( 'vboctor' )
			),
			'MentionWithEmailAddress' => array(
				'xxx@example.com',
				array()
			),
			'MentionWithLocalhost' => array(
				'xxx@localhost',
				array()
			),
			'MentionAtEndOfWord' => array(
				"vboctor@",
				array()
			),
			'MentionWithInvalidChars' => array(
				"@vboctor%%%%%",
				array( 'vboctor' )
			),
			'MentionUsernameThatIsAnEmailAddress' => array(
				"@vboctor@example.com",
				array()
			),
			'MentionUsernameThatIsLocalhost' => array(
				"@vboctor@localhost",
				array()
			),
		);

	}
}
