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
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage parsedown
 */

/**
 * MantisMarkdown Extension class, extending Parsedown library
 * This class serves which functions needs to customize and methods to override from Parsedown library
 *
 * To meet and match the MantisBT styles and logic requirements, we have to override and control with it
 * For example: #2 is treated as header markdown
 * So, to make sure #2 treated as bug link (not a header), then we have to change the logic in blockHeader and blockSetextHeader method
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
	private $table_class = null;

	/**
	 * @var string inline style
	 */
	private $inline_style = null;

	/**
	 * MantisMarkdown constructor.
	 */
	public function __construct() {

		# enable line break by default
		$this->breaksEnabled = true;

		# set the table class
		$this->table_class = 'table table-nonfluid';

		# XSS protection
		$this->setSafeMode( true );

		# Only turn URLs into links if config says so
		plugin_push_current( 'MantisCoreFormatting' );
		if( !plugin_config_get( 'process_urls' ) ) {
			$this->setUrlsLinked( false );
		}
		plugin_pop_current();
	}

	/**
	 * @param array $Element Properties of a marked element.
	 * @return string HTML markup of the element.
	 */
	protected function element( array $Element )
	{
		# Adding CSS classes to tables.
		if( $Element['name'] === 'table' ) {
			$Element['attributes']['class'] = $this->table_class;
		}

		return parent::element( $Element );
	}

	/**
	 * Convert a field that supports multiple lines form markdown to html.
	 * @param string $p_text The text to convert.
	 * @return string  The html text.
	 */
	public static function convert_text( $p_text ) {
		self::init();

		# Enabled quote conversion
		# Text processing converts special character to entity name
		# Make sure to restore "&gt;" entity name to its characted result ">"
		$p_text = str_replace( "&gt;", ">", $p_text );

		return self::$mantis_markdown->text( $p_text );
	}

	/**
	 * Convert a field that supports a single line only form markdown to html.
	 * @param string $p_text The text to convert.
	 * @return string  The html text.
	 */
	public static function convert_line( $p_text ) {
		self::init();
		return self::$mantis_markdown->line( $p_text );
	}

	/**
	 * Customize the blockHeader markdown
	 *
	 * @param string $line The Markdown syntax to parse
	 * @access protected
	 * @return string|null HTML representation generated from markdown or null
	 *                if text is not a valid header per CommonMark spec
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
	 * Customize the inlineCode method
	 *
	 * @param array $block A block-level element
	 * @access protected
	 * @return string html representation generated from markdown.
	 */
	protected function inlineCode( $block ) {

		$block = parent::inlineCode( $block );

		if( isset( $block['element']['text'] )) {
			$this->processAmpersand( $block['element']['text'] );
		}

		return $block;
	}

	/**
	 * Customize the blockFencedCodeComplete method
	 *
	 * @param array $block A block-level element
	 * @access protected
	 * @return string html representation generated from markdown.
	 */
	protected function blockFencedCodeComplete( $block = null ) {

		$block = parent::blockFencedCodeComplete( $block );

		if( isset( $block['element']['text']['text'] )) {
			$this->processAmpersand( $block['element']['text']['text'] );
		}

		return $block;
	}

	/**
	 * Customize the blockCodeComplete method
	 *
	 * @param array $block A block-level element
	 * @access protected
	 * @return string html representation generated from markdown.
	 */
	protected function blockCodeComplete( $block ) {

		$block = parent::blockCodeComplete( $block );

		if( isset( $block['element']['text']['text'] )) {
			$this->processAmpersand( $block['element']['text']['text'] );
		}

		return $block;
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

	protected function inlineUrlTag( $Excerpt ) {
		# @FIXME
		# This function is supposed to process links like `<http://example.com>`
		# on single-line texts, but it does not actually work: the function is
		# never called (see Parsedown::line() 1077), because
		# MantisCoreFormattingPlugin::formatted() applies html_specialchars()
		# first, so the < > are converted to &lt;/&gt;.
		return $this->processUrl( parent::inlineUrlTag( $Excerpt ) );
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
	 * Replace any '&amp;' entity in the given string by '&'.
	 *
	 * MantisBT text processing replaces '&' signs by their entity name. Within
	 * code blocks or backticks, Parsedown applies the same transformation again,
	 * so they ultimately become '&amp;amp;'. This reverts the initial conversion
	 * so ampersands are displayed correctly.
	 *
	 * @param string $p_text Text block to process
	 * @return void
	 */
	private function processAmpersand( &$p_text ) {
		$p_text = str_replace( '&amp;', '&', $p_text );
	}

	/**
	 * Set a link's target and rel attributes as appropriate.
	 *
	 * @param array|null $Excerpt
	 * @return array|null
	 *
	 * @see helper_get_link_attributes()
	 */
	private function processUrl( $Excerpt ) {
		if( isset( $Excerpt['element']['attributes']['href'] ) ) {
			$this->processAmpersand( $Excerpt['element']['attributes']['href'] );
		}

		if( isset( $Excerpt['element']['attributes'] ) ) {
			# Set the link's attributes according to configuration
			$Excerpt['element']['attributes'] = array_replace(
				$Excerpt['element']['attributes'],
				helper_get_link_attributes()
			);
		}

		return $Excerpt;
	}

}
