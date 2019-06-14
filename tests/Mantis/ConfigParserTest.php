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
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Includes
 */
require_once 'MantisCoreBase.php';

use PHPUnit_Framework_Constraint_IsType as PHPUnit_Type;


/**
 * Test cases for config API parser
 *
 * A list of test strings (as entered in adm_config_report page) will be parsed
 * by MantisBT core, and the result compared to PHP's native interpretation
 * using eval().
 *
 * @package    Tests
 * @subpackage ConfigParser
 */
class MantisConfigParserTest extends MantisCoreBase {

	/**
	 * Test with empty string or null
	 *
	 * @throws Exception
	 */
	public function testNoTokens() {
		$this->setExpectedException( 'Exception', 'No more tokens' );
		$t_parser = new ConfigParser( '' );
		$t_parser->parse();
		$t_parser = new ConfigParser( null );
		$t_parser->parse();
	}

	/**
	 * Test a list of strings representing scalar values, making sure
	 * the value and the type match
	 * @dataProvider providerScalarTypes
	 *
	 * @param string $p_string The string to parse
	 * @param string $p_type   Expected type (PHPUnit_Type::TYPE_xxx constant)
	 *
	 * @throws Exception
	 */
	public function testScalarTypes( $p_string, $p_type ) {
		$t_reference_result = eval( 'return ' . $p_string . ';' );

		$t_parser = new ConfigParser( $p_string );
		$t_parsed_result = $t_parser->parse();

		$this->assertInternalType( $p_type, $t_parsed_result );
		$this->assertEquals( $t_parsed_result, $t_reference_result, $this->errorMessage( $p_string ) );
	}

	/**
	 * Test various types of arrays
	 * @dataProvider providerArrays

	 * @param string $p_string Representation of array (e.g. output of var_export)
	 *
	 * @throws Exception
	 */
	public function testArrays( $p_string ) {
		$t_reference_result = eval( 'return ' . $p_string . ';' );

		# Check that the parsed array matches the model array
		$t_parser = new ConfigParser( $p_string );
		$t_parsed_1 = $t_parser->parse();
		$this->assertEquals( $t_parsed_1, $t_reference_result, $this->errorMessage( $p_string )  );

		# Export converted array and parse again: result should match the model
		$t_parser = new ConfigParser( var_export( $t_parsed_1 , true ) );
		$t_parsed_2 = $t_parser->parse();
		$this->assertEquals( $t_parsed_2, $t_reference_result, $this->errorMessage( $p_string )  );
	}

	/**
	 * Test failure if we get extra tokens when the parser is set to error
	 *
	 * @throws Exception
	 */
	public function testExtraTokensError() {
		$this->setExpectedExceptionRegExp('Exception', '/^Extra tokens found/');

		$t_parser = new ConfigParser( '1; 2' );
		$t_parser->parse( ConfigParser::EXTRA_TOKENS_ERROR );

		$t_parser = new ConfigParser( 'array(); 2' );
		$t_parser->parse( ConfigParser::EXTRA_TOKENS_ERROR );
	}

	/**
	 * Test no errors if we get extra tokens when the parser is set to ignore
	 *
	 * @throws Exception
	 */
	public function testExtraTokensIgnore() {
		$t_parser = new ConfigParser( '1; 2' );
		$t_result = $t_parser->parse( ConfigParser::EXTRA_TOKENS_IGNORE );
		$this->assertEquals( $t_result, 1 );

		$t_parser = new ConfigParser( 'array(); 2' );
		$t_result = $t_parser->parse( ConfigParser::EXTRA_TOKENS_IGNORE );
		$this->assertEquals( $t_result, array() );
	}

	/**
	 * Parser should error out if given string is not syntactically correct
	 *
	 * @throws Exception
	 */
	public function testSyntaxError() {
		$this->setExpectedExceptionRegExp('Exception', '/^syntax error/');

		$t_parser = new ConfigParser( 'array(' );
		$t_parser->parse();
	}

	/**
	 * Parser only accepts arrays, scalar types and constants
	 *
	 * @throws Exception
	 */
	public function testInvalidTokensError() {
		$this->setExpectedExceptionRegExp('Exception', '/^Unexpected token/');

		$t_parser = new ConfigParser( 'echo 1;' );
		$t_parser->parse();
	}

