<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.6 $
	# $Author: jfitzell $
	# $Date: 2003-01-08 02:44:39 $
	#
	# $Id: csv_export.php,v 1.6 2003-01-08 02:44:39 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# check to see if the cookie does not exist
	if ( !filter_is_cookie_valid() ) {
		print_header_redirect( 'view_all_set.php?type=0' );
	}

	$t_bug_count = null;
	$t_page_count = null;

	$rows = filter_get_bug_rows( 1, -1, &$t_page_count, &$t_bug_count );

	echo lang_get('email_project') . ',' . config_get('page_title') . "\n\n";
	echo lang_get( 'priority' ) . ',' .
			lang_get( 'id' ) . ',' .
			lang_get( 'severity' ) . ',' .
			lang_get( 'status' ) . ',' .
			lang_get( 'version' ) . ',' .
			lang_get( 'assigned_to' ) . ',' .
			lang_get( 'reporter' ) . ',' .
			lang_get( 'updated' ) . ',' .
			lang_get( 'summary' ) . "\n";

	for ( $i=0 ; $i < sizeof($rows) ; $i++ ) {
		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

		$t_last_updated		= date( config_get( 'short_date_format' ), $v_last_updated );
		$t_priority			= get_enum_element( 'priority', $v_priority );
		$t_severity			= get_enum_element( 'severity', $v_severity );
		$t_status			= get_enum_element( 'status', $v_status );
		$t_hander_name		= user_get_name( $v_handler_id );
		$t_reporter_name	= user_get_name( $v_reporter_id );
		$v_summary			= string_display( $v_summary );

		echo "$t_priority,$v_id,$t_severity,$t_status,$v_version,$t_hander_name,$t_reporter_name,$t_last_updated,\"$v_summary\"\r\n";
	}

	# Send headers to browser to active mime loading
	header( 'Content-Type: text/plain; name=' . config_get( 'page_title' ) . '.csv' );
	header( 'Content-Transfer-Encoding: BASE64;' );
	header( 'Content-Disposition: attachment; filename=' . config_get( 'page_title' ) . '.csv' );
	
	exit; 

?>
