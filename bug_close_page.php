<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_close_page.php,v 1.31 2004-04-12 21:04:35 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	access_ensure_can_close_bug( $f_bug_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

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
		<input type="submit" class="button" value="<?php echo lang_get( 'close_bug_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Close Form END ?>

<br />
<?php include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' ) ?>
<?php include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' ) ?>

<?php html_page_bottom1( __FILE__ ) ?>
