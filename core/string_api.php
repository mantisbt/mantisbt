<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: string_api.php,v 1.92.2.1 2007-10-13 22:35:43 giallu Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'user_pref_api.php' );

	### String Processing API ###

	### --------------------
	# Preserve spaces at beginning of lines.
	# Lines must be separated by \n rather than <br />
	function string_preserve_spaces_at_bol( $p_string ) {
		$lines = explode( "\n", $p_string );
		$line_count = count( $lines );
		for ( $i = 0; $i < $line_count; $i++ ) {
			$count	= 0;
			$prefix	= '';

			$t_char = substr( $lines[$i], $count, 1 );
			$spaces = 0;
			while ( ( $t_char  == ' ' ) || ( $t_char == "\t" ) ) {
				if ( $t_char == ' ' )
					$spaces++;
				else
					$spaces += 4; // 1 tab = 4 spaces, can be configurable.

				$count++;
				$t_char = substr( $lines[$i], $count, 1 );
			}

			for ( $j = 0; $j < $spaces; $j++ ) {
				$prefix .= '&nbsp;';
			}

			$lines[$i] = $prefix . substr( $lines[$i], $count );
		}
		return implode( "\n", $lines );
	}
	# --------------------
	# Prepare a string to be printed without being broken into multiple lines
	function string_no_break( $p_string ) {
		if ( strpos( $p_string, ' ' ) !== false ) {
			return '<span class="nowrap">' . $p_string . "</span>";
		} else {
			return $p_string;
		}
	}

	# --------------------
	# Similar to nl2br, but fixes up a problem where new lines are doubled between
	# <pre> tags.
	# additionally, wrap the text an $p_wrap character intervals if the config is set
	function string_nl2br( $p_string, $p_wrap = 100 ) {
		$output = '';
		$pieces = preg_split('/(<pre[^>]*>.*?<\/pre>)/is', $p_string, -1, PREG_SPLIT_DELIM_CAPTURE); 	
		if(isset($pieces[1])) 
		{
			foreach($pieces as $piece)
			{
				if(preg_match('/(<pre[^>]*>.*?<\/pre>)/is', $piece)) 
				{
					$piece = preg_replace("/<br[^>]*?>/", "", $piece);
					# @@@ thraxisp - this may want to be replaced by html_entity_decode (or equivalent)
					#     if other encoded characters are a problem
					$piece = preg_replace("/&nbsp;/", " ", $piece);
					if ( ON == config_get( 'wrap_in_preformatted_text' ) ) {
						$output .= preg_replace('/([^\n]{'.$p_wrap.'})(?!<\/pre>)/', "$1\n", $piece);
					} else {
						$output .= $piece;
					}
				} else {
					$output .= nl2br($piece);
				}
			}
			return $output;
		} else {
			return nl2br($p_string);
		}
	}

	# --------------------
	# Prepare a multiple line string for display to HTML
	function string_display( $p_string ) {	
		$p_string = string_strip_hrefs( $p_string );
		$p_string = string_html_specialchars( $p_string );
		$p_string = string_restore_valid_html_tags( $p_string, /* multiline = */ true );
		$p_string = string_preserve_spaces_at_bol( $p_string );
		$p_string = string_nl2br( $p_string );

		return $p_string;
	}

	# --------------------
	# Prepare a single line string for display to HTML
	function string_display_line( $p_string ) {
		$p_string = string_strip_hrefs( $p_string );
		$p_string = string_html_specialchars( $p_string );
		$p_string = string_restore_valid_html_tags( $p_string, /* multiline = */ false );

		return $p_string;
	}

	# --------------------
	# Prepare a string for display to HTML and add href anchors for URLs, emails,
	#  bug references, and cvs references
	function string_display_links( $p_string ) {
		$p_string = string_display( $p_string );
		$p_string = string_insert_hrefs( $p_string );
		$p_string = string_process_bug_link( $p_string );
		$p_string = string_process_bugnote_link( $p_string );
		$p_string = string_process_cvs_link( $p_string );

		return $p_string;
	}

	# --------------------
	# Prepare a single line string for display to HTML and add href anchors for 
	# URLs, emails, bug references, and cvs references
	function string_display_line_links( $p_string ) {
		$p_string = string_display_line( $p_string );
		$p_string = string_insert_hrefs( $p_string );
		$p_string = string_process_bug_link( $p_string );
		$p_string = string_process_bugnote_link( $p_string );
		$p_string = string_process_cvs_link( $p_string );

		return $p_string;
	}

	# --------------------
	# Prepare a string for display in rss
	function string_rss_links( $p_string ) {
		# rss can not start with &nbsp; which spaces will be replaced into by string_display().
		$t_string = trim( $p_string );

		# same steps as string_display_links() without the preservation of spaces since &nbsp; is undefined in XML.
		$t_string = string_strip_hrefs( $t_string );
		$t_string = string_html_specialchars( $t_string );
		$t_string = string_restore_valid_html_tags( $t_string );
		$t_string = string_nl2br( $t_string );
		$t_string = string_insert_hrefs( $t_string );
		$t_string = string_process_bug_link( $t_string, /* anchor */ true, /* detailInfo */ false, /* fqdn */ true );
		$t_string = string_process_bugnote_link( $t_string, /* anchor */ true, /* detailInfo */ false, /* fqdn */ true );
		$t_string = string_process_cvs_link( $t_string );

		# another escaping to escape the special characters created by the generated links
		$t_string = string_html_specialchars( $t_string );

		return $t_string;
	}

	# --------------------
	# Prepare a string for plain text display in email
	function string_email( $p_string ) {
		$p_string = string_strip_hrefs( $p_string );

		return $p_string;
	}

	# --------------------
	# Prepare a string for plain text display in email and add URLs for bug
	#  links and cvs links
	function string_email_links( $p_string ) {
		$p_string = string_email( $p_string );
		$p_string = string_process_bug_link( $p_string, false );
		$p_string = string_process_bugnote_link( $p_string, false );
		$p_string = string_process_cvs_link( $p_string, false );

		return $p_string;
	}



	# --------------------
	# Process a string for display in a textarea box
	function string_textarea( $p_string ) {
		$p_string = string_html_specialchars( $p_string );

		return $p_string;
	}

	# --------------------
	# Process a string for display in a text box
	function string_attribute( $p_string ) {
		$p_string = string_html_specialchars( $p_string );

		return $p_string;
	}

	# --------------------
	# Process a string for inclusion in a URL as a GET parameter
	function string_url( $p_string ) {
		$p_string = rawurlencode( $p_string );

		return $p_string;
	}

	# --------------------
	# validate the url as part of this site before continuing
	function string_sanitize_url( $p_url ) {
		$t_url = strip_tags( urldecode( $p_url ) );
		if ( preg_match( '?http(s)*://?', $t_url ) > 0 ) { 
			// no embedded addresses
			if ( preg_match( '?^' . config_get( 'path' ) . '?', $t_url ) == 0 ) { 
				// url is ok if it begins with our path, if not, replace it
				$t_url = 'index.php';
			}
		}
		if ( $t_url == '' ) {
			$t_url = 'index.php';
		}
		
		// split and encode parameters
		if ( strpos( $t_url, '?' ) !== FALSE ) {
			list( $t_path, $t_param ) = split( '\?', $t_url, 2 );
			if ( !is_blank($t_param ) ) {
				$t_vals = array();
				parse_str( html_entity_decode( $t_param ), $t_vals );
				$t_param = '';
				foreach( $t_vals as $k => $v ) {
					if ( $t_param != '' ) {
						$t_param .= '&amp;'; 
					}
					if ( is_array( $v ) ) {
						for ( $i = 0, $t_size = sizeof( $v ); $i < $t_size; $i++ ) {
							$t_param .= $k . urlencode( '[]' ) . '=' . urlencode( strip_tags( urldecode( $v[$i] ) ) );
							$t_param .= ( $i != $t_size - 1 ) ? '&amp;' : '';
						}
					} else {
						$t_param .= "$k=" . urlencode( strip_tags( urldecode( $v ) ) );
					}
				}
				return $t_path . '?' . $t_param;
			} else {
				return $t_path;
			}
		} else {
			return $t_url;
		}
	}
	
	# --------------------
	# process the $p_string and convert filenames in the format
	#  cvs:filename.ext or cvs:filename.ext:n.nn to a html link
	# if $p_include_anchor is true, include an <a href="..."> tag,
	#  otherwise, just insert the URL as text
	function string_process_cvs_link( $p_string, $p_include_anchor=true ) {
		$t_cvs_web = config_get( 'cvs_web' );

		if ( $p_include_anchor ) {
			$t_replace_with = '[CVS] <a href="'.$t_cvs_web.'\\1?rev=\\4" target="_new">\\1</a>\\5';
		} else {
			$t_replace_with = '[CVS] '.$t_cvs_web.'\\1?rev=\\4\\5';
		}

		return preg_replace( '/cvs:([^\.\s:,\?!<]+(\.[^\.\s:,\?!<]+)*)(:)?(\d\.[\d\.]+)?([\W\s])?/i',
							 $t_replace_with,
							 $p_string );
	}

	# --------------------
	# Process $p_string, looking for bug ID references and creating bug view
	#  links for them.
	#
	# Returns the processed string.
	#
	# If $p_include_anchor is true, include the href tag, otherwise just insert
	#  the URL
	#
	# The bug tag ('#' by default) must be at the beginning of the string or
	#  preceeded by a character that is not a letter, a number or an underscore
	#
	# if $p_include_anchor = false, $p_fqdn is ignored and assumed to true.
	$string_process_bug_link_callback = array();

	function string_process_bug_link( $p_string, $p_include_anchor = true, $p_detail_info = true, $p_fqdn = false ) {
		global $string_process_bug_link_callback;

		$t_tag = config_get( 'bug_link_tag' );
		# bail if the link tag is blank
		if ( '' == $t_tag || $p_string == '' ) {
			return $p_string;
		}

		if ( !isset( $string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] ) ) {
			if ($p_include_anchor) {
				$string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function('$p_array','
										if ( bug_exists( (int)$p_array[2] ) && access_has_bug_level( VIEWER, (int)$p_array[2] ) ) {
											return $p_array[1] . string_get_bug_view_link( (int)$p_array[2], null, ' . ($p_detail_info ? 'true' : 'false') . ', ' . ($p_fqdn ? 'true' : 'false') . ');
										} else {    	
											return $p_array[0];
										}
										');
			} else {
				$string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function('$p_array','
										# We might as well create the link here even if the bug
										#  doesnt exist.  In the case above we dont want to do
										#  the summary lookup on a non-existant bug.  But here, we
										#  can create the link and by the time it is clicked on, the
										#  bug may exist.			
										return $p_array[1] . string_get_bug_view_url_with_fqdn( (int)$p_array[2], null );
										');
			}
		}

		$p_string = preg_replace_callback( '/(^|[^\w&])' . preg_quote($t_tag, '/') . '(\d+)\b/', $string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn], $p_string);
		return $p_string;
	}

	# --------------------
	# Process $p_string, looking for bugnote ID references and creating bug view
	#  links for them.
	#
	# Returns the processed string.
	#
	# If $p_include_anchor is true, include the href tag, otherwise just insert
	#  the URL
	#
	# The bugnote tag ('~' by default) must be at the beginning of the string or
	#  preceeded by a character that is not a letter, a number or an underscore
	#
	# if $p_include_anchor = false, $p_fqdn is ignored and assumed to true.
	$string_process_bugnote_link_callback = array();

	function string_process_bugnote_link( $p_string, $p_include_anchor = true, $p_detail_info = true, $p_fqdn = false ) {
		global $string_process_bugnote_link_callback;
		$t_tag = config_get( 'bugnote_link_tag' );

		# bail if the link tag is blank
		if ( '' == $t_tag || $p_string == '' ) {
			return $p_string;
		}

		if ( !isset ( $string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] ) ) {
			if ($p_include_anchor) {
				$string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function('$p_array','
										if ( bugnote_exists( (int)$p_array[2] ) ) {
											$t_bug_id = bugnote_get_field( (int)$p_array[2], \'bug_id\' );
											if ( bug_exists( $t_bug_id ) ) {
												return $p_array[1] . string_get_bugnote_view_link( $t_bug_id, (int)$p_array[2], null, ' . ($p_detail_info ? 'true' : 'false') . ', ' . ($p_fqdn ? 'true' : 'false') . ' );
											} else {
												return $p_array[0];
											}
										} else {
											return $p_array[0];
										}
										');
			} else {
				$string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function('$p_array','
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
										');
			}
		}
		$p_string = preg_replace_callback( '/(^|[^\w])' . preg_quote($t_tag, '/') .'(\d+)\b/', $string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn], $p_string);
		return $p_string;
	}

	#===================================
	# Tag Processing
	#===================================

	# --------------------
	# Detect URLs and email addresses in the string and replace them with href anchors
	function string_insert_hrefs( $p_string ) {
		if ( !config_get( 'html_make_links' ) ) {
			return $p_string;
		}

		$t_change_quotes = false;
		if( ini_get_bool( 'magic_quotes_sybase' ) ) {
			$t_change_quotes = true;
			ini_set( 'magic_quotes_sybase', false );
		}

		# Find any URL in a string and replace it by a clickable link
		$p_string = preg_replace( '/(([[:alpha:]][-+.[:alnum:]]*):\/\/(%[[:digit:]A-Fa-f]{2}|[-_.!~*\';\/?%^\\\\:@&={\|}+$#\(\),\[\][:alnum:]])+)/se',
                                                                 "'<a href=\"'.rtrim('\\1','.').'\">\\1</a> [<a href=\"'.rtrim('\\1','.').'\" target=\"_blank\">^</a>]'",
                                                                 $p_string);
		if( $t_change_quotes ) {
			ini_set( 'magic_quotes_sybase', true );
		}

		$p_string = preg_replace( '/\b' . email_regex_simple() . '\b/i',
								'<a href="mailto:\0">\0</a>',
								$p_string);

		return $p_string;
	}

	# --------------------
	# Detect href anchors in the string and replace them with URLs and email addresses
	function string_strip_hrefs( $p_string ) {
		# First grab mailto: hrefs.  We don't care whether the URL is actually
		# correct - just that it's inside an href attribute.
		$p_string = preg_replace( '/<a\s[^\>]*href="mailto:([^\"]+)"[^\>]*>[^\<]*<\/a>/si',
								'\1',
								$p_string);

		# Then grab any other href
		$p_string = preg_replace( '/<a\s[^\>]*href="([^\"]+)"[^\>]*>[^\<]*<\/a>/si',
								'\1',
								$p_string);
		return $p_string;
	}

	# --------------------
	# This function looks for text with htmlentities
	# like &lt;b&gt; and converts is into corresponding
	# html <b> based on the configuration presets
	function string_restore_valid_html_tags( $p_string, $p_multiline = true ) {
		$t_html_valid_tags = config_get( $p_multiline ? 'html_valid_tags' : 'html_valid_tags_single_line' );

		if ( OFF === $t_html_valid_tags || is_blank( $t_html_valid_tags ) ) {
			return $p_string;
		}

		$tags = explode( ',', $t_html_valid_tags );
		foreach ($tags as $key => $value) { 
           if ( !is_blank( $value ) ) {
           	$tags[$key] = trim($value); 
           }
          }
        $tags = implode( '|', $tags);

		$p_string = eregi_replace( '&lt;(' . $tags . ')[[:space:]]*&gt;', '<\\1>', $p_string );
		$p_string = eregi_replace( '&lt;\/(' .$tags . ')[[:space:]]*&gt;', '</\\1>', $p_string );
		$p_string = eregi_replace( '&lt;(' . $tags . ')[[:space:]]*\/&gt;', '<\\1 />', $p_string );

		return $p_string;
	}


	#===================================
	# Advanced/Simple page selection
	#===================================

	# --------------------
	# return the name of a bug page for the user
	#  account for the user preference and site override
	#
	# $p_action should be something like 'view', 'update', or 'report'
	# If $p_user_id is null or not specified, use the current user
	function string_get_bug_page( $p_action, $p_user_id=null ) {
		if ( null === $p_user_id ) {
			if ( auth_is_user_authenticated() ) {
				$p_user_id = auth_get_current_user_id();
			}
		}

		$g_show_action = config_get( 'show_' . $p_action );
		switch ( $g_show_action ) {
			case BOTH:
					if ( ( null !== $p_user_id ) &&
						 ( ON == user_pref_get_pref( $p_user_id, 'advanced_' . $p_action ) ) ) {
						return 'bug_' . $p_action . '_advanced_page.php';
					} else {
						return 'bug_' . $p_action . '_page.php';
					}
			case SIMPLE_ONLY:
					return 'bug_' . $p_action . '_page.php';
			case ADVANCED_ONLY:
					return 'bug_' . $p_action . '_advanced_page.php';
		}
	}

	# --------------------
	# return an href anchor that links to a bug VIEW page for the given bug
	#  account for the user preference and site override
	function string_get_bug_view_link( $p_bug_id, $p_user_id = null, $p_detail_info = true, $p_fqdn = false ) {
		if ( bug_exists( $p_bug_id ) ) {
			$t_link = '<a href="';
			if ( $p_fqdn ) {
				$t_link .= config_get( 'path' );
			}
			$t_link .= string_get_bug_view_url( $p_bug_id, $p_user_id ) . '"';
			if ( $p_detail_info ) {
				$t_summary = string_attribute( bug_get_field( $p_bug_id, 'summary' ) );
				$t_status = string_attribute( get_enum_element( 'status', bug_get_field( $p_bug_id, 'status' ) ) );
				$t_link .=  ' title="[' . $t_status . '] ' . $t_summary . '"';
			}
			$t_link .= '>' . bug_format_id( $p_bug_id ) . '</a>';
		} else {
			$t_link = bug_format_id( $p_bug_id );
		}

		return $t_link;
	}

	# --------------------
	# return an href anchor that links to a bug VIEW page for the given bug
	#  account for the user preference and site override
	function string_get_bugnote_view_link( $p_bug_id, $p_bugnote_id, $p_user_id = null, $p_detail_info = true, $p_fqdn = false ) {
		if ( bug_exists( $p_bug_id ) && bugnote_exists( $p_bugnote_id ) ) {
			$t_link = '<a href="';
			if ( $p_fqdn ) {
				$t_link .= config_get( 'path' );
			}

			$t_link .= string_get_bugnote_view_url( $p_bug_id, $p_bugnote_id, $p_user_id ) . '"';
			if ( $p_detail_info ) {
				$t_reporter = string_attribute( user_get_name ( bugnote_get_field( $p_bugnote_id, 'reporter_id' ) ) );
				$t_update_date = string_attribute( date( config_get( 'normal_date_format' ), ( db_unixtimestamp( bugnote_get_field( $p_bugnote_id, 'last_modified' ) ) ) ) );
				$t_link .=  ' title="[' . $t_update_date . '] ' . $t_reporter . '"';
			}
			$t_link .= '>' . lang_get( 'bugnote' ) . ': ' . bugnote_format_id( $p_bugnote_id) . '</a>';
		} else {
			$t_link = lang_get( 'bugnote' ) . ': ' . bugnote_format_id( $p_bugnote_id);
		}

		return $t_link;
	}

	# --------------------
	# return the name and GET parameters of a bug VIEW page for the given bug
	#  account for the user preference and site override
	function string_get_bug_view_url( $p_bug_id, $p_user_id = null ) {
		return 'view.php?id=' . $p_bug_id;
	}

	# --------------------
	# return the name and GET parameters of a bug VIEW page for the given bug
	#  account for the user preference and site override
	function string_get_bugnote_view_url( $p_bug_id, $p_bugnote_id, $p_user_id = null ) {
		return 'view.php?id=' . $p_bug_id . '#c'. $p_bugnote_id;
	}

	# --------------------
	# return the name and GET parameters of a bug VIEW page for the given bug
	#  account for the user preference and site override
	# The returned url includes the fully qualified domain, hence it is suitable to be included
	# in emails.
	function string_get_bugnote_view_url_with_fqdn( $p_bug_id, $p_bugnote_id, $p_user_id = null ) {
		return config_get( 'path' ) . string_get_bug_view_url( $p_bug_id, $p_user_id ).'#c'.$p_bugnote_id;
	}

	# --------------------
	# return the name and GET parameters of a bug VIEW page for the given bug
	#  account for the user preference and site override
	# The returned url includes the fully qualified domain, hence it is suitable to be included
	# in emails.
	function string_get_bug_view_url_with_fqdn( $p_bug_id, $p_user_id = null ) {
		return config_get( 'path' ) . string_get_bug_view_url( $p_bug_id, $p_user_id );
	}

	# --------------------
	# return the name of a bug VIEW page for the user
	#  account for the user preference and site override
	function string_get_bug_view_page( $p_user_id = null ) {
		return string_get_bug_page( 'view', $p_user_id );
	}

	# --------------------
	# return an href anchor that links to a bug UPDATE page for the given bug
	#  account for the user preference and site override
	function string_get_bug_update_link( $p_bug_id, $p_user_id = null ) {
		$t_summary = string_attribute( bug_get_field( $p_bug_id, 'summary' ) );
		return '<a href="' . string_get_bug_update_url( $p_bug_id, $p_user_id ) . '" title="' . $t_summary . '">' . bug_format_id( $p_bug_id ) . '</a>';
	}

	# --------------------
	# return the name and GET parameters of a bug UPDATE page for the given bug
	#  account for the user preference and site override
	function string_get_bug_update_url( $p_bug_id, $p_user_id = null ) {
		return string_get_bug_update_page( $p_user_id ) . '?bug_id=' . $p_bug_id;
	}

	# --------------------
	# return the name of a bug UPDATE page for the user
	#  account for the user preference and site override
	function string_get_bug_update_page( $p_user_id = null ) {
		return string_get_bug_page( 'update', $p_user_id );
	}

	# --------------------
	# return an href anchor that links to a bug REPORT page for the given bug
	#  account for the user preference and site override
	function string_get_bug_report_link( $p_user_id = null ) {
		return '<a href="' . string_get_bug_report_url( $p_user_id ) . '">' . lang_get( 'report_bug_link' ) . '</a>';
	}

	# --------------------
	# return the name and GET parameters of a bug REPORT page for the given bug
	#  account for the user preference and site override
	function string_get_bug_report_url( $p_user_id = null ) {
		return string_get_bug_report_page( $p_user_id );
	}

	# --------------------
	# return the name of a bug REPORT page for the user
	#  account for the user preference and site override
	function string_get_bug_report_page( $p_user_id = null ) {
		return string_get_bug_page( 'report', $p_user_id );
	}

	# --------------------
	# return the complete url link to checkin using the confirm_hash
	function string_get_confirm_hash_url( $p_user_id, $p_confirm_hash ) {
		$t_path = config_get( 'path' );
		return $t_path . "verify.php?id=" . string_url( $p_user_id ) . "&confirm_hash=" . string_url( $p_confirm_hash );
	}

	# --------------------
	# Return a string with the $p_character pattern repeated N times.
	# $p_character - pattern to repeat
	# $p_repeats - number of times to repeat.
	function string_repeat_char( $p_character, $p_repeats ) {
		return str_pad( '', $p_repeats, $p_character );
	}

	# --------------------
	# Format date for display
	function string_format_complete_date( $p_date ) {
		return date( config_get( 'complete_date_format' ), $p_date );
	}

	# --------------------
	# Shorten a string for display on a dropdown to prevent the page rendering too wide
	#  ref issues #4630, #5072, #5131

	function string_shorten( $p_string ) {
		$t_max = config_get( 'max_dropdown_length' );
		if ( ( strlen( $p_string ) > $t_max ) && ( $t_max > 0 ) ){
			$t_pattern = '/([\s|.|,|\-|_|\/|\?]+)/';
			$t_bits = preg_split( $t_pattern, $p_string, -1, PREG_SPLIT_DELIM_CAPTURE );

			$t_string = '';
			$t_last = $t_bits[ count( $t_bits ) - 1 ];
			$t_last_len = strlen( $t_last );

			foreach ( $t_bits as $t_bit ) {
				if ( ( strlen( $t_string ) + strlen( $t_bit ) + $t_last_len + 3 <= $t_max )
					|| ( strpos( $t_bit, '.,-/?' ) > 0 ) ) {
					$t_string .= $t_bit;
				} else {
					break;
				}
			}
			$t_string .= '...' . $t_last;
			return $t_string;
		} else {
			return $p_string;
		}
	}

	# --------------------
	# remap a field name to a string name (for sort filter)

	function string_get_field_name( $p_string ) {

		$t_map = array(
				'last_updated' => 'last_update',
				'id' => 'email_bug'
				);

		$t_string = $p_string;
		if ( isset( $t_map[ $p_string ] ) ) {
			$t_string = $t_map[ $p_string ];
		}
		return lang_get_defaulted( $t_string );
	}

	# --------------------
	# Calls htmlentities on the specified string, passing along
	# the current charset.
	function string_html_entities( $p_string ) {
		return htmlentities( $p_string, ENT_COMPAT, lang_get( 'charset' ) );
	}

	# --------------------
	# Calls htmlspecialchars on the specified string, passing along
	# the current charset, if the current PHP version supports it.
	function string_html_specialchars( $p_string ) {
		# achumakov: @ added to avoid warning output in unsupported codepages
		# e.g. 8859-2, windows-1257, Korean, which are treated as 8859-1.
		# This is VERY important for Eastern European, Baltic and Korean languages
		return preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", @htmlspecialchars( $p_string, ENT_COMPAT, lang_get( 'charset' ) ) );
	}
	
	# --------------------
	# Prepares a string to be used as part of header().
	function string_prepare_header( $p_string ) {
		$t_string = $p_string;

		$t_truncate_pos = strpos($p_string, "\n");
		if ($t_truncate_pos !== false ) {
			$t_string = substr($t_string, 0, $t_truncate_pos);
		}

		$t_truncate_pos = strpos($p_string, "\r");
		if ($t_truncate_pos !== false ) {
			$t_string = substr($t_string, 0, $t_truncate_pos);
		}

		return $t_string;
	}

	# --------------------
	# Checks the supplied string for scripting characters, if it contains any, then return true, otherwise return false.
	function string_contains_scripting_chars( $p_string ) {
		if ( ( strstr( $p_string, '<' ) !== false ) || ( strstr( $p_string, '>' ) !== false ) ) {
			return true;
		}

		return false;
	}
?>
