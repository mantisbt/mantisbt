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
	# $Id: history_inc.php,v 1.32.2.1 2007-10-13 22:33:15 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the bug history

	# $f_bug_id must already be defined
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'history_api.php' );
?>

<?php
	$t_access_level_needed = config_get( 'view_history_threshold' );
	if ( !access_has_bug_level( $t_access_level_needed, $f_bug_id ) ) {
		return;
	}
?>

<a name="history" id="history" /><br />

<?php
	collapse_open( 'history' );
	$t_history = history_get_events_array( $f_bug_id );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
<?php
	collapse_icon( 'history' );
	echo lang_get( 'bug_history' ) ?>
	</td>
</tr>
<tr class="row-category-history">
	<td class="small-caption">
		<?php echo lang_get( 'date_modified' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'field' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'change' ) ?>
	</td>
</tr>
<?php
	foreach ( $t_history as $t_item ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="small-caption">
		<?php echo $t_item['date'] ?>
	</td>
	<td class="small-caption">
		<?php print_user( $t_item['userid'] ) ?>
	</td>
	<td class="small-caption">
		<?php echo string_display( $t_item['note'] ) ?>
	</td>
	<td class="small-caption">
		<?php echo string_display_line_links( $t_item['change'] ) ?>
	</td>
</tr>
<?php
	} # end for loop
?>
</table>
</div>
<?php
	collapse_closed( 'history' );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
	<?php	collapse_icon( 'history' );
		echo lang_get( 'bug_history' ) ?>
	</td>
</tr>
</table>

<?php
	collapse_end( 'history' );
?>
