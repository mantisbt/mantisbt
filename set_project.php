<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php #login_cookie_check() ?>
<?php
	$valid_project = 1;
	# Check for invalid project_id selection
	$c_project_id = (integer)$f_project_id;
	if ( empty( $f_project_id ) ) {
		$valid_project = 0;
	}

	check_varset( $f_ref, '' );

	# Set default project
	if ( isset( $f_make_default ) ) {
		$t_user_id = get_current_user_field( 'id' );
		$query = "UPDATE $g_mantis_user_pref_table
				SET default_project='$c_project_id'
				WHERE user_id='$t_user_id'";
		$result = db_query( $query );
	}

	# Add item
	setcookie( $g_project_cookie, $f_project_id, time()+$g_cookie_time_length, $g_cookie_path );

	# redirect to 'same page' when switching projects.
	# view_all_* pages, and summary
	# for proxies that clear out HTTP_REFERER
	if ( 1 == $valid_project ) {
		if ( !isset( $HTTP_REFERER ) || empty( $HTTP_REFERER ) ) {
			$t_redirect_url = 'main_page.php';
		} else if ( eregi( 'view_all_bug_page.php', $HTTP_REFERER ) ){
			$t_redirect_url = 'view_all_set.php?f_type=0';
		} else if ( eregi( 'summary_page.php', $HTTP_REFERER ) ){
			$t_redirect_url =  'summary_page.php';
		} else if ( eregi( 'proj_user_menu_page.php', $HTTP_REFERER ) ){
			$t_redirect_url = 'proj_user_menu_page.php';
		} else {
			$t_redirect_url = 'main_page.php';
		}
	}

	if ( !empty( $f_ref ) ) {
		$t_redirect_url = $f_ref;
	}

	# clear view filter between projects
	setcookie( $g_view_all_cookie,	'', -1, $g_cookie_path );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	}
?>
<?php print_page_top1() ?>
<?php
	print_meta_redirect( $t_redirect_url );
?>
<?php print_page_top1() ?>

<p>
<div align="center">
<?php
	if ( 1 == $valid_project ) {	# SUCCESS
		PRINT $s_operation_successful.'<p>';
	} else {						# FAILURE
		echo $s_valid_project_msg;
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
