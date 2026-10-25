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
		return array(
			'NoMention' => array(
				'some random string.',
				array()
			),
			'NoMentionWithAtSign' => array(
				'some random string with {tag} sign.',
				array()
			),
			'NoMentionWithMultipleAtSigns' => array(
				'some random string with {tag}{tag}vboctor sign.',
				array()
			),
			'JustMention' => array(
				'{tag}vboctor',
				array( 'vboctor' )
			),
			'WithDotInMiddle' => array(
				'{tag}victor.boctor',
				array( 'victor.boctor' )
			),
			'WithDotAtEnd' => array(
				'{tag}vboctor.',
				array( 'vboctor' )
			),
			'MentionWithUnderscore' => array(
				'{tag}victor_boctor',
				array( 'victor_boctor' )
			),
			'MentionAtStart' => array(
				'{tag}vboctor will check',
				array( 'vboctor' )
			),
			'MentionAtEnd' => array(
				'Please assign to {tag}vboctor',
				array( 'vboctor' )
			),
			'MentionAtEndWithFullstop' => array(
				'Please assign to {tag}vboctor.',
				array( 'vboctor' )
			),
			'MentionSeparatedWithColon' => array(
				'{tag}vboctor: please check.',
				array( 'vboctor' )
			),
			'MentionSeparatedWithSemiColon' => array(
				'{tag}vboctor; please check.',
				array( 'vboctor' )
			),
			'MentionWithMultiple' => array(
				'Please check with {tag}vboctor and {tag}someone.',
				array( 'vboctor', 'someone' )
			),
			'MentionWithDuplicates' => array(
				'Please check with {tag}vboctor and {tag}vboctor.',
				array( 'vboctor' )
			),
			'MentionWithMultipleSlashSeparated' => array(
				'{tag}vboctor/{tag}someone, please check.',
				array( 'vboctor', 'someone' )
			),
			'MentionWithMultipleNewLineSeparated' => array(
				"Check with:\n{tag}vboctor\n{tag}someone.",
				array( 'vboctor', 'someone' )
			),
			'MentionNl2br' => array(
				string_nl2br( "Check with {tag}vboctor\n" ) ,
				array( 'vboctor' )
			),
			'MentionWithEmailAddress' => array(
				'xxx{tag}example.com',
				array()
			),
			'MentionWithLocalhost' => array(
				'xxx{tag}localhost',
				array()
			),
			'MentionAtEndOfWord' => array(
				"{tag}vboctor{tag}",
				array()
			),
			'MentionWithInvalidChars' => array(
				"{tag}vboctor%%%%%",
				array( 'vboctor' )
			),
			'MentionUsernameThatIsAnEmailAddress' => array(
				"{tag}vboctor@example.com",
				array()
			),
			'MentionUsernameThatIsLocalhost' => array(
				"{tag}vboctor@localhost",
				array()
			),
		);

	}
}
