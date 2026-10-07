<?php

declare( strict_types=1 );

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
		__DIR__ . '/rules/array.php',
		__DIR__ . '/rules/quotes.php',
		__DIR__ . '/rules/casing.php',
		__DIR__ . '/rules/cast.php',
		//		__DIR__ . '/rules/comments.php',
		__DIR__ . '/rules/alias.php',
		__DIR__ . '/rules/braces.php',
		__DIR__ . '/rules/control-structures-parentheses.php',
		__DIR__ . '/rules/control-structures.php',
		__DIR__ . '/rules/echo.php',
		__DIR__ . '/rules/semicolon.php',
		//		__DIR__ . '/rules/imports.php',
	] )
	// @todo go with psr12 and adjust the mantis specials?
	//	->withPreparedSets(
	//		psr12: true
	//	)
;
