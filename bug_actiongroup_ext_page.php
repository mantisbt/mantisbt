<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_actiongroup_ext_page.php,v 1.2 2007-04-14 03:00:28 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	require_once( $t_core_path.'bug_group_action_api.php' );

	auth_ensure_user_authenticated();

	$f_action = gpc_get_string( 'action' );
	$f_bug_arr = gpc_get_int_array( 'bug_arr', array() );

	# redirect to view issues if nothing is selected
	if ( is_blank( $f_action ) || ( 0 == sizeof( $f_bug_arr ) ) ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}

  # redirect to view issues page if action doesn't have ext_* prefix.
  # This should only occur if this page is called directly.
	$t_external_action_prefix = 'EXT_';
	if ( strpos( $f_action, $t_external_action_prefix ) !== 0 ) {
		print_header_redirect( 'view_all_bug_page.php' );
  }

	$t_external_action = strtolower( substr( $f_action, strlen( $t_external_action_prefix ) ) );
	$t_form_fields_page = 'bug_actiongroup_' . $t_external_action . '_inc.php';

	bug_group_action_print_top();
?>

	<br />

	<div align="center">
	<form method="post" action="bug_actiongroup_ext.php">
		<input type="hidden" name="action" value="<?php echo string_attribute( $t_external_action ) ?>" />
		<input type="hidden" name="action" value="<?php echo string_attribute( $t_external_action ) ?>" />
<table class="width75" cellspacing="1">
	<?php
		bug_group_action_print_title( $t_external_action );
		bug_group_action_print_hidden_fields( $f_bug_arr );
		bug_group_action_print_action_fields( $t_external_action );
	?>
</table>
	</form>
	</div>

	<br />

<?php
	bug_group_action_print_bug_list( $f_bug_arr );
	bug_group_action_print_bottom();
?>
