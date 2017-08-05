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
 * Configuration Parser class.
 * @copyright Copyright 2016 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */


/**
 * Configuration Parser class
 *
 * Simple PHP code parser for scalar and array types
 *
 * @package MantisBT
 * @subpackage classes
 *
 * @uses Tokenizer
 */
class ConfigParser
{
	/**
	 * Define how extra tokens should be handled by parse() method
	 */
	const EXTRA_TOKENS_IGNORE = 0;
	const EXTRA_TOKENS_ERROR = 1;

	/**
	 * @var Tokenizer $tokens
	 */
	protected $tokens;

	/**
	 * Parser constructor.
	 * @param string $p_code PHP code to parse
	 */
	public function __construct( $p_code ) {
		$this->tokens = new Tokenizer( $p_code );
	}

	/**
	 * Parse the code for a variable assignment.
	 * Handles scalar types, and various array types (simple, associative,
	 * multi-dimentional)
	 * @param integer $p_extra_tokens Define how extra tokens should be handled
	 *                             - EXTRA_TOKENS_IGNORE silently ignore any
	 *                               extra code given after the first token
	 *                             - EXTRA_TOKENS_ERROR (default) throws an
	 *                               exception if extra code is found
	 * @return mixed variable
	 * @throws Exception when there are unexpected or extra tokens
	 */
	public function parse( $p_extra_tokens = self::EXTRA_TOKENS_ERROR ) {
		switch( $this->tokens->type() ) {
			case T_ARRAY:
				$t_result = $this->process_array();
				break;

			case T_CONSTANT_ENCAPSED_STRING:
			case T_STRING:
			case T_LNUMBER:
			case T_DNUMBER:
			case '-':
			case '+':
				$t_result = $this->process_value();
				break;

			default:
				throw new Exception( 'Unexpected token "' . $this->tokens->value() . '"' );
		}

		# Make sure we have processed all tokens
		if( $p_extra_tokens == self::EXTRA_TOKENS_ERROR && !$this->tokens->is_empty() ) {
			throw new Exception( 'Extra tokens found "' . $this->tokens->get_string() .'":' );
		}

		return $t_result;
	}

	/**
	 * Check if the passed string is a constant and returns its value if yes,
	 * or the string itself if not
	 * @param string $p_name String to check.
	 * @return mixed|string value of constant $p_name, or $p_name itself
	 */
	public static function constant_replace( $p_name ) {
		$t_name = trim( $p_name );
		if( is_string( $t_name ) && defined( $t_name ) ) {
			# we have a constant
			return constant( $t_name );
		}
		return $t_name;
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
			$t_value = $this->constant_replace( $t_value );
			if( $t_value !== $t_token[1] ) {
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
		throw new Exception( "Unexpected value" . $this->tokens->value() );
	}
}
