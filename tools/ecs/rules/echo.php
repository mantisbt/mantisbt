<?php

declare ( strict_types=1 );

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
