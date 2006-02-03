<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_create.php,v 1.7.14.1.2.1 2006-02-03 03:56:34 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'project_hierarchy_api.php' );
?>
<?php
	access_ensure_global_level( config_get( 'create_project_threshold' ) );

	$f_name 		= gpc_get_string( 'name' );
	$f_description 	= gpc_get_string( 'description' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_status		= gpc_get_int( 'status' );
	$f_file_path	= gpc_get_string( 'file_path', '' );

	$t_project_id = project_create( strip_tags( $f_name ), $f_description, $f_status, $f_view_state, $f_file_path );

	if ( ( $f_view_state == VS_PRIVATE ) && ( false === current_user_is_administrator() ) ) {
		$t_access_level = access_get_global_level();
		$t_current_user_id = auth_get_current_user_id();
		project_add_user( $t_project_id, $t_current_user_id, $t_access_level );
	}

	$f_parent_id	= gpc_get_int( 'parent_id', 0 );

	if ( 0 != $f_parent_id ) {
		project_hierarchy_add( $t_project_id, $f_parent_id );
	}

	$t_redirect_url = 'manage_proj_page.php';

	html_page_top1();

	html_meta_redirect( $t_redirect_url );

	html_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
