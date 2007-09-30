<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_custom_field_create.php,v 1.17 2007-09-30 02:55:05 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'custom_field_api.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

	$f_name	= gpc_get_string( 'name' );

	$t_field_id = custom_field_create( $f_name );

	if ( ON == config_get( 'custom_field_edit_after_create' ) ) {
		$t_redirect_url = "manage_custom_field_edit_page.php?field_id=$t_field_id";
	} else {
		$t_redirect_url = 'manage_custom_field_page.php';
	}

	html_page_top1();
	html_meta_redirect( $t_redirect_url );
	html_page_top2();

	echo '<br />';
	echo '<div align="center">';

	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );

	echo '</div>';

	html_page_bottom1( __FILE__ );
?>
