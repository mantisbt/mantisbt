<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_stats_inc.php,v 1.1 2006-12-12 18:26:28 davidnewcomb Exp $
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
		<?php echo lang_get( 'time_tracking_bugnote_stats' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="bugnotestats_open">
<?php
	$t_bugnote_stats_from_def = date( "m/d/Y", $t_bug->date_submitted );
	$f_bugnote_stats_from = gpc_get_string('bugnote_stats_from', $t_bugnote_stats_from_def);
	$f_bugnote_stats_to = gpc_get_string('bugnote_stats_to', date("d/m/Y"));
	$f_get_bugnote_stats_button = gpc_get_string('get_bugnote_stats_button', '');
?>
<form method="post" action="<?php echo $PHP_SELF ?>">
<input type="hidden" name="id" value="<?php echo $f_bug_id ?>" />
<table border=0 class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onClick="ToggleDiv( 'bugnotestats', g_div_bugnotestats ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
<?php } ?>
		<?php echo lang_get( 'time_tracking_bugnote_stats' ) ?>
	</td>
</tr>
<tr class="row-2">
        <td class="category" width="25%">
                <?php echo lang_get( 'from' ). " (mm/dd/yyyy)"; ?>
        </td>
        <td width="75%">
                <input type="text" name="bugnote_stats_from" value="<?php echo $f_bugnote_stats_from ?>" />
        </td>
</tr>
<tr class="row-1">
        <td class="category">
                <?php echo lang_get( 'to' ). " (mm/dd/yyyy)"; ?>
        </td>
        <td>
                <input type="text" name="bugnote_stats_to" value="<?php echo $f_bugnote_stats_to ?>" />
        </td>
</tr>
<tr>
        <td class="center" colspan="2">
                <input type="submit" class="button" name="get_bugnote_stats_button" value="<?php echo lang_get( 'time_tracking_get_bugnote_stats_button' ) ?>" />
        </td>
</tr>

</table>
</form>
<?php
if ( "" != $f_get_bugnote_stats_button ) {
	$t_bugnote_stats = bugnote_stats_get_events_array( $f_bug_id, $f_bugnote_stats_from, $f_bugnote_stats_to );
?>
<table border=0 class="width100" cellspacing="0">
<tr class="row-category-history">
	<td class="small-caption">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'time_tracking' ) ?>
	</td>
</tr>
<?php foreach ( $t_bugnote_stats as $t_item ) { ?>
<?php
if ( "" == $t_item['sum_time_tracking'] ) {
	$t_item['sum_time_tracking'] = "&nbsp;";
}
?>

<tr <?php echo helper_alternate_class() ?>>
	<td class="small-caption">
		<?php echo $t_item['username'] ?>
	</td>
	<td class="small-caption">
		<?php echo $t_item['sum_time_tracking'] ?>
	</td>
</tr>
<?php } # end for loop ?>
</table>
<?php } # end if ?>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/JavaScript">
	SetDiv( "bugnotestats", g_div_bugnotestats );
</script>
<?php } ?>
