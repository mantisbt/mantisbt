<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_reopen_page.php,v 1.23 2003-01-23 23:02:55 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'bug_api.php' );
	require_once( $g_core_path . 'project_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );

	project_access_check( $f_bug_id );
	if ( OFF == config_get( 'allow_reporter_reopen' )
		 || auth_get_current_user_id() != bug_get_field( $f_bug_id, 'reporter_id' ) ) {
		check_access( config_get( 'reopen_bug_threshold' ) );
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<form method="post" action="bug_reopen.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'reopen_add_bugnote_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'reopen_bug_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />
<?php include( config_get( 'bug_view_inc' ) ) ?>

<?php print_page_bot1( __FILE__ ) ?>
