<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_view_advanced_page.php,v 1.67 2004-10-25 19:59:12 marcelloscata Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( $t_core_path.'relationship_api.php' );

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_history		= gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

	if ( SIMPLE_ONLY == config_get( 'show_view' ) ) {
		print_header_redirect ( 'bug_view_page.php?bug_id=' . $f_bug_id );
	}

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

	compress_enable();

	html_page_top1( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
	html_page_top2();

	$t_access_level_needed = config_get( 'view_history_threshold' );
	$t_can_view_history = access_has_bug_level( $t_access_level_needed, $f_bug_id );

	$t_bugslist = gpc_get_cookie( config_get( 'bug_list_cookie' ), false );
?>

<br />
<table class="width100" cellspacing="1">


<tr>

	<!-- Title -->
	<td class="form-title" colspan="<?php echo $t_bugslist ? '3' : '4' ?>">
		<?php echo lang_get( 'viewing_bug_advanced_details_title' ) ?>

		<!-- Jump to Bugnotes -->
		<span class="small"><?php print_bracket_link( "#bugnotes", lang_get( 'jump_to_bugnotes' ) ) ?></span>

		<!-- Send Bug Reminder -->
	<?php
		if ( !current_user_is_anonymous() && !bug_is_readonly( $f_bug_id ) &&
			  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id ) ) {
	?>
		<span class="small">
			<?php print_bracket_link( 'bug_reminder_page.php?bug_id='.$f_bug_id, lang_get( 'bug_reminder' ) ) ?>
		</span>
	<?php
		}
	?>
	</td>

	<!-- prev/next links -->
	<?php if( $t_bugslist ) { ?>
	<td class="center"><span class="small">
		<?php 
			$t_bugslist = explode( ',', $t_bugslist );
			$t_index = array_search( $f_bug_id, $t_bugslist );
			if( false !== $t_index ) {
				if( isset( $t_bugslist[$t_index-1] ) ) print_bracket_link( 'bug_view_advanced_page.php?bug_id='.$t_bugslist[$t_index-1], '&lt;&lt;' ); 
				if( isset( $t_bugslist[$t_index+1] ) ) print_bracket_link( 'bug_view_advanced_page.php?bug_id='.$t_bugslist[$t_index+1], '&gt;&gt;' ); 
			}
		?>
	</span></td>
	<?php } ?>

	<!-- Links -->
	<td class="right" colspan="2">

		<!-- Simple View (if enabled) -->
	<?php if ( BOTH == config_get( 'show_view' ) ) { ?>
			<span class="small"><?php print_bracket_link( 'bug_view_page.php?bug_id=' . $f_bug_id, lang_get( 'view_simple_link' ) ) ?></span>
	<?php } ?>

	<?php if ( $t_can_view_history ) { ?>
		<!-- History -->
		<span class="small"><?php print_bracket_link( 'bug_view_advanced_page.php?bug_id=' . $f_bug_id . '&amp;history=1#history', lang_get( 'bug_history' ) ) ?></span>
	<?php } ?>

		<!-- Print Bug -->
		<span class="small"><?php print_bracket_link( 'print_bug_page.php?bug_id=' . $f_bug_id, lang_get( 'print' ) ) ?></span>

	</td>

</tr>


<!-- Labels -->
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
		<?php
			$t_project_name = project_get_field( $t_bug->project_id, 'name' );
			echo "[$t_project_name] $t_bug->category";
		?>
	</td>

	<!-- Severity -->
	<td>
		<?php echo get_enum_element( 'severity', $t_bug->severity ) ?>
	</td>

	<!-- Reproducibility -->
	<td>
		<?php echo get_enum_element( 'reproducibility', $t_bug->reproducibility ) ?>
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
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Reporter -->
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<?php print_user_with_subject( $t_bug->reporter_id, $f_bug_id ) ?>
	</td>

	<!-- View Status -->
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $t_bug->view_state ) ?>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

</tr>


