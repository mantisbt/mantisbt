<?php

require_once( config_get( 'class_path' ) . 'MantisFormattingPlugin.class.php' );

class MantisCoreFormattingPlugin extends MantisFormattingPlugin {

	function register() {
		$this->name			= lang_get( 'plugin_format_title' );
		$this->description	= lang_get( 'plugin_format_description' );
		$this->page			= 'config';

		$this->version		= '1.0a';
		$this->requires		= array(
			'MantisCore' => '1.2.0',
		);

		$this->author		= 'Mantis Team';
		$this->contact		= 'mantisbt-dev@lists.sourceforge.net';
		$this->url			= 'http://www.mantisbt.org';
	}

	/**
	 * Default plugin configuration.
	 */
	function config() {
		return array(
			'process_text'		=> ON,
			'process_urls'		=> ON,
			'process_buglinks'	=> ON,
			'process_vcslinks'	=> ON,
		);
	}

	/**
	 * Plain text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @param boolean Multiline text
	 * @return multi Array with formatted text and multiline paramater
	 */
	function text( $p_event, $p_string, $p_multiline = true ) {
		$t_string = $p_string;

		if ( ON == plugin_config_get( 'process_text' ) ) {
			$t_string = string_strip_hrefs( $t_string );
			$t_string = string_html_specialchars( $t_string );
			$t_string = string_restore_valid_html_tags( $t_string, /* multiline = */ true );

			if ( $p_multiline ) {
				$t_string = string_preserve_spaces_at_bol( $t_string );
				$t_string = string_nl2br( $t_string );
			}
		}

		return array( $t_string, $p_multiline );
	}

	/**
	 * Formatted text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @param boolean Multiline text
	 * @return multi Array with formatted text and multiline paramater
	 */
	function formatted( $p_event, $p_string, $p_multiline = true ) {
		$t_string = $p_string;

		if ( ON == plugin_config_get( 'process_text' ) ) {
			$t_string = string_strip_hrefs( $t_string );
			$t_string = string_html_specialchars( $t_string );
			$t_string = string_restore_valid_html_tags( $t_string, /* multiline = */ true );

			if ( $p_multiline ) {
				$t_string = string_preserve_spaces_at_bol( $t_string );
				$t_string = string_nl2br( $t_string );
			}
		}

		if ( ON == plugin_config_get( 'process_urls' ) ) {
			$t_string = string_insert_hrefs( $t_string );
		}

		if ( ON == plugin_config_get( 'process_buglinks' ) ) {
			$t_string = string_process_bug_link( $t_string );
			$t_string = string_process_bugnote_link( $t_string );
		}

		if ( ON == plugin_config_get( 'process_vcslinks' ) ) {
			$t_string = string_process_cvs_link( $t_string );
		}

		return array( $t_string, $p_multiline );
	}

	/**
	 * RSS text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @return string Formatted text
	 */
	function rss( $p_event, $p_string ) {
		$t_string = $p_string;

		if ( ON == plugin_config_get( 'process_text' ) ) {
			$t_string = string_strip_hrefs( $t_string );
			$t_string = string_html_specialchars( $t_string );
			$t_string = string_restore_valid_html_tags( $t_string );
			$t_string = string_nl2br( $t_string );
		}

		if ( ON == plugin_config_get( 'process_urls' ) ) {
			$t_string = string_insert_hrefs( $t_string );
		}

		if ( ON == plugin_config_get( 'process_buglinks' ) ) {
			$t_string = string_process_bug_link( $t_string, /* anchor */ true, /* detailInfo */ false, /* fqdn */ true );
			$t_string = string_process_bugnote_link( $t_string, /* anchor */ true, /* detailInfo */ false, /* fqdn */ true );
		}

		if ( ON == plugin_config_get( 'process_vcslinks' ) ) {
			$t_string = string_process_cvs_link( $t_string );
		}

		return $t_string;
	}

	/**
	 * Email text processing.
	 * @param string Event name
	 * @param string Unformatted text
	 * @return string Formatted text
	 */
	function email( $p_event, $p_string ) {
		$t_string = $p_string;

		if ( ON == plugin_config_get( 'process_text' ) ) {
			$t_string = string_strip_hrefs( $t_string );
		}

		if ( ON == plugin_config_get( 'process_buglinks' ) ) {
			$t_string = string_process_bug_link( $t_string, false );
			$t_string = string_process_bugnote_link( $t_string, false );
		}

		if ( ON == plugin_config_get( 'process_vcslinks' ) ) {
			$t_string = string_process_cvs_link( $t_string, false );
		}

		return $t_string;
	}

}
