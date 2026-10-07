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

use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * String: Single quotes
		 *
		 * Convert double quotes to single quotes for simple strings.
		 *
		 * Configurable. Default is keep double-quoted strings if they contain a
		 * single-quoted string.
		 *
		 * $a = "sample"                       > $a = 'sample'
		 * $b = "sample with 'single-quotes'"  > $b = "sample with 'single-quotes'"
		 *
		 * @see https://cs.symfony.com/doc/rules/string_notation/single_quote.html
		 */
		SingleQuoteFixer::class,
	] )
;
