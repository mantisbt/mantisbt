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
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 * @copyright Copyright 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Checks that # style comments are used
 */
class Mantis_Sniffs_Commenting_InlineCommentSniff implements PHP_CodeSniffer_Sniff
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_COMMENT);

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

		if( $tokens[$stackPtr]['content']{0} === '#' ) {
			$phpcsFile->recordMetric($stackPtr, 'Inline comment style', '# ...');
		} else if( $tokens[$stackPtr]['content']{0} === '/'
			&& $tokens[$stackPtr]['content']{1} === '/'
		) {
			$error  = 'Use # style comment instead.';
			$phpcsFile->addError($error, $stackPtr, 'WrongStyle');
			$phpcsFile->recordMetric($stackPtr, 'Inline comment style', '// ...');
		} else if( $tokens[$stackPtr]['content']{0} === '/'
			&& $tokens[$stackPtr]['content']{1} === '*'
		) {
			$error  = 'Use # style comment instead.';
			$phpcsFile->addError($error, $stackPtr, 'WrongStyle');
			$phpcsFile->recordMetric($stackPtr, 'Inline comment style', '/* ... */');
		}
	}
}
