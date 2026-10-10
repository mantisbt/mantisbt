<?php

declare ( strict_types=1 );

namespace ECSPrefix202609;

use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
	->withRules( [
		/**
		 * Basic: Encoding
		 *
		 * PHP code MUST use only UTF-8 without BOM (remove BOM).
		 *
		 * @see https://cs.symfony.com/doc/rules/basic/encoding.html
		 */
		EncodingFixer::class,

		/**
		 * Whitespace: Line encoding
		 *
		 * All PHP files must use same line ending. Default is `\n`
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/line_ending.html
		 */
		LineEndingFixer::class,

		/**
		 * PHP tag: Full opening tag
		 *
		 * 	<input>
		 * 	<?
		 * 	</input>
		 *
		 * 	<output>
		 * 	<?php
		 * 	</output>
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/full_opening_tag.html
		 */
		FullOpeningTagFixer::class,

		/**
		 * PHP tag: Linebreak after opening tag
		 *
		 * Ensure there is no code on the same line as the PHP open tag.
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/linebreak_after_opening_tag.html
		 */
		LinebreakAfterOpeningTagFixer::class,

		/**
		 * PHP tag: No closing tag
		 *
		 * The closing `?>` tag MUST be omitted from files containing only PHP.
		 *
		 * @see https://cs.symfony.com/doc/rules/php_tag/no_closing_tag.html
		 */
		NoClosingTagFixer::class,

		/**
		 * Whitespace: Single blank line at eof
		 *
		 * A PHP file without end tag must always end with a single empty line feed.
		 *
		 * @see https://cs.symfony.com/doc/rules/whitespace/single_blank_line_at_eof.html
		 */
		SingleBlankLineAtEofFixer::class,
	] )
;
