<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Email API
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
		if (eregi("^[_.0-9a-z-]+@([0-9a-z][-0-9a-z.]+).([a-z]{2,3}$)", $p_email, $check)) {
			# passed format check. see if we should check the mx records
			if ( ON == $g_check_mx_record ) {	# Check for valid mx records
				if (getmxrr($check[1].".".$check[2], $temp)) {
					return true;
				} else {
					$host = substr( strstr( $check[0], '@' ), 1 ).".";

					# for no mx record... try dns check
					if (checkdnsrr ( $host, "ANY" ))
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

	# @@@ ugly function: here's how it works:
	# We store all the email addresses in an array.  First we grab the reporter.
	# If the handler is assigned then we add that.

	# The next part is only for NEW bugs.
	# We go through the assigned project users and grab everyone who is a
	# DEVELOPER or higher and add that to a user_id array.  We also add the
	# emails to the email address array.  Then we check the mantis_user_table
	# and grab anyone who should have automatic access.
	# This way we don't accidentally grab a normal developer who is set to be
	# lower for a specific project.  The check_duplicates function should prevent
	# duplicate entries from being entered

	# Lastly, we do a bit of post processing and return the bcc string.
	function build_bcc_list( $p_bug_id, $p_notify_type ) {
		global $g_mantis_bug_table, $g_mantis_user_table,
				$g_mantis_project_table,
				$g_project_cookie_val,
				$g_mantis_project_user_list_table,
				$g_notify_developers_on_new,
				$g_use_bcc, $g_use_phpMailer;

		# setup the array of email entries
		$send_arr = array();
		$send_counter = 0;

		# Get Reporter and Handler IDs
		$query = "SELECT reporter_id, handler_id
		    FROM $g_mantis_bug_table
		    WHERE id='$p_bug_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		# Get Reporter Email
		$t_notify_reporter = get_user_pref_info( $v_reporter_id, $p_notify_type );
		$t_reporter_email = get_user_info( $v_reporter_id, "email" );
		if ( 1 == $t_notify_reporter ) {
			$send_arr[$send_counter++] = $t_reporter_email;
		}

		# Get Handler Email
		if ( $v_handler_id > 0 ) {
			$t_handler_email = get_user_info( $v_handler_id, "email" );
			$t_notify_handler = get_user_pref_info( $v_handler_id, $p_notify_type );

			if ( 1 == $t_notify_handler ) {
				if (!check_duplicate($send_arr,$t_handler_email) ) {
					$send_arr[$send_counter++] = $t_handler_email;
				}
			}
		}

		# Get Developer Email
		if ( ( ON == $g_notify_developers_on_new )&&( "email_on_new" == $p_notify_type ) ) {
			$user_id_arr = array();
			$user_id_counter = 0;
			$p_project_id = get_bug_project_id( $p_bug_id );
			$t_dev = DEVELOPER;

			# Project specific developers
			# All assigned users that are develoeprs and higher whose accounts are enabled
			$query = "SELECT DISTINCT p.user_id
					FROM $g_mantis_project_user_list_table p,
						$g_mantis_user_table u
					WHERE p.project_id=$p_project_id AND
							p.access_level>=$t_dev AND
							p.user_id=u.id AND
							u.enabled=1";
			$result = db_query( $query );
			$proj_user_count = db_num_rows( $result );
			for ($i=0;$i<$proj_user_count;$i++) {
				$row = db_fetch_array( $result );

				# if the user's notification is on then add to the list
				$t_notify = get_user_pref_info( $row["user_id"], $p_notify_type );

				if ( 1 == $t_notify ) {
					$user_id_arr[$user_id_counter++] = $row["user_id"];

					if ( !check_duplicate( $send_arr,get_user_info( $row["user_id"], "email" ) ) ) {
						$send_arr[$send_counter++] = get_user_info( $row["user_id"], "email" );
					}
				}
			}

			# Grab the minimum access threshold for this project
			$query = "SELECT access_min, view_state
					FROM $g_mantis_project_table
					WHERE id='$g_project_cookie_val'";
			$result = db_query( $query );
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			# Global developer role
			if ( PRIVATE == $v_view_state ) {
				$query = "SELECT id
							FROM $g_mantis_user_table
							WHERE access_level>=$v_access_min AND
									enabled=1";
			} else {
				$query = "SELECT id
							FROM $g_mantis_user_table
							WHERE access_level>=$t_dev AND
									enabled=1";
			}
			$result = db_query( $query );

			$user_count = db_num_rows( $result );
			for ($i=0;$i<$user_count;$i++) {
				$row = db_fetch_array( $result );

				$t_notify = get_user_pref_info( $row["id"], $p_notify_type );
				$found = 0;
				if ( 1 == $t_notify ) {
					for ($k=0;$k<count( $user_id_arr );$k++) {
						if ( $user_id_arr[$k] == $row["id"] ) {
							$found = 1;
							break;
						}
					}
					if ( $found == 0 ) {
						if ( !check_duplicate( $send_arr, get_user_info( $row["id"], "email" ) ) ) {
							$send_arr[$send_counter++] = get_user_info( $row["id"], "email" );
						}
					}
				}
			}
		}

		$t_bcc = ($g_use_bcc && !$g_use_phpMailer) ? "Bcc: " : "";
		## win-bcc-bug

		for ($i=0; $i<count($send_arr); $i++) {
			$t_bcc .= $send_arr[$i].", ";
		}

		# chop off the last comma and add a \n
		if ( strlen( $t_bcc )>5 ) {
			return substr( $t_bcc, 0, strlen( $t_bcc )-2).(($g_use_bcc) ? "\n" : "");  ## win-bcc-bug
		} else {
			return "";
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

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$p_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		# Build Welcome Message
		$t_message = $s_new_account_greeting.
						$s_new_account_url.$g_path."\n".
						$s_new_account_username.$v_username."\n".
						$s_new_account_password.$p_password."\n\n".
						$s_new_account_message.
						$s_new_account_do_not_reply;

		$t_headers = "";
		email_send( $v_email, $s_new_account_subject, $t_message, $t_headers );
	}
	# --------------------
	# Send new password when user forgets
	function email_reset( $p_user_id, $p_password ) {
		global 	$g_mantis_user_table, $g_path,
				$s_reset_request_msg, $s_account_name_msg,
				$s_news_password_msg;

		$query = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$p_user_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		# Build Welcome Message
		$t_message = $s_reset_request_msg."\n\n".
					$s_account_name_msg.": ".$v_username."\n".
					$s_news_password_msg.": ".$p_password."\n\n".
					$g_path."\n\n";

		email_send( $v_email, "New Password", $t_message );
	}
	# --------------------
	# send notices when a new bug is added
	function email_new_bug( $p_bug_id ) {
		global 	$g_mantis_user_table, $s_new_bug_msg,
				$g_project_cookie_val;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_new" );
		email_bug_info( $p_bug_id, $s_new_bug_msg, $t_bcc );
	}
	# --------------------
	# send notices when a new bugnote
	function email_bugnote_add( $p_bug_id ) {
		global $s_email_bugnote_msg;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_bugnote" );
		email_bug_info( $p_bug_id, $s_email_bugnote_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is RESOLVED
	function email_resolved( $p_bug_id ) {
		global $s_email_resolved_msg;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_resolved" );
		email_bug_info( $p_bug_id, $s_email_resolved_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is CLOSED
	function email_close( $p_bug_id ) {
		global $s_email_close_msg;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_closed" );
		email_bug_info( $p_bug_id, $s_email_close_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is set to FEEDBACK
	function email_feedback( $p_bug_id ) {
		global $s_email_feedback_msg;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_feedback" );
		email_bug_info( $p_bug_id, $s_email_feedback_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is REOPENED
	function email_reopen( $p_bug_id ) {
		global $s_email_reopen_msg;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_reopened" );
		email_bug_info( $p_bug_id, $s_email_reopen_msg, $t_bcc );
	}
	# --------------------
	# send notices when a bug is ASSIGNED
	function email_assign( $p_bug_id ) {
		global $s_email_assigned_msg;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_assigned" );
		email_bug_info( $p_bug_id, $s_email_assigned_msg, $t_bcc );
	}
	# --------------------
	# messages are in two parts, the bug info and the bugnotes
	# Build the bug info part of the message
	function email_build_bug_message( $p_bug_id ) {
		global 	$g_mantis_bug_table, $g_mantis_bug_text_table,
				$g_mantis_user_table, $g_mantis_project_table,
				$g_complete_date_format,
				$g_bugnote_order, $g_view_bug_page,
				$s_email_reporter, $s_email_handler,
				$s_email_project, $s_email_bug, $s_email_category,
				$s_email_reproducibility, $s_email_severity,
				$s_email_priority, $s_email_status, $s_email_resolution,
				$s_email_duplicate, $s_email_date_submitted,
				$s_email_last_modified, $s_email_summary,
				$s_email_description,
				$s_resolution_enum_string,
				$s_severity_enum_string,
				$s_priority_enum_string,
				$s_status_enum_string,
				$s_reproducibility_enum_string;

		$query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted,
				UNIX_TIMESTAMP(last_updated) as last_updated
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'
				ORDER BY date_submitted $g_bugnote_order";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		$query = "SELECT *
				FROM $g_mantis_bug_text_table
				WHERE id='$v_bug_text_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v2" );

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

		$t_sev_str = get_enum_element( $s_severity_enum_string, $v_severity );
		$t_pri_str = get_enum_element( $s_priority_enum_string, $v_priority );
		$t_sta_str = get_enum_element( $s_status_enum_string, $v_status );
		$t_rep_str = get_enum_element( $s_reproducibility_enum_string, $v_reproducibility );
		$t_message = "=======================================================================\n";
		$t_message .= $g_view_bug_page."?f_id=".$p_bug_id."\n";
		$t_message .= "=======================================================================\n";
		$t_message .= str_pd( $s_email_reporter.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_reporter_name."\n";
		$t_message .= str_pd( $s_email_handler.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_handler_name."\n";
		$t_message .= "=======================================================================\n";
		$t_message .= str_pd( $s_email_project.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_project_name."\n";
		$t_message .= str_pd( $s_email_bug.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$v_id."\n";
		$t_message .= str_pd( $s_email_category.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$v_category."\n";
		$t_message .= str_pd( $s_email_reproducibility.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_rep_str."\n";
		$t_message .= str_pd( $s_email_severity.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_sev_str."\n";
		$t_message .= str_pd( $s_email_priority.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_pri_str."\n";
		$t_message .= str_pd( $s_email_status.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_sta_str."\n";
		if ( RESOLVED == $v_status ) {
			$t_res_str = get_enum_element( $s_resolution_enum_string, $v_resolution );
			$t_message .= str_pd( $s_email_resolution.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$t_res_str."\n";
			if ( DUPLICATE == $v_resolution ) {
				$t_message .= str_pd( $s_email_duplicate.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$v_duplicate_id."\n";
			}
		}
		$t_message .= "=======================================================================\n";
		$t_message .= str_pd( $s_email_date_submitted.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$v_date_submitted."\n";
		$t_message .= str_pd( $s_email_last_modified.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$v_last_updated."\n";
		$t_message .= "=======================================================================\n";
		$t_message .= str_pd( $s_email_summary.": ", " ", EMAIL_PAD_LENGTH, STR_PAD_RIGHT ).$v_summary."\n";
		$t_message .= "$s_email_description: \n".word_wrap( $v2_description )."\n";
		$t_message .= "=======================================================================\n\n";

		return $t_message;
	}
	# --------------------
	# messages are in two parts, the bug info and the bugnotes
	# Build the bugnotes part of the message
	function email_build_bugnote_message( $p_bug_id ) {
		global 	$g_mantis_bugnote_table, $g_mantis_bugnote_text_table,
				$g_mantis_user_table, $g_complete_date_format,
				$g_bugnote_order;

		$t_message = "";

		$query = "SELECT *, UNIX_TIMESTAMP(last_updated) as last_updated
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$p_bug_id'
				ORDER BY date_submitted $g_bugnote_order";
		$result = db_query( $query );
		$bugnote_count = db_num_rows( $result );

		# BUILT MESSAGE
		for ( $i=0; $i<$bugnote_count; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "t" );

			$query = "SELECT note
					FROM $g_mantis_bugnote_text_table
					WHERE id='$t_bugnote_text_id'";
			$result2 = db_query( $query );

			$t_username = get_user( $t_reporter_id );

			$t_note = db_result( $result2, 0, 0 );
			$t_note = string_email( $t_note );
			$t_last_modified = date( $g_complete_date_format, $t_last_modified );
			$t_string = " ".$t_username." - ".$t_last_modified." ";
			$t_message = $t_message."-----------------------------------------------------------------------\n";
			$t_message = $t_message.$t_string."\n";
			$t_message = $t_message."-----------------------------------------------------------------------\n";
			$t_message = $t_message.word_wrap( $t_note )."\n\n";
		}

		return $t_message;
	}
	# --------------------
	# Send bug info to reporter and handler
	function email_bug_info( $p_bug_id, $p_message, $p_headers="" ) {
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
			$to = $g_to_email.(($p_headers && $g_to_email) ? ", " : "").$p_headers;
			# echo "<br>email_bug_info::Sending email to :" . $to;
			$res1 = email_send( $to, $p_subject, $t_message, "" );
		} else {
			# Send Email
			# echo "<br>email_bug_info::Sending email to : ".$g_to_email;
			$res1 = email_send( $g_to_email, $p_subject, $t_message, $p_headers );
		}
	}
	# --------------------
	# Send to only the id
	# @@@ UNUSED
	function email_bug_info_to_id( $p_bug_id, $p_message, $p_user_id ) {
		# build subject
		$p_subject = email_build_subject( $p_bug_id );

		# build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );
		$t_message .= email_build_bugnote_message( $p_bug_id );

		$p_user_email = get_user_info( $p_user_id, "email" );

		# send mail
		$res = email_send( $p_user_email, $p_subject, $t_message, $p_bcc_header );
	}
	# --------------------
	# this function sends the actual email
	function email_send( $p_recipient, $p_subject, $p_message, $p_header="" ) {
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

		# @@@ for debugging only
		#echo $t_recipient."<BR>".$t_subject."<BR>".$t_message."<BR>".$t_headers;
		#exit;
		#echo "<br>xxxRecipient =".$t_recipient."<br>";
		#echo "Headers =".nl2br($t_headers)."<br>";
		#echo $t_subject."<br>";
		#echo nl2br($t_message)."<br>";
		#exit;

		if ( ON == $g_use_phpMailer )  {
			# Visit http://phpmailer.sourceforge.net
			# if you have problems with phpMailer

			include("class.phpmailer.php");
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
			$mail->FromName = "";

			$mail->AddAddress($t_recipient, "");

			# add to the BCC list
			$t_bcc_list = split(",", $p_header);
			while(list(, $t_bcc) = each($t_bcc_list)) {
				if ( !empty( $t_bcc ) ) {
					$mail->AddBCC($t_bcc, "");
				}
			}

			$mail->Subject = $t_subject;
			$mail->Body    = "\n".$t_message;

			if( !$mail->Send() ) {
				PRINT "PROBLEMS SENDING MAIL TO: $t_recipient<br>";
				PRINT "Mailer Error: ".$mail->ErrorInfo."<br>";
				exit;
			}
		} else {
			# Visit http://www.php.net/manual/function.mail.php
			# if you have problems with mailing

			$t_headers = "From: $g_from_email\n";
			#$t_headers .= "Reply-To: $p_reply_to_email\n";

			$t_headers .= "X-Sender: <$g_from_email>\n";
			$t_headers .= "X-Mailer: PHP/".phpversion()."\n";
			if ( ON == $g_use_x_priority ) {
				$t_headers .= "X-Priority: 0\n";    # Urgent = 1, Not Urgent = 5, Disable = 0
			}
			$t_headers .= "Return-Path: <$g_return_path_email>\n";          # return email if error
			# If you want to send foreign charsets
			# $t_headers .= "Content-Type: text/html; charset=iso-8859-1\n";

			$t_headers .= $p_header;
			$result = mail( $t_recipient, $t_subject, $t_message, $t_headers );
			if ( !$result ) {
				PRINT "PROBLEMS SENDING MAIL TO: $t_recipient<p>";
				PRINT htmlspecialchars($t_recipient)."<br>";
				PRINT htmlspecialchars($t_subject)."<p>";
				PRINT nl2br(htmlspecialchars($t_headers))."<br>";
				PRINT nl2br(htmlspecialchars($t_message))."<p>";
				exit;
			}
		}
	}
	# --------------------
	# helper function
	function get_bug_project_id( $p_bug_id ) {
		global $g_mantis_bug_table;

		$query = "SELECT project_id
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# helper function
	function get_bug_summary( $p_bug_id ) {
		global $g_mantis_bug_table;

		$query = "SELECT summary
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	# --------------------
	# formats the subject correctly
	# we include the project name, bug id, and summary.
	# @@@ NOTE: Is there a limit to the length of the subject field?
	function email_build_subject( $p_bug_id ) {
		# grab the project name
		$p_project_name = get_project_name( get_bug_project_id( $p_bug_id ) );

		# grab the subject (summary)
		$p_subject = string_email( get_bug_summary( $p_bug_id ) );

		# padd the bug id with zeros
		$p_bug_id = str_pd( $p_bug_id, "0", 7, STR_PAD_LEFT );

		return "[".$p_project_name." ".$p_bug_id."]: ".$p_subject;
	}
	# --------------------
?>