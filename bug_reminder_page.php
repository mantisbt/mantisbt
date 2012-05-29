<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'bug_api.php' );

	$f_bug_id = gpc_get_int( 'bug_id' );

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	access_ensure_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );

	html_page_top( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
?>

<?php # Send reminder Form BEGIN ?>
  <script type="text/javascript">    $('#myModal').modal()</script>
    <div class="modal" id="myModal">
    <div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3><?php echo lang_get( 'bug_reminder' ) ?></h3>
    </div>
    <div class="modal-body">

		<?php
			echo lang_get( 'reminder_explain' ) . ' ';
			if ( ON == config_get( 'reminder_recipients_monitor_bug' ) ) {
				echo "<p>".lang_get( 'reminder_monitor' ) . '</p> ';
			}
			if ( ON == config_get( 'store_reminders' ) ) {
				echo "<p>".lang_get( 'reminder_store' ). '</p>';
			}
		?>
		<form method="post" action="bug_reminder.php">
		<?php echo form_security_field( 'bug_reminder' ) ?>
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />

		<?php echo lang_get( 'to' ) ?><br />
		<select name="to[]" multiple="multiple" size="5">
			<?php
				$t_project_id = bug_get_field( $f_bug_id, 'project_id' );
				$t_access_level = config_get( 'reminder_receive_threshold' );
				$t_selected_user_id = 0;
				print_user_option_list( $t_selected_user_id, $t_project_id, $t_access_level );
			?>
		</select>
		<br />
		<?php echo lang_get( 'reminder' ) ?><br />
		<textarea name="body" cols="65" rows="5" style="width:99%;"></textarea>

	</form>


    </div>
    <div class="modal-footer">
    <a href="#" class="btn">Cerrar</a>
	<input type="submit" class="btn btn-primary" value="<?php echo lang_get( 'bug_send_button' ) ?>" />
    </div>
    </div>
    
    

<?php
define ( 'BUG_VIEW_INC_ALLOW', true );
$_GET['id'] = $f_bug_id;
$tpl_fields_config_option = 'bug_view_page_fields';
$tpl_show_page_header = false;
$tpl_force_readonly = true;
$tpl_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$tpl_file = __FILE__;

include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
