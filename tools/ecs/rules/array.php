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
