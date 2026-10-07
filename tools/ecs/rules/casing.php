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

use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Casing: Constant case: lower
		 *
		 * The PHP constants true, false, and null MUST be written
		 * using the correct casing.
		 *
		 * Configurable. Default is "lower"
		 *
		 * "$a = FALse" > "a = false"
		 *
		 * @see https://cs.symfony.com/doc/rules/casing/constant_case.html
		 */
		ConstantCaseFixer::class,

		/**
		 * Casing: Lowercase keywords
		 *
		 * PHP keywords MUST be in lower case.
		 *
		 * "FOREACH( $a AS $B )" > "foreach( $a as $B )"
		 *
		 * @see https://cs.symfony.com/doc/rules/casing/lowercase_keywords.html
		 */
		LowercaseKeywordsFixer::class,
	] )
;
