<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: email_api.php,v 1.60 2003-07-27 03:17:13 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'custom_field_api.php' );
	require_once( $t_core_dir . 'string_api.php' );
	require_once( $t_core_dir . 'history_api.php' );

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
					if (checkdnsrr ( $host, 'ANY' ))
						return true;
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
		if ( ! email_is_valid( $p_email ) ) {
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
		global	$g_notify_flags, $g_default_notify_flags;

		if ( isset ( $g_notify_flags[$action][$flag] ) ) {
			return $g_notify_flags[$action][$flag];
		} elseif ( isset ( $g_default_notify_flags[$flag] ) ) {
			return $g_default_notify_flags[$flag];
		}

		return OFF;
	}

	function email_collect_recipients( $p_bug_id, $p_notify_type ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_recipients = array();

		# Reporter
		if ( ON == email_notify_flag( $p_notify_type, 'reporter' ) ) {
			$t_reporter_id = bug_get_field( $p_bug_id, 'reporter_id' );
			$t_recipients[$t_reporter_id] = true;
		}

		# Handler
		if ( ON == email_notify_flag( $p_notify_type, 'handler' )) {
			$t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );
			$t_recipients[$t_handler_id] = true;
		}

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		# monitor
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
			}
		}

		# bugnotes
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
			}
		}

		# threshold
		$t_threshold_min = email_notify_flag( $p_notify_type, 'threshold_min' );
		$t_threshold_max = email_notify_flag( $p_notify_type, 'threshold_max' );
		$t_threshold_users = project_get_all_user_rows( $t_project_id, $t_threshold_min );
		foreach( $t_threshold_users as $t_user ) {
			if ( $t_user['access_level'] <= $t_threshold_max ) {
				$t_recipients[$t_user['id']] = true;
			}
		}

		$t_pref_field = 'email_on_' . $p_notify_type;
		$t_user_pref_table = config_get( 'mantis_user_pref_table' );
		if ( ! db_field_exists( $t_pref_field, $t_user_pref_table ) ) {
			$t_pref_field = false;
		}

		# @@@ we could optimize by modifiying user_cache() to take an array
		#  of user ids so we could pull them all in.  We'll see if it's necessary

		# Check whether users should receive the emails
		foreach ( $t_recipients as $t_id => $t_ignore ) {
			# Possibly eliminate the current user
			if ( auth_get_current_user_id() == $t_id &&
				 OFF == config_get( 'email_receive_own' ) ) {
				unset( $t_recipients[$t_id] );
				continue;
			}

			# Eliminate users who don't exist anymore or who are disabled
			if ( ! user_exists( $t_id ) ||
				 ! user_is_enabled( $t_id ) ) {
				unset( $t_recipients[$t_id] );
				continue;
			}

			# Exclude users who have this notification type turned off
			if ( $t_pref_field ) {
				$t_notify = user_pref_get_pref( $t_id, $t_pref_field );
				if ( OFF == $t_notify ) {
					unset( $t_recipients[$t_id] );
					continue;
				}
			}

			# Finally, let's get their emails, if they've set one
			$t_email = user_get_email( $t_id );
			if ( is_blank( $t_email ) ) {
				unset( $t_recipients[$t_id] );
			} else {
				# @@@ we could check the emails for validity again but I think
				#   it would be too slow
				$t_recipients[$t_id] = $t_email;
			}
		}

		return $t_recipients;
	}

	# --------------------
	# Build the bcc list
	function email_build_bcc_list( $p_bug_id, $p_notify_type ) {
		$t_recipients = email_collect_recipients( $p_bug_id, $p_notify_type );

		$t_use_bcc			= config_get( 'use_bcc' );
		$t_use_phpMailer	= config_get( 'use_phpMailer' );
		
		## win-bcc-bug
		$t_bcc = ( $t_use_bcc && !$t_use_phpMailer ) ? 'Bcc: ' : '';

		$t_emails = array_values( $t_recipients );

		$t_bcc .= implode( ', ', $t_emails );

		## win-bcc-bug
		if ( $t_use_bcc ) {
			$t_bcc .= "\n";
		}

		return $t_bcc;
	}

	# --------------------
	# Send password to user
	function email_signup( $p_user_id, $p_password ) {
		global $g_mantis_user_table, $g_path;

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$c_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		# Build Welcome Message
		$t_message = lang_get( 'new_account_greeting' ).
						lang_get( 'new_account_url' ) . $g_path . "\n".
						lang_get( 'new_account_username' ) . $v_username . "\n".
						lang_get( 'new_account_password' ) . $p_password . "\n\n".
						lang_get( 'new_account_message' ) .
						lang_get( 'new_account_do_not_reply' );

		email_send( $v_email, lang_get( 'new_account_subject' ), $t_message );
	}
	# --------------------
	# Send new password when user forgets
	function email_reset( $p_user_id, $p_password ) {
		global 	$g_mantis_user_table, $g_path;

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$c_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		# Build Welcome Message
		$t_message = lang_get( 'reset_request_msg' ) . "\n\n".
					lang_get( 'new_account_username' ) . $v_username."\n".
					lang_get( 'new_account_password' ) . $p_password."\n\n".
					$g_path."\n\n";

		email_send( $v_email, lang_get( 'news_password_msg' ), $t_message );
	}
	# --------------------
	# send notices when a new bug is added
	function email_new_bug( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'new' );
		email_bug_info( $p_bug_id, lang_get( 'new_bug_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a new bugnote
	function email_bugnote_add( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'bugnote' );
		email_bug_info( $p_bug_id, lang_get( 'email_bugnote_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is RESOLVED
	function email_resolved( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'resolved' );
		email_bug_info( $p_bug_id, lang_get( 'email_resolved_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is CLOSED
	function email_close( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'closed' );
		email_bug_info( $p_bug_id, lang_get( 'email_close_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is set to FEEDBACK
	function email_feedback( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'feedback' );
		email_bug_info( $p_bug_id, lang_get( 'email_feedback_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is REOPENED
	function email_reopen( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'reopened' );
		email_bug_info( $p_bug_id, lang_get( 'email_reopen_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is ASSIGNED
	function email_assign( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'assigned' );
		email_bug_info( $p_bug_id, lang_get( 'email_assigned_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is DELETED
	function email_bug_deleted( $p_bug_id ) {
		$t_bcc = email_build_bcc_list( $p_bug_id, 'deleted' );
		email_bug_info( $p_bug_id, lang_get( 'email_bug_deleted_msg' ), $t_bcc );
	}
	# --------------------
	# Build the bug info part of the message
	function email_build_bug_message( $p_bug_id, $p_message, &$p_category ) {
		global 	$g_complete_date_format, $g_show_view,
				$g_bugnote_order,
				$g_email_separator1, $g_email_padding_length;

		$row = bug_get_extended_row( $p_bug_id );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$t_project_name = project_get_field( $v_project_id, 'name' );
		$t_reporter_name = user_get_name( $v_reporter_id );

		if ( 0 != $v_handler_id ) {
			$t_handler_name  = user_get_name( $v_handler_id );
		} else {
			$t_handler_name  = '';
		}

		$v_date_submitted = date( $g_complete_date_format, $v_date_submitted );
		$v_last_updated   = date( $g_complete_date_format, $v_last_updated );

		$t_sev_str = get_enum_element( 'severity', $v_severity );
		$t_pri_str = get_enum_element( 'priority', $v_priority );
		$t_sta_str = get_enum_element( 'status', $v_status );
		$t_rep_str = get_enum_element( 'reproducibility', $v_reproducibility );
		$t_message = $g_email_separator1."\n";
		if ( $p_message != lang_get( 'email_bug_deleted_msg' ) ) {
			$t_message .= string_get_bug_view_url_with_fqdn( $p_bug_id ) . "\n";
			$t_message .= $g_email_separator1."\n";
		}
		$t_message .= str_pad( lang_get( 'email_reporter' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_reporter_name."\n";
		$t_message .= str_pad( lang_get( 'email_handler' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_handler_name."\n";
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pad( lang_get( 'email_project' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_project_name."\n";
		$t_message .= str_pad( lang_get( 'email_bug' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$v_id."\n";
		$t_message .= str_pad( lang_get( 'email_category')  . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$v_category."\n";
		$t_message .= str_pad( lang_get( 'email_reproducibility' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_rep_str."\n";
		$t_message .= str_pad( lang_get( 'email_severity' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_sev_str."\n";
		$t_message .= str_pad( lang_get( 'email_priority' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_pri_str."\n";
		$t_message .= str_pad( lang_get( 'email_status' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_sta_str."\n";

		# @@@ Add support for access levels, possible only send the ones that are available for access level EVERYBODY
		$t_related_custom_field_ids = custom_field_get_linked_ids( $v_project_id );
		foreach( $t_related_custom_field_ids as $t_id ) {
			$t_def = custom_field_get_definition( $t_id );

			$t_message .= str_pad( $t_def['name'] . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT );

			$t_custom_field_value = custom_field_get_value( $t_id, $p_bug_id );
			if( CUSTOM_FIELD_TYPE_EMAIL == $t_def['type'] ) {
				$t_message .= "mailto:$t_custom_field_value";
			} else {
				$t_message .= $t_custom_field_value;
			}
			$t_message .= "\n";
		}       // foreach

		if ( RESOLVED == $v_status ) {
			$t_res_str = get_enum_element( 'resolution', $v_resolution );
			$t_message .= str_pad( lang_get( 'email_resolution' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$t_res_str."\n";
			if ( DUPLICATE == $v_resolution ) {
				$t_message .= str_pad( lang_get( 'email_duplicate' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$v_duplicate_id."\n";
			}
		}
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pad( lang_get( 'email_date_submitted' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$v_date_submitted."\n";
		$t_message .= str_pad( lang_get( 'email_last_modified' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$v_last_updated."\n";
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pad( lang_get( 'email_summary' ) . ': ', $g_email_padding_length, ' ', STR_PAD_RIGHT ).$v_summary."\n";
		$t_message .= lang_get( 'email_description' ) . ": \n".wordwrap( $v_description )."\n";
		$t_message .= $g_email_separator1."\n\n";

		$p_category = '';
		if ( OFF != config_get( 'email_set_category' ) ) {
			$p_category = '[' . $t_project_name . '] ' . $v_category;
		}

		return $t_message;
	}
	# --------------------
	# Build the bugnotes part of the message
	function email_build_bugnote_message( $p_bug_id ) {
		global 	$g_mantis_bugnote_table, $g_mantis_bugnote_text_table,
				$g_complete_date_format,
				$g_bugnote_order, $g_email_separator2;

		$c_bug_id = (integer)$p_bug_id;

		$t_message = '';

		$t_state = VS_PUBLIC;

		$query = "SELECT *, UNIX_TIMESTAMP(last_modified) as last_modified
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$c_bug_id' AND view_state='$t_state'
				ORDER BY date_submitted $g_bugnote_order";
		$result = db_query( $query );
		$bugnote_count = db_num_rows( $result );

		# BUILT MESSAGE
		for ( $i=0; $i<$bugnote_count; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 't' );

			$query = "SELECT note
					FROM $g_mantis_bugnote_text_table
					WHERE id='$t_bugnote_text_id'";
			$result2 = db_query( $query );

			$t_username = user_get_name( $t_reporter_id );

			$t_note = db_result( $result2, 0, 0 );
			$t_last_modified = date( $g_complete_date_format, $t_last_modified );
			$t_string = ' '.$t_username.' - '.$t_last_modified.' ';
			$t_message = $t_message.$g_email_separator2."\n";
			$t_message = $t_message.$t_string."\n";
			$t_message = $t_message.$g_email_separator2."\n";
			$t_message = $t_message.wordwrap( $t_note )."\n\n";
		}
		return $t_message;
	}
	# --------------------
	# Builds the bug history portion of the bug e-mail
	function email_build_history_message( $p_bug_id ) {
		$history = history_get_events_array( $p_bug_id );
		$t_message = lang_get( 'bug_history' ) . "\n";
		$t_message .=	str_pad( lang_get( 'date_modified' ), 15 ) .
						str_pad( lang_get( 'username' ), 15 ) .
						str_pad( lang_get( 'field' ), 25 ) .
						str_pad( lang_get( 'change' ), 20 ). "\n";
		$t_message .= config_get( 'email_separator1' ) . "\n";
		for ( $i = 0; $i < count($history); $i++ ) {
			$t_message .=	str_pad( $history[$i]['date'], 15 ) .
							str_pad( $history[$i]['username'], 15 ) .
							str_pad( $history[$i]['note'], 25 ) .
							str_pad( $history[$i]['change'], 20 ). "\n";
		}

		$t_message .= config_get( 'email_separator1' ) . "\n\n";
		return ( $t_message );
	}
	# --------------------
	# Send bug info to reporter and handler
	function email_bug_info( $p_bug_id, $p_message, $p_headers='' ) {
		global $g_to_email, $g_use_bcc;

		# build subject
		$t_subject = email_build_subject( $p_bug_id );

		# build message
		$t_category = '';
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id, $p_message, $t_category );
		$t_message .= email_build_bugnote_message( $p_bug_id );
		$t_message .= email_build_history_message( $p_bug_id );

		# send mail

		## win-bcc-bug
		if ( OFF == $g_use_bcc ) {
			## list of receivers
			$to = $g_to_email.( ( is_blank( $p_headers ) || is_blank( $g_to_email ) ) ? '' : ', ').$p_headers;
			# echo '<br />email_bug_info::Sending email to :'.$to;
			$res1 = email_send( $to, $t_subject, $t_message, '', $t_category );
		} else {
			# Send Email
			# echo '<br />email_bug_info::Sending email to : '.$g_to_email;
			$res1 = email_send( $g_to_email, $t_subject, $t_message, $p_headers, $t_category );
		}
	}
	# --------------------
	# this function sends the actual email
	function email_send( $p_recipient, $p_subject, $p_message, $p_header='', $p_category='' ) {
		global $g_from_email, $g_enable_email_notification,
				$g_return_path_email, $g_use_x_priority,
				$g_use_phpMailer, $g_phpMailer_method, $g_smtp_host,
				$g_smtp_username, $g_smtp_password, $g_mail_priority;

		# short-circuit if no emails should be sent
		if ( OFF ==$g_enable_email_notification ) {
			return;
		}

		$t_recipient = trim( $p_recipient );
		$t_subject   = string_email( trim( $p_subject ) );
		$t_message   = string_email_links( trim( $p_message ) );

		# short-circuit if no recipient is defined
		if ( is_blank( $p_recipient ) && ( OFF == config_get('use_bcc') ) ) {
			return;
		}

		# for debugging only
		#echo $t_recipient.'<br />'.$t_subject.'<br />'.$t_message.'<br />'.$t_headers;
		#exit;
		#echo '<br />xxxRecipient ='.$t_recipient.'<br />';
		#echo 'Headers ='.nl2br($t_headers).'<br />';
		#echo $t_subject.'<br />';
		#echo nl2br($t_message).'<br />';
		#exit;

		$t_debug_email = config_get('debug_email');

		if ( ON == $g_use_phpMailer )  {
			# Visit http://phpmailer.sourceforge.net
			# if you have problems with phpMailer

			$t_phpMailer_path = config_get( 'phpMailer_path' );

			include_once( $t_phpMailer_path . 'class.phpmailer.php');
			$mail = new phpmailer;
			$mail->PluginDir = $t_phpMailer_path;

			# Select the method to send mail
			switch ( $g_phpMailer_method ) {
				case 0: $mail->IsMail();
						break;
				case 1: $mail->IsSendmail();
						break;
				case 2: $mail->IsSMTP();
						break;
			}
			$mail->IsHTML(false);              # set email format to plain text
			$mail->WordWrap = 80;              # set word wrap to 50 characters
			$mail->Priority = config_get( 'mail_priority' );               # Urgent = 1, Not Urgent = 5, Disable = 0
			$mail->CharSet = lang_get( 'charset' );
			$mail->Host     = $g_smtp_host;
			$mail->From     = $g_from_email;
			$mail->FromName = '';
			if ( ! is_blank( $g_smtp_username ) ) {     # Use SMTP Authentication
				$mail->SMTPAuth = true;
				$mail->Username = $g_smtp_username;
				$mail->Password = $g_smtp_password;
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

			if ( OFF !== $t_debug_email )
			{
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
			
			if ( EMAIL_CATEGORY_PROJECT_CATEGORY == config_get( 'email_set_category' ) ) {
				$mail->AddCustomHeader( "Keywords: $p_category" );
			}

			if( !$mail->Send() ) {
				PRINT "PROBLEMS SENDING MAIL TO: $t_recipient<br />";
				PRINT 'Mailer Error: '.$mail->ErrorInfo.'<br />';
				exit;
			}
		} else {
			# Visit http://www.php.net/manual/function.mail.php
			# if you have problems with mailing

			$t_headers = "From: $g_from_email\n";
			#$t_headers .= "Reply-To: $p_reply_to_email\n";

			$t_headers .= "X-Sender: <$g_from_email>\n";
			$t_headers .= 'X-Mailer: PHP/'.phpversion()."\n";
			if ( ON == $g_use_x_priority ) {
				$t_headers .= "X-Priority: $g_mail_priority\n";    # Urgent = 1, Not Urgent = 5, Disable = 0
			}
			$t_headers .= 'Content-Type: text/plain; charset=' . lang_get( 'charset' ) . "\n";

			if ( EMAIL_CATEGORY_PROJECT_CATEGORY == config_get( 'email_set_category' ) ) {
				$t_headers .= "Keywords: $p_category\n";
			}

			if ( OFF === $t_debug_email ) {
				$t_headers .= $p_header;
			} else {
				$t_message = "To: $t_recipient\n$p_header\n\n$t_message";
				$t_recipient = $t_debug_email;
			}

			$t_recipient = make_lf_crlf( $t_recipient );
			$t_subject = make_lf_crlf( $t_subject );
			$t_message = make_lf_crlf( $t_message );
			$t_headers = make_lf_crlf( $t_headers );

			# set the SMTP host... only used on window though
			ini_set( 'SMTP', config_get( 'smtp_host', 'localhost' ) );

			$result = mail( $t_recipient, $t_subject, $t_message, $t_headers );
			if ( TRUE != $result ) {
				PRINT "PROBLEMS SENDING MAIL TO: $t_recipient<br />";
				PRINT htmlspecialchars($t_recipient).'<br />';
				PRINT htmlspecialchars($t_subject).'<br />';
				PRINT nl2br(htmlspecialchars($t_headers)).'<br />';
				PRINT nl2br(htmlspecialchars($t_message)).'<br />';
				exit;
			}
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
		if ( ! is_array( $p_recipients ) ) {
			$p_recipients = array( $p_recipients );
		}

		$t_subject = email_build_subject( $p_bug_id );
		$t_sender = current_user_get_field( 'username' ) . ' <' .
					current_user_get_field( 'email' ) . '>' ;
		$t_date = date( config_get( 'normal_date_format' ) );
		$t_header = "\n" . lang_get( 'on' ) . " $t_date, $t_sender " .
					lang_get( 'sent_you_this_reminder_about' ) . ":\n\n" .
					config_get( 'path' );

		$result = array();
		foreach ( $p_recipients as $t_recipient ) {
			$t_email = user_get_email( $t_recipient );
			$result[] = user_get_name( $t_recipient );
			$t_contents = $t_header .
							string_get_bug_view_url_with_fqdn( $p_bug_id, $t_recipient ) .
							"\n\n$p_message";
							
			email_send( $t_email, $t_subject, $t_contents );
		}
		return $result;
	}
?>
