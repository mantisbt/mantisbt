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

	switch ( $g_show_view ) {
		case 0:	if ( get_current_user_pref_field( "advanced_view" )==1 ) {
					$t_redirect_url = $g_view_bug_page;
				} else {
					$t_redirect_url = $g_view_bug_advanced_page;
				}
				break;
		case 1:	$t_redirect_url = $g_view_bug_page;
				break;
		case 2:	$t_redirect_url = $g_view_bug_advanced_page;
				break;
	}
	$t_redirect_url = $t_redirect_url."?f_id=".$f_id;
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
<table class="width75" cellspacing="0">
<form method="post" action="<? echo $g_bugnote_update ?>">
<input type="hidden" name="f_id" value="<? echo $f_id ?>">
<input type="hidden" name="f_bugnote_text_id" value="<? echo $f_bugnote_text_id ?>">
<tr>
	<td class="form-title">
		<? echo $s_edit_bugnote_title ?>
	</td>
	<td class="right">
		<? print_bracket_link( $t_redirect_url, $s_go_back ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea cols="80" rows="10" name="f_bugnote_text" wrap="virtual"><? echo $f_bugnote_text ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_update_information_button ?>">
	</td>
</tr>
</form>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>