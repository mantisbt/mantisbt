<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: email_api.php,v 1.120 2005-07-05 18:50:49 thraxisp Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	define( 'PHPMAILER_PATH', $t_core_dir . 'phpmailer' . DIRECTORY_SEPARATOR );

	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'custom_field_api.php' );
	require_once( $t_core_dir . 'string_api.php' );
	require_once( $t_core_dir . 'history_api.php' );
	require_once( PHPMAILER_PATH . 'class.phpmailer.php' );

	# reusable object of class SMTP
	$g_phpMailer_smtp = null;

	###########################################################################
	# Email API
	###########################################################################


	# --------------------
	# Return a perl compatible regular expression that will
	#  match a valid email address as per RFC 822 (approximately)
	#
	# The regex will provide too matched groups: the first will be the
	#  local part (or mailbox name) and the second will be the domain
	function email_get_rfc822_regex() {
		# Build up basic RFC 822 BNF definitions.

		# list of the special characters: ( ) < > @ , ; : \ " . [ ]
		$t_specials = '\(\)\<\>\@\,\;\:\\\"\.\[\]';
		# the space character
		$t_space    = '\040';
		# valid characters in a quoted string
		$t_char     = '\000-\177';
		# control characters
		$t_ctl      = '\000-\037\177';

		# a chunk of quoted text (anything except " \ \r are valid)
		$t_qtext_re = '[^"\\\r]+';
		# match any valid character preceded by a backslash ( mostly for \" )
		$t_qpair_re = "\\\\[$t_char]";

		# a complete quoted string - " characters with valid characters or
		#  backslash-escaped characters between them
		$t_quoted_string_re = "(?:\"(?:$t_qtext_re|$t_qpair_re)*\")";

		# an unquoted atom (anything that isn't a control char, a space, or a
		#  special char)
		$t_atom_re  = "(?:[^$t_ctl$t_space$t_specials]+)";

		# a domain ref is an atom
		$t_domain_ref_re = $t_atom_re;

		# the characters in a domain literal can be anything except: [ ] \ \r
		$t_dtext_re = "[^\\[\\]\\\\\\r]";
		# a domain-literal is a sequence of characters or escaped pairs inside
		#  square brackets
		$t_domain_literal_re = "\\[(?:$t_dtext_re|$t_qpair_re)*\\]";
		# a subdomain is a domain ref or a domain literal
		$t_sub_domain_re = "(?:$t_domain_ref_re|$t_domain_literal_re)";
		# a domain is at least one subdomain, with optional further subdomains
		#  separated by periods.  eg: '[1.2.3.4]' or 'foo.bar'
		$t_domain_re = "$t_sub_domain_re(?:\.$t_sub_domain_re)*";

		# a word is either quoted string or an atom
		$t_word_re = "(?:$t_atom_re|$t_quoted_string_re)";

		# the local part of the address spec (the mailbox name)
		#  is one or more words separated by periods
		$t_local_part_re = "$t_word_re(?:\.$t_word_re)*";

		# the address spec is made up of a local part, and @ symbol,
		#  and a domain
		$t_addr_spec_re = "/^($t_local_part_re)\@($t_domain_re)$/";

		return $t_addr_spec_re;
	}
	# --------------------
	# check to see that the format is valid and that the mx record exists
	function email_is_valid( $p_email ) {
		# if we don't validate then just accept
		if ( OFF == config_get( 'validate_email' ) ) {
			return true;
		}

		if ( is_blank( $p_email ) && ON == config_get( 'allow_blank_email' ) ) {
			return true;
		}

		# Use a regular expression to check to see if the email is in valid format
		#  x-xx.xxx@yyy.zzz.abc etc.
		if ( preg_match( email_get_rfc822_regex(), $p_email, $t_check ) ) {
			$t_local = $t_check[1];
			$t_domain = $t_check[2];

			# see if we're limited to one domain
			if ( ON == config_get( 'limit_email_domain' ) ) {
				if ( 0 != strcasecmp( $t_limit_email_domain, $t_domain ) ) {
					return false;
				}
			}

			if ( preg_match( '/\\[(\d+)\.(\d+)\.(\d+)\.(\d+)\\]/', $t_domain, $t_check ) ) {
				# Handle domain-literals of the form '[1.2.3.4]'
				#  as long as each segment is less than 255, we're ok
				if ( $t_check[1] <= 255 &&
					 $t_check[2] <= 255 &&
					 $t_check[3] <= 255 &&
					 $t_check[4] <= 255 ) {
					return true;
				}
			} else if ( ON == config_get( 'check_mx_record' ) ) {
				# Check for valid mx records
				if ( getmxrr( $t_domain, $temp ) ) {
					return true;
				} else {
					$host = $t_domain . '.';

					# for no mx record... try dns check
					if ( checkdnsrr( $host, 'ANY' ) ) {
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
	# --------------------
	# Check if the email address is valid
	#  return true if it is, trigger an ERROR if it isn't
	function email_ensure_valid( $p_email ) {
		if ( !email_is_valid( $p_email ) ) {
			trigger_error( ERROR_EMAIL_INVALID, ERROR );
		}
	}
	# --------------------
	# email_notify_flag
	# Get the value associated with the specific action and flag.
	# For example, you can get the value associated with notifying "admin"
	# on action "new", i.e. notify administrators on new bugs which can be
	# ON or OFF.
	function email_notify_flag( $action, $flag ) {
		$t_notify_flags = config_get( 'notify_flags' );
		$t_default_notify_flags = config_get( 'default_notify_flags' );
		if ( isset ( $t_notify_flags[$action][$flag] ) ) {
			return $t_notify_flags[$action][$flag];
		} elseif ( isset ( $t_default_notify_flags[$flag] ) ) {
			return $t_default_notify_flags[$flag];
		}

		return OFF;
	}

	# @@@ yarick123: email_collect_recipients(...) will be completely rewritten to provide additional
	#     information such as language, user access,..
	# @@@ yarick123:sort recipients list by language to reduce switches between different languages
	function email_collect_recipients( $p_bug_id, $p_notify_type ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_recipients = array();

		# add Reporter
		if ( ON == email_notify_flag( $p_notify_type, 'reporter' ) ) {
			$t_reporter_id = bug_get_field( $p_bug_id, 'reporter_id' );
			$t_recipients[$t_reporter_id] = true;
			log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, add reporter=$t_reporter_id" );
		}

		# add Handler
		if ( ON == email_notify_flag( $p_notify_type, 'handler' )) {
			$t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );
			$t_recipients[$t_handler_id] = true;
			log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, add handler=$t_handler_id" );
		}

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		# add users monitoring the bug
		$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );
		if ( ON == email_notify_flag( $p_notify_type, 'monitor' ) ) {
			$query = "SELECT DISTINCT user_id
					  FROM $t_bug_monitor_table
					  WHERE bug_id=$c_bug_id";
			$result = db_query( $query );

			$count = db_num_rows( $result );
			for ( $i=0 ; $i < $count ; $i++ ) {
				$t_user_id = db_result( $result, $i );
				$t_recipients[$t_user_id] = true;
			log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, add monitor=$t_user_id" );
			}
		}

		# add users who contributed bugnotes
		$t_bugnote_id = bugnote_get_latest_id( $p_bug_id );
		$t_bugnote_view = bugnote_get_field( $t_bugnote_id, 'view_state' );
		$t_bugnote_date = db_unixtimestamp( bugnote_get_field( $t_bugnote_id, 'last_modified' ) );
		$t_bug_date = bug_get_field( $p_bug_id, 'last_updated' );

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );
		if ( ON == email_notify_flag( $p_notify_type, 'bugnotes' ) ) {
			$query = "SELECT DISTINCT reporter_id
					  FROM $t_bugnote_table
					  WHERE bug_id = $c_bug_id";
			$result = db_query( $query );

			$count = db_num_rows( $result );
			for( $i=0 ; $i < $count ; $i++ ) {
				$t_user_id = db_result( $result, $i );
				$t_recipients[$t_user_id] = true;
				log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, add note=$t_user_id" );
			}
		}

		# add project users who meet the thresholds
		$t_bug_is_private = bug_get_field( $p_bug_id, 'view_state' ) == VS_PRIVATE;
		$t_threshold_min = email_notify_flag( $p_notify_type, 'threshold_min' );
		$t_threshold_max = email_notify_flag( $p_notify_type, 'threshold_max' );
		$t_threshold_users = project_get_all_user_rows( $t_project_id, $t_threshold_min );
		foreach( $t_threshold_users as $t_user ) {
			if ( $t_user['access_level'] <= $t_threshold_max ) {
				if ( !$t_bug_is_private || access_compare_level( $t_user['access_level'], config_get( 'private_bug_threshold' ) ) ) {
					$t_recipients[$t_user['id']] = true;
					log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, add project=" . $t_user['id'] );
				}
			}
		}

		# set up to eliminate unwanted users
		#  get list of status values that are not covered specifically in the prefs
		#  These are handled by email_on_status generically
		#  @@@ thraxisp note that email_on_assigned was co-opted to handle change in handler
		$t_status_change = get_enum_to_array( config_get( 'status_enum_string' ) );
		unset( $t_status_change[NEW_] );
		unset( $t_status_change[FEEDBACK] );
		unset( $t_status_change[RESOLVED] );
		unset( $t_status_change[CLOSED] );

		if ( 'owner' == $p_notify_type ) {
			$t_pref_field = 'email_on_assigned';
		} else if ( in_array( $p_notify_type, $t_status_change ) ) {
			$t_pref_field = 'email_on_status';
		} else {
			$t_pref_field = 'email_on_' . $p_notify_type;
		}
		$t_user_pref_table = config_get( 'mantis_user_pref_table' );
		if ( !db_field_exists( $t_pref_field, $t_user_pref_table ) ) {
			$t_pref_field = false;
		}

		# @@@ we could optimize by modifiying user_cache() to take an array
		#  of user ids so we could pull them all in.  We'll see if it's necessary
		$t_final_recipients = array();
		# Check whether users should receive the emails
		# and put email address to $t_recipients[user_id]
		foreach ( $t_recipients as $t_id => $t_ignore ) {

			# Possibly eliminate the current user
			if ( ( auth_get_current_user_id() == $t_id ) &&
				 ( OFF == config_get( 'email_receive_own' ) ) ) {
				log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, drop $t_id (own)" );
				continue;
			}

			# Eliminate users who don't exist anymore or who are disabled
			if ( !user_exists( $t_id ) ||
				 !user_is_enabled( $t_id ) ) {
				log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, drop $t_id (disabled)" );
				continue;
			}

			# Exclude users who have this notification type turned off
			if ( $t_pref_field ) {
				$t_notify = user_pref_get_pref( $t_id, $t_pref_field );
				if ( OFF == $t_notify ) {
					log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, drop $t_id (pref $t_pref_field off)" );
					continue;
				} else {
					# Users can define the severity of an issue before they are emailed for
					# each type of notification
					$t_min_sev_pref_field = $t_pref_field . '_min_severity';
					$t_min_sev_notify     = user_pref_get_pref( $t_id, $t_min_sev_pref_field );
					$t_bug_severity       = bug_get_field( $p_bug_id, 'severity' );

					if ( $t_bug_severity < $t_min_sev_notify ) {
						log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, drop $t_id (pref threshold)" );
						continue;
					}
				}
			}

			# check that user can see bugnotes if the last update included a bugnote
			if ( $t_bug_date == $t_bugnote_date ) {
				if ( !access_has_bugnote_level( VIEWER, $t_bugnote_id, $t_id ) ) {
						log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, drop $t_id (access level)" );
					continue;
				}
			}

			# Finally, let's get their emails, if they've set one
			$t_email = user_get_email( $t_id );
			if ( is_blank( $t_email ) ) {
				log_event( LOG_EMAIL_RECIPIENT, "bug= $p_bug_id, drop $t_id (no email)" );
			} else {
				# @@@ we could check the emails for validity again but I think
				#   it would be too slow
				$t_final_recipients[$t_id] = $t_email;
			}
		}
		return $t_final_recipients;
	}

	# --------------------
	# Send password to user
	function email_signup( $p_user_id, $p_password, $p_confirm_hash ) {

		if ( ( OFF == config_get( 'send_reset_password' ) ) || ( OFF == config_get( 'enable_email_notification' ) ) ) {
					return;
		}

		lang_push( user_pref_get_language( $p_user_id ) );

		# retrieve the username and email
		$t_username = user_get_field( $p_user_id, 'username' );
		$t_email = user_get_email( $p_user_id );

		# Build Welcome Message
		$t_subject = '[' . config_get( 'window_title' ) . '] ' . lang_get( 'new_account_subject' );

		$t_message = lang_get( 'new_account_greeting' ) . $t_username .
						lang_get( 'new_account_greeting2' ) . " \n\n" .
						string_get_confirm_hash_url( $p_user_id, $p_confirm_hash ) . " \n\n" .
						lang_get( 'new_account_message' ) .
						lang_get( 'new_account_do_not_reply' );

		# Send signup email regardless of mail notification pref
		# or else users won't be able to sign up
		if( !is_blank( $t_email ) ) {
			email_send( $t_email, $t_subject, $t_message );
			log_event( LOG_EMAIL, "signup=$t_email, hash=$p_confirm_hash, id=$p_user_id" );
		}

		lang_pop();
	}

	# --------------------
	# Send confirm_hash url to user forgets the password
	function email_send_confirm_hash_url( $p_user_id, $p_confirm_hash ) {

		if ( ( OFF == config_get( 'send_reset_password' ) ) || ( OFF == config_get( 'enable_email_notification' ) ) ) {
			return;
		}

		lang_push( user_pref_get_language( $p_user_id ) );

		# retrieve the username and email
		$t_username = user_get_field( $p_user_id, 'username' );
		$t_email = user_get_email( $p_user_id );

		$t_subject = '[' . config_get( 'window_title' ) . '] ' . lang_get( 'lost_password_subject' );

		$t_message = lang_get( 'reset_request_msg' ) . " \n\n" .
						string_get_confirm_hash_url( $p_user_id, $p_confirm_hash ) . " \n\n" .
						lang_get( 'new_account_username' ) . $t_username . " \n" .
						lang_get( 'new_account_IP' ) . $_SERVER["REMOTE_ADDR"] . " \n\n" .
						lang_get( 'new_account_do_not_reply' );

		# Send password reset regardless of mail notification prefs
		# or else users won't be able to receive their reset pws
		if( !is_blank( $t_email ) ) {
			email_send( $t_email, $t_subject, $t_message );
			log_event( LOG_EMAIL, "password_reset=$t_email" );
		}

		lang_pop();
	}

	# --------------------
	# notify the selected group a new user has signup
	function email_notify_new_account( $p_username, $p_email ) {
		global $g_path;

		$t_threshold_min = config_get( 'notify_new_user_created_threshold_min' );
		$t_threshold_users = project_get_all_user_rows( ALL_PROJECTS, $t_threshold_min );

		foreach( $t_threshold_users as $t_user ) {
			lang_push( user_pref_get_language( $t_user['id'] ) );

			$t_recipient_email = user_get_email( $t_user['id'] );
			$t_subject = '[' . config_get( 'window_title' ) . '] ' . lang_get( 'new_account_subject' );

			$t_message = lang_get( 'new_account_signup_msg' ) . " \n\n" .
						lang_get( 'new_account_username' ) . $p_username . " \n" .
						lang_get( 'new_account_email' ) . $p_email . " \n" .
						lang_get( 'new_account_IP' ) . $_SERVER["REMOTE_ADDR"] . " \n" .
						$g_path . "\n\n" .
						lang_get( 'new_account_do_not_reply' );

			if( !is_blank( $t_recipient_email ) ) {
				email_send( $t_recipient_email, $t_subject, $t_message );
				log_event( LOG_EMAIL, "new_account_notify=$t_recipient_email" );
			}

			lang_pop();
		}
	}

	# --------------------
	# send a generic email
	# $p_notify_type: use check who she get notified of such event.
	# $p_message_id: message id to be translated and included at the top of the email message.
	# Return false if it were problems sending email
	function email_generic( $p_bug_id, $p_notify_type, $p_message_id = null, $p_header_optional_params = null ) {
		$t_ok = true;
		if ( ON === config_get( 'enable_email_notification' ) ) {
			ignore_user_abort( true );

			# @@@ yarick123: email_collect_recipients(...) will be completely rewritten to provide additional
			#     information such as language, user access,..
			# @@@ yarick123:sort recipients list by language to reduce switches between different languages
			$t_recipients = email_collect_recipients( $p_bug_id, $p_notify_type );

			$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
			if ( is_array( $t_recipients ) ) {
				log_event( LOG_EMAIL, sprintf("bug=%d, type=%s, msg=%s, recipients=(%s)", $p_bug_id, $p_notify_type, $p_message_id, implode( '. ', $t_recipients ) ) );

				# send email to every recipient
				foreach ( $t_recipients as $t_user_id => $t_user_email ) {
					# load (push) user language here as build_visible_bug_data assumes current language
					lang_push( user_pref_get_language( $t_user_id, $t_project_id ) );

					$t_visible_bug_data = email_build_visible_bug_data( $t_user_id, $p_bug_id, $p_message_id );
					$t_ok = email_bug_info_to_one_user( $t_visible_bug_data, $p_message_id, $t_project_id, $t_user_id, $p_header_optional_params ) && $t_ok;
					
					lang_pop();
				}
			}
		}

		return $t_ok;
	}

	# --------------------
	# send notices when a relationship is ADDED
	# MASC RELATIONSHIP
	function email_relationship_added( $p_bug_id, $p_related_bug_id, $p_rel_type ) {
		$t_opt = array();
		$t_opt[] = bug_format_id( $p_related_bug_id );
		switch ( $p_rel_type ) {
			case BUG_BLOCKS:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_blocks_relationship_added', $t_opt );
				break;
			case BUG_DEPENDANT:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_dependant_on_relationship_added', $t_opt );
				break;
			case BUG_HAS_DUPLICATE:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_has_duplicate_relationship_added', $t_opt );
				break;
			case BUG_DUPLICATE:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_duplicate_of_relationship_added', $t_opt );
				break;
			case BUG_RELATED:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_related_to_relationship_added', $t_opt );
				break;
			default:
				trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
				break;
			}
	}

	# --------------------
	# send notices when a relationship is DELETED
	# MASC RELATIONSHIP
	function email_relationship_deleted( $p_bug_id, $p_related_bug_id, $p_rel_type ) {
		$t_opt = array();
		$t_opt[] = bug_format_id( $p_related_bug_id );
		switch ( $p_rel_type ) {
			case BUG_BLOCKS:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_blocks_relationship_deleted', $t_opt );
				break;
			case BUG_DEPENDANT:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_dependant_on_relationship_deleted', $t_opt );
				break;
			case BUG_HAS_DUPLICATE:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_has_duplicate_relationship_deleted', $t_opt );
				break;
			case BUG_DUPLICATE:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_duplicate_of_relationship_deleted', $t_opt );
				break;
			case BUG_RELATED:
				email_generic( $p_bug_id, 'relation', 'email_notification_title_for_action_related_to_relationship_deleted', $t_opt );
				break;
			default:
				trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
				break;
			}
	}

	# --------------------
	# send notices to all the handlers of the parent bugs when a child bug is RESOLVED
	# MASC RELATIONSHIP
	function email_relationship_child_resolved( $p_bug_id ) {
		email_relationship_child_resolved_closed( $p_bug_id, 'email_notification_title_for_action_relationship_child_resolved' );
	}

	# --------------------
	# send notices to all the handlers of the parent bugs when a child bug is CLOSED
	# MASC RELATIONSHIP
	function email_relationship_child_closed( $p_bug_id ) {
		email_relationship_child_resolved_closed( $p_bug_id, 'email_notification_title_for_action_relationship_child_closed' );
	}

	# --------------------
	# send notices to all the handlers of the parent bugs still open when a child bug is resolved/closed
	# MASC RELATIONSHIP
	function email_relationship_child_resolved_closed( $p_bug_id, $p_message_id ) {

		# retrieve all the relationships in which the bug is the destination bug
		$t_relationship = relationship_get_all_dest( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		if ( $t_relationship_count == 0 ) {
			# no parent bug found
			return;
		}

		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			if ( $t_relationship[$i]->type == BUG_DEPENDANT ) {
				$t_src_bug_id = $t_relationship[$i]->src_bug_id;
				$t_status = bug_get_field( $t_src_bug_id, 'status' );
				if ( $t_status < config_get( 'bug_resolved_status_threshold' ) ) {
					# sent the notification just for parent bugs not resolved/closed
					$t_opt = array();
					$t_opt[] = bug_format_id( $p_bug_id );
					email_generic( $t_src_bug_id, 'handler', $p_message_id, $t_opt );
				}
			}
		}
	}

	# --------------------
	# send notices when a bug is sponsored
	function email_sponsorship_added( $p_bug_id ) {
		email_generic( $p_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_added' );
	}

	# --------------------
	# send notices when a sponsorship is modified
	function email_sponsorship_updated( $p_bug_id ) {
		email_generic( $p_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_updated' );
	}

	# --------------------
	# send notices when a sponsorship is deleted
	function email_sponsorship_deleted( $p_bug_id ) {
		email_generic( $p_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_deleted' );
	}

	# --------------------
	# send notices when a new bug is added
	function email_new_bug( $p_bug_id ) {
		email_generic( $p_bug_id, 'new', 'email_notification_title_for_action_bug_submitted' );
	}
	# --------------------
	# send notices when a new bugnote
	function email_bugnote_add( $p_bug_id ) {
		email_generic( $p_bug_id, 'bugnote', 'email_notification_title_for_action_bugnote_submitted' );
	}
	# --------------------
	# send notices when a bug is RESOLVED
	function email_resolved( $p_bug_id ) {
		email_generic( $p_bug_id, 'resolved', 'email_notification_title_for_status_bug_resolved' );
	}
	# --------------------
	# send notices when a bug is CLOSED
	function email_close( $p_bug_id ) {
		email_generic( $p_bug_id, 'closed', 'email_notification_title_for_status_bug_closed' );
	}
	# --------------------
	# send notices when a bug is REOPENED
	function email_reopen( $p_bug_id ) {
		email_generic( $p_bug_id, 'reopened', 'email_notification_title_for_action_bug_reopened' );
	}
	# --------------------
	# send notices when a bug is ASSIGNED
	function email_assign( $p_bug_id ) {
		email_generic( $p_bug_id, 'owner', 'email_notification_title_for_action_bug_assigned' );
	}
	# --------------------
	# send notices when a bug is DELETED
	function email_bug_deleted( $p_bug_id ) {
		email_generic( $p_bug_id, 'deleted', 'email_notification_title_for_action_bug_deleted' );
	}
	# --------------------
	# this function sends the actual email
	# if $p_exit_on_error == true (default) - calls exit() on errors, else - returns true on success and false on errors
	# @@@@ (thraxisp) $p_header doesn't work as expected, it adds a list of names to the bcc list, rather than headers
	#         this is ok for now as nothing uses it
	function email_send( $p_recipient, $p_subject, $p_message, $p_header='', $p_category='', $p_exit_on_error=true ) {
		global $g_phpMailer_smtp;

		$t_recipient = trim( $p_recipient );
		$t_subject   = string_email( trim( $p_subject ) );
		$t_message   = string_email_links( trim( $p_message ) );

		# short-circuit if no recipient is defined, or email disabled
		# note that this may cause signup messages not to be sent

		if ( is_blank( $p_recipient ) || ( OFF == config_get( 'enable_email_notification' ) ) ) {
			return;
		}

		# for debugging only
		#PRINT $t_recipient.'<br />'.$t_subject.'<br />'.$t_message.'<br />'.$t_headers;
		#exit;
		#PRINT '<br />xxxRecipient ='.$t_recipient.'<br />';
		#PRINT 'Headers ='.nl2br($t_headers).'<br />';
		#PRINT $t_subject.'<br />';
		#PRINT nl2br($t_message).'<br />';
		#exit;

		$t_debug_email = config_get('debug_email');

		# Visit http://phpmailer.sourceforge.net
		# if you have problems with phpMailer

		$mail = new PHPMailer;

		$mail->PluginDir = PHPMAILER_PATH;
		# @@@ should this be the current language (for the recipient) or the default one (for the user running the command) (thraxisp)
		$mail->SetLanguage( lang_get( 'phpmailer_language', lang_get_current() ), PHPMAILER_PATH . 'language' . DIRECTORY_SEPARATOR );

		# Select the method to send mail
		switch ( config_get( 'phpMailer_method' ) ) {
			case 0: $mail->IsMail();
					break;

			case 1: $mail->IsSendmail();
					break;

			case 2: $mail->IsSMTP();
					{
						# SMTP collection is always kept alive
						#
						$mail->SMTPKeepAlive = true;
						# @@@ yarick123: It is said in phpMailer comments, that phpMailer::smtp has private access.
						# but there is no common method to reset PHPMailer object, so
						# I see the smallest evel - to initialize only one 'private'
						# field phpMailer::smtp in order to reuse smtp connection.

						if( is_null( $g_phpMailer_smtp ) )  {
							register_shutdown_function( 'email_smtp_close' );
						} else {
							$mail->smtp = $g_phpMailer_smtp;
						}
					}
					break;
		}
		$mail->IsHTML(false);              # set email format to plain text
		$mail->WordWrap = 80;              # set word wrap to 50 characters
		$mail->Priority = config_get( 'mail_priority' );               # Urgent = 1, Not Urgent = 5, Disable = 0
		$mail->CharSet = lang_get( 'charset', lang_get_current() );
		$mail->Host     = config_get( 'smtp_host' );
		$mail->From     = config_get( 'from_email' );
		$mail->Sender   = config_get( 'return_path_email' );
		$mail->FromName = '';
		if ( !is_blank( config_get( 'smtp_username' ) ) ) {     # Use SMTP Authentication
			$mail->SMTPAuth = true;
			$mail->Username = config_get( 'smtp_username' );
			$mail->Password = config_get( 'smtp_password' );
		}

		$t_debug_to = '';
		# add to the Recipient list
		$t_recipient_list = split(',', $t_recipient);
		while ( list( , $t_recipient ) = each( $t_recipient_list ) ) {
			if ( !is_blank( $t_recipient ) ) {
				if ( OFF === $t_debug_email ) {
					$mail->AddAddress( $t_recipient, '' );
				} else {
					$t_debug_to .= !is_blank( $t_debug_to ) ? ', ' : '';
					$t_debug_to .= $t_recipient;
				}
			}
		}

		# add to the BCC list
		$t_debug_bcc = '';
		$t_bcc_list = split(',', $p_header);
		while(list(, $t_bcc) = each($t_bcc_list)) {
			if ( !is_blank( $t_bcc ) ) {
				if ( OFF === $t_debug_email ) {
					$mail->AddBCC( $t_bcc, '' );
				} else {
					$t_debug_bcc .= !is_blank( $t_debug_bcc ) ? ', ' : '';
					$t_debug_bcc .= $t_bcc;
				}
			}
		}

		if ( OFF !== $t_debug_email ) {
			$t_message = "\n" . $t_message;

			if ( !is_blank( $t_debug_bcc ) ) {
				$t_message = 'Bcc: ' . $t_debug_bcc . "\n" . $t_message;
			}

			if ( !is_blank( $t_debug_to ) ) {
				$t_message = 'To: '. $t_debug_to . "\n" . $t_message;
			}

			$mail->AddAddress( $t_debug_email, '' );
		}

		$mail->Subject = $t_subject;
		$mail->Body    = make_lf_crlf( "\n".$t_message );

		if ( EMAIL_CATEGORY_PROJECT_CATEGORY == config_get( 'email_set_category' ) )  {
			$mail->AddCustomHeader( "Keywords: $p_category" );
		}

		if ( !$mail->Send() ) {
			PRINT "PROBLEMS SENDING MAIL TO: $p_recipient<br />";
			PRINT 'Mailer Error: '.$mail->ErrorInfo.'<br />';
			if ( $p_exit_on_error )  {
				exit;
			} else {
				return false;
			}
		}

		if ( !is_null( $mail->smtp ) )  {
			# @@@ yarick123: It is said in phpMailer comments, that phpMailer::smtp has private access.
			# but there is no common method to reset PHPMailer object, so
			# I see the smallest evel - to initialize only one 'private'
			# field phpMailer::smtp in order to reuse smtp connection.
			$g_phpMailer_smtp = $mail->smtp;
		}

		return true;
	}

	# --------------------
	# closes opened kept alive SMTP connection (if it was opened)
	function email_smtp_close()  {
		global $g_phpMailer_smtp;

		if ( !is_null( $g_phpMailer_smtp ) )  {
			if ( $g_phpMailer_smtp->Connected() )  {
				$g_phpMailer_smtp->Quit();
				$g_phpMailer_smtp->Close();
			}
			$g_phpMailer_smtp = null;
		}
	}
	# --------------------
	# formats the subject correctly
	# we include the project name, bug id, and summary.
	function email_build_subject( $p_bug_id ) {
		# grab the project name
		$p_project_name = project_get_field( bug_get_field( $p_bug_id, 'project_id' ), 'name' );

		# grab the subject (summary)
		$p_subject = bug_get_field( $p_bug_id, 'summary' );

		# padd the bug id with zeros
		$p_bug_id = bug_format_id( $p_bug_id );

		return '['.$p_project_name.' '.$p_bug_id.']: '.$p_subject;
	}
	# --------------------
	# clean up LF to CRLF
	function make_lf_crlf( $p_string ) {
		$t_string = str_replace( "\n", "\r\n", $p_string );
		return str_replace( "\r\r\n", "\r\n", $t_string );
	}
	# --------------------
	# Check limit_email_domain option and append the domain name if it is set
	function email_append_domain( $p_email ) {
		$t_limit_email_domain = config_get( 'limit_email_domain' );
		if ( $t_limit_email_domain && !is_blank( $p_email ) ) {
			$p_email = "$p_email@$t_limit_email_domain";
		}

		return $p_email;
	}
	# --------------------
	# Send a bug reminder to each of the given user, or to each user if the first
	#  parameter is an array
	# return an array of usernames to which the reminder was successfully sent
	#
	# @@@ I'm not sure this shouldn't return an array of user ids... more work for
	#  the caller but cleaner from an API point of view.
	function email_bug_reminder( $p_recipients, $p_bug_id, $p_message ) {

		if ( !is_array( $p_recipients ) ) {
			$p_recipients = array( $p_recipients );
		}

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
		$t_sender_id = auth_get_current_user_id();
		$t_sender = user_get_name( $t_sender_id );

		$t_subject = email_build_subject( $p_bug_id );
		$t_date = date( config_get( 'normal_date_format' ) );
		
		$result = array();
		foreach ( $p_recipients as $t_recipient ) {
			lang_push( user_pref_get_language( $t_recipient, $t_project_id ) );

			$t_email = user_get_email( $t_recipient );
			$result[] = user_get_name( $t_recipient );

			if ( access_has_project_level( config_get( 'show_user_email_threshold' ), $t_project_id, $t_recipient ) ) {
				$t_sender_email = ' <' . current_user_get_field( 'email' ) . '>' ;
			} else {
				$t_sender_email = '';
			}
			$t_header = "\n" . lang_get( 'on' ) . " $t_date, $t_sender $t_sender_email " .
						lang_get( 'sent_you_this_reminder_about' ) . ": \n\n";
			$t_contents = $t_header .
							string_get_bug_view_url_with_fqdn( $p_bug_id, $t_recipient ) .
							" \n\n$p_message";

			if( ON == config_get( 'enable_email_notification' ) ) {
				email_send( $t_email, $t_subject, $t_contents );
			}

			lang_pop();
		}
		return $result;
	}

	# --------------------
	# Send bug info to given user
	# return true on success
	function email_bug_info_to_one_user( $p_visible_bug_data, $p_message_id, $p_project_id, $p_user_id, $p_header_optional_params = null ) {

		$t_user_email = user_get_email( $p_user_id );

		# check whether email should be sent
		# @@@ can be email field empty? if yes - then it should be handled here
		if ( ON !== config_get( 'enable_email_notification' ) || is_blank( $t_user_email ) ) {
			return true;
		}

		# build subject
		$t_subject = '['.$p_visible_bug_data['email_project'].' '
						.bug_format_id( $p_visible_bug_data['email_bug'] )
					.']: '.$p_visible_bug_data['email_summary'];

		# build message

		$t_message = lang_get_defaulted( $p_message_id, null );

		if ( is_array( $p_header_optional_params ) ) {
			$t_message = vsprintf( $t_message, $p_header_optional_params );
		}

		if ( ( $t_message !== null ) && ( !is_blank( $t_message ) ) ) {
			$t_message .= " \n";
		}

		$t_message .= email_format_bug_message(  $p_visible_bug_data );

		# send mail
		# PRINT '<br />email_bug_info::Sending email to :'.$t_user_email;
		$t_ok = email_send( $t_user_email, $t_subject, $t_message, '', $p_visible_bug_data['set_category'], false );

		return $t_ok;
	}

	# --------------------
	# Build the bug info part of the message
	function email_format_bug_message( $p_visible_bug_data ) {

		$t_normal_date_format = config_get( 'normal_date_format' );
		$t_complete_date_format = config_get( 'complete_date_format' );

		$t_email_separator1 = config_get( 'email_separator1' );
		$t_email_separator2 = config_get( 'email_separator2' );
		$t_email_padding_length = config_get( 'email_padding_length' );

		$t_status = $p_visible_bug_data['email_status'];

		$p_visible_bug_data['email_date_submitted'] = date( $t_complete_date_format, $p_visible_bug_data['email_date_submitted'] );
		$p_visible_bug_data['email_last_modified']   = date( $t_complete_date_format, $p_visible_bug_data['email_last_modified'] );

		$p_visible_bug_data['email_status'] = get_enum_element( 'status', $t_status );
		$p_visible_bug_data['email_severity'] = get_enum_element( 'severity', $p_visible_bug_data['email_severity'] );
		$p_visible_bug_data['email_priority'] = get_enum_element( 'priority', $p_visible_bug_data['email_priority'] );
		$p_visible_bug_data['email_reproducibility'] = get_enum_element( 'reproducibility', $p_visible_bug_data['email_reproducibility'] );

		$t_message = $t_email_separator1 . " \n";

		if ( isset( $p_visible_bug_data['email_bug_view_url'] ) ) {
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

		# custom fields formatting
		foreach( $p_visible_bug_data['custom_fields'] as $t_custom_field_name => $t_custom_field_data ) {
			$t_message .= str_pad( lang_get_defaulted( $t_custom_field_name, null ) . ': ', $t_email_padding_length, ' ', STR_PAD_RIGHT );
			$t_message .= string_custom_field_value_for_email ( $t_custom_field_data['value'], $t_custom_field_data['type'] );
			$t_message .= " \n";
		} # end foreach custom field

		if ( config_get( 'bug_resolved_status_threshold' ) <= $t_status ) {
			$p_visible_bug_data['email_resolution'] = get_enum_element( 'resolution', $p_visible_bug_data['email_resolution'] );
			$t_message .= email_format_attribute( $p_visible_bug_data, 'email_resolution' );
			$t_message .= email_format_attribute( $p_visible_bug_data, 'email_duplicate' );
			$t_message .= email_format_attribute( $p_visible_bug_data, 'email_fixed_in_version' );
		}
		$t_message .= $t_email_separator1 . " \n";

		$t_message .= email_format_attribute( $p_visible_bug_data, 'email_date_submitted' );
		$t_message .= email_format_attribute( $p_visible_bug_data, 'email_last_modified' );
		$t_message .= $t_email_separator1 . " \n";

		$t_message .= email_format_attribute( $p_visible_bug_data, 'email_summary' );

		$t_message .= lang_get( 'email_description' ) . ": \n".wordwrap( $p_visible_bug_data['email_description'] )."\n";

		# MASC RELATIONSHIP
		if ( ON == config_get( 'enable_relationship' ) ) {
			if (isset( $p_visible_bug_data['relations'] )) {
				$t_message .= $p_visible_bug_data['relations'];
			}
		}
		# MASC RELATIONSHIP

		# Sponsorship
		if ( isset( $p_visible_bug_data['sponsorship_total'] ) && ( $p_visible_bug_data['sponsorship_total'] > 0 ) ) {
			$t_message .= $t_email_separator1 . " \n";
			$t_message .= sprintf( lang_get( 'total_sponsorship_amount' ), sponsorship_format_amount( $p_visible_bug_data['sponsorship_total'] ) ) . "\n" . "\n";

			if ( isset( $p_visible_bug_data['sponsorships'] ) ) {
				foreach ( $p_visible_bug_data['sponsorships'] as $t_sponsorship ) {
					$t_date_added = date( config_get( 'normal_date_format' ), $t_sponsorship->date_submitted );

					$t_message .= $t_date_added . ': ';
					$t_message .= user_get_name( $t_sponsorship->user_id );
					$t_message .= ' (' . sponsorship_format_amount( $t_sponsorship->amount ) . ')' . " \n";
				}
			}
		}

		$t_message .= $t_email_separator1 . " \n\n";

		# format bugnotes
		foreach ( $p_visible_bug_data['bugnotes'] as $t_bugnote ) {
			$t_last_modified = date( $t_normal_date_format, $t_bugnote->last_modified );
			$t_string = ' '. user_get_name( $t_bugnote->reporter_id ) . ' - ' . $t_last_modified . ' ';

			$t_message .= $t_email_separator2 . " \n";
			$t_message .= $t_string . " \n";
			$t_message .= $t_email_separator2 . " \n";
			$t_message .= wordwrap( $t_bugnote->note ) . " \n\n";
		}

		# format history
		if ( array_key_exists( 'history', $p_visible_bug_data ) ) {
			$t_message .=	lang_get( 'bug_history' ) . " \n";
			$t_message .=	str_pad( lang_get( 'date_modified' ), 16 ) .
							str_pad( lang_get( 'username' ), 15 ) .
							str_pad( lang_get( 'field' ), 25 ) .
							str_pad( lang_get( 'change' ), 20 ). " \n";

			$t_message .= $t_email_separator1 . " \n";

			foreach ( $p_visible_bug_data['history'] as $t_raw_history_item ) {
				$t_localized_item = history_localize_item(	$t_raw_history_item['field'],
															$t_raw_history_item['type'],
															$t_raw_history_item['old_value'],
															$t_raw_history_item['new_value'] );

				$t_message .=	str_pad( date( $t_normal_date_format, $t_raw_history_item['date'] ), 16 ) .
								str_pad( $t_raw_history_item['username'], 15 ) .
								str_pad( $t_localized_item['note'], 25 ) .
								str_pad( $t_localized_item['change'], 20 ) . "\n";
			}
			$t_message .= $t_email_separator1 . " \n\n";
		}

		return $t_message;
	}

	# --------------------
	# if $p_visible_bug_data contains specified attribute the function
	# returns concatenated translated attribute name and original
	# attribute value. Else return empty string.
	function email_format_attribute( $p_visible_bug_data, $attribute_id ) {

		if ( array_key_exists( $attribute_id, $p_visible_bug_data ) ) {
			return str_pad( lang_get( $attribute_id ) . ': ', config_get( 'email_padding_length' ), ' ', STR_PAD_RIGHT ).$p_visible_bug_data[$attribute_id]."\n";
		}
		return '';
	}

	# --------------------
	# Build the bug raw data visible for specified user to be translated and sent by email to the user
	# (Filter the bug data according to user access level)
	# return array with bug data. See usage in email_format_bug_message(...)
	function email_build_visible_bug_data( $p_user_id, $p_bug_id, $p_message_id ) {
		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
		$t_user_access_level = user_get_access_level( $p_user_id, $t_project_id );
		$t_user_bugnote_order = user_pref_get_pref ( $p_user_id, 'bugnote_order' );
		$t_user_bugnote_limit = user_pref_get_pref ( $p_user_id, 'email_bugnote_limit' );

		$row = bug_get_extended_row( $p_bug_id );
		$t_bug_data = array();

		$t_bug_data['email_bug'] = $p_bug_id;

		if ( $p_message_id !== 'email_notification_title_for_action_bug_deleted' ) {
			$t_bug_data['email_bug_view_url'] = string_get_bug_view_url_with_fqdn( $p_bug_id );
		}

		if ( access_compare_level( $t_user_access_level, config_get( 'view_handler_threshold' ) ) ) {
			if ( 0 != $row['handler_id'] ) {
				$t_bug_data['email_handler'] = user_get_name( $row['handler_id'] );
			} else {
				$t_bug_data['email_handler'] = '';
			}
		}

		$t_bug_data['email_reporter'] = user_get_name( $row['reporter_id'] );
		$t_bug_data['email_project']  = project_get_field( $row['project_id'], 'name' );

		$t_bug_data['email_category'] = $row['category'];

		$t_bug_data['email_date_submitted'] = $row['date_submitted'];
		$t_bug_data['email_last_modified']   = $row['last_updated'];

		$t_bug_data['email_status'] = $row['status'];
		$t_bug_data['email_severity'] = $row['severity'];
		$t_bug_data['email_priority'] = $row['priority'];
		$t_bug_data['email_reproducibility'] = $row['reproducibility'];

		$t_bug_data['email_resolution'] = $row['resolution'];
		$t_bug_data['email_fixed_in_version'] = $row['fixed_in_version'];

		if ( DUPLICATE == $row['resolution'] ) {
			$t_bug_data['email_duplicate'] = $row['duplicate_id'];
		}

		$t_bug_data['email_summary'] = $row['summary'];
		$t_bug_data['email_description'] = $row['description'];

		$t_bug_data['set_category'] = '[' . $t_bug_data['email_project'] . '] ' . $row['category'];

		$t_bug_data['custom_fields'] = custom_field_get_linked_fields( $p_bug_id, $t_user_access_level );
		$t_bug_data['bugnotes'] = bugnote_get_all_visible_bugnotes( $p_bug_id, $t_user_access_level, $t_user_bugnote_order, $t_user_bugnote_limit );

		# put history data
		if ( ( ON == config_get( 'history_default_visible' ) ) &&  access_compare_level( $t_user_access_level, config_get( 'view_history_threshold' ) ) ) {
			$t_bug_data['history']  = history_get_raw_events_array( $p_bug_id );
		}

		# Sponsorship Information
		if ( ( config_get( 'enable_sponsorship' ) == ON ) && ( access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $p_bug_id, $p_user_id ) ) ) {
			$t_sponsorship_ids = sponsorship_get_all_ids( $p_bug_id );
			$t_bug_data['sponsorship_total'] = sponsorship_get_amount( $t_sponsorship_ids );

			if ( access_has_bug_level( config_get( 'view_sponsorship_details_threshold' ), $p_bug_id, $p_user_id ) ) {
				$t_bug_data['sponsorships'] = array();
				foreach ( $t_sponsorship_ids as $id ) {
					$t_bug_data['sponsorships'][] = sponsorship_get( $id );
				}
			}
		}

		# MASC RELATIONSHIP
		if ( ON == config_get( 'enable_relationship' ) ) {
			$t_bug_data['relations'] = relationship_get_summary_text( $p_bug_id );
		}

		return $t_bug_data;
	}
?>
