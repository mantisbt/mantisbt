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

	### Update all fields
    $query = "UPDATE $g_mantis_bug_table
    		SET category='$f_category', severity='$f_severity',
    			reproducibility='$f_reproducibility',
				priority='$f_priority', status='$f_status',
				projection='$f_projection', duplicate_id='$f_duplicate_id',
				resolution='$f_resolution', handler_id='$f_handler_id',
				date_submitted='$f_date_submitted',
				last_updated=NOW(), eta='$f_eta',
				handler_id='$f_handler_id'
    		WHERE id='$f_id'";
   	$result = db_query($query);

	### If we should notify and it's in feedback state then send an email
   	if ( $f_status=="feedback" ) {
   		email_feedback( $f_id );
   	}

   	if ( $f_status=="assigned" ) {
   		email_assign( $f_id );
   	}
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

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<?
	### SUCCESS
	if ( $result ) {
		PRINT "$s_bug_updated_msg<p>";
	}
	### FAILURE
	else {
		PRINT "$s_sql_error_detected <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
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

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>