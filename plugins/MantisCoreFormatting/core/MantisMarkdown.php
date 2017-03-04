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

require_lib( 'parsedown' . DIRECTORY_SEPARATOR . 'Parsedown.php' );

/**
 * A class that overrides default Markdown parsing for Mantis specific scenarios.
 */
class MantisMarkdown extends Parsedown
{
	/**
	 * MantisMarkdown singleton instance for MantisMarkdown class.
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
	}

	/**
	 * Convert a field that supports multiple lines form markdown to html.
	 * @param string $p_text The text to convert.
	 * @return string  The html text.
	 */
	public static function convert_text( $p_text ) {
		self::init();
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
	 * @return null|string HTML representation generated from markdown or null 
	 				for example text starts with # symbol and immediately followed by 
					any character other than space.
	 */
	protected function blockHeader( $line ) {
		
		# Text either indented 0-3 spaces
		# An opening sequence of 1–6 # characters
		# The opening sequence of # characters must be followed by a space
		# The #'s can be followed by a newline as well
		if ( preg_match( '/^ {0,3}#{1,6}( |$)/', $line['text'] ) ) {
			return parent::blockHeader($line);
		}
	}

	/**
	 * Add a class attribute on a table markdown elements
	 *
	 * @param string $line The Markdown syntax to parse
	 * @param array $block A block-level element
	 * @param string $fn the function name to call (blockTable or blockTableContinue)
	 * @access private
	 * @return string html representation generated from markdown.
	 */
	private function __doTable( $line, $block, $fn ) {
		if( $block = call_user_func( 'parent::' . $fn, $line, $block ) ) {
			$block['element']['attributes']['class'] = $this->table_class;
		}

		return $block;
	}

	/**
	 * Customize the logic on blockTable method by adding a class attribute
	 *
	 * @param string $line The Markdown syntax to parse
	 * @param array $block A block-level element
	 * @access protected
	 * @return string html representation generated from markdown.
	 */
	protected function blockTable( $line, array $block = null ) {
		return $this->__doTable( $line, $block, __FUNCTION__ );
	}

	/**
	 * Customize the logic on blockTableContinue method by adding a class attribute
	 *
	 * @param string $line The Markdown syntax to parse
	 * @param array $block A block-level element
	 * @access protected
	 * @return string html representation generated from markdown.
	 */
	protected function blockTableContinue( $line, array $block ) {
		return $this->__doTable( $line, $block, __FUNCTION__ );
	}

	/**
	 * Add an inline style on a blockquote markdown elements
	 *
	 * @param string $line The Markdown syntax to parse
	 * @param array $block A block-level element
	 * @param string $fn the function name to call (blockQuote or blockQuoteContinue)
	 * @access private
	 * @return string html representation generated from markdown.
	 */
	private function __quote( $line, $block, $fn ) {

		if( $block = call_user_func( 'parent::' . $fn, $line, $block ) ) {
			# TODO: To open another issue to track css style sheet issue vs. inline style.
			$block['element']['attributes']['style'] = 'padding:0.13em 1em;color:#777;border-left:0.25em solid #C0C0C0;font-size:13px;';
		}

		return $block;
	}

	/**
	 * Customize the blockQuote method by adding a style attribute
	 *
	 * @param string $line The Markdown syntax to parse
	 * @access protected
	 * @return string html representation generated from markdown.
	 */
	protected function blockQuote( $line ){
		return $this->__quote( $line, array(), __FUNCTION__ );
	}

	/**
	 * Customize the blockQuoteContinue method by adding a style attribute
	 *
	 * @param string $line The Markdown syntax to parse
	 * @param array $block A block-level element
	 * @access protected
	 * @return string html representation generated from markdown.
	 */
	protected function blockQuoteContinue( $line, array $block ){
		return $this->__quote( $line, $block, __FUNCTION__ );
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
}
