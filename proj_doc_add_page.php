<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" enctype="multipart/form-data" action="<?php echo $g_proj_doc_add ?>">
		<?php echo $s_upload_file_title ?>
	</td>
	<td class="right">
		<?php print_doc_menu( $g_proj_doc_add_page ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_title ?>
	</td>
	<td width="75%">
		<input type="text" name="f_title" size="70" maxlength="250">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="7" wrap="virtual"></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_select_file ?>
	</td>
	<td>
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="hidden" name="max_file_size" value="5000000">
		<input name="f_file" type="file" size="70">
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_upload_file_button ?>">
		</form>
	</td>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>