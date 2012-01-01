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
 * Authentication API
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 */

 /**
  * requires helper_api
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'gpc_api.php' );

/**
 *
 * @global array $g_script_login_cookie
 */
$g_script_login_cookie = null;

/**
 *
 * @global array $g_cache_anonymous_user_cookie_string
 */
$g_cache_anonymous_user_cookie_string = null;

/**
 *
 * @global array $g_cache_cookie_valid
 */
$g_cache_cookie_valid = null;

/**
 *
 * @global array $g_cache_current_user_id
 */
$g_cache_current_user_id = null;

/**
 * Check that there is a user logged-in and authenticated
 * If the user's account is disabled they will be logged out
 * If there is no user logged in, redirect to the login page
 * If parameter is given it is used as a URL to redirect to following
 * successful login.  If none is given, the URL of the current page is used
 * @param string $p_return_page Page to redirect to following successful logon, defaults to current page
 * @access public
 */
function auth_ensure_user_authenticated( $p_return_page = '' ) {
	# if logged in
	if( auth_is_user_authenticated() ) {
		# check for access enabled
		#  This also makes sure the cookie is valid
		if( OFF == current_user_get_field( 'enabled' ) ) {
			print_header_redirect( 'logout_page.php' );
		}
	} else {
		# not logged in
		if( is_blank( $p_return_page ) ) {
			if( !isset( $_SERVER['REQUEST_URI'] ) ) {
				$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
			}
			$p_return_page = $_SERVER['REQUEST_URI'];
		}
		$p_return_page = string_url( $p_return_page );
		print_header_redirect( 'login_page.php?return=' . $p_return_page );
	}
}

/**
 * Return true if there is a currently logged in and authenticated user, false otherwise
 *
 * @param boolean auto-login anonymous user
 * @return bool
 * @access public
 */
function auth_is_user_authenticated() {
	global $g_cache_cookie_valid, $g_login_anonymous;
	if( $g_cache_cookie_valid == true ) {
		return $g_cache_cookie_valid;
	}
	$g_cache_cookie_valid = auth_is_cookie_valid( auth_get_current_user_cookie( $g_login_anonymous ) );
	return $g_cache_cookie_valid;
}

/**
 * prepare/override the username provided from logon form (if necessary)
 * @todo when we rewrite authentication api for plugins, this should be merged with prepare_password and return some object
 * @param string $p_username
 * @return string prepared username
 * @access public
 */
function auth_prepare_username( $p_username ) {
	switch( config_get( 'login_method' ) ) {
		case BASIC_AUTH:
			$f_username = $_SERVER['REMOTE_USER'];
			break;
		case HTTP_AUTH:
			if( !auth_http_is_logout_pending() ) {
				if( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
					$f_username = $_SERVER['PHP_AUTH_USER'];
				}
			} else {
				auth_http_set_logout_pending( false );
				auth_http_prompt();

				/* calls exit */
				return;
			}
			break;
		default:
			$f_username = $p_username;
			break;
	}
	return $f_username;
}

/**
 * prepare/override the password provided from logon form (if necessary)
 * @todo when we rewrite authentication api for plugins, this should be merged with prepare_username and return some object
 * @param string $p_password
 * @return string prepared password
 * @access public
 */
function auth_prepare_password( $p_password ) {
	switch( config_get( 'login_method' ) ) {
		case BASIC_AUTH:
			$f_password = $_SERVER['PHP_AUTH_PW'];
			break;
		case HTTP_AUTH:
			if( !auth_http_is_logout_pending() ) {

				/* this will never get hit - see auth_prepare_username */
				if( isset( $_SERVER['PHP_AUTH_PW'] ) ) {
					$f_password = $_SERVER['PHP_AUTH_PW'];
				}
			} else {
				auth_http_set_logout_pending( false );
				auth_http_prompt();

				/* calls exit */
				return;
			}
			break;
		default:
			$f_password = $p_password;
			break;
	}
	return $f_password;
}

