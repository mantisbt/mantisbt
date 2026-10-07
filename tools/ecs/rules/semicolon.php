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

use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Semicolon: No empty statement
		 *
		 * Remove useless (semicolon) statements.
		 *
		 * "$a = 1;;"        > "$a = 1;"
		 * "<?php echo 1;2;" > "<?php echo 1;"
		 *
		 * @see https://cs.symfony.com/doc/rules/semicolon/no_empty_statement.html
		 */
		NoEmptyStatementFixer::class,
	] )
;
