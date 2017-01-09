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
 * Mantis Markdown Plugin
 */
class MantisMarkdownPlugin extends MantisPlugin {
	
	/**
	 * Initialized any needed methods, api's, etc... 
	 *
	 * Make sure to turn Text Processing off, causing markdown to not render properly
	 * @return void
	 */	
	function init() {

		if ( ON == config_get( 'plugin_MantisCoreFormatting_process_text' ) ) {
			config_set( 'plugin_MantisCoreFormatting_process_text', OFF );
		}

		if ( ON == config_get( 'plugin_MantisCoreFormatting_process_urls' ) ) {
			config_set( 'plugin_MantisCoreFormatting_process_urls', OFF );
		}
		
		if ( ON == config_get( 'plugin_MantisCoreFormatting_process_buglinks' ) ) {
			config_set( 'plugin_MantisCoreFormatting_process_buglinks', OFF );
		}

	}

	/**
	 * Make sure to turn Text Processing back on uninstall
	 * reset it back to MantisCoreFormatting position
	 */	
	function uninstall() {
		config_set( 'plugin_MantisCoreFormatting_process_text', ON );
		config_set( 'plugin_MantisCoreFormatting_process_urls', ON );
		config_set( 'plugin_MantisCoreFormatting_process_buglinks', ON );
	}

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = lang_get( 'plugin_markdown_title' );
		$this->description = lang_get( 'plugin_markdown_description' );
		
		$this->version = MANTIS_VERSION;
		$this->requires = array(
			'MantisCore' => '2.0.0',
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
			'EVENT_DISPLAY_FORMATTED'	=> 'markdown',		# Formatted String Display
			'EVENT_CORE_HEADERS' 		=> 'csp_headers',
			'EVENT_DISPLAY_RSS'		=> 'rss',		# RSS String Display
			'EVENT_DISPLAY_EMAIL'		=> 'email',		# Email String Display
		);
	}

	/**
	 * Add img-src directives to enable to referenced images from internet.
	 * @return void
	 */
	function csp_headers() {
		http_csp_add( 'img-src', "*" );
	}

	/**
	 * Markdown processing.
	 *
	 * Performs markdown and mantis current formatting process
	 *
	 * @param string  $p_event     Event name.
	 * @param string  $p_string    Raw text to process.
	 * @param boolean $p_multiline True for multiline text (default), false for single-line.
	 *
	 * @return string The html text
	 */
	function markdown( $p_event, $p_string, $p_multiline = true ) {
		
		$t_string = $p_string;	
		
		# Keep mantis current formatting behavior 
		$t_string = string_strip_hrefs( $t_string );
		$t_string = string_html_specialchars( $t_string );
		$t_string = string_restore_valid_html_tags( $t_string, $p_multiline );

		# Process bug links
		$t_string = string_process_bug_link( $t_string );
		$t_string = string_process_bugnote_link( $t_string );

		# Process mention
		$t_string = mention_format_text( $t_string, true );

		# Process markdown with multiline enabled (description, notes, etc)
		if ( $p_multiline ) {
			# restore and allows img tag to display
			$t_string = preg_replace_callback( "/&quot;|'/",
				function ( ) {
					return "";
				},
				str_replace('/">', '">', preg_replace( '#&lt;img.+?src=(.*).*?&gt;#i', '<img src="$1">', $t_string ))
			);

			# We need to enabled quote conversion
			# "> quote or >quote" is part of an html tag
			# Make sure to replaced the restored tags with ">"
			$t_string = str_replace( "&gt;", ">", $t_string );

			$t_string = MantisMarkdown::convert_text( $t_string );
		}
			
		return $t_string;
	}

	/**
	 * RSS markdown processing.
	 * @param string $p_event  Event name.
	 * @param string $p_string Unformatted text.
	 * @return string Formatted text
	 */
	function rss( $p_event, $p_string ) {
		
		$t_string = $this->markdown( $p_event, $p_string );

		return $t_string;
	}

	/**
	 * Email text processing.
	 * @param string $p_event  Event name.
	 * @param string $p_string Unformatted text.
	 * @return string Formatted text
	 */
	function email( $p_event, $p_string ) {
		
		$t_string =  $p_string;
		
		$t_string = string_strip_hrefs( $t_string );
		$t_string = string_process_bug_link( $t_string, false );
		$t_string = string_process_bugnote_link( $t_string, false );
		$t_string = mention_format_text( $t_string, /* html */ false );

		return $t_string;
	}
}