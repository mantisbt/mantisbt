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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';
require_once dirname( dirname( __FILE__ ) ) . '/../core/classes/MantisMarkdown.php';

# MantisBT Core API
require_mantis_core();

/**
 * Mantis markdown handling test cases
 * @package    Tests
 * @subpackage Markdown
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class MantisMarkdownTest extends PHPUnit_Framework_TestCase {

	protected static $markdown;

	/**
	 * Initiazed MantisMarkdown class before a test method run
	 * @return void
	 */
	public static function setUpBeforeClass(){
        self::$markdown = new MantisMarkdown();
    }	

	/**
	 * Test If string starts with hash character followed by letters
	 * @return void
	 */
	public function testHashLetters() {
		$this->assertEquals( '<h1>hello</h1>', self::$markdown->text( '# hello' ) );
		$this->assertEquals( '<h1>hello</h1>', self::$markdown->text( '#hello' ) );
    }

    /**
	 * Test If string starts with hash character followed by number and letters
	 * @return void
	 */
	public function testHashNumberAny() {
		$this->assertEquals( '<h1>1abcd</h1>', self::$markdown->text( '# 1abcd' ) );
		$this->assertEquals( '<h1>1abcd</h1>', self::$markdown->text( '#1abcd' ) );
    }

	/**
	 * Test If string starts with hash character followed by letters and numbers
	 * @return void
	 */
	public function testHashLettersAny() {
		$this->assertEquals( '<h1>abcd1234</h1>', self::$markdown->text( '# abcd1234' ) );
		$this->assertEquals( '<h1>abcd1234</h1>', self::$markdown->text( '#abcd1234' ) );
	}

	/**
	 * Test If string starts with hash character followed by numbers
	 * @return void
	 */
	public function testHashNumbers() {
		$this->assertEquals( '<p>#1</p>', self::$markdown->text( '#1' ) );
	}
	
    
}
