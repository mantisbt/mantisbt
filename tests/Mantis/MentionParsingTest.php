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
 * @link https://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

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
	 * Run in the separate process to work around a static variable in the mention_get_candidates()
	 * @runInSeparateProcess
	 *
	 * @param string $p_input_text
	 * @param array  $p_expected   List of expected mentions
	 */
	public function testMentions( $p_input_text, array $p_expected ) {
		$t_actual = mention_get_candidates( str_replace( '{tag}', mentions_tag(), $p_input_text ) );

		$this->assertEquals( $p_expected, $t_actual );
	}

	/**
	 * Data provider function for mentions tests.
	 * Test case structure:
	 *   <test case> => array( <string to test>, <list of expected mentions>)
	 * @return array
	 */
	public static function provider() {
		return [
			'NoMention' => [
				'some random string.',
				[]
			],
			'NoMentionWithAtSign' => [
				'some random string with {tag} sign.',
				[]
			],
			'NoMentionWithMultipleAtSigns' => [
				'some random string with {tag}{tag}vboctor sign.',
				[]
			],
			'JustMention' => [
				'{tag}vboctor',
				['vboctor']
			],
			'WithDotInMiddle' => [
				'{tag}victor.boctor',
				['victor.boctor']
			],
			'WithDotAtEnd' => [
				'{tag}vboctor.',
				['vboctor']
			],
			'MentionWithUnderscore' => [
				'{tag}victor_boctor',
				['victor_boctor']
			],
			'MentionAtStart' => [
				'{tag}vboctor will check',
				['vboctor']
			],
			'MentionAtEnd' => [
				'Please assign to {tag}vboctor',
				['vboctor']
			],
			'MentionAtEndWithFullstop' => [
				'Please assign to {tag}vboctor.',
				['vboctor']
			],
			'MentionSeparatedWithColon' => [
				'{tag}vboctor: please check.',
				['vboctor']
			],
			'MentionSeparatedWithSemiColon' => [
				'{tag}vboctor; please check.',
				['vboctor']
			],
			'MentionWithMultiple' => [
				'Please check with {tag}vboctor and {tag}someone.',
				['vboctor', 'someone']
			],
			'MentionWithDuplicates' => [
				'Please check with {tag}vboctor and {tag}vboctor.',
				['vboctor']
			],
			'MentionWithMultipleSlashSeparated' => [
				'{tag}vboctor/{tag}someone, please check.',
				['vboctor', 'someone']
			],
			'MentionWithMultipleNewLineSeparated' => [
				"Check with:\n{tag}vboctor\n{tag}someone.",
				['vboctor', 'someone']
			],
			'MentionNl2br' => [
				string_nl2br( "Check with {tag}vboctor\n" ),
				['vboctor']
			],
			'MentionWithEmailAddress' => [
				'xxx{tag}example.com',
				[]
			],
			'MentionWithLocalhost' => [
				'xxx{tag}localhost',
				[]
			],
			'MentionAtEndOfWord' => [
				'{tag}vboctor{tag}',
				[]
			],
			'MentionWithInvalidChars' => [
				'{tag}vboctor%%%%%',
				['vboctor']
			],
			'MentionUsernameThatIsAnEmailAddress' => [
				'{tag}vboctor@example.com',
				[]
			],
			'MentionUsernameThatIsLocalhost' => [
				'{tag}vboctor@localhost',
				[]
			],
		];

	}
}
