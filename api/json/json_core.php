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

function json_exit() {
	global $g_json_file_tmp_name;

	if ($g_json_file_tmp_name != '') {
		@unlink($g_json_file_tmp_name);
	}
	exit();
}

function json_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {

	if ($p_type == E_USER_ERROR) {
		header('Content-type: application/json');
		$t_error_description = error_string( $p_error );
		echo json_encode( array('result'=>'error', 'message'=>$t_error_description) );

		json_exit();
	}
}

