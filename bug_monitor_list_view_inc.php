<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_monitor_list_view_inc.php,v 1.6 2003-03-22 21:42:22 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the list of users monitoring the current
	# bug.	$f_bug_id must be set and be set to the bug id
?>
<?php	if ( access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $f_bug_id ) ) { ?>
<?php
	$c_bug_id = db_prepare_int( $f_bug_id );
	$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );
	$t_user_table = config_get( 'mantis_user_table' );

	# get the bugnote data
	$query = "SELECT user_id
			FROM $t_bug_monitor_table m, $t_user_table u
			WHERE m.bug_id=$c_bug_id AND m.user_id = u.id
			ORDER BY u.username";
	$result = db_query($query);
	$num_users = db_num_rows($result);

	echo '<a name="monitors" id="monitors" /><br />';
 	echo '<table class="width100" cellspacing="1">';
 	if ( 0 == $num_users ) {
		echo '<tr><td class="center">';
 	 	echo lang_get( 'no_users_monitoring_bug' );
		echo '</td></tr>';
	} else {
		echo '<tr><td class="form-title" colspan="2">' . lang_get( 'users_monitoring_bug' ) . '</td></tr>';
		echo '<tr class="row-1">';
		echo '<td class="category" width="15%">' . lang_get( 'monitoring_user_list' ) . '</td>';
		echo '<td>';

 		for ( $i = 0; $i < $num_users; $i++ ) {
 			$row = db_fetch_array( $result );
			echo ($i > 0) ? ', ' : '';
			echo print_user( $row[0] );
 		}

		echo '</td></tr>';
 	}
 	echo '</table>';
?>
<?php } # show monitor list ?>
