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

/**
 * Mantis Core Formatting plugin
 */
class MantisMarkdownPlugin extends MantisPlugin {
	
	/**
	 * Initialized any needed methods, api's, etc... 
	 *
	 * Make sure to turn off Text Processing, causing markdown to not render properly
	 * @return void
	 */	
	function init() {

		if ( ON == config_get( 'plugin_MantisCoreFormatting_process_text' ) ) {
			config_set( 'plugin_MantisCoreFormatting_process_text', OFF );
		}

		if ( ON == config_get( 'plugin_MantisCoreFormatting_process_urls' ) ) {
			config_set( 'plugin_MantisCoreFormatting_process_urls', OFF );
		}
			
		#images can be referenced from internet.
		http_csp_add( 'img-src', "*" );
	}

	/**
	 * Make sure to turn ON Text Processing back on uninstall
	 * reset it back to MantisCoreFormatting position
	 */	
	function uninstall() {
		config_set( 'plugin_MantisCoreFormatting_process_text', ON );
		config_set( 'plugin_MantisCoreFormatting_process_urls', ON );
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
		);
	}

	/**
	 * Markdown processing.
	 *
	 * Performs markdown and bug links processing
	 *
	 * @param string  $p_event     Event name.
	 * @param string  $p_string    Raw text to process.
	 * @param boolean $p_multiline True for multiline text (default), false for single-line.
	 *
	 * @return string The html text
	 */
	function markdown( $p_event, $p_string, $p_multiline = true ) {
		
		$t_string = $p_string;	
		
		# Process bug links
		if( ON == config_get( 'plugin_MantisCoreFormatting_process_buglinks' ) ) {
			$t_string = string_process_bug_link( $t_string );
			$t_string = string_process_bugnote_link( $t_string );
		}
		
		$t_string = mention_format_text( $t_string, true );

		# Markdown processing
		if ( $p_multiline ) {
			$t_string = MantisMarkdown::convert_text( $t_string );
		} else {
			$t_string = MantisMarkdown::convert_line( $t_string );
		}

		return $t_string;
	}

}
