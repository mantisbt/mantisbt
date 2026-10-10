<?php

declare ( strict_types=1 );

namespace ECSPrefix202609;

use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	/**
	 * Cast: No space after cast
	 *
	 * 	$bar = ( string )  $a; > $bar = (string)$a;
	 *
	 * @see https://cs.symfony.com/doc/rules/cast_notation/cast_spaces.html
	 */
	->withConfiguredRule(
		CastSpacesFixer::class, [
			'space' => 'none'
		]
	)
;
