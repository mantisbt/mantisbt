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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# constants and configurations
$t_current_dir = dirname( __FILE__ ) . '/';

# MantisConnect APIs
#   mc_* = public methods
#   mci_* = internal methods
require_once( $t_current_dir . 'mc_api.php' );
require_once( $t_current_dir . 'mc_account_api.php' );
require_once( $t_current_dir . 'mc_enum_api.php' );
require_once( $t_current_dir . 'mc_filter_api.php' );
require_once( $t_current_dir . 'mc_issue_api.php' );
require_once( $t_current_dir . 'mc_issue_attachment_api.php' );
require_once( $t_current_dir . 'mc_project_api.php' );
require_once( $t_current_dir . 'mc_project_attachment_api.php' );
require_once( $t_current_dir . 'mc_file_api.php' );
require_once( $t_current_dir . 'mc_config_api.php' );
require_once( $t_current_dir . 'mc_custom_field_api.php' );
require_once( $t_current_dir . 'mc_user_pref_api.php' );
require_once( $t_current_dir . 'mc_tag_api.php' );
require_once( $t_current_dir . 'mc_user_profile_api.php' );

unset( $t_current_dir );
