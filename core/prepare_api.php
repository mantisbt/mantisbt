<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: prepare_api.php,v 1.1 2004-11-30 12:17:04 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	### Prepare API ###

	# this file handles preparing of strings like to be printed
	# or stored.  print_api.php will gradually be replaced by
	# think calls to echo the results of functions implemented here.

	# --------------------
	# return the mailto: href string link
	function prepare_email_link( $p_email, $p_text ) {
		if ( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
			return $p_text;
		}

		# If we apply string_url() to the whole mailto: link then the @
		#  gets turned into a %40 and you can't right click in browsers to
		#  do Copy Email Address.
		$t_mailto	= string_attribute( "mailto:$p_email" );
		$p_text		= string_display( $p_text );

		return "<a href=\"$t_mailto\">$p_text</a>";
	}

	# --------------------
	# prepares the name of the user given the id.  also makes it an email link.
	function prepare_user_name( $p_user_id ) {
		# Catch a user_id of NO_USER (like when a handler hasn't been assigned)
		if ( NO_USER == $p_user_id ) {
			return '';
		}

		$t_username = user_get_name( $p_user_id );
		if ( user_exists( $p_user_id ) && user_get_field( $p_user_id, 'enabled' ) ) {
			$t_email = user_get_email( $p_user_id );
			if ( !is_blank( $t_email ) ) {
				return prepare_email_link( $t_email, $t_username );
			} else {
				return $t_username;
			}
		} else {
			$t_result = '<font STYLE="text-decoration: line-through">';
			$t_result .= $t_username;
			$t_result .= '</font>';
			return $t_result;
		}
	}
