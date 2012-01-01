<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

if ( !defined( 'BUGNOTE_ADD_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

?>
<?php if ( ( !bug_is_readonly( $f_bug_id ) ) &&
		( access_has_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>
<a id="addbugnote"></a> <br />

<?php
	collapse_open( 'bugnote_add' );
?>
<form name="bugnoteadd" method="post" action="bugnote_add.php">
<?php echo form_security_field( 'bugnote_add' ) ?>
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
<?php
	collapse_icon( 'bugnote_add' );
	echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
<tr class="row-2">
	<th class="category" width="25%">
		<?php echo lang_get( 'bugnote' ) ?>
	</th>
	<td width="75%">
		<textarea name="bugnote_text" cols="80" rows="10"></textarea>
	</td>
</tr>
<?php if ( access_has_bug_level( config_get( 'set_view_status_threshold' ), $f_bug_id ) ) { ?>
<tr class="row-1">
	<th class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</th>
	<td>
<?php
		$t_default_bugnote_view_status = config_get( 'default_bugnote_view_status' );
		if ( access_has_bug_level( config_get( 'set_view_status_threshold' ), $f_bug_id ) ) {
?>
			<input type="checkbox" id="bugnote_add_view_status" name="private" <?php check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
			<label for="bugnote_add_view_status"><?php echo lang_get( 'private' ) ?></label>
<?php
		} else {
			echo get_enum_element( 'project_view_state', $t_default_bugnote_view_status );
		}
?>
	</td>
</tr>
<?php } ?>

<?php if ( config_get('time_tracking_enabled') ) { ?>
<?php if ( access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $f_bug_id ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<th class="category">
		<?php echo lang_get( 'time_tracking' ) ?>
	</th>
	<td>
		<?php if ( config_get( 'time_tracking_stopwatch' ) && config_get( 'use_javascript' ) ) { ?>
		<input type="text" name="time_tracking" class="stopwatch_time" size="8" placeholder="hh:mm:ss" />
		<input type="button" name="time_tracking_toggle" class="stopwatch_toggle" value="<?php echo lang_get( 'time_tracking_stopwatch_start' ) ?>" />
		<input type="button" name="time_tracking_reset" class="stopwatch_reset" value="<?php echo lang_get( 'time_tracking_stopwatch_reset' ) ?>" />
		<?php } else { ?>
		<input type="text" name="time_tracking" size="5" placeholder="hh:mm" />
		<?php } ?>
	</td>
</tr>
<?php } ?>
<?php } ?>

<?php event_signal( 'EVENT_BUGNOTE_ADD_FORM', array( $f_bug_id ) ); ?>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_bugnote_button' ) ?>" />
	</td>
</tr>
</table>
</form>
<?php
	collapse_closed( 'bugnote_add' );
?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
	<?php	collapse_icon( 'bugnote_add' );
		echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
</table>
<?php
	collapse_end( 'bugnote_add' );
?>

<?php # Bugnote Add Form END ?>
<?php
}
