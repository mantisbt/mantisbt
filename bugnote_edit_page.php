<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Remove the bugnote and bugnote text and redirect back to
	# the viewing page
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	#check_access( UPDATER );

	# grab the bugnote text
  	$query = "SELECT note
			FROM $g_mantis_bugnote_text_table
			WHERE id='$f_bugnote_text_id'";
	$result = db_query( $query );
	$f_bugnote_text = db_result( $result, 0, 0 );
	$f_bugnote_text = string_edit_textarea( $f_bugnote_text );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<?php echo $g_bugnote_update ?>">
<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
<input type="hidden" name="f_bugnote_text_id" value="<?php echo $f_bugnote_text_id ?>">
<tr>
	<td class="form-title">
		<?php echo $s_edit_bugnote_title ?>
	</td>
	<td class="right">
		<?php print_bracket_link( $t_redirect_url, $s_go_back ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea cols="80" rows="10" name="f_bugnote_text" wrap="virtual"><?php echo $f_bugnote_text ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_update_information_button ?>">
	</td>
</tr>
</form>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>