/**
 * Attempt to login the user with the given password
 * If the user fails validation, false is returned
 * If the user passes validation, the cookies are set and
 * true is returned.  If $p_perm_login is true, the long-term
 * cookie is created.
 * @param string $p_username a prepared username
 * @param string $p_password a prepared password
 * @param bool $p_perm_login whether to create a long-term cookie
 * @return bool indicates if authentication was successful
 * @access public
 */
function auth_attempt_login( $p_username, $p_password, $p_perm_login = false ) {
	$t_user_id = user_get_id_by_name( $p_username );

	$t_login_method = config_get( 'login_method' );

	if ( false === $t_user_id ) {
		if ( BASIC_AUTH == $t_login_method ) {
			$t_auto_create = true;
		} else if ( LDAP == $t_login_method && ldap_authenticate_by_username( $p_username, $p_password ) ) {
			$t_auto_create = true;
		} else {
			$t_auto_create = false;
		}

		if ( $t_auto_create ) {
			# attempt to create the user
			$t_cookie_string = user_create( $p_username, md5( $p_password ) );

			if ( false === $t_cookie_string ) {
				# it didn't work
				return false;
			}

			# ok, we created the user, get the row again
			$t_user_id = user_get_id_by_name( $p_username );

			if( false === $t_user_id ) {
				# uh oh, something must be really wrong
				# @@@ trigger an error here?
				return false;
			}
		} else {
			return false;
		}
	}

	# check for disabled account
	if( !user_is_enabled( $t_user_id ) ) {
		return false;
	}

	# max. failed login attempts achieved...
	if( !user_is_login_request_allowed( $t_user_id ) ) {
		return false;
	}

	# check for anonymous login
	if( !user_is_anonymous( $t_user_id ) ) {
		# anonymous login didn't work, so check the password

		if( !auth_does_password_match( $t_user_id, $p_password ) ) {
			user_increment_failed_login_count( $t_user_id );
			return false;
		}
	}

	# ok, we're good to login now
	# increment login count
	user_increment_login_count( $t_user_id );

	user_reset_failed_login_count_to_zero( $t_user_id );
	user_reset_lost_password_in_progress_count_to_zero( $t_user_id );

	# set the cookies
	auth_set_cookies( $t_user_id, $p_perm_login );
	auth_set_tokens( $t_user_id );

	return true;
}

/**
 * Allows scripts to login using a login name or ( login name + password )
 * @param string $p_username username
 * @param string $p_password username
 * @return bool indicates if authentication was successful
 * @access public
 */
function auth_attempt_script_login( $p_username, $p_password = null ) {
	global $g_script_login_cookie, $g_cache_current_user_id;

	$t_user_id = user_get_id_by_name( $p_username );

	if( false === $t_user_id ) {
		return false;
	}

	$t_user = user_get_row( $t_user_id );

	# check for disabled account
	if( OFF == $t_user['enabled'] ) {
		return false;
	}

	# validate password if supplied
	if( null !== $p_password ) {
		if( !auth_does_password_match( $t_user_id, $p_password ) ) {
			return false;
		}
	}

	# ok, we're good to login now
	# With cases like RSS feeds and MantisConnect there is a login per operation, hence, there is no
	# real significance of incrementing login count.
	# increment login count
	# user_increment_login_count( $t_user_id );
	# set the cookies
	$g_script_login_cookie = $t_user['cookie_string'];

	# cache user id for future reference
	$g_cache_current_user_id = $t_user_id;

	return true;
}

/**
 * Logout the current user and remove any remaining cookies from their browser
 * Returns true on success, false otherwise
 * @access public
 */
function auth_logout() {
	global $g_cache_current_user_id, $g_cache_cookie_valid;

	# clear cached userid
	user_clear_cache( $g_cache_current_user_id );
	$g_cache_current_user_id = null;
	$g_cache_cookie_valid = null;

	# clear cookies, if they were set
	if( auth_clear_cookies() ) {
		helper_clear_pref_cookies();
	}

	if( HTTP_AUTH == config_get( 'login_method' ) ) {
		auth_http_set_logout_pending( true );
	}

	session_clean();
}

/**
 * Identicates whether to bypass logon form e.g. when using http auth
 * @return bool
 * @access public
 */
function auth_automatic_logon_bypass_form() {
	switch( config_get( 'login_method' ) ) {
		case HTTP_AUTH:
			return true;
	}
	return false;
}

