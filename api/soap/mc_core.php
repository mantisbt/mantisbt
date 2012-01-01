<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

# Path to MantisBT is assumed to be the grand parent directory.  If this is not
# the case, then this variable should be set to the MantisBT path.
# This can not be a configuration option, then MantisConnect configuration
# needs MantisBT to be included first to make use of the constants and possibly
# configuration defined in MantisBT.
$t_mantis_dir = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR;

# include Mantis files
$g_bypass_headers = true;
require_once( $t_mantis_dir . 'core.php' );

# constants and configurations
$t_current_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
require_once( $t_current_dir . 'mc_config_defaults_inc.php' );

$t_user_configs = $t_current_dir . 'mc_config_inc.php';
if( file_exists( $t_user_configs ) ) {
	require_once( $t_user_configs );
}

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
