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
 * This rule is based upon Greg Sherwood's code from php codesniffer available
 * at http://pear.php.net/package/PHP_CodeSniffer. The original rule has been
 * modified to meet MantisBT coding guidelines
 *
 * @package MantisBuild
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @copyright Copyright 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 * @link http://www.mantisbt.org
 */

/**
 * Parses and verifies the class doc comment.
 */
class Mantis_Sniffs_Commenting_ClassCommentSniff implements PHP_CodeSniffer_Sniff {
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_CLASS);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer              $stackPtr  The position of the current token
	 *                                        in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( PHP_CodeSniffer_File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;

		$commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		if( $tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
			&& $tokens[$commentEnd]['code'] !== T_COMMENT
		) {
			$phpcsFile->addError('Missing class doc comment', $stackPtr, 'Missing');
			return;
		}

		# Try and determine if this is a file comment instead of a class comment.
		# We assume that if this is the first comment after the open PHP tag, then
		# it is most likely a file comment instead of a class comment.
		if( $tokens[$commentEnd]['code'] === T_DOC_COMMENT_CLOSE_TAG ) {
			$start = ($tokens[$commentEnd]['comment_opener'] - 1);
		} else {
			$start = $phpcsFile->findPrevious(T_COMMENT, ($commentEnd - 1), null, true);
		}

		$prev = $phpcsFile->findPrevious(T_WHITESPACE, $start, null, true);
		if( $tokens[$prev]['code'] === T_OPEN_TAG ) {
			$prevOpen = $phpcsFile->findPrevious(T_OPEN_TAG, ($prev - 1));
			if( $prevOpen === false ) {
				# This is a comment directly after the first open tag,
				# so probably a file comment.
				$phpcsFile->addError('Missing class doc comment', $stackPtr, 'Missing');
				return;
			}
		}

		if( $tokens[$commentEnd]['code'] === T_COMMENT ) {
			$phpcsFile->addError('You must use "/**" style comments for a class comment', $stackPtr, 'WrongStyle');
			return;
		}

		if( $tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1) ) {
			$error = 'There must be no blank lines after the class comment';
			$phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
		}

		$commentStart = $tokens[$commentEnd]['comment_opener'];
		if( $tokens[$prev]['line'] !== ($tokens[$commentStart]['line'] - 2) ) {
			$error = 'There must be exactly one blank line before the class comment';
			$phpcsFile->addError($error, $commentStart, 'SpacingBefore');
		}
	}
}
