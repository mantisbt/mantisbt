<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?php
	# This include file prints out the list of bugnotes attached to the bug
	# $f_id must be set and be set to the bug id
?>
<?php
	# grab the user id currently logged in
	$t_user_id = get_current_user_field( "id " );

	# get the bugnote data
	$query = "SELECT *,UNIX_TIMESTAMP(date_submitted) as date_submitted
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$f_id'
			ORDER BY date_submitted $g_bugnote_order";
	$result = db_query($query);
	$num_notes = db_num_rows($result);
?>

<?php # Bugnotes BEGIN ?>
<a name="bugnotes"><p>
<table class="width100" cellspacing="1">
<?php
	# no bugnotes
	if ( 0 == $num_notes ) {
?>
<tr>
	<td class="center" colspan="2">
		<?php echo $s_no_bugnotes_msg ?>
	</td>
</tr>
<?php } else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_bug_notes_title ?>
	</td>
</tr>
<?php
	for ( $i=0; $i < $num_notes; $i++ ) {
		# prefix all bugnote data with v3_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v3" );
		$v3_date_submitted = date( $g_normal_date_format, ( $v3_date_submitted ) );

		# grab the bugnote text and id and prefix with v3_
		$query = "SELECT note, id
				FROM $g_mantis_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_query( $query );
		$v3_note = db_result( $result2, 0, 0 );
		$v3_bugnote_text_id = db_result( $result2, 0, 1 );

		$v3_note = string_display( $v3_note );
?>
<tr>
	<td class="nopad" valign="top" width="25%">
		<table class="hide" cellspacing="1">
		<tr>
			<td class="category" colspan="2">
				<?php print_user( $v3_reporter_id ) ?>
			</td>
		</tr>
		<tr class="row-1">
			<td class="small-caption">
				<?php echo $v3_date_submitted ?>
			</td>
			<td class="small-caption">
			<?php
				# check access level
				# only admins and the bugnote creator can delete this bugnote
				# bug must be open to be editable
				if ( get_bug_field( $f_id, "status" ) < RESOLVED ) {
					if (( access_level_check_greater_or_equal( ADMINISTRATOR ) ) ||
						( $v3_reporter_id == $t_user_id )) {
						print_bracket_link( $g_bugnote_edit_page."?f_bugnote_text_id=".$v3_bugnote_text_id."&f_id=".$f_id."&f_bugnote_id=".$v3_id, $s_bugnote_edit_link );
						print_bracket_link( $g_bugnote_delete."?f_bugnote_id=".$v3_id."&f_id=".$f_id, $s_delete_link );
					}
				}
			?>
			</td>
		</tr>
		</table>
	</td>
	<td class="nopad" valign="top" width="75%">
		<table class="hide" cellspacing="1">
		<tr class="row-2">
			<td>
				<?php echo $v3_note ?>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="spacer">
		&nbsp;
	</td>
</tr>
<?php
		} # end for loop
	} # end else
?>
</table>
<?php # Bugnotes END ?>

<?php if ( ( ( $v_status < RESOLVED ) ||
		  ( isset( $f_resolve_note ) ) ) &&
		( access_level_check_greater_or_equal( REPORTER ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>
<p>
<table class="width100" cellspacing="1">
<form method="post" action="<?php echo $g_bugnote_add ?>">
<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
<tr>
	<td class="form-title">
		<?php echo $s_add_bugnote_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center">
		<textarea name="f_bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<?php echo $s_add_bugnote_button ?>">
	</td>
</tr>
</form>
</table>
<?php # Bugnote Add Form END ?>
<?php } ?>