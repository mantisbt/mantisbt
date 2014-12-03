<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage StringProcessingAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires bug_api
 */
require_once( 'bug_api.php' );

/**
 * requires user_pref_api
 */
require_once( 'user_pref_api.php' );

$g_cache_html_valid_tags = '';
$g_cache_html_valid_tags_single_line = '';

/**
 * Preserve spaces at beginning of lines.
 * Lines must be separated by \n rather than <br />
 * @param string $p_string
 * @return string
 */
 function string_preserve_spaces_at_bol( $p_string ) {
	$lines = explode( "\n", $p_string );
	$line_count = count( $lines );
	for( $i = 0;$i < $line_count;$i++ ) {
		$count = 0;
		$prefix = '';

		$t_char = utf8_substr( $lines[$i], $count, 1 );
		$spaces = 0;
		while(( $t_char == ' ' ) || ( $t_char == "\t" ) ) {
			if( $t_char == ' ' ) {
				$spaces++;
			} else {
				$spaces += 4;
			}

			// 1 tab = 4 spaces, can be configurable.

			$count++;
			$t_char = utf8_substr( $lines[$i], $count, 1 );
		}

		for( $j = 0;$j < $spaces;$j++ ) {
			$prefix .= '&#160;';
		}

		$lines[$i] = $prefix . utf8_substr( $lines[$i], $count );
	}
	return implode( "\n", $lines );
}

/**
 * Prepare a string to be printed without being broken into multiple lines
 * @param string $p_string
 * @return string
 */
function string_no_break( $p_string ) {
	if( strpos( $p_string, ' ' ) !== false ) {
		return '<span class="nowrap">' . $p_string . "</span>";
	} else {
		return $p_string;
	}
}

/**
 * Similar to nl2br, but fixes up a problem where new lines are doubled between
 * html pre tags.
 * additionally, wrap the text an $p_wrap character intervals if the config is set
 * @param string $p_string
 * @param int $p_wrap
 * @return string
 */
function string_nl2br( $p_string, $p_wrap = 100 ) {
	$output = '';
	$pieces = preg_split( '/(<pre[^>]*>.*?<\/pre>)/is', $p_string, -1, PREG_SPLIT_DELIM_CAPTURE );
	if( isset( $pieces[1] ) ) {
		foreach( $pieces as $piece ) {
			if( preg_match( '/(<pre[^>]*>.*?<\/pre>)/is', $piece ) ) {
				$piece = preg_replace( "/<br[^>]*?>/", '', $piece );

				# @@@ thraxisp - this may want to be replaced by html_entity_decode (or equivalent)
				#     if other encoded characters are a problem
				$piece = preg_replace( '/&#160;/', ' ', $piece );
				if( ON == config_get( 'wrap_in_preformatted_text' ) ) {
					$output .= preg_replace( '/([^\n]{' . $p_wrap . ',}?[\s]+)(?!<\/pre>)/', "$1\n", $piece );
				} else {
					$output .= $piece;
				}
			} else {
				$output .= nl2br( $piece );
			}
		}
		return $output;
	} else {
		return nl2br( $p_string );
	}
}

/**
 * Prepare a multiple line string for display to HTML
 * @param string $p_string
 * @return string
 */
function string_display( $p_string ) {
	$t_data = event_signal( 'EVENT_DISPLAY_TEXT', $p_string, true );
	return $t_data;
}

/**
 * Prepare a single line string for display to HTML
 * @param string $p_string
 * @return string
 */
function string_display_line( $p_string ) {
	$t_data = event_signal( 'EVENT_DISPLAY_TEXT', $p_string, false );
	return $t_data;
}

/**
 * Prepare a string for display to HTML and add href anchors for URLs, emails,
 * bug references, and cvs references
 * @param string $p_string
 * @return string
 */
function string_display_links( $p_string ) {
	$t_data = event_signal( 'EVENT_DISPLAY_FORMATTED', $p_string, true );
	return $t_data;
}

/**
 * Prepare a single line string for display to HTML and add href anchors for
 * URLs, emails, bug references, and cvs references
 * @param string $p_string
 * @return string
 */
