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
 * Test cases for markdown handling within mantis
 *
 * @package    Tests
 * @subpackage String
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once( dirname( __FILE__, 2 ) . '../../../tests/TestConfig.php' );
require_once( dirname( __FILE__, 2 ) . '/core/MantisMarkdown.php' );

# MantisBT Core API
require_mantis_core();

/**
 * Mantis markdown handling test cases
 * @package    Tests
 * @subpackage Markdown
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class MantisMarkdownTest extends PHPUnit\Framework\TestCase {

	/**
	 * Test If string starts with hash character followed by letters
	 * @return void
	 */
	public function testHashLetters() {
		$this->assertEquals( '<h1>hello</h1>', MantisMarkdown::convert_text( '# hello' ) );
		$this->assertEquals( '<h1>hello</h1>', MantisMarkdown::convert_text( '#hello' ) );
	}

	/**
	 * Test If string starts with hash character followed by number and letters
	 * @return void
	 */
	public function testHashNumberAny() {
		$this->assertEquals( '<h1>1abcd</h1>', MantisMarkdown::convert_text( '# 1abcd' ) );
		$this->assertEquals( '<h1>1abcd</h1>', MantisMarkdown::convert_text( '#1abcd' ) );
	}

	/**
	 * Test If string starts with hash character followed by letters and numbers
	 * @return void
	 */
	public function testHashLettersAny() {
		$this->assertEquals( '<h1>abcd1234</h1>', MantisMarkdown::convert_text( '# abcd1234' ) );
		$this->assertEquals( '<h1>abcd1234</h1>', MantisMarkdown::convert_text( '#abcd1234' ) );
	}

	/**
	 * Test If string starts with hash character followed by numbers
	 * since the class overrides the default Markdown parsing on Header
	 * then the methods should return the standard text.
	 * @return void
	 */
	public function testHashNumbers() {
		$this->assertEquals( '<p>#1</p>', MantisMarkdown::convert_text( '#1' ) );
	}

	/**
	 * Test if table class attribute is defined
	 * @return void
	 */
	public function testTableClassDefined() {
		$markdown_table = <<<EOD
| header |
| ---    |
| cell   |
EOD;
		$this->assertTrue( false !== strpos( MantisMarkdown::convert_text( $markdown_table ), 'class="table table-nonfluid"' ));
	}
}
