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
 * @package CoreAPI
 * @subpackage EmailAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires current_user_api
 */
require_once( 'current_user_api.php' );
/**
 * requires bug_api
 */
require_once( 'bug_api.php' );
/**
 * requires custom_field_api
 */
require_once( 'custom_field_api.php' );
/**
 * requires string_api
 */
require_once( 'string_api.php' );
/**
 * requires history_api
 */
require_once( 'history_api.php' );
/**
 * requires email_queue_api
 */
require_once( 'email_queue_api.php' );
/**
 * requires relationship_api
 */
require_once( 'relationship_api.php' );

/**
 * reusable object of class SMTP
 */
 $g_phpMailer = null;

/**
 * 
 * Use a simple perl regex for valid email addresses.  This is not a complete regex,
 * as it does not cover quoted addresses or domain literals, but it is simple and
 * covers the vast majority of all email addresses without being overly complex.
 * @return string
 */
function email_regex_simple() {
	return "/([a-z0-9!#*+\/=?^_{|}~-]+(?:\.[a-z0-9!#*+\/=?^_{|}~-]+)*)" . 				# recipient
			"\@((?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)/i";	# @domain
}

/**
 * check to see that the format is valid and that the mx record exists
 * @param string $p_email
 * @return bool
 */
function email_is_valid( $p_email ) {
	# if we don't validate then just accept
	if( OFF == config_get( 'validate_email' ) ) {
		return true;
	}

	if ( ON == config_get( 'use_ldap_email' ) ) {
		return true;
	}

	if( is_blank( $p_email ) && ON == config_get( 'allow_blank_email' ) ) {
		return true;
	}

	# Use a regular expression to check to see if the email is in valid format
	#  x-xx.xxx@yyy.zzz.abc etc.
	if( preg_match( email_regex_simple(), $p_email, $t_check ) ) {
		$t_local = $t_check[1];
		$t_domain = $t_check[2];

		# see if we're limited to one domain
		$t_limit_email_domain = config_get( 'limit_email_domain' );
		if( $t_limit_email_domain !== OFF  ) {
			if( 0 != strcasecmp( $t_limit_email_domain, $t_domain ) ) {
				return false;
			}
		}

		if( ON == config_get( 'check_mx_record' ) ) {
			$temp = '';

			# Check for valid mx records
			if( getmxrr( $t_domain, $temp ) ) {
				return true;
			} else {
				$host = $t_domain . '.';

				# for no mx record... try dns check
				if( checkdnsrr( $host, 'ANY' ) ) {
					return true;
				}
			}
		} else {
			# Email format was valid but did't check for valid mx records
			return true;
		}
	}

	# Everything failed.  The email is invalid
	return false;
}

/**
 * Check if the email address is valid
 * trigger an ERROR if it isn't
 * @param string $p_email
 * @return null
 */
function email_ensure_valid( $p_email ) {
	if( !email_is_valid( $p_email ) ) {
		trigger_error( ERROR_EMAIL_INVALID, ERROR );
	}
}

/**
 * Check if the email address is disposable
 * @param string $p_email
 * @return bool
 */
function email_is_disposable( $p_email ) {
	if( !class_exists( 'DisposableEmailChecker' ) ) {
		require_once( BASE_PATH . DIRECTORY_SEPARATOR  . 'library' . DIRECTORY_SEPARATOR . 'disposable' . DIRECTORY_SEPARATOR . 'disposable.php' );
	}

	return DisposableEmailChecker::is_disposable_email( $p_email );
}

/**
 * Check if the email address is disposable
 * trigger an ERROR if it isn't
 * @param string $p_email
 * @return null
 */
function email_ensure_not_disposable( $p_email ) {
	if( email_is_disposable( $p_email ) ) {
		trigger_error( ERROR_EMAIL_DISPOSABLE, ERROR );
	}
}

/**
 * email_notify_flag
 * Get the value associated with the specific action and flag.
 * For example, you can get the value associated with notifying "admin"
 * on action "new", i.e. notify administrators on new bugs which can be
 * ON or OFF.
 * @param string $action
 * @param string $flag
 * @return int
 */
function email_notify_flag( $action, $flag ) {
	$t_notify_flags = config_get( 'notify_flags' );
	$t_default_notify_flags = config_get( 'default_notify_flags' );
	if( isset( $t_notify_flags[$action][$flag] ) ) {
		return $t_notify_flags[$action][$flag];
	}
	else if( isset( $t_default_notify_flags[$flag] ) ) {
		return $t_default_notify_flags[$flag];
	}

	return OFF;
}

/**
 * @todo yarick123: email_collect_recipients(...) will be completely rewritten to provide additional information such as language, user access,..
 * @todo yarick123:sort recipients list by language to reduce switches between different languages
 * @param int $p_bug_id
 * @param string $p_notify_type
 * @param array $p_extra_user_ids_to_email
 * @return array
 */
