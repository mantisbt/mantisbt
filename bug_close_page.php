<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_close_page.php,v 1.23 2003-01-25 21:13:15 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'close_bug_threshold' ) );
	bug_ensure_exists( $f_bug_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Close Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="bug_close.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'close_bug_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'close_bug_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Close Form END ?>

<br />
<?php include( config_get( 'bug_view_inc' ) ) ?>

<?php print_page_bot1( __FILE__ ) ?>
