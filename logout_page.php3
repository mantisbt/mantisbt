<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ); ?>
<?
	### delete cookies
	setcookie( $g_string_cookie );
	setcookie( $g_last_access_cookie );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? print_meta_redirect( $g_index, $p_time=1 ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<p>
<div align=center>
<table width=50% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<tr>
		<td bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_logged_out ?></b>
		</td>
	</tr>
	<tr>
		<td align=right bgcolor=<? echo $g_primary_color_dark ?>>
			<b><? echo $s_redirecting ?> <a href="<? echo $g_index ?>"><? echo $s_here ?></a></b>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>