function email_collect_recipients( $p_bug_id, $p_notify_type, $p_extra_user_ids_to_email = array() ) {
	$c_bug_id = db_prepare_int( $p_bug_id );

	$t_recipients = array();

	# add explicitly specified users
	if ( ON == email_notify_flag( $p_notify_type, 'explicit' ) ) {
		foreach ( $p_extra_user_ids_to_email as $t_user_id ) {
			$t_recipients[$t_user_id] = true;
			log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, add explicitly specified user = @U%d', $p_bug_id, $t_user_id ) );
		}
	}

	# add Reporter
	if( ON == email_notify_flag( $p_notify_type, 'reporter' ) ) {
		$t_reporter_id = bug_get_field( $p_bug_id, 'reporter_id' );
		$t_recipients[$t_reporter_id] = true;
		log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, add Reporter = @U%d', $p_bug_id, $t_reporter_id ) );
	}

	# add Handler
	if( ON == email_notify_flag( $p_notify_type, 'handler' ) ) {
		$t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );

		if( $t_handler_id > 0 ) {
			$t_recipients[$t_handler_id] = true;
			log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, add Handler = @U%d', $p_bug_id, $t_handler_id ) );
		}
	}

	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

	# add users monitoring the bug
	$t_bug_monitor_table = db_get_table( 'mantis_bug_monitor_table' );
	if( ON == email_notify_flag( $p_notify_type, 'monitor' ) ) {
		$query = "SELECT DISTINCT user_id
					  FROM $t_bug_monitor_table
					  WHERE bug_id=" . db_param();
		$result = db_query_bound( $query, Array( $c_bug_id ) );

		$count = db_num_rows( $result );
		for( $i = 0;$i < $count;$i++ ) {
			$t_user_id = db_result( $result, $i );
			$t_recipients[$t_user_id] = true;
			log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, add Monitor = @U%d', $p_bug_id, $t_user_id ) );
		}
	}

	# add users who contributed bugnotes
	$t_bugnote_id = bugnote_get_latest_id( $p_bug_id );
	$t_bugnote_view = bugnote_get_field( $t_bugnote_id, 'view_state' );
	$t_bugnote_date = bugnote_get_field( $t_bugnote_id, 'last_modified' );
	$t_bug = bug_get( $p_bug_id );
	$t_bug_date = $t_bug->last_updated;

	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );
	if( ON == email_notify_flag( $p_notify_type, 'bugnotes' ) ) {
		$query = "SELECT DISTINCT reporter_id
					  FROM $t_bugnote_table
					  WHERE bug_id = " . db_param();
		$result = db_query_bound( $query, Array( $c_bug_id ) );

		$count = db_num_rows( $result );
		for( $i = 0;$i < $count;$i++ ) {
			$t_user_id = db_result( $result, $i );
			$t_recipients[$t_user_id] = true;
			log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, add Note Author = @U%d', $p_bug_id, $t_user_id ) );
		}
	}

	# add project users who meet the thresholds
	$t_bug_is_private = bug_get_field( $p_bug_id, 'view_state' ) == VS_PRIVATE;
	$t_threshold_min = email_notify_flag( $p_notify_type, 'threshold_min' );
	$t_threshold_max = email_notify_flag( $p_notify_type, 'threshold_max' );
	$t_threshold_users = project_get_all_user_rows( $t_project_id, $t_threshold_min );
	foreach( $t_threshold_users as $t_user ) {
		if( $t_user['access_level'] <= $t_threshold_max ) {
			if( !$t_bug_is_private || access_compare_level( $t_user['access_level'], config_get( 'private_bug_threshold' ) ) ) {
				$t_recipients[$t_user['id']] = true;
				log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, add Project User = @U%d', $p_bug_id, $t_user['id'] ) );
			}
		}
	}

	# add users as specified by plugins
	$t_recipients_include_data = event_signal( 'EVENT_NOTIFY_USER_INCLUDE', array( $p_bug_id, $p_notify_type ) );
	foreach( $t_recipients_include_data as $t_plugin => $t_recipients_include_data2 ) {
		foreach( $t_recipients_include_data2 as $t_callback => $t_recipients_included ) {
			# only handle if we get an array from the callback
			if ( is_array( $t_recipients_included ) ) {
				foreach( $t_recipients_included as $t_user_id ) {
					$t_recipients[ $t_user_id ] = true;
					log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, %s plugin added user @U%d', $p_bug_id, $t_plugin, $t_user_id ) );
				}
			}
		}
	}

	# FIXME: the value of $p_notify_type could at this stage be either a status
	# or a built-in actions such as 'owner and 'sponsor'. We have absolutely no
	# idea whether 'new' is indicating a new bug has been filed, or if the
	# status of an existing bug has been changed to 'new'. Therefore it is best
	# to just assume built-in actions have precedence over status changes.
	switch( $p_notify_type) {
		case 'new':
		case 'feedback': # This isn't really a built-in action (delete me!)
		case 'reopened':
		case 'resolved':
		case 'closed':
		case 'bugnote':
			$t_pref_field = 'email_on_' . $p_notify_type;
			break;
		case 'owner':
			# The email_on_assigned notification type is now effectively
			# email_on_change_of_handler.
			$t_pref_field = 'email_on_assigned';
			break;
		case 'deleted':
		case 'updated':
		case 'sponsor':
		case 'relation':
		case 'monitor':
		case 'priority': # This is never used, but exists in the database!
			# FIXME: these notification actions are not actually implemented
			# in the database and therefore aren't adjustable on a per-user
			# basis! The exception is 'monitor' that makes no sense being a
			# customisable per-user preference.
			$t_pref_field = false;
			break;
		default:
			# Anything not built-in is probably going to be a status
			$t_pref_field = 'email_on_status';
			break;
	}

	# @@@ we could optimize by modifiying user_cache() to take an array
	#  of user ids so we could pull them all in.  We'll see if it's necessary
	$t_final_recipients = array();

	$t_user_ids = array_keys( $t_recipients );
	user_cache_array_rows( $t_user_ids );
	user_pref_cache_array_rows( $t_user_ids );
	user_pref_cache_array_rows( $t_user_ids, $t_bug->project_id );

	# Check whether users should receive the emails
	# and put email address to $t_recipients[user_id]
	foreach( $t_recipients as $t_id => $t_ignore ) {
		# Possibly eliminate the current user
		if(( auth_get_current_user_id() == $t_id ) && ( OFF == config_get( 'email_receive_own' ) ) ) {
			log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, drop @U%d (own)', $p_bug_id, $t_id ) );
			continue;
		}

		# Eliminate users who don't exist anymore or who are disabled
		if( !user_exists( $t_id ) || !user_is_enabled( $t_id ) ) {
			log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, drop @U%d (disabled)', $p_bug_id, $t_id ) );
			continue;
		}

		# Exclude users who have this notification type turned off
		if( $t_pref_field ) {
			$t_notify = user_pref_get_pref( $t_id, $t_pref_field );
			if( OFF == $t_notify ) {
				log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, drop @U%d (pref %s off)', $p_bug_id, $t_id, $t_pref_field ) );
				continue;
			} else {
				# Users can define the severity of an issue before they are emailed for
				# each type of notification
				$t_min_sev_pref_field = $t_pref_field . '_min_severity';
				$t_min_sev_notify = user_pref_get_pref( $t_id, $t_min_sev_pref_field );
				$t_bug_severity = bug_get_field( $p_bug_id, 'severity' );

				if( $t_bug_severity < $t_min_sev_notify ) {
					log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, drop @U%d (pref threshold)', $p_bug_id, $t_id ) );
					continue;
				}
			}
		}

		# check that user can see bugnotes if the last update included a bugnote
		if( $t_bug_date == $t_bugnote_date ) {
			if( !access_has_bugnote_level( VIEWER, $t_bugnote_id, $t_id ) ) {
				log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, drop @U%d (access level)', $p_bug_id, $t_id ) );
				continue;
			}
		}

		# check to exclude users as specified by plugins
		$t_recipient_exclude_data = event_signal( 'EVENT_NOTIFY_USER_EXCLUDE', array( $p_bug_id, $p_notify_type, $t_id ) );
		$t_exclude = false;
		foreach( $t_recipient_exclude_data as $t_plugin => $t_recipient_exclude_data2 ) {
			foreach( $t_recipient_exclude_data2 as $t_callback => $t_recipient_excluded ) {
				# exclude if any plugin returns true (excludes the user)
				if ( $t_recipient_excluded ) {
					$t_exclude = true;
					log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, %s plugin dropped user @U%d', $p_bug_id, $t_plugin, $t_id ) );
				}
			}
		}

		# user was excluded by a plugin
		if ( $t_exclude ) {
			continue;
		}

		# Finally, let's get their emails, if they've set one
		$t_email = user_get_email( $t_id );
		if( is_blank( $t_email ) ) {
			log_event( LOG_EMAIL_RECIPIENT, sprintf( 'Issue = #%d, drop @U%d (no email)', $p_bug_id, $t_id ) );
		} else {
			# @@@ we could check the emails for validity again but I think
			#   it would be too slow
			$t_final_recipients[$t_id] = $t_email;
		}
	}

	return $t_final_recipients;
}

