<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_project_level( config_get( 'manage_news_threshold' ) );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Add News Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="news_add.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="poster_id" value="<?php echo auth_get_current_user_id() ?>" />
		<?php echo $s_add_news_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_headline ?>
	</td>
	<td width="75%">
		<input type="text" name="headline" size="64" maxlength="64" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_body ?>
	</td>
	<td>
		<textarea name="body" cols="60" rows="8" wrap="virtual"></textarea>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_announcement ?><br />
		<span class="small"><?php echo $s_stays_on_top ?></span>
	</td>
	<td>
		<input type="checkbox" name="announcement" />
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_view_status ?>
	</td>
	<td width="75%">
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state' ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_post_news_button ?>" />
	</td>
</tr>
</form>
</table>
</div>
<?php # Add News Form END ?>

<?php # Edit/Delete News Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="news_edit_page.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_edit_or_delete_news_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<input type="radio" name="action" value="edit" checked="checked" /> <?php echo $s_edit_post ?>
		<input type="radio" name="action" value="delete" /> <?php echo $s_delete_post ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo $s_select_post ?>
	</td>
	<td width="75%">
		<select name="news_id">
			<?php print_news_item_option_list() ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_submit_button ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Edit/Delete News Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
