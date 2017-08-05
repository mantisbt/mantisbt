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
 * Tokenizer class.
 * @copyright Copyright 2016 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */


/**
 * Tokenizer class
 *
 * Uses PHP's internal token_get_all() function to parse a piece of code
 * into tokens
 *
 * @package MantisBT
 * @subpackage classes
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
	 * @throws Exception if given code is not valid
	 */
	public function __construct( $p_code )
	{
		# Check syntax to make sure we get valid PHP code
		# prepend 'return' statement to ensure the code is not actually executed
		$t_code = 'return; ' . $p_code . ';';
		if( version_compare( PHP_VERSION, '7', '>=' ) ) {
			# In PHP >= 7, eval() throws a ParseError which we can catch
			# and rethrow as an Exception
			try {
				eval( $t_code );
			}
			catch( ParseError $e ) {
				throw new Exception( $e->getMessage() );
			}
		} else {
			# In earlier PHP versions, eval() simply returns false and outputs
			# an error message to STDERR, but this can't be capture using ob_
			# functions so we suppress errors and throw a generic error message
			$result = @eval( $t_code );
			if( $result === false ) {
				throw new Exception( 'syntax error' );
			}
		}

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
	 * Returns all remaining tokens as PHP Code
	 * @return string
	 */
	public function get_string() {
		$t_code = '';
		foreach ($this->tokens as $t_token) {
			$t_code .= is_array( $t_token ) ? $t_token[1] : $t_token;
		}
		return $t_code;
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
				'Invalid token: got "' . $this->value() . '", expected "' . $p_value . '"'
			);
		}
		$this->pop();
	}

#	/**
#	 * Prints the tokens array.
#	 * For debugging purposes only
#	 */
#	public function debug_output()
#	{
#		if( count( $this->tokens ) == 0 ) {
#			echo "Empty !\n";
#		}
#		foreach ($this->tokens as $id => $token) {
#			echo "$id - ";
#			if( is_array( $token ) ) {
#				echo token_name($token[0]) . " " . var_export( $token[1], true ) . "\n";
#			} else {
#				echo $token . "\n";
#			}
#		}
#	}

}
