<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page allows the user to edit his/her profile
	# Changes get POSTed to account_prof_update.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# get protected state
	$t_protected = current_user_get_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	$c_user_id = (integer)current_user_get_field( 'id' );
	$c_id = (integer)$f_id;

	# If deleteing profile redirect to delete script
	if ( 'delete' == $f_action) {
		print_header_redirect( 'account_prof_delete.php?f_id='.$f_id );
	}
	# If Defaulting profile redirect to make default script
	else if ( 'make default' == $f_action ) {
		print_header_redirect( 'account_prof_make_default.php?f_id='.$f_id.'&amp;f_user_id='.$f_user_id );
	}

	# Retrieve new item data and prefix with v_
	$query = "SELECT *
		FROM $g_mantis_user_profile_table
		WHERE id='$c_id' AND user_id='$c_user_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, 'v' );
    }

	# Prepare for edit display
   	$v_platform 	= string_edit_text( $v_platform );
   	$v_os 			= string_edit_text( $v_os );
   	$v_os_build 	= string_edit_text( $v_os_build );
   	$v_description  = string_edit_textarea( $v_description );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Edit Profile Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" action="account_prof_update.php">
		<input type="hidden" name="f_id" value="<?php echo $v_id ?>">
		<?php echo $s_edit_profile_title ?>
	</td>
	<td class="right">
		<?php print_account_menu() ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_platform ?>
	</td>
	<td width="75%">
		<input type="text" name="f_platform" size="32" maxlength="32" value="<?php echo $v_platform ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_operating_system ?>
	</td>
	<td>
		<input type="text" name="f_os" size="32" maxlength="32" value="<?php echo $v_os ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_version ?>
	</td>
	<td>
		<input type="text" name="f_os_build" size="16" maxlength="16" value="<?php echo $v_os_build ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_additional_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="8" wrap="virtual"><?php echo $v_description ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_update_profile_button ?>">
		</form>
	</td>
</tr>
</table>
</div>
<?php # Edit Profile Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
