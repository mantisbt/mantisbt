<?php
# MantisBT - A PHP based bugtracking system

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
 * CALLERS
 *	This page is submitted to by the following pages:
 *	- bugnote_inc.php
 *
 * EXPECTED BEHAVIOUR
 *	Allow the user to modify the text of a bugnote, then submit to
 *	bugnote_update.php with the new text
 *
 * RESTRICTIONS & PERMISSIONS
 *	- none beyond API restrictions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

$f_bugnote_id = gpc_get_int( 'bugnote_id' );
$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );

$t_bug = bug_get( $t_bug_id, true );
if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

# Check if the current user is allowed to edit the bugnote
$t_user_id = auth_get_current_user_id();
$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

if( $t_user_id == $t_reporter_id ) {
	access_ensure_bugnote_level( config_get( 'bugnote_user_edit_threshold' ), $f_bugnote_id );
} else {
	access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
}

# Check if the bug is readonly
if( bug_is_readonly( $t_bug_id ) ) {
	error_parameters( $t_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

$t_bugnote_text = string_textarea( bugnote_get_text( $f_bugnote_id ) );

# No need to gather the extra information if not used
if( config_get( 'time_tracking_enabled' ) &&
	access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) {
	$t_time_tracking = bugnote_get_field( $f_bugnote_id, 'time_tracking' );
	$t_time_tracking = db_minutes_to_hhmm( $t_time_tracking );
}

# Determine which view page to redirect back to.
$t_redirect_url = string_get_bug_view_url( $t_bug_id );

layout_page_header( bug_format_summary( $t_bug_id, SUMMARY_CAPTION ) );

layout_page_begin();

$t_bugnote_class = bugnote_get_field( $f_bugnote_id, 'view_state' ) == VS_PUBLIC ? '' : 'bugnote-private';
?>
<div class="col-md-12 col-xs-12">

<form method="post" action="bugnote_update.php">
<?php echo form_security_field( 'bugnote_update' ) ?>
<input type="hidden" name="bugnote_id" value="<?php echo $f_bugnote_id ?>" />
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-comment"></i>
		<?php echo lang_get( 'edit_bugnote_title' ) ?>
	</h4>
	</div>
	<div class="widget-body">
		<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
<tr>
	<td colspan="2">
		<textarea class="form-control <?php echo $t_bugnote_class; ?>" cols="80" rows="10" name="bugnote_text"
		id="bugnote_text"><?php echo $t_bugnote_text ?></textarea>
	</td>
</tr>
<?php if( config_get( 'time_tracking_enabled' ) ) { ?>
<?php if( access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) { ?>
<tr>
	<td class="center" colspan="2">
		<strong><?php echo lang_get( 'time_tracking' ) ?> (HH:MM)</strong><br />
		<input type="text" name="time_tracking" class="input-sm" size="5" value="<?php echo $t_time_tracking ?>" />
	</td>
</tr>
<?php } ?>
<?php } ?>

<?php event_signal( 'EVENT_BUGNOTE_EDIT_FORM', array( $t_bug_id, $f_bugnote_id ) ); ?>

</table>
	</div>
	</div>
		<div class="widget-toolbox padding-8 clearfix">
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_information_button' ) ?>" />
			<?php print_link_button( $t_redirect_url, lang_get( 'go_back' ) ) ?>
		</div>
	</div>
</div>
</form>
</div>

<?php
layout_page_end();
