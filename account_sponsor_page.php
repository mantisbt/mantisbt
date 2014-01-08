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
	 * CALLERS
	 *	This page is called from:
	 *	- print_menu()
	 *	- print_account_menu()
	 *
	 * EXPECTED BEHAVIOUR
	 *	- Display the user's current sponsorships
	 *	- Allow the user to edit the payment flag
	 *
	 * CALLS
	 *	This page calls the following pages:
	 *	- account_sponsor_update.php  (to save changes)
	 *
	 * RESTRICTIONS & PERMISSIONS
	 *	- User must be authenticated, and not anonymous
	 *   - sponsorship must be enabled
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );

	if ( config_get( 'enable_sponsorship' ) == OFF ) {
		trigger_error( ERROR_SPONSORSHIP_NOT_ENABLED, ERROR );
	}

	# anonymous users are not allowed to sponsor issues
	if ( current_user_is_anonymous() ) {
		access_denied();
	}

	$t_show_all = gpc_get_bool( 'show_all', false );

	# start the page
	html_page_top( lang_get( 'my_sponsorship' ) );

	$t_project = helper_get_current_project();
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'my_sponsorship' ) ?>
	</td>
	<td class="right">
		<?php print_account_menu( 'account_sponsor_page.php' ) ?>
	</td>
