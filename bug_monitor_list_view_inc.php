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
	# $Id: bug_monitor_list_view_inc.php,v 1.16.2.1 2007-10-13 22:32:43 giallu Exp $
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
	$query = "SELECT user_id, enabled
			FROM $t_bug_monitor_table m, $t_user_table u
			WHERE m.bug_id=$c_bug_id AND m.user_id = u.id
			ORDER BY u.realname, u.username";
	$result = db_query($query);
	$num_users = db_num_rows($result);

	echo '<a name="monitors" id="monitors" /><br />';
?>

<?php
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
 			$row = db_fetch_array( $result );
			echo ($i > 0) ? ', ' : '';
			echo print_user( $row['user_id'] );
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
