<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: collapse_api.php,v 1.2 2004-07-18 13:22:28 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	### Collapse API ###

	#
	#	collapse_open( 'xyz' );		# marks the start of the expanded section
	#	:
	#	... collapse_icon( 'xyz' );	# this will add the '+' icon
	#	:
	#	collapse_closed( 'xyz' );	# marks the start of the collapsed section
	#	:
	#	... collapse_icon( 'xyz' );	# this will add the '-' icon
	#	:
	#	collapse_end( 'xyz' );		# marks the end of the whole section
	#
	# In javascript/common.js, add the g_div_xyz constants.
	#

	$g_current_collapse_section = null;
	$g_open_collapse_section = false;

	# ---------------
	# Use at the top of the section that should be visible when the section is expanded.
	# sections can not be nested
	function collapse_open( $p_name ) {
		global $g_current_collapse_section, $g_open_collapse_section;

		# make sure no other collapse section is started
		if ( $g_current_collapse_section !== null ) {
			trigger_error( ERROR_GENERIC, ERROR );
		}

		$g_open_collapse_section = true;
		$g_current_collapse_section = $p_name;

		$t_div_id = $p_name . '_open';
		echo "<div id=\"$t_div_id\">";
	}

	# ---------------
	# Use to mark the end of the expanded section and the beginning of the closed section
	# the closed section will not be sent to the browser if $g_Use_javascript is OFF.
	# This is achieved using output buffering.
	function collapse_closed( $p_name ) {
		global $g_current_collapse_section, $g_open_collapse_section;

		# Make sure a section is opened, and it is the same section.
		if ( $p_name !== $g_current_collapse_section ) {
			trigger_error( ERROR_GENERIC, ERROR );
		}

		echo '</div>';

		$g_open_collapse_section = false;

		ob_start();

		echo '<div id="', $p_name, '_closed" style="display: none;">';
	}

	# ---------------
	# This is used within both the open and closed section to identify the location where the
	# '+'/'-' icon should be placed.
	function collapse_icon( $p_name ) {
		if ( OFF == config_get( 'use_javascript' ) ) {
			return;
		}

		global $g_open_collapse_section;

		if ( $g_open_collapse_section === true ) {
			$t_icon = 'minus.png';
			$t_alt  = '-';
		} else {
			$t_icon = 'plus.png';
			$t_alt  = '+';
		}

		echo "<a href=\"\" onClick=\"ToggleDiv( '$p_name', g_div_$p_name ); return false;\"
			><img border=\"0\" src=\"images/$t_icon\" alt=\"$t_alt\" /></a>&nbsp;";
	}

	# ---------------
	# Mark the end of the collapsible section
	function collapse_end( $p_name ) {
		global $g_current_collapse_section, $g_open_collapse_section;


		# Make sure a section is opened, and it is the same section.
		if ( $p_name !== $g_current_collapse_section ) {
			ob_end_clean();
			trigger_error( ERROR_GENERIC, ERROR );
		}

		echo '</div>';

		$g_open_collapse_section = false;

		if ( ON == config_get( 'use_javascript' ) ) {
			echo '<script type="text/JavaScript">';
			echo '	SetDiv( "', $p_name, '", g_div_', $p_name, ' );';
			echo '</script>';

			ob_end_flush();
		} else {
			ob_end_clean();
		}

		$g_current_collapse_section = null;
	}
?>