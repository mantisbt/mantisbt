<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

/**
 * MantisConnect - A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
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
