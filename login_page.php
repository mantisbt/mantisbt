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
	 * Login page POSTs results to login.php
	 * Check to see if the user is already logged in
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	$f_error		 = gpc_get_bool( 'error' );
	$f_cookie_error		 = gpc_get_bool( 'cookie_error' );
	$f_return		 = string_sanitize_url( gpc_get_string( 'return', '' ) );
	$f_username		 = gpc_get_string( 'username', '' );
	$f_perm_login		 = gpc_get_bool( 'perm_login', false );
	$f_secure_session	 = gpc_get_bool( 'secure_session', false );
	$f_secure_session_cookie = gpc_get_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', null );

	$t_session_validation = ( ON == config_get_global( 'session_validation' ) );

	// If user is already authenticated and not anonymous
	if( auth_is_user_authenticated() && !current_user_is_anonymous() ) {
		// If return URL is specified redirect to it; otherwise use default page
		if( !is_blank( $f_return ) ) {
			print_header_redirect( $f_return, false, false, true );
		}
		else {
			print_header_redirect( config_get( 'default_home_page' ) );
		}
	}

	# Check for automatic logon methods where we want the logon to just be handled by login.php
	if ( auth_automatic_logon_bypass_form() ) {
		$t_uri = "login.php";

		if ( ON == config_get( 'allow_anonymous_login' ) ) {
			$t_uri = "login_anon.php";
		}

		if ( !is_blank( $f_return ) ) {
			$t_uri .= "?return=" . string_url( $f_return );
		}

		print_header_redirect( $t_uri );
		exit;
	}

	# Login page shouldn't be indexed by search engines
	html_robots_noindex();

	html_page_top1();
	html_page_top2a();

	echo '<br /><div align="center">';

	# Display short greeting message
	# echo lang_get( 'login_page_info' ) . '<br />';

	# Only echo error message if error variable is set
	if ( $f_error ) {
		echo '<font color="red">' . lang_get( 'login_error' ) . '</font>';
	}
	if ( $f_cookie_error ) {
		echo lang_get( 'login_cookies_disabled' ) . '<br />';
	}

	# Determine if secure_session should default on or off?
	# - If no errors, and no cookies set, default to on.
	# - If no errors, but cookie is set, use the cookie value.
	# - If errors, use the value passed in.
	if ( $t_session_validation ) {
		if ( !$f_error && !$f_cookie_error ) {
			$t_default_secure_session = ( is_null( $f_secure_session_cookie ) ? true : $f_secure_session_cookie );
		} else {
			$t_default_secure_session = $f_secure_session;
		}
	}

	echo '</div>';
?>

<!-- Login Form BEGIN -->
<br />
<div align="center">
<form name="login_form" method="post" action="login.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title">
		<?php
			if ( !is_blank( $f_return ) ) {
			?>
				<input type="hidden" name="return" value="<?php echo string_html_specialchars( $f_return ) ?>" />
				<?php
			}
			echo lang_get( 'login_title' ) ?>
	</td>
	<td class="right">
	<?php
		if ( ON == config_get( 'allow_anonymous_login' ) ) {
			print_bracket_link( 'login_anon.php?return=' . string_url( $f_return ), lang_get( 'login_anonymously' ) );
		}
	?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td>
		<input type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" value="<?php echo string_attribute( $f_username ); ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'password' ) ?>
	</td>
	<td>
		<input type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
	</td>
</tr>
<?php
	if( ON == config_get( 'allow_permanent_cookie' ) ) {
?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'save_login' ) ?>
	</td>
	<td>
	<input type="checkbox" name="perm_login" <?php echo ( $f_perm_login ? 'checked="checked" ' : '' ) ?>/>
	</td>
</tr>
<?php
	}

	if ( $t_session_validation ) {
?>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'secure_session' ) ?>
	</td>
	<td>
	<input type="checkbox" name="secure_session" <?php echo ( $t_default_secure_session ? 'checked="checked" ' : '' ) ?>/>
	<?php echo '<span class="small">' . lang_get( 'secure_session_long' ) . '</span>' ?>
	</td>
