<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php #login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	$valid_project = 1;
	# Check for invalid project_id selection
	if ( empty( $f_project_id ) ) {
		$valid_project = 0;
	}

	if ( !isset( $f_ref ) ) {
		$f_ref = "";
	}

	# Set default project
	if ( isset( $f_make_default ) ) {
		$t_user_id = get_current_user_field( "id" );
		$query = "UPDATE $g_mantis_user_pref_table
				SET default_project='$f_project_id'
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
			$t_redirect_url = $g_main_page;
		} else if ( eregi( $g_view_all_bug_page,$HTTP_REFERER ) ){
			$t_redirect_url = $g_view_all_bug_page;
		} else if ( eregi( $g_summary_page,$HTTP_REFERER ) ){
			$t_redirect_url = $g_summary_page;
		} else if ( eregi( $g_manage_project_user_menu_page,$HTTP_REFERER ) ){
			$t_redirect_url = $g_manage_project_user_menu_page;
		} else {
			$t_redirect_url = $g_main_page;
		}
	}

	if ( !empty( $f_ref ) ) {
		$t_redirect_url = $f_ref;
	}

	# clear view filter between projects
	setcookie( $g_view_all_cookie );

	if ( ( ON == $g_quick_proceed )&&( $result ) ) {
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
		PRINT "$s_operation_successful<p>";
	} else {						# FAILURE
		PRINT "$s_valid_project_msg";
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
