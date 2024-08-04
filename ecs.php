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

use PHP_CodeSniffer\Standards\Generic\Sniffs\WhiteSpace\DisallowSpaceIndentSniff;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use PhpCsFixer\Fixer\Whitespace\SpacesInsideParenthesesFixer;
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
		 * Whitespace: Tab as indention style
		 *
		 * Note: The config `->withSpacing(Option::INDENTATION_TAB)` does not
		 *       work in all circumstances. In combination with some other
		 *       whitespace fixers it results in a mix of tabs and whitespaces.
		 *
		 *       For example, the "ArrayIndentationFixer" behaves this way, this
		 *       could be because the symfony cs-fixer is fixed to whitespaces.
		 */
		DisallowSpaceIndentSniff::class,

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
		SingleQuoteFixer::class,

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

		/**
		 * Whitespace: Single blank line at eof
		 *
		 * A PHP file without end tag must always end with a single empty line feed.
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/single_blank_line_at_eof.html
		 */
		SingleBlankLineAtEofFixer::class,

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
		ConstantCaseFixer::class,

		/**
		 * Casing: Lowercase keywords
		 *
		 * PHP keywords MUST be in lower case.
		 *
		 * "FOREACH( $a AS $B )" > "foreach( $a as $B )"
		 *
		 * @see https://cs.symfony.com/doc/rules/casing/lowercase_keywords.html
		 */
		LowercaseKeywordsFixer::class,

		/**
		 * Alias: No mixed echo and print
		 *
		 * Either language construct print or echo should be used.
		 *
		 * "print()" > "echo()"
		 *
		 * @see https://cs.symfony.com/doc/rules/alias/no_mixed_echo_print.html
		 */
		NoMixedEchoPrintFixer::class,

		/**
		 * Semicolon: No empty statement
		 *
		 * Remove useless (semicolon) statements.
		 *
		 * "$a = 1;;"        > "$a = 1;"
		 * "<?php echo 1;2;" > "<?php echo 1;"
		 *
		 * @see https://cs.symfony.com/doc/rules/semicolon/no_empty_statement.html
		 */
		NoEmptyStatementFixer::class,

		/**
		 * Control structure: Continuation position: same line
		 *
		 * Configurable. Default is "same_line"
		 *
		 * <input>
		 * if( $baz == true ) {
		 *     echo "foo";
		 * }
		 * else {
		 *     echo "bar";
		 * }
		 * </input>
		 * <output>
		 * if( $baz == true ) {
		 *     echo "foo";
		 * } else {
		 *     echo "bar";
		 * }
		 * </output>
		 *
		 * @see https://cs.symfony.com/doc/rules/control_structure/control_structure_continuation_position.html
		 */
		ControlStructureContinuationPositionFixer::class,

		/**
		 * Control structure: No inline control structure
		 *
		 * The body of each control structure MUST be enclosed within braces.
		 *
		 * <input>
		 * if( $foo === $bar )
		 *     echo 'same'
		 * </input>
		 * <output>
		 *  if( $foo === $bar ) {
		 *      echo 'same'
		 *  }
		 * </output>
		 *
		 * @see https://cs.symfony.com/doc/rules/control_structure/control_structure_braces.html
		 */
		ControlStructureBracesFixer::class,

		/**
		 * Comment: Spacing of single line comment
		 *
		 * #comment > # comment
		 *
		 * @see https://cs.symfony.com/doc/rules/comment/single_line_comment_spacing.html
		 */
		SingleLineCommentSpacingFixer::class,

		/**
		 * Array: Array syntax
		 *
		 * Configurable. Default is "short" syntax.
		 *
		 * array(1,2) > [1,2];
		 *
		 * @see https://cs.symfony.com/doc/rules/array_notation/array_syntax.html
		 */
		ArraySyntaxFixer::class,

		/**
		 * Array: Trim array spaces
		 *
		 * Arrays should be formatted like function/method arguments,
		 * without leading or trailing single line space.
		 *
		 * [ ]     > []
		 * [ 1,2 ] > [1,2]
		 *
		 * @see https://cs.symfony.com/doc/rules/array_notation/trim_array_spaces.html
		 */
		TrimArraySpacesFixer::class,

		/**
		 * Array: Whitespace after comma
		 *
		 * In array declaration, there MUST be a whitespace after each comma.
		 *
		 * Configurable.
		 *
		 * [1,2,3] > [1, 2, 3]
		 *
		 * "ensure_single_space" => false
		 * ['one', 'two', 'three']
		 * [1,     2,     3]
		 *
		 * @see https://cs.symfony.com/doc/rules/array_notation/whitespace_after_comma_in_array.html
		 */
		WhitespaceAfterCommaInArrayFixer::class,

		/**
		 * Array: No whitespace before comma in array
		 *
		 * In array declaration, there MUST NOT be a whitespace before
		 * each comma.
		 *
		 * [1 , 2 , 8] > [1, 2, 3]
		 *
		 * @see https://cs.symfony.com/doc/rules/array_notation/no_whitespace_before_comma_in_array.html
		 */
		NoWhitespaceBeforeCommaInArrayFixer::class,

		/**
		 * Whitespace: Array indention
		 *
		 * Each element of an array must be indented exactly once.
		 *
		 * <input>
		 *     $foo = [
		 *      'bar' => [
		 *        'baz' => true,
		 *      ],
		 *    ];
		 * </input>
		 * <output>
		 *  $foo = [
		 *  	'bar' => [
		 *  		'baz' => true,
		 *		],
		 *	];
		 * </output>
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/array_indentation.html
		 */
		ArrayIndentationFixer::class,

		/**
		 * Function: No spaces after function name
		 *
		 * When making a method or function call, there MUST NOT be a space
		 * between the method or function name and the opening parenthesis.
		 *
		 * "foo ( test ( 3 ) );"  > "foo( test( 3 ) )"
		 *
		 * @see https://cs.symfony.com/doc/rules/function_notation/no_spaces_after_function_name.html
		 */
		NoSpacesAfterFunctionNameFixer::class,

		/**
		 * Control structure: include
		 *
		 * Include/Require and file path should be divided with a single space.
		 * File path should not be placed within parentheses.
		 *
		 * require ("sample1.php"); >  require "sample1.php";
		 * require_once "sample2.php"; > require_once "sample2.php";
		 *
		 * @see https://cs.symfony.com/doc/rules/control_structure/include.html
		 */
		IncludeFixer::class,

		/*
		 * Comment: Align multiline comment
		 *
		 * Each line of multi-line DocComments must have an asterisk
		 * [PSR-5] and must be aligned with the first one.
		 *
		 * @see https://cs.symfony.com/doc/rules/phpdoc/align_multiline_comment.html
		 */
		AlignMultilineCommentFixer::class,

		/*
		 * Comment: No empty comment
		 *
		 * @see https://cs.symfony.com/doc/rules/comment/no_empty_comment.html
		 */
		NoEmptyCommentFixer::class,
	] )

	/**
	 * Whitespace: Spaces inside parentheses
	 *
	 * "function foo($bar, $baz)" > "function foo( $bar, $baz )"
	 * "if($bar === $baz)"        > "if( $bar === $baz )"
	 * "foo( )"                   > "foo()"
	 *
	 * Sniffs:
	 *  - build/CodeSniffer/Mantis/Sniffs/ControlStructures/ControlSignatureSniff.php
	 *  - ControlStructureSpacingSniff
	 *  - FunctionDeclarationArgumentSpacingSniff
	 *
	 * @see https://cs.symfony.com/doc/rules/whitespace/spaces_inside_parentheses.html
	 */
	->withConfiguredRule( SpacesInsideParenthesesFixer::class, [
		'space' => 'single',
	] )

	/**
	 * Basic: Position of braces
	 *
	 * Opening braces on same line
	 *
	 * "Class Name {}"
	 * "function name() {}"
	 *
	 * @see https://cs.symfony.com/doc/rules/basic/braces_position.html
	 */
	->withConfiguredRule( BracesPositionFixer::class, [
		'classes_opening_brace' => 'same_line',
		'functions_opening_brace' => 'same_line',
	] )

	/**
	 * Operator: Concat spaces
	 *
	 * Spacing to apply around concatenation operator.
	 *
	 * @see https://cs.symfony.com/doc/rules/operator/concat_space.html
	 */
	->withConfiguredRule( ConcatSpaceFixer::class, [
		'spacing' => 'one',
	] )

	/**
	 * Cast: No space after cast
	 *
	 * "$bar = ( string )  $a;" > "$bar = (string)$a;"
	 *
	 * @see https://cs.symfony.com/doc/rules/cast_notation/cast_spaces.html
	 */
	->withConfiguredRule(
		CastSpacesFixer::class, [
			'space' => 'none'
		]
	)
;
