<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# <SQLI>
	# This page allows the close / suppress / others mass treatments, and display the adequate page
?>


<?php include( 'core_API.php' ) ?>
<? login_cookie_check() ?>

<?php
	# the pencil shortcut, to directly edit bugs
	if ( $QUERY_STRING!='' ) {
		$f_qsValues=split('&', $QUERY_STRING);
		foreach($f_qsValues as $value) {
			$val=split('=', $value);	
			if ( substr($val[0], 0, 6)=='update' ) {
				print_meta_redirect( 'bug_update_advanced_page.php?f_id='.substr($val[0], 7, strlen($val[0])-9), 0 );
			}
		}
	}
	
	# redirects to all_bug_page if nothing is selected
	if (( $f_action=='') or count($f_bug_arr)==0 ) {
		print_meta_redirect( 'view_all_bug_page.php',0);
		exit;
	}

	$t_finished = 0;

	switch ($f_action)  {

		case 'CLOSE' :
		# Use a simple confirmation page, if close or delete...
			$t_finished = 1 ;
			$t_question_title = $s_close_bugs_conf_msg ; 
			$t_button_title = $s_close_group_bugs_button ;
			break ;

		
		case 'DELETE' :
			$t_finished = 1;
			$t_question_title = $s_delete_bugs_conf_msg ; 
			$t_button_title = $s_delete_group_bugs_button ;
			break ;


		# ...else we define the variables used in the form
		case 'MOVE' :
			$t_question_title = $s_move_bugs_conf_msg ; 
			$t_button_title = $s_move_group_bugs_button ;
			$t_form='f_project_id' ;
			break ;

		
		case 'ASSIGN' :
			$t_question_title = $s_assign_bugs_conf_msg ; 
			$t_button_title = $s_assign_group_bugs_button ;
			$t_form = 'f_assign' ;
			break ;

		case 'RESOLVE' :
			$t_question_title = $s_resolve_bugs_conf_msg ; 
			$t_button_title = $s_resolve_group_bugs_button ;
			$t_form = 'f_resolution' ;
			$t_request = 'resolution'; // the "request" vars allow to display the adequate list
			break ;

		
		case 'UP_PRIOR' :
			$t_question_title = $s_priority_bugs_conf_msg ; 
			$t_button_title = $s_priority_group_bugs_button ;
			$t_request = 'priority';
			$t_form = 'f_priority' ;
			break ;

		case 'UP_STATUS' :
			$t_question_title = $s_status_bugs_conf_msg ; 
			$t_button_title = $s_status_group_bugs_button ;
			$t_form = 'f_status' ;
			$t_request = 'status';
			break ; 
	}
?>

<?php print_page_top1() ?>
<?php print_page_top2() ?>



<?php  # displays the choices popup menus
	if ($t_finished != 1) {  
?>
<p>
<div align="center">
<table class="width75" cellspacing="1">

<form method="POST" action="bug_actiongroup.php">
<input type="hidden" name="f_actionconfirmed" value="1">
<input type="hidden" name="f_action" value="<?php echo $f_action ?>">

<?php
	foreach($f_bug_arr as $value) {
		print "<input type=\"hidden\" name=\"f_bug_arr[]\" value=\"$value\">";
	}
?>	 

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
		<input type="submit" value="<?php echo $t_button_title ?>"> 
	</td>
</tr>

</form>
</table>
</div>
<?php # Choices Form END ?>


<?php 
} 
# else, asks for a simple confirmation to close or delete
else { 
?>

<p>
<div align="center">
	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
	<?php echo $t_question_title ?>

	<form method="post" action="bug_actiongroup.php">
		<input type="hidden" name="f_actionconfirmed" value="1">
		<input type="hidden" name="f_action" value="<?php echo $f_action ?>">

	<?php
		foreach($f_bug_arr as $value)  
			print "<input type=\"hidden\" name=\"f_bug_arr[]\" value=\"$value\">"; 
	?>	

		<input type="submit" value="<?php echo $t_button_title ?>"> 
	</form>

	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
</div>

<?php
}
?>


<?php print_page_bot1( __FILE__ ) ?>