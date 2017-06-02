<?php
/**
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
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

require_api( 'mention_api.php' );
require_once( 'core/MantisMarkdown.php' );

/**
 * Mantis Core Formatting plugin
 */
class MantisCoreFormattingPlugin extends MantisFormattingPlugin {
	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = lang_get( 'plugin_format_title' );
		$this->description = lang_get( 'plugin_format_description' );
		$this->page = 'config';

		$this->version = MANTIS_VERSION;
		$this->requires = array(
			'MantisCore' => '2.1.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Event hook declaration.
	 * @return array
	 */
	function hooks() {
		return array(
			'EVENT_DISPLAY_TEXT'		=> 'text',			# Text String Display
			'EVENT_DISPLAY_FORMATTED'	=> 'formatted',		# Formatted String Display
			'EVENT_DISPLAY_RSS'			=> 'rss',			# RSS String Display
			'EVENT_DISPLAY_EMAIL'		=> 'email',			# Email String Display
		);
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	function config() {
		return array(
			'process_text'		=> ON,
			'process_urls'		=> ON,
			'process_buglinks'	=> ON,
			'process_markdown'	=> OFF
		);
	}

	/**
	 * Process Text, make sure to block any possible xss attacks
	 *
	 * @param string  $p_string    Raw text to process.
	 * @param boolean $p_multiline True for multiline text (default), false for single-line.
	 *                             Determines which html tags are used.
	 *
	 * @return string valid formatted text
	 */
	private function processText( $p_string, $p_multiline = true ){

		$t_string = $p_string;

		$t_string = string_strip_hrefs( $t_string );
		$t_string = string_html_specialchars( $t_string );
		$t_string = string_restore_valid_html_tags( $t_string, $p_multiline );

		return $t_string;
	}

	/**
	 * Process Bug and Note links
	 * @param string  $p_string    Raw text to process.
	 *
	 * @return string Formatted text
	 */
	private function processBugAndNoteLinks( $p_string ){

		$t_string = $p_string;

		$t_string = string_process_bug_link( $t_string );
		$t_string = string_process_bugnote_link( $t_string );

		return $t_string;
	}

	/**
	 * Plain text processing.
	 *
	 * @param string  $p_event     Event name.
	 * @param string  $p_string    Raw text to process.
	 * @param boolean $p_multiline True for multiline text (default), false for single-line.
	 *                             Determines which html tags are used.
	 *
	 * @return string Formatted text
	 *
	 * @see $g_html_valid_tags
	 * @see $g_html_valid_tags_single_line
	 */
	function text( $p_event, $p_string, $p_multiline = true ) {
		static $s_text;

		$t_string = $p_string;

		if( null === $s_text ) {
			$s_text = plugin_config_get( 'process_text' );
		}

		if( ON == $s_text ) {
			$t_string = $this->processText( $t_string, $p_multiline );

			if( $p_multiline ) {
				$t_string = string_preserve_spaces_at_bol( $t_string );
				$t_string = string_nl2br( $t_string );
			}
		}

		return $t_string;
	}

	/**
	 * Formatted text processing.
	 *
	 * Performs plain text, URLs, bug links, markdown processing
	 *
	 * @param string  $p_event     Event name.
	 * @param string  $p_string    Raw text to process.
	 * @param boolean $p_multiline True for multiline text (default), false for single-line.
	 *                             Determines which html tags are used.
	 *
	 * @return string Formatted text
	 */
	function formatted( $p_event, $p_string, $p_multiline = true ) {
		static $s_text, $s_urls, $s_buglinks, $s_markdown;

		$t_string = $p_string;

		if( null === $s_text ) {
			$s_text = plugin_config_get( 'process_text' );
		}

		if( null === $s_urls ) {
			$s_urls = plugin_config_get( 'process_urls' );
			$s_buglinks = plugin_config_get( 'process_buglinks' );
		}

		if( null === $s_markdown ) {
			$s_markdown = plugin_config_get( 'process_markdown' );
		}

		if( ON == $s_text ) {
			$t_string = $this->processText( $t_string );

			if( $p_multiline && OFF == $s_markdown ) {
				$t_string = string_preserve_spaces_at_bol( $t_string );
				$t_string = string_nl2br( $t_string );
			}
		}

		if( ON == $s_urls && OFF == $s_markdown ) {
			$t_string = string_insert_hrefs( $t_string );
		}

		if ( ON == $s_buglinks ) {
			$t_string = $this->processBugAndNoteLinks( $t_string );
		}

		$t_string = mention_format_text( $t_string, /* html */ true );

		# Process Markdown
		if( ON == $s_markdown ) {
			if( $p_multiline ) {
				$t_string = MantisMarkdown::convert_text( $t_string );
			} else {
				$t_string = MantisMarkdown::convert_line( $t_string );
			}
		}

		return $t_string;
	}

	/**
	 * RSS text processing.
	 * @param string $p_event  Event name.
	 * @param string $p_string Unformatted text.
	 * @return string Formatted text
	 */
	function rss( $p_event, $p_string ) {
		static $s_text, $s_urls, $s_buglinks;

		$t_string = $p_string;

		if( null === $s_text ) {
			$s_text = plugin_config_get( 'process_text' );
			$s_urls = plugin_config_get( 'process_urls' );
			$s_buglinks = plugin_config_get( 'process_buglinks' );
		}

		if( ON == $s_text ) {
			$t_string = string_strip_hrefs( $t_string );
			$t_string = string_html_specialchars( $t_string );
			$t_string = string_restore_valid_html_tags( $t_string );
			$t_string = string_nl2br( $t_string );
		}

		if( ON == $s_urls ) {
			$t_string = string_insert_hrefs( $t_string );
		}

		if( ON == $s_buglinks ) {
			$t_string = string_process_bug_link( $t_string, true, false, true );
			$t_string = string_process_bugnote_link( $t_string, true, false, true );
		}

		$t_string = mention_format_text( $t_string, /* html */ true );

		return $t_string;
	}

	/**
	 * Email text processing.
	 * @param string $p_event  Event name.
	 * @param string $p_string Unformatted text.
	 * @return string Formatted text
	 */
	function email( $p_event, $p_string ) {
		static $s_text, $s_buglinks;

		$t_string = $p_string;

		if( null === $s_text ) {
			$s_text = plugin_config_get( 'process_text' );
			$s_buglinks = plugin_config_get( 'process_buglinks' );
		}

		if( ON == $s_text ) {
			$t_string = string_strip_hrefs( $t_string );
		}

		if( ON == $s_buglinks ) {
			$t_string = string_process_bug_link( $t_string, false );
			$t_string = string_process_bugnote_link( $t_string, false );
		}

		$t_string = mention_format_text( $t_string, /* html */ false );

		return $t_string;
	}
}
