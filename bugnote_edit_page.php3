<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Remove the bugnote and bugnote text and redirect back to
	### the viewing page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	#check_access( UPDATER );

	# grab the bugnote text
  	$query = "SELECT note
			FROM $g_mantis_bugnote_text_table
			WHERE id='$f_bugnote_text_id'";
	$result = db_query( $query );
	$f_bugnote_text = db_result( $result, 0, 0 );

	$f_bugnote_text = string_edit_textarea( $f_bugnote_text );
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

<p>
<div align="center">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<form method="post" action="<? echo $g_bugnote_update ?>">
<input type="hidden" name="f_id" value="<? echo $f_id ?>">
<input type="hidden" name="f_bugnote_text_id" value="<? echo $f_bugnote_text_id ?>">
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_edit_bugnote_title ?></b>
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_primary_color_dark ?>">
			<textarea cols="80" rows="10" name="f_bugnote_text" wrap="virtual"><? echo $f_bugnote_text ?></textarea>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="6" bgcolor="<? echo $g_white_color ?>">
			<input type="submit" value="<? echo $s_update_information_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</form>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>

