<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### get the bugnote data
	$query = "SELECT *
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$f_id'";
	$result = db_mysql_query($query);
	$num_notes = mysql_num_rows($result);
?>

<table width=100% cols=2 bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<?
	### no bugnotes
	if ( $num_notes==0 ) {
?>
<tr>
	<td bgcolor=<? echo $g_white_color ?> align=center colspan=2>
		There are no notes attached to this bug yet.<br>
	</td>
</tr>
<?
	}
	### print bugnotes
	else {
?>
<tr>
	<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
		<b>Bug Notes</b>
	</td>
</tr>
<?
	for($i=0; $i < $num_notes; $i++) {
		### prefix all bugnote data with v3_
		$row = mysql_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v3" );
		$v3_date_submitted = date( "m-d H:i", sql_to_unix_time( $v3_date_submitted ) );

		### grab the bugnote text and prefix with v3_
		$query = "SELECT note
				FROM $g_mantis_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_mysql_query($query);
		$v3_note = mysql_result( $result2, 0);

		### grab the bugnote posters username and email and prefix with v5_
		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$v3_reporter_id'";
		$result3 = db_mysql_query($query);
		$row3 = mysql_fetch_array( $result3 );
		extract( $row3, EXTR_PREFIX_ALL, "v5" );
?>
<tr height=5 bgcolor=<? echo $g_white_color ?>>
	<td colspan=2 bgcolor=<? echo $g_white_color ?>>
	</td>
</tr>
<tr>
	<td valign=top width=25% bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=2 bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center colspan=2>
			<a href="mailto:<? echo $v5_email ?>"><? echo $v5_username ?></a>
		</td>
	</tr>
	<tr align=center>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v3_date_submitted ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
		<?
			### check access level
			if ( access_level_check_greater( "reporter" ) ) {
		?>
			<font size=1><a href="<? echo $g_bugnote_delete ?>?f_id=<? echo $f_id ?>&f_bug_id=<? echo $v3_id ?>">Delete</a></font>
		<? } ?>
		</td>
	</tr>
	</table>
	</td>
	<td valign=top width=75% bgcolor=<? echo $g_white_color ?>>
	<table width=100% align=center>
	<tr>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo string_unsafe( $v3_note ) ?>
		</td>
	</tr>
	</table>
	</td>
</tr>
<?
		}
	}
?>
</table>