<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Removes all the cookies and then redirect to $g_logout_redirect_page
?>
<? include( "core_API.php" ); ?>
<?
	### delete cookies then redirect to $g_logout_redirect_page
	setcookie( $g_string_cookie );
	setcookie( $g_project_cookie );
	setcookie( $g_view_all_cookie );
	setcookie( $g_view_reported_cookie );
	setcookie( $g_view_assigned_cookie );
	setcookie( $g_view_unassigned_cookie );
	setcookie( $g_manage_cookie );

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? print_meta_redirect( $g_logout_redirect_page, $g_wait_time ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<p>
<div align="center">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_logged_out_title ?></b>
		</td>
	</tr>
	<tr>
		<td align="right" bgcolor="<? echo $g_primary_color_dark ?>">
			<b><? echo $s_redirecting ?> <a href="<? echo $g_logout_redirect_page ?>"><? echo $s_here ?></a></b>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>