<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.24 $
	# $Author: jfitzell $
	# $Date: 2002-08-25 21:04:56 $
	#
	# $Id: bug_update_page.php,v 1.24 2002-08-25 21:04:56 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Show the simple update bug options
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	if ( ADVANCED_ONLY == $g_show_update ) {
		print_header_redirect ( 'bug_update_advanced_page.php?f_id='.$f_id );
	}

	project_access_check( $f_id );
	check_access( $g_update_bug_threshold );
	check_bug_exists( $f_id );

	$c_id = (integer)$f_id;

	# grab data
    $query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted,
    		UNIX_TIMESTAMP(last_updated) as last_updated
    		FROM $g_mantis_bug_table
    		WHERE id='$c_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );
	
	# if bug is private, make sure user can view private bugs
	access_bug_check( $f_id, $v_view_state );

    $query = "SELECT *
    		FROM $g_mantis_bug_text_table
    		WHERE id='$v_bug_text_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v2' );

	# prepare strings for display
	$v_summary					= string_edit_text( $v_summary );
	$v2_description 			= string_edit_textarea( $v2_description );
	$v2_additional_information 	= string_edit_textarea( $v2_additional_information );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<form method="post" action="bug_update.php">
		<input type="hidden" name="f_id"                 value="<?php echo $v_id ?>">
		<input type="hidden" name="f_old_status"         value="<?php echo $v_status ?>">
		<input type="hidden" name="f_old_handler_id"     value="<?php echo $v_handler_id ?>">
		<?php echo $s_updating_bug_simple_title ?>
	</td>
	<td class="right" colspan="3">
<?php
	switch ( $g_show_view ) {
		case 0: print_bracket_link( 'view_bug_page.php?f_id='.$f_id, $s_back_to_bug_link );
				break;
		case 1: print_bracket_link( 'view_bug_page.php?f_id='.$f_id, $s_back_to_bug_link );
				break;
		case 2: print_bracket_link( 'view_bug_advanced_page.php?f_id='.$f_id, $s_back_to_bug_link );
				break;
	}

	if ( BOTH == $g_show_update ) {
		print_bracket_link( 'bug_update_advanced_page.php?f_id='.$f_id, $s_update_advanced_link );
	}
?>
	</td>
</tr>
<tr class="row-category">
	<td width="15%">
		<?php echo $s_id ?>
	</td>
	<td width="20%">
		<?php echo $s_category ?>
	</td>
	<td width="15%">
		<?php echo $s_severity ?>
	</td>
	<td width="20%">
		<?php echo $s_reproducibility ?>
	</td>
	<td width="15%">
		<?php echo $s_date_submitted ?>
	</td>
	<td width="15%">
		<?php echo $s_last_update ?>
	</td>
</tr>
<tr class="row-2">
	<td>
		<?php echo $v_id ?>
	</td>
	<td>
		<select name="f_category">
			<?php print_category_option_list( $v_category ) ?>
		</select>
	</td>
	<td>
		<select name="f_severity">
			<?php print_enum_string_option_list( 'severity', $v_severity ) ?>
		</select>
	</td>
	<td>
		<select name="f_reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $v_reproducibility ) ?>
		</select>
	</td>
	<td>
		<?php print_date( $g_normal_date_format, $v_date_submitted ) ?>
	</td>
	<td>
		<?php print_date( $g_normal_date_format, $v_last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_reporter ?>
	</td>
	<td>
		<select name="f_reporter_id">
			<?php print_reporter_option_list( $v_reporter_id ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo $s_view_status ?>
	</td>
	<td>
		<select name="f_view_state">
			<?php print_enum_string_option_list( 'view_state', $v_view_state) ?>
		</select>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_assigned_to ?>
	</td>
	<td colspan="5">
		<select name="f_handler_id">
			<option value="0"></option>
			<?php print_assign_to_option_list( $v_handler_id ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_priority ?>
	</td>
	<td>
		<select name="f_priority">
			<?php print_enum_string_option_list( 'priority', $v_priority ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo $s_resolution ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $v_resolution ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_status ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $v_status ) ?>">
		<select name="f_status">
			<?php print_enum_string_option_list( 'status', $v_status ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo $s_duplicate_id ?>
	</td>
	<td>
		<?php echo $v_duplicate_id ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_summary ?>
	</td>
	<td colspan="5">
		<input type="text" name="f_summary" size="80" maxlength="128" value="<?php echo $v_summary ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_description" wrap="virtual"><?php echo $v2_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_additional_information ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_additional_information" wrap="virtual"><?php echo $v2_additional_information ?></textarea>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_add_bugnote_title ?>
	</td>
	<td colspan="5">
		<textarea name="f_bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<?php if ( access_level_check_greater_or_equal( $g_private_bugnote_threshold ) ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo $s_private ?>
	</td>
	<td>
		<input type="checkbox" name="f_private">
	</td>
</tr>
<?php } ?>
<tr>
	<td class="center" colspan="6">
		<input type="submit" value="<?php echo $s_update_information_button ?>">
		</form>
	</td>
</tr>
</table>

<?php print_page_bot1( __FILE__ ) ?>
