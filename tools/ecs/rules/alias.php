<?php

declare ( strict_types=1 );

namespace ECSPrefix202609;

use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Alias: No mixed echo and print
		 *
		 * Either language construct print or echo should be used.
		 *
		 * 	<input>
		 * 	print 'foo';
		 * 	print('foo');
		 * 	</input>
		 *
		 * 	</output>
		 * 	echo 'foo';
		 * 	echo('foo');
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/alias/no_mixed_echo_print.html
		 */
		NoMixedEchoPrintFixer::class,
	] )
;
