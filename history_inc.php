<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This include file prints out the bug history

	# $f_bug_id must already be defined
?>

<a name="history"><br /></a>
<?php
	$t_history = history_get_events_array( $f_bug_id );
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
	foreach ( $t_history as $t_item ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="small-caption">
		<?php echo $t_item['date'] ?>
	</td>
	<td class="small-caption">
		<?php echo $t_item['username'] ?>
	</td>
	<td class="small-caption">
		<?php echo $t_item['note'] ?>
	</td>
	<td class="small-caption">
		<?php echo $t_item['change'] ?>
	</td>
</tr>
<?php
	} # end for loop
?>
</table>

