<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_monitor_list_view_inc.php,v 1.14 2005-04-22 22:06:07 prichards Exp $
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

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="monitoring_closed" style="display: none;">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<a href="" onclick="ToggleDiv( 'monitoring', g_div_monitoring ); return false;"
		><img border="0" src="images/plus.png" alt="+" /></a>
		<?php echo lang_get( 'users_monitoring_bug' ); ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="monitoring_open">
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
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onclick="ToggleDiv( 'monitoring', g_div_monitoring ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
<?php } ?>
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
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/javascript">
<!--
	SetDiv( "monitoring", g_div_monitoring );
-->
</script>
<?php } ?>

<?php } # show monitor list ?>
