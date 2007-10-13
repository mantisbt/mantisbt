<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: bug_view_inc.php,v 1.16.2.1 2007-10-13 22:33:00 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the bug information
	# $f_bug_id MUST be specified before the file is included
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'date_api.php' );
?>
<?php
	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );
?>

<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="6">
		<?php echo lang_get( 'viewing_bug_simple_details_title' ) ?>
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
		<?php echo $t_bug->category ?>
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
<tr class="spacer">
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

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Status -->
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<?php echo get_enum_element( 'status', $t_bug->status ) ?>
	</td>

	<!-- Duplicate ID -->
	<td class="category">
		<?php
			if ( ! config_get( 'enable_relationship' ) ) {
				echo lang_get( 'duplicate_id' );
			} # MASC RELATIONSHIP
		?>&nbsp;
	</td>
	<td>
		<?php
			if ( !config_get( 'enable_relationship' ) ) {
				print_duplicate_id( $t_bug->duplicate_id );
			} # MASC RELATIONSHIP
		?>&nbsp;
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

</tr>


<!-- spacer giallu-->
<tr class="spacer">
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


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->additional_information ?>
	</td>
</tr>


</table>
