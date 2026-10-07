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

use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * PHP tag: Echo tag syntax
		 *
		 * Configurable. Default is "format" = "short", "long_function" = "echo"
		 *
		 * 	<input>
		 * 	<?= $foo ?>
		 * 	</input>
		 *
		 * 	<output>
		 * 	<?php echo $foo; ?>
		 * 	</output>
		 *
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/echo_tag_syntax.html
		 */
		EchoTagSyntaxFixer::class,
	] )
;