	/**
	 * Use of undefined constant should trigger an error
	 *
	 * @throws Exception
	 */
	public function testUnknownConstantError() {
		$this->setExpectedExceptionRegExp('Exception', '/^Unknown string literal/');

		# Make sure we have a string that is not a defined constant
		$t_constant = 'UNDEFINED_CONSTANT';
		while( defined($t_constant) ) {
			$t_constant .= '_' . rand(0, 9999);
		}
		$t_parser = new ConfigParser( $t_constant );
		$t_parser->parse();
	}

	/**
	 * Display original string in case of error to help troubleshooting
	 *
	 * @param $p_text
	 * @return string
	 */
	private function errorMessage( $p_text ) {
		return "Original input:\n"
			. ">>>------------------------\n"
			. $p_text . "\n"
			. "<<<------------------------\n";
	}

	/**
	 * Adds a new test case to the list
	 *
	 * @param string $p_string
	 */
	private function addArrayCase( $p_string ) {
		$this->cases_array[] = $p_string;
	}

	/**
	 * Data provider for Scalar types test cases.
	 * Test case structure:
	 *   <test case> => array( <string to test>, <expected type> )
	 * @return array
	 */
	public function providerScalarTypes() {
		return array(
			'Integer Zero' => array( '0', PHPUnit_Type::TYPE_INT ),
			'Integer One' => array( '1', PHPUnit_Type::TYPE_INT ),
			'Integer with whitespace' => array( " 1\n", PHPUnit_Type::TYPE_INT ),
			'Integer negative' => array( '-1', PHPUnit_Type::TYPE_INT ),
			'Integer positive' => array( '+1', PHPUnit_Type::TYPE_INT ),
	
			'Float' => array( '1.1', PHPUnit_Type::TYPE_FLOAT ),
			'Float negative' => array( '-1.1', PHPUnit_Type::TYPE_FLOAT ),
			'Float positive' => array( '+1.1', PHPUnit_Type::TYPE_FLOAT ),
			'Float scientific' => array( '1.2e3', PHPUnit_Type::TYPE_FLOAT ),

			'String empty double-quote' => array( '""', PHPUnit_Type::TYPE_STRING ),
			'String empty single-quote' => array( "''", PHPUnit_Type::TYPE_STRING ),
			'String whitespace' => array( '" "', PHPUnit_Type::TYPE_STRING ),
			'String number double-quote' => array( '"1"', PHPUnit_Type::TYPE_STRING ),
			'String number single-quote' => array( "'1'", PHPUnit_Type::TYPE_STRING ),
	
			'Built-in string literal null' => array( 'null', PHPUnit_Type::TYPE_NULL ),
			'Built-in string literal false' => array( 'false', PHPUnit_Type::TYPE_BOOL ),
			'Built-in string literal true' => array( 'true', PHPUnit_Type::TYPE_BOOL ),
	
			'Constant = null' => array( 'VERSION_ALL', PHPUnit_Type::TYPE_NULL ),
			'Constant = false' => array( 'VERSION_FUTURE', PHPUnit_Type::TYPE_BOOL ),
			'Constant = true' => array( 'VERSION_RELEASED', PHPUnit_Type::TYPE_BOOL ),
			'Constant = 0' => array( 'OFF', PHPUnit_Type::TYPE_INT ),
			'Constant integer' => array( 'DEVELOPER', PHPUnit_Type::TYPE_INT ),
			'Constant integer with whitespace' => array( " DEVELOPER\n", PHPUnit_Type::TYPE_INT ),
			'Constant string' => array( 'MANTIS_VERSION', PHPUnit_Type::TYPE_STRING ),
			'Constant string with whitespace' => array( " MANTIS_VERSION\n", PHPUnit_Type::TYPE_STRING ),
		);
	}

