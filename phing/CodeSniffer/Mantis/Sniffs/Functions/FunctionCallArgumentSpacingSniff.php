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

/**
 * Checks that calls to methods and functions are spaced correctly.
 *
 */
class Mantis_Sniffs_Functions_FunctionCallArgumentSpacingSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);

    }


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Skip tokens that are the names of functions or classes
        // within their definitions. For example:
        // function myFunction...
        // "myFunction" is T_STRING but we should skip because it is not a
        // function or method *call*.
        $functionName    = $stackPtr;
        $ignoreTokens    = PHP_CodeSniffer_Tokens::$emptyTokens;
        $ignoreTokens[]  = T_BITWISE_AND;
        $functionKeyword = $phpcsFile->findPrevious($ignoreTokens, ($stackPtr - 1), null, true);
        if( $tokens[$functionKeyword]['code'] === T_FUNCTION || $tokens[$functionKeyword]['code'] === T_CLASS ) {
            return;
        }

        // If the next non-whitespace token after the function or method call
        // is not an opening parenthesis then it cant really be a *call*.
        $openBracket = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($functionName + 1), null, true);
        if( $tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS ) {
            return;
        }
		
		if( $tokens[$openBracket-1]['content'] !== $tokens[$functionName]['content'] ) {
                            $error = 'Whitespace found after function call to %s';
                            $data  = array($tokens[$functionName]['content']);
                            $phpcsFile->addError($error, $stackPtr, 'TooMuchSpace', $data);
		}
		
		if( $tokens[$openBracket+1]['code'] !== T_WHITESPACE && $tokens[$openBracket+1]['code']!== T_CLOSE_PARENTHESIS ) {
                            $error = 'Whitespace not found after { in function call to %s';
                            $data  = array($tokens[$functionName]['content']);
                            $phpcsFile->addError($error, $stackPtr, 'TooMuchSpace', $data);
		} else {
							$space = strlen($tokens[($openBracket+1)]['content']);
							if( $space > 1 ) {
								$error = 'Expected 1 space after { in function call; %s found';
								$data  = array($space);
								$fix   = $phpcsFile->addError($error, $openBracket, 'TooMuchSpaceAfterComma', $data);
							}
		}		

        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

		if( $tokens[$closeBracket-1]['code'] !== T_WHITESPACE && $tokens[$closeBracket-1]['code'] !== T_OPEN_PARENTHESIS ) {
                            $error = 'Whitespace not found before } in function call to %s';
                            $data  = array($tokens[$functionName]['content']);
                            $phpcsFile->addError($error, $stackPtr, 'TooMuchSpace', $data);
		} else {
							$space = strlen($tokens[($closeBracket-1)]['content']);
							if ($space > 1) {
								$error = 'Expected 1 space before } in function call; %s found';
								$data  = array($space);
								$fix   = $phpcsFile->addError($error, $closeBracket, 'TooMuchSpaceAfterComma', $data);
							}
		}	
		
		return;

        $nextSeparator = $openBracket;
        while( ( $nextSeparator = $phpcsFile->findNext( array( T_COMMA, T_VARIABLE, T_CLOSURE ), ( $nextSeparator + 1 ), $closeBracket ) ) !== false ) {
            if( $tokens[$nextSeparator]['code'] === T_CLOSURE ) {
                $nextSeparator = $tokens[$nextSeparator]['scope_closer'];
                continue;
            }

            // Make sure the comma or variable belongs directly to this function call,
            // and is not inside a nested function call or array.
            $brackets    = $tokens[$nextSeparator]['nested_parenthesis'];
            $lastBracket = array_pop($brackets);
            if( $lastBracket !== $closeBracket ) {
                continue;
            }

            if( $tokens[$nextSeparator]['code'] === T_COMMA) {
                if( $tokens[($nextSeparator - 1)]['code'] === T_WHITESPACE) {
                    if( isset(PHP_CodeSniffer_Tokens::$heredocTokens[$tokens[($nextSeparator - 2)]['code']]) === false) {
                        $error = 'Space found before comma in function call';
                        $fix   = $phpcsFile->addFixableError($error, $nextSeparator, 'SpaceBeforeComma');
                        if ($fix === true && $phpcsFile->fixer->enabled === true) {
                            $phpcsFile->fixer->replaceToken(($nextSeparator - 1), '');
                        }
                    }
                }

                if ($tokens[($nextSeparator + 1)]['code'] !== T_WHITESPACE) {
                    $error = 'No space found after comma in function call';
                    $fix   = $phpcsFile->addFixableError($error, $nextSeparator, 'NoSpaceAfterComma');
                    if ($fix === true && $phpcsFile->fixer->enabled === true) {
                        $phpcsFile->fixer->addContent($nextSeparator, ' ');
                    }
                } else {
                    // If there is a newline in the space, then they must be formatting
                    // each argument on a newline, which is valid, so ignore it.
                    if( strpos($tokens[($nextSeparator + 1)]['content'], $phpcsFile->eolChar) === false) {
                        $space = strlen($tokens[($nextSeparator + 1)]['content']);
                        if ($space > 1) {
                            $error = 'Expected 1 space after comma in function call; %s found';
                            $data  = array($space);
                            $fix   = $phpcsFile->addFixableError($error, $nextSeparator, 'TooMuchSpaceAfterComma', $data);
                            if ($fix === true && $phpcsFile->fixer->enabled === true) {
                                $phpcsFile->fixer->replaceToken(($nextSeparator + 1), ' ');
                            }
                        }
                    }
                }//end if
            } else {
                // Token is a variable.
                $nextToken = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextSeparator + 1), $closeBracket, true);
                if ($nextToken !== false) {
                    if ($tokens[$nextToken]['code'] === T_EQUAL) {
                        if (($tokens[($nextToken - 1)]['code']) !== T_WHITESPACE) {
                            $error = 'Expected 1 space before = sign of default value';
                            $fix   = $phpcsFile->addFixableError($error, $nextToken, 'NoSpaceBeforeEquals');
                            if ($fix === true && $phpcsFile->fixer->enabled === true) {
                                $phpcsFile->fixer->addContentBefore($nextToken, ' ');
                            }
                        }

                        if ($tokens[($nextToken + 1)]['code'] !== T_WHITESPACE) {
                            $error = 'Expected 1 space after = sign of default value';
                            $fix   = $phpcsFile->addFixableError($error, $nextToken, 'NoSpaceAfterEquals');
                            if ($fix === true && $phpcsFile->fixer->enabled === true) {
                                $phpcsFile->fixer->addContent($nextToken, ' ');
                            }
                        }
                    }
                }
            }
        }
    }
}
