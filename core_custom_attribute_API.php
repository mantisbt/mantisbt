<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Category API
	###########################################################################
	# --------------------
	# return the custom list for the specified project id and the specified attribute
	function attribute_get_all( $p_parameter, $p_project_id ) {
		global $g_mantis_project_customization_table;

		$c_project_id = (integer)$p_project_id;
		$c_parameter	= addslashes($p_parameter);

		$query = "SELECT $c_parameter
				FROM $g_mantis_project_customization_table
				WHERE project_id='$c_project_id'";
		$result = db_query( $query );
		$row = db_fetch_array( $result );
		return explode_enum_string( $row[0] );
	}

	# --------------------
	# checks to see if the attribute is a duplicate
	# we do it this way because each different project can have the same attribute names
	# The old attribute name is excluded from the search for duplicate since an attribute
	# can re-take its name.  It is also useful when changing the case of an attribute name.
	# For example, "attribute" -> "Attribute".
	function is_duplicate_attribute( $p_parameter, $p_project_id, $p_attribute , $p_old_attribute = '' ) {
		global $g_mantis_project_customization_table;

		$c_project_id	= (integer)$p_project_id;
		$c_attribute	= addslashes($p_attribute);
		$c_parameter	= addslashes($p_parameter);

		$t_old_flag = 0 ;
		if (strlen($p_old_attribute) != 0) {
			$c_old_attribute = addslashes($p_old_attribute);
			$t_old_flag = 1;
		}
		# get all the attributes
		$attribute_count = 0 ;
		$t_attribute_arr = attribute_get_all($c_parameter, $p_project_id );
		$att_arr_count = count($t_attribute_arr);

		for ($i=0 ; $i<$att_arr_count ; $i++) {
			if ( strtolower($p_attribute) == strtolower($t_attribute_arr[$i]) ) {
				$attribute_count++ ;
				if ($t_old_flag && ($p_old_attribute == $t_attribute_arr[$i]) )  {
					$attribute_count-- ;
				}
			}
		}
		return ( $attribute_count > 0 );
	}

	# --------------------
	function attribute_add($p_parameter, $p_project_id, $p_attribute ) {
		global $g_mantis_project_customization_table;

		$c_project_id		= (integer)$p_project_id;
		$c_attribute		= addslashes($p_attribute);
		$c_parameter		= addslashes($p_parameter);

		$t_attribute_arr = attribute_get_all($c_parameter, $c_project_id );
		$att_arr_count = count($t_attribute_arr);

		# Grab the data to test if the table is empty
		$query = "SELECT $c_parameter
				FROM $g_mantis_project_customization_table
				WHERE project_id='$c_project_id'";
		$result = db_query($query);

		## OOPS, No entry in the database yet.  Lets make one, used for copy attribute too
		if ( 0 == db_num_rows( $result ) ) {

			# add attribute field and project id
			$query = "INSERT
					INTO $g_mantis_project_customization_table
					(project_id)
					VALUES
					('$c_project_id')";

			$result = db_query($query);
		}
		if ($t_attribute_arr[0]=='') {  # entry created for the 1st time, remove the ''
			$t_attribute_arr[0] = $c_attribute ;
		}
		else {
			$t_attribute_arr[$att_arr_count+1] = $c_attribute ;
		}

		# convert the array in a string for export
		$t_attribute_str = implode(',',$t_attribute_arr);

		$query = "UPDATE $g_mantis_project_customization_table
				SET $c_parameter='$t_attribute_str'
				WHERE project_id='$c_project_id'";
		return db_query( $query );
	}

	# --------------------
	function attribute_update( $p_parameter, $p_project_id, $p_attribute, $p_orig_attribute, $p_color='' ) {
		global $g_mantis_project_customization_table, $s_states;

		$c_project_id		= (integer)$p_project_id;
		$c_attribute		= addslashes($p_attribute);
		$c_orig_attribute	= addslashes($p_orig_attribute);
		$c_parameter		= addslashes($p_parameter);
		$t_color_req = '';
		$t_index = 0 ;

		$t_attribute_arr = attribute_get_all($c_parameter, $c_project_id );
		foreach($t_attribute_arr as $key=>$val)
		{
			if ($val == $p_orig_attribute) {
				$t_attribute_arr[$key] = $p_attribute ;
				$t_index = $key;
				break ;
			}
		}
		if ( ($p_parameter == $s_states) && ($p_color != '') ) {
			$t_color_arr = attribute_get_all('colors', $c_project_id) ;
			$t_color_arr[$t_index] = $p_color ;
			$t_color_str = implode(',',$t_color_arr);
			$t_color_req = ", colors='$t_color_str'";
		}

		# convert the array in a string for export
		$t_attribute_str = implode(',',$t_attribute_arr);

		$query = "UPDATE $g_mantis_project_customization_table".
				" SET $c_parameter='$t_attribute_str'".$t_color_req.
				" WHERE project_id='$c_project_id'";
		return db_query( $query );
	}

	# --------------------
	function attribute_delete( $p_parameter, $p_project_id, $p_attribute ) {
		global $g_mantis_project_customization_table, $s_states, $g_custom_status_slot, $g_mantis_bug_table ;

		$c_project_id	= (integer)$p_project_id;
		$c_attribute	= addslashes($p_attribute);
		$c_parameter	= addslashes($p_parameter);
		$t_color_req = '';

		# get the modified array
		$t_attribute_arr = attribute_get_all($c_parameter, $c_project_id );
		$att_arr_count = count($t_attribute_arr);
		$t_offset = -1 ; # the position of the element to delete

		for ($i=0 ; $i < $att_arr_count ; $i++) {
			if ($t_attribute_arr[$i] == $p_attribute) {
				$t_offset = $i ;
				break ;
			}
		}
		array_splice( $t_attribute_arr, $t_offset, 1);

		# color treatment for custom states
		if ($p_parameter == $s_states)  {
			$t_color_arr = attribute_get_all('colors', $c_project_id) ;
			array_splice( $t_color_arr, $t_offset, 1);
			$t_color_str = implode(',',$t_color_arr);
			$t_color_req = ", colors='$t_color_str'";
		}

		# convert the array in a string for export
		$t_attribute_str = implode(',',$t_attribute_arr);
		# update the modified array in the database
		$query = "UPDATE $g_mantis_project_customization_table".
				" SET $c_parameter='$t_attribute_str'".$t_color_req.
				" WHERE project_id='$c_project_id'";

		# update mantis_bug table
		# the bug with the deleted status is now set as @null@
		# the status above are decreased by 1
		if ($p_parameter == $s_states)  {

			#slot[0] : the unused one ; index : the deleted bug ; index_up : the end of slot
			$t_status_index = $g_custom_status_slot[0] + 1 + $t_offset ;
			$t_status_index_up = $g_custom_status_slot[1] ;

			$query2 = "UPDATE $g_mantis_bug_table
				SET status=$g_custom_status_slot[0]
				WHERE status=$t_status_index";
			$result2 = db_query($query2);

			$query3 = "UPDATE $g_mantis_bug_table
				SET status=status-1
				WHERE status >$t_status_index
				AND status < $t_status_index_up";
			$result3 = db_query($query3);
		}

		return db_query( $query );

	}

	# --------------------
	function attribute_display( $p_parameter, $s_title, $p_project_id) {
		global $s_attribute_customization_title, $s_edit_link, $s_delete_link, $s_add_button, $s_copy_from, $s_copy_to, $s_asso_color, $s_states ;
	?>
	<tr class="row-category">
		<td width="100%">
			<?php echo ucfirst($p_parameter); ?>
		</td>
	</tr>
	<tr>
		<td width="100%">
			<table width="100%" cellspacing="1">
			<?php
				$t_attribute_arr = attribute_get_all($p_parameter, $p_project_id );
				$att_arr_count = count( $t_attribute_arr );
				for ($i=0;$i<$att_arr_count;$i++) {

					$t_attribute = $t_attribute_arr[$i];
					$t2_attribute = urlencode( $t_attribute );

					# alternate row colors
					$t_bgcolor = alternate_colors( $i );

					# attribute are not initialized the 1st time
					# so the blank line isn't displayed
					if ($t_attribute != '') {
			?>
			<tr>
				<td width="50%" bgcolor="<?php echo $t_bgcolor ?>">
					<?php echo $t_attribute ?>
				</td>
				<td class="center" width="25%" bgcolor="<?php echo $t_bgcolor ?>">
					<?php
				print_bracket_link('manage_proj_attribute_edit_page.php?f_project_id='.$p_project_id."&amp;f_parameter=$p_parameter&amp;f_attribute=".$t2_attribute, $s_edit_link );
				PRINT '&nbsp;';
				print_bracket_link('manage_proj_attribute_del_page.php?f_project_id='.$p_project_id."&amp;f_parameter=$p_parameter&amp;f_attribute=".$t2_attribute, $s_delete_link );
					?>
				</td>
			</tr>
			<?php 		} # end for if
					} # end for loop ?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="left">
			<form method="get" action="manage_proj_attribute_add.php">
			<input type="hidden" name="f_project_id" value="<?php echo $p_project_id ?>">
			<input type="text" name="f_attribute" size="32" maxlength="64">
			<?php if ($p_parameter == $s_states) {
				echo $s_asso_color ?>
				<select name="f_color">
				<?php print_custom_status_color_list() ?>
				</select>
			<?php } #if color ?>
			<input type="hidden" name="f_parameter" value="<?php echo $p_parameter ?>">
			<input type="submit" value="<?php echo $s_add_button.' '.$s_title ?>">
			</form>
		</td>
	</tr>
	<?php # allow copies through different projects. Enable this when Per Project Settings will be done ?>
	<!--
	<?php if ($t_attribute != '') {  ?>
	<tr>
		<td class="left">
			<form method="post" action="manage_proj_attribute_copy.php">
			<input type="hidden" name="f_project_id" value="<?php echo $p_project_id ?>">
			<input type="hidden" name="f_parameter" value="<?php echo $p_parameter ?>">
			<select name="f_other_project_id">
				<?php print_project_option_list() ?>
			</select>
			<input type="submit" name="f_copy_from" value="<?php echo $s_copy_from ?>">
			<input type="submit" name="f_copy_to" value="<?php echo $s_copy_to ?>">
			</form>
		</td>
	</tr>
	<?php }  #end if  ?>
	-->
	<tr>
		<td>
			<hr />
		</td>
	</tr>
	<?php
	} # end of attribute_display

	# --------------------
	# function used in core_helper.api and core_print_api to display the custom attributes
	# $p_selector is either 'global' for global varis treatment, or 'str' for string treatments
	function insert_attributes( $p_enum_name, $p_project_id, $p_selector ) {

		$t_prefix = '';
		# if the function is called for anything that printing attributes, do nothing
		$t_continue = 0;
		switch ($p_selector) {
			case 'global' :
				$t_prefix = 'g_';
				break ;
			case 'str' :
				$t_prefix = 's_';

				break ;
		}
		$t_var = $t_prefix.$p_enum_name.'_enum_string';
		global $$t_var, $g_custom_status_slot;

		$p_parameter = '';
		switch ($p_enum_name) {
			case 'priority' :
				$p_parameter = 'priorities' ;
				$t_continue = 1;
				break;
			case 'severity' :
				$p_parameter = 'severities' ;
				$t_continue = 1;
				break;
			case 'reproducibility' :
				$p_parameter = 'reproducibilities' ;
				$t_continue = 1;
				break;
			case 'status' :
				$p_parameter = 'states' ;
				$t_continue = 1;
				break;
			case 'resolution' :
				$p_parameter = 'resolutions' ;
				$t_continue = 1;
				break;
			case 'projection' :
				$p_parameter = 'projections' ;
				$t_continue = 1;
				break;
			case 'eta' :
				$p_parameter = 'etas' ;
				$t_continue = 1;
				break;
		}
		if ($t_continue) {
			$c_project_id = (integer)$p_project_id;
			$c_parameter  = addslashes($p_parameter);

			# get the selected attribute array
			$t_attribute_arr = attribute_get_all($c_parameter, $p_project_id );
			$att_arr_count = count($t_attribute_arr);

			# status attribute has a particular status, custom fields are inserted between 60 and the resolved status (80)
			if ( ( $t_var == ($t_prefix.'status_enum_string') ) ) {

				# checks if the strings are already included
				if ( !ereg(($g_custom_status_slot[0] + 1),$$t_var) )  {
					for ($i = 0 ; $i < $att_arr_count ; $i++) {
						$pos = strpos(($$t_var), ',80');
						# Prevent empty values
						if ($t_attribute_arr[$i]) {
							$$t_var = substr($$t_var,0,$pos).','.($g_custom_status_slot[0]+1+$i).':'.$t_attribute_arr[$i].substr($$t_var,$pos);
						}
					} # for loop
				} # if ereg
			} else {   // for other attributes, custom ones are appended after the others, starting with number 110
				$attribute_index = '110' ;
				# checks if the strings are already included
				if ( !ereg($attribute_index,$$t_var) ) {
					for ($i = 0 ; $i < $att_arr_count ; $i++) {
						$$t_var = $$t_var.','.($attribute_index+$i).':'.$t_attribute_arr[$i];
					} # for loop
				} # if ereg
			} # if status
		}# if continue
	} # end of insert_attributes

?>