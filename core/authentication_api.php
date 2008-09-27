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
	# $Id: authentication_api.php,v 1.60.2.3 2007-10-19 06:54:58 vboctor Exp $
	# --------------------------------------------------------

	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'gpc_api.php' );

	### Authentication API ###

	$g_script_login_cookie = null;
	$g_cache_anonymous_user_cookie_string = null;

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check that there is a user logged-in and authenticated
	#  If the user's account is disabled they will be logged out
	#  If there is no user logged in, redirect to the login page
	#  If parameter is given it is used as a URL to redirect to following
	#   successful login.  If none is given, the URL of the current page is used
	function auth_ensure_user_authenticated( $p_return_page = '' ) {
		# if logged in
		if ( auth_is_user_authenticated() ) {
			# check for access enabled
			#  This also makes sure the cookie is valid
			if ( OFF == current_user_get_field( 'enabled' ) ) {
				print_header_redirect( 'logout_page.php' );
			}
		} else { # not logged in
			if ( is_blank( $p_return_page ) ) {
				if (!isset($_SERVER['REQUEST_URI'])) {
					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
				}
				$p_return_page = $_SERVER['REQUEST_URI'];
			}
			$p_return_page = string_url( $p_return_page );
			print_header_redirect( 'login_page.php?return=' . $p_return_page );
		}
	}

	# --------------------
	# Return true if there is a currently logged in and authenticated user,
	#  false otherwise
	function auth_is_user_authenticated() {
		return ( auth_is_cookie_valid( auth_get_current_user_cookie() ) );
	}


	#===================================
	# Login / Logout
	#===================================

	# --------------------
	# Attempt to login the user with the given password
	#  If the user fails validation, false is returned
	#  If the user passes validation, the cookies are set and
	#   true is returned.  If $p_perm_login is true, the long-term
	#   cookie is created.
	function auth_attempt_login( $p_username, $p_password, $p_perm_login=false ) {
		$t_user_id = user_get_id_by_name( $p_username );

		$t_login_method = config_get( 'login_method' );

		if ( false === $t_user_id ) {
			if ( BASIC_AUTH == $t_login_method ) {
				# attempt to create the user if using BASIC_AUTH
				$t_cookie_string = user_create( $p_username, $p_password );

				if ( false === $t_cookie_string ) {
					# it didn't work
					return false;
				}

				# ok, we created the user, get the row again
				$t_user_id = user_get_id_by_name( $p_username );

				if ( false === $t_user_id ) {
					# uh oh, something must be really wrong

					# @@@ trigger an error here?

					return false;
				}
			} else {
				return false;
			}
		}

		# check for disabled account
		if ( !user_is_enabled( $t_user_id ) ) {
			return false;
		}

		# max. failed login attempts achieved...
		if( !user_is_login_request_allowed( $t_user_id ) ) {
			return false;
		}

		$t_anon_account = config_get( 'anonymous_account' );
		$t_anon_allowed = config_get( 'allow_anonymous_login' );

		# check for anonymous login
		if ( !( ( ON == $t_anon_allowed ) && ( $t_anon_account == $p_username)  ) ) {
			# anonymous login didn't work, so check the password

			if ( !auth_does_password_match( $t_user_id, $p_password ) ) {
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

	# --------------------
	# Allows scripts to login using a login name or ( login name + password )
	function auth_attempt_script_login( $p_username, $p_password = null ) {
		global $g_script_login_cookie, $g_cache_current_user_id;

		$t_user_id = user_get_id_by_name( $p_username );

		$t_user = user_get_row( $t_user_id );

		# check for disabled account
		if ( OFF == $t_user['enabled'] ) {
			return false;
		}

		# validate password if supplied
		if ( null !== $p_password ) {
			if ( !auth_does_password_match( $t_user_id, $p_password ) ) {
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

	# --------------------
	# Logout the current user and remove any remaining cookies from their browser
	# Returns true on success, false otherwise
	function auth_logout() {
        global $g_cache_current_user_id;
        
        # clear cached userid
        $g_cache_current_user_id = null;
        
        # clear cookies, if they were set  
        if (auth_clear_cookies()) {
            helper_clear_pref_cookies();
        }

		session_clean();

		return true;
	}

	#===================================
	# Password functions
	#===================================

	# --------------------
	# Return true if the password for the user id given matches the given
	#  password (taking into account the global login method)
	function auth_does_password_match( $p_user_id, $p_test_password ) {
		$t_configured_login_method = config_get( 'login_method' );

		if ( LDAP == $t_configured_login_method ) {
			return ldap_authenticate( $p_user_id, $p_test_password );
		}

		$t_password			= user_get_field( $p_user_id, 'password' );
		$t_login_methods	= Array(MD5, CRYPT, PLAIN);
		foreach ( $t_login_methods as $t_login_method ) {

			# pass the stored password in as the salt
			if ( auth_process_plain_password( $p_test_password, $t_password, $t_login_method ) == $t_password ) {
				# Do not support migration to PLAIN, since this would be a crazy thing to do.
				# Also if we do, then a user will be able to login by providing the MD5 value
				# that is copied from the database.  See #8467 for more details.
				if ( $t_configured_login_method != PLAIN && $t_login_method == PLAIN ) {
					continue;
				}

				# Check for migration to another login method and test whether the password was encrypted
				# with our previously insecure implemention of the CRYPT method
				if ( ( $t_login_method != $t_configured_login_method ) ||
					( ( CRYPT == $t_configured_login_method ) && substr( $t_password, 0, 2 ) == substr( $p_test_password, 0, 2 ) ) ) {
					user_set_password( $p_user_id, $p_test_password, true );
				}

				return true;
			}
		}

		return false;
	}

	# --------------------
	# Encrypt and return the plain password given, as appropriate for the current
	#  global login method.
	#
	# When generating a new password, no salt should be passed in.
	# When encrypting a password to compare to a stored password, the stored
	#  password should be passed in as salt.  If the auth method is CRYPT then
	#  crypt() will extract the appropriate portion of the stored password as its salt
	function auth_process_plain_password( $p_password, $p_salt=null, $p_method=null ) {
		$t_login_method = config_get( 'login_method' );
		if ( $p_method !== null ) {
			$t_login_method = $p_method;
		}

		switch ( $t_login_method ) {
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

		# cut this off to 32 cahracters which the largest possible string in the database
		return substr( $t_processed_password, 0, 32 );
	}

	# --------------------
	# Generate a random 12 character password
	# p_email is unused
	function auth_generate_random_password( $p_email ) {
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$t_val = md5( $t_val );

		return substr( $t_val, 0, 12 );
	}

	# --------------------
	# Generate a confirm_hash 12 character to valide the password reset request
	function auth_generate_confirm_hash( $p_user_id ) {
		$t_confirm_hash_generator = config_get( 'password_confirm_hash_magic_string' );
		$t_password = user_get_field( $p_user_id, 'password' );
		$t_last_visit = user_get_field( $p_user_id, 'last_visit' );

		$t_confirm_hash = md5( $t_confirm_hash_generator . $t_password . $t_last_visit );

		return $t_confirm_hash;
	}

	#===================================
	# Cookie functions
	#===================================

	# --------------------
	# Set login cookies for the user
	#  If $p_perm_login is true, a long-term cookie is created
	function auth_set_cookies( $p_user_id, $p_perm_login=false ) {
		$t_cookie_string = user_get_field( $p_user_id, 'cookie_string' );

		$t_cookie_name = config_get( 'string_cookie' );

		if ( $p_perm_login ) {
			# set permanent cookie (1 year)
			gpc_set_cookie( $t_cookie_name, $t_cookie_string, true );
		} else {
			# set temp cookie, cookie dies after browser closes
			gpc_set_cookie( $t_cookie_name, $t_cookie_string, false );
		}
	}

	# --------------------
	# Clear login cookies, return true if they were cleared
	function auth_clear_cookies() {
		global $g_script_login_cookie;

        $t_cookies_cleared = false;
        
        # clear cookie, if not logged in from script
        if ($g_script_login_cookie == null) {
		    $t_cookie_name =  config_get( 'string_cookie' );
		    $t_cookie_path = config_get( 'cookie_path' );

		    gpc_clear_cookie( $t_cookie_name, $t_cookie_path );
            $t_cookies_cleared = true;
        } else {
            $g_script_login_cookie = null;
        }
        return $t_cookies_cleared;
	}

	# --------------------
	# Generate a string to use as the identifier for the login cookie
	# It is not guaranteed to be unique and should be checked
	# The string returned should be 64 characters in length
	function auth_generate_cookie_string() {
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$t_val = md5( $t_val ) . md5( time() );

		return substr( $t_val, 0, 64 );
	}

	# --------------------
	# Generate a UNIQUE string to use as the identifier for the login cookie
	# The string returned should be 64 characters in length
	function auth_generate_unique_cookie_string() {
		do {
			$t_cookie_string = auth_generate_cookie_string();
		} while ( !auth_is_cookie_string_unique( $t_cookie_string ) );

		return $t_cookie_string;
	}

	# --------------------
	# Return true if the cookie login identifier is unique, false otherwise
	function auth_is_cookie_string_unique( $p_cookie_string ) {
		$t_user_table = config_get( 'mantis_user_table' );

		$c_cookie_string = db_prepare_string( $p_cookie_string );

		$query = "SELECT COUNT(*)
				  FROM $t_user_table
				  WHERE cookie_string='$c_cookie_string'";
		$result = db_query( $query );
		$t_count = db_result( $result );

		if ( $t_count > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Return the current user login cookie string,
	# note that the cookie cached by a script login superceeds the cookie provided by
	#  the browser. This shouldn't normally matter, except that the password verification uses
	#  this routine to bypass the normal authentication, and can get confused when a normal user
	#  logs in, then runs the verify script. the act of fetching config variables may get the wrong
	#  userid.
	# if no user is logged in and anonymous login is enabled, returns cookie for anonymous user
	# otherwise returns '' (an empty string)
	function auth_get_current_user_cookie() {
		global $g_script_login_cookie, $g_cache_anonymous_user_cookie_string;
 
		# if logging in via a script, return that cookie
		if ( $g_script_login_cookie !== null ) {
			return $g_script_login_cookie;
		}
			
		# fetch user cookie 
		$t_cookie_name = config_get( 'string_cookie' );
		$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

		# if cookie not found, and anonymous login enabled, use cookie of anonymous account.
		if ( is_blank( $t_cookie ) ) {
			if ( ON == config_get( 'allow_anonymous_login' ) ) {
				if ( $g_cache_anonymous_user_cookie_string === null ) {
                    if ( function_exists( 'db_is_connected' ) && db_is_connected() ) { 
                        # get anonymous information if database is available
                        $query = sprintf('SELECT id, cookie_string FROM %s WHERE username = \'%s\'',
								config_get( 'mantis_user_table' ), config_get( 'anonymous_account' ) );
                        $result = db_query( $query );
                        
                        if ( 1 == db_num_rows( $result ) ) {
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

	#===================================
	# Re-Authentication Tokens
	#===================================

	/**
	 * Set authentication tokens for secure session.
	 * @param integer User ID
	 */
	function auth_set_tokens( $p_user_id ) {
		$t_auth_token = token_get( TOKEN_AUTHENTICATED, $p_user_id );
		if ( null == $t_auth_token ) {
			token_set( TOKEN_AUTHENTICATED, true, TOKEN_EXPIRY_AUTHENTICATED, $p_user_id );
		} else {
			token_touch( $t_auth_token['id'], TOKEN_EXPIRY_AUTHENTICATED );
		}
	}

	/**
	 * Check for authentication tokens, and display re-authentication page if needed.
	 * Currently, if using BASIC or HTTP authentication methods, or if logged in anonymously, 
	 * this function will always "authenticate" the user (do nothing).
	 */
	function auth_reauthenticate() {
		if ( BASIC_AUTH == config_get( 'login_method' ) ||
				HTTP_AUTH == config_get( 'login_method' ) ) {
			return true;
		}

		$t_auth_token = token_get( TOKEN_AUTHENTICATED );
		if ( null != $t_auth_token ) {
			token_touch( $t_auth_token['id'], TOKEN_EXPIRY_AUTHENTICATED );
			return true;
		} else {
			$t_anon_account = config_get( 'anonymous_account' );
			$t_anon_allowed = config_get( 'allow_anonymous_login' );

			$t_user_id = auth_get_current_user_id();
			$t_username = user_get_field( $t_user_id, 'username' );

			# check for anonymous login
			if ( ON == $t_anon_allowed && $t_anon_account == $t_username ) {
				return true;
			}
	
			return auth_reauthenticate_page( $t_user_id, $t_username );
		}
	}

	/**
	 * Generate the intermediate authentication page.
	 * @param integer User ID
	 * @param string Username
	 */
	function auth_reauthenticate_page( $p_user_id, $p_username ) {
		$t_error = false;

		if ( true == gpc_get_bool( '_authenticate' ) ) {
			$f_password     = gpc_get_string( 'password', '' );
					    
			if ( auth_attempt_login( $p_username, $f_password ) ) {
				auth_set_tokens( $p_user_id );
				return true;
			} else {
				$t_error = true;
			}
		}

		html_page_top1();
		html_page_top2();

?>
<div align="center">
<p>
<?php 
		echo lang_get( 'reauthenticate_message' ); 
		if ( $t_error != false ) {
			echo '<br/><font color="red">',lang_get( 'login_error' ),'</font>';
		}
?>
</p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<?php
		print_hidden_inputs( gpc_strip_slashes( $_POST ) );
		print_hidden_inputs( gpc_strip_slashes( $_GET ) );
?>

<input type="hidden" name="_authenticate" value="1" />

<table class="width50 center">
<tr>
	<td class="form-title"><?php echo lang_get( 'reauthenticate_title' ); ?></td>
</tr>

<tr class="row-1">
	<td class="category"><?php echo lang_get( 'username' ); ?></td>
	<td><input type="text" disabled="disabled" size="32" maxlength="32" value="<?php echo $p_username; ?>" /></td>
</tr>

<tr class="row-2">
	<td class="category"><?php echo lang_get( 'password' ); ?></td>
	<td><input type="password" name="password" size="16" maxlength="32" /></td>
</tr>

<tr>
	<td class="center" colspan="2"><input type="submit" class="button" value="<?php echo lang_get( 'login_button' ); ?>" /></td>
</tr>
</table>

</form>
</div>

		<?php
		html_page_bottom1();

		exit;
	}

	#===================================
	# Data Access
	#===================================

	#########################################
	# is cookie valid?

	function auth_is_cookie_valid( $p_cookie_string ) {
		global $g_cache_current_user_id;
		
		# fail if DB isn't accessible
		if ( !db_is_connected() ) {
			return false;
		}

		# fail if cookie is blank
		if ( '' === $p_cookie_string ) {
			return false;
		}

		# succeeed if user has already been authenticated
		if ( null !== $g_cache_current_user_id ) {
			return true;
		}
		
		# look up cookie in the database to see if it is valid
		$t_user_table = config_get( 'mantis_user_table' );

		$c_cookie_string = db_prepare_string( $p_cookie_string );

		$query = "SELECT id
				  FROM $t_user_table
				  WHERE cookie_string='$c_cookie_string'";
		$result = db_query( $query );

		# return true if a matching cookie was found
 		return ( 1 == db_num_rows( $result ) );
	}

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_cache_current_user_id = null;

	function auth_get_current_user_id() {
		global $g_cache_current_user_id;

		if ( null !== $g_cache_current_user_id ) {
			return $g_cache_current_user_id;
		}

		$t_user_table = config_get( 'mantis_user_table' );

		$t_cookie_string = auth_get_current_user_cookie();

		# @@@ error with an error saying they aren't logged in?
		#     Or redirect to the login page maybe?

		$c_cookie_string = db_prepare_string( $t_cookie_string );

		$query = "SELECT id
				  FROM $t_user_table
				  WHERE cookie_string='$c_cookie_string'";
		$result = db_query( $query );

		# The cookie was invalid. Clear the cookie (to allow people to log in again)
		# and give them an Access Denied message.
		if ( db_num_rows( $result ) < 1 ) {
			auth_clear_cookies();
		    access_denied(); # never returns
			return false;
		}

		$t_user_id = (int)db_result( $result );
		$g_cache_current_user_id = $t_user_id;

		return $t_user_id;
	}

	#===================================
	# HTTP Auth
	#===================================

	function auth_http_prompt() {
		header( "HTTP/1.0 401 Authorization Required" );
		header( "WWW-Authenticate: Basic realm=\"" . lang_get( 'http_auth_realm' ) . "\"" );
		header( 'status: 401 Unauthorized' );

		echo '<center>';
		echo '<p>'.error_string(ERROR_ACCESS_DENIED).'</p>';
		print_bracket_link( 'main_page.php', lang_get( 'proceed' ) );
		echo '</center>';

		exit;
	}

	function auth_http_set_logout_pending( $p_pending ) {
		$t_cookie_name = config_get( 'logout_cookie' );

		if ( $p_pending ) {
			gpc_set_cookie( $t_cookie_name, "1", false );
		} else {
			$t_cookie_path = config_get( 'cookie_path' );
			gpc_clear_cookie( $t_cookie_name, $t_cookie_path );
		}
	}

	function auth_http_is_logout_pending() {
		$t_cookie_name = config_get( 'logout_cookie' );
		$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

		return( $t_cookie > '' );
	}
?>