/**
 * Return the user's password maximum length for the current login method
 *
 * @return int
 * @access public
 */
function auth_get_password_max_size() {
	switch( config_get( 'login_method' ) ) {
		# Max password size cannot be bigger than the database field
		case PLAIN:
		case BASIC_AUTH:
		case HTTP_AUTH:
			return DB_FIELD_SIZE_PASSWORD;

		# All other cases, i.e. password is stored as a hash
		default:
			return PASSWORD_MAX_SIZE_BEFORE_HASH;
	}
}

/**
 * Return true if the password for the user id given matches the given
 * password (taking into account the global login method)
 * @param int $p_user_id User id to check password against
 * @param string $p_test_password Password
 * @return bool indicating whether password matches given the user id
 * @access public
 */
function auth_does_password_match( $p_user_id, $p_test_password ) {
	$t_configured_login_method = config_get( 'login_method' );

	if( LDAP == $t_configured_login_method ) {
		return ldap_authenticate( $p_user_id, $p_test_password );
	}

	$t_password = user_get_field( $p_user_id, 'password' );
	$t_login_methods = Array(
		MD5,
		CRYPT,
		PLAIN,
	);
	foreach( $t_login_methods as $t_login_method ) {

		# pass the stored password in as the salt
		if( auth_process_plain_password( $p_test_password, $t_password, $t_login_method ) == $t_password ) {

			# Do not support migration to PLAIN, since this would be a crazy thing to do.
			# Also if we do, then a user will be able to login by providing the MD5 value
			# that is copied from the database.  See #8467 for more details.
			if( $t_configured_login_method != PLAIN && $t_login_method == PLAIN ) {
				continue;
			}

			# Check for migration to another login method and test whether the password was encrypted
			# with our previously insecure implemention of the CRYPT method
			if(( $t_login_method != $t_configured_login_method ) || (( CRYPT == $t_configured_login_method ) && utf8_substr( $t_password, 0, 2 ) == utf8_substr( $p_test_password, 0, 2 ) ) ) {
				user_set_password( $p_user_id, $p_test_password, true );
			}

			return true;
		}
	}

	return false;
}

/**
 * Encrypt and return the plain password given, as appropriate for the current
 *  global login method.
 *
 * When generating a new password, no salt should be passed in.
 * When encrypting a password to compare to a stored password, the stored
 *  password should be passed in as salt.  If the auth method is CRYPT then
 *  crypt() will extract the appropriate portion of the stored password as its salt
 *
 * @param string $p_password
 * @param string $p_salt salt, defaults to null
 * @param string $p_method logon method, defaults to null (use config login method)
 * @return string processed password, maximum DB_FIELD_SIZE_PASSWORD chars in length
 * @access public
 */
 function auth_process_plain_password( $p_password, $p_salt = null, $p_method = null ) {
	$t_login_method = config_get( 'login_method' );
	if( $p_method !== null ) {
		$t_login_method = $p_method;
	}

	switch( $t_login_method ) {
		case CRYPT:

			# a null salt is the same as no salt, which causes a salt to be generated
			# otherwise, use the salt given
			$t_processed_password = crypt( $p_password, $p_salt );
			break;
		case MD5:
			$t_processed_password = md5( $p_password );
			break;
		case BASIC_AUTH:
		case PLAIN:
		default:
			$t_processed_password = $p_password;
			break;
	}

	# cut this off to DB_FIELD_SIZE_PASSWORD characters which the largest possible string in the database
	return utf8_substr( $t_processed_password, 0, DB_FIELD_SIZE_PASSWORD );
}

/**
 * Generate a random 12 character password
 * @todo Review use of $p_email within mantis
 * @param string $p_email unused
 * @return string 12 character random password
 * @access public
 */
function auth_generate_random_password( $p_email ) {
	$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
	$t_val = md5( $t_val );

	return utf8_substr( $t_val, 0, 12 );
}

/**
 * Generate a confirm_hash 12 character to valide the password reset request
 * @param int $p_user_id user id
 * @return string representing MD5 hash
 * @access public
 */
