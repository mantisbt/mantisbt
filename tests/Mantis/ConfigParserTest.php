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
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';

# Mantis Core required for class autoloader and constants
require_mantis_core();

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
class MantisConfigParserTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var array List of test cases for scalar types
	 */
	private $cases_scalar = array();

	/**
	 * @var array List of test cases for arrays
	 */
	private $cases_array = array();

	/**
	 * MantisConfigParserTest constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->initScalarTestCases();
		$this->initArrayTestCases();
	}

	/**
	 * Test a list of strings representing scalar values, making sure
	 * the value and the type match
	 *
	 * @throws Exception
	 */
	public function testScalarTypes() {
		foreach( $this->cases_scalar as $t_string => $t_expected_type ) {
			$t_reference_result = eval( 'return ' . $t_string . ';' );

			$t_parser = new ConfigParser( $t_string );
			$t_parsed_result = $t_parser->parse();

			$this->assertInternalType( $t_expected_type, $t_parsed_result );
			$this->assertEquals( $t_parsed_result, $t_reference_result, $this->errorMessage( $t_string ) );
		}
	}

	/**
	 * Test various types of arrays
	 *
	 * @see initArrayTestCases
	 * @throws Exception
	 */
	public function testArrays() {
		foreach( $this->cases_array as $t_string ) {
			$t_reference_result = eval( 'return ' . $t_string . ';' );

			# Check that the parsed array matches the model array
			$t_parser = new ConfigParser( $t_string );
			$t_parsed_1 = $t_parser->parse();
			$this->assertEquals( $t_parsed_1, $t_reference_result, $this->errorMessage( $t_string )  );

			# Export converted array and parse again: result should match the model
			$t_parser = new ConfigParser( var_export( $t_parsed_1 , true ) );
			$t_parsed_2 = $t_parser->parse();
			$this->assertEquals( $t_parsed_2, $t_reference_result, $this->errorMessage( $t_string )  );
		}
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
	 * Adds a new scalar test case to the list
	 *
	 * @param string $p_string Value to check
	 * @param string $p_type   Expected type
	 */
	private function addScalarCase( $p_string, $p_type ) {
		$this->cases_scalar[$p_string] = $p_type;
	}

	/**
	 * Initialize the Scalar type test cases
	 */
	private function initScalarTestCases() {
		# Integer
		$this->addScalarCase( '1', PHPUnit_Type::TYPE_INT );
		$this->addScalarCase( " 1\n", PHPUnit_Type::TYPE_INT );

		# Float
		$this->addScalarCase( '1.1', PHPUnit_Type::TYPE_FLOAT );

		# String
		$this->addScalarCase( '"1"', PHPUnit_Type::TYPE_STRING );
		$this->addScalarCase( "'1'", PHPUnit_Type::TYPE_STRING );

		# Built-in string literals
		$this->addScalarCase( 'null', PHPUnit_Type::TYPE_NULL );
		$this->addScalarCase( 'false', PHPUnit_Type::TYPE_BOOL );
		$this->addScalarCase( 'true', PHPUnit_Type::TYPE_BOOL );

		# Constants
		$this->addScalarCase( 'VERSION_ALL', PHPUnit_Type::TYPE_NULL );         # null
		$this->addScalarCase( 'VERSION_FUTURE', PHPUnit_Type::TYPE_BOOL );      # false
		$this->addScalarCase( 'VERSION_RELEASED', PHPUnit_Type::TYPE_BOOL );    # true
		$this->addScalarCase( 'OFF', PHPUnit_Type::TYPE_INT );                  # 0
		$this->addScalarCase( 'DEVELOPER', PHPUnit_Type::TYPE_INT );            # int
		$this->addScalarCase( " DEVELOPER\n", PHPUnit_Type::TYPE_INT );
		$this->addScalarCase( 'MANTIS_VERSION', PHPUnit_Type::TYPE_STRING );    #string
		$this->addScalarCase( " MANTIS_VERSION\n", PHPUnit_Type::TYPE_STRING );
	}
	
	/**
	 * Initialize the array test cases list
	 */
	private function initArrayTestCases() {
		/**
		 * Template for new test cases
		 * ---------------------------
		# comment
		$this->addArrayCase(
<<<'EOT'
EOT
 		);
		 * ---------------------------
 */

		/**
		 * Simple arrays
		 */

		# empty
		$this->addArrayCase( "array( )" );

		# one element
		$this->addArrayCase( "array( 1 )" );

		# several elements, trailing delimiter
		$this->addArrayCase( "array( 1, 2, )" );

		# formatted whitespace
		$this->addArrayCase( "array ( 1, 2, 3 )" );

		# no whitespace
		$this->addArrayCase( "array(1,2,3)" );

		# arbitrary whitespace
		$this->addArrayCase( "  array(\n1,\t2  ,    3 )\r  " );

		# mixed types, quotes
		$this->addArrayCase(
<<<'EOT'
array( 1, 'a', "b" )
EOT
		);

		# nested quotes
		$this->addArrayCase(
<<<'EOT'
array( '"a""b"""', "'a''b'''" )
EOT
		);

		/**
		 * Associative arrays
		 */

		# associative
		$this->addArrayCase( "array( 0 => 'a', 1 => 'b' )" );

		# associative, unordered keys
		$this->addArrayCase( "array( 5 => 'a', 2 => 'b' )" );

		# associative, text keys
		$this->addArrayCase( "array( 'i' => 'a', 'j' => 'b' )" );

		# associative, mixed keys
		$this->addArrayCase( "array( 'i' => 'a', 1 => 'b', 'j' => 'c', 7 => 'd' )" );

		# mixed associative, omitting some keys
		$this->addArrayCase( "array( 'i' => 'a', 1 => 'b', 'c', 'j' => 'd' )" );

		# mixed associative, overwriting implicit keys
		$this->addArrayCase( "array( 0 => 'a0', 1 => 'a1', 'axx', 2 => 'a2' )" );

		$this->addArrayCase(
<<<'EOT'
array(
	array ( 1, 'a', 3 => 1, 4 => 'b', 'x' => 'y' )
)
EOT
		);

		/**
		 * Use of constants
		 */

		# As array values (e.g. handle_bug_threshold)
		$this->addArrayCase( "array( DEVELOPER, MANAGER )" );

		# As array keys (e.g. status_enum_workflow)
		$this->addArrayCase(
<<<'EOT'
array (
  NEW_ => '20:feedback,30:acknowledged',
  ACKNOWLEDGED => '40:confirmed',
)
EOT
		);

		# both (e.g. set_status_threshold)
		$this->addArrayCase( 'array( NEW_ => REPORTER )' );

		/**
		 * Multidimensional arrays
		 */

		# notify_flags sample
		$this->addArrayCase(
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
		);

		$this->addArrayCase(
<<<'EOT'
array(
	1 => array( 1, 2 => array() ),
	array( 'a', 'b', array(3, 4, ) ),
	'c' => array( 'd', 5 => 'e' ),
)
EOT
		);

		/**
		 * Test cases for specific issues reported on the bugtracker
		 */

		# Test case for issue #0020787
		$this->addArrayCase(
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
		);

		# Test case for issue #0020812
		$this->addArrayCase(
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
		);

		# Test case for issue #0020813
		$this->addArrayCase(
<<<'EOT'
array(
 0 => "aa'aa",
 1 => "bb\"bb"
)
EOT
		);

		# Test case for issue #0020850
		$this->addArrayCase(
			<<<'EOT'
			array ( 0 => '""a"' )
EOT
		);

		# Test case for issue #0020851
		$this->addArrayCase(
<<<'EOT'
array (
	'a' => 'x1',
	'x2',
)
EOT
		);
	}
}
