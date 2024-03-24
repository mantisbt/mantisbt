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
 * For example: #2 is treated as header markdown
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
	 * @var MantisMarkdown singleton instance for MantisMarkdown class.
	 */
	private static $mantis_markdown = null;

	/**
	 * @var string table class
	 */
	private $table_class = 'table table-nonfluid';

	/**
	 * @var int plugin configuration
	 */
	private $config_process_buglinks;

	/**
	 * @var int plugin configuration
	 */
	private $config_process_urls;

	/**
	 * @var array codeblocks
	 */
	private $codeblocks = [];

	/**
	 * MantisMarkdown constructor.
	 */
	public function __construct() {
		# enable line break by default
		$this->breaksEnabled = true;

		# XSS protection
		$this->setSafeMode( true );

		# Plugin configuration
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
	private static function init() {
		if ( null === static::$mantis_markdown ) {
			static::$mantis_markdown = new MantisMarkdown();
		}

		return static::$mantis_markdown;
	}

	/**
	 * Convert a field that supports multiple lines form markdown to html.
	 *
	 * @param string $p_text The text to convert.
	 * @return string  The html text.
	 */
	public static function convert_text( $p_text ) {
		self::init();
		$t_markup = self::$mantis_markdown->text( $p_text );

		return self::$mantis_markdown->finalizeMarkup( $t_markup );
	}

	/**
	 * Convert a field that supports a single line only form markdown to html.
	 *
	 * @param string $p_text The text to convert.
	 * @return string  The html text.
	 */
	public static function convert_line( $p_text ) {
		self::init();
		$t_markup = self::$mantis_markdown->line( $p_text );

		return self::$mantis_markdown->finalizeMarkup( $t_markup );
	}

	/**
	 * @param array $Element
	 * @return string Markup for a element.
	 */
	protected function element( array $Element )
	{
		# Catch the codeblocks to prevent them from being processed by apply links.
		if( $Element['name'] === 'code' ) {
			$t_hash = md5($Element['text']);
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
	 * Catch plain inserted email addresses in unmarked text.
	 *
	 * @param string $text
	 * @return string
	 */
	protected function unmarkedText($text)
	{
		if( ON == $this->config_process_urls && false !== strpos( $text, '@' ) ) {
			$text = string_insert_hrefs($text);
		}

		return parent::unmarkedText($text);
	}

	/**
	 * Customize the inlineLink method
	 *
	 * @param array $Excerpt A block-level element
	 * @access protected
	 * @return array html representation generated from markdown.
	 */
	protected function inlineLink( $Excerpt ) {
		return $this->processUrl( parent::inlineLink( $Excerpt ) );
	}

	protected function inlineUrl( $Excerpt ) {
		return $this->processUrl( parent::inlineUrl( $Excerpt ) );
	}

	protected function inlineUrlTag( $Excerpt )	{
		return $this->processUrl( parent::inlineUrlTag( $Excerpt ) );
	}

	/**
	 * Detect whether text is a valid heading according to the commonmark specification.
	 * If not, it is probably an issue mention.
	 *
	 * @param array $line The Markdown syntax to parse
	 * @access protected
	 * @return array|void
	 */
	protected function blockHeader( $line ) {
		# Header detection logic
		# - the opening # may be indented 0-3 spaces
		# - a sequence of 1–6 '#' characters
		# - The #'s must be followed by a space or a newline
		if ( preg_match( '/^ {0,3}#{1,6}(?: |$)/', $line['text'] ) ) {
			return parent::blockHeader($line);
		}
	}

	/**
	 * Set a link's target and rel attributes according to configuration.
	 *
	 * @param array|null $Excerpt
	 * @return array|null
	 *
	 * @see helper_get_link_attributes()
	 */
	private function processUrl( $Excerpt ) {
		if( isset( $Excerpt['element']['attributes'] ) ) {
			$Excerpt['element']['attributes'] = array_replace(
				$Excerpt['element']['attributes'],
				helper_get_link_attributes()
			);
		}

		return $Excerpt;
	}

	/**
	 * Finalize markup, process links and restore valid html tags and codeblocks.
	 *
	 * @param $p_markup
	 * @return string HTML markup
	 */
	private function finalizeMarkup( $p_markup ) {
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
