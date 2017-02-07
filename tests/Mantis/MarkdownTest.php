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
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';
require_once dirname( dirname( __FILE__ ) ) . '/../plugins/MantisCoreFormatting/core/MantisMarkdown.php';

# MantisBT Core API
require_mantis_core();

/**
 * Mantis markdown handling test cases
 * @package    Tests
 * @subpackage Markdown
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class MantisMarkdownTest extends PHPUnit_Framework_TestCase {

	/**
	 * Mantis Text Processing
	 *
	 * @return string Formatted text
	 */
	private function processText( $p_string ) {
		
		$t_string = $p_string;
		
		$t_string = string_strip_hrefs( $t_string );
		$t_string = string_html_specialchars( $t_string );
		$t_string = string_restore_valid_html_tags( $t_string, true );
		
		# TODO: make sure to process the bug/note link
		# NOTE: helper is throwing a dependency error
		# PHP Fatal error:  Call to a member function Param() on null in core/database_api.php on line 85 
		//$t_string = string_process_bug_link( $t_string );
		//$t_string = string_process_bugnote_link( $t_string );
		//$t_string = mention_format_text( $t_string, true );

		return $t_string;
	}

	/**
	 * Test If string starts with hash(#) symbol
	 *
	 * @return void
	 */
	public function testHeader() {

		$t_expected_string = '<h1>hello</h1>';
		
		# followed by letter
		$t_input_string = '#hello';
		$t_process_string = $this->processText( $t_input_string );
		$this->assertEquals( $t_expected_string, MantisMarkdown::convert_text( $t_process_string ) );

		# followed by space
		$t_input_string = '# hello';
		$t_process_string = $this->processText( $t_input_string );
		$this->assertEquals( $t_expected_string, MantisMarkdown::convert_text( $t_process_string ) );

		# followed by numeric, markdown must ignore this
		# this is consider as bug/note link
		$t_input_string = '#1';
		
		$t_process_string = $this->processText( $t_input_string );

		$this->assertEquals( '<p>#1</p>', MantisMarkdown::convert_text( $t_process_string ) );


	}

}