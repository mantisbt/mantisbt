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
	 * This page updates a user's sponsorships
	 * If an account is protected then changes are forbidden
	 * The page gets redirected back to account_page.php
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

	require_once( 'email_api.php' );

	form_security_validate( 'account_sponsor_update' );

	auth_ensure_user_authenticated();

	$f_bug_list = gpc_get_string( 'buglist', '' );
	$t_bug_list = explode( ',', $f_bug_list );

	foreach ( $t_bug_list as $t_bug ) {
		list( $t_bug_id, $t_sponsor_id ) = explode( ':', $t_bug );
		$c_bug_id = (int) $t_bug_id;

		bug_ensure_exists( $c_bug_id ); # dies if bug doesn't exist

		access_ensure_bug_level( config_get( 'handle_sponsored_bugs_threshold' ), $c_bug_id ); # dies if user can't handle bug

		$t_bug = bug_get( $c_bug_id );
		$t_sponsor = sponsorship_get( (int) $t_sponsor_id );

		$t_new_payment = gpc_get_int( 'sponsor_' . $c_bug_id . '_' . $t_sponsor->id, $t_sponsor->paid );
		if ( $t_new_payment != $t_sponsor->paid ) {
			sponsorship_update_paid( $t_sponsor_id, $t_new_payment );
		}
	}

	form_security_purge( 'account_sponsor_update' );

	$t_redirect = 'account_sponsor_page.php';
	html_page_top( null, $t_redirect );

	echo '<br /><div align="center">';

	echo lang_get( 'payment_updated' ) . '<br />';

	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
	echo '</div>';
	html_page_bottom();
