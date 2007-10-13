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
	# $Id: rss_api.php,v 1.1.4.1 2007-10-13 22:35:42 giallu Exp $
	# --------------------------------------------------------

	### RSS API ###

	# --------------------
	# Calculates a key to be used for RSS authentication based on user name, cookie and password.
	# if the user changes his user name or password, then the key becomes invalid.
	function rss_calculate_key( $p_user_id = null ) {
		if ( $p_user_id === null ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		$t_seed     = config_get_global( 'rss_key_seed' );

		$t_username = user_get_field( $t_user_id, 'username' );
		$t_password = user_get_field( $t_user_id, 'password' );
		$t_cookie   = user_get_field( $t_user_id, 'cookie_string' );

		return md5( $t_seed . $t_username . $t_cookie . $t_password );
	}

	# --------------------
	# Given the user name and the rss key, this method attempts to login the user.  If successful, it
	# return true, otherwise, returns false.
	function rss_login( $p_username, $p_key ) {
		if ( ( $p_username === null ) || ( $p_key === null ) ) {
			return false;
		}

		$t_user_id = user_get_id_by_name( $p_username );

		$t_correct_key = rss_calculate_key( $t_user_id );
		if ( $p_key != $t_correct_key ) {
			return false;
		}

		if ( !auth_attempt_script_login( $p_username ) ) {
			return false;
		}

		return true;
	}

	# --------------------
	function rss_get_issues_feed_url( $p_project_id = null, $p_username = null, $p_filter_id = null, $p_relative = true ) {
		if ( $p_username === null ) {
			$t_username = current_user_get_field( 'username' );
		} else {
			$t_username = $p_username;
		}

		if ( $p_project_id === null ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = (integer)$p_project_id;
		}

		$t_user_id = user_get_id_by_name( $t_username );

		if ( $p_relative ) {
			$t_url = config_get( 'path' );
		} else {
			$t_url = '';
		}

		if ( $t_username == config_get( 'anonymous_account' ) ) {
			$t_url .= 'issues_rss.php?';

			if ( $t_project_id == ALL_PROJECTS ) {
				$t_url .= 'project_id=' . $t_project_id;
			}
		} else {
			$t_url .= 'issues_rss.php?username=' . $t_username . '&amp;key=' . rss_calculate_key( $t_user_id );

			if ( $t_project_id != ALL_PROJECTS ) {
				$t_url .= '&amp;project_id=' . $t_project_id;
			}
		}

		if ( $p_filter_id !== null ) {
			$t_url .= '&amp;filter_id=' . $p_filter_id;
		}

		return $t_url;
	}

	# --------------------
	function rss_get_news_feed_url( $p_project_id = null, $p_username = null, $p_relative = true ) {
		if ( $p_username === null ) {
			$t_username = current_user_get_field( 'username' );
		} else {
			$t_username = $p_username;
		}

		if ( $p_project_id === null ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = (integer)$p_project_id;
		}

		if ( $p_relative ) {
			$t_rss_link = '';
		} else {
			$t_rss_link = config_get( 'path' );
		}
		
		$t_user_id = user_get_id_by_name( $t_username );

		// If we have a logged in user then they can be given a 'proper' feed, complete with auth string.
		if ( $t_username == config_get( 'anonymous_account' ) ) {
			$t_rss_link .= "news_rss.php?";

			if ( $t_project_id != ALL_PROJECTS ) {
				$t_rss_link .= "news_rss.php?project_id=" . $t_project_id;
			}
		} else {
			$t_rss_link .= "news_rss.php?username=" . $t_username . "&amp;key=" . rss_calculate_key( $t_user_id );

			if ( $t_project_id != ALL_PROJECTS ) {
				$t_rss_link .= "&amp;project_id=" . $t_project_id;
			}
		}

		return $t_rss_link;
	}
?>