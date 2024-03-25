<?php
# MantisBT - A PHP based bugtracking system

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
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MantisMarkdown class
 * @copyright Copyright 2016 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 * @package MantisBT
 * @subpackage parsedown
 */

/**
 * MantisMarkdown Extension class, extending Parsedown library
 * This class serves which functions needs to customize and methods to override from Parsedown library
 *
 * To meet and match the MantisBT styles and logic requirements, we have to override and control with it
 * For example: #2 is treated as header Markdown
 * So, to make sure #2 treated as bug link (not a header), then we have to change the logic in blockHeader method
 *
 * @package MantisBT
 * @subpackage parsedown
 *
 * @uses Parsedown Library
 */

/**
 * A class that overrides default Markdown parsing for Mantis specific scenarios.
 */
class MantisMarkdown extends Parsedown
{
	/**
	 * singleton instance for MantisMarkdown class.
	 */
	private static ?MantisMarkdown $mantis_markdown = null;

	/**
	 * table class to add
	 */
	private string $table_class = 'table table-nonfluid';

	/**
	 * plugin configuration.
	 * The value of the constant "ON" (1) or "OFF" (0).
	 */
	private int $config_process_buglinks;

	/**
	 * plugin configuration
	 * The value of the constant "ON" (1) or "OFF" (0).
	 */
	private int $config_process_urls;

	/**
	 * Collection of the captured code blocks.
	 *
	 * @var array<string, string>
	 */
	private array $codeblocks = [];

	public function __construct() {
		# enable line break by default
		$this->breaksEnabled = true;

		# XSS protection
		$this->setSafeMode( true );

		# Plugin configuration
		# @todo decoupling the parser, inserting config values via constructor or setter.
		plugin_push_current( 'MantisCoreFormatting' );
		$this->config_process_urls = plugin_config_get('process_urls');
		$this->config_process_buglinks = plugin_config_get('process_buglinks');
		plugin_pop_current();

		# Only turn URLs into links if config says so
		$this->setUrlsLinked( (bool) $this->config_process_urls );
	}

	/**
	 * Initialize the singleton static instance.
	 */
	private static function init(): void {
		if ( null === static::$mantis_markdown ) {
			static::$mantis_markdown = new MantisMarkdown();
		}
	}

	/**
	 * Convert a field that supports multiple lines form Markdown to html.
	 *
	 * @param string $p_text The input to parse
	 * @return string HTML markup
	 */
	public static function convert_text( string $p_text ): string {
		self::init();

		return self::$mantis_markdown->finalizeMarkup(
			self::$mantis_markdown->text( $p_text )
		);
	}

	/**
	 * Convert a field that supports a single line only from Markdown to html.
	 *
	 * @param string $p_text The input to convert
	 * @return string HTML markup
	 */
	public static function convert_line( string $p_text ): string {
		self::init();

		return self::$mantis_markdown->finalizeMarkup(
			self::$mantis_markdown->line( $p_text )
		);
	}

	/**
	 * Hash value of a piece of code that is used as the
	 * key for $this->codeblocks collection.
	 */
	public function hash( string $p_string ): string {
		return md5( $p_string );
	}

	/**
	 * Activating or deactivating the "process_urls" functionality.
	 *
	 * @param int $p_value value of constant "ON" (1) or "OFF" (0)
	 */
	public function setConfigProcessUrls( int $p_value ): void {
		// @todo test for is 0 or 1?
		$this->config_process_urls = $p_value;
		$this->setUrlsLinked( (bool) $this->config_process_urls );
	}

	/**
	 * @return int value of constant "ON" (1) or "OFF" (0)
	 */
	public function getConfigProcessUrls(): int {
		return $this->config_process_urls;
	}

	/**
	 * @return array<string, string>
	 */
	public function getCodeblocks(): array {
		return $this->codeblocks;
	}

