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
	check_access( MANAGER );
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

<? print_doc_menu( $g_proj_doc_add_page ) ?>

<p>
<div align="center">
<table class="width75" cellspacing="0">
<form method="post" enctype="multipart/form-data" action="<? echo $g_proj_doc_add ?>">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_upload_file_title ?>
	</td>
</tr>
<tr class="row-1">
	<td width="25%">
		<? echo $s_title ?>
	</td>
	<td width="75%">
		<input type="text" name="f_title" size="70" maxlength="250">
	</td>
</tr>
<tr class="row-2">
	<td>
		<? echo $s_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="7" wrap="virtual"></textarea>
	</td>
</tr>
<tr class="row-1">
	<td>
		<? echo $s_select_file ?>
	</td>
	<td>
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<input type="hidden" name="max_file_size" value="5000000">
		<input name="f_file" type="file" size="70">
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_upload_file_button ?>">
	</td>
</tr>
</table>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>