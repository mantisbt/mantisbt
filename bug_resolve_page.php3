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
	check_access( UPDATER );
	check_bug_exists( $f_id );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>
<? print_menu( $g_menu_include_file ) ?>

<? ### Resolve Form BEGIN ?>
<p>
<div align="center">
<table class="width50" cellspacing="0">
<form method="post" action="<? echo $g_bug_resolve_page2 ?>">
<input type="hidden" name="f_id" value="<? echo $f_id ?>">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_resolve_bug_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_resolution ?>
	</td>
	<td>
		<select name="f_resolution">
			<? print_enum_string_option_list( $s_resolution_enum_string, FIXED ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_duplicate_id ?>
	</td>
	<td>
		<input type="text" name="f_duplicate_id" maxlength="7">
	</td>
</tr>
<? if ( 1 == $g_allow_close_immediately ) { ?>
<tr class="row-1">
	<td class="category">
		<? echo $s_close_immediately ?>
	</td>
	<td>
		<input type="checkbox" name="f_close_now">
	</td>
</tr>
<? } ?>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_resolve_bug_button ?>">
	</td>
</tr>
</form>
</table>
</div>
<? ### Resolve Form END ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>