<?php

declare( strict_types=1 );

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

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return ECSConfig::configure()
	->withPaths( [
		__DIR__ . '/rules',
		__DIR__ . '/config.php',
		'admin',
		'api',
		'core',
		'plugins/MantisCoreFormatting',
		'plugins/Gravatar',
		'plugins/XmlImportExport',
		'tests',
	] )
	// include *.php files in the root directory
	->withRootFiles()
	->withSpacing( Option::INDENTATION_TAB, "\n" )
	->withSets( [
		__DIR__ . '/rules/basic.php',
		__DIR__ . '/rules/whitepaces.php',
		__DIR__ . '/rules/functions.php',
		__DIR__ . '/rules/array.php',
		__DIR__ . '/rules/quotes.php',
		__DIR__ . '/rules/casing.php',
		__DIR__ . '/rules/cast.php',
		__DIR__ . '/rules/comments.php',
		__DIR__ . '/rules/comments-header.php',
		__DIR__ . '/rules/alias.php',
		__DIR__ . '/rules/braces.php',
		__DIR__ . '/rules/control-structures-parentheses.php',
		__DIR__ . '/rules/control-structures.php',
		__DIR__ . '/rules/echo.php',
		__DIR__ . '/rules/semicolon.php',
		__DIR__ . '/rules/imports.php',
	] )
	// @todo go with psr12 and adjust the mantis specials?
	//	->withPreparedSets(
	//		psr12: true
	//	)
;
