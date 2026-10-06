<?php

declare ( strict_types=1 );

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
