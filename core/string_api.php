<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: string_api.php,v 1.2 2002-08-25 21:48:12 jfitzell Exp $
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
		$p_string = stripslashes( $p_string );
		$p_string = process_bug_link( $p_string );
		$p_string = process_cvs_link( $p_string );
		$p_string = nl2br( $p_string );
		return $p_string;
	}
	# --------------------
	# Prepare a string for plain text display in email
	function string_email( $p_string ) {
		$p_string = stripslashes( $p_string );
		$p_string = unfilter_href_tags( $p_string );
		$p_string = process_bug_link_email( $p_string );
		$p_string = process_cvs_link_email( $p_string );
		$p_string = str_replace( '&lt;', '<',  $p_string );
		$p_string = str_replace( '&gt;', '>',  $p_string );
		$p_string = str_replace( '&quot;', '"',  $p_string );
		$p_string = str_replace( '&amp;', '&',  $p_string );

		return $p_string;
	}
	# --------------------
	# Process a string for display in a textarea box
	function string_edit_textarea( $p_string ) {
		$p_string = stripslashes( $p_string );
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
		$p_string = stripslashes( $p_string );
		$p_string = str_replace( '<br>', '',  $p_string );
		$p_string = unfilter_href_tags( $p_string );
		$p_string = str_replace( '&lt;', '<',  $p_string );
		$p_string = str_replace( '&gt;', '>',  $p_string );
		$p_string = str_replace( '&quot;', '\'',  $p_string );
		return $p_string;
	}

	# --------------------	
	# process the $p_string and convert filenames in the format
	# cvs:filename.ext or cvs:filename.ext:n.nn to a html link
	function process_cvs_link( $p_string ) {
		global $g_cvs_web;

		return preg_replace( '/cvs:([^\.\s:,\?!]+(\.[^\.\s:,\?!]+)*)(:)?(\d\.[\d\.]+)?([\W\s])?/i',
							 '[CVS] <a href="'.$g_cvs_web.'\\1?rev=\\4" target="_new">\\1</a>\\5',
							 $p_string );
	}
	### --------------------
	# process the $p_string and convert filenames in the format
	# cvs:filename.ext or cvs:filename.ext:n.nn to a html link
	function process_cvs_link_email( $p_string ) {
		global $g_cvs_web;

		return preg_replace( '/cvs:([^\.\s:,\?!]+(\.[^\.\s:,\?!]+)*)(:)?(\d\.[\d\.]+)?([\W\s])?/i',
							 '[CVS] '.$g_cvs_web.'\\1?rev=\\4\\5',
							 $p_string );
	}
	# --------------------
	# process the $p_string and create links to bugs if warranted
	# Uses the $g_bug_link_tag variable to determine the bug link tag
	# eg. #45  or  bug:76
	# default is the # symbol.  You may substitue any pattern you want.
	function process_bug_link( $p_string ) {
		global $g_bug_link_tag;

		if ( ON == get_current_user_pref_field( 'advanced_view' ) ) {
			return preg_replace("/$g_bug_link_tag([0-9]+)/",
								"<a href=\"view_bug_advanced_page.php?f_id=\\1\">#\\1</a>",
								$p_string);
		} else {
			return preg_replace("/$g_bug_link_tag([0-9]+)/",
								"<a href=\"view_bug_page.php?f_id=\\1\">#\\1</a>",
								$p_string);
		}
	}
	# --------------------
	# process the $p_string and convert bugs in this format #123 to a plain text link
	function process_bug_link_email( $p_string ) {
		global	$g_bug_link_tag;

		if ( ON == get_current_user_pref_field( 'advanced_view' ) ) {
			return preg_replace("/$g_bug_link_tag([0-9]+)/",
								"view_bug_advanced_page.php?f_id=\\1",
								$p_string);
		} else {
			return preg_replace("/$g_bug_link_tag([0-9]+)/",
								"view_bug_page.php?f_id=\\1",
								$p_string);
		}
	}

?>
