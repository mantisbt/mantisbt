<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_edit_page.php,v 1.37 2003-02-11 09:08:40 jfitzell Exp $
	# --------------------------------------------------------

	# CALLERS
	#	This page is submitted to by the following pages:
	#	- bugnote_inc.php

	# EXPECTED BEHAVIOUR
	#	Allow the user to modify the text of a bugnote, then submit to
	#	bugnote_update.php with the new text

	# RESTRICTIONS & PERMISSIONS
	#	- none beyond API restrictions
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_bugnote_id = gpc_get_int( 'bugnote_id' );

	bugnote_ensure_exists( $f_bugnote_id );
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	project_access_check( $t_bug_id );
	bug_ensure_exists( $t_bug_id );

	# Check if the bug has been resolved
	if ( bug_get_field( $t_bug_id, 'status' ) >= config_get( 'bug_resolved_status_threshold' ) ) {
		# @@@ The error should be more generic.
		trigger_error( ERROR_BUG_RESOLVED_ACTION_DENIED, ERROR );
	}

	# make sure the user accessing the note is valid and has proper access
	$t_bugnote_user_id	= bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( ! access_level_check_greater_or_equal( config_get( 'update_bugnote_threshold' ) ) ) &&
		 ( $t_bugnote_user_id != auth_get_current_user_id() ) ) {
		access_denied();
	}

	$t_bugnote_text = string_textarea( bugnote_get_text( $f_bugnote_id ) );

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $t_bug_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<form method="post" action="bugnote_update.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="bugnote_id" value="<?php echo $f_bugnote_id ?>" />
		<?php echo lang_get( 'edit_bugnote_title' ) ?>
	</td>
	<td class="right">
		<?php print_bracket_link( $t_redirect_url, lang_get( 'go_back' ) ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea cols="80" rows="10" name="bugnote_text" wrap="virtual"><?php echo $t_bugnote_text ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'update_information_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php print_page_bot1( __FILE__ ) ?>
