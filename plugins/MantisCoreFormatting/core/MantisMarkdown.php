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
	 * Singleton instance for MantisMarkdown class.
	 */
	private static ?MantisMarkdown $instance = null;

	/**
	 * CSS class for tables.
	 */
	private string $table_class = 'table table-nonfluid';

	/**
	 * Plugin configuration
	 * The value of the constant "ON" (1) or "OFF" (0).
	 */
	private int $config_process_buglinks = OFF;

	/**
	 * Plugin configuration
	 * The value of the constant "ON" (1) or "OFF" (0).
	 */
	private int $config_process_urls = OFF;

	/**
	 * Collection of the captured code blocks.
	 *
	 * @var array<string, string>
	 */
	private array $codeblocks = [];

	public function __construct( ?int $p_process_urls = OFF, ?int $p_process_buglinks = OFF ) {
		# Plugin configuration
		if( in_array( $p_process_urls, [OFF, ON], true ) ) {
			$this->config_process_urls = $p_process_urls;
		}

		if( in_array( $p_process_buglinks, [OFF, ON], true ) ) {
			$this->config_process_buglinks = $p_process_buglinks;
		}

		# Parser configuration
		# Enable line break by default
		$this->breaksEnabled = true;
		# XSS protection
		$this->setSafeMode( true );
		# Only turn URLs into links if config says so
		$this->setUrlsLinked( (bool) $this->config_process_urls );
	}

	public static function getInstance( ?int $p_process_urls = OFF, ?int $p_process_buglinks = OFF ): self {
		if ( null === static::$instance ) {
			static::$instance = new MantisMarkdown( $p_process_urls, $p_process_buglinks );
		}

		return static::$instance;
	}

	/**
	 * Convert text input form Markdown to HTML.
	 *
	 * @param string $p_string The input to convert
	 * @param bool $p_multiline Determines the method for parsing.
	 * @return string HTML markup
	 */
	public function convert( string $p_string, bool $p_multiline = false ): string {
		return $this->finalizeMarkup($p_multiline
			? parent::text( $p_string )
			: parent::line( $p_string )
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
	 * @return int Value of constant "ON" (1) or "OFF" (0)
	 */
	public function getConfigProcessUrls(): int {
		return $this->config_process_urls;
	}

	/**
	 * @return int Value of constant "ON" (1) or "OFF" (0)
	 */
	public function getConfigProcessBugLinks(): int {
		return $this->config_process_buglinks;
	}

	/**
	 * @return array<string, string>
	 */
	public function getCodeblocks(): array {
		return $this->codeblocks;
	}

	/**
	 * Convert an array of element data into the HTML markup.
	 *
	 * @param array $Element Data for an element
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
	 * Convert an email addresses in unmarked text into a link.
	 *
	 * Unlike unmarked URLs, unmarked email addresses are not
	 * processed by Parsedown.
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
		# - The #'s must be followed by a space, a tab or a newline
		if ( preg_match( '/^ {0,3}#{1,6}(?:[ \t]|$)/', $Line['text'] ) ) {
			return parent::blockHeader( $Line );
		}
	}

	/**
	 * Add link attributes to element data.
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
	 * Add link attributes to element data.
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
	 * Add link attributes to element data.
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
	 * the configuration of "g_html_make_links".
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

		if( ON == $this->config_process_buglinks ) {
			$t_markup = string_process_bugnote_link( $t_markup );
			$t_markup = string_process_bug_link( $t_markup );
		}

		$t_markup = string_restore_valid_html_tags( $t_markup );

		$t_markup = mention_format_text( $t_markup );

		foreach( $this->codeblocks as $t_hash => $t_code ) {
			$t_markup = str_replace( $t_hash, $this->disarmCode( $t_code ), $t_markup );
		}

		return $t_markup;
	}

	/**
	 * Encode special chars to their HTML entities.
	 *
	 * @param string $p_markup
	 * @return string The encoded HTML markup
	 */
	public function disarmCode( string $p_markup ): string {
		return htmlspecialchars( $p_markup );
	}
}
