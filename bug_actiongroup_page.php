<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_actiongroup_page.php,v 1.43 2004-09-08 02:13:58 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows actions to be performed on an array of bugs

	require_once( 'core.php' );

	auth_ensure_user_authenticated(); 

	$f_action = gpc_get_string( 'action', '' );
	$f_bug_arr = gpc_get_int_array( 'bug_arr', array() );

	# redirects to all_bug_page if nothing is selected
	if ( ( $f_action=='' ) || 0 == sizeof( $f_bug_arr ) ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}
	$c_project_id = helper_get_current_project();

	$t_finished = false;
	$t_request = '';
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

		case 'COPY' :
			$t_question_title 		= lang_get( 'copy_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'copy_group_bugs_button' );
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
			if ( ALL_PROJECTS != $c_project_id ) {
				$t_question_title2 = lang_get( 'fixed_in_version' );
				$t_form2 = 'fixed_in_version';
			}
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

		case 'VIEW_STATUS':
			$t_question_title		= lang_get( 'view_status_bugs_conf_msg' );
			$t_button_title			= lang_get( 'view_status_group_bugs_button' );
			$t_form				= 'view_status';
			$t_request			= 'view_status';
			break;

		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}

	html_page_top1();
	html_page_top2();
?>

<br />

<div align="center">
<form method="POST" action="bug_actiongroup.php">
<input type="hidden" name="action" value="<?php echo string_attribute( $f_action ) ?>" />
<table class="width75" cellspacing="1">
<?php 

$t_bug_rows = "";
$t_i = 1;

foreach( $f_bug_arr as $t_bug_id ) { 
	$t_class = sprintf( "row-%d", ($t_i++ % 2) + 1 );
	$t_bug_rows .= sprintf( "<tr class=\"%s\"> <td>%s</td> <td>%s</td> </tr>\n"
		, $t_class, string_get_bug_view_link( $t_bug_id ), bug_get_field( $t_bug_id, 'summary' ) 
    );
	echo '<input type="hidden" name="bug_arr[]" value="' . $t_bug_id . '" />' . "\n";
}
?>

<?php
if ( !$t_finished ) {
?>
<tr class="row-1">
	<td class="category">
		<?php echo $t_question_title ?>
	</td>
	<td>
		<select name="<?php echo $t_form ?>">
			<?php
				switch ( $f_action ) {
					case 'COPY':
					case 'MOVE':
						print_project_option_list( null, false );
						break;
					case 'ASSIGN':
						$t_new_status = ( ON == config_get( 'auto_set_status_to_assigned' ) ) ? config_get( 'bug_assigned_status' ) : NEW_;
						$t_assign_threshold = access_get_status_threshold( $t_new_status, $c_project_id );
						# threshold is correct if auto_set_status_to_assigned is set, false thresholds will be caught when the bug is assigned
						print_assign_to_option_list( 0, $c_project_id, $t_assign_threshold);
						break;
					case 'VIEW_STATUS':
						print_enum_string_option_list( 'view_state', config_get( 'default_bug_view_status' ) );
						break;
				}

				#other forms use the same function to display the list
				if ( $t_request > '' ) {
					print_enum_string_option_list( $t_request, FIXED );
				}
			?>
		</select>
	</td>
</tr>
	<?php
	if ( isset( $t_question_title2 ) ) {
		switch ( $f_action ) {
			case 'RESOLVE':
				$t_show_version = ( ON == config_get( 'show_product_version' ) ) 
					|| ( ( AUTO == config_get( 'show_product_version' ) ) 
								&& ( count( version_get_all_rows( $c_project_id ) ) > 0 ) );
				if ( $t_show_version ) { 
	?>
		<tr class="row-2">
			<td class="category">
				<?php echo $t_question_title2 ?>
			</td>
			<td>
				<select name="<?php echo $t_form2 ?>">
					<?php print_version_option_list( '', null, VERSION_ALL );?>
				</select>
			</td>
		</tr>
	<?php
				}
				break;
		}			
	}
	?>
<?php
} else {
?>

<tr class="row-1">
	<td class="category" colspan="2">
		<?php echo $t_question_title; ?>
	</td>
</tr>
<?php
}
?>

<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo $t_button_title ?>" />
	</td>
</tr>
</table>

<br />

<table class="width75" cellspacing="1">
<tr class="row-1">
	<td class="category" colspan="2">
		<?php echo lang_get( 'actiongroup_bugs' ); ?>
	</td>
</tr>
<?php
	echo $t_bug_rows;
?>
</table>
</form>
</div>


<?php
	html_page_bottom1( __FILE__ );
?>
