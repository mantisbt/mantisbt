<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path . 'file_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	if ( ! file_allow_project_upload() ) {
		access_denied();
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<form method="post" enctype="multipart/form-data" action="proj_doc_add.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo $s_upload_file_title ?>
	</td>
	<td class="right">
		<?php print_doc_menu( 'proj_doc_add_page.php' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_title ?>
	</td>
	<td width="75%">
		<input type="text" name="title" size="70" maxlength="250" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="7" wrap="virtual"></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_select_file ?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<?php echo $g_max_file_size ?>" />
		<input name="file" type="file" size="70" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_upload_file_button ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php print_page_bot1( __FILE__ ) ?>