function string_display_line_links( $p_string ) {
	$t_data = event_signal( 'EVENT_DISPLAY_FORMATTED', $p_string, false );
	return $t_data;
}

/**
 * Prepare a string for display in rss
 * @param string
 * @return string
 */
function string_rss_links( $p_string ) {
	# rss can not start with &#160; which spaces will be replaced into by string_display().
	$t_string = trim( $p_string );

	$t_string = event_signal( 'EVENT_DISPLAY_RSS', $t_string );

	# another escaping to escape the special characters created by the generated links
	return string_html_specialchars( $t_string );
}

/**
 * Prepare a string for plain text display in email
 * @param string $p_string
 * @return string
 */
function string_email( $p_string ) {
	return string_strip_hrefs( $p_string );
}

/**
 * Prepare a string for plain text display in email and add URLs for bug
 * links and cvs links
 * @param string
 * @return string
 */
function string_email_links( $p_string ) {
	return event_signal( 'EVENT_DISPLAY_EMAIL', $p_string );
}

# --------------------
# Process a string for display in a textarea box
/**
 * @todo function documentation
 * @param string
 * @return string
 */
function string_textarea( $p_string ) {
	return string_html_specialchars( $p_string );
}

/**
 * Process a string for display in a text box
 * @param string
 * @return string
 */
function string_attribute( $p_string ) {
	return string_html_specialchars( $p_string );
}

/**
 * Process a string for inclusion in a URL as a GET parameter
 * @param string $p_string
 * @return string
 */
function string_url( $p_string ) {
	return rawurlencode( $p_string );
}

/**
 * validate the url as part of this site before continuing
 * @param string $p_url
 * @param bool $p_return_absolute
 * @return string
 */
function string_sanitize_url( $p_url, $p_return_absolute = false ) {
	$t_url = strip_tags( urldecode( $p_url ) );

	$t_path = rtrim( config_get( 'path' ), '/' );
	$t_short_path = rtrim( config_get( 'short_path' ), '/' );

	$t_pattern = '(?:/*(?P<script>[^\?#]*))(?:\?(?P<query>[^#]*))?(?:#(?P<anchor>[^#]*))?';

	# Break the given URL into pieces for path, script, query, and anchor
	$t_type = 0;
	if ( preg_match( '@^(?P<path>' . preg_quote( $t_path, '@' ) . ')' . $t_pattern . '$@', $t_url, $t_matches ) ) {
		$t_type = 1;
	} else if ( !empty( $t_short_path )
			 && preg_match( '@^(?P<path>' . preg_quote( $t_short_path, '@' ) . ')' . $t_pattern . '$@', $t_url, $t_matches )
	) {
		$t_type = 2;
	} else if ( preg_match( '@^(?P<path>)' . $t_pattern . '$@', $t_url, $t_matches ) ) {
		$t_type = 3;
	}

	# Check for URL's pointing to other domains
	if ( 0 == $t_type || empty( $t_matches['script'] ) ||
		3 == $t_type && preg_match( '@(?:[^:]*)?://@', $t_url ) > 0 ) {

		return ( $p_return_absolute ? $t_path . '/' : '' ) . 'index.php';
	}

	# Start extracting regex matches
	$t_script = $t_matches['script'];
	$t_script_path = $t_matches['path'];

	# Clean/encode query params
	$t_query = '';
	if ( isset( $t_matches['query'] ) ) {
		$t_pairs = array();
		parse_str( html_entity_decode( $t_matches['query'] ), $t_pairs );

		$t_clean_pairs = array();
		foreach( $t_pairs as $t_key => $t_value ) {
			if ( is_array( $t_value ) ) {
				foreach( $t_value as $t_value_each ) {
					$t_clean_pairs[] .= rawurlencode( $t_key ) . '[]=' . rawurlencode( $t_value_each );
				}
			} else {
				$t_clean_pairs[] = rawurlencode( $t_key ) . '=' . rawurlencode( $t_value );
			}
		}

		if ( !empty( $t_clean_pairs ) ) {
			$t_query = '?' . join( '&', $t_clean_pairs );
		}
	}

	# encode link anchor
	$t_anchor = '';
	if ( isset( $t_matches['anchor'] ) ) {
		$t_anchor = '#' . rawurlencode( $t_matches['anchor'] );
	}

	# Return an appropriate re-combined URL string
	if ( $p_return_absolute ) {
		return $t_path . '/' . $t_script . $t_query . $t_anchor;
	} else {
		return ( !empty( $t_script_path ) ? $t_script_path . '/' : '' ) . $t_script . $t_query . $t_anchor;
	}
}

