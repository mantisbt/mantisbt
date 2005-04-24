<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: set_project.php,v 1.54 2005-04-24 13:27:03 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	$f_project_id	= gpc_get_string( 'project_id' );
	$f_make_default	= gpc_get_bool  ( 'make_default' );
	$f_ref			= gpc_get_string( 'ref', '' );

	$t_project = split( ';', $f_project_id );
	$t_top     = $t_project[0];
	$t_bottom  = $t_project[ count( $t_project ) - 1 ];

	if ( ALL_PROJECTS != $t_bottom ) {
		project_ensure_exists( $t_bottom );
	}

	# Set default project
	if ( $f_make_default ) {
		current_user_set_default_project( $t_top );
	}

	helper_set_current_project( $f_project_id );

	# redirect to 'same page' when switching projects.

	# for proxies that clear out HTTP_REFERER
	if ( !is_blank( $f_ref ) ) {
		$t_redirect_url = $f_ref;
	} else if ( !isset( $_SERVER['HTTP_REFERER'] ) || is_blank( $_SERVER['HTTP_REFERER'] ) ) {
		$t_redirect_url = config_get( 'default_home_page' );
	} else {
		$t_home_page = config_get( 'default_home_page' );

		# Check that referrer matches our address after squashing case (case insensitive compare)
		$t_path = config_get( 'path' );
		if ( strtolower( $t_path ) == strtolower( substr( $_SERVER['HTTP_REFERER'], 0, strlen( $t_path ) ) ) ) {
			$t_referrer_page = substr( $_SERVER['HTTP_REFERER'], strlen( $t_path ) );
			# if view_all_bug_page, pass on filter	
			if ( eregi( 'view_all_bug_page.php', $t_referrer_page ) ) {
				$t_source_filter_id = filter_db_get_project_current( $f_project_id );
				$t_redirect_url = 'view_all_set.php?type=4';

				if ( $t_source_filter_id !== null ) {
					$t_redirect_url = 'view_all_set.php?type=3&amp;source_query_id=' . $t_source_filter_id;
				}
			} else if ( eregi( '_page.php', $t_referrer_page ) ) {
				# if any other page, return to that page
				$t_redirect_url = $t_referrer_page;
			} else {
				$t_redirect_url = $t_home_page;
			}
		} else {
			$t_redirect_url = $t_home_page;
		}
	}

	print_header_redirect( $t_redirect_url );
?>
<?php html_page_top1() ?>
<?php
	html_meta_redirect( $t_redirect_url );
?>
<?php html_page_top1() ?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
