<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	### check access level
	if ( access_level_check_greater( "reporter" ) ) {
?>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td valign=top width=100% bgcolor=<? echo $g_white_color ?>>
	<table width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
	<form method=post action="<? echo $g_bugnote_add_page ?>">
		<td align=center bgcolor=<? echo $g_white_color ?> colspan=2>
		<input type=hidden name=f_id value="<? echo $v_id ?>">
		<input type=submit value="Add Bugnote">
		</td>
	</form>
	</tr>
	</table>
	</td>
</tr>
</table>
<?
	}
?>