<?php

declare ( strict_types=1 );

namespace ECSPrefix202609;

use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Comment: Comment style `//`
		 *
		 * @see https://cs.symfony.com/doc/rules/comment/single_line_comment_style.html
		 */
		SingleLineCommentStyleFixer::class,
	] )
;
