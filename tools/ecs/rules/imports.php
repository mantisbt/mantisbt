<?php

declare ( strict_types=1 );

namespace ECSPrefix202609;

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		OrderedImportsFixer::class,
		/**
		 * Imports: Unused use statements must be removed.
		 *
		 * @see https://cs.symfony.com/doc/rules/import/no_unused_imports.html
		 */
		NoUnusedImportsFixer::class,

		/**
		 * Imports: One statement per line
		 *
		 * There MUST be one use keyword per declaration.
		 *
		 * 	<input>
		 * 	use Foo, Sample, Sample\Sample as Sample2;
		 * 	use Space\Models\ {
		 * 	    TestModelA,
		 * 	    TestModelB,
		 * 	    TestModel,
		 * 	};
		 * 	</input>
		 *
		 * 	<output>
		 * 	use Foo;
		 * 	use Sample;
		 * 	use Sample\Sample as Sample2;
		 * 	use Space\Models\TestModelA;
		 * 	use Space\Models\TestModelB;
		 * 	use Space\Models\TestModel;
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/import/single_import_per_statement.html
		 */
		SingleImportPerStatementFixer::class,
	] )

	/**
	 * Imports: Order imports
	 *
	 * @see https://cs.symfony.com/doc/rules/import/ordered_imports.html
	 */
	->withConfiguredRule( OrderedImportsFixer::class, [
		'imports_order' => ['class', 'function', 'const'],
	] )
;
