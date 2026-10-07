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

use PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Function: No spaces after function name
		 *
		 * When making a method or function call, there MUST NOT be a space
		 * between the method or function name and the opening parenthesis.
		 *
		 * 	<input>
		 * 	foo ( test ( 3 ) );
		 * 	</input>
		 *
		 * 	<output>
		 * 	foo( test( 3 ) );
		 * 	<output>
		 *
		 * @see https://cs.symfony.com/doc/rules/function_notation/no_spaces_after_function_name.html
		 * @note next SingleSpaceAroundConstructFixer
		 */
		NoSpacesAfterFunctionNameFixer::class,
	] )
;
