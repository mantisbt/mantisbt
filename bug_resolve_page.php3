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
	project_access_check( $f_id );
	check_access( UPDATER );
	check_bug_exists( $f_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Resolve Form BEGIN ?>
<p>
<div align="center">
<table class="width50" cellspacing="0">
<form method="post" action="<?php echo $g_bug_resolve_page2 ?>">
<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_resolve_bug_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_resolution ?>
	</td>
	<td>
		<select name="f_resolution">
			<?php print_enum_string_option_list( $s_resolution_enum_string, FIXED ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_duplicate_id ?>
	</td>
	<td>
		<input type="text" name="f_duplicate_id" maxlength="7">
	</td>
</tr>
<?php if ( ON == $g_allow_close_immediately ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo $s_close_immediately ?>
	</td>
	<td>
		<input type="checkbox" name="f_close_now">
	</td>
</tr>
<?php } ?>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_resolve_bug_button ?>">
	</td>
</tr>
</form>
</table>
</div>
<?php # Resolve Form END ?>

<?php print_page_bot1( __FILE__ ) ?>