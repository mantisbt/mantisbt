<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_reminder_page.php,v 1.8 2003-02-11 09:08:34 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'bug_reminder_threshold' ) );
	bug_ensure_exists( $f_bug_id );
	
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Send reminder Form BEGIN ?>
<br />
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="bug_reminder.php">
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'bug_reminder' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'to' ) ?>
	</td>
	<td>
		<select name="to[]" multiple size="10">
			<?php echo print_project_user_option_list() ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="center" colspan="2">
		<textarea name="body" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'bug_send_button' ) ?>">
	</td>
</tr>
</form>
</table>
</div>
<?php # Send reminder Form END ?>
</form>
</table>

<br />
<?php include( config_get( 'bug_view_inc' ) ) ?>

<?php print_page_bot1( __FILE__ ) ?>
