<?php

declare ( strict_types=1 );

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
