<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### Remove the bugnote
	$query = "DELETE
			FROM $g_mantis_bugnote_table
			WHERE id='$f_bug_id'";
	$result = db_query($query);

	$query = "SELECT date_submitted
			FROM $g_mantis_bug_table
    		WHERE id='$f_id'";
   	$result = db_query( $query );
   	$t_date_submitted = db_result( $result, 0 );

	### update bug last updated
	$query = "UPDATE $g_mantis_bug_table
    		SET date_submitted='$t_date_submitted', last_updated=NOW()
    		WHERE id='$f_id'";
   	$result = db_query($query);
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		if ( get_current_user_profile_field( "advanced_view" )=="on" ) {
			print_meta_redirect( "$g_view_bug_advanced_page?f_id=$f_id", $g_wait_time );
		}
		else {
			print_meta_redirect( "$g_view_bug_page?f_id=$f_id", $g_wait_time );
		}
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<?
	### SUCCESS
	if ( $result ) {
		PRINT "$s_bugnote_deleted_msg<p>";
	}
	### FAILURE
	else {
		PRINT "$s_sql_error_detected <a href=\"mailto:<? echo $g_administrator_email ?>\">administrator</a><p>";
		echo $query;
	}
?>

<p>
<?
	if ( get_current_user_profile_field( "advanced_view" )=="on" ) {
		PRINT "<a href=\"$g_view_bug_advanced_page?f_id=$f_id\">$s_proceed</a>";
	}
	else {
		PRINT "<a href=\"$g_view_bug_page?f_id=$f_id\">$s_proceed</a>";
	}
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>