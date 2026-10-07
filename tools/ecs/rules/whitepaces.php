<?php

declare ( strict_types=1 );

/*
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace ECSPrefix202609;

use PHP_CodeSniffer\Standards\Generic\Sniffs\WhiteSpace\DisallowSpaceIndentSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\SuperfluousWhitespaceSniff;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SpacesInsideParenthesesFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Whitespace: Tab as indention style
		 *
		 * The config `->withSpacing(Option::INDENTATION_TAB)` does not
		 * work in all circumstances. In combination with some other
		 * whitespace fixers it results in a mix of tabs and whitespaces.
		 *
		 * For example, the `ArrayIndentationFixer` behaves this way, this
		 * could be because the symfony cs-fixer is set to use whitespaces
		 * instead of tabs.
		 */
		DisallowSpaceIndentSniff::class,

		/**
		 * Whitespace: No trailing whitespaces
		 *
		 * Remove trailing whitespace at the end of non-blank lines.
		 *
		 * 	<input>
		 * 	$foo = 'bar'···
		 * 	</input>
		 *
		 * 	<output>
		 * 	$foo = 'bar'
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/no_trailing_whitespace.html
		 */
		NoTrailingWhitespaceFixer::class,

		/**
		 * Whitespaces: No whitespace in blank lines
		 *
		 * Remove trailing whitespace at the end of blank lines.
		 *
		 * 	<input>
		 * 	···
		 * 	$a = 1;
		 * 	</input>
		 *
		 * 	<output>
		 *
		 * 	$a = 1;
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/no_whitespace_in_blank_line.html
		 */
		NoWhitespaceInBlankLineFixer::class,

		/**
		 * Comment: No trailing whitespaces
		 *
		 * There must be no trailing whitespace at the end of lines in comments and PHPDocs.
		 *
		 * 	<input>
		 * 	// foo = 'bar'···
		 * 	</input>
		 *
		 * 	<output>
		 * 	// foo = 'bar'
		 * 	</output>
		 *
		 *
		 * 	<?php $foo = 'bar'; # my comment ?>
		 * 	                                ^ This pace will be removed
		 *
		 * @see https://cs.symfony.com/doc/rules/comment/no_trailing_whitespace_in_comment.html
		 *
		 * SuperfluousWhitespaceSniff works
		 */
		SuperfluousWhitespaceSniff::class,

		/**
		 * Whitespaces: Statement indention
		 *
		 * @todo: Fails on HTML/PHP files
		 *
		 * 	<input>
		 * 	if ($baz == true) {
		 * 	echo "foo";
		 * 	} else {
		 * 	echo "bar";
		 * 	}
		 * 	</input>
		 *
		 * 	<output>
		 * 	if ($baz == true) {
		 * 	    echo "foo";
		 * 	} else {
		 * 	    echo "bar";
		 * 	}
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/statement_indentation.html
		 */
		// StatementIndentationFixer::class
	] )

	/**
	 * Whitespaces: No extra whitespaces
	 *
	 * Removes extra blank lines and/or blank lines following configuration.
	 *
	 * `tokens`:
	 *  Allowed values: a subset of ['attribute', 'break', 'case', 'comma',
	 * 'continue', 'curly_brace_block', 'default', 'extra',
	 * 'parenthesis_brace_block', 'return', 'square_brace_block',
	 * 'switch', 'throw', 'use', 'use_trait']
	 *
	 * Default value: ['extra']
	 *
	 * @see https://cs.symfony.com/doc/rules/whitespace/no_extra_blank_lines.html
	 */
	->withConfiguredRule( NoExtraBlankLinesFixer::class, [
		'tokens' => [
			'extra',
			'parenthesis_brace_block',
			// 'curly_brace_block',
		],
	] )

	/**
	 * Whitespace: Spaces inside parentheses
	 *
	 * 	function foo($bar, $baz) > function foo( $bar, $baz )
	 * 	if($bar === $baz)        > if( $bar === $baz )
	 * 	foo( )                   > foo()
	 *
	 * @see https://cs.symfony.com/doc/rules/whitespace/spaces_inside_parentheses.html
	 */
	->withConfiguredRule( SpacesInsideParenthesesFixer::class, [
		'space' => 'single',
	] )

	/**
	 * Operator: Concat spaces
	 *
	 * Spacing to apply around concatenation operator.
	 *
	 * 	echo 'hello '.$world.'!'; > echo 'hello ' . $world . '!';
	 *
	 * @see https://cs.symfony.com/doc/rules/operator/concat_space.html
	 */
	->withConfiguredRule( ConcatSpaceFixer::class, [
		'spacing' => 'one',
	] )
;
