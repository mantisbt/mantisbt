<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	# If deleting item redirect to delete script
	if ( 'delete' == $f_action ) {
		print_header_redirect( 'news_delete_page.php?f_id='.$f_id );
		exit;
	}

	# Retrieve news item data and prefix with v_
	$row = news_select_query( $f_id );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, 'v' );
    }

   	$v_headline = string_edit_text( $v_headline );
   	$v_body 	= string_edit_textarea( $v_body );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Edit News Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" action="news_update.php">
		<input type="hidden" name="f_id" value="<?php echo $v_id ?>">
		<?php echo $s_headline ?>
	</td>
	<td class="right">
		<?php print_bracket_link( 'news_menu_page.php', $s_go_back ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_headline ?><br />
		<span class="small"><?php echo $s_do_not_use ?> "</span>
	</td>
	<td width="75%">
		<input type="text" name="f_headline" size="64" maxlength="64" value="<?php echo $v_headline ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_body ?>
	</td>
	<td>
		<textarea name="f_body" cols="60" rows="10" wrap="virtual"><?php echo $v_body ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_post_to ?>
	</td>
	<td>
		<select name="f_project_id">
		<?php
			if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
				PRINT '<option value="0000000">Sitewide</option>';
			}
			print_project_option_list( $v_project_id );
		?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_announcement ?><br />
		<span class="small"><?php echo $s_stays_on_top ?></span>
	</td>
	<td>
		<input type="checkbox" name="f_announcement" <?php if ( 1 == $v_announcement ) { echo 'CHECKED'; } ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_view_status ?>
	</td>
	<td width="75%">
		<select name="f_view_state">
			<?php print_enum_string_option_list( 'view_state', $v_view_state ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_update_news_button ?>">
		</form>
	</td>
</tr>
</table>
</div>
<?php # Edit News Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
