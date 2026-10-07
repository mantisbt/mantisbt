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
