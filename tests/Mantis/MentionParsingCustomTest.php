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
 * @copyright Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

/**
 * Test cases for parsing functionality in mention API with a custom tag.
 * @package    Tests
 * @subpackage Mention
 */
final class MentionParsingCustomTest extends MentionParsingTest {
	
	private static $mentions_tag;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$mentions_tag = config_get( 'mentions_tag' );
		config_set( 'mentions_tag', '$' );
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();

		config_set( 'mentions_tag', self::$mentions_tag );
	}
}
