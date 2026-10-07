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

use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Control structure: include
		 *
		 * Include/Require and file path should be divided with a single space.
		 * File path should not be placed within parentheses.
		 *
		 * 	<input>
		 * 	require ('foo.php');
		 * 	require_once ('bar.php');
		 * 	</input>
		 *
		 * <output>
		 * 	require 'foo.php';
		 * 	require_once 'bar.php';
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/control_structure/include.html
		 */
		IncludeFixer::class,

		/**
		 * Control structure: statements
		 *
		 * Removes unneeded parentheses around control statements.
		 *
		 * Configurable: `statements`
		 *
		 * Allowed values: a subset of ['break', 'clone', 'continue',
		 * 'echo_print', 'negative_instanceof', 'others', 'return',
		 * 'switch_case', 'yield', 'yield_from']
		 *
		 * Default value: ['break', 'clone', 'continue', 'echo_print',
		 * 'return', 'switch_case', 'yield']
		 *
		 * 	<input>
		 * 	echo('foo');
		 * 	return('bar');
		 * 	</input>
		 *
		 * 	<output>
		 * 	echo 'foo';
		 * 	return 'bar';
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/control_structure/no_unneeded_control_parentheses.html
		 */
		NoUnneededControlParenthesesFixer::class,
	] )
;