function auth_generate_confirm_hash( $p_user_id ) {
	$t_confirm_hash_generator = config_get( 'password_confirm_hash_magic_string' );
	$t_password = user_get_field( $p_user_id, 'password' );
	$t_last_visit = user_get_field( $p_user_id, 'last_visit' );

	$t_confirm_hash = md5( $t_confirm_hash_generator . $t_password . $t_last_visit );

	return $t_confirm_hash;
}

/**
 * Set login cookies for the user
 * If $p_perm_login is true, a long-term cookie is created
 * @param int $p_user_id user id
 * @param bool $p_perm_login indicates whether to generate a long-term cookie
 * @access public
 */
function auth_set_cookies( $p_user_id, $p_perm_login = false ) {
	$t_cookie_string = user_get_field( $p_user_id, 'cookie_string' );

	$t_cookie_name = config_get( 'string_cookie' );

	if( $p_perm_login ) {
		# set permanent cookie (1 year)
		gpc_set_cookie( $t_cookie_name, $t_cookie_string, true );
	} else {
		# set temp cookie, cookie dies after browser closes
		gpc_set_cookie( $t_cookie_name, $t_cookie_string, false );
	}
}

/**
 * Clear login cookies, return true if they were cleared
 * @return bool indicating whether cookies were cleared
 * @access public
 */
function auth_clear_cookies() {
	global $g_script_login_cookie, $g_cache_cookie_valid;

	$t_cookies_cleared = false;
	$g_cache_cookie_valid = null;

	# clear cookie, if not logged in from script
	if( $g_script_login_cookie == null ) {
		$t_cookie_name = config_get( 'string_cookie' );
		$t_cookie_path = config_get( 'cookie_path' );

		gpc_clear_cookie( $t_cookie_name, $t_cookie_path );
		$t_cookies_cleared = true;
	} else {
		$g_script_login_cookie = null;
	}
	return $t_cookies_cleared;
}

/**
 * Generate a string to use as the identifier for the login cookie
 * It is not guaranteed to be unique and should be checked
 * The string returned should be 64 characters in length
 * @return string 64 character cookie string
 * @access public
 */
function auth_generate_cookie_string() {
	$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
	$t_val = md5( $t_val ) . md5( time() );
	return $t_val;
}

/**
 * Generate a UNIQUE string to use as the identifier for the login cookie
 * The string returned should be 64 characters in length
 * @return string 64 character cookie string
 * @access public
 */
function auth_generate_unique_cookie_string() {
	do {
		$t_cookie_string = auth_generate_cookie_string();
	}
	while( !auth_is_cookie_string_unique( $t_cookie_string ) );

	return $t_cookie_string;
}

/**
 * Return true if the cookie login identifier is unique, false otherwise
 * @param string $p_cookie_string
 * @return bool indicating whether cookie string is unique
 * @access public
 */
