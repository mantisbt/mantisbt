<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: email_api.php,v 1.15 2002-09-06 07:13:18 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Email API
	###########################################################################

	# --------------------
	# check to see that the format is valid and that the mx record exists
	function email_is_valid( $p_email ) {
		global $g_validate_email, $g_check_mx_record;

		# if we don't validate then just accept
		if ( OFF == config_get( 'validate_email' ) ) {
			return true;
		}

		if ( empty( $p_email ) && ON == config_get( 'allow_blank_email' ) ) {
			return true;
		}

		# Use a regular expression to check to see if the email is in valid format
		#  x-xx.xxx@yyy.zzz.abc etc.
		if (eregi("^[_.0-9a-z-]+@([0-9a-z][-0-9a-z.]+)\.([a-z]{2,6}$)", $p_email, $check)) {
			# see if we're limited to one domain
			$t_limit_email_domain = config_get( 'limit_email_domain' );
			if ( $t_limit_email_domain ) {
				if ( 0 != strcasecmp( $t_limit_email_domain, $check[1].'.'.$check[2] ) ) {
					return false;
				}
			}

			# passed format check. see if we should check the mx records
			if ( ON == $g_check_mx_record ) {	# Check for valid mx records
				if (getmxrr($check[1].'.'.$check[2], $temp)) {
					return true;
				} else {
					$host = substr( strstr( $check[0], '@' ), 1 ).'.';

					# for no mx record... try dns check
					if (checkdnsrr ( $host, 'ANY' ))
						return true;
				}
			} else {
				# Email format was valid but did't check for valid mx records
				return true;
			}
		}
		# Everything failed.  Bad email.
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
	# get_notify_flag
	# Get the value associated with the specific action and flag.
	# For example, you can get the value associated with notifying "admin"
	# on action "new", i.e. notify administrators on new bugs which can be
	# ON or OFF.
	function get_notify_flag( $action, $flag ) {
		global	$g_notify_flags, $g_default_notify_flags;

		if ( isset ( $g_notify_flags[$action][$flag] ) ) {
			return $g_notify_flags[$action][$flag];
		} elseif ( isset ( $g_default_notify_flags[$flag] ) ) {
			return $g_default_notify_flags[$flag];
		}

		return OFF;
	}
	# --------------------
	# build the bcc list

	# UGLY function: here's how it works:
	# We store all the email addresses in an array.  First we grab the reporter.
	# If the handler is assigned then we add that.

	# The next part is only for NEW bugs.
	# We add all ADMINISTRATORs then add DEVELOPERs.
	# Lastly, we do a bit of post processing and return the bcc string.
	function build_bcc_list( $p_bug_id, $p_notify_type ) {
		global $g_mantis_bug_table, $g_mantis_user_table, $g_mantis_bugnote_table,
				$g_mantis_project_table, $g_mantis_user_pref_table,
				$g_project_cookie_val,
				$g_mantis_project_user_list_table,
				$g_use_bcc, $g_use_phpMailer,
				$g_mantis_bug_monitor_table;

		$c_bug_id = (integer)$p_bug_id;

		# setup the array of email entries
		$send_arr = array();

		# Get Reporter Email
		if ( ON == get_notify_flag( $p_notify_type, 'reporter' )) {
			$v_reporter_id = get_bug_field( $p_bug_id, 'reporter_id' );
			$t_pref_field = 'email_on_' . $p_notify_type;
			if ( db_field_exists( $t_pref_field, $g_mantis_user_pref_table ) ) {
				$t_notify_reporter = user_get_pref( $v_reporter_id, $t_pref_field );
				if ( ON == $t_notify_reporter ) {
					$send_arr[] = user_get_email( $v_reporter_id );
				}
			} else {
				$send_arr[] = user_get_email( $v_reporter_id );
			}
		}

		# Get Handler Email
		if ( ON == get_notify_flag( $p_notify_type, 'handler' )) {
			$v_handler_id = get_bug_field( $p_bug_id, 'handler_id' );
			if ( $v_handler_id > 0 ) {
				$t_pref_field = 'email_on_' . $p_notify_type;
				if ( db_field_exists( $t_pref_field, $g_mantis_user_pref_table ) ) {
					$t_notify_handler = user_get_pref( $v_handler_id, $t_pref_field );
					if ( ON == $t_notify_handler ) {
						$send_arr[] = user_get_email( $v_handler_id );
					}
				} else {
					$send_arr[] = user_get_email( $v_handler_id );
				}
			}
		}

		# Check if we want to broadcast to all developers on a NEW bug
		$t_project_id = get_bug_field( $p_bug_id, 'project_id' );
		$t_project_view_state = project_get_field( $g_project_cookie_val, 'view_state' );

		#@@@@@@@
		$temp_arr = array();
		# grab the administrators
		$query = "SELECT id, email
				FROM $g_mantis_user_table
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );
		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			$temp_arr[$v_email] = array( $v_email, $v_id );
		}

		foreach ( $temp_arr as $key => $val ) {
			$v_id = $val[1];
			$v_email = $val[0];

			# add all administrators if notification flag enabled
			$t_access_level = user_get_field( $v_id, 'access_level' );

			if ( ($t_access_level == ADMINISTRATOR) && (ON == get_notify_flag( $p_notify_type, 'admin' ))) {
				$send_arr[] = $v_email;
				continue;
			}

			# Get authors of bug notes
			if ( ON == get_notify_flag( $p_notify_type, 'bugnotes' )) {
				$query = "SELECT 1 ".
						"FROM $g_mantis_bugnote_table ".
						"WHERE (bug_id = $p_bug_id) AND (reporter_id = $v_id) ".
						"LIMIT 1";
				$result = db_query( $query );
				if ( db_num_rows( $result ) > 0 ) {
					$send_arr[] = $v_email;
				}
			}

			# see if users belong
			$t_project_view_state = project_get_field( $g_project_cookie_val, 'view_state' );
			if ( PUBLIC == $t_project_view_state ) {
				$query = "SELECT l.access_level
						FROM	$g_mantis_project_user_list_table l,
								$g_mantis_project_table p
						WHERE	l.project_id='$t_project_id' AND
								p.id=l.project_id AND
								l.user_id='$v_id'
						LIMIT 1";
				$result = db_query( $query );
				$count = db_num_rows( $result );
				if ( $count > 0 ){
					$t_access_level = db_result( $result );
				}

				if ( $t_access_level >= get_notify_flag($p_notify_type, 'threshold')) {
					$send_arr[] = $v_email;
				}
			} else {
				$query = "SELECT 1
						FROM	$g_mantis_project_user_list_table l,
								$g_mantis_project_table p
						WHERE	l.project_id='$t_project_id' AND
								p.id=l.project_id AND
								l.user_id='$v_id' AND
								l.access_level>='" . get_notify_flag($p_notify_type, 'threshold') ."'
						LIMIT 1";
				$result = db_query( $query );
				if ( db_num_rows( $result ) > 0 ) {
					$send_arr[] = $v_email;
					continue;
				}
			}
		}

		if ( ON == get_notify_flag( $p_notify_type, 'monitor' )) {
			# grab all users MONITORING bug
			$query = "SELECT DISTINCT m.user_id, u.email
					FROM $g_mantis_bug_monitor_table m,
						$g_mantis_user_table u
					WHERE m.bug_id=$c_bug_id AND
							m.user_id=u.id";
			$result = db_query( $query );
			$monitor_user_count = db_num_rows( $result );
			for ($i=0;$i<$monitor_user_count;$i++) {
				$row = db_fetch_array( $result );

				$t_pref_field = 'email_on_' . $p_notify_type;
				if ( db_field_exists( $t_pref_field, $g_mantis_user_pref_table ) ) {
					# if the user's notification is on then add to the list
					$t_notify = user_get_pref( $row['user_id'], $t_pref_field );
					if ( ON == $t_notify ) {
						$send_arr[] = $row['email'];
					}
				}
			} # end MONITORING
		}

		$t_bcc = ( $g_use_bcc && !$g_use_phpMailer ) ? 'Bcc: ' : '';
		## win-bcc-bug

		$send_arr = array_unique( $send_arr );
		foreach ( $send_arr as $send_val ) {
			if (strlen($send_val) != 0) {
				$t_bcc .= $send_val.', ';
			}
		}

		# chop off the last comma and add a \n
		if ( strlen( $t_bcc ) > 5 ) {
			return substr( $t_bcc, 0, strlen( $t_bcc )-2).(($g_use_bcc) ? "\n" : '');  ## win-bcc-bug
		} else {
			return '';
		}
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

		$t_headers = '';
		email_send( $v_email, lang_get( 'new_account_subject' ), $t_message, $t_headers );
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
					lang_get( 'account_name_msg' ) . ': '.$v_username."\n".
					lang_get( 'news_password_msg' ) . ': '.$p_password."\n\n".
					$g_path."\n\n";

		email_send( $v_email, 'New Password', $t_message );
	}
	# --------------------
	# send notices when a new bug is added
	function email_new_bug( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'new' );
		email_bug_info( $p_bug_id, lang_get( 'new_bug_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a new bugnote
	function email_bugnote_add( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'bugnote' );
		email_bug_info( $p_bug_id, lang_get( 'email_bugnote_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is RESOLVED
	function email_resolved( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'resolved' );
		email_bug_info( $p_bug_id, lang_get( 'email_resolved_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is CLOSED
	function email_close( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'closed' );
		email_bug_info( $p_bug_id, lang_get( 'email_close_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is set to FEEDBACK
	function email_feedback( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'feedback' );
		email_bug_info( $p_bug_id, lang_get( 'email_feedback_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is REOPENED
	function email_reopen( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'reopened' );
		email_bug_info( $p_bug_id, lang_get( 'email_reopen_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is ASSIGNED
	function email_assign( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'assigned' );
		email_bug_info( $p_bug_id, lang_get( 'email_assigned_msg' ), $t_bcc );
	}
	# --------------------
	# send notices when a bug is DELETED
	function email_bug_deleted( $p_bug_id ) {
		$t_bcc = build_bcc_list( $p_bug_id, 'deleted' );
		email_bug_info( $p_bug_id, lang_get( 'email_bug_deleted_msg' ), $t_bcc );
	}
	# --------------------
	# Build the bug info part of the message
	function email_build_bug_message( $p_bug_id, $p_message ) {
		global 	$g_mantis_bug_table, $g_mantis_bug_text_table,
				$g_mantis_project_table,
				$g_complete_date_format, $g_show_view,
				$g_bugnote_order, $g_path,
				$g_email_separator1, $g_email_padding_length;

		$c_bug_id = db_prepare_int( $p_bug_id );

		$query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted,
				UNIX_TIMESTAMP(last_updated) as last_updated
				FROM $g_mantis_bug_table
				WHERE id='$c_bug_id'
				ORDER BY date_submitted $g_bugnote_order";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$query = "SELECT *
				FROM $g_mantis_bug_text_table
				WHERE id='$v_bug_text_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v2' );

		$query = "SELECT name
				FROM $g_mantis_project_table
				WHERE id='$v_project_id'";
		$result = db_query( $query );
		$t_project_name = db_result( $result, 0, 0 );

		$t_reporter_name = user_get_name( $v_reporter_id );

		if ( 0 != $v_handler_id ) {
			$t_handler_name  = user_get_name( $v_handler_id );
		} else {
			$t_handler_name  = '';
		}

		$v2_description   = string_email( $v2_description );
		$v_summary        = string_email( $v_summary );
		$v_date_submitted = date( $g_complete_date_format, $v_date_submitted );
		$v_last_updated   = date( $g_complete_date_format, $v_last_updated );

		$t_sev_str = get_enum_element( 'severity', $v_severity );
		$t_pri_str = get_enum_element( 'priority', $v_priority );
		$t_sta_str = get_enum_element( 'status', $v_status );
		$t_rep_str = get_enum_element( 'reproducibility', $v_reproducibility );
		$t_message = $g_email_separator1."\n";
		if ( $p_message != lang_get( 'email_bug_deleted_msg' ) ) {
			$t_message .= $g_path;
			if ( ADVANCED_ONLY == $g_show_view || ( BOTH == $g_show_view && ON == current_user_get_pref( 'advanced_view' ) ) ) {
				$t_message .= 'view_bug_advanced_page.php';
			} else {
				$t_message .= 'view_bug_page.php';
			}
			$t_message .= '?f_id='.$p_bug_id."\n";
			$t_message .= $g_email_separator1."\n";
		}
		$t_message .= str_pad( lang_get( 'email_reporter' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_reporter_name."\n";
		$t_message .= str_pad( lang_get( 'email_handler' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_handler_name."\n";
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pad( lang_get( 'email_project' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_project_name."\n";
		$t_message .= str_pad( lang_get( 'email_bug' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_id."\n";
		$t_message .= str_pad( lang_get( 'email_category')  . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_category."\n";
		$t_message .= str_pad( lang_get( 'email_reproducibility' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_rep_str."\n";
		$t_message .= str_pad( lang_get( 'email_severity' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_sev_str."\n";
		$t_message .= str_pad( lang_get( 'email_priority' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_pri_str."\n";
		$t_message .= str_pad( lang_get( 'email_status' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_sta_str."\n";
		if ( RESOLVED == $v_status ) {
			$t_res_str = get_enum_element( 'resolution', $v_resolution );
			$t_message .= str_pad( lang_get( 'email_resolution' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_res_str."\n";
			if ( DUPLICATE == $v_resolution ) {
				$t_message .= str_pad( lang_get( 'email_duplicate' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_duplicate_id."\n";
			}
		}
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pad( lang_get( 'email_date_submitted' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_date_submitted."\n";
		$t_message .= str_pad( lang_get( 'email_last_modified' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_last_updated."\n";
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pad( lang_get( 'email_summary' ) . ': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_summary."\n";
		$t_message .= lang_get( 'email_description' ) . ": \n".wordwrap( $v2_description )."\n";
		$t_message .= $g_email_separator1."\n\n";

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

		$t_state = PUBLIC;

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
			$t_note = string_email( $t_note );
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
		$t_message .=	str_pad( lang_get( 'date_modified' ), 20 ) .
						str_pad( lang_get( 'username' ), 20 ) .
						str_pad( lang_get( 'field' ), 20 ) .
						str_pad( lang_get( 'change' ), 20 ). "\n";
		$t_message .= config_get( 'email_separator1' ) . "\n";
		for ( $i = 0; $i < count($history); $i++ ) {
			$t_message .=	str_pad( $history[$i]['date'], 20 ) .
							str_pad( $history[$i]['username'], 20 ) .
							str_pad( $history[$i]['note'], 20 ) .
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
		$p_subject = email_build_subject( $p_bug_id );

		# build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id, $p_message );
		$t_message .= email_build_bugnote_message( $p_bug_id );
		$t_message .= email_build_history_message( $p_bug_id );

		# send mail

		## win-bcc-bug
		if ( OFF == $g_use_bcc ) {
			## list of receivers
			$to = $g_to_email.(($p_headers && $g_to_email) ? ', ' : '').$p_headers;
			# echo '<br />email_bug_info::Sending email to :'.$to;
			$res1 = email_send( $to, $p_subject, $t_message, '' );
		} else {
			# Send Email
			# echo '<br />email_bug_info::Sending email to : '.$g_to_email;
			$res1 = email_send( $g_to_email, $p_subject, $t_message, $p_headers );
		}
	}
	# --------------------
	# this function sends the actual email
	function email_send( $p_recipient, $p_subject, $p_message, $p_header='' ) {
		global $g_from_email, $g_enable_email_notification,
				$g_return_path_email, $g_use_x_priority,
				$g_use_phpMailer, $g_phpMailer_method, $g_smtp_host;

		# short-circuit if no emails should be sent
		if ( OFF ==$g_enable_email_notification ) {
			return;
		}

		$t_recipient = trim( $p_recipient );
		$t_subject   = trim( $p_subject );
		$t_message   = trim( $p_message );

		# short-circuit if no recipient is defined
		if (!$p_recipient) {
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

		if ( ON == $g_use_phpMailer )  {
			# Visit http://phpmailer.sourceforge.net
			# if you have problems with phpMailer

			include_once('class.phpmailer.php');
			$mail = new phpmailer;

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
			$mail->Priority = 0;               # Urgent = 1, Not Urgent = 5, Disable = 0
			$mail->Host     = $g_smtp_host;
			$mail->From     = $g_from_email;
			$mail->FromName = '';

			# add to the Recipient list
			$t_recipient_list = split(',', $t_recipient);
			while ( list( , $t_recipient ) = each( $t_recipient_list ) ) {
				if ( !empty( $t_recipient ) ) {
					$mail->AddAddress( $t_recipient, '' );
				}
			}
			# add to the BCC list
			$t_bcc_list = split(',', $p_header);
			while(list(, $t_bcc) = each($t_bcc_list)) {
				if ( !empty( $t_bcc ) ) {
					$mail->AddBCC($t_bcc, '');
				}
			}

			$mail->Subject = $t_subject;
			$mail->Body    = make_lf_crlf( "\n".$t_message );

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
				$t_headers .= "X-Priority: 0\n";    # Urgent = 1, Not Urgent = 5, Disable = 0
			}
			$t_headers .= "Return-Path: <$g_return_path_email>\n";          # return email if error
			$t_headers .= 'Content-Type: text/plain; charset=' . lang_get( 'charset' ) . "\n";

			$t_headers .= $p_header;

			$t_recipient = make_lf_crlf( $t_recipient );
			$t_subject = make_lf_crlf( $t_subject );
			$t_message = make_lf_crlf( $t_message );
			$t_headers = make_lf_crlf( $t_headers );

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
	# helper function
	function get_bug_summary( $p_bug_id ) {
		global $g_mantis_bug_table;

		$c_bug_id = (integer)$p_bug_id;

		$query = "SELECT summary
				FROM $g_mantis_bug_table
				WHERE id='$c_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# formats the subject correctly
	# we include the project name, bug id, and summary.
	function email_build_subject( $p_bug_id ) {
		global $g_project_cookie_val;

		# grab the project name
		$p_project_name = project_get_field( $g_project_cookie_val, 'name' );

		# grab the subject (summary)
		$p_subject = string_email( get_bug_summary( $p_bug_id ) );

		# padd the bug id with zeros
		$p_bug_id = str_pad( $p_bug_id, '0', 7, STR_PAD_LEFT );

		return '['.$p_project_name.' '.$p_bug_id.']: '.$p_subject;
	}
	# --------------------
	# clean up LF to CRLF
	function make_lf_crlf( $p_string ) {
		global $g_mail_send_crlf;

		if ( ON == $g_mail_send_crlf ) {
			$p_string = str_replace( "\n", "\r\n", $p_string );
			return str_replace( "\r\r\n", "\r\n", $p_string );
		} else {
			return $p_string;
		}
	}
	# --------------------
?>