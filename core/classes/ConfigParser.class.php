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
