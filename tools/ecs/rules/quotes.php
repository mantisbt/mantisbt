<?php

declare ( strict_types=1 );

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
