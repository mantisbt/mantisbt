<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=2>
	<tr>
		<td bgcolor=<? echo $g_white_color ?> colspan=2>
			<b>Summary</b>
		</td>
	</tr>
	<tr valign=bottom height=28 bgcolor=<? echo $g_white ?>>
		<td width=50%>
			<b>by status:</b>
		</td>
		<td width=50%>
			<b>by date:</b>
		</td>
	</tr>
	<tr align=center valign=top height=28 bgcolor=<? echo $g_white ?>>
		<td width=50%>
			<? ### STATUS ### ?>
			<table width=97%>
				<? print_bug_enum_summary( "status" ) ?>
			</table>
		</td>
		<td width=50%>
			<? ### DATE ### ?>
			<table width=97%>
			<?
				print_bug_date_summary( $g_date_partitions );
			?>
			</table>
		</td>
	</tr>
	<tr valign=bottom height=28 bgcolor=<? echo $g_white ?>>
		<td width=50%>
			<b>by severity:</b>
		</td>
		<td width=50%>
			<b>by resolution:</b>
		</td>
	</tr>
	<tr align=center valign=top height=28 bgcolor=<? echo $g_white ?>>
		<td>
			<? ### SEVERITY ### ?>
			<table width=97%>
				<? print_bug_enum_summary( "severity" ) ?>
			</table>
		</td>
		<td>
			<? ### RESOLUTION ### ?>
			<table width=97%>
				<? print_bug_enum_summary( "resolution" ) ?>
			</table>
		</td>
		</tr>
	<tr valign=bottom height=28 bgcolor=<? echo $g_white ?>>
		<td>
			<b>by category:</b>
		</td>
		<td>
			<b>by priority:</b>
		</td>
	</tr>
	<tr align=center valign=top height=28 bgcolor=<? echo $g_white ?>>
		<td>
			<? ### CATEGORY ### ?>
			<table width=97%>
				<? print_bug_enum_summary( "category" ) ?>
			</table>
		</td>
		<td>
			<? ### CATEGORY ### ?>
			<table width=97%>
				<? print_bug_enum_summary( "priority", "open" ) ?>
			</table>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>