/**
 * process the $p_string and convert filenames in the format
 *  cvs:filename.ext or cvs:filename.ext:n.nn to a html link
 * if $p_include_anchor is true, include an <a href="..."> tag,
 *  otherwise, just insert the URL as text
 * @param string $p_string
 * @param bool $p_include_anchor
 * @return string
 */
function string_process_cvs_link( $p_string, $p_include_anchor = true ) {
	$t_cvs_web = config_get( 'cvs_web' );

	if( $p_include_anchor ) {
		$t_replace_with = '[CVS] <a href="' . $t_cvs_web . '\\1?rev=\\4" target="_new">\\1</a>\\5';
	} else {
		$t_replace_with = '[CVS] ' . $t_cvs_web . '\\1?rev=\\4\\5';
	}

	return preg_replace( '/cvs:([^\.\s:,\?!<]+(\.[^\.\s:,\?!<]+)*)(:)?(\d\.[\d\.]+)?([\W\s])?/i', $t_replace_with, $p_string );
}

$string_process_bug_link_callback = array();

/**
 * Process $p_string, looking for bug ID references and creating bug view
 * links for them.
 *
 * Returns the processed string.
 *
 * If $p_include_anchor is true, include the href tag, otherwise just insert
 * the URL
 *
 * The bug tag ('#' by default) must be at the beginning of the string or
 * preceeded by a character that is not a letter, a number or an underscore
 *
 * if $p_include_anchor = false, $p_fqdn is ignored and assumed to true.
 * @param string $p_string
 * @param bool $p_include_anchor
 * @param bool $p_detail_info
 * @param bool $p_fqdn
 * @return string
 */
function string_process_bug_link( $p_string, $p_include_anchor = true, $p_detail_info = true, $p_fqdn = false ) {
	global $string_process_bug_link_callback;

	$t_tag = config_get( 'bug_link_tag' );

	# bail if the link tag is blank
	if( '' == $t_tag || $p_string == '' ) {
		return $p_string;
	}

	if( !isset( $string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] ) ) {
		if( $p_include_anchor ) {
			$string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function( '$p_array', '
										if ( bug_exists( (int)$p_array[2] ) && access_has_bug_level( VIEWER, (int)$p_array[2] ) ) {
											return $p_array[1] . string_get_bug_view_link( (int)$p_array[2], null, ' . ( $p_detail_info ? 'true' : 'false' ) . ', ' . ( $p_fqdn ? 'true' : 'false' ) . ');
										} else {
											return $p_array[0];
										}
										' );
		} else {
			$string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function( '$p_array', '
										# We might as well create the link here even if the bug
										#  doesnt exist.  In the case above we dont want to do
										#  the summary lookup on a non-existant bug.  But here, we
										#  can create the link and by the time it is clicked on, the
										#  bug may exist.
										return $p_array[1] . string_get_bug_view_url_with_fqdn( (int)$p_array[2], null );
										' );
		}
	}

	$p_string = preg_replace_callback( '/(^|[^\w&])' . preg_quote( $t_tag, '/' ) . '(\d+)\b/', $string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn], $p_string );
	return $p_string;
}

$string_process_bugnote_link_callback = array();

/**
 * Process $p_string, looking for bugnote ID references and creating bug view
 * links for them.
 *
 * Returns the processed string.
 *
 * If $p_include_anchor is true, include the href tag, otherwise just insert
 * the URL
 *
 * The bugnote tag ('~' by default) must be at the beginning of the string or
 * preceeded by a character that is not a letter, a number or an underscore
 *
 * if $p_include_anchor = false, $p_fqdn is ignored and assumed to true.
 * @param string $p_string
 * @param bool $p_include_anchor
 * @param bool $p_detail_info
 * @param bool $p_fqdn
 * @return string
 */
