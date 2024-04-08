<?php
# MantisBT - A PHP based bugtracking system
#
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <https://www.gnu.org/licenses/>.

declare( strict_types = 1 );

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/',
    ])
	->withSkip([
		__DIR__ . '/build',
		__DIR__ . '/config',
		__DIR__ . '/library',
	])
    ->withRules([
		\PhpCsFixer\Fixer\Import\NoUnusedImportsFixer::class
	])
;
