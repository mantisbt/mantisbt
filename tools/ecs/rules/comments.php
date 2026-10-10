<?php

declare ( strict_types=1 );

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

	/**
	 * Comment: Align multiline comment
	 *
	 * Each line of multi-line DocComments must have an asterisk
	 * [PSR-5] and must be aligned with the first one.
	 *
	 * @see https://cs.symfony.com/doc/rules/phpdoc/align_multiline_comment.html
	 */
	->withConfiguredRule( AlignMultilineCommentFixer::class, [
		// 'comment_type' => 'phpdocs_only',
		// 'comment_type' => 'phpdocs_like',
		'comment_type' => 'all_multiline',
	] )
;
