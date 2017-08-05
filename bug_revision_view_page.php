<?php
# Mantis - a php based bugtracking system

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

/**
 * View Bug Revisions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses bug_revision_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'bug_revision_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

$f_bug_id = gpc_get_int( 'bug_id', 0 );
$f_bugnote_id = gpc_get_int( 'bugnote_id', 0 );
$f_rev_id = gpc_get_int( 'rev_id', 0 );

$t_title = '';

if( $f_bug_id ) {
	$t_bug_id = $f_bug_id;
	$t_bug_data = bug_get( $t_bug_id, true );
	$t_bug_revisions = array_reverse( bug_revision_list( $t_bug_id ), true );

	$t_title = lang_get( 'issue_id' ) . $t_bug_id;

} else if( $f_bugnote_id ) {
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	$t_bug_data = bug_get( $t_bug_id, true );

	$t_bug_revisions = bug_revision_list( $t_bug_id, REV_ANY, $f_bugnote_id );

	$t_title = lang_get( 'bugnote' ) . ' ' . $f_bugnote_id;

} else if( $f_rev_id ) {
	$t_bug_revisions = bug_revision_like( $f_rev_id );

	if( count( $t_bug_revisions ) < 1 ) {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$t_bug_id = $t_bug_revisions[$f_rev_id]['bug_id'];
	$t_bug_data = bug_get( $t_bug_id, true );

	$t_title = lang_get( 'issue_id' ) . $t_bug_id;

} else {
	trigger_error( ERROR_GENERIC, ERROR );
}

/**
 * Show Bug revision
 *
 * @param array $p_revision Bug Revision Data.
 * @return null
 */
function show_revision( array $p_revision ) {
	static $s_can_drop = null;
	static $s_user_access = null;

	if( is_null( $s_can_drop ) ) {
		$s_can_drop = access_has_bug_level( config_get( 'bug_revision_drop_threshold' ), $p_revision['bug_id'] );
	}

	switch( $p_revision['type'] ) {
		case REV_DESCRIPTION:
			$t_label = lang_get( 'description' );
			break;
		case REV_STEPS_TO_REPRODUCE:
			$t_label = lang_get( 'steps_to_reproduce' );
			break;
		case REV_ADDITIONAL_INFO:
			$t_label = lang_get( 'additional_information' );
			break;
		case REV_BUGNOTE:
			if( is_null( $s_user_access ) ) {
				$s_user_access = access_has_bug_level( config_get( 'private_bugnote_threshold' ), $p_revision['bug_id'] );
			}

			if( !$s_user_access ) {
				return null;
			}

			$t_label = lang_get( 'bugnote' );
			break;
		default:
			$t_label = '';
	}

	$t_by_string = sprintf( lang_get( 'revision_by' ), string_display_line( date( config_get( 'normal_date_format' ), $p_revision['timestamp'] ) ), prepare_user_name( $p_revision['user_id'] ) );

?>
<tr class="spacer"><td><a id="revision-<?php echo $p_revision['id'] ?>"></a></td></tr>

<tr>
<th class="category"><?php echo lang_get( 'revision' ) ?></th>
<td colspan="2"><?php echo $t_by_string ?></td>
<td class="center" width="5%">
<?php if( $s_can_drop ) {
	$t_drop_token = form_security_param( 'bug_revision_drop' );
	print_small_button( 'bug_revision_drop.php?id=' . $p_revision['id'] . $t_drop_token, lang_get( 'revision_drop' ) );
} ?>
</tr>

<tr>
<th class="category"><?php echo $t_label ?></th>
<td colspan="3"><?php echo string_display_links( $p_revision['value'] ) ?></td>
</tr>

	<?php
}

layout_page_header( bug_format_summary( $t_bug_id, SUMMARY_CAPTION ) );

layout_page_begin();

?>

<div class="col-md-12 col-xs-12">
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<i class="ace-icon fa fa-history"></i>
	<?php echo lang_get( 'view_revisions' ), ': ', $t_title ?>
</h4>
</div>

<div class="widget-body">
<div class="widget-toolbox">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
<?php
if( !$f_bug_id && !$f_bugnote_id ) {
	print_small_button( '?bug_id=' . $t_bug_id, lang_get( 'all_revisions' ) );
}
print_small_button( 'view.php?id=' . $t_bug_id, lang_get( 'back_to_issue' ) );
?>
	</div>
</div>
</div>
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
<tr>
<th class="category" width="15%"><?php echo lang_get( 'summary' ) ?></th>
<td colspan="3"><?php echo bug_format_summary( $t_bug_id, SUMMARY_FIELD ) ?></td>
</tr>

<?php foreach( $t_bug_revisions as $t_rev ) {
	show_revision( $t_rev );
} ?>

</table>
</div>
</div>
</div>
</div>
</div>
<?php
layout_page_end();

