<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# <SQLI>
	# This page allows the close / suppress / others mass treatments, and display the adequate page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_action = gpc_get_string( 'f_action', '' );
	$f_bug_arr = gpc_get_int_array( 'f_bug_arr', array() );

	# redirects to all_bug_page if nothing is selected
	if ( ( $f_action=='' ) || 0 == sizeof( $f_bug_arr ) ) {
		print_meta_redirect( 'view_all_bug_page.php', 0 );
		exit;
	}

	$t_finished = false;
	switch ( $f_action )  {
		case 'CLOSE' :
		# Use a simple confirmation page, if close or delete...
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
			$t_form					= 'f_project_id';
			break;
		case 'ASSIGN' :
			$t_question_title 		= lang_get( 'assign_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'assign_group_bugs_button' );
			$t_form 				= 'f_assign';
			break;

		case 'RESOLVE' :
			$t_question_title 		= lang_get( 'resolve_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'resolve_group_bugs_button' );
			$t_form 				= 'f_resolution';
			$t_request 				= 'resolution'; # the "request" vars allow to display the adequate list
			break;
		case 'UP_PRIOR' :
			$t_question_title 		= lang_get( 'priority_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'priority_group_bugs_button' );
			$t_form 				= 'f_priority';
			$t_request 				= 'priority';
			break;
		case 'UP_STATUS' :
			$t_question_title 		= lang_get( 'status_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'status_group_bugs_button' );
			$t_form 				= 'f_status';
			$t_request 				= 'status';
			break;
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>
<?php  # displays the choices popup menus
	if ( ! $t_finished ) {
?>
<br />
<div align="center">
<form method="POST" action="bug_actiongroup.php">
<input type="hidden" name="f_action" value="<?php echo $f_action ?>" />
<table class="width75" cellspacing="1">
<?php foreach( $f_bug_arr as $t_bug_id ) { ?>
		<input type="hidden" name="f_bug_arr[]" value="<?php echo $t_bug_id ?>" />
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
						print_project_option_list();
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
		<input type="hidden" name="f_action" value="<?php echo $f_action ?>" />

	<?php foreach( $f_bug_arr as $value ) { ?>
		<input type="hidden" name="f_bug_arr[]" value="<?php echo $value ?>" />
	<?php } ?>

		<input type="submit" value="<?php echo $t_button_title ?>" />
	</form>

	<?php print_hr() ?>
</div>
<?php
	}
?>
<?php print_page_bot1( __FILE__ ) ?>