/**
 * Send password to user
 * @param int $p_user_id
 * @param string $p_password
 * @param string $p_confirm_hash
 * @param string $p_admin_name 
 * @return null
 */
function email_signup( $p_user_id, $p_password, $p_confirm_hash, $p_admin_name = '' ) {
	if(( OFF == config_get( 'send_reset_password' ) ) || ( OFF == config_get( 'enable_email_notification' ) ) ) {
		return;
	}

	#	@@@ thraxisp - removed to address #6084 - user won't have any settings yet,
	#  use same language as display for the email
	#  lang_push( user_pref_get_language( $p_user_id ) );
	# retrieve the username and email
	$t_username = user_get_field( $p_user_id, 'username' );
	$t_email = user_get_email( $p_user_id );

	# Build Welcome Message
	$t_subject = '[' . config_get( 'window_title' ) . '] ' . lang_get( 'new_account_subject' );

	//if( $p_admin_created && $p_admin_name) {
	if( $p_admin_name ) {
		$intro_text = sprintf( lang_get( 'new_account_greeting_admincreated' ), $p_admin_name, $t_username );
	} else {
		$intro_text = sprintf( lang_get( 'new_account_greeting' ), $t_username );
	}

	$t_message = $intro_text . "\n\n" . string_get_confirm_hash_url( $p_user_id, $p_confirm_hash ) . "\n\n" . lang_get( 'new_account_message' ) . "\n\n" . lang_get( 'new_account_do_not_reply' );

	# Send signup email regardless of mail notification pref
	# or else users won't be able to sign up
	if( !is_blank( $t_email ) ) {
		email_store( $t_email, $t_subject, $t_message );
		log_event( LOG_EMAIL, sprintf( 'Signup Email = %s, Hash = %s, User = @U%d', $t_email, $p_confirm_hash, $p_user_id ) );

		if( OFF == config_get( 'email_send_using_cronjob' ) ) {
			email_send_all();
		}
	}

	#		lang_pop(); # see above
}

/**
 * Send confirm_hash url to user forgets the password
 * @param int $p_user_id
 * @param string $p_confirm_hash
 * @return null
 */
function email_send_confirm_hash_url( $p_user_id, $p_confirm_hash ) {
	if ( OFF == config_get( 'send_reset_password' ) ||
		OFF == config_get( 'enable_email_notification' ) ) {
		return;
	}

	lang_push( user_pref_get_language( $p_user_id ) );

	# retrieve the username and email
	$t_username = user_get_field( $p_user_id, 'username' );
	$t_email = user_get_email( $p_user_id );

	$t_subject = '[' . config_get( 'window_title' ) . '] ' . lang_get( 'lost_password_subject' );

	$t_message = lang_get( 'reset_request_msg' ) . " \n\n" . string_get_confirm_hash_url( $p_user_id, $p_confirm_hash ) . " \n\n" . lang_get( 'new_account_username' ) . ' ' . $t_username . " \n" . lang_get( 'new_account_IP' ) . ' ' . $_SERVER["REMOTE_ADDR"] . " \n\n" . lang_get( 'new_account_do_not_reply' );

	# Send password reset regardless of mail notification prefs
	# or else users won't be able to receive their reset pws
	if( !is_blank( $t_email ) ) {
		email_store( $t_email, $t_subject, $t_message );
		log_event( LOG_EMAIL, sprintf( 'Password reset for email = %s', $t_email ) );

		if( OFF == config_get( 'email_send_using_cronjob' ) ) {
			email_send_all();
		}
	}

	lang_pop();
}

/**
 * notify the selected group a new user has signup
 * @param string $p_username
 * @param string $p_email
 * @return null
 */
function email_notify_new_account( $p_username, $p_email ) {
	global $g_path;

	$t_threshold_min = config_get( 'notify_new_user_created_threshold_min' );
	$t_threshold_users = project_get_all_user_rows( ALL_PROJECTS, $t_threshold_min );

	foreach( $t_threshold_users as $t_user ) {
		lang_push( user_pref_get_language( $t_user['id'] ) );

		$t_recipient_email = user_get_email( $t_user['id'] );
		$t_subject = '[' . config_get( 'window_title' ) . '] ' . lang_get( 'new_account_subject' );

		$t_message = lang_get( 'new_account_signup_msg' ) . "\n\n" . lang_get( 'new_account_username' ) . ' ' . $p_username . "\n" . lang_get( 'new_account_email' ) . ' ' . $p_email . "\n" . lang_get( 'new_account_IP' ) . ' ' . $_SERVER["REMOTE_ADDR"] . "\n" . $g_path . "\n\n" . lang_get( 'new_account_do_not_reply' );

		if( !is_blank( $t_recipient_email ) ) {
			email_store( $t_recipient_email, $t_subject, $t_message );
			log_event( LOG_EMAIL, sprintf( 'New Account Notify for email = \'%s\'', $t_recipient_email ) );

			if( OFF == config_get( 'email_send_using_cronjob' ) ) {
				email_send_all();
			}
		}

		lang_pop();
	}
}


/**
 * send a generic email
 * $p_notify_type: use check who she get notified of such event.
 * $p_message_id: message id to be translated and included at the top of the email message.
 * Return false if it were problems sending email * @param string 
 * @param int $p_bug_id
 * @param string $p_notify_type
 * @param int $p_message_id
 * @param array $p_header_optional_params = null
 * @param array $p_extra_user_ids_to_email
 * @return bool
 */
function email_generic( $p_bug_id, $p_notify_type, $p_message_id = null, $p_header_optional_params = null, $p_extra_user_ids_to_email = array() ) {
	$t_ok = true;

	if( ON === config_get( 'enable_email_notification' ) ) {
		ignore_user_abort( true );

		bugnote_get_all_bugnotes( $p_bug_id );

		# @todo yarick123: email_collect_recipients(...) will be completely rewritten to provide additional information such as language, user access,..
		# @todo yarick123:sort recipients list by language to reduce switches between different languages
		$t_recipients = email_collect_recipients( $p_bug_id, $p_notify_type, $p_extra_user_ids_to_email );

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		if( is_array( $t_recipients ) ) {
			# send email to every recipient
			foreach( $t_recipients as $t_user_id => $t_user_email ) {
				log_event( LOG_EMAIL, sprintf( "Issue = #%d, Type = %s, Msg = '%s', User = @U%d, Email = '%s'.", $p_bug_id, $p_notify_type, $p_message_id, $t_user_id, $t_user_email ) );

				# load (push) user language here as build_visible_bug_data assumes current language
				lang_push( user_pref_get_language( $t_user_id, $t_project_id ) );

				$t_visible_bug_data = email_build_visible_bug_data( $t_user_id, $p_bug_id, $p_message_id );
				$t_ok = email_bug_info_to_one_user( $t_visible_bug_data, $p_message_id, $t_project_id, $t_user_id, $p_header_optional_params ) && $t_ok;

				lang_pop();
			}
		}

		# Only trigger the draining of the email queue if cronjob is disabled and email notifications are enabled.
		if( OFF == config_get( 'email_send_using_cronjob' ) ) {
			email_send_all();
		}
	}

	return $t_ok;
}

