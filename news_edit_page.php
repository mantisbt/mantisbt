<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php
	$f_news_id = gpc_get_int( 'news_id' );
	$f_action = gpc_get_string( 'action', '' );

	# If deleting item redirect to delete script
	if ( 'delete' == $f_action ) {
		print_header_redirect( 'news_delete.php?news_id='.$f_news_id );
	}

	# Retrieve news item data and prefix with v_
	$row = news_get_row( $f_news_id );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, 'v' );
    }

	access_ensure_project_level( config_get( 'manage_news_threshold' ), $v_project_id );

   	$v_headline = string_attribute( $v_headline );
   	$v_body 	= string_textarea( $v_body );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Edit News Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="news_update.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="news_id" value="<?php echo $v_id ?>" />
		<?php echo $s_headline ?>
	</td>
	<td class="right">
		<?php print_bracket_link( 'news_menu_page.php', $s_go_back ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_headline ?>
	</td>
	<td width="75%">
		<input type="text" name="headline" size="64" maxlength="64" value="<?php echo $v_headline ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_body ?>
	</td>
	<td>
		<textarea name="body" cols="60" rows="10" wrap="virtual"><?php echo $v_body ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_post_to ?>
	</td>
	<td>
		<select name="project_id">
		<?php
			$t_sitewide = false;
			if ( access_has_project_level( ADMINISTRATOR ) ) {
				$t_sitewide = true;
			}
			print_project_option_list( $v_project_id, $t_sitewide );
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
		<input type="checkbox" name="announcement" <?php check_checked( $v_announcement, 1 ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_view_status ?>
	</td>
	<td width="75%">
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state', $v_view_state ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_update_news_button ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Edit News Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
