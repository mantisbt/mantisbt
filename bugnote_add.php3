<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### get user information
	$u_id = get_current_user_field( "id " );

	$f_bugnote_text = string_safe( $f_bugnote_text );
	### insert bugnote text
	$query = "INSERT
			INTO $g_mantis_bugnote_text_table
			( id, note )
			VALUES
			( null, '$f_bugnote_text' )";
	$result = mysql_query( $query );

	### retrieve bugnote text id number
	### NOTE: this is guarranteed to be the correct one.
	### The value LAST_INSERT_ID is stored on a per connection basis.

	$query = "select LAST_INSERT_ID()";
	$result = mysql_query( $query );
	if ( $result ) {
		$t_bugnote_text_id = mysql_result( $result, 0 );
	}

	### insert bugnote info
	$query = "INSERT
			INTO $g_mantis_bugnote_table
			( id, bug_id, reporter_id, bugnote_text_id, date_submitted, last_modified )
			VALUES
			( null, '$f_bug_id', '$u_id','$t_bugnote_text_id',NOW(), NOW() )";
	$result = mysql_query( $query );

	### get date submitted (weird bug in mysql)
	$query = "SELECT date_submitted
			FROM $g_mantis_bug_table
    		WHERE id='$f_bug_id'";
   	$result = mysql_query( $query );
   	$t_date_submitted = mysql_result( $result, 0 );

	### update bug last updated
	$query = "UPDATE $g_mantis_bug_table
    		SET date_submitted='$t_date_submitted', last_updated=NOW()
    		WHERE id='$f_bug_id'";
   	$result = mysql_query($query);
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		if ( get_current_user_profile_field( "advanced_view" )=="on" ) {
			print_meta_redirect( "$g_view_bug_advanced_page?f_id=$f_bug_id", $g_wait_time );
		}
		else {
			print_meta_redirect( "$g_view_bug_page?f_id=$f_bug_id", $g_wait_time );
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
	if ( $result ) {
		PRINT "$s_bugnote_added<p>";
	}
	### OK!!!
	else {
		PRINT "$s_sql_error_detected <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
		PRINT $query;
	}
?>
<p>
<? if ( get_current_user_profile_field( "advanced_view" )=="on" ) { ?>
<a href="<? echo $g_view_bug_advanced_page ?>?f_id=<? echo $f_id ?>"><? echo $s_proceed ?></a>
<? } else { ?>
<a href="<? echo $g_view_bug_page ?>?f_id=<? echo $f_id ?>"><? echo $s_proceed ?></a>
<? } ?>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>