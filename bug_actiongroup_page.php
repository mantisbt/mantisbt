<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_actiongroup_page.php,v 1.30 2003-02-18 02:18:00 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows actions to be performed an an array of bugs
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_action = gpc_get_string( 'action', '' );
	$f_bug_arr = gpc_get_int_array( 'bug_arr', array() );

	# redirects to all_bug_page if nothing is selected
	if ( ( $f_action=='' ) || 0 == sizeof( $f_bug_arr ) ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}

	$t_finished = false;
	switch ( $f_action )  {
		# Use a simple confirmation page, if close or delete...
		case 'CLOSE' :
			$t_finished 			= true;
			$t_question_title 		= lang_get( 'close_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'close_group_bugs_button' );
			break;

		case 'DELETE' :
			$t_finished 			= true;
			$t_question_title		= lang_get( 'delete_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'delete_group_bugs_button' );
			break;

		# ...else we define the variables used in the form
		case 'MOVE' :
			$t_question_title 		= lang_get( 'move_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'move_group_bugs_button' );
			$t_form					= 'project_id';
			break;

		case 'ASSIGN' :
			$t_question_title 		= lang_get( 'assign_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'assign_group_bugs_button' );
			$t_form 				= 'assign';
			break;

		case 'RESOLVE' :
			$t_question_title 		= lang_get( 'resolve_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'resolve_group_bugs_button' );
			$t_form 				= 'resolution';
			$t_request 				= 'resolution'; # the "request" vars allow to display the adequate list
			break;

		case 'UP_PRIOR' :
			$t_question_title 		= lang_get( 'priority_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'priority_group_bugs_button' );
			$t_form 				= 'priority';
			$t_request 				= 'priority';
			break;

		case 'UP_STATUS' :
			$t_question_title 		= lang_get( 'status_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'status_group_bugs_button' );
			$t_form 				= 'status';
			$t_request 				= 'status';
			break;
	}
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>
<?php  # displays the choices popup menus
	if ( ! $t_finished ) {
?>
<br />
<div align="center">
<form method="POST" action="bug_actiongroup.php">
<input type="hidden" name="action" value="<?php echo $f_action ?>" />
<table class="width75" cellspacing="1">
<?php foreach( $f_bug_arr as $t_bug_id ) { ?>
		<input type="hidden" name="bug_arr[]" value="<?php echo $t_bug_id ?>" />
<?php } ?>
<tr class="row-1">
	<td class="category">
		<?php echo $t_question_title ?>
	</td>
	<td>
		<select name="<?php echo $t_form ?>">
			<?php
				switch ( $f_action ) {
					case 'MOVE':
						print_project_option_list( null, false );
						break;
					case 'ASSIGN':
						print_assign_to_option_list();
						break;
				}

				#other forms use the same function to display the list
				print_enum_string_option_list( $t_request, FIXED );
			?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $t_button_title ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Choices Form END ?>
<?php
	} else {
		# else, asks for a simple confirmation to close or delete
?>
<br />
<div align="center">
	<?php print_hr() ?>
	<?php echo $t_question_title ?>

	<form method="post" action="bug_actiongroup.php">
		<input type="hidden" name="action" value="<?php echo $f_action ?>" />

	<?php foreach( $f_bug_arr as $value ) { ?>
		<input type="hidden" name="bug_arr[]" value="<?php echo $value ?>" />
	<?php } ?>

		<input type="submit" value="<?php echo $t_button_title ?>" />
	</form>

	<?php print_hr() ?>
</div>
<?php
	}
?>
<?php html_page_bottom1( __FILE__ ) ?>