</tr>
</table>
<?php
	# get issues user has sponsored
	$t_user = auth_get_current_user_id();
	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_sponsor_table = db_get_table( 'mantis_sponsorship_table' );
	$t_payment = config_get( 'payment_enable', 0 );

	$t_project_clause = helper_project_specific_where( $t_project );

	$query = "SELECT b.id as bug, s.id as sponsor, s.paid, b.project_id, b.fixed_in_version, b.status
		FROM $t_bug_table b, $t_sponsor_table s
		WHERE s.user_id=" . db_param() . " AND s.bug_id = b.id " .
		( $t_show_all ? '' : 'AND ( b.status < ' . db_param() . ' OR s.paid < ' . SPONSORSHIP_PAID . ')' ) . "
		AND $t_project_clause
		ORDER BY s.paid ASC, b.project_id ASC, b.fixed_in_version ASC, b.status ASC, b.id DESC";

	$result = db_query_bound( $query, $t_show_all ? Array( $t_user ) : Array( $t_user , $t_resolved ) );

	$t_sponsors = db_num_rows( $result );
	if ( 0 == $t_sponsors ) {
		echo '<p>' . lang_get( 'no_own_sponsored' ) . '</p>';
	} else {
?>

<!-- # Edit own sponsorship Form BEGIN -->
<br />
<div align="center">
<table class="width100" cellspacing="1">

	<!-- Headings -->
	<tr>
		<td class="form-title" colspan="9">
			<?php echo lang_get( 'own_sponsored' ) ?>
		</td>
	</tr>
	<tr>
		<td class="form-title" width="10%"><?php echo lang_get( 'email_bug' ) ?></td>
		<td class="form-title" width="8%"><?php echo lang_get( 'email_project' ) ?></td>
		<td class="form-title" width="7%"><?php echo lang_get( 'fixed_in_version' ) ?></td>
		<td class="form-title" width="10%"><?php echo lang_get( 'email_status' ) ?></td>
		<td class="form-title" width="10%"><?php echo lang_get( 'email_handler' ) ?></td>
		<td class="form-title" width="30%"><?php echo lang_get( 'email_summary' ) ?></td>
		<td class="form-title" width="8%"><?php echo lang_get( 'amount' ) ?></td>
		<td class="form-title" width="7%"><?php echo lang_get( 'status' ) ?></td>
		<td class="form-title" width="10%">&#160;</td>
	</tr>
<?php
		$t_total_owing = 0;
		$t_total_paid = 0;
		for ( $i=0; $i < $t_sponsors; ++$i ) {
			$row = db_fetch_array( $result );
			$t_bug = bug_get( $row['bug'] );
			$t_sponsor = sponsorship_get( $row['sponsor'] );

			# describe bug
			$t_status = string_attribute( get_enum_element( 'status', $t_bug->status, auth_get_current_user_id(), $t_bug->project_id ) );
			$t_resolution = string_attribute( get_enum_element( 'resolution', $t_bug->resolution, auth_get_current_user_id(), $t_bug->project_id ) );
			$t_version_id = version_get_id( $t_bug->fixed_in_version, $t_project );
			if ( ( false !== $t_version_id ) && ( VERSION_RELEASED == version_get_field( $t_version_id, 'released' ) ) ) {
				$t_released_label = '<a title="' . lang_get( 'released' ) . '">' . $t_bug->fixed_in_version . '</a>';
			} else {
				$t_released_label = $t_bug->fixed_in_version;
			}

			echo '<tr bgcolor="' . get_status_color( $t_bug->status, auth_get_current_user_id(), $t_bug->project_id ) . '">';
			echo '<td><a href="' . string_get_bug_view_url( $row['bug'] ) . '">' . bug_format_id( $row['bug'] ) . '</a></td>';
			echo '<td>' . string_display_line( project_get_field( $t_bug->project_id, 'name' ) ) . '&#160;</td>';
			echo '<td class="right">' . $t_released_label . '&#160;</td>';
			echo '<td><span class="issue-status" title="' . $t_resolution . '">' . $t_status . '</span></td>';
			echo '<td>';
			print_user( $t_bug->handler_id );
			echo '</td>';

			# summary
			echo '<td>' . string_display_line( $t_bug->summary );
			if ( VS_PRIVATE == $t_bug->view_state ) {
				printf( ' <img src="%s" alt="(%s)" title="%s" />', $t_icon_path . 'protected.gif', lang_get( 'private' ), lang_get( 'private' ) );
			}
			echo '</td>';

			# describe sponsorship amount
			echo '<td class="right">' . sponsorship_format_amount( $t_sponsor->amount ) . '</td>';
			echo '<td>' . get_enum_element( 'sponsorship', $t_sponsor->paid ) . '</td>';

			if ( SPONSORSHIP_PAID == $t_sponsor->paid ) {
				$t_total_paid += $t_sponsor->amount;
			} else {
				$t_total_owing += $t_sponsor->amount;
			}

			echo '<td>';
			if ( $t_payment ) {
				echo '(paypal button)';
			} else {
				echo '&#160;';
			}
			echo '</td>';
			echo '</tr>';
		}
?>
<!-- Totals -->
<tr>
	<td colspan="5"></td>
	<td><?php echo lang_get( 'total_owing' ) ?></td>
	<td class="right"><?php echo sponsorship_format_amount( $t_total_owing ) ?></td>
	<td colspan="2"></td>
</tr>
<tr>
	<td colspan="5"></td>
	<td><?php echo lang_get( 'total_paid' ) ?></td>
	<td class="right"><?php echo sponsorship_format_amount( $t_total_paid ) ?></td>
	<td colspan="2"></td>
</tr>
</table>
</div>
<?php } # end sponsored issues

	$query = "SELECT b.id as bug, s.id as sponsor, s.paid, b.project_id, b.fixed_in_version, b.status
		FROM $t_bug_table b, $t_sponsor_table s
		WHERE b.handler_id=" . db_param() . " AND s.bug_id = b.id " .
		( $t_show_all ? '' : 'AND ( b.status < ' . db_param() . ' OR s.paid < ' . SPONSORSHIP_PAID . ')' ) . "
		AND $t_project_clause
		ORDER BY s.paid ASC, b.project_id ASC, b.fixed_in_version ASC, b.status ASC, b.id DESC";

	$result = db_query_bound( $query, $t_show_all ? Array( $t_user ) : Array( $t_user , $t_resolved ) );
	$t_sponsors = db_num_rows( $result );
	if ( 0 == $t_sponsors ) {
		echo '<p>' . lang_get( 'no_sponsored' ) . '</p>';
	} else {
?>

<!-- # Edit sponsorship Form BEGIN -->
<br />
<div align="center">
<form method="post" action="account_sponsor_update.php">
<?php echo form_security_field( 'account_sponsor_update' ) ?>
<table class="width100" cellspacing="1">

	<!-- Headings -->
	<tr>
		<td class="form-title" colspan="8">
			<?php echo lang_get( 'issues_handled' ) ?>
		</td>
	</tr>
	<tr>
		<td class="form-title" width="10%"><?php echo lang_get( 'email_bug' ) ?></td>
		<td class="form-title" width="8%"><?php echo lang_get( 'email_project' ) ?></td>
		<td class="form-title" width="7%"><?php echo lang_get( 'fixed_in_version' ) ?></td>
		<td class="form-title" width="10%"><?php echo lang_get( 'email_status' ) ?></td>
		<td class="form-title" width="35%"><?php echo lang_get( 'email_summary' ) ?></td>
		<td class="form-title" width="10%"><?php echo lang_get( 'sponsor' ) ?></td>
		<td class="form-title" width="10%"><?php echo lang_get( 'amount' ) ?></td>
		<td class="form-title" width="10%"><?php echo lang_get( 'status' ) ?></td>
	</tr>
<?php
		$t_bug_list = array();
		$t_total_owing = 0;
		$t_total_paid = 0;
		for ( $i=0; $i < $t_sponsors; ++$i ) {
			$row = db_fetch_array( $result );
			$t_bug = bug_get( $row['bug'] );
			$t_sponsor = sponsorship_get( $row['sponsor'] );
			$t_buglist[] = $row['bug'] . ':' . $row['sponsor'];

			# describe bug
			$t_status = string_attribute( get_enum_element( 'status', $t_bug->status, auth_get_current_user_id(), $t_bug->project_id ) );
			$t_resolution = string_attribute( get_enum_element( 'resolution', $t_bug->resolution, auth_get_current_user_id(), $t_bug->project_id ) );
			$t_version_id = version_get_id( $t_bug->fixed_in_version, $t_project );
			if ( ( false !== $t_version_id ) && ( VERSION_RELEASED == version_get_field( $t_version_id, 'released' ) ) ) {
				$t_released_label = '<a title="' . lang_get( 'released' ) . '">' . $t_bug->fixed_in_version . '</a>';
			} else {
				$t_released_label = $t_bug->fixed_in_version;
			}

			echo '<tr bgcolor="' . get_status_color( $t_bug->status, auth_get_current_user_id(), $t_bug->project_id ) . '">';
			echo '<td><a href="' . string_get_bug_view_url( $row['bug'] ) . '">' . bug_format_id( $row['bug'] ) . '</a></td>';
			echo '<td>' . string_display_line( project_get_field( $t_bug->project_id, 'name' ) ) . '&#160;</td>';
			echo '<td class="right">' . $t_released_label . '&#160;</td>';
			echo '<td><a title="' . $t_resolution . '"><u>' . $t_status . '</u>&#160;</a></td>';

			# summary
			echo '<td>' . string_display_line( $t_bug->summary );
			if ( VS_PRIVATE == $t_bug->view_state ) {
				printf( ' <img src="%s" alt="(%s)" title="%s" />', $t_icon_path . 'protected.gif', lang_get( 'private' ), lang_get( 'private' ) );
			}

			# describe sponsorship amount
			echo '<td>';
			print_user( $t_sponsor->user_id );
			echo '</td>';
			echo '<td class="right">' . sponsorship_format_amount( $t_sponsor->amount ) . '</td>';
			echo '<td><select name="sponsor_' . $row['bug'] . '_' . $t_sponsor->id . '">';
			print_enum_string_option_list( 'sponsorship', $t_sponsor->paid );
			echo '</select></td>';

			echo '</tr>';
			if ( SPONSORSHIP_PAID == $t_sponsor->paid ) {
				$t_total_paid += $t_sponsor->amount;
			} else {
				$t_total_owing += $t_sponsor->amount;
			}

		}
		$t_hidden_bug_list = implode( ',', $t_buglist );
?>
<!-- Totals -->
<tr>
	<td colspan="5"></td>
	<td><?php echo lang_get( 'total_owing' ) ?></td>
	<td class="right"><?php echo sponsorship_format_amount( $t_total_owing ) ?></td>
	<td></td>
</tr>
<tr>
	<td colspan="5"></td>
	<td><?php echo lang_get( 'total_paid' ) ?></td>
	<td class="right"><?php echo sponsorship_format_amount( $t_total_paid ) ?></td>
	<td></td>
</tr>
	<input type="hidden" name="buglist" value="<?php echo $t_hidden_bug_list ?>" />
	<!-- BUTTONS -->
	<tr>
		<td colspan="5">&#160;</td>
		<!-- Update Button -->
		<td colspan="2">
			<input type="submit" class="button" value="<?php echo lang_get( 'update_sponsorship_button' ) ?>" />
		</td>
	</tr>
</table>
</form>
</div>
<?php } # end sponsored issues ?>

<br />
<div align="center">
<?php
	html_button ( 'account_sponsor_page.php',
		lang_get( ( $t_show_all ? 'sponsor_hide' : 'sponsor_show' ) ),
		array( 'show_all' => ( $t_show_all ? 0 : 1 ) ) );
?>
</div>

<?php
	html_page_bottom();
