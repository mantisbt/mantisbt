<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_update_advanced_page.php,v 1.46 2003-01-25 13:52:41 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# Show the advanced update bug options
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'bug_api.php' );
	require_once( $g_core_path . 'project_api.php' );
	require_once( $g_core_path . 'custom_field_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	if ( SIMPLE_ONLY == config_get( 'show_update' ) ) {
		print_header_redirect ( 'bug_update_page.php?bug_id=' . $f_bug_id );
	}

	project_access_check( $f_bug_id );
	check_access( config_get( 'update_bug_threshold' ) );
	bug_ensure_exists( $f_bug_id );

	# if bug is private, make sure user can view private bugs
	access_bug_check( $f_bug_id );

	$t_bug = bug_prepare_edit( bug_get( $f_bug_id, true ) );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<form method="post" action="bug_update.php">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'updating_bug_advanced_title' ) ?>
	</td>
	<td class="right" colspan="3">
<?php
	print_bracket_link( string_get_bug_view_url( $f_bug_id ), lang_get( 'back_to_bug_link' ) );

	if ( BOTH == config_get( 'show_update' ) ) {
		print_bracket_link( 'bug_update_page.php?bug_id=' . $f_bug_id, lang_get( 'update_simple_link' ) );
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


<tr <?php echo helper_alternate_class() ?>>
	
	<!-- Bug ID -->
	<td>
		<?php echo bug_format_id( $f_bug_id ) ?>
	</td>

	<!-- Category -->
	<td>
		<select name="category">
			<?php print_category_option_list( $t_bug->category ) ?>
		</select>
	</td>

	<!-- Severity -->
	<td>
		<select name="severity">
			<?php print_enum_string_option_list( 'severity', $t_bug->severity ) ?>
		</select>
	</td>

	<!-- Reproducibility -->
	<td>
		<select name="reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $t_bug->reproducibility ) ?>
		</select>
	</td>

	<!-- Date Submitted -->
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) ?>
	</td>

	<!-- Date Updated -->
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->last_updated ) ?>
	</td>

</tr>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>



<tr <?php echo helper_alternate_class() ?>>

	<!-- Reporter -->
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<select name="reporter_id">
			<?php print_reporter_option_list( $t_bug->reporter_id ) ?>
		</select>
	</td>

	<!-- View Status -->
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state', $t_bug->view_state) ?>
		</select>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Assigned To -->
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<select name="handler_id">
			<option value="0"></option>
			<?php print_assign_to_option_list( $t_bug->handler_id ) ?>
		</select>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Priority -->
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td align="left">
		<select name="priority">
			<?php print_enum_string_option_list( 'priority', $t_bug->priority ) ?>
		</select>
	</td>

	<!-- Resolution -->
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $t_bug->resolution ) ?>
	</td>

	<!-- Platform -->
	<td class="category">
		<?php echo lang_get( 'platform' ) ?>
	</td>
	<td>
		<input type="text" name="platform" size="16" maxlength="32" value="<?php echo $t_bug->platform ?>" />
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Status -->
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<select name="status">
			<?php print_enum_string_option_list( 'status', $t_bug->status ) ?>
		</select>
	</td>

	<!-- Duplicate ID -->
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<?php echo $t_bug->duplicate_id ?>
	</td>

	<!-- Operating System -->
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<input type="text" name="os" size="16" maxlength="32" value="<?php echo $t_bug->os ?>" />
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Projection -->
	<td class="category">
		<?php echo lang_get( 'projection' ) ?>
	</td>
	<td>
		<select name="projection">
			<?php print_enum_string_option_list( 'projection', $t_bug->projection ) ?>
		</select>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

	<!-- OS Version -->
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<input type="text" name="os_build" size="16" maxlength="16" value="<?php echo $t_bug->os_build ?>" />
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- ETA -->
	<td class="category">
		<?php echo lang_get( 'eta' ) ?>
	</td>
	<td>
		<select name="eta">
			<?php print_enum_string_option_list( 'eta', $t_bug->eta ) ?>
		</select>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

	<!-- Product Version -->
	<td class="category">
		<?php echo lang_get( 'product_version' ) ?>
	</td>
	<td>
		<select name="version">
			<?php print_version_option_list( $t_bug->version ) ?>
		</select>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- spacer -->
	<td colspan="4">&nbsp;</td>

	<!-- Build -->
	<td class="category">
		<?php echo lang_get( 'build' ) ?>
	</td>
	<td>
		<input type="text" name="build" size="16" maxlength="32" value="<?php echo $t_bug->build ?>" />
	</td>

</tr>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<input type="text" name="summary" size="80" maxlength="128" value="<?php echo $t_bug->summary ?>" />
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="description" wrap="virtual"><?php echo $t_bug->description ?></textarea>
	</td>
</tr>


<!-- Steps to Reproduce -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="steps_to_reproduce" wrap="virtual"><?php echo $t_bug->steps_to_reproduce ?></textarea>
	</td>
</tr>


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="additional_information" wrap="virtual"><?php echo $t_bug->additional_information ?></textarea>
	</td>
</tr>


<!-- Custom Fields -->
<?php if( ON == config_get( 'use_experimental_custom_fields' ) ) { ?>
<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>
<?php
	$t_related_custom_field_ids = custom_field_get_linked_ids( helper_get_current_project() );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( !$t_def['advanced'] ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>
	</td>
	<td colspan="5">
		<?php
			print_custom_field_input( $t_def, $f_bug_id );
		?>
	</td>
</tr>
<?php
		} # !$t_def['advanced']
	} # foreach( $t_related_custom_field_ids as $t_id )
?>
<?php } # ON = config_get( 'use_experimental_custom_fields' ) ?>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="6">&nbsp;</td>
</tr>


<!-- Bugnote Text Box -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
	<td colspan="5">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>


<!-- Bugnote Private Checkbox (if permitted) -->
<?php if ( access_level_check_greater_or_equal( config_get( 'private_bugnote_threshold' ) ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'private' ) ?>
	</td>
	<td>
		<input type="checkbox" name="private" />
	</td>
</tr>
<?php } ?>


<!-- Submit Button -->
<tr>
	<td class="center" colspan="6">
		<input type="submit" value="<?php echo lang_get( 'update_information_button' ) ?>" />
	</td>
</tr>


</table>
</form>

<?php print_page_bot1( __FILE__ ) ?>
