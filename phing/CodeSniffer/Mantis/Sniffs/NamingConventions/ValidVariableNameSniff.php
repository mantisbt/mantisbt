<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.	If not, see <http://www.gnu.org/licenses/>.

/**
 * This rule is based upon Greg Sherwood's code from php codesniffer available
 * at http://pear.php.net/package/PHP_CodeSniffer. The original rule has been
 * modified to meet MantisBT coding guidelines
 *
 * @package MantisBuild
 * @copyright Copyright 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if( class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false ) {
	throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found');
}

/**
 * Checks the naming of variables and member variables.
 */
class Mantis_Sniffs_NamingConventions_ValidVariableNameSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff {
	/**
	 * Tokens to ignore so that we can find a DOUBLE_COLON.
	 *
	 * @var array
	 */
	private $_ignore = array(
						T_WHITESPACE,
						T_COMMENT,
					   );

	public $staticVariables = array();

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer              $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	protected function processVariable( PHP_CodeSniffer_File $phpcsFile, $stackPtr ) {
		$tokens  = $phpcsFile->getTokens();
		$varName = ltrim($tokens[$stackPtr]['content'], '$');

		$phpReservedVars = array(
							'_SERVER',
							'_GET',
							'_POST',
							'_REQUEST',
							'_SESSION',
							'_ENV',
							'_COOKIE',
							'_FILES',
							'GLOBALS',
							'http_response_header',
							'HTTP_RAW_POST_DATA',
							'php_errormsg',
							'this',
							'ADODB_FETCH_MODE',
							'ADODB_vers'
						   );

		# If it's a PHP reserved variable, then its ok.
		if( in_array($varName, $phpReservedVars) === true ) {
			return;
		}

		if( substr( $varName, 1, 1 ) !== '_' ) {
			if( strlen($varName) == '1' ) {
				return;
			}

			$error = 'Variable "%s" %s is not prefixed';
			$data  = array($varName, substr( $varName, 1, 1 ) );
			$phpcsFile->addError($error, $stackPtr, 'BadVariableName', $data);
		} else {
			# variable name has a _

			# get previous operator
			$objOperator = $phpcsFile->findPrevious(array(T_WHITESPACE), ($stackPtr - 1), null, true);

			if( $tokens[$objOperator]['code'] === T_STATIC ) {
				if( substr( $varName, 0, 1 ) !== 's' ) {
					$error = 'Static Variable "%s" should be prefixed $s_';
					$data  = array($varName, substr( $varName, 1, 1 ) );
					$phpcsFile->addError($error, $stackPtr, 'BadVariableName', $data);
				} else {
					$this->staticVariables[] = $varName;
				}
			} else if( $tokens[$objOperator]['code'] === T_GLOBAL ) {
				if( substr( $varName, 0, 1 ) !== 'g' ) {
					$error = 'Global Variable "%s" should be prefixed $g_';
					$data  = array($varName, substr( $varName, 1, 1 ) );
					$phpcsFile->addError($error, $stackPtr, 'BadVariableName', $data);
				}
			}

			switch( substr( $varName, 0, 1 ) ) {
				case 's':
					if( !in_array( $varName, $this->staticVariables ) ) {
						$error = 'Static Variable "%s" not found';
						$data  = array($varName, substr( $varName, 1, 1 ) );
						$phpcsFile->addError($error, $stackPtr, 'BadVariableName', $data);
					}
			}
		}
		return;

		$objOperator = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true);
		if( $tokens[$objOperator]['code'] === T_OBJECT_OPERATOR ) {
			# Check to see if we are using a variable from an object.
			$var = $phpcsFile->findNext(array(T_WHITESPACE), ($objOperator + 1), null, true);
			if( $tokens[$var]['code'] === T_STRING ) {
				$bracket = $objOperator = $phpcsFile->findNext(array(T_WHITESPACE), ($var + 1), null, true);
				if( $tokens[$bracket]['code'] !== T_OPEN_PARENTHESIS ) {
					$objVarName = $tokens[$var]['content'];

					# There is no way for us to know if the var is public or
					# private, so we have to ignore a leading underscore if there is
					# one and just check the main part of the variable name.
					$originalVarName = $objVarName;
					if( substr($objVarName, 0, 1) === '_' ) {
						$objVarName = substr($objVarName, 1);
					}

					if( PHP_CodeSniffer::isCamelCaps($objVarName, false, true, false) === false ) {
						$error = 'Variable "%s" is not in valid camel caps format';
						$data  = array($originalVarName);
						$phpcsFile->addError($error, $var, 'NotCamelCaps', $data);
					}
				}
			}
		}

