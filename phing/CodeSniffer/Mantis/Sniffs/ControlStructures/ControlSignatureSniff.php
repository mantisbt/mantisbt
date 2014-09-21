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
 * @author	  Greg Sherwood <gsherwood@squiz.net>
 * @author	  Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license	  https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link	  http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractPatternSniff', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractPatternSniff not found');
}

/**
 * Verifies that control statements conform to their coding standards.
 */
class Mantis_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff {
	/**
	 * If true, comments will be ignored if they are found in the code.
	 *
	 * @var boolean
	 */
	public $ignoreComments = true;

	/**
	 * Returns the patterns that this test wishes to verify.
	 *
	 * @return array(string)
	 */
	protected function getPatterns() {
		return array(
				'do {EOL...} while(...);EOL',
				'while(...) {EOL',
				'switch(...) {EOL',
				'for(...) {EOL',
				'if(...) {EOL',
				'foreach( ... ) {EOL',
				'} else if(...) {EOL',
				'} elseif(...) {EOL',
				'} else {EOL',
				'do {EOL',
			   );
	}
}
