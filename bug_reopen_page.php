<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_reopen_page.php,v 1.18 2002-10-23 04:54:44 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id		= gpc_get_int( 'f_bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'reopen_bug_threshold ' ) );
	bug_ensure_exists( $f_bug_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<form method="post" action="bug_reopen.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'reopen_add_bugnote_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea name="f_bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
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

<?php include( config_get( 'bug_view_inc' ) ) ?>

<?php print_page_bot1( __FILE__ ) ?>
