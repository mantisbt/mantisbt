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
 * This page stores the reported bug
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'adm_config_set' );

$f_user_id = gpc_get_int( 'user_id' );
$f_project_id = gpc_get_int( 'project_id' );
$f_config_option = trim( gpc_get_string( 'config_option' ) );
$f_type = gpc_get_string( 'type' );
$f_value = gpc_get_string( 'value' );

if( is_blank( $f_config_option ) ) {
	error_parameters( 'config_option' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

access_ensure_global_level( config_get( 'set_configuration_threshold' ) );

if( $f_project_id != ALL_PROJECTS ) {
	project_ensure_exists( $f_project_id );
}

# make sure that configuration option specified is a valid one.
$t_not_found_value = '***CONFIG OPTION NOT FOUND***';
if( config_get( $f_config_option, $t_not_found_value ) === $t_not_found_value ) {
	error_parameters( $f_config_option );
	trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, ERROR );
}

# make sure that configuration option specified can be stored in the database
if( !config_can_set_in_database( $f_config_option ) ) {
	error_parameters( $f_config_option );
	trigger_error( ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB, ERROR );
}

if( !config_can_delete( $f_config_option ) ) {
	error_parameters( $f_config_option );
	# @TODO define an error code for values that cant be set in DB, nor config_inc
	trigger_error( ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB, ERROR );
}


# For 'default', behavior is based on the global variable's type
if( $f_type == CONFIG_TYPE_DEFAULT ) {
	$t_config_global_value = config_get_global( $f_config_option );
	if( is_string( $t_config_global_value ) ) {
		$t_type = CONFIG_TYPE_STRING;
	} else if( is_int( $t_config_global_value ) ) {
		$t_type = CONFIG_TYPE_INT;
	} else if( is_float( $t_config_global_value ) ) {
		$t_type = CONFIG_TYPE_FLOAT;
	} else {
		# note that we consider bool and float as complex.
		# We use ON/OFF for bools which map to numeric.
		$t_type = CONFIG_TYPE_COMPLEX;
	}
} else {
	$t_type = $f_type;
}

switch( $t_type ) {
	case CONFIG_TYPE_STRING:
		$t_value = $f_value;
		break;
	case CONFIG_TYPE_INT:
		$t_value = (integer)constant_replace( trim( $f_value ) );
		break;
	case CONFIG_TYPE_FLOAT:
		$t_value = (float)constant_replace( trim( $f_value ) );
		break;
	case CONFIG_TYPE_COMPLEX:
	default:
		try {
			$t_parser = new Parser( $f_value );
			$t_value = $t_parser->parse();
		}
		catch (Exception $e) {
			error_parameters( $f_config_option, $f_value, $e->getMessage() );
			trigger_error(ERROR_CONFIG_OPT_BAD_SYNTAX, ERROR);
		}
		var_dump($t_value);
		break;
}

config_set( $f_config_option, $t_value, $f_user_id, $f_project_id );

form_security_purge( 'adm_config_set' );

print_successful_redirect( 'adm_config_report.php' );

/**
 * Class Tokenizer.
 * Uses PHP's internal token_get_all() function to parse a piece of code
 * into tokens
 */
class Tokenizer
{
	/**
	 * @var array $tokens
	 */
	protected $tokens;

	/**
	 * Tokenizer constructor.
	 * Builds the token array from given code, discarding whitespace and
	 * trailing semicolons
	 * @param string $p_code PHP code to tokenize
	 * @throws Exception if there are no tokens to process
	 * @throws Exception if given code is not valid
	 */
	public function __construct( $p_code )
	{
		if( empty( $p_code ) ) {
			throw new Exception( 'No more tokens' );
		}

		# Check syntax to make sure we get valid PHP code
		# prepend 'return' statement to ensure the code is not actually executed
		# Suppress errors as we can't capture STDERR with ob_ functions
		$result = @eval( 'return; ' . $p_code . ';' );
		if( $result === false ) {
			throw new Exception( 'Syntax error' );
		};

		$t_tokens = token_get_all( '<?php ' . $p_code );

		# Strip whitespace
		$t_tokens = array_filter( $t_tokens,
			function( $p_token ) {
				return !is_array( $p_token ) || $p_token[0] !== T_WHITESPACE;
			}
		);

		# Get rid of the opening '<?php' tag we added
		array_shift( $t_tokens );

		# Remove any trailing ';'
		while( true ) {
			$t_last = end( $t_tokens );
			if( $t_last != ';' ) {
				break;
			}
			array_pop( $t_tokens );
		}

		$this->tokens = $t_tokens;
	}

	/**
	 * Return true if we're at the end of the token array.
	 * @return bool
	 */
	public function is_empty() {
		return empty( $this->tokens );
	}

	/**
	 * Retrieves the next token without consuming it.
	 * @return mixed token
	 * @throws Exception if there are no more tokens to process
	 */
	public function get() {
		if( $this->is_empty() ) {
			throw new Exception( 'No more tokens' );
		}
		return $this->tokens[0];
	}

	/**
	 * Consume the next token and return it.
	 * @return mixed token
	 * @throws Exception
	 */
	public function pop() {
		$t_token = $this->get();
		array_shift( $this->tokens );
		return $t_token;
	}

	/**
	 * Get the current token's type.
	 * @link http://php.net/manual/en/tokens.php
	 * @return int|string Token number or character
	 */
	public function type() {
		$t_token = $this->get();
		return is_array( $t_token ) ? $t_token[0] : $t_token;
	}

	/**
	 * Get the current token's value.
	 * @return int|string Token number or character
	 */
	public function value() {
		$t_token = $this->get();
		return is_array( $t_token ) ? $t_token[1] : $t_token;
	}

	/**
	 * Return true if the next token matches the given value.
	 * @param int|string $p_value value to check
	 * @return bool
	 */
	public function matches( $p_value ) {
		$t_type = $this->type();
		return $t_type === $p_value;
	}

	/**
	 * Ensures the next token matches the value and consumes it.
	 * @param int|string $p_value value to check
	 * @throws Exception if token does not match
	 */
	public function ensure_matches( $p_value ) {
		if( !$this->matches( $p_value ) ) {
			if( is_int( $p_value ) ) {
				$p_value = token_name( $p_value );
			}
			throw new Exception(
				"Invalid token: got '" . $this->value() . "', expected '$p_value'"
			);
		}
		$this->pop();
	}

	/**
	 * Prints the tokens array.
	 * @TODO For debugging purposes only, should be deleted
	 */
	public function debug_output()
	{
		if( count( $this->tokens ) == 0 ) {
			echo "Empty !\n";
		}
		foreach ($this->tokens as $id => $token) {
			echo "$id - ";
			if( is_array( $token ) ) {
				echo token_name($token[0]) . " " . var_export( $token[1], true ) . "\n";
			} else {
				echo $token;
			}
		}
	}

}

/**
 * Class Parser.
 * Simple PHP code parser for scalar and array types
 */
class Parser
{
	/**
	 * @var Tokenizer $tokens
	 */
	protected $tokens;

	/**
	 * Parser constructor.
	 * @param $p_code PHP code to parse
	 */
	public function __construct( $p_code ) {
		$this->tokens = new Tokenizer( $p_code );
	}

	/**
	 * Parse the code for a variable assignment.
	 * Handles scalar types, and various array types (simple, associative,
	 * multi-dimentional)
	 * @return mixed variable
	 * @throws Exception when there are unexpected or extra tokens
	 */
	public function parse() {
		switch( $this->tokens->type() ) {
			case T_ARRAY:
				$t_result = $this->process_array();
				break;

			case T_CONSTANT_ENCAPSED_STRING:
			case T_STRING:
			case T_LNUMBER:
			case T_DNUMBER:
				return $this->process_value();

			default:
				throw new Exception( 'Unexpected token' );
		}

		# Make sure we have processed all tokens
		if( !$this->tokens->is_empty() ) {
			$this->tokens->debug_output();
			throw new Exception("Extra tokens");
		}

		return $t_result;
	}

	/**
	 * Recursively process array declarations.
	 * @return array
	 * @throws Exception when there's an invalid token
	 */
	protected function process_array() {
		$t_array = array();
		$t_count = 0;

		$this->tokens->ensure_matches( T_ARRAY );
		$this->tokens->ensure_matches( '(' );

		# Loop until we reach the end of the array
		while( !$this->tokens->matches( ')' ) ) {
			# A comma is required before each element except the first one
			if ($t_count > 0) {
				$this->tokens->ensure_matches(',');
			}

			switch( $this->tokens->type() ) {
				# Nested array
				case T_ARRAY:
					$t_array[] = $this->process_array();
					break;

				# Value
				case T_CONSTANT_ENCAPSED_STRING:
				case T_STRING:
				case T_LNUMBER:
				case T_DNUMBER:
					$t_str = $this->process_value();

					if( $this->tokens->matches( T_DOUBLE_ARROW ) ) {
						# key => value
						$this->tokens->pop();
						if( $this->tokens->matches( T_ARRAY ) ) {
							$t_array[$t_str] = $this->process_array();
						} else {
							$t_array[$t_str] = $this->process_value();
						}
					} else {
						# Simple value
						$t_array[] = $t_str;
					}
					break;

				case ')':
					# Cover the trailing ',' case
					break;

				default:
					throw new Exception("Invalid token '" . $this->tokens->value() . "'");
			}

			$t_count++;
		}
		$this->tokens->ensure_matches( ')' );

		return $t_array;
	}

	/**
	 * Process a scalar value.
	 * Handles string literals including defined constants
	 * @see constant_replace()
	 * @return mixed
	 * @throws Exception when there's an unexpected value
	 */
	protected function process_value() {
		# String literals
		if( $this->tokens->matches( T_STRING ) ) {
			$t_token = $this->tokens->pop();
			$t_value = $t_token[1];

			# PHP Standard string literals
			switch (strtolower($t_value)) {
				case 'null':
					return null;
				case 'true':
					return true;
				case 'false':
					return false;
			}

			# Defined constants
			$t_value = constant_replace( $t_value );
			if( $t_value != $t_token[1] ) {
				return $t_value;
			}

			throw new Exception("Unknown string literal '$t_value'");
		}

		# Strings
		if( $this->tokens->matches( T_CONSTANT_ENCAPSED_STRING ) ) {
			$t_value = $this->tokens->pop();
			return (string)stripslashes( substr( $t_value[1], 1, -1 ) );
		}

		# Numbers
		$t_negate = 1;
		if( $this->tokens->matches( '-' ) ) {
			$this->tokens->pop();
			$t_negate = -1;
		}
		if( $this->tokens->matches( '+' ) ) {
			$this->tokens->pop();
		}

		# Integers
		if( $this->tokens->matches( T_LNUMBER ) ) {
			$t_value = $this->tokens->pop();
			return $t_negate * (int)$t_value[1];
		}

		# Floating point
		if( $this->tokens->matches( T_DNUMBER ) ) {
			$t_value = $this->tokens->pop();
			return $t_negate * (float)$t_value[1];
		}

		# Anything else
		throw new Exception( "Unexpected value" );
	}
}

/**
 * Check if the passed string is a constant and returns its value
 * if yes, or the string itself if not
 * @param string $p_name String to check.
 * @return mixed|string value of constant $p_name, or $p_name itself
 */
function constant_replace( $p_name ) {
	if( is_string( $p_name ) && defined( $p_name ) ) {
		# we have a constant
		return constant( $p_name );
	}
	return $p_name;
}
