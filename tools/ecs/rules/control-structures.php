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
