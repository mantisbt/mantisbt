<?php

declare ( strict_types=1 );

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
