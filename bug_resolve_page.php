<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_resolve_page.php,v 1.42 2004-08-01 22:24:58 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );

	# MASC RELATIONSHIP
	require_once( $t_core_path.'relationship_api.php' );
	# MASC RELATIONSHIP
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<form method="post" action="bug_resolve.php">
<table class="width75" cellspacing="1">


<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'resolve_bug_title' ) ?>
	</td>
</tr>

<?php
# MASC RELATIONSHIP
if ( ON == config_get( 'enable_relationship' ) ) {
	if ( relationship_can_resolve_bug( $f_bug_id ) == false ) {
		echo "<tr><td colspan=\"2\">" . lang_get( 'relationship_warning_blocking_bugs_not_resolved_2' ) . "</td></tr>";
	}
}
# MASC RELATIONSHIP
?>

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


<!-- Duplicate ID -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<input type="text" name="duplicate_id" maxlength="7" />
	</td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( bug_get_field( $f_bug_id, 'project_id' ) );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( ( $t_def['display_resolve'] || $t_def['require_resolve'] ) && custom_field_has_write_access( $t_id, $f_bug_id ) ) {
			$t_custom_fields_found = true;
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php if($t_def['require_resolve']) {?><span class="required">*</span><?php } ?><?php echo lang_get_defaulted( $t_def['name'] ) ?>
	</td>
	<td>
		<?php
			print_custom_field_input( $t_def, $f_bug_id );
		?>
	</td>
</tr>
<?php
		} # $t_def['display_resolve'] || $t_def['require_resolve'] && custom_field_has_write_access( $t_id, $f_bug_id ) )
		else if( ( $t_def['display_resolve'] || $t_def['require_resolve'] ) && custom_field_has_read_access( $t_id, $f_bug_id ) ) {
?>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get_defaulted( $t_def['name'] ) ?>
		</td>
		<td>
			<?php print_custom_field_value( $t_def, $t_id, $f_bug_id ); ?>		
		</td>
	</tr>
<?php
		} # $t_def['display_resolve'] || $t_def['require_resolve'] ) && custom_field_has_read_access( $t_id, $f_bug_id ) )
	} # foreach( $t_related_custom_field_ids as $t_id )
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


<!-- Close Immediately (if enabled) -->
<?php if ( ON == config_get( 'allow_close_immediately' ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'close_immediately' ) ?>
	</td>
	<td>
		<input type="checkbox" name="close_now" />
	</td>
</tr>
<?php } ?>


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
		<input type="submit" class="button" value="<?php echo lang_get( 'resolve_bug_button' ) ?>" />
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