	/**
	 * Data provider for Arrays test cases.
	 * Test case structure:
	 *   <test case> => array( <string to test> )
	 * @return array
	 * Initialize the array test cases list
	 */
	public function providerArrays() {
		/**
		 * Template for new test cases
		 * ---------------------------
		'case description' => array(
<<<'EOT'
EOT
 		),
		 * ---------------------------
 */

		return array(
			/**
			 * Simple arrays
			 */
			'SimpleArray empty' => array( "array( )" ),
			'SimpleArray one element' => array( "array( 1 )" ),
			'SimpleArray several elements, trailing delimiter' => array( "array( 1, 2, )" ),
			'SimpleArray formatted whitespace' => array( "array ( 1, 2, 3 )" ),
			'SimpleArray no whitespace' => array( "array(1,2,3)" ),
			'SimpleArray arbitrary whitespace' => array( "  array(\n1,\t2  ,    3 )\r  " ),
			'SimpleArray mixed types, quotes' => array(
<<<'EOT'
array( 1, 'a', "b" )
EOT
			),
			'SimpleArray nested quotes' => array(
<<<'EOT'
array( '"a""b"""', "'a''b'''" )
EOT
		),

			/**
			 * Associative arrays
			 */
			'AssocArray' => array( "array( 0 => 'a', 1 => 'b' )" ),
			'AssocArray, unordered keys' => array( "array( 5 => 'a', 2 => 'b' )" ),
			'AssocArray, text keys' => array( "array( 'i' => 'a', 'j' => 'b' )" ),
			'AssocArray mixed keys' => array( "array( 'i' => 'a', 1 => 'b', 'j' => 'c', 7 => 'd' )" ),
			'AssocArray mixed, keys omitted' => array( "array( 'i' => 'a', 1 => 'b', 'c', 'j' => 'd' )" ),

			# mixed associative, overwriting implicit keys
			'AssocArray mixed, overwritten implicit keys' => array( "array( 0 => 'a0', 1 => 'a1', 'axx', 2 => 'a2' )" ),

			'AssocArray mixed' => array(
<<<'EOT'
array(
	array ( 1, 'a', 3 => 1, 4 => 'b', 'x' => 'y' )
)
EOT
			),

			/**
			 * Use of constants
			 */

			# e.g. handle_bug_threshold
			'Constants as array values' => array( "array( DEVELOPER, MANAGER )" ),

				# e.g. status_enum_workflow
			'Constants as array keys' => array(
<<<'EOT'
array (
  NEW_ => '20:feedback,30:acknowledged',
  ACKNOWLEDGED => '40:confirmed',
)
EOT
			),

			# e.g. set_status_threshold
			'Constants as both key and value' => array( 'array( NEW_ => REPORTER )' ),

			/**
			 * Multidimensional arrays
			 */
			'Multidimentional array' => array(
				<<<'EOT'
array(
	1 => array( 1, 2 => array() ),
	array( 'a', 'b', array(3, 4, ) ),
	'c' => array( 'd', 5 => 'e' ),
)
EOT
			),

			'Multidimentional, notify_flags sample' => array(
<<<'EOT'
array(
	'updated' => array (
		'reporter' => ON,
		'handler' => ON,
		'monitor' => ON,
		'bugnotes' => OFF,
		'threshold_min' => DEVELOPER,
		'threshold_max' => MANAGER,
	),
	'owner' => array (
		'reporter' => 1,
		'handler' => 1,
		'monitor' => 1,
		'bugnotes' => 1,
		'threshold_min' => 55,
	),
	'reopened' => array (
		'reporter' => 1,
		'handler' => 1,
		'monitor' => 1,
		'bugnotes' => 1,
		'threshold_max' => ANYBODY,
	),
)
EOT
			),

			/**
			 * Test cases for specific issues reported on the bugtracker
			 */
			'Issue #0020787' => array(
<<<'EOT'
array (
	'additional_info',
	'attachments',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
)
EOT
			),

			'Issue #0020812' => array(
<<<'EOT'
array (
	0 =>
	array (
		0 => 1,
		1 => 2,
		2 => 3,
	),
)
EOT
			),

			'Issue #0020813' => array(
<<<'EOT'
array(
 0 => "aa'aa",
 1 => "bb\"bb"
)
EOT
			),

			'Issue #0020850' => array(
<<<'EOT'
			array ( 0 => '""a"' )
EOT
			),

			'Issue #0020851' => array(
<<<'EOT'
array (
	'a' => 'x1',
	'x2',
)
EOT
			),
		);
	}
}