function string_process_bugnote_link( $p_string, $p_include_anchor = true, $p_detail_info = true, $p_fqdn = false ) {
	global $string_process_bugnote_link_callback;
	$t_tag = config_get( 'bugnote_link_tag' );

	# bail if the link tag is blank
	if( '' == $t_tag || $p_string == '' ) {
		return $p_string;
	}

	if( !isset( $string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] ) ) {
		if( $p_include_anchor ) {
			$string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] =
				create_function( '$p_array',
					'
					if ( bugnote_exists( (int)$p_array[2] ) ) {
						$t_bug_id = bugnote_get_field( (int)$p_array[2], \'bug_id\' );
						if ( bug_exists( $t_bug_id ) ) {
							$g_project_override = bug_get_field( $t_bug_id, \'project_id\' );
							if (   access_compare_level(
										user_get_access_level( auth_get_current_user_id(),
										bug_get_field( $t_bug_id, \'project_id\' ) ),
										config_get( \'private_bugnote_threshold\' )
								   )
								|| bugnote_get_field( (int)$p_array[2], \'reporter_id\' ) == auth_get_current_user_id()
								|| bugnote_get_field( (int)$p_array[2], \'view_state\' ) == VS_PUBLIC
							) {
								$g_project_override = null;
								return $p_array[1] .
									string_get_bugnote_view_link(
										$t_bug_id,
										(int)$p_array[2],
										null,
										' . ( $p_detail_info ? 'true' : 'false' ) . ', ' . ( $p_fqdn ? 'true' : 'false' ) . '
									);
							}
							$g_project_override = null;
						}
					}
					return $p_array[0];
					'
				);
		} else {
			$string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] =
				create_function(
					'$p_array',
					'
					# We might as well create the link here even if the bug
					#  doesnt exist.  In the case above we dont want to do
					#  the summary lookup on a non-existant bug.  But here, we
					#  can create the link and by the time it is clicked on, the
					#  bug may exist.
					$t_bug_id = bugnote_get_field( (int)$p_array[2], \'bug_id\' );
					if ( bug_exists( $t_bug_id ) ) {
						return $p_array[1] . string_get_bugnote_view_url_with_fqdn( $t_bug_id, (int)$p_array[2], null );
					} else {
						return $p_array[0];
					}
					'
				);
		}
	}
	$p_string = preg_replace_callback( '/(^|[^\w])' . preg_quote( $t_tag, '/' ) . '(\d+)\b/', $string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn], $p_string );
	return $p_string;
}

/**
 * Search email addresses and URLs for a few common protocols in the given
 * string, and replace occurences with href anchors.
 * @param string $p_string
 * @return string
 */
function string_insert_hrefs( $p_string ) {
	static $s_url_regex = null;
	static $s_email_regex = null;
	static $s_anchor_regex = '/(<a[^>]*>.*?<\/a>)/is';

	if( !config_get( 'html_make_links' ) ) {
		return $p_string;
	}

	$t_change_quotes = false;
	if( ini_get_bool( 'magic_quotes_sybase' ) && function_exists( 'ini_set' ) ) {
		$t_change_quotes = true;
		ini_set( 'magic_quotes_sybase', false );
	}

	# Initialize static variables
	if ( is_null( $s_url_regex ) ) {
		# URL protocol. The regex accepts a small subset from the list of valid
		# IANA permanent and provisional schemes defined in
		# http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
		$t_url_protocol = '(?:https?|s?ftp|file|irc[6s]?|ssh|telnet|nntp|git|svn(?:\+ssh)?|cvs):\/\/';

		# %2A notation in url's
		$t_url_hex = '%[[:digit:]A-Fa-f]{2}';

		# valid set of characters that may occur in url scheme. Note: - should be first (A-F != -AF).
		$t_url_valid_chars       = '-_.,!~*\';\/?%^\\\\:@&={\|}+$#[:alnum:]\pL';
		$t_url_chars             = "(?:${t_url_hex}|[${t_url_valid_chars}\(\)\[\]])";
		$t_url_chars2            = "(?:${t_url_hex}|[${t_url_valid_chars}])";
		$t_url_chars_in_brackets = "(?:${t_url_hex}|[${t_url_valid_chars}\(\)])";
		$t_url_chars_in_parens   = "(?:${t_url_hex}|[${t_url_valid_chars}\[\]])";

		$t_url_part1 = "${t_url_chars}";
		$t_url_part2 = "(?:\(${t_url_chars_in_parens}*\)|\[${t_url_chars_in_brackets}*\]|${t_url_chars2})";

		$s_url_regex = "/(${t_url_protocol}(${t_url_part1}*?${t_url_part2}+))/su";

		# e-mail regex
		$s_email_regex = substr_replace( email_regex_simple(), '(?:mailto:)?', 1, 0 );
	}

	# Find any URL in a string and replace it by a clickable link
	$t_function = create_function( '$p_match', '
		$t_url_href = \'href="\' . rtrim( $p_match[1], \'.\' ) . \'"\';
		return "<a ${t_url_href}>${p_match[1]}</a> [<a ${t_url_href} target=\"_blank\">^</a>]";
	' );
	$p_string = preg_replace_callback( $s_url_regex, $t_function, $p_string );
	if( $t_change_quotes ) {
		ini_set( 'magic_quotes_sybase', true );
	}

	# Find any email addresses in the string and replace them with a clickable
	# mailto: link, making sure that we skip processing of any existing anchor
	# tags, to avoid parts of URLs such as https://user@example.com/ or
	# http://user:password@example.com/ to be not treated as an email.
	$t_pieces = preg_split( $s_anchor_regex, $p_string, null, PREG_SPLIT_DELIM_CAPTURE );
	$p_string = '';
	foreach( $t_pieces as $piece ) {
		if( preg_match( $s_anchor_regex, $piece ) ) {
			$p_string .= $piece;
		} else {
			$p_string .= preg_replace( $s_email_regex, '<a href="mailto:\0">\0</a>', $piece );
		}
	}

	return $p_string;
}

/**
 * Detect href anchors in the string and replace them with URLs and email addresses
 * @param string $p_string
 * @return string
 */
function string_strip_hrefs( $p_string ) {
	# First grab mailto: hrefs.  We don't care whether the URL is actually
	# correct - just that it's inside an href attribute.
	$p_string = preg_replace( '/<a\s[^\>]*href="mailto:([^\"]+)"[^\>]*>[^\<]*<\/a>/si', '\1', $p_string );

	# Then grab any other href
	$p_string = preg_replace( '/<a\s[^\>]*href="([^\"]+)"[^\>]*>[^\<]*<\/a>/si', '\1', $p_string );
	return $p_string;
}

/**
 * This function looks for text with htmlentities
 * like &lt;b&gt; and converts is into corresponding
 * html < b > tag based on the configuration presets
 * @param string $p_string
 * @param bool $p_multiline
 * @return string
 */
function string_restore_valid_html_tags( $p_string, $p_multiline = true ) {
	global $g_cache_html_valid_tags_single_line, $g_cache_html_valid_tags;
	$tags = '';
	if( is_blank(( $p_multiline ? $g_cache_html_valid_tags : $g_cache_html_valid_tags_single_line ) ) ) {
		$t_html_valid_tags = config_get( $p_multiline ? 'html_valid_tags' : 'html_valid_tags_single_line' );

		if( OFF === $t_html_valid_tags || is_blank( $t_html_valid_tags ) ) {
			return $p_string;
		}

		$tags = explode( ',', $t_html_valid_tags );
		foreach( $tags as $key => $value ) {
			if( !is_blank( $value ) ) {
				$tags[$key] = trim( $value );
			}
		}
		$tags = implode( '|', $tags );
		if( $p_multiline ) {
			$g_cache_html_valid_tags = $tags;
		} else {
			$g_cache_html_valid_tags_single_line = $tags;
		}
	} else {
		$tags = ( $p_multiline ? $g_cache_html_valid_tags : $g_cache_html_valid_tags_single_line );
	}

	$p_string = preg_replace( '/&lt;(' . $tags . ')\s*&gt;/ui', '<\\1>', $p_string );
	$p_string = preg_replace( '/&lt;\/(' . $tags . ')\s*&gt;/ui', '</\\1>', $p_string );
	$p_string = preg_replace( '/&lt;(' . $tags . ')\s*\/&gt;/ui', '<\\1 />', $p_string );

	return $p_string;
}

/**
 * return the name of a bug page for the user
 * account for the user preference and site override
 * $p_action should be something like 'view', 'update', or 'report'
 * If $p_user_id is null or not specified, use the current user * @param string $p_action
 * @param string $p_action
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_page( $p_action, $p_user_id = null ) {
	if ( $p_action == 'view' ) {
		return 'bug_view_page.php';
	}

	if ( $p_action == 'update' ) {
		return 'bug_update_page.php';
	}

	if ( $p_action == 'report' ) {
		return 'bug_report_page.php';
	}

	trigger_error( ERROR_GENERIC, ERROR );
}

/**
 * return an href anchor that links to a bug VIEW page for the given bug
 * account for the user preference and site override
 * @param int $p_bug_id
 * @param int $p_user_id
 * @param bool $p_detail_info
 * @param bool $p_fqdn
 * @return string
 */
function string_get_bug_view_link( $p_bug_id, $p_user_id = null, $p_detail_info = true, $p_fqdn = false ) {
	if( bug_exists( $p_bug_id ) ) {
		$t_link = '<a href="';
		if( $p_fqdn ) {
			$t_link .= config_get_global( 'path' );
		} else {
			$t_link .= config_get_global( 'short_path' );
		}
		$t_link .= string_get_bug_view_url( $p_bug_id, $p_user_id ) . '"';
		if( $p_detail_info ) {
			$t_summary = string_attribute( bug_get_field( $p_bug_id, 'summary' ) );
			$t_status = string_attribute( get_enum_element( 'status', bug_get_field( $p_bug_id, 'status' ) ) );
			$t_link .= ' title="[' . $t_status . '] ' . $t_summary . '"';

			$t_resolved = bug_get_field( $p_bug_id, 'status' ) >= config_get( 'bug_resolved_status_threshold' );
			if( $t_resolved ) {
				$t_link .= ' class="resolved"';
			}
		}
		$t_link .= '>' . bug_format_id( $p_bug_id ) . '</a>';
	} else {
		$t_link = bug_format_id( $p_bug_id );
	}

	return $t_link;
}

/**
 * return an href anchor that links to a bug VIEW page for the given bug
 * account for the user preference and site override
 * @param int $p_bug_id
 * @param int $p_bugnote_id
 * @param int $p_user_id
 * @param bool $p_detail_info
 * @param bool $p_fqdn
 * @return string
 */
function string_get_bugnote_view_link( $p_bug_id, $p_bugnote_id, $p_user_id = null, $p_detail_info = true, $p_fqdn = false ) {
	$t_bug_id = (int)$p_bug_id;

	if( bug_exists( $t_bug_id ) && bugnote_exists( $p_bugnote_id ) ) {
		$t_link = '<a href="';
		if( $p_fqdn ) {
			$t_link .= config_get_global( 'path' );
		} else {
			$t_link .= config_get_global( 'short_path' );
		}

		$t_link .= string_get_bugnote_view_url( $p_bug_id, $p_bugnote_id, $p_user_id ) . '"';
		if( $p_detail_info ) {
			$t_reporter = string_attribute( user_get_name( bugnote_get_field( $p_bugnote_id, 'reporter_id' ) ) );
			$t_update_date = string_attribute( date( config_get( 'normal_date_format' ), ( bugnote_get_field( $p_bugnote_id, 'last_modified' ) ) ) );
			$t_link .= ' title="' . bug_format_id( $t_bug_id ) . ': [' . $t_update_date . '] ' . $t_reporter . '"';
		}

		$t_link .= '>' . bug_format_id( $t_bug_id ) . ':' . bugnote_format_id( $p_bugnote_id ) . '</a>';
	} else {
		$t_link = bugnote_format_id( $t_bug_id ) . ':' . bugnote_format_id( $p_bugnote_id );
	}

	return $t_link;
}

/**
 * return the name and GET parameters of a bug VIEW page for the given bug
 * @param int $p_bug_id
 * @return string
 */
function string_get_bug_view_url( $p_bug_id ) {
	return 'view.php?id=' . $p_bug_id;
}

/**
 * return the name and GET parameters of a bug VIEW page for the given bug
 * @param int $p_bug_id
 * @param int $p_bugnote_id
 * @return string
 */
function string_get_bugnote_view_url( $p_bug_id, $p_bugnote_id ) {
	return 'view.php?id=' . $p_bug_id . '#c' . $p_bugnote_id;
}

/**
 * return the name and GET parameters of a bug VIEW page for the given bug
 * account for the user preference and site override
 * The returned url includes the fully qualified domain, hence it is suitable to be included
 * in emails.
 * @param int $p_bug_id
 * @param int $p_bugnote_id
 * @param int $p_user_id
 * @return string
 */
function string_get_bugnote_view_url_with_fqdn( $p_bug_id, $p_bugnote_id, $p_user_id = null ) {
	return config_get( 'path' ) . string_get_bug_view_url( $p_bug_id, $p_user_id ) . '#c' . $p_bugnote_id;
}

/**
 * return the name and GET parameters of a bug VIEW page for the given bug
 * account for the user preference and site override
 * The returned url includes the fully qualified domain, hence it is suitable to be included in emails.
 * @param int $p_bug_id
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_view_url_with_fqdn( $p_bug_id, $p_user_id = null ) {
	return config_get( 'path' ) . string_get_bug_view_url( $p_bug_id, $p_user_id );
}

/**
 * return the name of a bug VIEW page for the user
 * account for the user preference and site override
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_view_page( $p_user_id = null ) {
	return string_get_bug_page( 'view', $p_user_id );
}

/**
 * return an href anchor that links to a bug UPDATE page for the given bug
 * account for the user preference and site override
 * @param int $p_bug_id
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_update_link( $p_bug_id, $p_user_id = null ) {
	$t_summary = string_attribute( bug_get_field( $p_bug_id, 'summary' ) );
	return '<a href="' . helper_mantis_url( string_get_bug_update_url( $p_bug_id, $p_user_id ) ) . '" title="' . $t_summary . '">' . bug_format_id( $p_bug_id ) . '</a>';
}

/**
 * return the name and GET parameters of a bug UPDATE page for the given bug
 * account for the user preference and site override
 * @param int $p_bug_id
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_update_url( $p_bug_id, $p_user_id = null ) {
	return string_get_bug_update_page( $p_user_id ) . '?bug_id=' . $p_bug_id;
}

/**
 * return the name of a bug UPDATE page for the user
 * account for the user preference and site override
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_update_page( $p_user_id = null ) {
	return string_get_bug_page( 'update', $p_user_id );
}

/**
 * return an href anchor that links to a bug REPORT page for the given bug
 * account for the user preference and site override
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_report_link( $p_user_id = null ) {
	return '<a href="' . helper_mantis_url( string_get_bug_report_url( $p_user_id ) ) . '">' . lang_get( 'report_bug_link' ) . '</a>';
}

/**
 * return the name and GET parameters of a bug REPORT page for the given bug
 * account for the user preference and site override
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_report_url( $p_user_id = null ) {
	return string_get_bug_report_page( $p_user_id );
}

/**
 * return the name of a bug REPORT page for the user
 * account for the user preference and site override
 * @param int $p_user_id
 * @return string
 */
function string_get_bug_report_page( $p_user_id = null ) {
	return string_get_bug_page( 'report', $p_user_id );
}

/**
 * return the complete url link to checkin using the confirm_hash
 * @param int $p_user_id
 * @param string $p_confirm_hash
 * @return string
 */
function string_get_confirm_hash_url( $p_user_id, $p_confirm_hash ) {
	$t_path = config_get( 'path' );
	return $t_path . "verify.php?id=" . string_url( $p_user_id ) . "&confirm_hash=" . string_url( $p_confirm_hash );
}

/**
 * Format date for display
 * @param int $p_date
 * @return string
 */
function string_format_complete_date( $p_date ) {
	return date( config_get( 'complete_date_format' ), $p_date );
}

/**
 * Shorten a string for display on a dropdown to prevent the page rendering too wide
 * ref issues #4630, #5072, #5131
 * @param string $p_string
 * @param int $p_max
 * @return string
 */
function string_shorten( $p_string, $p_max = null ) {
	if( $p_max === null ) {
		$t_max = config_get( 'max_dropdown_length' );
	} else {
		$t_max = (int) $p_max;
	}

	if( ( $t_max > 0 ) && ( utf8_strlen( $p_string ) > $t_max ) ) {
		$t_pattern = '/([\s|.|,|\-|_|\/|\?]+)/';
		$t_bits = preg_split( $t_pattern, $p_string, -1, PREG_SPLIT_DELIM_CAPTURE );

		$t_string = '';
		$t_last = $t_bits[count( $t_bits ) - 1];
		$t_last_len = strlen( $t_last );

		if( count( $t_bits ) == 1 ) {
			$t_string .= utf8_substr( $t_last, 0, $t_max - 3 );
			$t_string .= '...';
		} else {
			foreach( $t_bits as $t_bit ) {
				if(( utf8_strlen( $t_string ) + utf8_strlen( $t_bit ) + $t_last_len + 3 <= $t_max ) || ( strpos( $t_bit, '.,-/?' ) > 0 ) ) {
					$t_string .= $t_bit;
				} else {
					break;
				}
			}
			$t_string .= '...' . $t_last;
		}
		return $t_string;
	} else {
		return $p_string;
	}
}

/**
 * Normalize a string by removing leading, trailing and excessive internal spaces
 * note a space is used as the pattern instead of '\s' to make it work with UTF-8 strings
 * @param string $p_string
 * @return string
 */
function string_normalize( $p_string ) {
	return preg_replace( '/ +/', ' ', trim( $p_string ) );
}

/**
 * remap a field name to a string name (for sort filter)
 * @param string $p_string
 * @return string
 */
function string_get_field_name( $p_string ) {
	$t_map = array(
		'attachment_count' => 'attachments',
		'category_id' => 'category',
		'handler_id' => 'assigned_to',
		'id' => 'email_bug',
		'last_updated' => 'updated',
		'project_id' => 'email_project',
		'reporter_id' => 'reporter',
		'view_state' => 'view_status',
	);

	$t_string = $p_string;
	if( isset( $t_map[$p_string] ) ) {
		$t_string = $t_map[$p_string];
	}
	return lang_get_defaulted( $t_string );
}

/**
 * Calls htmlentities on the specified string, passing along
 * the current charset.
 * @param string $p_string
 * @return string
 */
function string_html_entities( $p_string ) {
	return htmlentities( $p_string, ENT_COMPAT, 'utf-8' );
}

/**
 * Calls htmlspecialchars on the specified string, handling utf8
 * @param string $p_string
 * @return string
 */
function string_html_specialchars( $p_string ) {
	# achumakov: @ added to avoid warning output in unsupported codepages
	# e.g. 8859-2, windows-1257, Korean, which are treated as 8859-1.
	# This is VERY important for Eastern European, Baltic and Korean languages
	return preg_replace( "/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", @htmlspecialchars( $p_string, ENT_COMPAT, 'utf-8' ) );
}

/**
 * Prepares a string to be used as part of header().
 * @param string $p_string
 * @return string
 */
function string_prepare_header( $p_string ) {
	$t_string= explode( "\n", $p_string, 2 );
	$t_string= explode( "\r", $t_string[0], 2 );
	return $t_string[0];
}

/**
 * Checks the supplied string for scripting characters, if it contains any, then return true, otherwise return false.
 * @param string $p_string
 * @return bool
 */
function string_contains_scripting_chars( $p_string ) {
	if(( strstr( $p_string, '<' ) !== false ) || ( strstr( $p_string, '>' ) !== false ) ) {
		return true;
	}

	return false;
}
