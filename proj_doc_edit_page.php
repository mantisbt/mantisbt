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

	$f_file_id = gpc_get_int( 'file_id' );

	$c_file_id = (integer)$f_file_id;

	$query = "SELECT *
			FROM $g_mantis_project_file_table
			WHERE id='$c_file_id'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

	$v_title		= string_edit_text( $v_title );
	$v_description 	= string_edit_textarea( $v_description );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<form method="post" action="proj_doc_update.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="file_id" value="<?php echo $f_file_id ?>" />
		<?php echo $s_upload_file_title ?>
	</td>
	<td class="right">
		<?php print_doc_menu() ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="20%">
		<?php echo $s_title ?>
	</td>
	<td width="80%">
		<input type="text" name="title" size="70" maxlength="250" value="<?php echo $v_title ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="7" wrap="virtual"><?php echo $v_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_filename ?>
	</td>
	<td>
		<?php echo $v_filename ?>
	</td>
</tr>
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type="submit" value="<?php echo $s_file_update_button ?>" />
	</td>
</tr>
</table>
</form>

<br />

		<form method="post" action="proj_doc_delete.php">
		<input type="hidden" name="file_id" value="<?php echo $f_file_id ?>" />
		<input type="submit" value="<?php echo $s_file_delete_button ?>" />
		</form>

</div>

<?php print_page_bot1( __FILE__ ) ?>
