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
<? print_page_top1() ?>
<? print_page_top2() ?>

<? # Add News Form BEGIN ?>
<p>
<div align="center">
<form method="post" action="<? echo $g_news_add ?>">
<input type="hidden" name="f_poster_id" value="<? echo get_current_user_field( "id " ) ?>">
<table class="width75" cellspacing="0">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_add_news_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<? echo $s_headline ?><br>
		<? echo $s_do_not_use ?> "
	</td>
	<td width="75%">
		<input type="text" name="f_headline" size="64" maxlength="64">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_body ?>
	</td>
	<td>
		<textarea name="f_body" cols="60" rows="8" wrap="virtual"></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_post_to ?>
	</td>
	<td>
		<select name="f_project_id">
			<?
				if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
					PRINT "<option value=\"0000000\">Sitewide</option>";
				}
			?>
			<? print_news_project_option_list( $g_project_cookie_val ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_post_news_button ?>">
	</td>
</tr>
</table>
</form>
</div>
<? # Add News Form END ?>

<? # Edit/Delete News Form BEGIN ?>
<p>
<div align="center">
<form method="post" action="<? echo $g_news_edit_page ?>">
<table class="width75" cellspacing="0">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_edit_or_delete_news_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<input type="radio" name="f_action" value="edit" CHECKED> <? echo $s_edit_post ?>
		<input type="radio" name="f_action" value="delete"> <? echo $s_delete_post ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<? echo $s_select_post ?>
	</td>
	<td width="75%">
		<select name="f_id">
			<? print_news_item_option_list() ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_submit_button ?>">
	</td>
</tr>
</table>
</form>
</div>
<? # Edit/Delete News Form END ?>

<? print_page_bot1( __FILE__ ) ?>