<?php

function json_auth_get_current_project_id($json_message) {
	global $g_cache_current_project;

	$project_id = $json_message->project_id;
	if (!$project_id) {
		$project_id = helper_get_current_project();
	}
	if($project_id >0)
		$g_cache_current_project = (int) $t_project_id;

	return $project_id;
}

function json_auth_get_current_user_id($json_message) {
	global $g_cache_current_user_id, $g_cache_cookie_valid;

	$user_token = $json_message->user_token;
	//list($user_id , $user_pw) = explode($user_token

	$t_user_table = db_get_table( 'user' );
	//support older versions
	if (empty($t_user_table) ) {
		$t_user_table = db_get_table('mantis_user_table');
	}

	/** @todo error with an error saying they aren't logged in? Or redirect to the login page maybe? */
	$query = "SELECT id
				  FROM $t_user_table
				  WHERE MD5( CONCAT(username, ':', password))=" . db_param();
	$result = db_query_bound( $query, Array( $user_token ) );

	$t_user_id = (int) db_result( $result );
	if ($t_user_id > 0 ) {
		$g_cache_current_user_id = $t_user_id;
		$g_cache_cookie_valid = true;
	}
	return $t_user_id;
}

function json_access_denied() {
	header('Content-type: application/json');
	echo json_encode( array('result'=>'fail') );
}


function json_get_string($key) {
	global $g_json_message;
	return (isset($g_json_message->{$key}))? $g_json_message->{$key}: "";
}
function json_get_int($key) {
	global $g_json_message;
	return (isset($g_json_message->{$key}))? (int)$g_json_message->{$key}: 0;
}
function json_get_bool($key) {
	global $g_json_message;
	return (isset($g_json_message->{$key}))? true:false;
}

function json_get_file( $p_var_name, $p_default = null ) {
	global $g_json_message, $g_json_file_tmp_name;


	$file_bin      = '';
	$file_name     = '';
	$file_tmp_name = '';
	$file_type     = '';

	//file type
	if( isset( $g_json_message->{$p_var_name.'_type'} ) ) {
		$file_type = $g_json_message->{$p_var_name.'_type'};
	}

	//file name
	if( isset( $g_json_message->{$p_var_name.'_name'} ) ) {
		$file_name = $g_json_message->{$p_var_name.'_name'};
	}
	//binary
	if( isset( $g_json_message->{$p_var_name.'_base64'} ) ) {
		$file_bin = base64_decode($g_json_message->{$p_var_name.'_base64'});
	}
	if( isset( $g_json_message->{$p_var_name.'_raw'} ) ) {
		$file_bin = base64_decode($g_json_message->{$p_var_name.'_raw'});
	}


	//file tmp name
	$file_tmp_name  = tempnam('/tmp', 'mantis_');
	$f = @fopen($file_tmp_name, 'wb');
   	if (!$f) {
		return NULL;
	}
	fwrite($f, $file_bin, strlen($file_bin));
	fclose($f);
	$g_json_file_tmp_name = $file_tmp_name;

	$file_struct = array();
	$file_struct['name'] = $file_name;
	$file_struct['type'] = $file_type;
	$file_struct['tmp_name'] = $file_tmp_name;
	$file_struct['size'] = strlen($file_bin);
	unset($file_bin);

	return $file_struct;
}

function json_isset( $p_var_name ) {
	global $g_json_message;
	return (isset($g_json_message->{$p_var_name})) ? true : false;
}

/**
 * see if a custom field variable is set.  Uses json_isset().
 * @param string $p_var_name
 * @param int $p_custom_field_type
 * @return bool
 */
function json_isset_custom_field( $p_var_name, $p_custom_field_type ) {
	$t_field_name = 'custom_field_' . $p_var_name;

	switch ($p_custom_field_type ) {
		case CUSTOM_FIELD_TYPE_DATE:
			// date field is three dropdowns that default to 0
			// Dropdowns are always present, so check if they are set
			return json_isset( $t_field_name . '_day' ) &&
				json_get_int( $t_field_name . '_day', 0 ) != 0 &&
				json_isset( $t_field_name . '_month' ) &&
				json_get_int( $t_field_name . '_month', 0 ) != 0 &&
				json_isset( $t_field_name . '_year' ) &&
				json_get_int( $t_field_name . '_year', 0 ) != 0 ;
		case CUSTOM_FIELD_TYPE_STRING:
		case CUSTOM_FIELD_TYPE_NUMERIC:
		case CUSTOM_FIELD_TYPE_FLOAT:
		case CUSTOM_FIELD_TYPE_ENUM:
		case CUSTOM_FIELD_TYPE_EMAIL:
			return json_isset( $t_field_name ) && !is_blank( json_get_string( $t_field_name ) );
		default:
			return json_isset( $t_field_name );
	}
}


/**
 * Retrieve a custom field variable.  Uses json_get().
 * If you pass in *no* default, an error will be triggered if
 * the variable does not exist
 * @param string $p_var_name
 * @param int $p_custom_field_Type
 * @param mixed $p_default
 * @return string
 */
function json_get_custom_field( $p_var_id, $p_var_name, $p_custom_field_type, $p_default = null ) {
	if (json_isset('custom_field_' . $p_var_name)) {
		$t_field_name = 'custom_field_' . $p_var_name;
	} else {
		$t_field_name = 'custom_field_' . $p_var_id;
	}

	switch( $p_custom_field_type ) {
		case CUSTOM_FIELD_TYPE_MULTILIST:
		case CUSTOM_FIELD_TYPE_CHECKBOX:
		    // ensure that the default is an array, if set
		    if ( ($p_default !== null) && !is_array($p_default) ) {
		        $p_default = array( $p_default );
		    }
			$t_values = json_get( $t_field_name, $p_default );
			if( is_array( $t_values ) ) {
				return implode( '|', $t_values );
			} else {
				return '';
			}
			break;
		case CUSTOM_FIELD_TYPE_DATE:
			$t_day = json_get_int( $t_field_name . '_day', 0 );
			$t_month = json_get_int( $t_field_name . '_month', 0 );
			$t_year = json_get_int( $t_field_name . '_year', 0 );
			if(( $t_year == 0 ) || ( $t_month == 0 ) || ( $t_day == 0 ) ) {
				if( $p_default == null ) {
					return '';
				} else {
					return $p_default;
				}
			} else {
				return strtotime( $t_year . '-' . $t_month . '-' . $t_day );
			}
			break;
		default:
			return json_get_string( $t_field_name, $p_default );
	}
}


/**
 * JSON API
 * ---------------
 * Retrieve a JSON variable.
 * If the variable is not set, the default is returned.
 *
 *  You may pass in any variable as a default (including null) but if
 *  you pass in *no* default then an error will be triggered if the field
 *  cannot be found
 *
 * @param string
 * @return null
 */
function json_get( $p_var_name, $p_default = null ) {
	global $g_json_message;
	return (isset($g_json_message->{$p_var_name}))? $g_json_message->{$p_var_name}: $p_default;
}

function json_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {

	if ($p_type == E_USER_ERROR) {
		header('Content-type: application/json');
		$t_error_description = error_string( $p_error );
		echo json_encode( array('result'=>'error', 'message'=>$t_error_description) );

		json_exit();
	}
}


function json_exit() {
exit();
}
