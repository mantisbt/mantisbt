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
	# $Id$
	# --------------------------------------------------------

	# This include file prints out the list of users monitoring the current
	# bug.	$f_bug_id must be set and be set to the bug id

	if ( access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $f_bug_id ) ) { 
		$c_bug_id = db_prepare_int( $f_bug_id );
		$t_bug_monitor_table = db_get_table( 'mantis_bug_monitor_table' );
		$t_user_table = db_get_table( 'mantis_user_table' );

		# get the bugnote data
		$query = "SELECT user_id, enabled
				FROM $t_bug_monitor_table m, $t_user_table u
				WHERE m.bug_id=" . db_param() . " AND m.user_id = u.id
				ORDER BY u.realname, u.username";
		$result = db_query_bound($query, Array( $c_bug_id ) );
		$num_users = db_num_rows($result);

		$t_users = array();
		for ( $i = 0; $i < $num_users; $i++ ) {
			$row = db_fetch_array( $result );
			$t_users[$i] = $row['user_id'];
	}
	user_cache_array_rows( $t_users );

	echo '<a name="monitors" id="monitors" /><br />';

	collapse_open( 'monitoring' );
?>
<table class="width100" cellspacing="1">
<?php 	if ( 0 == $num_users ) { ?>
<tr>
	<td class="center">
		<?php echo lang_get( 'no_users_monitoring_bug' ); ?>
	</td>
</tr>
<?php	} else { ?>
<tr>
	<td class="form-title" colspan="2">
<?php
	collapse_icon( 'monitoring' );
 ?>
		<?php echo lang_get( 'users_monitoring_bug' ); ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="15%">
		<?php echo lang_get( 'monitoring_user_list' ); ?>
	</td>
	<td>
<?php
 		for ( $i = 0; $i < $num_users; $i++ ) { 			
			echo ($i > 0) ? ', ' : '';
			echo print_user( $t_users[$i] );
 		}
?>
	</td>
</tr>
<?php 	} ?>
</table>
<?php
	collapse_closed( 'monitoring' ); 
?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2"><?php collapse_icon( 'monitoring' ); ?>
		<?php echo lang_get( 'users_monitoring_bug' ); ?>
	</td>
</tr>
</table>
<?php
	collapse_end( 'monitoring' );
?>

<?php } # show monitor list ?>
