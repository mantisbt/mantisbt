<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### grab the user id currently logged in
	$t_user_id = get_current_user_field( "id " );

	### get the bugnote data
	$query = "SELECT *
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$f_id'
			ORDER BY date_submitted $g_bugnote_order";
	$result = db_query($query);
	$num_notes = db_num_rows($result);
?>

<table width=100% cols=2 bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<?
	### no bugnotes
	if ( $num_notes==0 ) {
?>
<tr>
	<td bgcolor=<? echo $g_white_color ?> align=center colspan=2>
		<? echo $s_no_bugnotes_msg ?>
	</td>
</tr>
<?
	}
	### print bugnotes
	else {
?>
<tr>
	<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
		<b><? echo $s_bug_notes_title ?></b>
	</td>
</tr>
<?
	for($i=0; $i < $num_notes; $i++) {
		### prefix all bugnote data with v3_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v3" );
		$v3_date_submitted = date( $g_normal_date_format, sql_to_unix_time( $v3_date_submitted ) );

		### grab the bugnote text and prefix with v3_
		$query = "SELECT note
				FROM $g_mantis_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_query($query);
		$v3_note = db_result( $result2, 0);
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
			<? print_user( $v3_reporter_id ) ?>
		</td>
	</tr>
	<tr align=center>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v3_date_submitted ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
		<?
			### check access level
			### only admins and the bugnote creator can delete this bug
			if (( access_level_check_greater_or_equal( "administrator" ) )||
				( $v3_reporter_id==$t_user_id )) {
		?>
			<span class="bugnote_delete"><a href="<? echo $g_bugnote_delete ?>?f_id=<? echo $f_id ?>&f_bug_id=<? echo $v3_id ?>"><? echo $s_delete_link ?></a></span>
		<? } ?>
		</td>
	</tr>
	</table>
	</td>
	<td valign=top width=75% bgcolor=<? echo $g_white_color ?>>
	<table width=100% align=center>
	<tr>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? echo string_display_with_br( $v3_note ) ?>
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