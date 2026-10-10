<?php

declare ( strict_types=1 );

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
