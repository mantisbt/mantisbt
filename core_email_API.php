<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Email API
	###########################################################################
	### --------------------
	# check to see that the format is valid and that the mx record exists
	function is_valid_email( $p_email ) {
		global $g_validate_email, $g_check_mx_record;

		### if we don't validate then just accept
		if ( $g_validate_email==0 ) {
			return true;
		}

		if (eregi("^[_.0-9a-z-]+@([0-9a-z][-0-9a-z.]+).([a-z]{2,3}$)", $p_email, $check)) {
			if ( $g_check_mx_record == 1 ) {	# Check for valid mx records
				if (getmxrr($check[1].".".$check[2], $temp)) {
					return true;
				} else {
					$host = substr( strstr( $check[0], '@' ), 1 ).".";

					# for no mx record... try dns
					if (checkdnsrr ( $host, "ANY" ))
						return true;
				}
			} else {	# Email format was valid but don't check for valid mx records
				return true;
			}
		}
		### Everything failed.  Bad email.
		return false;
	}
	### --------------------
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
	### --------------------
	# build the bcc list
	function build_bcc_list( $p_bug_id, $p_notify_type ) {
		global $g_mantis_bug_table, $g_mantis_user_table,
				$g_mantis_project_user_list_table;

		# setup the array
		$send_arr = array();
		$send_counter = 0;

		### Get Reporter and Handler IDs
		$query = "SELECT reporter_id, handler_id
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		### Get Reporter Email
		$t_notify_reporter = get_user_pref_info( $v_reporter_id, $p_notify_type );
		$t_reporter_email = get_user_info( $v_reporter_id, "email" );
		if ( $t_notify_reporter==1 ) {
			$send_arr[$send_counter++] = $t_reporter_email;
		}

		### Get Handler Email
		if ( $v_handler_id > 0 ) {
			$t_handler_email = get_user_info( $v_handler_id, "email" );
			$t_notify_handler = get_user_pref_info( $v_handler_id, $p_notify_type );

			if ( $t_notify_handler==1 ) {
				if ( !check_duplicate($send_arr,$t_handler_email) ) {
					$send_arr[$send_counter++] = $t_handler_email;
				}
			}
		}

		# get the project id
		$p_project_id = get_bug_project_id( $p_bug_id );

		$t_dev = DEVELOPER;
		$query = "SELECT id, email
				FROM $g_mantis_user_table
				WHERE access_level>=$t_dev AND
					enabled='1'";
		$result = db_query( $query );

		# if the user's notification is on then add to the list
		$user_count = db_num_rows( $result );
		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );

			$t_notify = get_user_pref_info( $row["id"], $p_notify_type );
			if ( $t_notify==1 ) {
				if ( !check_duplicate($send_arr,$row["email"]) ) {
					$send_arr[$send_counter++] = $row["email"];
				}
			}
		}

		$query = "SELECT DISTINCT user_id
				FROM $g_mantis_project_user_list_table
				WHERE project_id=$p_project_id AND
					access_level>=$t_dev";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );
		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );

			$t_notify = get_user_pref_info( $row["user_id"], $p_notify_type );

			if ( $t_notify==1 ) {
				if ( !check_duplicate($send_arr,get_user_info( $row["user_id"], "email" )) ) {
					$send_arr[$send_counter++] = get_user_info( $row["user_id"], "email" );
				}
			}
		}

		$t_bcc = "Bcc: ";
		for ($i=0; $i<count($send_arr); $i++) {
			$t_bcc .= $send_arr[$i].", ";
		}

		# chop off the last comma and add a \n
		if ( strlen( $t_bcc )>5 ) {
			return substr( $t_bcc, 0, strlen( $t_bcc )-2 )."\n";
		} else {
			return "";
		}
	}
	### --------------------
	### Send password to user
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

		### Build Welcome Message
		$t_message = $s_new_account_greeting.
						$s_new_account_url.$g_path."\n".
						$s_new_account_username.$v_username."\n".
						$s_new_account_password.$p_password."\n\n".
						$s_new_account_message.
						$s_new_account_do_not_reply;

		$t_headers = "";
		email_send( $v_email, $s_new_account_subject, $t_message, $t_headers );
	}
	### --------------------
	### Send new password when user forgets
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

		### Build Welcome Message
		$t_message = $s_reset_request_msg."\n\n".
					$s_account_name_msg.": ".$v_username."\n".
					$s_news_password_msg.": ".$p_password."\n\n";
					$g_path."\n\n";

		email_send( $v_email, "New Password", $t_message );
	}
	### --------------------
	function email_new_bug( $p_bug_id ) {
		global 	$g_mantis_user_table, $s_new_bug_msg, $g_email_new_address,
				$g_project_cookie_val;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_new" );
		email_bug_info( $p_bug_id, $s_new_bug_msg, $t_bcc );

		if ( !empty($g_email_new_address) ) {
			email_bug_info_to_address( $p_bug_id, $s_new_bug_msg, $g_email_new_address );
		}
	}
	### --------------------
	### Notify reporter and handler when new bugnote is added
	function email_bugnote_add( $p_bug_id ) {
		global $s_email_bugnote_msg, $g_email_update_address;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_bugnote" );
		email_bug_info( $p_bug_id, $s_email_bugnote_msg, $t_bcc );

		if ( !empty($g_email_update_address) ) {
			email_bug_info_to_address( $p_bug_id, $s_email_bugnote_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_resolved( $p_bug_id ) {
		global $s_email_resolved_msg, $g_email_update_address;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_resolved" );
		email_bug_info( $p_bug_id, $s_email_resolved_msg, $t_bcc );

		if ( !empty($g_email_update_address) ) {
			email_bug_info_to_address( $p_bug_id, $s_email_resolved_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_close( $p_bug_id ) {
		global $s_email_close_msg, $g_email_update_address;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_closed" );
		email_bug_info( $p_bug_id, $s_email_close_msg, $t_bcc );

		if ( !empty($g_email_update_address) ) {
			email_bug_info_to_address( $p_bug_id, $s_email_resolved_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_feedback( $p_bug_id ) {
		global $s_email_feedback_msg, $g_email_update_address;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_feedback" );
		email_bug_info( $p_bug_id, $s_email_feedback_msg, $t_bcc );

		if ( !empty($g_email_update_address) ) {
			email_bug_info_to_address( $p_bug_id, $s_email_feedback_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_reopen( $p_bug_id ) {
		global $s_email_reopen_msg, $g_email_update_address;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_reopened" );
		email_bug_info( $p_bug_id, $s_email_reopen_msg, $t_bcc );

		if ( !empty($g_email_update_address) ) {
			email_bug_info_to_address( $p_bug_id, $s_email_reopen_msg, $g_email_update_address );
		}
	}
	### --------------------
	function email_assign( $p_bug_id ) {
		global $s_email_assigned_msg, $g_email_update_address;

		$t_bcc = build_bcc_list( $p_bug_id, "email_on_assigned" );
		email_bug_info( $p_bug_id, $s_email_assigned_msg, $t_bcc );

		if ( !empty($g_email_update_address) ) {
			email_bug_info_to_address( $p_bug_id, $s_email_assigned_msg, $g_email_update_address );
		}
	}
	### --------------------
	# Build the top part of the message
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

		$query = "SELECT *
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
		$v_date_submitted = date( $g_complete_date_format, sql_to_unix_time( $v_date_submitted ) );
		$v_last_updated   = date( $g_complete_date_format, sql_to_unix_time( $v_last_updated ) );

		$t_sev_str = get_enum_element( $s_severity_enum_string, $v_severity );
		$t_pri_str = get_enum_element( $s_priority_enum_string, $v_priority );
		$t_sta_str = get_enum_element( $s_status_enum_string, $v_status );
		$t_rep_str = get_enum_element( $s_reproducibility_enum_string, $v_reproducibility );
		$t_message = "=======================================================================\n";
		$t_message .= $g_view_bug_page."?f_id=".$p_bug_id."\n";
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_reporter:        $t_reporter_name\n";
		$t_message .= "$s_email_handler:         $t_handler_name\n";
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_project:         $t_project_name\n";
		$t_message .= "$s_email_bug:             $v_id\n";
		$t_message .= "$s_email_category:        $v_category\n";
		$t_message .= "$s_email_reproducibility: $t_rep_str\n";
		$t_message .= "$s_email_severity:        $t_sev_str\n";
		$t_message .= "$s_email_priority:        $t_pri_str\n";
		$t_message .= "$s_email_status:          $t_sta_str\n";
		if ( $v_status==RESOLVED ) {
			$t_res_str = get_enum_element( $s_resolution_enum_string, $v_resolution );
			$t_message .= "$s_email_resolution:      $t_res_str\n";
			if ( $v_resolution==DUPLICATE ) {
				$t_message .= "$s_email_duplicate:      $v_duplicate_id\n";
			}
		}
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_date_submitted:   $v_date_submitted\n";
		$t_message .= "$s_email_last_modified:    $v_last_updated\n";
		$t_message .= "=======================================================================\n";
		$t_message .= "$s_email_summary:  $v_summary\n\n";
		$t_message .= "$s_email_description: \n".word_wrap( $v2_description )."\n";
		$t_message .= "=======================================================================\n\n";

		return $t_message;
	}
	### --------------------
	# Build the bottom part of the message
	function email_build_bugnote_message( $p_bug_id ) {
		global 	$g_mantis_bugnote_table, $g_mantis_bugnote_text_table,
				$g_mantis_user_table, $g_complete_date_format,
				$g_bugnote_order;

		$t_message = "";

		$query = "SELECT *
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$p_bug_id'
				ORDER BY date_submitted $g_bugnote_order";
		$result = db_query( $query );
		$bugnote_count = db_num_rows( $result );

		### BUILT MESSAGE
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
			$t_last_modified = date( $g_complete_date_format, sql_to_unix_time( $t_last_modified ) );
			$t_string = " ".$t_username." - ".$t_last_modified." ";
			$t_message = $t_message."-----------------------------------------------------------------------\n";
			$t_message = $t_message.word_wrap( $t_string )."\n";
			$t_message = $t_message."-----------------------------------------------------------------------\n";
			$t_message = $t_message.word_wrap( $t_note )."\n\n";
		}

		return $t_message;
	}
	### --------------------
	### Send bug info to reporter and handler
	function email_bug_info( $p_bug_id, $p_message, $p_headers="" ) {
		global $g_mantis_user_table, $g_mantis_bug_table, $g_mantis_project_table, $g_from_email;

		# build subject
		$p_subject = email_build_subject( $p_bug_id );

		### build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );
		$t_message .= email_build_bugnote_message( $p_bug_id );

		### send mail
		$res1 = 1;
		$res2 = 1;

		### Send Email
		$res1 = email_send( $g_from_email, $p_subject, $t_message, $p_headers );
	}
	### --------------------
	### Send to only the id
	function email_bug_info_to_id( $p_bug_id, $p_message, $p_user_id ) {
		# build subject
		$p_subject = email_build_subject( $p_bug_id );

		### build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );
		$t_message .= email_build_bugnote_message( $p_bug_id );

		$p_user_email = get_user_info( $p_user_id, "email" );

		### send mail
		$res = email_send( $p_user_email, $p_subject, $t_message, $p_bcc_header );
	}
	### --------------------
	### Send to only the address
	function email_bug_info_to_address( $p_bug_id, $p_message, $p_email_address ) {
		# build subject
		$p_subject = email_build_subject( $p_bug_id );

		### build message
		$t_message = $p_message."\n";
		$t_message .= email_build_bug_message( $p_bug_id );
		$t_message .= email_build_bugnote_message( $p_bug_id );

		### send mail
		$res = email_send( $p_email_address, $p_subject, $t_message );
	}
	### --------------------
	function email_send( $p_recipient, $p_subject, $p_message, $p_header="" ) {
		global $g_from_email, $g_enable_email_notification, $g_return_path_email;

		if ( $g_enable_email_notification == 1 ) {

			# Visit http://www.php.net/manual/function.mail.php
			# if you have problems with mailing

			$t_recipient = trim( $p_recipient );

			$t_subject = trim( $p_subject );

			$t_message = trim( $p_message );

			# @@@ Is it important to wordwrap???
			/*if ( floor( phpversion() )>=4 ) {
				$t_message = trim( wordwrap( $t_message, 72 ) );
			} else {
				$t_message = trim( word_wrap( $t_message, 72 ) );
			}*/

			$t_headers = "From: $g_from_email\n";
			#$t_headers .= "Reply-To: $p_reply_to_email\n";
			$t_headers .= "X-Sender: <$g_from_email>\n";
			$t_headers .= "X-Mailer: PHP/".phpversion()."\n";
			$t_headers .= "X-Priority: 0\n"; # Urgent = 1, No Urgent = 5, Disable = 0
			$t_headers .= "Return-Path: <$g_return_path_email>\n"; # return email if error
			# If you want to send foreign charsets
			#$t_headers .= "Content-Type: text/html; charset=iso-8859-1\n";

			$t_headers .= $p_header;

			# @@@ for debugging only
			#echo $t_recipient."<BR>".$t_subject."<BR>".$t_message."<BR>".$t_headers;
			#exit;
			#echo $t_recipient."<br>";
			#echo nl2br($t_headers)."<br>";
			#echo $t_subject."<br>";
			#echo nl2br($t_message)."<br>";
			#exit;

			$result = mail( $t_recipient, $t_subject, $t_message, $t_headers );
			if ( !$result ) {
				PRINT "PROBLEMS SENDING MAIL TO: $t_recipient";
				exit;
			}
		}
	}
	### --------------------
	function get_bug_project_id( $p_bug_id ) {
		global $g_mantis_bug_table;

		$query = "SELECT project_id
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	### --------------------
	function get_bug_summary( $p_bug_id ) {
		global $g_mantis_bug_table;

		$query = "SELECT summary
				FROM $g_mantis_bug_table
				WHERE id='$p_bug_id'";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	### --------------------
	function email_build_subject( $p_bug_id ) {
		# grab the project name
		$p_project_name = get_project_name( get_bug_project_id( $p_bug_id ) );

		# grab the subject (summary)
		$p_subject = string_email( get_bug_summary( $p_bug_id ) );

		# padd the bug id with zeros
		$p_bug_id = str_pd( $p_bug_id, "0", 7 );

		return "[".$p_project_name." ".$p_bug_id."]: ".$p_subject;
	}
	###########################################################################
	### END                                                                 ###
	###########################################################################
?>