<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: history_inc.php,v 1.23 2004-06-29 08:38:43 int2str Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the bug history

	# $f_bug_id must already be defined
?>
<?php
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'history_api.php' );
?>

<a name="history" id="history" /><br />

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="history_closed" style="display: none;">
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
		<a href="" onClick="ToggleDiv( 'history', g_div_history ); return false;"
		><img border="0" src="images/plus.png" alt="+" /></a>
		<?php echo lang_get( 'bug_history' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="history_open">
<?php
	$t_history = history_get_events_array( $f_bug_id );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onClick="ToggleDiv( 'history', g_div_history ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
<?php } ?>
		<?php echo lang_get( 'bug_history' ) ?>
	</td>
</tr>
<tr class="row-category">
	<td class="small-caption">
		<?php echo lang_get( 'date_modified' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'field' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'change' ) ?>
	</td>
</tr>
<?php
	foreach ( $t_history as $t_item ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="small-caption">
		<?php echo $t_item['date'] ?>
	</td>
	<td class="small-caption">
		<?php print_user( $t_item['userid'] ) ?>
	</td>
	<td class="small-caption">
		<?php echo string_display( $t_item['note'] ) ?>
	</td>
	<td class="small-caption">
		<?php echo string_display( $t_item['change'] ) ?>
	</td>
</tr>
<?php
	} # end for loop
?>
</table>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/JavaScript">
	SetDiv( "history", g_div_history );
</script>
<?php } ?>
