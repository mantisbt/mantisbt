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

		$this->assertEquals( $p_expected, $t_actual );
	}

	/**
	 * Data provider function for mentions tests.
	 * Test case structure:
	 *   <test case> => array( <string to test>, <list of expected mentions>)
	 * @return array
	 */
	public function provider() {
		return [
			'NoMention' => [
				'some random string.',
				[]
			],
			'NoMentionWithAtSign' => [
				'some random string with @ sign.',
				[]
			],
			'NoMentionWithMultipleAtSigns' => [
				'some random string with @@vboctor sign.',
				[]
			],
			'JustMention' => [
				'@vboctor',
				['vboctor']
			],
			'WithDotInMiddle' => [
				'@victor.boctor',
				['victor.boctor']
			],
			'WithDotAtEnd' => [
				'@vboctor.',
				['vboctor']
			],
			'MentionWithUnderscore' => [
				'@victor_boctor',
				['victor_boctor']
			],
			'MentionAtStart' => [
				'@vboctor will check',
				['vboctor']
			],
			'MentionAtEnd' => [
				'Please assign to @vboctor',
				['vboctor']
			],
			'MentionAtEndWithFullstop' => [
				'Please assign to @vboctor.',
				['vboctor']
			],
			'MentionSeparatedWithColon' => [
				'@vboctor: please check.',
				['vboctor']
			],
			'MentionSeparatedWithSemiColon' => [
				'@vboctor; please check.',
				['vboctor']
			],
			'MentionWithMultiple' => [
				'Please check with @vboctor and @someone.',
				['vboctor', 'someone']
			],
			'MentionWithDuplicates' => [
				'Please check with @vboctor and @vboctor.',
				['vboctor']
			],
			'MentionWithMultipleSlashSeparated' => [
				'@vboctor/@someone, please check.',
				['vboctor', 'someone']
			],
			'MentionWithMultipleNewLineSeparated' => [
				"Check with:\n@vboctor\n@someone.",
				['vboctor', 'someone']
			],
			'MentionNl2br' => [
				string_nl2br( "Check with @vboctor\n" ),
				['vboctor']
			],
			'MentionWithEmailAddress' => [
				'xxx@example.com',
				[]
			],
			'MentionWithLocalhost' => [
				'xxx@localhost',
				[]
			],
			'MentionAtEndOfWord' => [
				'vboctor@',
				[]
			],
			'MentionWithInvalidChars' => [
				'@vboctor%%%%%',
				['vboctor']
			],
			'MentionUsernameThatIsAnEmailAddress' => [
				'@vboctor@example.com',
				[]
			],
			'MentionUsernameThatIsLocalhost' => [
				'@vboctor@localhost',
				[]
			],
		];

	}
}
