<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php #login_cookie_check() ?>
<?php
	$f_project_id	= gpc_get_int( 'f_project_id' );
	$f_make_default	= gpc_get_bool( 'f_make_default' );
	$f_ref			= gpc_get_string( 'f_ref', '' );

	# Set default project
	if ( $f_make_default ) {
		current_user_set_default_project( $f_project_id );
	}

	# Add item
	gpc_set_cookie( $g_project_cookie, $f_project_id, true );

	# redirect to 'same page' when switching projects.
	# view_all_* pages, and summary
	# for proxies that clear out HTTP_REFERER
	if ( !empty( $f_ref ) ) {
		$t_redirect_url = $f_ref;
	} else if ( !isset( $_SERVER['HTTP_REFERER'] ) || empty( $_SERVER['HTTP_REFERER'] ) ) {
		$t_redirect_url = 'main_page.php';
	} else if ( eregi( 'view_all_bug_page.php', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url = 'view_all_set.php?f_type=0';
	} else if ( eregi( 'summary_page.php', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url =  'summary_page.php';
	} else if ( eregi( 'proj_user_menu_page.php', $_SERVER['HTTP_REFERER'] ) ){
		$t_redirect_url = 'proj_user_menu_page.php';
	} else {
		$t_redirect_url = 'main_page.php';
	}

	# clear view filter between projects
	gpc_clear_cookie( $g_view_all_cookie );

	print_header_redirect( $t_redirect_url );
?>
<?php print_page_top1() ?>
<?php
	print_meta_redirect( $t_redirect_url );
?>
<?php print_page_top1() ?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