</tr>
<?php } ?>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'login_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	echo '<br /><div align="center">';
	print_signup_link();
	echo '&#160;';
	print_lost_password_link();
	echo '</div>';

	#
	# Do some checks to warn administrators of possible security holes.
	# Since this is considered part of the admin-checks, the strings are not translated.
	#

	if ( config_get_global( 'admin_checks' ) == ON ) {

		# Generate a warning if administrator/root is valid.
		$t_admin_user_id = user_get_id_by_name( 'administrator' );
		if ( $t_admin_user_id !== false ) {
			if ( user_is_enabled( $t_admin_user_id ) && auth_does_password_match( $t_admin_user_id, 'root' ) ) {
				echo '<div class="warning" align="center">', "\n";
				echo "\t", '<p><font color="red">', lang_get( 'warning_default_administrator_account_present' ), '</font></p>', "\n";
				echo '</div>', "\n";
			}
		}

		# Check if the admin directory is available and is readable.
		$t_admin_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
		if ( is_dir( $t_admin_dir ) ) {
			echo '<div class="warning" align="center">', "\n";
			echo '<p><font color="red">', lang_get( 'warning_admin_directory_present' ), '</font></p>', "\n";
			echo '</div>', "\n";
		}
		if ( is_dir( $t_admin_dir ) && is_readable( $t_admin_dir ) && is_executable( $t_admin_dir ) && @file_exists( "$t_admin_dir/." ) ) {
			# since admin directory and db_upgrade lists are available check for missing db upgrades
			# Check for db upgrade for versions < 1.0.0 using old upgrader
			$t_db_version = config_get( 'database_version' , 0 );
			# if db version is 0, we haven't moved to new installer.
			if ( $t_db_version == 0 ) {
				$t_upgrade_count = 0;
				if ( db_table_exists( db_get_table( 'mantis_upgrade_table' ) ) ) {
					$query = "SELECT COUNT(*) from " . db_get_table( 'mantis_upgrade_table' ) . ";";
					$result = db_query_bound( $query );
					if ( db_num_rows( $result ) > 0 ) {
						$t_upgrade_count = (int)db_result( $result );
					}
				}

				if ( $t_upgrade_count > 0 ) { # table exists, check for number of updates

					# new config table database version is 0.
					# old upgrade tables exist.
					# assume user is upgrading from <1.0 and therefore needs to update to 1.x before upgrading to 1.2
					echo '<div class="warning" align="center">';
					echo '<p><font color="red">', lang_get( 'error_database_version_out_of_date_1' ), '</font></p>';
					echo '</div>';
				} else {
					# old upgrade tables do not exist, yet config database_version is 0
					echo '<div class="warning" align="center">';
					echo '<p><font color="red">', lang_get( 'error_database_no_schema_version' ), '</font></p>';
					echo '</div>';
				}
			}

			# Check for db upgrade for versions > 1.0.0 using new installer and schema
			require_once( 'admin' . DIRECTORY_SEPARATOR . 'schema.php' );
			$t_upgrades_reqd = count( $upgrade ) - 1;

			if ( ( 0 < $t_db_version ) &&
					( $t_db_version != $t_upgrades_reqd ) ) {

				if ( $t_db_version < $t_upgrades_reqd ) {
					echo '<div class="warning" align="center">';
					echo '<p><font color="red">', lang_get( 'error_database_version_out_of_date_2' ), '</font></p>';
					echo '</div>';
				} else {
					echo '<div class="warning" align="center">';
					echo '<p><font color="red">', lang_get( 'error_code_version_out_of_date' ), '</font></p>';
					echo '</div>';
				}
			}
		}

	} # if 'admin_checks'
?>

<!-- Autofocus JS -->
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/javascript" language="JavaScript">
<!--
	window.document.login_form.<?php if ( is_blank( $f_username ) ) { echo 'username'; } else { echo 'password'; } ?>.focus();
// -->
</script>
<?php } ?>

<?php
	html_page_bottom1a( __FILE__ );
