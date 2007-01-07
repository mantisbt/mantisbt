<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: billing_inc.php,v 1.4 2007-01-07 11:36:48 davidnewcomb Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the bug bugnote_stats

	# $f_bug_id must already be defined
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bugnote_api.php' );
?>
<?php
	if ( ! config_get('time_tracking_enabled') )
		return;
?>

<a name="bugnotestats" id="bugnotestats" /><br />

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="bugnotestats_closed" style="display: none;">
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
		<a href="" onClick="ToggleDiv( 'bugnotestats', g_div_bugnotestats ); return false;"
		><img border="0" src="images/plus.png" alt="+" /></a>
		<?php echo lang_get( 'time_tracking' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="bugnotestats_open">
<?php
	$f_bugnote_stats_from = gpc_get_string('bugnote_stats_from', '');
	$f_bugnote_stats_to = gpc_get_string('bugnote_stats_to', '');
	$f_bugnote_cost = gpc_get_string('bugnote_cost', '');
	$f_get_bugnote_stats_button = gpc_get_string('get_bugnote_stats_button', '');
	$f_project_id = helper_get_current_project();

	if ( config_get('time_tracking_with_billing') )
		$t_cost_col = true;
	else
		$t_cost_col = false;
?>
<form method="post" action="<?php echo $PHP_SELF ?>">
<table border=0 class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onClick="ToggleDiv( 'bugnotestats', g_div_bugnotestats ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
<?php } ?>
		<?php echo lang_get( 'time_tracking' ) ?>
	</td>
</tr>
<tr class="row-2">
        <td class="category" width="25%">
                <?php echo lang_get( 'from_date' ). " (mm/dd/yyyy)"; ?>
        </td>
        <td width="75%">
                <input type="text" name="bugnote_stats_from" value="<?php echo $f_bugnote_stats_from ?>" />
        </td>
</tr>
<tr class="row-1">
        <td class="category">
                <?php echo lang_get( 'to_date' ). " (mm/dd/yyyy)"; ?>
        </td>
        <td>
                <input type="text" name="bugnote_stats_to" value="<?php echo $f_bugnote_stats_to ?>" />
        </td>
</tr>
<?php if ($t_cost_col) { ?>
<tr class="row-2">
        <td class="category">
                <?php echo lang_get( 'time_tracking_cost_per_hour' ); ?>
        </td>
        <td>
                <input type="text" name="bugnote_cost" value="<?php echo $f_bugnote_cost ?>" />
        </td>
</tr>
<?php } ?>
<tr>
        <td class="center" colspan="2">
                <input type="submit" class="button" name="get_bugnote_stats_button" value="<?php echo lang_get( 'time_tracking_get_info_button' ) ?>" />
        </td>
</tr>

</table>
</form>
<?php
if ( "" != $f_get_bugnote_stats_button ) {
	$t_bugnote_stats = bugnote_stats_get_project_array( $f_project_id, $f_bugnote_stats_from, $f_bugnote_stats_to, $f_bugnote_cost );

if ( $f_bugnote_cost == "" )
	$t_cost_col = false;
?>
<table border=0 class="width100" cellspacing="0">
<?php $t_prev_id = -1; ?>
<?php foreach ( $t_bugnote_stats as $t_item ) { ?>
<?php
	$t_item['sum_time_tracking'] = db_minutes_to_hhmm( $t_item['sum_time_tracking'] );
	if ( $t_item['bug_id'] != $t_prev_id) {
		$t_link = bug_format_summary( $t_item['bug_id'], SUMMARY_CAPTION );
		//$t_link = string_get_bug_view_link( $t_item['bug_id'] , null, true);
		echo "<tr class='row-category-history'><td colspan=4>".$t_link."</td></tr>";
?>
<tr class="row-category-history">
	<td class="small-caption">&nbsp;</td>
	<td class="small-caption"><?php echo lang_get( 'username' ) ?></td>
	<td class="small-caption"><?php echo lang_get( 'time_tracking' ) ?></td>
<?php if ( $t_cost_col) { ?>
	<td class="small-caption"><?php echo lang_get( 'time_tracking_cost' ) ?></td>
<?php } ?>
</tr>
<?php
		$t_prev_id = $t_item['bug_id'];
	}
?>

<tr <?php echo helper_alternate_class() ?>>
	<td class="small-caption">&nbsp;</td>
	<td class="small-caption">
		<?php echo $t_item['username'] ?>
	</td>
	<td class="small-caption">
		<?php echo $t_item['sum_time_tracking'] ?>
	</td>
<?php if ($t_cost_col) { ?>
	<td class="small-caption">
		<?php echo $t_item['cost'] ?>
	</td>
<?php } ?>
</tr>
<?php } # end for?>
</table>
<?php } # end if f_get_bugnote_stats_button?>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/JavaScript">
	SetDiv( "bugnotestats", g_div_bugnotestats );
</script>
<?php } ?>
