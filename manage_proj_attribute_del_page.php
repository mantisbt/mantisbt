<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # attribute definition
	  # complete this switch to add new attributes
	$t_title = '';
	switch ($f_parameter) {
		case 'priorities' :
			$t_title = lang_get( 'priority' ) ;
			break;
		case 'severities' :
			$t_title = lang_get( 'severity' ) ;
			break;
		case 'reproducibilities' :
			$t_title = lang_get( 'reproducibility' ) ;
			break;
		case 'states':
			$t_title = lang_get( 'status' ) ;
			break;
		case 'resolutions' :
			$t_title = lang_get( 'resolution' ) ;
			break;
		case 'projections' :
			$t_title = lang_get( 'projections' ) ;
			break;
		case 'etas' :
			$t_title = lang_get( 'eta' ) ;
			break;
	} // end switch
?>

<br />
<div align="center">
	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
	<?php echo lang_get( 'attribute_delete_sure_msg' ) ?>

	<form method="get" action="manage_proj_attribute_delete.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="hidden" name="f_attribute" value="<?php echo $f_attribute ?>" />
		<input type="hidden" name="f_parameter" value="<?php echo $f_parameter ?>" />
		<input type="submit" value="<?php echo lang_get( 'delete_button' ).' '.$t_title ?>" />
	</form>

	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
