<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_change_status_page.php,v 1.8 2004-09-04 05:06:03 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );

	require_once( $t_core_path.'relationship_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );
	$f_new_status = gpc_get_int( 'new_status' );

	if ( ! ( ( access_has_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id ) ) ||
				( ( bug_get_field( $f_bug_id, 'reporter_id' ) == auth_get_current_user_id() ) && 
						( ( ON == config_get( 'allow_reporter_reopen' ) ) ||
								( ON == config_get( 'allow_reporter_close' ) ) ) ) ) ) {
		access_denied();
	}

	$t_status_label = str_replace( " ", "_", get_enum_to_string( config_get( 'status_enum_string' ), $f_new_status ) );
	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	
	$t_bug = bug_get( $f_bug_id );
?>
<?php html_page_top1(bug_format_summary( $f_bug_id, SUMMARY_CAPTION )) ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<form method="post" action="bug_update.php">
<table class="width75" cellspacing="1">


<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="status" value="<?php echo $f_new_status ?>" />
		<?php echo lang_get( $t_status_label . '_bug_title' ) ?>
	</td>
</tr>

<?php
# relationship warnings
if ( ON == config_get( 'enable_relationship' ) ) {
	if ( $t_resolved <= $f_new_status ) {
		if ( relationship_can_resolve_bug( $f_bug_id ) == false ) {
			echo "<tr><td colspan=\"2\">" . lang_get( 'relationship_warning_blocking_bugs_not_resolved_2' ) . "</td></tr>";
		}
	}
}
?>

<?php
if ( $t_resolved == $f_new_status ) { ?>
<!-- Resolution -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<select name="resolution">
			<?php print_enum_string_option_list( "resolution", FIXED ) ?>
		</select>
	</td>
</tr>
<?php } ?>

<?php
if ( $t_resolved == $f_new_status ) { ?>
<!-- Duplicate ID -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<input type="text" name="duplicate_id" maxlength="7" />
	</td>
</tr>
<?php } ?>

<?php
if ( ASSIGNED == $f_new_status ) { ?>
<!-- Assigned To -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<select name="handler_id">
			<option value="0"></option>
			<?php print_assign_to_option_list( $t_bug->handler_id, $t_bug->project_id ) ?>
		</select>
	</td>
</tr>
<?php } ?>


<!-- Custom Fields -->
<?php
$t_custom_status_label = "update"; # default info to check
if ( $f_new_status == config_get( 'bug_resolved_status_threshold' ) ) {
	$t_custom_status_label = "resolved";
}
if ( $f_new_status == CLOSED ) {
	$t_custom_status_label = "closed";
}

$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( bug_get_field( $f_bug_id, 'project_id' ) );
foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
	if( ( $t_def['display_' . $t_custom_status_label] || $t_def['require_' . $t_custom_status_label] ) && custom_field_has_write_access( $t_id, $f_bug_id ) ) {
		$t_custom_fields_found = true;
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php if($t_def['require_'. $t_custom_status_label]) {?><span class="required">*</span><?php } ?><?php echo lang_get_defaulted( $t_def['name'] ) ?>
	</td>
	<td>
		<?php
			print_custom_field_input( $t_def, $f_bug_id );
		?>
	</td>
</tr>
<?php
	} # $t_def['display_' . $t_custom_status_label] || $t_def['require_' . $t_custom_status_label] && custom_field_has_write_access( $t_id, $f_bug_id ) )
	else if( ( $t_def['display_' . $t_custom_status_label] || $t_def['require_' . $t_custom_status_label] ) && custom_field_has_read_access( $t_id, $f_bug_id ) ) {
?>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get_defaulted( $t_def['name'] ) ?>
		</td>
		<td>
			<?php print_custom_field_value( $t_def, $t_id, $f_bug_id );			?>
		</td>
	</tr>
<?php
	} # $t_def['display_' . $t_custom_status_label] || $t_def['require_' . $t_custom_status_label] ) && custom_field_has_read_access( $t_id, $f_bug_id ) )
} # foreach( $t_related_custom_field_ids as $t_id )
?>

<?php
if (  $f_new_status >= $t_resolved ) { 
	$t_show_version = ( ON == config_get( 'show_product_version' ) ) 
		|| ( ( AUTO == config_get( 'show_product_version' ) ) 
					&& ( count( version_get_all_rows( $t_bug->project_id ) ) > 0 ) );
	if ( $t_show_version ) { 
?>
<!-- Fixed in Version -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'fixed_in_version' ) ?>
	</td>
	<td>
		<select name="fixed_in_version">
			<?php print_version_option_list( bug_get_field( $f_bug_id, 'fixed_in_version' ),
							bug_get_field( $f_bug_id, 'project_id' ), VERSION_ALL ) ?>
		</select>
	</td>
</tr>
<?php } 
	} ?>

<?php
if ( $f_new_status >= $t_resolved ) { ?>
<!-- Close Immediately (if enabled) -->
<?php if ( ( ON == config_get( 'allow_close_immediately' ) )
				&& ( access_has_bug_level( access_get_status_threshold( CLOSED ), $f_bug_id ) ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'close_immediately' ) ?>
	</td>
	<td>
		<input type="checkbox" name="close_now" />
	</td>
</tr>
<?php } ?>
<?php } ?>

<?php
	if ( ( bug_get_field( $f_bug_id, 'status' ) == $t_resolved ) 
			&& ( $f_new_status == config_get( 'bug_reopen_status' ) ) ) {
		# bug was re-opened
		printf("	<input type=\"hidden\" name=\"resolution\" value=\"%s\" />\n",  config_get( 'bug_reopen_resolution' ) );
	}
?>

<!-- Bugnote -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" colspan="2">
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="center" colspan="2">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>


<!-- Submit Button -->
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( $t_status_label . '_bug_button' ) ?>" />
	</td>
</tr>


</table>
</form>
</div>

<br />
<?php
	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
?>

<?php html_page_bottom1( __FILE__ ) ?>