/**
 * Send notices that a user is now monitoring the bug.  Typically this will only be sent when the added
 * user is not the logged in user.  This is assuming that receive own notifications is OFF (default).
 * @param int $p_bug_id
 * @param int $p_user_id
 * @return null
 */
function email_monitor_added( $p_bug_id, $p_user_id ) {
	log_event( LOG_EMAIL, sprintf( 'Issue #%d monitored by user @U%d', $p_bug_id, $p_user_id ) );

	$t_opt = array();
	$t_opt[] = bug_format_id( $p_bug_id );
	$t_opt[] = user_get_name( $p_user_id );

	email_generic( $p_bug_id, 'monitor', 'email_notification_title_for_action_monitor', $t_opt, array( $p_user_id ) );
}

/**
 * send notices when a relationship is ADDED
 * @param int $p_bug_id
 * @param int $p_related_bug_id
 * @param int $p_rel_type
 * @return null
 */
function email_relationship_added( $p_bug_id, $p_related_bug_id, $p_rel_type ) {
	log_event( LOG_EMAIL, sprintf( 'Relationship added: Issue #%d, related issue %d, relationship type %s.', $p_bug_id, $p_related_bug_id, $p_rel_type ) );

	$t_opt = array();
	$t_opt[] = bug_format_id( $p_related_bug_id );
	global $g_relationships;
	if( !isset( $g_relationships[$p_rel_type] ) ) {
		trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
	}
	email_generic( $p_bug_id, 'relation', $g_relationships[$p_rel_type]['#notify_added'], $t_opt );
}

/**
 * send notices when a relationship is DELETED
 * @param int $p_bug_id
 * @param int $p_related_bug_id
 * @param int $p_rel_type
 * @return null
 */
function email_relationship_deleted( $p_bug_id, $p_related_bug_id, $p_rel_type ) {
	log_event( LOG_EMAIL, sprintf( 'Relationship deleted: Issue #%d, related issue %d, relationship type %s.', $p_bug_id, $p_related_bug_id, $p_rel_type ) );

	$t_opt = array();
	$t_opt[] = bug_format_id( $p_related_bug_id );
	global $g_relationships;
	if( !isset( $g_relationships[$p_rel_type] ) ) {
		trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
	}
	email_generic( $p_bug_id, 'relation', $g_relationships[$p_rel_type]['#notify_deleted'], $t_opt );
}

/**
 * send notices to all the handlers of the parent bugs when a child bug is RESOLVED
 * @param int $p_bug_id
 * @return null
 */
function email_relationship_child_resolved( $p_bug_id ) {
	email_relationship_child_resolved_closed( $p_bug_id, 'email_notification_title_for_action_relationship_child_resolved' );
}

/**
 * send notices to all the handlers of the parent bugs when a child bug is CLOSED
 * @param int $p_bug_id
 * @return null
 */
function email_relationship_child_closed( $p_bug_id ) {
	email_relationship_child_resolved_closed( $p_bug_id, 'email_notification_title_for_action_relationship_child_closed' );
}

/**
 * send notices to all the handlers of the parent bugs still open when a child bug is resolved/closed
 * 
 * @param int $p_bug_id
 * @param int $p_message_id 
 * @return null
 */
function email_relationship_child_resolved_closed( $p_bug_id, $p_message_id ) {
	# retrieve all the relationships in which the bug is the destination bug
	$t_relationship = relationship_get_all_dest( $p_bug_id );
	$t_relationship_count = count( $t_relationship );
	if( $t_relationship_count == 0 ) {
		# no parent bug found
		return;
	}

	for( $i = 0;$i < $t_relationship_count;$i++ ) {
		if( $t_relationship[$i]->type == BUG_DEPENDANT ) {
			$t_src_bug_id = $t_relationship[$i]->src_bug_id;
			$t_status = bug_get_field( $t_src_bug_id, 'status' );
			if( $t_status < config_get( 'bug_resolved_status_threshold' ) ) {

				# sent the notification just for parent bugs not resolved/closed
				$t_opt = array();
				$t_opt[] = bug_format_id( $p_bug_id );
				email_generic( $t_src_bug_id, 'handler', $p_message_id, $t_opt );
			}
		}
	}
}

/**
 * send notices when a bug is sponsored
 * @param int $p_bug_id 
 * @return null
 */
