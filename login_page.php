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
	# $Id: login_page.php,v 1.57.2.1 2007-10-13 22:33:19 giallu Exp $
	# --------------------------------------------------------

	# Login page POSTs results to login.php
	# Check to see if the user is already logged in

	require_once( 'core.php' );

	if ( auth_is_user_authenticated() && !current_user_is_anonymous() ) {
		print_header_redirect( config_get( 'default_home_page' ) );
	}

	$f_error		= gpc_get_bool( 'error' );
	$f_cookie_error	= gpc_get_bool( 'cookie_error' );
	$f_return		= gpc_get_string( 'return', '' );

	# Check for HTTP_AUTH. HTTP_AUTH is handled in login.php

	if ( HTTP_AUTH == config_get( 'login_method' ) ) {
		$t_uri = "login.php";

		if ( !$f_return && ON == config_get( 'allow_anonymous_login' ) ) {
			$t_uri = "login_anon.php";
		}

		if ( $f_return ) {
			$t_uri .= "?return=" . urlencode( $f_return );
		}

		print_header_redirect( $t_uri );
		exit;
	}

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

	echo '</div>';
?>

<!-- Login Form BEGIN -->
<br />
<div align="center">
<form name="login_form" method="post" action="login.php">
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
			print_bracket_link( 'login_anon.php', lang_get( 'login_anonymously' ) );
		}
	?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'password' ) ?>
	</td>
	<td>
		<input type="password" name="password" size="16" maxlength="32" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'save_login' ) ?>
	</td>
	<td>
		<input type="checkbox" name="perm_login" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'login_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	PRINT '<br /><div align="center">';
	print_signup_link();
	PRINT '&nbsp;';
	print_lost_password_link();
	PRINT '</div>';

	#
	# Do some checks to warn administrators of possible security holes.
	# Since this is considered part of the admin-checks, the strings are not translated.
	#

	# Warning, if plain passwords are selected
	if ( config_get( 'login_method' ) === PLAIN ) {
		echo '<div class="warning" align="center">';
		echo '<p><font color="red"><strong>WARNING:</strong> Plain password authentication is used, this will expose your passwords to administrators.</font></p>';
		echo '</div>';
	}

	# Generate a warning if administrator/root is valid.
	$t_admin_user_id = user_get_id_by_name( 'administrator' );
	if ( $t_admin_user_id !== false ) {
		if ( user_is_enabled( $t_admin_user_id ) && auth_does_password_match( $t_admin_user_id, 'root' ) ) {
			echo '<div class="warning" align="center">';
			echo '<p><font color="red"><strong>WARNING:</strong> You should disable the default "administrator" account or change its password.</font></p>';
			echo '</div>';
		}
	}

	# Check if the admin directory is available and is readable.
	$t_admin_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
	if ( is_dir( $t_admin_dir ) && is_readable( $t_admin_dir ) ) {
		echo '<div class="warning" align="center">', "\n";
		echo '<p><font color="red"><strong>WARNING:</strong> Admin directory should be removed.</font></p>', "\n";
		echo '</div>', "\n";
			
		# since admin directory and db_upgrade lists are available check for missing db upgrades	
		# Check for db upgrade for versions < 1.0.0 using old upgrader
		$t_db_version = config_get( 'database_version' , 0 );
		# if db version is 0, we haven't moved to new installer.
		if ( $t_db_version == 0 ) {
			if ( db_table_exists( config_get( 'mantis_upgrade_table' ) ) ) {
				$query = "SELECT COUNT(*) from " . config_get( 'mantis_upgrade_table' ) . ";";
				$result = db_query( $query );
				if ( db_num_rows( $result ) < 1 ) {
					$t_upgrade_count = 0;
				} else {
					$t_upgrade_count = (int)db_result( $result );
				}
			} else {
				$t_upgrade_count = 0;
			}

			if ( $t_upgrade_count > 0 ) { # table exists, check for number of updates
				if ( file_exists( 'admin/upgrade_inc.php' ) ) {
					require_once( 'admin/upgrade_inc.php' );
					$t_upgrades_reqd = $upgrade_set->count_items();
				} else {
					// can't find upgrade file, assume system is up to date
					$t_upgrades_reqd = $t_upgrade_count;
				}
			} else {
				$t_upgrades_reqd = 1000; # arbitrarily large number to force an upgrade
			}

			if ( ( $t_upgrade_count != $t_upgrades_reqd ) &&
					( $t_upgrade_count != ( $t_upgrades_reqd + 10 ) ) ) { # there are 10 optional data escaping fixes that may be present
				echo '<div class="warning" align="center">';
				echo '<p><font color="red"><strong>WARNING:</strong> The database structure may be out of date. Please upgrade <a href="admin/upgrade.php">here</a> before logging in.</font></p>';
				echo '</div>';
			}
		}

		# Check for db upgrade for versions > 1.0.0 using new installer and schema
		require_once( 'admin/schema.php' );
		$t_upgrades_reqd = sizeof( $upgrade ) - 1;

		if ( ( 0 < $t_db_version ) &&
				( $t_db_version != $t_upgrades_reqd ) ) {

			if ( $t_db_version < $t_upgrades_reqd ) {
				echo '<div class="warning" align="center">';
				echo '<p><font color="red"><strong>WARNING:</strong> The database structure may be out of date. Please upgrade <a href="admin/install.php">here</a> before logging in.</font></p>';
				echo '</div>';
			} else {
				echo '<div class="warning" align="center">';
				echo '<p><font color="red"><strong>WARNING:</strong> The database structure is more up-to-date than the code installed.  Please upgrade the code.</font></p>';
				echo '</div>';
			}
		}
	}
?>

<!-- Autofocus JS -->
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/javascript" language="JavaScript">
<!--
	window.document.login_form.username.focus();
// -->
</script>
<?php } ?>

<?php html_page_bottom1a( __FILE__ ) ?>
