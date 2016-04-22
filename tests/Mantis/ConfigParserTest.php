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
 * Test cases for config API
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

require_once 'config_api.php';

/**
 * Test cases for config API parser
 * @package    Tests
 * @subpackage ConfigParser
 */
class Mantis_ConfigParserTest extends PHPUnit_Framework_TestCase {

	# This array contains definition of arrays with a PHP correct syntax
	# This test cases will be parsed and compared to its eval() version 
	# interpreted by PHP
	private $test_correct_syntax = array();

	public function __construct() {
		$this->test_correct_syntax[] = "array( 'a' => 1, 2 )";

/*
 * Template

		# comment
		$this->test_correct_syntax[] =
<<<'EOT'
EOT;

 *
 */

		# no whitespace
		$this->test_correct_syntax[] = "array(1,2,3)";

		# formatted whitespace
		$this->test_correct_syntax[] = "array ( 1, 2, 3 )";

		# arbitrary whitespace
		$this->test_correct_syntax[] = "  array(  1,2  ,    3 )  ";

		# one element
		$this->test_correct_syntax[] = "array( 1 )";

		# several elements, trailing delimiter
		$this->test_correct_syntax[] = "array( 1, 2, )";

		# empty
		$this->test_correct_syntax[] = "array( )";

		# mixed types, quotes
		$this->test_correct_syntax[] =
<<<'EOT'
array( 1, 'a', "b" )
EOT;

		# nested quotes
		# @TODO this fails
		/*
		$this->test_correct_syntax[] =
<<<'EOT'
array( '"a""b"""', "'a''b'''" )
EOT;
		 *
		 */

		# associative
		$this->test_correct_syntax[] = "array( 0 => 'a', 1 => 'b' )";

		# associative, unordered keys
		$this->test_correct_syntax[] = "array( 5 => 'a', 2 => 'b' )";

		# associative, text keys
		$this->test_correct_syntax[] = "array( 'i' => 'a', 'j' => 'b' )";

		# associative, mixed keys
		$this->test_correct_syntax[] = "array( 'i' => 'a', 1 => 'b', 'j' => 'c', 7 => 'd' )";

		# mixed associative, omitting some keys
		$this->test_correct_syntax[] = "array( 'i' => 'a', 1 => 'b', 'c', 'j' => 'd' )";

		# mixed associative, overwriting implicit keys
		$this->test_correct_syntax[] = "array( 0 => 'a0', 1 => 'a1', 'axx', 2 => 'a2' )";

		#@TODO this fails
		$this->test_correct_syntax[] =
<<<'EOT'
array(
	array ( 1, 'a', 3 => 1, 4 => 'b', 'x' => 'y' )
)
EOT;

		# Test case for issue #0020787
		$this->test_correct_syntax[] =
<<<'EOT'
array (
	'additional_info',
	'attachments',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
)
EOT;

		# Test case for issue #0020850
		$this->test_correct_syntax[] =
<<<'EOT'
array ( 0 => '""a"' )
EOT;

		# Test case for issue #0020812
		$this->test_correct_syntax[] =
<<<'EOT'
array (
	0 =>
	array (
		0 => 1,
		1 => 2,
		2 => 3,
	),
)
EOT;

		# Test case for issue #0020851
		$this->test_correct_syntax[] =
<<<'EOT'
array (
	'a' => 'x1',
	'x2',
)
EOT;

	}

	public function testParserCorrectSyntax() {
		foreach( $this->test_correct_syntax as $t_string ) {
			$t_eval_result = eval( 'return ' . $t_string . ';' );
			$this->checkParserArray( $t_string, $t_eval_result );
		}
	}

	private function checkParserArray( $p_text, $p_expected_array ) {
		$t_message = "Original input was :\n"
				. ">>>------------------------\n"
				. $p_text . "\n"
				. "<<<------------------------\n";

		# Check that the parsed array matches the model array
		$t_parsed = config_process_complex_value( $p_text );
		$this->assertEquals( json_encode( $p_expected_array ), json_encode( $t_parsed ), $t_message  );
		$this->assertEquals( serialize( $p_expected_array ), serialize( $t_parsed ), $t_message  );

		# Export the converted array, and parse again.
		# The result should match both the model and the previously parsed array
		$t_export = var_export( $p_expected_array , true );
		$t_parsed2 = config_process_complex_value( $t_export );
		$this->assertEquals( json_encode( $p_expected_array ), json_encode( $t_parsed2 ), $t_message );
		$this->assertEquals( json_encode( $t_parsed ), json_encode( $t_parsed2 ), $t_message );
		$this->assertEquals( serialize( $p_expected_array ), serialize( $t_parsed2 ), $t_message );
		$this->assertEquals( serialize( $t_parsed ), serialize( $t_parsed2 ), $t_message );
	}
}

