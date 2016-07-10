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
 * Tag Attach
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses bug_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses tag_api.php
 */

require_once( 'core.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'tag_attach' );

$f_bug_id = gpc_get_int( 'bug_id' );
$f_tag_select = gpc_get_int( 'tag_select' );
$f_tag_string = gpc_get_string( 'tag_string' );

$t_user_id = auth_get_current_user_id();

access_ensure_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id, $t_user_id );

# @todo The handling of tag strings which can include multiple tags should be moved
#     to the APIs.  This is to allow other clients of the API to support such
#     functionality.  The access level checks should also be moved to the API.
$t_tags = tag_parse_string( $f_tag_string );
$t_can_create = access_has_global_level( config_get( 'tag_create_threshold' ) );

$t_tags_create = array();
$t_tags_attach = array();
$t_tags_failed = array();

foreach ( $t_tags as $t_tag_row ) {
	if( -1 == $t_tag_row['id'] ) {
		if( $t_can_create ) {
			$t_tags_create[] = $t_tag_row;
		} else {
			$t_tags_failed[] = $t_tag_row;
		}
	} else if( -2 == $t_tag_row['id'] ) {
		$t_tags_failed[] = $t_tag_row;
	} else {
		$t_tags_attach[] = $t_tag_row;
	}
}

if( 0 < $f_tag_select && tag_exists( $f_tag_select ) ) {
	$t_tags_attach[] = tag_get( $f_tag_select );
}

# failed to attach at least one tag
if( count( $t_tags_failed ) > 0 ) {
	layout_page_header( lang_get( 'tag_attach_long' ) . ' ' . bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
	layout_page_begin();
?>
<br />
<table class="width75">
	<tr>
	<td colspan="2"><?php echo lang_get( 'tag_attach_failed' ) ?></td>
	</tr>
	<tr class="spacer"><td colspan="2"></td></tr>
<?php
	$t_tag_string = '';
	foreach( $t_tags_attach as $t_tag_row ) {
		if( !is_blank( $t_tag_string ) ) {
			$t_tag_string .= config_get( 'tag_separator' );
		}
		$t_tag_string .= $t_tag_row['name'];
	}

	foreach( $t_tags_failed as $t_tag_row ) {
		echo '<tr>';
		if( -1 == $t_tag_row['id'] ) {
			echo '<th class="category">', lang_get( 'tag_create_denied' ), '</th>';
		} else if( -2 == $t_tag_row['id'] ) {
			echo '<th class="category">', lang_get( 'tag_invalid_name' ), '</th>';
		}
		echo '<td>', string_html_specialchars( $t_tag_row['name'] ), '</td></tr>';

		if( !is_blank( $t_tag_string ) ) {
			$t_tag_string .= config_get( 'tag_separator' );
		}
		$t_tag_string .= $t_tag_row['name'];
	}
?>
	<tr class="spacer"><td colspan="2"></td></tr>
	<tr>
	<th class="category"><?php echo lang_get( 'tag_attach_long' ) ?></th>
	<td>
<?php
	print_tag_attach_form( $f_bug_id, $t_tag_string );
?>
	</td>
	</tr>
</table>
<?php
	layout_page_end();
	# end failed to attach tag
} else {
	foreach( $t_tags_create as $t_tag_row ) {
		$t_tag_row['id'] = tag_create( $t_tag_row['name'], $t_user_id );
		$t_tags_attach[] = $t_tag_row;
	}

	foreach( $t_tags_attach as $t_tag_row ) {
		if( !tag_bug_is_attached( $t_tag_row['id'], $f_bug_id ) ) {
			tag_bug_attach( $t_tag_row['id'], $f_bug_id, $t_user_id );
		}
	}

	event_signal( 'EVENT_TAG_ATTACHED', array( $f_bug_id, $t_tags_attach ) );

	form_security_purge( 'tag_attach' );
	print_successful_redirect_to_bug( $f_bug_id );
}
