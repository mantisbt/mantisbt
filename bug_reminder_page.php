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
	# $Id: bug_reminder_page.php,v 1.23.2.1 2007-10-13 22:32:50 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	access_ensure_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

?>
<?php html_page_top1( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) ) ?>
<?php html_page_top2() ?>

<?php # Send reminder Form BEGIN ?>
<br />
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="bug_reminder.php">
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'bug_reminder' ) ?>
	</td>
</tr>
<tr>
	<td class="category">
		<?php echo lang_get( 'to' ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'reminder' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td>
		<select name="to[]" multiple="multiple" size="10">
			<?php echo print_project_user_option_list( bug_get_field( $f_bug_id, 'project_id' ) ) ?>
		</select>
	</td>
	<td class="center">
		<textarea name="body" cols="65" rows="10"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'bug_send_button' ) ?>">
	</td>
</tr>
</form>
</table>
<br />
<table class="width75" cellspacing="1">
<tr>
	<td>
		<?php
			echo lang_get( 'reminder_explain' ) . ' ';
			if ( ON == config_get( 'reminder_recipents_monitor_bug' ) ) {
				echo lang_get( 'reminder_monitor' ) . ' ';
			}
			if ( ON == config_get( 'store_reminders' ) ) {
				echo lang_get( 'reminder_store' );
			}
		?>
	</td>
</tr>
</table>
</div>

<br />
<?php include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' ) ?>
<?php include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' ) ?>

<?php html_page_bottom1( __FILE__ ) ?>
