<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );


	$query = "SELECT *
			FROM $g_mantis_project_file_table
			WHERE id='$f_id'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );

	$v_title		= string_edit_text( $v_title );
	$v_description 	= string_edit_textarea( $v_description );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<form method="post" action="<?php echo $g_proj_doc_update ?>">
<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
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
		<input type="text" name="f_title" size="70" maxlength="250" value="<?php echo $v_title ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="7" wrap="virtual"><?php echo $v_description ?></textarea>
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
	<td class="left">
		<input type="submit" value="<?php echo $s_file_update_button ?>">
	</td>
	</form>
	<form method="post" action="<?php echo $g_proj_doc_delete_page ?>">
	<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
	<td class="right">
		<input type="submit" value="<?php echo $s_file_delete_button ?>">
	</td>
	</form>
</tr>
</table>
</form>
</div>

<?php print_page_bot1( __FILE__ ) ?>