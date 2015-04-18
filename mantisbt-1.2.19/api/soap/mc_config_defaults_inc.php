<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2014  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

# Minimum global access level required to access webservice for readonly operations.
$g_mc_readonly_access_level_threshold = REPORTER;

# Minimum global access level required to access webservice for read/write operations.
$g_mc_readwrite_access_level_threshold = REPORTER;

# Minimum global access level required to access the administrator webservices
$g_mc_admin_access_level_threshold = MANAGER;

# Minimum project access level required to be able to specify a reporter name when
# adding an issue.  Otherwise, the current user is used as the reporter.  Users
# who don't have this access level can always do another step to modify the issue
# and specify a different name, but in this case it will be logged in the history
# who original reported the issue.
$g_mc_specify_reporter_on_add_access_level_threshold = DEVELOPER;

# The following enum ids are used when the webservices get enum labels that are not
# defined in the associated MantisBT installation.  In this case, the enum id is set
# to the value specified by the corresponding configuration option.
$g_mc_priority_enum_default_when_not_found = 0;
$g_mc_severity_enum_default_when_not_found = 0;
$g_mc_status_enum_default_when_not_found = 0;
$g_mc_resolution_enum_default_when_not_found = 0;
$g_mc_projection_enum_default_when_not_found = 0;
$g_mc_eta_enum_default_when_not_found = 0;

# If ON and the supplied category is not found, then a SoapException will be raised.
# (at the moment this value does not depend on the project).
$g_mc_error_when_category_not_found = ON;

# Default category to be used if the specified category is not found and $g_mc_error_when_category_not_found == OFF.
$g_mc_category_when_not_found = '';

# If ON and the supplied version is not found, then a SoapException will be raised.
$g_mc_error_when_version_not_found = ON;

# Default version to be used if the specified version is not found and $g_mc_error_when_version_not_found == OFF.
# (at the moment this value does not depend on the project).
$g_mc_version_when_not_found = '';

/**
 * MantisConnect - use nusoap for SOAP handling
 * 
 * <p>When the native PHP extension is available this flag default to OFF, which means that nusoap 
 * will not used. The native extension has the advantage of being faster, more memory efficient and 
 * maintained to work with recent versions on PHP. When the extension is not available 
 * MantisBT falls back to using nusoap.</p>
 */
$g_mc_use_nusoap = extension_loaded('soap') && defined('SOAP_USE_XSI_ARRAY_TYPE') ? OFF : ON;
