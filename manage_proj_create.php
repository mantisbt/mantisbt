<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_create.php,v 1.2 2003-02-11 09:08:44 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# We can't use check_access() because we need absolute access level, not
	#  project-based access level
	# @@@ try to add some new access apis
	if ( !absolute_access_level_check_greater_or_equal( config_get( 'create_project_threshold' ) ) ) {
		access_denied();
	}

	$f_name 		= gpc_get_string( 'name' );
	$f_description 	= gpc_get_string( 'description' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_status		= gpc_get_int( 'status' );
	$f_file_path	= gpc_get_string( 'file_path', '' );

	project_create( $f_name, $f_description, $f_status, $f_view_state, $f_file_path );

	$t_redirect_url = 'manage_proj_page.php';

	print_page_top1();

	print_meta_redirect( $t_redirect_url );

	print_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
