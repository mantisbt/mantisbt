<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	### This include file prints out the list of bugnotes attached to the bug
	### $f_id must be set and be set to the bug id
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

<? ### Bugnotes BEGIN ?>
<p>
<div align="center">
<table width="100%" cols="2" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<?
	### no bugnotes
	if ( $num_notes==0 ) {
?>
<tr>
	<td align="center" colspan="2" bgcolor="<? echo $g_white_color ?>">
		<? echo $s_no_bugnotes_msg ?>
	</td>
</tr>
<?	} else { ### print bugnotes ?>
<tr>
	<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
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

		$v3_note = string_display( $v3_note );
?>
<tr height="5" bgcolor="<? echo $g_white_color ?>">
	<td colspan="2" bgcolor="<? echo $g_white_color ?>">
	</td>
</tr>
<tr>
	<td valign="top" width="25%" bgcolor="<? echo $g_white_color ?>">
	<table width="100%" cols="2" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td align="center" colspan="2" bgcolor="<? echo $g_category_title_color ?>">
			<? print_user( $v3_reporter_id ) ?>
		</td>
	</tr>
	<tr align="center">
		<td bgcolor="<? echo $g_primary_color_dark ?>">
			<? echo $v3_date_submitted ?>
		</td>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
		<?
			### check access level
			### only admins and the bugnote creator can delete this bug
			if (( access_level_check_greater_or_equal( ADMINISTRATOR ) )||
				( $v3_reporter_id==$t_user_id )) {
		?>
			<span class="bugnotedelete">
				<? print_bracket_link( $g_bugnote_delete."?f_bug_id=".$v3_id, $s_delete_link ) ?>
			</span>
		<? } ?>
		</td>
	</tr>
	</table>
	</td>
	<td valign="top" width="75%" bgcolor="<? echo $g_white_color ?>">
	<table width="100%" align="center">
	<tr>
		<td bgcolor="<? echo $g_primary_color_light ?>">
			<? echo $v3_note ?>
		</td>
	</tr>
	</table>
	</td>
</tr>
<?
		} ### end for loop
	} ### end else
?>
</table>
</div>
<? ### Bugnotes END ?>

<? if (( $v_status < RESOLVED )||( isset( $f_resolve_note ) )) { ?>
<? ### Bugnote Add Form BEGIN ?>
<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<form method="post" action="<? echo $g_bugnote_add ?>">
	<input type="hidden" name="f_id" value="<? echo $f_id ?>">
	<tr>
		<td bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_add_bugnote_title ?></b>
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_primary_color_dark ?>">
			<textarea name="f_bugnote_text" cols="80" rows="10"></textarea>
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_primary_color_light ?>">
			<input type="submit" value="<? echo $s_add_bugnote_button ?>">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>
<? ### Bugnote Add Form END ?>
<?
	} else if (( access_level_check_greater_or_equal( $g_reopen_bug_threshold ) )||
		( $v3_reporter_id==$t_user_id )) {
?>
<? ### Bugnote Reopen Form BEGIN ?>
<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<form method="post" action="<? echo $g_bug_reopen_page ?>">
	<input type="hidden" name="f_id" value="<? echo $f_id ?>">
	<tr>
		<td align="center" bgcolor="<? echo $g_primary_color_light ?>">
			<input type="submit" value="<? echo $s_reopen_bug_button ?>">
		</td>
	</form>
	<? if ( $v_status != CLOSED ) { ?>
	<form method="post" action="<? echo $g_bug_close ?>">
	<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<td align="center" bgcolor="<? echo $g_primary_color_light ?>">
			<input type="submit" value="Close Bug">
		</td>
	</tr>
	</form>
	<? } ?>
	</table>
	</td>
</tr>
</table>
</div>
<? } ?>