function email_sponsorship_added( $p_bug_id ) {
	email_generic( $p_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_added' );
}

/**
 * send notices when a sponsorship is modified
 * @param int $p_bug_id 
 * @return null
 */
function email_sponsorship_updated( $p_bug_id ) {
	email_generic( $p_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_updated' );
}

/**
 * send notices when a sponsorship is deleted
 * @param int $p_bug_id 
 * @return null
 */
function email_sponsorship_deleted( $p_bug_id ) {
	email_generic( $p_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_deleted' );
}

/**
 * send notices when a new bug is added
 * @param int $p_bug_id 
 * @return null
 */
function email_new_bug( $p_bug_id ) {
	email_generic( $p_bug_id, 'new', 'email_notification_title_for_action_bug_submitted' );
}

/**
 * send notices when a new bugnote
 * @param int $p_bug_id 
 * @return null
 */
function email_bugnote_add( $p_bug_id ) {
	email_generic( $p_bug_id, 'bugnote', 'email_notification_title_for_action_bugnote_submitted' );
}

/**
 * send notices when a bug is RESOLVED
 * @param int $p_bug_id 
 * @return null
 */
function email_resolved( $p_bug_id ) {
	email_generic( $p_bug_id, 'resolved', 'email_notification_title_for_status_bug_resolved' );
}

/**
 * send notices when a bug is CLOSED
 * @param int $p_bug_id 
 * @return null
 */
 function email_close( $p_bug_id ) {
	email_generic( $p_bug_id, 'closed', 'email_notification_title_for_status_bug_closed' );
}

/**
 * send notices when a bug is REOPENED
 * @param int $p_bug_id 
 * @return null
 */
function email_reopen( $p_bug_id ) {
	email_generic( $p_bug_id, 'reopened', 'email_notification_title_for_action_bug_reopened' );
}

/**
 * send notices when a bug is ASSIGNED
 * @param int $p_bug_id 
 * @return null
 */
function email_assign( $p_bug_id ) {
	email_generic( $p_bug_id, 'owner', 'email_notification_title_for_action_bug_assigned' );
}

/**
 * send notices when a bug is DELETED
 * @param int $p_bug_id
 * @return null
 */
function email_bug_deleted( $p_bug_id ) {
	email_generic( $p_bug_id, 'deleted', 'email_notification_title_for_action_bug_deleted' );
}

/**
 * Store email in queue for sending
 * 
 * @param string $p_recipient
 * @param string $p_subject
 * @param string $p_message
 * @param array $p_headers
 * @return int
 */
function email_store( $p_recipient, $p_subject, $p_message, $p_headers = null ) {
	$t_recipient = trim( $p_recipient );
	$t_subject = string_email( trim( $p_subject ) );
	$t_message = string_email_links( trim( $p_message ) );

	# short-circuit if no recipient is defined, or email disabled
	# note that this may cause signup messages not to be sent

	if( is_blank( $p_recipient ) || ( OFF == config_get( 'enable_email_notification' ) ) ) {
		return;
	}

	$t_email_data = new EmailData;

	$t_email_data->email = $t_recipient;
	$t_email_data->subject = $t_subject;
	$t_email_data->body = $t_message;
	$t_email_data->metadata = array();
	$t_email_data->metadata['headers'] = $p_headers === null ? array() : $p_headers;
	$t_email_data->metadata['priority'] = config_get( 'mail_priority' );

	# Urgent = 1, Not Urgent = 5, Disable = 0
	$t_email_data->metadata['charset'] = 'utf-8';

	$t_hostname = '';
	$t_server = isset( $_SERVER ) ? $_SERVER : $HTTP_SERVER_VARS;
	if( isset( $t_server['SERVER_NAME'] ) ) {
		$t_hostname = $t_server['SERVER_NAME'];
	} else {
		$t_address = explode( '@', config_get( 'from_email' ) );
		if( isset( $t_address[1] ) ) {
			$t_hostname = $t_address[1];
		}
	}
	$t_email_data->metadata['hostname'] = $t_hostname;

	$t_email_id = email_queue_add( $t_email_data );

	return $t_email_id;
}

/**
 * This function sends all the emails that are stored in the queue.  If a failure occurs, then the
 * function exists.  This function will be called after storing emails in case of synchronous
 * emails, or will be called from a cronjob in case of asynchronous emails.
 * @todo In case of synchronous email sending, we may get a race condition where two requests send the same email.
 * @param bool $p_delete_on_failure indicates whether to remove email from queue on failure (default false)
 * @return null
 */
function email_send_all($p_delete_on_failure = false) {
	$t_ids = email_queue_get_ids();

	$t_emails_recipients_failed = array();
	$t_start = microtime(true);
	foreach( $t_ids as $t_id ) {
		$t_email_data = email_queue_get( $t_id );

		# check if email was not found.  This can happen if another request picks up the email first and sends it.
		if( $t_email_data === false ) {
			continue;
		}

		# if unable to place the email in the email server queue, then the connection to the server is down,
		# and hence no point to continue trying with the rest of the emails.
		if( !email_send( $t_email_data ) ) {
			if ($p_delete_on_failure) {
				email_queue_delete( $t_email_data->email_id );
			}
			if( microtime(true) - $t_start > 5 ) {
				break;
			} else {
				continue;
			}
		}
	}
}

/**
 * This function sends an email message based on the supplied email data.
 * 
 * @param EmailData $p_email_data
 * @return bool
 */
function email_send( $p_email_data ) {
	global $g_phpMailer;

	$t_email_data = $p_email_data;

	$t_recipient = trim( $t_email_data->email );
	$t_subject = string_email( trim( $t_email_data->subject ) );
	$t_message = string_email_links( trim( $t_email_data->body ) );

	$t_debug_email = config_get( 'debug_email' );
	$t_mailer_method = config_get( 'phpMailer_method' );

	if( is_null( $g_phpMailer ) ) {
		if ( $t_mailer_method == PHPMAILER_METHOD_SMTP )
			register_shutdown_function( 'email_smtp_close' );
		if( !class_exists( 'PHPMailer' ) ) {
			require_once( BASE_PATH . DIRECTORY_SEPARATOR  . 'library' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'class.phpmailer.php' );
		}
		$mail = new PHPMailer(true);
	} else {
		$mail = $g_phpMailer;
	}

	if( isset( $t_email_data->metadata['hostname'] ) ) {
		$mail->Hostname = $t_email_data->metadata['hostname'];
	}

	# @@@ should this be the current language (for the recipient) or the default one (for the user running the command) (thraxisp)
	$t_lang = config_get( 'default_language' );
	if( 'auto' == $t_lang ) {
		$t_lang = config_get( 'fallback_language' );
	}
	$mail->SetLanguage( lang_get( 'phpmailer_language', $t_lang ) );

	# Select the method to send mail
	switch( config_get( 'phpMailer_method' ) ) {
		case PHPMAILER_METHOD_MAIL:
			$mail->IsMail();
			break;

		case PHPMAILER_METHOD_SENDMAIL:
			$mail->IsSendmail();
			break;

		case PHPMAILER_METHOD_SMTP:
			$mail->IsSMTP();

			// SMTP collection is always kept alive
			$mail->SMTPKeepAlive = true;

			if ( !is_blank( config_get( 'smtp_username' ) ) ) {
				# Use SMTP Authentication
				$mail->SMTPAuth = true;
				$mail->Username = config_get( 'smtp_username' );
				$mail->Password = config_get( 'smtp_password' );
			}

			if ( !is_blank( config_get( 'smtp_connection_mode' ) ) ) {
				$mail->SMTPSecure = config_get( 'smtp_connection_mode' );
			}

			$mail->Port = config_get( 'smtp_port' );

			break;
	}

	$mail->IsHTML( false );              # set email format to plain text
	$mail->WordWrap = 80;              # set word wrap to 50 characters
	$mail->Priority = $t_email_data->metadata['priority'];  # Urgent = 1, Not Urgent = 5, Disable = 0
	$mail->CharSet = $t_email_data->metadata['charset'];
	$mail->Host = config_get( 'smtp_host' );
	$mail->From = config_get( 'from_email' );
	$mail->Sender = config_get( 'return_path_email' );
	$mail->FromName = config_get( 'from_name' );
	$mail->AddCustomHeader('Auto-Submitted:auto-generated');

	if( OFF !== $t_debug_email ) {
		$t_message = 'To: ' . $t_recipient . "\n\n" . $t_message;
		try {
			$mail->AddAddress( $t_debug_email, '' );
		} catch ( phpmailerException $e ) {
			$t_success = false;
			$mail->ClearAllRecipients();
			$mail->ClearAttachments();
			$mail->ClearReplyTos();
			$mail->ClearCustomHeaders();
			return $t_success;
		}
	} else {
		try {
			$mail->AddAddress( $t_recipient, '' );
		} catch ( phpmailerException $e ) {
			$t_success = false;
			$mail->ClearAllRecipients();
			$mail->ClearAttachments();
			$mail->ClearReplyTos();
			$mail->ClearCustomHeaders();
			return $t_success;
		}
	}

	$mail->Subject = $t_subject;
	$mail->Body = make_lf_crlf( "\n" . $t_message );

	if( isset( $t_email_data->metadata['headers'] ) && is_array( $t_email_data->metadata['headers'] ) ) {
		foreach( $t_email_data->metadata['headers'] as $t_key => $t_value ) {
			switch( $t_key ) {
				case 'Message-ID':
					/* Note: hostname can never be blank here as we set metadata['hostname']
					   in email_store() where mail gets queued. */
						if ( !strchr( $t_value, '@' ) && !is_blank( $mail->Hostname ) ) {
							$t_value = $t_value . '@' . $mail->Hostname;
						}
					$mail->set( 'MessageID', "<$t_value>" );
					break;
				case 'In-Reply-To':
					$mail->AddCustomHeader( "$t_key: <{$t_value}@{$mail->Hostname}>" );
					break;
				default:
					$mail->AddCustomHeader( "$t_key: $t_value" );
					break;
			}
		}
	}

	try
	{
		if ( !$mail->Send() ) {
			$t_success = false;
		} else {
			$t_success = true;

			if ( $t_email_data->email_id > 0 ) {
				email_queue_delete( $t_email_data->email_id );
			}
		}
	}
	catch ( phpmailerException $e )
	{
		$t_success = false;
	}

	$mail->ClearAllRecipients();
	$mail->ClearAttachments();
	$mail->ClearReplyTos();
	$mail->ClearCustomHeaders();

	return $t_success;
}

/**
 * closes opened kept alive SMTP connection (if it was opened)
 * 
 * @param string 
 * @return null
 */
function email_smtp_close() {
	global $g_phpMailer;

	if( !is_null( $g_phpMailer ) ) {
		if( $g_phpMailer->smtp->Connected() ) {
			$g_phpMailer->smtp->Quit();
			$g_phpMailer->smtp->Close();
		}
		$g_phpMailer = null;
	}
}

/**
 * formats the subject correctly
 * we include the project name, bug id, and summary.
 * 
 * @param int $p_bug_id
 * @return null
 */
function email_build_subject( $p_bug_id ) {
	# grab the project name
	$p_project_name = project_get_field( bug_get_field( $p_bug_id, 'project_id' ), 'name' );

	# grab the subject (summary)
	$p_subject = bug_get_field( $p_bug_id, 'summary' );

	# padd the bug id with zeros
	$p_bug_id = bug_format_id( $p_bug_id );

	return '[' . $p_project_name . ' ' . $p_bug_id . ']: ' . $p_subject;
}

/**
 * clean up LF to CRLF
 * 
 * @param string $p_string
 * @return null
 */
function make_lf_crlf( $p_string ) {
	$t_string = str_replace( "\n", "\r\n", $p_string );
	return str_replace( "\r\r\n", "\r\n", $t_string );
}

/**
 * Appends an email domain to the specified email address if email is
 * not empty, it doesn't already have a domain part and if a
 * limit_email_domain is configured.
 *
 * Check limit_email_domain option and append the domain name if it is set
 * @todo limit_email_domain called after we look for @ in domain name?
 * @param string $p_email The email address to append the domain to.
 * @returns The email address with the appended domain (if applicable).
 */
function email_append_domain( $p_email ) {
	# If email is empty or already contains a domain, then return as is.
	if ( is_blank( $p_email ) || strchr( $p_email, '@' ) ) {
		return $p_email;
	}

	# If limit email domain is set, then append it.
	$t_limit_email_domain = config_get( 'limit_email_domain' );
	if ( $t_limit_email_domain === OFF ) {
		return $p_email;
	}

	return "$p_email@$t_limit_email_domain";
}

/**
 * Send a bug reminder to each of the given user, or to each user if the first parameter is an array
 * return an array of usernames to which the reminder was successfully sent
 *
 * @todo I'm not sure this shouldn't return an array of user ids... more work for the caller but cleaner from an API point of view.
 * @param array $p_recipients
 * @param int $p_bug_id
 * @param string $p_message
 * @return null
 */
function email_bug_reminder( $p_recipients, $p_bug_id, $p_message ) {
	if( !is_array( $p_recipients ) ) {
		$p_recipients = array(
			$p_recipients,
		);
	}

	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
	$t_sender_id = auth_get_current_user_id();
	$t_sender = user_get_name( $t_sender_id );

	$t_subject = email_build_subject( $p_bug_id );
	$t_date = date( config_get( 'normal_date_format' ) );

	$result = array();
	foreach( $p_recipients as $t_recipient ) {
		lang_push( user_pref_get_language( $t_recipient, $t_project_id ) );

		$t_email = user_get_email( $t_recipient );
		$result[] = user_get_name( $t_recipient );

		if( access_has_project_level( config_get( 'show_user_email_threshold' ), $t_project_id, $t_recipient ) ) {
			$t_sender_email = ' <' . current_user_get_field( 'email' ) . '>';
		} else {
			$t_sender_email = '';
		}
		$t_header = "\n" . lang_get( 'on_date' ) . " $t_date, $t_sender $t_sender_email " . lang_get( 'sent_you_this_reminder_about' ) . ": \n\n";
		$t_contents = $t_header . string_get_bug_view_url_with_fqdn( $p_bug_id, $t_recipient ) . " \n\n$p_message";

		if( ON == config_get( 'enable_email_notification' ) ) {
			email_store( $t_email, $t_subject, $t_contents );
		}

		lang_pop();
	}

	if( OFF == config_get( 'email_send_using_cronjob' ) ) {
		email_send_all();
	}

	return $result;
}

/**
 * Send bug info to given user
 * return true on success
 * @param array $p_visible_bug_data
 * @param string $p_message_id
 * @param int $p_project_id
 * @param int $p_user_id
 * @param array $p_header_optional_params
 * @return bool
 */
function email_bug_info_to_one_user( $p_visible_bug_data, $p_message_id, $p_project_id, $p_user_id, $p_header_optional_params = null ) {
	$t_user_email = user_get_email( $p_user_id );

	# check whether email should be sent
	# @@@ can be email field empty? if yes - then it should be handled here
	if( ON !== config_get( 'enable_email_notification' ) || is_blank( $t_user_email ) ) {
		return true;
	}

	# build subject
	$t_subject = '[' . $p_visible_bug_data['email_project'] . ' ' . bug_format_id( $p_visible_bug_data['email_bug'] ) . ']: ' . $p_visible_bug_data['email_summary'];

	# build message

	$t_message = lang_get_defaulted( $p_message_id, null );

	if( is_array( $p_header_optional_params ) ) {
		$t_message = vsprintf( $t_message, $p_header_optional_params );
	}

	if(( $t_message !== null ) && ( !is_blank( $t_message ) ) ) {
		$t_message .= " \n";
	}

	$t_message .= email_format_bug_message( $p_visible_bug_data );

	# build headers
	$t_bug_id = $p_visible_bug_data['email_bug'];
	$t_message_md5 = md5( $t_bug_id . $p_visible_bug_data['email_date_submitted'] );
	$t_mail_headers = array(
		'keywords' => $p_visible_bug_data['set_category'],
	);
	if( $p_message_id == 'email_notification_title_for_action_bug_submitted' ) {
		$t_mail_headers['Message-ID'] = $t_message_md5;
	} else {
		$t_mail_headers['In-Reply-To'] = $t_message_md5;
	}

	# send mail
	$t_ok = email_store( $t_user_email, $t_subject, $t_message, $t_mail_headers );

	return $t_ok;
}

/**
 * Build the bug info part of the message
 * @param array $p_visible_bug_data 
 * @return string
 */
function email_format_bug_message( $p_visible_bug_data ) {
	$t_normal_date_format = config_get( 'normal_date_format' );
	$t_complete_date_format = config_get( 'complete_date_format' );

	$t_email_separator1 = config_get( 'email_separator1' );
	$t_email_separator2 = config_get( 'email_separator2' );
	$t_email_padding_length = config_get( 'email_padding_length' );

	$t_status = $p_visible_bug_data['email_status'];

	$p_visible_bug_data['email_date_submitted'] = date( $t_complete_date_format, $p_visible_bug_data['email_date_submitted'] );
	$p_visible_bug_data['email_last_modified'] = date( $t_complete_date_format, $p_visible_bug_data['email_last_modified'] );

	$p_visible_bug_data['email_status'] = get_enum_element( 'status', $t_status );
	$p_visible_bug_data['email_severity'] = get_enum_element( 'severity', $p_visible_bug_data['email_severity'] );
	$p_visible_bug_data['email_priority'] = get_enum_element( 'priority', $p_visible_bug_data['email_priority'] );
	$p_visible_bug_data['email_reproducibility'] = get_enum_element( 'reproducibility', $p_visible_bug_data['email_reproducibility'] );

	$t_message = $t_email_separator1 . " \n";

	if( isset( $p_visible_bug_data['email_bug_view_url'] ) ) {
		$t_message .= $p_visible_bug_data['email_bug_view_url'] . " \n";
		$t_message .= $t_email_separator1 . " \n";
	}

	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_reporter' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_handler' );
	$t_message .= $t_email_separator1 . " \n";
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_project' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_bug' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_category' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_reproducibility' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_severity' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_priority' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_status' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_target_version' );

	# custom fields formatting
	foreach( $p_visible_bug_data['custom_fields'] as $t_custom_field_name => $t_custom_field_data ) {
		$t_message .= utf8_str_pad( lang_get_defaulted( $t_custom_field_name, null ) . ': ', $t_email_padding_length, ' ', STR_PAD_RIGHT );
		$t_message .= string_custom_field_value_for_email( $t_custom_field_data['value'], $t_custom_field_data['type'] );
		$t_message .= " \n";
	}

	# end foreach custom field

	if( config_get( 'bug_resolved_status_threshold' ) <= $t_status ) {
		$p_visible_bug_data['email_resolution'] = get_enum_element( 'resolution', $p_visible_bug_data['email_resolution'] );
		$t_message .= email_format_attribute( $p_visible_bug_data, 'email_resolution' );
		$t_message .= email_format_attribute( $p_visible_bug_data, 'email_fixed_in_version' );
	}
	$t_message .= $t_email_separator1 . " \n";

	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_date_submitted' );
	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_last_modified' );
	$t_message .= $t_email_separator1 . " \n";

	$t_message .= email_format_attribute( $p_visible_bug_data, 'email_summary' );

	$t_message .= lang_get( 'email_description' ) . ": \n" . $p_visible_bug_data['email_description'] . "\n";

	if ( !is_blank( $p_visible_bug_data['email_steps_to_reproduce'] ) ) {
		$t_message .= "\n" . lang_get( 'email_steps_to_reproduce' ) . ": \n" . $p_visible_bug_data['email_steps_to_reproduce'] . "\n";
	}

	if ( !is_blank( $p_visible_bug_data['email_additional_information'] ) ) {
		$t_message .= "\n" . lang_get( 'email_additional_information' ) . ": \n" . $p_visible_bug_data['email_additional_information'] . "\n";
	}

	if( isset( $p_visible_bug_data['relations'] ) ) {
		if( $p_visible_bug_data['relations'] != '' ) {
			$t_message .= $t_email_separator1 . "\n" . str_pad( lang_get( 'bug_relationships' ), 20 ) . str_pad( lang_get( 'id' ), 8 ) . lang_get( 'summary' ) . "\n" . $t_email_separator2 . "\n" . $p_visible_bug_data['relations'];
		}
	}

	# Sponsorship
	if( isset( $p_visible_bug_data['sponsorship_total'] ) && ( $p_visible_bug_data['sponsorship_total'] > 0 ) ) {
		$t_message .= $t_email_separator1 . " \n";
		$t_message .= sprintf( lang_get( 'total_sponsorship_amount' ), sponsorship_format_amount( $p_visible_bug_data['sponsorship_total'] ) ) . "\n" . "\n";

		if( isset( $p_visible_bug_data['sponsorships'] ) ) {
			foreach( $p_visible_bug_data['sponsorships'] as $t_sponsorship ) {
				$t_date_added = date( config_get( 'normal_date_format' ), $t_sponsorship->date_submitted );

				$t_message .= $t_date_added . ': ';
				$t_message .= user_get_name( $t_sponsorship->user_id );
				$t_message .= ' (' . sponsorship_format_amount( $t_sponsorship->amount ) . ')' . " \n";
			}
		}
	}

	$t_message .= $t_email_separator1 . " \n\n";

	# format bugnotes
	foreach( $p_visible_bug_data['bugnotes'] as $t_bugnote ) {
		$t_last_modified = date( $t_normal_date_format, $t_bugnote->last_modified );

		$t_formatted_bugnote_id = bugnote_format_id( $t_bugnote->id );
		$t_bugnote_link = string_process_bugnote_link( config_get( 'bugnote_link_tag' ) . $t_bugnote->id, false, false, true );

		if( $t_bugnote->time_tracking > 0 ) {
			$t_time_tracking = ' ' . lang_get( 'time_tracking' ) . ' ' . db_minutes_to_hhmm( $t_bugnote->time_tracking ) . "\n";
		} else {
			$t_time_tracking = '';
		}

		if( user_exists( $t_bugnote->reporter_id ) ) {
			$t_access_level = access_get_project_level( $p_visible_bug_data['email_project_id'] , $t_bugnote->reporter_id );
			$t_access_level_string = ' (' . get_enum_element( 'access_levels', $t_access_level ) . ') - ';
		} else {
			$t_access_level_string = '';
		}

		$t_string = ' (' . $t_formatted_bugnote_id . ') ' . user_get_name( $t_bugnote->reporter_id ) . $t_access_level_string . $t_last_modified . "\n" . $t_time_tracking . ' ' . $t_bugnote_link;

		$t_message .= $t_email_separator2 . " \n";
		$t_message .= $t_string . " \n";
		$t_message .= $t_email_separator2 . " \n";
		$t_message .= $t_bugnote->note . " \n\n";
	}

	# format history
	if( array_key_exists( 'history', $p_visible_bug_data ) ) {
		$t_message .= lang_get( 'bug_history' ) . " \n";
		$t_message .= utf8_str_pad( lang_get( 'date_modified' ), 17 ) . utf8_str_pad( lang_get( 'username' ), 15 ) . utf8_str_pad( lang_get( 'field' ), 25 ) . utf8_str_pad( lang_get( 'change' ), 20 ) . " \n";

		$t_message .= $t_email_separator1 . " \n";

		foreach( $p_visible_bug_data['history'] as $t_raw_history_item ) {
			$t_localized_item = history_localize_item( $t_raw_history_item['field'], $t_raw_history_item['type'], $t_raw_history_item['old_value'], $t_raw_history_item['new_value'], false );

			$t_message .= utf8_str_pad( date( $t_normal_date_format, $t_raw_history_item['date'] ), 17 ) . utf8_str_pad( $t_raw_history_item['username'], 15 ) . utf8_str_pad( $t_localized_item['note'], 25 ) . utf8_str_pad( $t_localized_item['change'], 20 ) . "\n";
		}
		$t_message .= $t_email_separator1 . " \n\n";
	}

	return $t_message;
}

/**
 * if $p_visible_bug_data contains specified attribute the function
 * returns concatenated translated attribute name and original
 * attribute value. Else return empty string.
 * @param array $p_visible_bug_data
 * @param string $p_attribute_id
 * @return string
 */
function email_format_attribute( $p_visible_bug_data, $attribute_id ) {
	if( array_key_exists( $attribute_id, $p_visible_bug_data ) ) {
		return utf8_str_pad( lang_get( $attribute_id ) . ': ', config_get( 'email_padding_length' ), ' ', STR_PAD_RIGHT ) . $p_visible_bug_data[$attribute_id] . "\n";
	}
	return '';
}

/**
 * Build the bug raw data visible for specified user to be translated and sent by email to the user
 * (Filter the bug data according to user access level)
 * return array with bug data. See usage in email_format_bug_message(...)
 * @param int $p_user_id
 * @param int $p_bug_id
 * @param string $p_message_id 
 * @return array
 */
function email_build_visible_bug_data( $p_user_id, $p_bug_id, $p_message_id ) {
	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
	$t_user_access_level = user_get_access_level( $p_user_id, $t_project_id );
	$t_user_bugnote_order = user_pref_get_pref( $p_user_id, 'bugnote_order' );
	$t_user_bugnote_limit = user_pref_get_pref( $p_user_id, 'email_bugnote_limit' );

	$row = bug_get_extended_row( $p_bug_id );
	$t_bug_data = array();

	$t_bug_data['email_bug'] = $p_bug_id;

	if( $p_message_id !== 'email_notification_title_for_action_bug_deleted' ) {
		$t_bug_data['email_bug_view_url'] = string_get_bug_view_url_with_fqdn( $p_bug_id );
	}

	if( access_compare_level( $t_user_access_level, config_get( 'view_handler_threshold' ) ) ) {
		if( 0 != $row['handler_id'] ) {
			$t_bug_data['email_handler'] = user_get_name( $row['handler_id'] );
		} else {
			$t_bug_data['email_handler'] = '';
		}
	}

	$t_bug_data['email_reporter'] = user_get_name( $row['reporter_id'] );
	$t_bug_data['email_project_id'] = $row['project_id'];
	$t_bug_data['email_project'] = project_get_field( $row['project_id'], 'name' );

	$t_category_name = category_full_name( $row['category_id'], false );
	$t_bug_data['email_category'] = $t_category_name;

	$t_bug_data['email_date_submitted'] = $row['date_submitted'];
	$t_bug_data['email_last_modified'] = $row['last_updated'];

	$t_bug_data['email_status'] = $row['status'];
	$t_bug_data['email_severity'] = $row['severity'];
	$t_bug_data['email_priority'] = $row['priority'];
	$t_bug_data['email_reproducibility'] = $row['reproducibility'];

	$t_bug_data['email_resolution'] = $row['resolution'];
	$t_bug_data['email_fixed_in_version'] = $row['fixed_in_version'];

	if( !is_blank( $row['target_version'] ) && access_compare_level( $t_user_access_level, config_get( 'roadmap_view_threshold' ) ) ) {
		$t_bug_data['email_target_version'] = $row['target_version'];
	}

	$t_bug_data['email_summary'] = $row['summary'];
	$t_bug_data['email_description'] = $row['description'];
	$t_bug_data['email_additional_information'] = $row['additional_information'];
	$t_bug_data['email_steps_to_reproduce'] = $row['steps_to_reproduce'];

	$t_bug_data['set_category'] = '[' . $t_bug_data['email_project'] . '] ' . $t_category_name;

	$t_bug_data['custom_fields'] = custom_field_get_linked_fields( $p_bug_id, $t_user_access_level );
	$t_bug_data['bugnotes'] = bugnote_get_all_visible_bugnotes( $p_bug_id, $t_user_bugnote_order, $t_user_bugnote_limit, $p_user_id );

	# put history data
	if(( ON == config_get( 'history_default_visible' ) ) && access_compare_level( $t_user_access_level, config_get( 'view_history_threshold' ) ) ) {
		$t_bug_data['history'] = history_get_raw_events_array( $p_bug_id, $p_user_id );
	}

	# Sponsorship Information
	if(( config_get( 'enable_sponsorship' ) == ON ) && ( access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $p_bug_id, $p_user_id ) ) ) {
		$t_sponsorship_ids = sponsorship_get_all_ids( $p_bug_id );
		$t_bug_data['sponsorship_total'] = sponsorship_get_amount( $t_sponsorship_ids );

		if( access_has_bug_level( config_get( 'view_sponsorship_details_threshold' ), $p_bug_id, $p_user_id ) ) {
			$t_bug_data['sponsorships'] = array();
			foreach( $t_sponsorship_ids as $id ) {
				$t_bug_data['sponsorships'][] = sponsorship_get( $id );
			}
		}
	}

	$t_bug_data['relations'] = relationship_get_summary_text( $p_bug_id );

	return $t_bug_data;
}