		# There is no way for us to know if the variable is public or private,
		# so we have to ignore a leading underscore if there is one and just
		# check the main part of the variable name.
		$originalVarName = $varName;
		if( substr( $varName, 0, 1 ) === '_' ) {
			$objOperator = $phpcsFile->findPrevious(array(T_WHITESPACE), ($stackPtr - 1), null, true);
			if( $tokens[$objOperator]['code'] === T_DOUBLE_COLON ) {
				# The variable lives within a class, and is referenced like
				# this: MyClass::$_variable, so we don't know its scope.
				$inClass = true;
			} else {
				$inClass = $phpcsFile->hasCondition($stackPtr, array(T_CLASS, T_INTERFACE));
			}

			if( $inClass === true ) {
				$varName = substr($varName, 1);
			}
		}

		if( PHP_CodeSniffer::isCamelCaps($varName, false, true, false) === false ) {
			$error = 'Variable "%s" is not in valid camel caps format';
			$data  = array($originalVarName);
			$phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $data);
		}

	}


	/**
	 * Processes class member variables.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer              $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	protected function processMemberVar( PHP_CodeSniffer_File $phpcsFile, $stackPtr ) {
		return;
		$tokens = $phpcsFile->getTokens();

		$varName     = ltrim($tokens[$stackPtr]['content'], '$');
		$memberProps = $phpcsFile->getMemberProperties($stackPtr);
		if( empty($memberProps) === true ) {
			# Couldn't get any info about this variable, which
			# generally means it is invalid or possibly has a parse
			# error. Any errors will be reported by the core, so
			# we can ignore it.
			return;
		}

		$public    = ($memberProps['scope'] !== 'private');
		$errorData = array($varName);

		if ( $public === true ) {
			if ( substr($varName, 0, 1) === '_' ) {
				$error = '%s member variable "%s" must not contain a leading underscore';
				$data  = array(
						  ucfirst($memberProps['scope']),
						  $errorData[0],
						 );
				$phpcsFile->addError($error, $stackPtr, 'PublicHasUnderscore', $data);
				return;
			}
		} else {
			if( substr($varName, 0, 1) !== '_' ) {
				$error = 'Private member variable "%s" must contain a leading underscore';
				$phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $errorData);
				return;
			}
		}

		if ( PHP_CodeSniffer::isCamelCaps($varName, false, $public, false) === false ) {
			$error = 'Variable "%s" is not in valid camel caps format';
			$phpcsFile->addError($error, $stackPtr, 'MemberNotCamelCaps', $errorData);
		}

	}


	/**
	 * Processes the variable found within a double quoted string.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer              $stackPtr  The position of the double quoted
	 *                                        string.
	 *
	 * @return void
	 */
	protected function processVariableInString( PHP_CodeSniffer_File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$phpReservedVars = array(
							'_SERVER',
							'_GET',
							'_POST',
							'_REQUEST',
							'_SESSION',
							'_ENV',
							'_COOKIE',
							'_FILES',
							'GLOBALS',
							'http_response_header',
							'HTTP_RAW_POST_DATA',
							'php_errormsg',
							'this'
						   );

		if ( preg_match_all('|[^\\\]\${?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|', $tokens[$stackPtr]['content'], $matches) !== 0 ) {
			foreach ( $matches[1] as $varName ) {
				# If it's a php reserved var, then its ok.
				if( in_array($varName, $phpReservedVars) === true ) {
					continue;
				}

				if( substr( $varName, 1, 1 ) !== '_' ) {
					if( strlen($varName) == '1' ) {
						continue;
					}

					$error = 'Variable "%s" %s is not prefixed';
					$data  = array($varName, substr( $varName, 1, 1 ) );
					$phpcsFile->addError($error, $stackPtr, 'BadVariableName', $data);
				}
				continue;
				# There is no way for us to know if the var is public or private,
				# so we have to ignore a leading underscore if there is one and just
				# check the main part of the variable name.
				$originalVarName = $varName;
				if( substr($varName, 0, 1) === '_' ) {
					if( $phpcsFile->hasCondition($stackPtr, array(T_CLASS, T_INTERFACE)) === true ) {
						$varName = substr($varName, 1);
					}
				}

				if( PHP_CodeSniffer::isCamelCaps($varName, false, true, false) === false ) {
					$varName = $matches[0];
					$error   = 'Variable "%s" is not in valid camel caps format';
					$data    = array($originalVarName);
					$phpcsFile->addError($error, $stackPtr, 'StringNotCamelCaps', $data);

				}
			}
		}
	}
}
