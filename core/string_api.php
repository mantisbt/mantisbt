<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: string_api.php,v 1.15 2002-11-30 21:15:42 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# String Processing API
	###########################################################################

	# --------------------
	# every string that comes from a textarea should be processed through this
	# function *before* insertion into the database.
	#
	# @@@ Old function.  Use db_prepare_string() now.  Delete this at some point
	#
	function string_prepare_textarea( $p_string ) {
		global $g_allow_href_tags, $g_allow_html_tags;

		$p_string = htmlspecialchars( $p_string );

		if ( ON == $g_allow_html_tags ) {
			$p_string = filter_html_tags( $p_string );
		}

		if ( ON == $g_allow_href_tags ) {
			$p_string = filter_href_tags( $p_string );
		}

		$p_string = filter_img_tags( $p_string );
		$p_string = addslashes( $p_string );
		return $p_string;
	}

	# --------------------
	# every string that comes from a text field should be processed through this
	# function *before* insertion into the database.
	#
	# @@@ Old function.  Use db_prepare_string() now.  Delete this at some point
	#
	function string_prepare_text( $p_string ) {
		global $g_allow_href_tags, $g_allow_html_tags;

		$p_string = htmlspecialchars( $p_string );

		if ( ON == $g_allow_html_tags ) {
			$p_string = filter_html_tags( $p_string );
		}

		if ( ON == $g_allow_href_tags ) {
			$p_string = filter_href_tags( $p_string );
		}

		$p_string = filter_img_tags( $p_string );
		$p_string = addslashes( $p_string );
		return $p_string;
	}

	# --------------------
	# Use this to prepare a string for display to HTML
	function string_display( $p_string ) {
		$p_string = string_process_bug_link( $p_string );
		$p_string = string_process_cvs_link( $p_string );
		$p_string = nl2br( $p_string );
		return $p_string;
	}

	# --------------------
	# Prepare a string for plain text display in email
	function string_email( $p_string ) {
		$p_string = unfilter_href_tags( $p_string );
		$p_string = string_process_bug_link( $p_string, false );
		$p_string = string_process_cvs_link( $p_string, false );
		$p_string = str_replace( '&lt;', '<',  $p_string );
		$p_string = str_replace( '&gt;', '>',  $p_string );
		$p_string = str_replace( '&quot;', '"',  $p_string );
		$p_string = str_replace( '&amp;', '&',  $p_string );

		return $p_string;
	}

	# --------------------
	# Process a string for display in a textarea box
	function string_edit_textarea( $p_string ) {
		$p_string = str_replace( '<br>', '',  $p_string );
		$p_string = unfilter_href_tags( $p_string );
		$p_string = str_replace( '<br />', '\n',  $p_string );
		$p_string = str_replace( '&lt;', '<',  $p_string );
		$p_string = str_replace( '&gt;', '>',  $p_string );
		$p_string = str_replace( '&quot;', '"',  $p_string );
		return $p_string;
	}

	# --------------------
	# Process a string for display in a text box
	function string_edit_text( $p_string ) {
		$p_string = str_replace( '<br>', '',  $p_string );
		$p_string = unfilter_href_tags( $p_string );
		$p_string = str_replace( '&lt;', '<',  $p_string );
		$p_string = str_replace( '&gt;', '>',  $p_string );
		$p_string = str_replace( '&quot;', '\'',  $p_string );
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
	# process the $p_string and create links to bugs if warranted
	# Uses the bug_link_tag config variable to determine the bug link tag
	# eg. #45  or  bug:76
	# default is the # symbol.  You may substitue any pattern you want.
	# if $p_include_anchor is true, include an <a href="..."> tag,
	#  otherwise, just insert the URL as text
	# The symbol must be at the beginning of the string or preceeded by whitespace
	function string_process_bug_link( $p_string, $p_include_anchor=true ) {
		$t_tag = config_get( 'bug_link_tag' );
		$t_path = config_get( 'path' );

		if ( ON == current_user_get_pref( 'advanced_view' ) ) {
			$t_page_name = 'bug_view_advanced_page.php';
		} else {
			$t_page_name = 'bug_view_page.php';
		}

		if ( $p_include_anchor ) {
			$t_replace_with = <<< EOT
'\\1<a href="$t_page_name?f_bug_id=\\2" title="' .
bug_get_field( \\2, 'summary' ) .
'">#\\2</a>'
EOT;
		} else {
			$t_replace_with = $t_path.$t_page_name.'?f_bug_id=\\2';
		}
		
		return preg_replace('/(\W|^)' . $t_tag . '([0-9]+)/e',
								$t_replace_with,
								$p_string);
	}


	#===================================
	# Tag Processing
	#===================================

	# --------------------
	# Detect URLs and email addresses in the string and replace them with href anchors
	function filter_href_tags( $p_string ) {
    	$p_string = eregi_replace( "([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",
    							"<a href=\"\\1://\\2\\3\">\\1://\\2\\3</a>",
    							$p_string);
        $p_string = eregi_replace( "(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
        						"<a href=\"mailto:\\1\" target=\"_new\">\\1</a>",
        						$p_string);
		return $p_string;
	}

	# --------------------
	# Detect href anchors in the string and replace them with URLs and email addresses
	function unfilter_href_tags( $p_string ) {
		$p_string = eregi_replace( "<a href=\"mailto:(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))\" target=\"_new\">(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))</a>",
								"\\1",
								$p_string);
		$p_string = eregi_replace( "<a href=\"([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])\">([^[:space:]]*)([[:alnum:]#?/&=])</a>",
								"\\1://\\2\\3",
								$p_string);
		return $p_string;
	}

	# --------------------
	# @@@ currently does nothing
	function filter_img_tags( $p_string ) {
		return $p_string;
	}

	# --------------------
	# process $g_html_tags to be treated as html
	function filter_html_tags( $p_string ) {
		global $g_html_tags;

		$t_filter_from 	= @array( "/\//", "/</", "/>/" );
		$t_filter_to 	= @array( "\/", "", "" );
		//$t_filter_from 	= @array( "\/", "<", ">" );
		//$t_filter_to 	= @array( "\/", "", "" );

		$t_tag_count = count( $g_html_tags );
		for ($i=0;$i<$t_tag_count;$i++) {
			$tag = preg_replace( $t_filter_from, $t_filter_to, $g_html_tags[$i] );

			$p_string = preg_replace( "/&lt;($tag)&gt;/i", "<\\1>", $p_string );
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
			$p_user_id = auth_get_current_user_id();
		}

		switch ( config_get( 'show_' . $p_action ) ) {
			case BOTH:
				if ( ON == user_pref_get_pref( $p_user_id, 'advanced_' . $p_action ) ) {
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
		$t_summary = bug_get_field( $p_bug_id, 'summary' );
		return '<a href="' . string_get_bug_view_url( $p_bug_id, $p_user_id ) . '" title="' . $t_summary . '">' . bug_format_id( $p_bug_id ) . '</a>';
	}

	# --------------------
	# return the name and GET parameters of a bug VIEW page for the given bug
	#  account for the user preference and site override
	function string_get_bug_view_url( $p_bug_id, $p_user_id=null ) {
		return string_get_bug_view_page( $p_user_id ) . '?f_bug_id=' . bug_format_id( $p_bug_id );
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
		$t_summary = bug_get_field( $p_bug_id, 'summary' );
		return '<a href="' . string_get_bug_update_url( $p_bug_id, $p_user_id ) . '" title="' . $t_summary . '">' . bug_format_id( $p_bug_id ) . '</a>';
	}

	# --------------------
	# return the name and GET parameters of a bug UPDATE page for the given bug
	#  account for the user preference and site override
	function string_get_bug_update_url( $p_bug_id, $p_user_id=null ) {
		return string_get_bug_update_page( $p_user_id ) . '?f_bug_id=' . bug_format_id( $p_bug_id );
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
