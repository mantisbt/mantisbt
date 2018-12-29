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
 * Mantis Configuration Checks
 *
 * This process runs a number of configuration checks against the mantis configuration to find
 * common issues.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 */

# Load the MantisDB core in maintenance mode. This mode will assume that
# config_inc.php hasn't been specified. Thus the database will not be opened
# and plugins will not be loaded.
define( 'MANTIS_MAINTENANCE_MODE', true );

# Disable output buffering and compression so that test results are returned to
# the user in near real time. This ensures that the user can see the progress
# of the tests, ensuring that the testing process hasn't frozen.
define( 'COMPRESSION_DISABLED', true );

require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/core.php' );

require_once( 'check_api.php' );

require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'http_api.php' );

# Initialise a special error handler for use with check.php so that errors are
# not treated as being fatal. Instead, integrate error handling inline with the
# test results.
check_init_error_handler();

# Increase the time limit for this script to 5 minutes execution time as some
# of the tests may take a long time to complete.
set_time_limit( 60 * 5 );

$g_show_all = gpc_get_bool( 'show_all', false );
$g_show_errors = gpc_get_bool( 'show_errors', false );

/**
 * Returns a URL to implement filtering on the admin check page
 *
 * @param integer $p_all    Whether to show all check out.
 * @param integer $p_errors Whether to show all errors.
 * @return string url
 */
function mode_url( $p_all, $p_errors ) {
	return basename( __FILE__ ) . '?' . http_build_query(
		array(
			'show_all' => (int)$p_all,
			'show_errors' => (int)$p_errors,
		)
	);
}

$t_link = '<a href="%s">%s %s</a>';
$t_show_all_mode_link = sprintf( $t_link,
	mode_url( !$g_show_all, $g_show_errors ),
	($g_show_all ? 'Hide' : 'Show'),
	'passed tests'
);
$t_show_errors_mode_link = sprintf( $t_link,
	mode_url( $g_show_all, !$g_show_errors ),
	($g_show_errors ? 'Hide' : 'Show'),
	'verbose error messages'
);


layout_page_header( 'MantisBT Administration - Check Installation' );

layout_admin_page_begin();
print_admin_menu_bar( 'check/index.php' );

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		Checking your MantisBT installation...
	</h4>
</div>

<div class="widget-body">
	<div class="widget-toolbox padding-8 clearfix">
		Verbosity: <?php echo $t_show_all_mode_link ?> | <?php echo $t_show_errors_mode_link ?>
	</div>
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed">

<?php

define( 'CHECK_PHP_INC_ALLOW', true );
include( 'check_php_inc.php' );

if( !$g_failed_test ) {
	define( 'CHECK_DATABASE_INC_ALLOW', true );
	include( 'check_database_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_CONFIG_INC_ALLOW', true );
	include( 'check_config_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_PATHS_INC_ALLOW', true );
	include( 'check_paths_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_WEBSERVICE_INC_ALLOW', true );
	include( 'check_webservice_inc.php' );
}

/*
 * Disable integrity since the required blobs are no longer available
 * See https://sourceforge.net/p/mantisbt/mailman/message/24608409/
 *
if( !$g_failed_test ) {
	define( 'CHECK_INTEGRITY_INC_ALLOW', true );
	include( 'check_integrity_inc.php' );
}
*/

if( !$g_failed_test ) {
	define( 'CHECK_CRYPTO_INC_ALLOW', true );
	include( 'check_crypto_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_I18N_INC_ALLOW', true );
	include( 'check_i18n_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_L10N_INC_ALLOW', true );
	include( 'check_L10n_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_EMAIL_INC_ALLOW', true );
	include( 'check_email_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_ANONYMOUS_INC_ALLOW', true );
	include( 'check_anonymous_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_ATTACHMENTS_INC_ALLOW', true );
	include( 'check_attachments_inc.php' );
}

if( !$g_failed_test ) {
	define( 'CHECK_DISPLAY_INC_ALLOW', true );
	include( 'check_display_inc.php' );
}
?>
</table>
</div>
</div>
</div>
</div>

<div class="space-10"></div>

<?php if( $g_failed_test ) { ?>
	<div class="alert alert-danger" id="check-notice-failed">
		Some tests failed. Please review and correct these failed tests before using MantisBT.
	</div>
<?php } else if( $g_passed_test_with_warnings ) { ?>
	<div class="alert alert-warning" id="check-notice-warnings">
		Some warnings were encountered. Please review and consider correcting these warnings before using MantisBT.
	</div>
<?php } else { ?>
<div class="alert alert-success"  id="check-notice-passed">
	All tests passed.
</div>
<?php } ?>

<div class="alert alert-danger" id="notice-delete-admin">
	For security reasons, you should delete (or at least restrict access to) the
	<em>admin</em> directory.
	Refer to the <a href="http://mantisbt.org/docs/master/en-US/Admin_Guide/html-desktop/#admin.install.postcommon">
		MantisBT Admin Guide</a>
	for further details.
</div>

</div>
<?php
layout_admin_page_end();
