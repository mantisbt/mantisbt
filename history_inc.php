<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This include file prints out the bug history
?>
<?php # Bug History BEGIN ?>
<a name="history"><br /></a>
<?php
	$history = history_get_events_array( $f_bug_id );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
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
	for ( $i=0; $i < count( $history ); $i++ ) {
?>
<tr <?php echo helper_alternate_class( $i ) ?>>
	<td class="small-caption">
		<?php echo $history[$i]['date'] ?>
	</td>
	<td class="small-caption">
		<?php echo $history[$i]['username'] ?>
	</td>
	<td class="small-caption">
		<?php echo $history[$i]['note'] ?>
	</td>
	<td class="small-caption">
		<?php echo $history[$i]['change'] ?>
	</td>
</tr>
<?php
	} # end for loop
?>
</table>
<?php # Bug History END ?>
