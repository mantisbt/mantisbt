<?php
# MantisBT - A PHP based bugtracking system
#
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
# along with MantisBT.  If not, see <https://www.gnu.org/licenses/>.

declare( strict_types = 1 );

use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

/**
 * MantisBT coding standards
 *
 * @see https://mantisbt.org/wiki/doku.php/mantisbt:coding_guidelines
 */

return ECSConfig::configure()
    ->withPaths( [
        __DIR__ . '/',
    ] )
	->withSkip( [
		__DIR__ . '/build',
		__DIR__ . '/config',
		__DIR__ . '/library',
	] )
	->withSpacing(
		Option::INDENTATION_TAB,
		"\n",
	)
    ->withRules( [
		/**
		 * Basic: Encoding
		 *
		 * PHP code MUST use only UTF-8 without BOM (remove BOM).
		 *
		 * @see https://cs.symfony.com/doc/rules/basic/encoding.html
		 */
		EncodingFixer::class,

		/**
		 * Whitespace: Line encoding
		 *
		 * All PHP files must use same line ending. Default is "\n"
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/line_ending.html
		 */
		LineEndingFixer::class,

		/**
		 * PHP tag: Full opening tag
		 *
		 * "<?" > "<?php"
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/full_opening_tag.html
		 */
		FullOpeningTagFixer::class,

		/**
		 * PHP tag: Linebreak after opening tag
		 *
		 * Ensure there is no code on the same line as the PHP open tag.
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/linebreak_after_opening_tag.html
		 */
		LinebreakAfterOpeningTagFixer::class,

		/**
		 * PHP tag: Echo tag syntax
		 *
		 * Configurable. Default is "format" = "short", "long_function" = "echo"
		 *
		 * "<?= …" > "<?php echo …"
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/echo_tag_syntax.html
		 */
		EchoTagSyntaxFixer::class,

		/**
		 * PHP tag: No closing tag
		 *
		 * The closing ?> tag MUST be omitted from files containing only PHP.
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/no_closing_tag.html
		 */
		NoClosingTagFixer::class,

		/**
		 * Whitespace: No trailing whitespaces
		 *
		 * Remove trailing whitespace at the end of non-blank lines.
		 *
		 * "$foo = 'bar'···" > "$foo = 'bar'"
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/no_trailing_whitespace.html
		 */
		NoTrailingWhitespaceFixer::class,

		/**
		 * Whitespaces: No whitespace in blank lines
		 *
		 * Remove trailing whitespace at the end of blank lines.
		 *
		 * <input>
		 * ···
		 * $a = 1;"
		 * </input>
		 * <output>
		 *
		 *  $a = 1;"
		 * </output>
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/no_whitespace_in_blank_line.html
		 */
		NoWhitespaceInBlankLineFixer::class,
	] )
;
