<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Email API
	# -------------------------------------------------
	# $Revision: 1.62 $
	# $Author: jctrosset $
	# $Date: 2002-06-24 10:06:18 $
	#
	# $Id: core_email_API.php,v 1.62 2002-06-24 10:06:18 jctrosset Exp $
	###########################################################################
	# --------------------
	# check to see that the format is valid and that the mx record exists
	function is_valid_email( $p_email ) {
		global $g_validate_email, $g_check_mx_record;

		# if we don't validate then just accept
		if ( OFF == $g_validate_email ) {
			return true;
		}

		# Use a regular expression to check to see if the email is in valid format
		#  x-xx.xxx@yyy.zzz.abc etc.
		if (eregi("^[_.0-9a-z-]+@([0-9a-z][-0-9a-z.]+).([a-z]{2,6}$)", $p_email, $check)) {
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
	# takes an array and an element that might be in the array
	# return true if a duplicate entry exists
	# return false if entry does not already exist
	function check_duplicate( $p_arr, $p_str ) {
		$arr_count = count( $p_arr );
		for ($i=0; $i<$arr_count; $i++) {
			if ( $p_str == $p_arr[$i] ) {
				return true;
			}
		}
		return false;
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
		global $g_mantis_bug_table, $g_mantis_user_table,
				$g_mantis_project_table,
				$g_project_cookie_val,
				$g_mantis_project_user_list_table,
				$g_notify_developers_on_new,
 				$g_notify_on_new_threshold,
				$g_notify_admin_on_new,
				$g_use_bcc, $g_use_phpMailer,
				$g_mantis_bug_monitor_table;

		$c_bug_id = (integer)$p_bug_id;

		# setup the array of email entries
		$send_arr = array();

		# Get Reporter Email
		$v_reporter_id = get_bug_field( $p_bug_id, 'reporter_id' );
		$t_notify_reporter = get_user_pref_info( $v_reporter_id, $p_notify_type );
		if ( ON == $t_notify_reporter ) {
			$send_arr[] = get_user_info( $v_reporter_id, 'email' );
		}

		# Get Handler Email
		$v_handler_id = get_bug_field( $p_bug_id, 'handler_id' );
		if ( $v_handler_id > 0 ) {
			$t_notify_handler = get_user_pref_info( $v_handler_id, $p_notify_type );
			if ( ON == $t_notify_handler ) {
				$send_arr[] = get_user_info( $v_handler_id, 'email' );
			}
		}

		# Check if we want to broadcast to all developers on a NEW bug
		if ( ( ON == $g_notify_developers_on_new )&&( 'email_on_new' == $p_notify_type ) ) {
			$t_project_id = get_bug_field( $p_bug_id, 'project_id' );
			$t_project_view_state = get_project_field( $g_project_cookie_val, 'view_state' );

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

				# always add all administrators
				$t_access_level = get_user_field( $v_id, 'access_level' );
				if ( ( ADMINISTRATOR == $t_access_level ) && ( ON == $g_notify_admin_on_new ) ) {
					$send_arr[] = $v_email;
					continue;
				}

				# see if users belong
				$t_project_view_state = get_project_field( $g_project_cookie_val, 'view_state' );
				if ( PUBLIC == $t_project_view_state ) {
					$query = "SELECT l.access_level
							FROM	$g_mantis_project_user_list_table l,
									$g_mantis_project_table p
							WHERE	l.project_id='$t_project_id' AND
									p.id=l.project_id AND
									l.user_id='$v_id'";
					$result = db_query( $query );
					$count = db_num_rows( $result );
					if ( $count > 0 ){
						$t_access_level = db_result( $result );
					}
					if ( $t_access_level >= $g_notify_on_new_threshold ) {
						$send_arr[] = $v_email;
					}

				} else {
					$query = "SELECT COUNT(*)
							FROM	$g_mantis_project_user_list_table l,
									$g_mantis_project_table p
							WHERE	l.project_id='$t_project_id' AND
									p.id=l.project_id AND
									l.user_id='$v_id' AND
									l.access_level>='$g_notify_on_new_threshold'";
					$result = db_query( $query );
					$count = db_result( $result, 0, 0 );
					if ( $count > 0 ) {
						$send_arr[] = $v_email;
						continue;
					}
				}
			}
/*				# if the user's notification is on then add to the list
				$t_notify = get_user_pref_info( $row["id"], $p_notify_type );
				if ( ON == $t_notify ) {
					$send_arr[] = $row["email"];
				}
			} # end DEVELOPERS*/
		} # end NEW bug developer section

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

			# if the user's notification is on then add to the list
			$t_notify = get_user_pref_info( $row['user_id'], $p_notify_type );
			if ( ON == $t_notify ) {
				$send_arr[] = $row['email'];
			}
		} # end MONITORING

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
		global $g_mantis_user_table, $g_path,
			$s_new_account_subject,
			$s_new_account_greeting, $s_new_account_url,
			$s_new_account_username, $s_new_account_password,
			$s_new_account_message, $s_new_account_do_not_reply;

		$c_user_id = (integer)$p_user_id;

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$c_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		# Build Welcome Message
		$t_message = $s_new_account_greeting.
						$s_new_account_url.$g_path."\n".
						$s_new_account_username.$v_username."\n".
						$s_new_account_password.$p_password."\n\n".
						$s_new_account_message.
						$s_new_account_do_not_reply;

		$t_headers = '';
		email_send( $v_email, $s_new_account_subject, $t_message, $t_headers );
	}
	# --------------------
	# Send new password when user forgets
	function email_reset( $p_user_id, $p_password ) {
		global 	$g_mantis_user_table, $g_path,
				$s_reset_request_msg, $s_account_name_msg,
				$s_news_password_msg;

		$c_user_id = (integer)$p_user_id;

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$c_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		# Build Welcome Message
		$t_message = $s_reset_request_msg."\n\n".
					$s_account_name_msg.': '.$v_username."\n".
					$s_news_password_msg.': '.$p_password."\n\n".
					$g_path."\n\n";

		email_send( $v_email, 'New Password', $t_message );
	}
	# --------------------
	# send notices when a new bug is added
	function email_new_bug( $p_bug_id ) {
		global 	$g_mantis_user_table, $s_new_bug_msg,
				$g_project_cookie_val;

		$t_bcc = build_bcc_list( $p_bug_id, 'email_on_new' );
		email_bug_info( $p_bug_id, $s_new_bug_msg, $t_bcc );
	}
	# --------------------
	# send notices when a new bugnote
	function email_bugnote_add( $p_bug_id ) {
		global $s_email_bugnote_msg;

		$t_bcc = build_bcc_list( $p_bug_id, 'email_on_bugnote' );
		email_bug_info( $p_bug_id, $s_email_bugnote_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is RESOLVED
	function email_resolved( $p_bug_id ) {
		global $s_email_resolved_msg;

		$t_bcc = build_bcc_list( $p_bug_id, 'email_on_resolved' );
		email_bug_info( $p_bug_id, $s_email_resolved_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is CLOSED
	function email_close( $p_bug_id ) {
		global $s_email_close_msg;

		$t_bcc = build_bcc_list( $p_bug_id, 'email_on_closed' );
		email_bug_info( $p_bug_id, $s_email_close_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is set to FEEDBACK
	function email_feedback( $p_bug_id ) {
		global $s_email_feedback_msg;

		$t_bcc = build_bcc_list( $p_bug_id, 'email_on_feedback' );
		email_bug_info( $p_bug_id, $s_email_feedback_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is REOPENED
	function email_reopen( $p_bug_id ) {
		global $s_email_reopen_msg;

		$t_bcc = build_bcc_list( $p_bug_id, 'email_on_reopened' );
		email_bug_info( $p_bug_id, $s_email_reopen_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is ASSIGNED
	function email_assign( $p_bug_id ) {
		global $s_email_assigned_msg;

		$t_bcc = build_bcc_list( $p_bug_id, 'email_on_assigned' );
		email_bug_info( $p_bug_id, $s_email_assigned_msg, $t_bcc );
	}
	# --------------------
	# messages are in two parts, the bug info and the bugnotes
	# Build the bug info part of the message
	function email_build_bug_message( $p_bug_id ) {
		global 	$g_mantis_bug_table, $g_mantis_bug_text_table,
				$g_mantis_user_table, $g_mantis_project_table,
				$g_complete_date_format, $g_show_view,
				$g_bugnote_order, $g_path,
				$s_email_reporter, $s_email_handler,
				$s_email_project, $s_email_bug, $s_email_category,
				$s_email_reproducibility, $s_email_severity,
				$s_email_priority, $s_email_status, $s_email_resolution,
				$s_email_duplicate, $s_email_date_submitted,
				$s_email_last_modified, $s_email_summary,
				$s_email_description,
				$g_email_separator1,
				$g_email_padding_length;

		$c_bug_id = (integer)$p_bug_id;

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

		$t_reporter_name = get_user( $v_reporter_id );
		$t_handler_name  = get_user( $v_handler_id );

		$v2_description   = string_email( $v2_description );
		$v_summary        = string_email( $v_summary );
		$v_date_submitted = date( $g_complete_date_format, $v_date_submitted );
		$v_last_updated   = date( $g_complete_date_format, $v_last_updated );

		$t_sev_str = get_enum_element( 'severity', $v_severity );
		$t_pri_str = get_enum_element( 'priority', $v_priority );
		$t_sta_str = get_enum_element( 'status', $v_status );
		$t_rep_str = get_enum_element( 'reproducibility', $v_reproducibility );
		$t_message = $g_email_separator1."\n";
		$t_message .= $g_path;
		if ( ADVANCED_ONLY == $g_show_view || ( BOTH == $g_show_view && ON == get_current_user_pref_field( 'advanced_view' ) ) ) {
			$t_message .= 'view_bug_advanced_page.php';
		} else {
			$t_message .= 'view_bug_page.php';
		}
		$t_message .= '?f_id='.$p_bug_id."\n";
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pd( $s_email_reporter.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_reporter_name."\n";
		$t_message .= str_pd( $s_email_handler.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_handler_name."\n";
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pd( $s_email_project.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_project_name."\n";
		$t_message .= str_pd( $s_email_bug.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_id."\n";
		$t_message .= str_pd( $s_email_category.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_category."\n";
		$t_message .= str_pd( $s_email_reproducibility.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_rep_str."\n";
		$t_message .= str_pd( $s_email_severity.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_sev_str."\n";
		$t_message .= str_pd( $s_email_priority.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_pri_str."\n";
		$t_message .= str_pd( $s_email_status.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_sta_str."\n";
		if ( RESOLVED == $v_status ) {
			$t_res_str = get_enum_element( 'resolution', $v_resolution );
			$t_message .= str_pd( $s_email_resolution.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$t_res_str."\n";
			if ( DUPLICATE == $v_resolution ) {
				$t_message .= str_pd( $s_email_duplicate.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_duplicate_id."\n";
			}
		}
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pd( $s_email_date_submitted.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_date_submitted."\n";
		$t_message .= str_pd( $s_email_last_modified.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_last_updated."\n";
		$t_message .= $g_email_separator1."\n";
		$t_message .= str_pd( $s_email_summary.': ', ' ', $g_email_padding_length, STR_PAD_RIGHT ).$v_summary."\n";
		$t_message .= "$s_email_description: \n".wordwrap( $v2_description )."\n";
		$t_message .= $g_email_separator1."\n\n";

		return $t_message;
	}
	# --------------------
	# messages are in two parts, the bug info and the bugnotes
	# Build the bugnotes part of the message
	function email_build_bugnote_message( $p_bug_id ) {
		global 	$g_mantis_bugnote_table, $g_mantis_bugnote_text_table,
				$g_mantis_user_table, $g_complete_date_format,
				$g_bugnote_order, $g_email_separator2;

		$c_bug_id = (integer)$p_bug_id;

		$t_message = '';

		$query = "SELECT *, UNIX_TIMESTAMP(last_modified) as last_modified
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$c_bug_id'
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

			$t_username = get_user( $t_reporter_id );

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
	# Send bug info to reporter and handler
	function email_bug_info( $p_bug_id, $p_message, $p_headers='' ) {
		global $g_mantis_user_table, $g_mantis_bug_table, $g_mantis_project_table,
				$g_to_email, $g_use_bcc;

		# build subject
		$p_subject = email_build_subject( $p_bug_id );

		# build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );
		$t_message .= email_build_bugnote_message( $p_bug_id );

		# send mail
		$res1 = 1;
		$res2 = 1;

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
				$g_return_path_email, $g_use_x_priority, $s_charset,
				$g_use_phpMailer, $g_phpMailer_method, $g_smtp_host;

		# short-circuit if no emails should be sent
		if ( OFF ==$g_enable_email_notification ) {
			return;
		}

		$t_recipient = trim( $p_recipient );
		$t_subject   = trim( $p_subject );
		$t_message   = trim( $p_message );

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
			
			include('class.phpmailer.php');
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
			$t_headers .= 'Content-Type: text/plain; charset=' . $s_charset . "\n";

			$t_headers .= $p_header;

			$t_recipient = make_lf_crlf( $t_recipient );
			$t_subject = make_lf_crlf( $t_subject );
			$t_message = make_lf_crlf( $t_message );
			$t_headers = make_lf_crlf( $t_headers );

			$result = mail( $t_recipient, $t_subject, $t_message, $t_headers );
			if ( TRUE != $result ) {
				PRINT "PROBLEMS SENDING MAIL TO: $t_recipient<p>";
				PRINT htmlspecialchars($t_recipient).'<br />';
				PRINT htmlspecialchars($t_subject).'<p>';
				PRINT nl2br(htmlspecialchars($t_headers)).'<br />';
				PRINT nl2br(htmlspecialchars($t_message)).'<p>';
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
		$p_project_name = get_project_field( $g_project_cookie_val, 'name' );

		# grab the subject (summary)
		$p_subject = string_email( get_bug_summary( $p_bug_id ) );

		# padd the bug id with zeros
		$p_bug_id = str_pd( $p_bug_id, '0', 7, STR_PAD_LEFT );

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
