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

use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Basic: Empty body on the same line
		 *
		 * Empty body of class, interface, trait, enum or function must be abbreviated as {} and placed on the same line as the previous symbol, separated by a single space.
		 *
		 * 	<output>
		 * 	function foo(
		 * 	    int $x,
		 * 	    int $y,
		 * 	) {}
		 *
		 * 	function bar( $baz ) {}
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/basic/single_line_empty_body.html
		 */
		SingleLineEmptyBodyFixer::class
	] )
	/**
	 * Basic: Position of braces
	 *
	 * Opening braces on same line
	 *
	 * 	<output>
	 * 	Class Foo {
	 * 	    // …
	 * 	}
	 *
	 * 	function foo() {
	 * 	    // …
	 * 	}
	 * 	</output>
	 *
	 * @see https://cs.symfony.com/doc/rules/basic/braces_position.html
	 */
	->withConfiguredRule( BracesPositionFixer::class, [
		'classes_opening_brace' => 'same_line', // psr12: next-line
		'functions_opening_brace' => 'same_line', // psr12: next-line
		'control_structures_opening_brace' => 'same_line',
	] )
;