	/**
	 * Build the HTML markup from an array of elements data.
	 *
	 * @param array $Element data for an element
	 * @return string HTML markup for an element
	 */
	protected function element( array $Element ): string {
		# Capture the code blocks to prevent them from being processed further.
		if( $Element['name'] === 'code' ) {
			$t_hash = $this->hash( $Element['text'] );
			$this->codeblocks[$t_hash] = $Element['text'];
			$Element['text'] = $t_hash;
		}

		# Adding CSS classes to tables.
		if( $Element['name'] === 'table' ) {
			$Element['attributes']['class'] = $this->table_class;
		}

		return parent::element( $Element );
	}

	/**
	 * Converting email addresses in unmarked text.
	 */
	protected function unmarkedText( $text ): string {
		if( ON == $this->config_process_urls && false !== strpos( $text, '@' ) ) {
			$text = string_insert_hrefs( $text );
		}

		return parent::unmarkedText( $text );
	}

	/**
	 * Implementation of the CommonMark specification for headers,
	 * as Parsedown does not follow the specifications.
	 *
	 * @see https://spec.commonmark.org/0.31.2/#example-62
	 * @see https://parsedown.org/demo
	 *
	 * @param array $Line Data for the block element
	 * @return array|void Only return data for the header element if it matches
	 *                    the specification
	 */
	protected function blockHeader( $Line ) {
		# - the opening # may be indented 0-3 spaces
		# - a sequence of 1–6 '#' characters
		# - The #'s must be followed by a space or a newline
		if ( preg_match( '/^ {0,3}#{1,6}(?: |$)/', $Line['text'] ) ) {
			return parent::blockHeader( $Line );
		}
	}

	/**
	 * Add Link attributes to element data.
	 *
	 * - [link](http://example.com)
	 *
	 * @param array $Excerpt Element data
	 * @return array|null Element data or nothing
	 */
	protected function inlineLink( $Excerpt ): ?array
	{
		return $this->processUrl( parent::inlineLink( $Excerpt ) );
	}

	/**
	 * Add Link attributes to element data.
	 *
	 * - <http://example.com>
	 * - <user@example.com>
	 *
	 * @param array $Excerpt Element data
	 * @return array|null Element data or nothing
	 */
	protected function inlineUrlTag( $Excerpt ): ?array
	{
		return $this->processUrl( parent::inlineUrlTag( $Excerpt ) );
	}

	/**
	 * Add Link attributes to element data.
	 *
	 * - Not marked URLs. "https://example.com"
	 *
	 * @param array $Excerpt Element data
	 * @return array|null Element data or nothing
	 */
	protected function inlineUrl( $Excerpt ): ?array
	{
		return $this->processUrl( parent::inlineUrl( $Excerpt ) );
	}

	/**
	 * Set the attributes "target" and "rel" of a link according to
	 * the configuration of "$g_html_make_links".
	 *
	 * @see helper_get_link_attributes()
	 *
	 * @param array|null $Excerpt
	 * @return array|null
	 */
	private function processUrl( ?array $Excerpt = null ): ?array
	{
		if( isset( $Excerpt['element']['attributes'] ) ) {
			$Excerpt['element']['attributes'] = array_replace(
				$Excerpt['element']['attributes'],
				helper_get_link_attributes()
			);
		}

		return $Excerpt;
	}

	/**
	 * Finalize the HTML markup.
	 *
	 * - process mentions of bugs and bug notes
	 * - restore valid html tags
	 * - process mentions of users
	 * - restore the captured codeblocks
	 *
	 * @param string $p_markup The prepared HTML markup
	 * @return string The finalized HTML markup
	 */
	private function finalizeMarkup( string $p_markup ): string {
		$t_markup = $p_markup;

		if( ON == self::$mantis_markdown->config_process_buglinks ) {
			$t_markup = string_process_bugnote_link( $t_markup );
			$t_markup = string_process_bug_link( $t_markup );
		}

		$t_markup = string_restore_valid_html_tags( $t_markup );

		$t_markup = mention_format_text( $t_markup );

		foreach( self::$mantis_markdown->codeblocks as $t_hash => $t_code ) {
			$t_markup = str_replace( $t_hash, htmlspecialchars($t_code), $t_markup );
		}

		return $t_markup;
	}
}
