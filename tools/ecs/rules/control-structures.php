<?php

declare ( strict_types=1 );

namespace ECSPrefix202609;

use PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Control structure: Continuation position: same line
		 *
		 * Configurable. Default is "same_line"
		 *
		 * 	<input>
		 * 	if( $baz == true ) {
		 * 	    echo 'foo';
		 * 	}
		 * 	else {
		 * 	    echo 'bar';
		 * 	}
		 * 	</input>
		 *
		 * 	<output>
		 * 	if( $baz == true ) {
		 * 	    echo 'foo';
		 * 	} else {
		 * 	    echo 'bar';
		 * 	}
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/control_structure/control_structure_continuation_position.html
		 */
		ControlStructureContinuationPositionFixer::class,

		/**
		 * Control structure: No inline control structure
		 *
		 * The body of each control structure MUST be enclosed within braces.
		 *
		 * 	<input>
		 * 	if( $foo === $bar )
		 * 	    echo 'same'
		 * 	</input>
		 *
		 * 	<output>
		 * 	if( $foo === $bar ) {
		 * 	    echo 'same'
		 * 	}
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/control_structure/control_structure_braces.html
		 */
		ControlStructureBracesFixer::class,
	] )
;
