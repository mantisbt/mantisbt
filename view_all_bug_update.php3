<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This page stores the reported bug and then redirects to view_all_bug_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( UPDATER );

	# We check to see if the variable exists to avoid warnings
	$result = 1;
	if ( isset( $f_bug_arr ) ) {
		$t_count = count( $f_bug_arr );
		for ( $i=0; $i < $t_count; $i++ ) {
			$t_new_id = $f_bug_arr[$i];
			$query = "UPDATE $g_mantis_bug_table
					SET project_id='$f_project_id'
					WHERE id='$t_new_id'";
			$result = db_query( $query );

			if ( !$result ) {
				break;
			}
		}
	}

	$t_redirect_url = $g_view_all_bug_page;
?>
<? print_page_top1() ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<? print_page_top2() ?>

<? print_proceed( $result, $query, $t_redirect_url ) ?>

<? print_page_bot1( __FILE__ ) ?>