<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: string_api.php,v 1.43 2004-02-22 04:41:46 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'user_pref_api.php' );

	###########################################################################
	# String Processing API
	###########################################################################

	### --------------------
	# Preserve spaces at beginning of lines.
	# Lines must be separated by \n rather than <br />
	function string_preserve_spaces_at_bol( $p_string ) {
		$lines = explode("\n", $p_string);
		for ( $i = 0; $i < count( $lines ); $i++ ) {
			$count = 0;
			$prefix = '';
			while ( substr($lines[$i], $count, 1) == ' ' ) {
			  $count++;
			}
			for ($j = 0; $j < $count; $j++) {
			  $prefix .= '&nbsp;';
			}
			$lines[$i] = $prefix . substr( $lines[$i], $count );

		}
		$result = implode( "\n", $lines );
		return $result;
	}
	# --------------------
	# Prepare a string for display to HTML
	function string_display( $p_string ) {
		$p_string = string_strip_hrefs( $p_string );
		$p_string = htmlspecialchars( $p_string );
		$p_string = string_restore_valid_html_tags( $p_string );
		$p_string = string_preserve_spaces_at_bol( $p_string );
		$p_string = nl2br( $p_string );

		return $p_string;
	}
	
	# --------------------
	# Prepare a string for display to HTML and add href anchors for URLs, emails,
	#  bug references, and cvs references
	function string_display_links( $p_string ) {
		$p_string = string_display( $p_string );

		$p_string = string_insert_hrefs( $p_string );
		$p_string = string_process_bug_link( $p_string );
		$p_string = string_process_cvs_link( $p_string );
		
		return $p_string;
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
		$p_string = string_process_cvs_link( $p_string, false );
		
		return $p_string;
	}

	# --------------------
	# Process a string for display in a textarea box
	function string_textarea( $p_string ) {
		$p_string = htmlspecialchars( $p_string );
		return $p_string;
	}

	# --------------------
	# Process a string for display in a text box
	function string_attribute( $p_string ) {
		$p_string = htmlspecialchars( $p_string );
		return $p_string;
	}

	# --------------------
	# Process a string for inclusion in a URL as a GET parameter
	function string_url( $p_string ) {
		$p_string = rawurlencode( $p_string );
		return $p_string;
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

		return preg_replace( '/cvs:([^\.\s:,\?!]+(\.[^\.\s:,\?!]+)*)(:)?(\d\.[\d\.]+)?([\W\s])?/i',
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
	function string_process_bug_link( $p_string, $p_include_anchor=true ) {
		$t_tag = config_get( 'bug_link_tag' );
		$t_path = config_get( 'path' );

		preg_match_all( '/(^|.+?)(?:(?<=^|\W)' . preg_quote($t_tag) . '(\d+)|$)/s',
								$p_string, $t_matches, PREG_SET_ORDER );
		
		$t_result = '';

		if ( $p_include_anchor ) {
			foreach ( $t_matches as $t_match ) {
				$t_result .= $t_match[1];

				if ( isset( $t_match[2] ) ) {
					$t_bug_id = $t_match[2];
					if ( bug_exists( $t_bug_id ) ) {
						$t_result .= string_get_bug_view_link( $t_bug_id, null );
					} else {
						$t_result .= $t_bug_id;
					}
				}
			}
		} else {
			foreach ( $t_matches as $t_match ) {
				$t_result .= $t_match[1];
				
				if ( isset( $t_match[2] ) ) {
					$t_bug_id = $t_match[2];
					# We might as well create the link here even if the bug
					#  doesn't exist.  In the case above we don't want to do
					#  the summary lookup on a non-existant bug.  But here, we
					#  can create the link and by the time it is clicked on, the
					#  bug may exist.

					$t_result .= string_get_bug_view_url_with_fqdn( $t_bug_id, null );
				}
			}
		}

		return $t_result;
	}

	#===================================
	# Tag Processing
	#===================================

	# --------------------
	# Detect URLs and email addresses in the string and replace them with href anchors
	function string_insert_hrefs( $p_string ) {
		if ( ! config_get( 'html_make_links' ) ) {
			return $p_string;
		}

		# This is based on the description in RFC 2396 which specifies how
		#  to match URLs generically without knowing their type
		$p_string = preg_replace( '/(([[:alpha:]][-+.[:alnum:]]*):\/\/(%[[:digit:]A-Fa-f]{2}|[-_.!~*\';\/?:@&=+$,[:alnum:]])+)/s',
								'<a href="\1">\1</a>',
								$p_string);

		# Set up a simple subset of RFC 822 email address parsing
		#  We don't allow domain literals or quoted strings
		#  We also don't allow the & character in domains even though the RFC
		#  appears to do so.  This was to prevent &gt; etc from being included.
		#  Note: we could use email_get_rfc822_regex() but it doesn't work well
		#  when applied to data that has already had entities inserted.
		$t_atom = '(?:[^()<>@,;:\\\".\[\]\000-\037\177 &]+)';

		# In order to avoid selecting URLs containing @ characters as email
		#  addresses we limit our selection to addresses that are preceded by:
		#  * the beginning of the string
		#  * a &lt; entity (allowing '<foo@bar.baz>')
		#  * whitespace
		#  * a : (allowing 'send email to:foo@bar.baz')
		#  * a \n, \r, or > (because newlines have been replaced with <br />
		#    and > isn't valid in URLs anyway
		#
		# At the end of the string we allow the opposite:
		#  * the end of the string
		#  * a &gt; entity
		#  * whitespace
		#  * a , character (allowing 'email foo@bar.baz, or ...')
		#  * a \n, \r, or <
		$p_string = preg_replace( '/(?<=^|&lt;|[\s\:\>\n\r])('.$t_atom.'(?:\.'.$t_atom.')*\@'.$t_atom.'(?:\.'.$t_atom.')*)(?=$|&gt;|[\s\,\<\n\r])/s',
								'<a href="mailto:\1" target="_new">\1</a>',
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
	function string_restore_valid_html_tags( $p_string ) {
		$t_html_valid_tags = config_get( 'html_valid_tags' );

		if ( OFF === $t_html_valid_tags ||
			 is_blank( $t_html_valid_tags ) ) {
			return $p_string;
		}

		$tags = explode( ',', $t_html_valid_tags );

		foreach ( $tags as $tag ) {
			if ( !is_blank( $tag ) ) {
				$tag = trim( $tag );
				$p_string = eregi_replace( "&lt;($tag)[[:space:]]*&gt;", "<\\1>", $p_string );
				$p_string = eregi_replace( "&lt;\/($tag)[[:space:]]*&gt;", "</\\1>", $p_string );
				$p_string = eregi_replace( "&lt;($tag)[[:space:]]*\/&gt;", "<\\1 />", $p_string );
			}
		}

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

		switch ( config_get( 'show_' . $p_action ) ) {
			case BOTH:
				if ( null !== $p_user_id &&
					 ON == user_pref_get_pref( $p_user_id, 'advanced_' . $p_action ) ) {
					return 'bug_' . $p_action . '_advanced_page.php';
				} else {
					return 'bug_' . $p_action . '_page.php';
				}
				break;
			case SIMPLE_ONLY:
					return 'bug_' . $p_action . '_page.php';
				break;
			case ADVANCED_ONLY:
					return 'bug_' . $p_action . '_advanced_page.php';
				break;
		}
	}

	# --------------------
	# return an href anchor that links to a bug VIEW page for the given bug
	#  account for the user preference and site override
	function string_get_bug_view_link( $p_bug_id, $p_user_id=null ) {
		if ( bug_exists( $p_bug_id ) ) {
			$t_summary = string_attribute( bug_get_field( $p_bug_id, 'summary' ) );
			$t_status = string_attribute( get_enum_element( 'status', bug_get_field( $p_bug_id, 'status' ) ) );
			return '<a href="' . string_get_bug_view_url( $p_bug_id, $p_user_id ) . '" title="[' . $t_status . '] ' . $t_summary . '">' . bug_format_id( $p_bug_id ) . '</a>';
		} else {
			return bug_format_id( $p_bug_id );
		}
	}

	# --------------------
	# return the name and GET parameters of a bug VIEW page for the given bug
	#  account for the user preference and site override
	function string_get_bug_view_url( $p_bug_id, $p_user_id=null ) {
		return string_get_bug_view_page( $p_user_id ) . '?bug_id=' . bug_format_id( $p_bug_id );
	}

	# --------------------
	# return the name and GET parameters of a bug VIEW page for the given bug
	#  account for the user preference and site override
	# The returned url includes the fully qualified domain, hence it is suitable to be included
	# in emails.
	function string_get_bug_view_url_with_fqdn( $p_bug_id, $p_user_id=null ) {
		return config_get( 'path' ) . string_get_bug_view_url( $p_bug_id, $p_user_id );
	}

	# --------------------
	# return the name of a bug VIEW page for the user
	#  account for the user preference and site override
	function string_get_bug_view_page( $p_user_id=null ) {
		return string_get_bug_page( 'view', $p_user_id );
	}

	# --------------------
	# return an href anchor that links to a bug UPDATE page for the given bug
	#  account for the user preference and site override
	function string_get_bug_update_link( $p_bug_id, $p_user_id=null ) {
		$t_summary = string_attribute( bug_get_field( $p_bug_id, 'summary' ) );
		return '<a href="' . string_get_bug_update_url( $p_bug_id, $p_user_id ) . '" title="' . $t_summary . '">' . bug_format_id( $p_bug_id ) . '</a>';
	}

	# --------------------
	# return the name and GET parameters of a bug UPDATE page for the given bug
	#  account for the user preference and site override
	function string_get_bug_update_url( $p_bug_id, $p_user_id=null ) {
		return string_get_bug_update_page( $p_user_id ) . '?bug_id=' . bug_format_id( $p_bug_id );
	}

	# --------------------
	# return the name of a bug UPDATE page for the user
	#  account for the user preference and site override
	function string_get_bug_update_page( $p_user_id=null ) {
		return string_get_bug_page( 'update', $p_user_id );
	}

	# --------------------
	# return an href anchor that links to a bug REPORT page for the given bug
	#  account for the user preference and site override
	function string_get_bug_report_link( $p_user_id=null ) {
		return '<a href="' . string_get_bug_report_url( $p_user_id ) . '">' . lang_get( 'report_bug_link' ) . '</a>';
	}

	# --------------------
	# return the name and GET parameters of a bug REPORT page for the given bug
	#  account for the user preference and site override
	function string_get_bug_report_url( $p_user_id=null ) {
		return string_get_bug_report_page( $p_user_id );
	}

	# --------------------
	# return the name of a bug REPORT page for the user
	#  account for the user preference and site override
	function string_get_bug_report_page( $p_user_id=null ) {
		return string_get_bug_page( 'report', $p_user_id );
	}
?>
