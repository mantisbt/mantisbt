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
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';

require_mantis_core();

require_api( 'mention_api.php' );

/**
 * Test cases for parsing functionality in mention API.
 * @package    Tests
 * @subpackage Mention
 */
class MentionParsingTest extends PHPUnit_Framework_TestCase {
	public function testNoMention() {
		$this->checkMentions( 'some random string.', array() );
	}

	public function testNoMentionWithAtSign() {
		$this->checkMentions( 'some random string with @ sign.', array() );
	}

	public function testNoMentionWithMultipleAtSigns() {
		$this->checkMentions( 'some random string with @@vboctor sign.', array() );
	}

	public function testJustMention() {
		$this->checkMentions( '@vboctor', array( 'vboctor' ) );
	}

	public function testWithDotInMiddle() {
		$this->checkMentions( '@victor.boctor', array( 'victor.boctor' ) );
	}

	public function testWithDotAtEnd() {
		$this->checkMentions( '@vboctor.', array( 'vboctor' ) );
	}

	public function testMentionWithUnderscore() {
		$this->checkMentions( '@victor_boctor', array( 'victor_boctor' ) );
	}

	public function testMentionAtStart() {
		$this->checkMentions( '@vboctor will check', array( 'vboctor' ) );
	}

	public function testMentionAtEnd() {
		$this->checkMentions( 'Please assign to @vboctor', array( 'vboctor' ) );
	}

	public function testMentionAtEndWithFullstop() {
		$this->checkMentions( 'Please assign to @vboctor.', array( 'vboctor' ) );
	}

	public function testMentionSeparatedWithColon() {
		$this->checkMentions( '@vboctor: please check.', array( 'vboctor' ) );
	}

	public function testMentionSeparatedWithSemiColon() {
		$this->checkMentions( '@vboctor; please check.', array( 'vboctor' ) );
	}

	public function testMentionWithMultiple() {
		$this->checkMentions( 'Please check with @vboctor and @someone.', array( 'vboctor', 'someone' ) );
	}

	public function testMentionWithDuplicates() {
		$this->checkMentions( 'Please check with @vboctor and @vboctor.', array( 'vboctor' ) );
	}

	public function testMentionWithMultipleSlashSeparated() {
		$this->checkMentions( '@vboctor/@someone, please check.', array( 'vboctor', 'someone' ) );
	}

	public function testMentionWithMultipleNewLineSeparated() {
		$this->checkMentions( "Check with:\n@vboctor\n@someone.", array( 'vboctor', 'someone' ) );
	}

	public function testMentionWithEmailAddress() {
		$this->checkMentions( 'xxx@example.com', array() );
	}

	public function testMentionWithLocalhost() {
		$this->checkMentions( 'xxx@localhost', array() );
	}

	public function testMentionAtEndOfWord() {
		$this->checkMentions( "vboctor@", array() );
	}

	public function testMentionWithInvalidChars() {
		$this->checkMentions( "@vboctor%%%%%", array() );
	}

	public function testMentionUsernameThatIsAnEmailAddress() {
		$this->checkMentions( "@vboctor@example.com", array() );
	}

	public function testMentionUsernameThatIsLocalhost() {
		$this->checkMentions( "@vboctor@localhost", array() );
	}

	private function checkMentions( $p_text, $p_expected_array ) {
		$t_array = mention_get_candidates( $p_text );
		
		$this->assertEquals( count( $p_expected_array ), count( $t_array ) );
		for( $i = 0; $i < count( $p_expected_array ); $i++ ) {
			$this->assertEquals( $p_expected_array[$i], $t_array[$i] );
		}
	}
}
