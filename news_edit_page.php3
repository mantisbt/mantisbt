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

	### If deleting item redirect to delete script
	if ( $f_action=="delete" ) {
		print_header_redirect( "$g_news_delete_page?f_id=$f_id" );
		exit;
	}

	### Retrieve news item data and prefix with v_
	$row = news_select_query( $f_id );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, "v" );
    }

   	$v_headline = string_edit_text( $v_headline );
   	$v_body 	= string_edit_textarea( $v_body );
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
	<? print_bracket_link( $g_news_menu_page, "Back" ) ?>
</div>

<? ### Edit News Form BEGIN ?>
<p>
<div align="center">
<form method="post" action="<? echo $g_news_update ?>">
<input type="hidden" name="f_id" value="<? echo $v_id ?>">
<table class="width75" cellspacing="0">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_edit_news_title ?>
	</td>
</tr>
<tr class="row-1">
	<td width="25%">
		<? echo $s_headline ?>
	</td>
	<td width="75%">
		<input type="text" name="f_headline" size="64" maxlength="64" value="<? echo $v_headline ?>">
	</td>
</tr>
<tr class="row-2">
	<td>
		<? echo $s_body ?>
	</td>
	<td>
		<textarea name="f_body" cols="60" rows="10" wrap="virtual"><? echo $v_body ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td>
		<? echo $s_post_to ?>
	</td>
	<td>
		<select name="f_project_id">
			<? if( ADMINISTRATOR == get_current_user_field( "access_level" ) ) { ?>
				<option value="0000000" <? if ( $v_project_id=="0000000" ) echo "SELECTED"?>>Sitewide</option>
			<? } ?>
			<? print_news_project_option_list( $v_project_id ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_update_news_button ?>">
	</td>
</tr>
</table>
</form>
</div>
<? ### Edit News Form END ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>