function auth_is_cookie_string_unique( $p_cookie_string ) {
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT COUNT(*)
				  FROM $t_user_table
				  WHERE cookie_string=" . db_param();
	$result = db_query_bound( $query, Array( $p_cookie_string ) );
	$t_count = db_result( $result );

	if( $t_count > 0 ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Return the current user login cookie string,
 * note that the cookie cached by a script login superceeds the cookie provided by
 *  the browser. This shouldn't normally matter, except that the password verification uses
 *  this routine to bypass the normal authentication, and can get confused when a normal user
 *  logs in, then runs the verify script. the act of fetching config variables may get the wrong
 *  userid.
 * if no user is logged in and anonymous login is enabled, returns cookie for anonymous user
 * otherwise returns '' (an empty string)
 *
 * @param boolean auto-login anonymous user
 * @return string current user login cookie string
 * @access public
 */
function auth_get_current_user_cookie( $p_login_anonymous=true ) {
	global $g_script_login_cookie, $g_cache_anonymous_user_cookie_string;

	# if logging in via a script, return that cookie
	if( $g_script_login_cookie !== null ) {
		return $g_script_login_cookie;
	}

	# fetch user cookie
	$t_cookie_name = config_get( 'string_cookie' );
	$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

	# if cookie not found, and anonymous login enabled, use cookie of anonymous account.
	if( is_blank( $t_cookie ) ) {
		if( $p_login_anonymous && ON == config_get( 'allow_anonymous_login' ) ) {
			if( $g_cache_anonymous_user_cookie_string === null ) {
				if( function_exists( 'db_is_connected' ) && db_is_connected() ) {

					# get anonymous information if database is available
					$query = 'SELECT id, cookie_string FROM ' . db_get_table( 'mantis_user_table' ) . ' WHERE username = ' . db_param();
					$result = db_query_bound( $query, Array( config_get( 'anonymous_account' ) ) );

					if( 1 == db_num_rows( $result ) ) {
						$row = db_fetch_array( $result );
						$t_cookie = $row['cookie_string'];

						$g_cache_anonymous_user_cookie_string = $t_cookie;
						$g_cache_current_user_id = $row['id'];
					}
				}
			} else {
				$t_cookie = $g_cache_anonymous_user_cookie_string;
			}
		}
	}

	return $t_cookie;
}

/**
 * Set authentication tokens for secure session.
 * @param integer User ID
 * @access public
 */
function auth_set_tokens( $p_user_id ) {
	$t_auth_token = token_get( TOKEN_AUTHENTICATED, $p_user_id );
	if( null == $t_auth_token ) {
		token_set( TOKEN_AUTHENTICATED, true, config_get_global( 'reauthentication_expiry' ), $p_user_id );
	} else {
		token_touch( $t_auth_token['id'], config_get_global( 'reauthentication_expiry' ) );
	}
}

/**
 * Check for authentication tokens, and display re-authentication page if needed.
 * Currently, if using BASIC or HTTP authentication methods, or if logged in anonymously,
 * this function will always "authenticate" the user (do nothing).
 *
 * @return bool
 * @access public
 */
function auth_reauthenticate() {
	if( config_get_global( 'reauthentication' ) == OFF || BASIC_AUTH == config_get( 'login_method' ) || HTTP_AUTH == config_get( 'login_method' ) ) {
		return true;
	}

	$t_auth_token = token_get( TOKEN_AUTHENTICATED );
	if( null != $t_auth_token ) {
		token_touch( $t_auth_token['id'], config_get_global( 'reauthentication_expiry' ) );
		return true;
	} else {
		$t_anon_account = config_get( 'anonymous_account' );
		$t_anon_allowed = config_get( 'allow_anonymous_login' );

		$t_user_id = auth_get_current_user_id();
		$t_username = user_get_field( $t_user_id, 'username' );

		# check for anonymous login
		if( ON == $t_anon_allowed && $t_anon_account == $t_username ) {
			return true;
		}

		return auth_reauthenticate_page( $t_user_id, $t_username );
	}
}

/**
 * Generate the intermediate authentication page.
 * @param integer User ID
 * @param string Username
 * @return bool
 * @access public
 */
function auth_reauthenticate_page( $p_user_id, $p_username ) {
	$t_error = false;

	if( true == gpc_get_bool( '_authenticate' ) ) {
		$f_password = gpc_get_string( 'password', '' );

		if( auth_attempt_login( $p_username, $f_password ) ) {
			auth_set_tokens( $p_user_id );
			return true;
		} else {
			$t_error = true;
		}
	}

	html_page_top();

	?>
<div align="center">
<p>
<?php
		echo lang_get( 'reauthenticate_message' );
	if( $t_error != false ) {
		echo '<br /><font color="red">', lang_get( 'login_error' ), '</font>';
	}
	?>
</p>
<form name="reauth_form" method="post" action="<?php echo string_attribute( form_action_self() ) ?>">
<?php
	# CSRF protection not required here - user needs to enter password
	# (confirmation step) before the form is accepted.
	print_hidden_inputs( gpc_strip_slashes( $_POST ) );
	print_hidden_inputs( gpc_strip_slashes( $_GET ) );
?>

<input type="hidden" name="_authenticate" value="1" />

<table class="width50 center">
<tr>
	<td class="form-title" colspan="2"><?php echo lang_get( 'reauthenticate_title' ); ?></td>
</tr>

<tr class="row-1">
	<td class="category"><?php echo lang_get( 'username' );?></td>
	<td><input type="text" disabled="disabled" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" value="<?php echo $p_username;?>" /></td>
</tr>

<tr class="row-2">
	<td class="category"><?php echo lang_get( 'password' );?></td>
	<td><input type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" /></td>
</tr>

<tr>
	<td class="center" colspan="2"><input type="submit" class="button" value="<?php echo lang_get( 'login_button' );?>" /></td>
</tr>
</table>

</form>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<!-- Autofocus JS -->
<script type="text/javascript" language="JavaScript">
<!--
	window.document.reauth_form.password.focus();
// -->
</script>
<?php } ?>

		<?php
		html_page_bottom();

	exit;
}

/**
 * is cookie valid?
 * @param string $p_cookie_string
 * @return bool
 * @access public
 */
function auth_is_cookie_valid( $p_cookie_string ) {
	global $g_cache_current_user_id;

	# fail if DB isn't accessible
	if( !db_is_connected() ) {
		return false;
	}

	# fail if cookie is blank
	if( '' === $p_cookie_string ) {
		return false;
	}

	# succeeed if user has already been authenticated
	if( null !== $g_cache_current_user_id ) {
		return true;
	}

	if( user_search_cache( 'cookie_string', $p_cookie_string ) ) {
		return true;
	}

	# look up cookie in the database to see if it is valid
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT *
				  FROM $t_user_table
				  WHERE cookie_string=" . db_param();
	$result = db_query_bound( $query, Array( $p_cookie_string ) );

	# return true if a matching cookie was found
	if( 1 == db_num_rows( $result ) ) {
		user_cache_database_result( db_fetch_array( $result ) );
		return true;
	} else {
		return false;
	}
}

/**
 * Retrieve user id of current user
 * @return int user id
 * @access public
 */
function auth_get_current_user_id() {
	global $g_cache_current_user_id;

	if( null !== $g_cache_current_user_id ) {
		return $g_cache_current_user_id;
	}

	$t_cookie_string = auth_get_current_user_cookie();

	if( $t_result = user_search_cache( 'cookie_string', $t_cookie_string ) ) {
		$t_user_id = (int) $t_result['id'];
		$g_cache_current_user_id = $t_user_id;
		return $t_user_id;
	}

	$t_user_table = db_get_table( 'mantis_user_table' );

	/** @todo error with an error saying they aren't logged in? Or redirect to the login page maybe? */
	$query = "SELECT id
				  FROM $t_user_table
				  WHERE cookie_string=" . db_param();
	$result = db_query_bound( $query, Array( $t_cookie_string ) );

	# The cookie was invalid. Clear the cookie (to allow people to log in again)
	# and give them an Access Denied message.
	if( db_num_rows( $result ) < 1 ) {
		auth_clear_cookies();
		access_denied();
		exit();
	}

	$t_user_id = (int) db_result( $result );
	$g_cache_current_user_id = $t_user_id;

	return $t_user_id;
}


/**
 *
 * @access public
 */
function auth_http_prompt() {
	header( 'HTTP/1.0 401 Authorization Required' );
	header( 'WWW-Authenticate: Basic realm="' . lang_get( 'http_auth_realm' ) . '"' );
	header( 'status: 401 Unauthorized' );

	echo '<center>';
	echo '<p>' . error_string( ERROR_ACCESS_DENIED ) . '</p>';
	print_bracket_link( 'main_page.php', lang_get( 'proceed' ) );
	echo '</center>';

	exit;
}

/**
 *
 * @param bool $p_pending
 * @access public
 */
function auth_http_set_logout_pending( $p_pending ) {
	$t_cookie_name = config_get( 'logout_cookie' );

	if( $p_pending ) {
		gpc_set_cookie( $t_cookie_name, '1', false );
	} else {
		$t_cookie_path = config_get( 'cookie_path' );
		gpc_clear_cookie( $t_cookie_name, $t_cookie_path );
	}
}

/**
 *
 * @return bool
 * @access public
 */
function auth_http_is_logout_pending() {
	$t_cookie_name = config_get( 'logout_cookie' );
	$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

	return( $t_cookie > '' );
}
