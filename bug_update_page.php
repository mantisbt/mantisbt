<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_update_page.php,v 1.35 2002-10-29 08:30:22 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Show the simple update bug options
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );

	if ( ADVANCED_ONLY == config_get( 'show_update' ) ) {
		print_header_redirect ( 'bug_update_advanced_page.php?f_bug_id='.$f_bug_id );
	}

	project_access_check( $f_bug_id );
	check_access( config_get( 'update_bug_threshold' ) );
	bug_ensure_exists( $f_bug_id );

	# if bug is private, make sure user can view private bugs
	access_bug_check( $f_bug_id );

	$t_bug = bug_prepare_edit( bug_get( $f_bug_id ) );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<form method="post" action="bug_update.php">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<input type="hidden" name="f_bug_id"                 value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="f_old_status"         value="<?php echo $t_bug->status ?>" />
		<input type="hidden" name="f_old_handler_id"     value="<?php echo $t_bug->handler_id ?>" />
		<?php echo lang_get( 'updating_bug_simple_title' ) ?>
	</td>
	<td class="right" colspan="3">
<?php
	print_bracket_link( string_get_bug_view_url( $f_bug_id ), lang_get( 'back_to_bug_link' ) );

	if ( BOTH == config_get( 'show_update' ) ) {
		print_bracket_link( 'bug_update_advanced_page.php?f_bug_id=' . $f_bug_id, lang_get( 'update_advanced_link' ) );
	}
?>
	</td>
</tr>
<tr class="row-category">
	<td width="15%">
		<?php echo lang_get( 'id' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'severity' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'reproducibility' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'date_submitted' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'last_update' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td>
		<?php echo bug_format_id( $f_bug_id ) ?>
	</td>
	<td>
		<select name="f_category">
			<?php print_category_option_list( $t_bug->category ) ?>
		</select>
	</td>
	<td>
		<select name="f_severity">
			<?php print_enum_string_option_list( 'severity', $t_bug->severity ) ?>
		</select>
	</td>
	<td>
		<select name="f_reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $t_bug->reproducibility ) ?>
		</select>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) ?>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<select name="f_reporter_id">
			<?php print_reporter_option_list( $t_bug->reporter_id ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="f_view_state">
			<?php print_enum_string_option_list( 'view_state', $t_bug->view_state) ?>
		</select>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<select name="f_handler_id">
			<option value="0"></option>
			<?php print_assign_to_option_list( $t_bug->handler_id ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td>
		<select name="f_priority">
			<?php print_enum_string_option_list( 'priority', $t_bug->priority ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $t_bug->resolution ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<select name="f_status">
			<?php print_enum_string_option_list( 'status', $t_bug->status ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<?php echo $t_bug->duplicate_id ?>
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
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<input type="text" name="f_summary" size="80" maxlength="128" value="<?php echo $t_bug->summary ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_description" wrap="virtual"><?php echo $t_bug->description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_additional_information" wrap="virtual"><?php echo $t_bug->additional_information ?></textarea>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
	<td colspan="5">
		<textarea name="f_bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>

<?php if ( access_level_check_greater_or_equal( config_get( 'private_bugnote_threshold' ) ) ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'private' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_private" />
	</td>
</tr>
<?php } ?>

<tr>
	<td class="center" colspan="6">
		<input type="submit" value="<?php echo lang_get( 'update_information_button' ) ?>" />
	</td>
</tr>
</table>
</form>

<?php print_page_bot1( __FILE__ ) ?>
