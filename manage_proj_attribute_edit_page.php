<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php
	print_manage_menu( 'manage_proj_attribute_edit_page.php' );

	check_varset( $f_assigned_to, '0' );
?>

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
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_project_title' ).$t_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<form method="post" action="manage_proj_attribute_update.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="hidden" name="f_orig_attribute" value="<?php echo $f_attribute ?>" />
		<input type="hidden" name="f_parameter" value="<?php echo $f_parameter ?>" />
		<?php echo $t_title ?>
	</td>
	<td>
		<input type="text" name="f_attribute" size="32" value="<?php echo urldecode( stripslashes( $f_attribute ) ) ?>" />
	</td>
</tr>
<?php if ($f_parameter == lang_get( 'states' )) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'asso_color' ) ?>
	</td>
	<td>
		<select name="f_color">
		<?php print_custom_status_color_list() ?>
		</select>
	</td>
</tr>
	<?php } #if color ?>

<tr>
	<td class="left" width="50%">
		<input type="submit" value="<?php echo lang_get( 'update_button' ).' '.$t_title ?>" />
		</form>
	</td>
	<td class="right" width="50%">
		<form method="post" action="manage_proj_attribute_del_page.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="hidden" name="f_attribute" value="<?php echo $f_attribute ?>" />
		<input type="hidden" name="f_parameter" value="<?php echo $f_parameter ?>" />
		<input type="submit" value="<?php echo lang_get( 'delete_button' ).' '.$t_title ?>" />
		</form>
	</td>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
