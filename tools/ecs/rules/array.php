<?php

declare ( strict_types=1 );

namespace ECSPrefix202609;

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Array: Array syntax
		 *
		 * Configurable. Default is "short" syntax.
		 *
		 * 	array(1,2) > [1,2];
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
		 * 	[ ]     > []
		 * 	[ 1,2 ] > [1,2]
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
		 * 	[1,2,3] > [1, 2, 3]
		 *
		 * "ensure_single_space" => false
		 *
		 * 	['one', 'two', 'three']
		 * 	[1,     2,     3]
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
		 * 	[1 , 2 , 8] > [1, 2, 3]
		 *
		 * @see https://cs.symfony.com/doc/rules/array_notation/no_whitespace_before_comma_in_array.html
		 */
		NoWhitespaceBeforeCommaInArrayFixer::class,

		/**
		 * Whitespace: Array indention
		 *
		 * Each element of an array must be indented exactly once.
		 *
		 * 	<input>
		 * 	$foo = [
		 * 	 'bar' => [
		 * 	       'baz' => true,
		 * 	],
		 * 	];
		 * 	</input>
		 *
		 * 	<output>
		 * 	$foo = [
		 * 	    'bar' => [
		 * 	        'baz' => true,
		 * 	    ],
		 * 	];
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/array_indentation.html
		 */
		ArrayIndentationFixer::class,
	] )
;
