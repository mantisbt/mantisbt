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

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/',
    ])
	->withSkip([
		__DIR__ . '/build',
		__DIR__ . '/config',
		__DIR__ . '/library',
	])
    ->withRules([
		/**
		 * Basic: Encoding
		 *
		 * PHP code MUST use only UTF-8 without BOM (remove BOM).
		 *
		 * @see https://cs.symfony.com/doc/rules/basic/encoding.html
		 */
		\PhpCsFixer\Fixer\Basic\EncodingFixer::class,

		/**
		 * Whitespace: Line encoding
		 *
		 * All PHP files must use same line ending. Default is "\n"
		 *
		 * Sniff: LineEndingsSniff with ['eolChar' => "\n"]
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/line_ending.html
		 */
		\PhpCsFixer\Fixer\Whitespace\LineEndingFixer::class,

		/**
		 * Whitespace: No trailing whitespaces
		 *
		 * Remove trailing whitespace at the end of non-blank lines.
		 *
		 * "$foo = 'bar'···" > "$foo = 'bar'"
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/no_trailing_whitespace.html
		 */
		\PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer::class,

		/**
		 * Whitespace: Single blank line at eof
		 *
		 * A PHP file without end tag must always end with a single empty line feed.
		 *
		 * Sniff: PSR2.Files.EndFileNewline
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/single_blank_line_at_eof.html
		 */
		\PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer::class,

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
		\PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer::class,

		/**
		 * String: Single quotes
		 *
		 * Convert double quotes to single quotes for simple strings.
		 *
		 * Configurable. Default is keep double-quoted strings if they contain a
		 * single-quoted string.
		 *
		 * $a = "sample"                       > $a = 'sample'
		 * $b = "sample with 'single-quotes'"  > $b = "sample with 'single-quotes'"
		 *
		 * @see https://cs.symfony.com/doc/rules/string_notation/single_quote.html
		 */
		\PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer::class,

		/**
		 * PHP tag: Full opening tag
		 *
		 * "<?" > "<?php"
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/full_opening_tag.html
		 */
		\PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer::class,

		/**
		 * PHP tag: Echo tag syntax
		 *
		 * Configurable. Default is "format" = "short", "long_function" = "echo"
		 *
		 * "<?= …" > "<?php echo …"
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/echo_tag_syntax.html
		 */
		\PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer::class,

		/**
		 * PHP tag: No closing tag
		 *
		 * The closing ?> tag MUST be omitted from files containing only PHP.
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/no_closing_tag.html
		 */
		\PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer::class,

		/**
		 * Casing: Constant case: lower
		 *
		 * The PHP constants true, false, and null MUST be written
		 * using the correct casing.
		 *
		 * Configurable. Default is "lower"
		 *
		 * "$a = FALse" > "a = false"
		 *
		 * @see https://cs.symfony.com/doc/rules/casing/constant_case.html
		 */
		\PhpCsFixer\Fixer\Casing\ConstantCaseFixer::class,

		/**
		 * Casing: Lowercase keywords
		 *
		 * PHP keywords MUST be in lower case.
		 *
		 * "FOREACH( $a AS $B )" > "foreach( $a as $B )"
		 *
		 * Sniff: Generic.PHP.LowerCaseKeyword
		 *
		 * @see https://cs.symfony.com/doc/rules/casing/lowercase_keywords.html
		 */
		\PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer::class,

		/**
		 * Import: no_unused_imports
		 * Unused use statements must be removed.
		 *
		 * @see https://cs.symfony.com/doc/rules/import/no_unused_imports.html
		 */
		\PhpCsFixer\Fixer\Import\NoUnusedImportsFixer::class,
	])
;
