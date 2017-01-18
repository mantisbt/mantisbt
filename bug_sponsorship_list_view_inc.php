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
 * This include file prints out the list of users sponsoring the current
 * bug.	$f_bug_id must be set to the bug id
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses sponsorship_api.php
 * @uses utility_api.php
 */

if( !defined( 'BUG_SPONSORSHIP_LIST_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'sponsorship_api.php' );
require_api( 'utility_api.php' );

#
# Determine whether the sponsorship section should be shown.
#

if( ( config_get( 'enable_sponsorship' ) == ON ) && ( access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $f_bug_id ) ) ) {
	$t_sponsorship_ids = sponsorship_get_all_ids( $f_bug_id );

	$t_sponsorships_exist = count( $t_sponsorship_ids ) > 0;
	$t_can_sponsor = !bug_is_readonly( $f_bug_id ) && !current_user_is_anonymous();

	$t_show_sponsorships = $t_sponsorships_exist || $t_can_sponsor;
} else {
	$t_show_sponsorships = false;
}

#
# Sponsorship Box
#

if( $t_show_sponsorships ) {
?>

<a id="sponsorships"></a> <br />

<?php
	collapse_open( 'sponsorship' );
?>

<table class="width100" cellspacing="1">
	<tr>
		<td width="50" rowspan="3">
			<i class="fa fa-usd" title="<?php echo lang_get( 'sponsor_verb' ) ?>"></i>
		</td>
		<td class="form-title" colspan="2"><?php
			collapse_icon( 'sponsorship' );
			echo lang_get( 'users_sponsoring_bug' );

			$t_details_url = lang_get( 'sponsorship_process_url' );
			if( !is_blank( $t_details_url ) ) {
				echo '&#160;[<a href="' . $t_details_url . '">'
					. lang_get( 'sponsorship_more_info' ) . '</a>]';
			}
		?>
		</td>
	</tr>

<?php
	if( $t_can_sponsor ) {
?>
	<tr>
		<th class="category" width="15%"><?php echo lang_get( 'sponsor_issue' ) ?></th>
		<td>
			<form method="post" action="bug_set_sponsorship.php">
				<?php echo form_security_field( 'bug_set_sponsorship' ) ?>
				<?php echo sponsorship_get_currency() ?>
				<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" size="4" />
				<input type="text" name="amount" class="input-sm" value="<?php echo config_get( 'minimum_sponsorship_amount' )  ?>" size="4" />
				<input type="submit" class="btn btn-primary btn-white btn-round" name="sponsor" value="<?php echo lang_get( 'sponsor_verb' ) ?>" />
			</form>
		</td>
	</tr>
<?php
	}

	$t_total_sponsorship = bug_get_field( $f_bug_id, 'sponsorship_total' );
	if( $t_total_sponsorship > 0 ) {
?>
	<tr>
		<th class="category" width="15%"><?php echo lang_get( 'sponsors_list' ) ?></th>
		<td>
		<?php
			echo sprintf( lang_get( 'total_sponsorship_amount' ),
				sponsorship_format_amount( $t_total_sponsorship ) );

			if( access_has_bug_level( config_get( 'view_sponsorship_details_threshold' ), $f_bug_id ) ) {
				echo '<br /><br />';
				$i = 0;
				foreach ( $t_sponsorship_ids as $t_id ) {
					$t_sponsorship = sponsorship_get( $t_id );
					$t_date_added = date( config_get( 'normal_date_format' ), $t_sponsorship->date_submitted );

					echo ($i > 0) ? '<br />' : '';
					$i++;

					echo sprintf( lang_get( 'label' ), $t_date_added ) . lang_get( 'word_separator' );
					print_user( $t_sponsorship->user_id );
					echo ' (' . sponsorship_format_amount( $t_sponsorship->amount ) . ')';
					if( access_has_bug_level( config_get( 'handle_sponsored_bugs_threshold' ), $f_bug_id ) ) {
						echo ' ' . get_enum_element( 'sponsorship', $t_sponsorship->paid );
					}
				}
			}
		?>
		</td>
		</tr>
<?php
		}
?>
</table>

<?php
	collapse_closed( 'sponsorship' );
?>

<table class="width100" cellspacing="1">
	<tr>
		<td class="form-title"><?php
			collapse_icon( 'sponsorship' );
			echo lang_get( 'users_sponsoring_bug' );

			$t_details_url = lang_get( 'sponsorship_process_url' );
			if( !is_blank( $t_details_url ) ) {
				echo '&#160;[<a href="' . $t_details_url . '">'
					. lang_get( 'sponsorship_more_info' ) . '</a>]';
			}

			if( $t_total_sponsorship > 0 ) {
				echo ' <span style="font-weight: normal;">(';
				echo sprintf( lang_get( 'total_sponsorship_amount' ),
				sponsorship_format_amount( $t_total_sponsorship ) );
				echo ')</span>';
			}
?>
		</td>
	</tr>
</table>

<?php
	collapse_end( 'sponsorship' );
} # If sponsorship enabled