<!-- Handler -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<?php print_user_with_subject( $t_bug->handler_id, $f_bug_id ) ?>
	</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Priority -->
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'priority', $t_bug->priority ) ?>
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
		<?php echo $t_bug->platform ?>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Status -->
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<?php echo get_enum_element( 'status', $t_bug->status ) ?>
	</td>

	<?php
		# Duplicate Id
		# MASC RELATIONSHIP
		if ( OFF == config_get( 'enable_relationship' ) ) {
			# Duplicate ID
			echo '<td class="category">', lang_get( 'duplicate_id' ), '&nbsp;</td>';
			echo '<td>';
			print_duplicate_id( $t_bug->duplicate_id );
			echo '</td>';
		} else {
			# spacer
			echo '<td colspan="2">&nbsp;</td>';
		}
	?>

	<!-- Operating System -->
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os ?>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Projection -->
	<td class="category">
		<?php echo lang_get( 'projection' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'projection', $t_bug->projection ) ?>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

	<!-- OS Version -->
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os_build ?>
	</td>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- ETA -->
	<td class="category">
		<?php echo lang_get( 'eta' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'eta', $t_bug->eta ) ?>
	</td>

	<!-- fixed in version -->
	<td class="category">
		<?php 
			$t_show_version = ( ON == config_get( 'show_product_version' ) ) 
					|| ( ( AUTO == config_get( 'show_product_version' ) ) 
								&& ( count( version_get_all_rows( $t_bug->project_id ) ) > 0 ) );
			if ( $t_show_version ) { 
				echo lang_get( 'fixed_in_version' );
			}
		?>
	</td>
	<td>
		<?php 
			if ( $t_show_version ) { 
				echo $t_bug->fixed_in_version; 
			}
		?>
	</td>

	<!-- Product Version or Product Build, if version is suppressed -->
	<td class="category">
		<?php 
			if ( $t_show_version ) { 
				echo lang_get( 'product_version' ); 
			}else{
				echo lang_get( 'product_build' );
			}
		?>
	</td>
	<td>
		<?php  
			if ( $t_show_version ) { 
				echo $t_bug->version; 
			}else{
				echo $t_bug->build;
			}
		?>
	</td>

</tr>

<?php
	if( $t_show_version ) {
?>
<tr <?php echo helper_alternate_class() ?>>

	<!-- spacer -->
	<td colspan="4">&nbsp;</td>

	<!-- Product Build -->
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<?php echo $t_bug->build?>
	</td>

</tr>
<?php
	}
?>

<!-- spacer -->
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<?php echo bug_format_summary( $f_bug_id, SUMMARY_FIELD ) ?>
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->description ?>
	</td>
</tr>


<!-- Steps to Reproduce -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->steps_to_reproduce ?>
	</td>
</tr>


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->additional_information ?>
	</td>
</tr>


<!-- spacer -->
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		if ( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
			continue;			
		} # has read access

		$t_custom_fields_found = true;
		$t_def = custom_field_get_definition( $t_id );
?>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get_defaulted( $t_def['name'] ) ?>
		</td>
		<td colspan="5">
		<?php print_custom_field_value( $t_def, $t_id, $f_bug_id ); ?>
		</td>
	</tr>
<?php
	} # foreach
?>

<?php if ( $t_custom_fields_found ) { ?>
<!-- spacer -->
<tr height="5" class="spacer">
	<td colspan="6"></td>
</tr>
<?php } # custom fields found ?>


<!-- Attachments -->
<?php
	$t_show_attachments = ( $t_bug->reporter_id == auth_get_current_user_id() ) || access_has_bug_level( config_get( 'view_attachments_threshold' ), $f_bug_id );

	if ( $t_show_attachments ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<a name="attachments" id="attachments" />
		<?php echo lang_get( 'attached_files' ) ?>
	</td>
	<td colspan="5">
		<?php file_list_attachments ( $f_bug_id ); ?>
	</td>
</tr>
<?php
	}
?>

<!-- Buttons -->
<tr align="center">
	<td align="center" colspan="6">
<?php
	html_buttons_view_bug_page( $f_bug_id );
?>
	</td>
</tr>
</table>

<?php
	$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

	# User list sponsoring the bug
	include( $t_mantis_dir . 'bug_sponsorship_list_view_inc.php' );

	# Bug Relationships
	# MASC RELATIONSHIP
	if ( ON == config_get( 'enable_relationship' ) ) {
		relationship_view_box ( $f_bug_id );
	}
	# MASC RELATIONSHIP

	# File upload box
	if ( !bug_is_readonly( $f_bug_id ) ) {
		include( $t_mantis_dir . 'bug_file_upload_inc.php' );
	}

	# User list monitoring the bug
	include( $t_mantis_dir . 'bug_monitor_list_view_inc.php' );

	# Bugnotes
	include( $t_mantis_dir . 'bugnote_add_inc.php' );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );

	# History
	if ( $f_history ) {
		include( $t_mantis_dir . 'history_inc.php' );
	}

	html_page_bottom1( __FILE__ );
?>
