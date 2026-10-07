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

use PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Comment: Spacing of single line comment
		 *
		 * 	#comment > # comment
		 *
		 * @see https://cs.symfony.com/doc/rules/comment/single_line_comment_spacing.html
		 */
		SingleLineCommentSpacingFixer::class,

		/**
		 * Comment: Align multiline comment
		 *
		 * Each line of multi-line DocComments must have an asterisk
		 * [PSR-5] and must be aligned with the first one.
		 *
		 * @see https://cs.symfony.com/doc/rules/phpdoc/align_multiline_comment.html
		 */
		AlignMultilineCommentFixer::class,

		/**
		 * Comment: No empty comment
		 *
		 * @see https://cs.symfony.com/doc/rules/comment/no_empty_comment.html
		 */
		NoEmptyCommentFixer::class,

		/**
		 * Comment: Opening and closing
		 *
		 * DocBlocks must start with two asterisks, multiline comments must
		 * start with a single asterisk, after the opening slash.
		 * Both must end with a single asterisk before the closing slash.
		 *
		 * @see https://cs.symfony.com/doc/rules/comment/multiline_comment_opening_closing.html
		 */
		MultilineCommentOpeningClosingFixer::class,
	] )
;
