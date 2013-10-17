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
 * MantisBT schema definition
 * The schema is defined a list of schema updates, stored as an array.
 *
 * Each upgrade step consists of two elements:
 * 1. The function to generate SQL statements. Available functions (from ADOdb
 *    library) are:
 *      CreateTableSQL, DropTableSQL, ChangeTableSQL, RenameTableSQL,
 *      RenameColumnSQL, DropTableSQL, ChangeTableSQL, RenameTableSQL,
 *      RenameColumnSQL, AlterColumnSQL, DropColumnSQL
 *    A local function "InsertData" has been provided to add data to the db
 *
 * 2. An array of the parameters to be passed to the function.
 *
 * The integrity of the schema relies on strict ordering of this array.
 * - ONLY ADD NEW CHANGES TO THE END OF THE TABLE!!!
 * - NEVER SKIP AN INDEX IN THE SEQUENCE!!!
 */

if ( !function_exists( 'db_null_date' ) ) {
	/**
	 * Legacy null date function for installer backwards compatibility
	 */
	function db_null_date() {
		return 0;
	}
}


function installer_db_now() {
	global $g_db;

	return $g_db->BindTimeStamp( time() );
}

# Special handling for Oracle (oci8):
# - Field cannot be null with oci because empty string equals NULL
# - Oci uses a different date literal syntax
# - Default BLOBs to empty_blob() function
if( db_is_oracle() ) {
	$t_notnull = "";
	$t_timestamp = "timestamp" . installer_db_now();
	$t_blob_default = 'DEFAULT " empty_blob() "';
} else {
	$t_notnull = 'NOTNULL';
	$t_timestamp = "'" . installer_db_now() . "'";
	$t_blob_default = '';
}

/**
 * Begin schema definition
 *
 * 'Release markers' are placed right AFTER the last schema step that is
 * included in the corresponding release
 */
$upgrade[  0] = array('CreateTableSQL',array(db_get_table( 'config' ),"
			  config_id C(64) NOTNULL PRIMARY,
			  project_id I DEFAULT '0' PRIMARY,
			  user_id I DEFAULT '0' PRIMARY,
			  access_reqd I DEFAULT '0',
			  type I DEFAULT '90',
			  value XL NOTNULL",
array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[  1] = array('CreateIndexSQL',array('idx_config',db_get_table( 'config' ),'config_id'));
$upgrade[  2] = array('CreateTableSQL',array(db_get_table('bug_file'),"
  id			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT \" '' \",
  description 		C(250) NOTNULL DEFAULT \" '' \",
  diskfile 		C(250) NOTNULL DEFAULT \" '' \",
  filename 		C(250) NOTNULL DEFAULT \" '' \",
  folder 		C(250) NOTNULL DEFAULT \" '' \",
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT \" '' \",
  date_added 		T NOTNULL DEFAULT '" . db_null_date() . "',
  content 		B NOTNULL $t_blob_default
  ",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[  3] = array('CreateIndexSQL',array('idx_bug_file_bug_id',db_get_table('bug_file'),'bug_id'));
$upgrade[  4] = array('CreateTableSQL',array(db_get_table('bug_history'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  field_name 		C(32) $t_notnull DEFAULT \" '' \",
  old_value 		C(128) $t_notnull DEFAULT \" '' \",
  new_value 		C(128) $t_notnull DEFAULT \" '' \",
  type 			I2 NOTNULL DEFAULT '0'
  ",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[  5] = array('CreateIndexSQL',array('idx_bug_history_bug_id',db_get_table('bug_history'),'bug_id'));
$upgrade[  6] = array('CreateIndexSQL',array('idx_history_user_id',db_get_table('bug_history'),'user_id'));
$upgrade[  7] = array('CreateTableSQL',array(db_get_table('bug_monitor'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[  8] = array('CreateTableSQL',array(db_get_table('bug_relationship'),"
  id 			 I  UNSIGNED NOTNULL AUTOINCREMENT PRIMARY,
  source_bug_id		 I  UNSIGNED NOTNULL DEFAULT '0',
  destination_bug_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  relationship_type 	I2 NOTNULL DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[  9] = array('CreateIndexSQL',array('idx_relationship_source',db_get_table('bug_relationship'),'source_bug_id'));

# ----------------------------------------------------------------------------
# Schema version: 10
#
$upgrade[ 10] = array('CreateIndexSQL',array('idx_relationship_destination',db_get_table('bug_relationship'),'destination_bug_id'));
$upgrade[ 11] = array('CreateTableSQL',array(db_get_table('bug'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  reporter_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  handler_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  duplicate_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  priority 		I2 NOTNULL DEFAULT '30',
  severity 		I2 NOTNULL DEFAULT '50',
  reproducibility 	I2 NOTNULL DEFAULT '10',
  status 		I2 NOTNULL DEFAULT '10',
  resolution 		I2 NOTNULL DEFAULT '10',
  projection 		I2 NOTNULL DEFAULT '10',
  category 		C(64) NOTNULL DEFAULT \" '' \",
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_updated 		T NOTNULL DEFAULT '" . db_null_date() . "',
  eta 			I2 NOTNULL DEFAULT '10',
  bug_text_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  os 			C(32) NOTNULL DEFAULT \" '' \",
  os_build 		C(32) NOTNULL DEFAULT \" '' \",
  platform 		C(32) NOTNULL DEFAULT \" '' \",
  version 		C(64) NOTNULL DEFAULT \" '' \",
  fixed_in_version 	C(64) NOTNULL DEFAULT \" '' \",
  build 		C(32) NOTNULL DEFAULT \" '' \",
  profile_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  summary 		C(128) NOTNULL DEFAULT \" '' \",
  sponsorship_total 	 I  NOTNULL DEFAULT '0',
  sticky		L  $t_notnull DEFAULT  \"'0'\"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 12] = array('CreateIndexSQL',array('idx_bug_sponsorship_total',db_get_table('bug'),'sponsorship_total'));
$upgrade[ 13] = array('CreateIndexSQL',array('idx_bug_fixed_in_version',db_get_table('bug'),'fixed_in_version'));
$upgrade[ 14] = array('CreateIndexSQL',array('idx_bug_status',db_get_table('bug'),'status'));
$upgrade[ 15] = array('CreateIndexSQL',array('idx_project',db_get_table('bug'),'project_id'));
$upgrade[ 16] = array('CreateTableSQL',array(db_get_table('bug_text'),"
  id 			 I  PRIMARY UNSIGNED NOTNULL AUTOINCREMENT,
  description 		XL NOTNULL,
  steps_to_reproduce 	XL $t_notnull,
  additional_information XL $t_notnull
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 17] = array('CreateTableSQL',array(db_get_table('bugnote'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  reporter_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bugnote_text_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  note_type 		 I  DEFAULT '0',
  note_attr 		C(250) DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 18] = array('CreateIndexSQL',array('idx_bug',db_get_table('bugnote'),'bug_id'));
$upgrade[ 19] = array('CreateIndexSQL',array('idx_last_mod',db_get_table('bugnote'),'last_modified'));

# ----------------------------------------------------------------------------
# Schema version: 20
#
$upgrade[ 20] = array('CreateTableSQL',array(db_get_table('bugnote_text'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  note 			XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 21] = array('CreateTableSQL',array(db_get_table('custom_field_project'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  sequence 		I2 NOTNULL DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 22] = array('CreateTableSQL',array(db_get_table('custom_field_string'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  value 		C(255) NOTNULL DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 23] = array('CreateIndexSQL',array('idx_custom_field_bug',db_get_table('custom_field_string'),'bug_id'));
$upgrade[ 24] = array('CreateTableSQL',array(db_get_table('custom_field'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  name 			C(64) NOTNULL DEFAULT \" '' \",
  type 			I2 NOTNULL DEFAULT '0',
  possible_values 	C(255) NOTNULL DEFAULT \" '' \",
  default_value 	C(255) NOTNULL DEFAULT \" '' \",
  valid_regexp 		C(255) NOTNULL DEFAULT \" '' \",
  access_level_r 	I2 NOTNULL DEFAULT '0',
  access_level_rw 	I2 NOTNULL DEFAULT '0',
  length_min 		 I  NOTNULL DEFAULT '0',
  length_max 		 I  NOTNULL DEFAULT '0',
  advanced 		L NOTNULL DEFAULT \" '0' \",
  require_report 	L NOTNULL DEFAULT \" '0' \",
  require_update 	L NOTNULL DEFAULT \" '0' \",
  display_report 	L NOTNULL DEFAULT \" '0' \",
  display_update 	L NOTNULL DEFAULT \" '1' \",
  require_resolved 	L NOTNULL DEFAULT \" '0' \",
  display_resolved 	L NOTNULL DEFAULT \" '0' \",
  display_closed 	L NOTNULL DEFAULT \" '0' \",
  require_closed 	L NOTNULL DEFAULT \" '0' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 25] = array('CreateIndexSQL',array('idx_custom_field_name',db_get_table('custom_field'),'name'));
$upgrade[ 26] = array('CreateTableSQL',array(db_get_table('filters'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  NOTNULL DEFAULT '0',
  project_id 		 I  NOTNULL DEFAULT '0',
  is_public 		L DEFAULT NULL,
  name 			C(64) NOTNULL DEFAULT \" '' \",
  filter_string 	XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 27] = array('CreateTableSQL',array(db_get_table('news'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  poster_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_posted 		T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  view_state 		I2 NOTNULL DEFAULT '10',
  announcement 		L NOTNULL DEFAULT \" '0' \",
  headline 		C(64) NOTNULL DEFAULT \" '' \",
  body 			XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 28] = array('CreateTableSQL',array(db_get_table('project_category'),"
  project_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  category 		C(64) NOTNULL PRIMARY DEFAULT \" '' \",
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 29] = array('CreateTableSQL',array(db_get_table('project_file'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT \" '' \",
  description 		C(250) NOTNULL DEFAULT \" '' \",
  diskfile 		C(250) NOTNULL DEFAULT \" '' \",
  filename 		C(250) NOTNULL DEFAULT \" '' \",
  folder 		C(250) NOTNULL DEFAULT \" '' \",
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT \" '' \",
  date_added 		T NOTNULL DEFAULT '" . db_null_date() . "',
  content 		B NOTNULL $t_blob_default
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));

# ----------------------------------------------------------------------------
# Schema version: 30
#
$upgrade[ 30] = array('CreateTableSQL',array(db_get_table('project_hierarchy'),"
			  child_id I UNSIGNED NOTNULL,
			  parent_id I UNSIGNED NOTNULL",
array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 31] = array('CreateTableSQL',array(db_get_table('project'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  name 			C(128) NOTNULL DEFAULT \" '' \",
  status 		I2 NOTNULL DEFAULT '10',
  enabled 		L NOTNULL DEFAULT \" '1' \",
  view_state 		I2 NOTNULL DEFAULT '10',
  access_min 		I2 NOTNULL DEFAULT '10',
  file_path 		C(250) NOTNULL DEFAULT \" '' \",
  description 		XL $t_notnull
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));

# Index autocreated when oci used
$upgrade[ 32] = db_is_oracle()
	? NULL	# No-op - required to ensure schema version consistency
	: array('CreateIndexSQL',array('idx_project_id',db_get_table('project'),'id'));

$upgrade[ 33] = array('CreateIndexSQL',array('idx_project_name',db_get_table('project'),'name',array('UNIQUE')));
$upgrade[ 34] = array('CreateIndexSQL',array('idx_project_view',db_get_table('project'),'view_state'));
$upgrade[ 35] = array('CreateTableSQL',array(db_get_table('project_user_list'),"
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  user_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 36] = array( 'CreateIndexSQL',array('idx_project_user',db_get_table('project_user_list'),'user_id'));
$upgrade[ 37] = array('CreateTableSQL',array(db_get_table('project_version'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  version 		C(64) NOTNULL DEFAULT \" '' \",
  date_order 		T NOTNULL DEFAULT '" . db_null_date() . "',
  description 		XL $t_notnull,
  released 		L NOTNULL DEFAULT \" '1' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 38] = array('CreateIndexSQL',array('idx_project_version',db_get_table('project_version'),'project_id,version',array('UNIQUE')));
$upgrade[ 39] = array('CreateTableSQL',array(db_get_table('sponsorship'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  NOTNULL DEFAULT '0',
  user_id 		 I  NOTNULL DEFAULT '0',
  amount 		 I  NOTNULL DEFAULT '0',
  logo 			C(128) NOTNULL DEFAULT \" '' \",
  url 			C(128) NOTNULL DEFAULT \" '' \",
  paid 			L NOTNULL DEFAULT \" '0' \",
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_updated 		T NOTNULL DEFAULT '" . db_null_date() . "'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));

# ----------------------------------------------------------------------------
# Schema version: 40
#
$upgrade[ 40] = array('CreateIndexSQL',array('idx_sponsorship_bug_id',db_get_table('sponsorship'),'bug_id'));
$upgrade[ 41] = array('CreateIndexSQL',array('idx_sponsorship_user_id',db_get_table('sponsorship'),'user_id'));
$upgrade[ 42] = array('CreateTableSQL',array(db_get_table('tokens'),"
			  id I NOTNULL PRIMARY AUTOINCREMENT,
			  owner I NOTNULL,
			  type I NOTNULL,
			  timestamp T NOTNULL,
			  expiry T,
			  value XL NOTNULL",
array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 43] = array('CreateTableSQL',array(db_get_table('user_pref'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  default_profile 	 I  UNSIGNED NOTNULL DEFAULT '0',
  default_project 	 I  UNSIGNED NOTNULL DEFAULT '0',
  advanced_report 	L NOTNULL DEFAULT \" '0' \",
  advanced_view 	L NOTNULL DEFAULT \" '0' \",
  advanced_update 	L NOTNULL DEFAULT \" '0' \",
  refresh_delay 	 I  NOTNULL DEFAULT '0',
  redirect_delay 	L $t_notnull DEFAULT \" '0' \",
  bugnote_order 	C(4) NOTNULL DEFAULT 'ASC',
  email_on_new 		L NOTNULL DEFAULT \" '0' \",
  email_on_assigned 	L NOTNULL DEFAULT \" '0' \",
  email_on_feedback 	L NOTNULL DEFAULT \" '0' \",
  email_on_resolved	L NOTNULL DEFAULT \" '0' \",
  email_on_closed 	L NOTNULL DEFAULT \" '0' \",
  email_on_reopened 	L NOTNULL DEFAULT \" '0' \",
  email_on_bugnote 	L NOTNULL DEFAULT \" '0' \",
  email_on_status 	L $t_notnull DEFAULT \" '0' \",
  email_on_priority 	L $t_notnull DEFAULT \" '0' \",
  email_on_priority_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_status_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_bugnote_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_reopened_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_closed_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_resolved_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_feedback_min_severity	I2 NOTNULL DEFAULT '10',
  email_on_assigned_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_new_min_severity 	I2 NOTNULL DEFAULT '10',
  email_bugnote_limit 	I2 NOTNULL DEFAULT '0',
  language 		C(32) NOTNULL DEFAULT 'english'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 44] = array('CreateTableSQL',array(db_get_table('user_print_pref'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  print_pref 		C(27) NOTNULL DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 45] = array('CreateTableSQL',array(db_get_table('user_profile'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  platform 		C(32) NOTNULL DEFAULT \" '' \",
  os 			C(32) NOTNULL DEFAULT \" '' \",
  os_build 		C(32) NOTNULL DEFAULT \" '' \",
  description 		XL $t_notnull
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 46] = array('CreateTableSQL',array(db_get_table('user'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  username 		C(32) NOTNULL DEFAULT \" '' \",
  realname 		C(64) NOTNULL DEFAULT \" '' \",
  email 		C(64) NOTNULL DEFAULT \" '' \",
  password 		C(32) NOTNULL DEFAULT \" '' \",
  date_created 		T NOTNULL DEFAULT '" . db_null_date() . "',
  last_visit 		T NOTNULL DEFAULT '" . db_null_date() . "',
  enabled		L NOTNULL DEFAULT \" '1' \",
  protected 		L NOTNULL DEFAULT \" '0' \",
  access_level 		I2 NOTNULL DEFAULT '10',
  login_count 		 I  NOTNULL DEFAULT '0',
  lost_password_request_count 	I2 NOTNULL DEFAULT '0',
  failed_login_count 	I2 NOTNULL DEFAULT '0',
  cookie_string 	C(64) NOTNULL DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[ 47] = array('CreateIndexSQL',array('idx_user_cookie_string',db_get_table('user'),'cookie_string',array('UNIQUE')));
$upgrade[ 48] = array('CreateIndexSQL',array('idx_user_username',db_get_table('user'),'username',array('UNIQUE')));
$upgrade[ 49] = array('CreateIndexSQL',array('idx_enable',db_get_table('user'),'enabled'));

# ----------------------------------------------------------------------------
# Schema version: 50
#
$upgrade[ 50] = array('CreateIndexSQL',array('idx_access',db_get_table('user'),'access_level'));
$upgrade[ 51] = array('InsertData', array( db_get_table('user'),
	"(username, realname, email, password, date_created, last_visit, enabled, protected, access_level, login_count, lost_password_request_count, failed_login_count, cookie_string) VALUES
		('administrator', '', 'root@localhost', '63a9f0ea7bb98050796b649e85481845', " . $t_timestamp . ", " . $t_timestamp . ", '1', '0', 90, 3, 0, 0, '" .
			md5( mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() ) ) . md5( time() ) . "')" ) );

# Release marker: 1.0.0 - 1.0.7

$upgrade[ 52] = array('AlterColumnSQL', array( db_get_table( 'bug_history' ), "old_value C(255) $t_notnull" ) );
$upgrade[ 53] = array('AlterColumnSQL', array( db_get_table( 'bug_history' ), "new_value C(255) $t_notnull" ) );

$upgrade[ 54] = array('CreateTableSQL',array(db_get_table('email'),"
  email_id 		I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  email		 	C(64) NOTNULL DEFAULT \" '' \",
  subject		C(250) NOTNULL DEFAULT \" '' \",
  submitted		T NOTNULL DEFAULT '" . db_null_date() . "',
  metadata 		XL NOTNULL,
  body 			XL NOTNULL
  ",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));

# Index autocreated when oci used
$upgrade[ 55] = db_is_oracle()
	? NULL	# No-op - required to ensure schema version consistency
	: array('CreateIndexSQL',array('idx_email_id',db_get_table('email'),'email_id'));

$upgrade[ 56] = array('AddColumnSQL',array(db_get_table('bug'), "target_version C(64) NOTNULL DEFAULT \" '' \""));
$upgrade[ 57] = array('AddColumnSQL',array(db_get_table('bugnote'), "time_tracking I UNSIGNED NOTNULL DEFAULT \" 0 \""));
$upgrade[ 58] = array('CreateIndexSQL',array('idx_diskfile',db_get_table('bug_file'),'diskfile'));
$upgrade[ 59] = array('AlterColumnSQL', array( db_get_table( 'user_print_pref' ), "print_pref C(64) $t_notnull" ) );

# ----------------------------------------------------------------------------
# Schema version: 60
#
$upgrade[ 60] = array('AlterColumnSQL', array( db_get_table( 'bug_history' ), "field_name C(64) $t_notnull" ) );

# Release marker: 1.1.0a4

$upgrade[ 61] = array('CreateTableSQL', array( db_get_table( 'tag' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(100)	NOTNULL PRIMARY DEFAULT \" '' \",
	description		XL		$t_notnull,
	date_created	T		NOTNULL DEFAULT '" . db_null_date() . "',
	date_updated	T		NOTNULL DEFAULT '" . db_null_date() . "'
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[ 62] = array('CreateTableSQL', array( db_get_table( 'bug_tag' ), "
	bug_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	tag_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	user_id			I	UNSIGNED NOTNULL DEFAULT '0',
	date_attached	T	NOTNULL DEFAULT '" . db_null_date() . "'
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

$upgrade[ 63] = array('CreateIndexSQL', array( 'idx_typeowner', db_get_table( 'tokens' ), 'type, owner' ) );

# Release marker: 1.1.0 - 1.1.8
# Release marker: 1.2.0-SVN

$upgrade[ 64] = array('CreateTableSQL', array( db_get_table( 'plugin' ), "
	basename		C(40)	NOTNULL PRIMARY,
	enabled			L		NOTNULL DEFAULT \" '0' \"
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

$upgrade[ 65] = array('AlterColumnSQL', array( db_get_table( 'user_pref' ), "redirect_delay 	I $t_notnull DEFAULT 0" ) );

# Apparently mysql now has a STRICT mode, where setting a DEFAULT value on a
# blob/text is now an error, instead of being silently ignored
$upgrade[ 66] = ( isset( $f_db_type ) && ( $f_db_type == 'mysql' || $f_db_type == 'mysqli' ) )
	? array('AlterColumnSQL', array( db_get_table( 'custom_field' ), "possible_values X NOTNULL" ) )
	: array('AlterColumnSQL', array( db_get_table( 'custom_field' ), "possible_values X NOTNULL DEFAULT \" '' \"" ) );


$upgrade[ 67] = array( 'CreateTableSQL', array( db_get_table( 'category' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	project_id		I		UNSIGNED NOTNULL DEFAULT '0',
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(128)	NOTNULL DEFAULT \" '' \",
	status			I		UNSIGNED NOTNULL DEFAULT '0'
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[ 68] = array( 'CreateIndexSQL', array( 'idx_category_project_name', db_get_table( 'category' ), 'project_id, name', array( 'UNIQUE' ) ) );
$upgrade[ 69] = array( 'InsertData', array( db_get_table( 'category' ), "
	( project_id, user_id, name, status ) VALUES
	( '0', '0', 'General', '0' ) " ) );

# ----------------------------------------------------------------------------
# Schema version: 70
#
$upgrade[ 70] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
$upgrade[ 71] = array( 'UpdateFunction', "category_migrate" );
$upgrade[ 72] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "category" ) );
$upgrade[ 73] = array( 'DropTableSQL', array( db_get_table( 'project_category' ) ) );
$upgrade[ 74] = array( 'AddColumnSQL', array( db_get_table( 'project' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );

# remove unnecessary indexes
$upgrade[ 75] = array('CreateIndexSQL',array('idx_project_id',db_get_table('project'),'id', array('DROP')), array( 'db_index_exists', array( db_get_table('project'), 'idx_project_id')));
$upgrade[ 76] = array('CreateIndexSQL',array('idx_config',db_get_table( 'config' ),'config_id', array('DROP')), array( 'db_index_exists', array( db_get_table('config'), 'idx_config')));

$upgrade[ 77] = array( 'InsertData', array( db_get_table( 'plugin' ), "
	( basename, enabled ) VALUES
	( 'MantisCoreFormatting', '1' )" ) );

$upgrade[ 78] = array( 'AddColumnSQL', array( db_get_table( 'project' ), "inherit_global I UNSIGNED NOTNULL DEFAULT '0'" ) );
$upgrade[ 79] = array( 'AddColumnSQL', array( db_get_table( 'project_hierarchy' ), "inherit_parent I UNSIGNED NOTNULL DEFAULT '0'" ) );

# ----------------------------------------------------------------------------
# Schema version: 80
#
$upgrade[ 80] = array( 'AddColumnSQL', array( db_get_table( 'plugin' ), "
	protected		L		NOTNULL DEFAULT \" '0' \",
	priority		I		UNSIGNED NOTNULL DEFAULT '3'
	" ) );
$upgrade[ 81] = array( 'AddColumnSQL', array( db_get_table( 'project_version' ), "
	obsolete		L		NOTNULL DEFAULT \" '0' \"" ) );
$upgrade[ 82] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
	due_date        T       NOTNULL DEFAULT '" . db_null_date() . "' " ) );

# Release marker: 1.2.0a1

$upgrade[ 83] = array( 'AddColumnSQL', array( db_get_table( 'custom_field' ), "
  filter_by 		L 		NOTNULL DEFAULT \" '1' \"" ) );

# Release marker: 1.2.0a2 - 1.2.0a3

$upgrade[ 84] = array( 'CreateTableSQL', array( db_get_table( 'bug_revision' ), "
	id			I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	bug_id		I		UNSIGNED NOTNULL,
	bugnote_id	I		UNSIGNED NOTNULL DEFAULT '0',
	user_id		I		UNSIGNED NOTNULL,
	timestamp	T		NOTNULL DEFAULT '" . db_null_date() . "',
	type		I		UNSIGNED NOTNULL,
	value		XL		NOTNULL
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[ 85] = array( 'CreateIndexSQL', array( 'idx_bug_rev_id_time', db_get_table( 'bug_revision' ), 'bug_id, timestamp' ) );
$upgrade[ 86] = array( 'CreateIndexSQL', array( 'idx_bug_rev_type', db_get_table( 'bug_revision' ), 'type' ) );

# Date conversion
$upgrade[ 87] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 88] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
	due_date_int        			I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 89] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 90
#
$upgrade[ 90] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug' ), 'id', array( 'date_submitted', 'due_date', 'last_updated' ), array( 'date_submitted_int', 'due_date_int', 'last_updated_int' ) ) );

$upgrade[ 91] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "date_submitted" ) );
$upgrade[ 92] = array( 'RenameColumnSQL', array( db_get_table( 'bug' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 93] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "due_date" ) );
$upgrade[ 94] = array( 'RenameColumnSQL', array( db_get_table( 'bug' ), "due_date_int", "due_date", "due_date_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 95] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "last_updated" ) );
$upgrade[ 96] = array( 'RenameColumnSQL', array( db_get_table( 'bug' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[ 97] = array('CreateIndexSQL',array('idx_last_mod',db_get_table( 'bugnote' ),'last_modified', array('DROP')), array( 'db_index_exists', array( db_get_table('bugnote'), 'idx_last_mod')));

$upgrade[ 98] = array( 'AddColumnSQL', array( db_get_table( 'bugnote' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 99] = array( 'AddColumnSQL', array( db_get_table( 'bugnote' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 100
#
$upgrade[100] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bugnote' ), 'id', array( 'last_modified', 'date_submitted' ), array( 'last_modified_int', 'date_submitted_int' ) ) );

$upgrade[101] = array( 'DropColumnSQL', array( db_get_table( 'bugnote' ), "last_modified" ) );
$upgrade[102] = array( 'RenameColumnSQL', array( db_get_table( 'bugnote' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[103] = array('CreateIndexSQL',array('idx_last_mod',db_get_table('bugnote'),'last_modified'));
$upgrade[104] = array( 'DropColumnSQL', array( db_get_table( 'bugnote' ), "date_submitted" ) );
$upgrade[105] = array( 'RenameColumnSQL', array( db_get_table( 'bugnote' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[106] = array( 'AddColumnSQL', array( db_get_table( 'bug_file' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[107] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_file' ), 'id', 'date_added', 'date_added_int' ) );
$upgrade[108] = array( 'DropColumnSQL', array( db_get_table( 'bug_file' ), "date_added" ) );
$upgrade[109] = array( 'RenameColumnSQL', array( db_get_table( 'bug_file' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 110
#

$upgrade[110] = array( 'AddColumnSQL', array( db_get_table( 'project_file' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[111] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'project_file' ), 'id', 'date_added', 'date_added_int' ) );
$upgrade[112] = array( 'DropColumnSQL', array( db_get_table( 'project_file' ), "date_added" ) );
$upgrade[113] = array( 'RenameColumnSQL', array( db_get_table( 'project_file' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[114] = array( 'AddColumnSQL', array( db_get_table( 'bug_history' ), "
	date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[115] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_history' ), 'id', 'date_modified', 'date_modified_int' ) );
$upgrade[116] = array( 'DropColumnSQL', array( db_get_table( 'bug_history' ), "date_modified" ) );
$upgrade[117] = array( 'RenameColumnSQL', array( db_get_table( 'bug_history' ), "date_modified_int", "date_modified", "date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[118] = array( 'AddColumnSQL', array( db_get_table( 'user' ), "
	last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[119] = array( 'AddColumnSQL', array( db_get_table( 'user' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 120
#

$upgrade[120] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'user' ), 'id', array( 'last_visit', 'date_created' ), array( 'last_visit_int', 'date_created_int' ) ) );

$upgrade[121] = array( 'DropColumnSQL', array( db_get_table( 'user' ), "date_created" ) );
$upgrade[122] = array( 'RenameColumnSQL', array( db_get_table( 'user' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[123] = array( 'DropColumnSQL', array( db_get_table( 'user' ), "last_visit" ) );
$upgrade[124] = array( 'RenameColumnSQL', array( db_get_table( 'user' ), "last_visit_int", "last_visit", "last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[125] = array( 'AddColumnSQL', array( db_get_table( 'email' ), "
	submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[126] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'email' ), 'email_id', 'submitted', 'submitted_int' ) );
$upgrade[127] = array( 'DropColumnSQL', array( db_get_table( 'email' ), "submitted" ) );
$upgrade[128] = array( 'RenameColumnSQL', array( db_get_table( 'email' ), "submitted_int", "submitted", "submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[129] = array( 'AddColumnSQL', array( db_get_table( 'tag' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 130
#
$upgrade[130] = array( 'AddColumnSQL', array( db_get_table( 'tag' ), "
	date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[131] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'tag' ), 'id', array( 'date_created', 'date_updated' ), array( 'date_created_int', 'date_updated_int' ) ) );

$upgrade[132] = array( 'DropColumnSQL', array( db_get_table( 'tag' ), "date_created" ) );
$upgrade[133] = array( 'RenameColumnSQL', array( db_get_table( 'tag' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[134] = array( 'DropColumnSQL', array( db_get_table( 'tag' ), "date_updated" ) );
$upgrade[135] = array( 'RenameColumnSQL', array( db_get_table( 'tag' ), "date_updated_int", "date_updated", "date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[136] = array( 'AddColumnSQL', array( db_get_table( 'bug_tag' ), "
	date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[137] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_tag' ), 'bug_id', 'date_attached', 'date_attached_int' ) );
$upgrade[138] = array( 'DropColumnSQL', array( db_get_table( 'bug_tag' ), "date_attached" ) );
$upgrade[139] = array( 'RenameColumnSQL', array( db_get_table( 'bug_tag' ), "date_attached_int", "date_attached", "date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 140
#

$upgrade[140] = array( 'AddColumnSQL', array( db_get_table( 'tokens' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[141] = array( 'AddColumnSQL', array( db_get_table( 'tokens' ), "
	expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[142] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'tokens' ), 'id', array( 'timestamp', 'expiry' ), array( 'timestamp_int', 'expiry_int' ) ) );

$upgrade[143] = array( 'DropColumnSQL', array( db_get_table( 'tokens' ), "timestamp" ) );
$upgrade[144] = array( 'RenameColumnSQL', array( db_get_table( 'tokens' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[145] = array( 'DropColumnSQL', array( db_get_table( 'tokens' ), "expiry" ) );
$upgrade[146] = array( 'RenameColumnSQL', array( db_get_table( 'tokens' ), "expiry_int", "expiry", "expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[147] = array( 'AddColumnSQL', array( db_get_table( 'news' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[148] = array( 'AddColumnSQL', array( db_get_table( 'news' ), "
	date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[149] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'news' ), 'id', array( 'date_posted', 'last_modified' ), array( 'date_posted_int', 'last_modified_int' ) ) );

# ----------------------------------------------------------------------------
# Schema version: 150
#

$upgrade[150] = array( 'DropColumnSQL', array( db_get_table( 'news' ), "last_modified" ) );
$upgrade[151] = array( 'RenameColumnSQL', array( db_get_table( 'news' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[152] = array( 'DropColumnSQL', array( db_get_table( 'news' ), "date_posted" ) );
$upgrade[153] = array( 'RenameColumnSQL', array( db_get_table( 'news' ), "date_posted_int", "date_posted", "date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[154] = array('CreateIndexSQL',array('idx_bug_rev_id_time',db_get_table( 'bug_revision' ),'bug_id, timestamp', array('DROP')), array( 'db_index_exists', array( db_get_table('bug_revision'), 'idx_bug_rev_id_time')));
$upgrade[155] = array( 'AddColumnSQL', array( db_get_table( 'bug_revision' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[156] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_revision' ), 'id', 'timestamp', 'timestamp_int' ) );
$upgrade[157] = array( 'DropColumnSQL', array( db_get_table( 'bug_revision' ), "timestamp" ) );
$upgrade[158] = array( 'RenameColumnSQL', array( db_get_table( 'bug_revision' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[159] = array( 'CreateIndexSQL', array( 'idx_bug_rev_id_time', db_get_table( 'bug_revision' ), 'bug_id, timestamp' ) );

# ----------------------------------------------------------------------------
# Schema version: 160
#

$upgrade[160] = array( 'AddColumnSQL', array( db_get_table( 'user_pref' ), "
	 timezone C(32) NOTNULL DEFAULT '' " ) );

$upgrade[161] = array( 'AddColumnSQL', array( db_get_table( 'project_version' ), "
	date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[162] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'project_version' ), 'id', 'date_order', 'date_order_int' ) );
$upgrade[163] = array( 'DropColumnSQL', array( db_get_table( 'project_version' ), "date_order" ) );
$upgrade[164] = array( 'RenameColumnSQL', array( db_get_table( 'project_version' ), "date_order_int", "date_order", "date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[165] = array( 'AddColumnSQL', array( db_get_table( 'sponsorship' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[166] = array( 'AddColumnSQL', array( db_get_table( 'sponsorship' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[167] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'sponsorship' ), 'id', array( 'date_submitted', 'last_updated' ), array( 'date_submitted_int', 'last_updated_int' ) ) );

$upgrade[168] = array( 'DropColumnSQL', array( db_get_table( 'sponsorship' ), "last_updated" ) );
$upgrade[169] = array( 'RenameColumnSQL', array( db_get_table( 'sponsorship' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 170
#
$upgrade[170] = array( 'DropColumnSQL', array( db_get_table( 'sponsorship' ), "date_submitted" ) );
$upgrade[171] = array( 'RenameColumnSQL', array( db_get_table( 'sponsorship' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[172] = array( 'AddColumnSQL', array( db_get_table( 'project_file' ), "
	user_id			I		UNSIGNED NOTNULL DEFAULT '0' " ) );
$upgrade[173] = array( 'AddColumnSQL', array( db_get_table( 'bug_file' ), "
	user_id		I  			UNSIGNED NOTNULL DEFAULT '0' " ) );

# Release marker: 1.2.0rc1

$upgrade[174] = array( 'DropColumnSQL', array( db_get_table( 'custom_field'), "advanced" ) );
$upgrade[175] = array( 'DropColumnSQL', array( db_get_table( 'user_pref'), "advanced_report" ) );
$upgrade[176] = array( 'DropColumnSQL', array( db_get_table( 'user_pref'), "advanced_view" ) );
$upgrade[177] = array( 'DropColumnSQL', array( db_get_table( 'user_pref'), "advanced_update" ) );
$upgrade[178] = array( 'CreateIndexSQL', array( 'idx_project_hierarchy_child_id', db_get_table( 'project_hierarchy' ), 'child_id' ) );

# Decrease index name length for oci8 (30 chars max)
if( db_is_oracle() ) {
	$t_index_name = 'idx_prj_hier_parent_id';
} else {
	$t_index_name = 'idx_project_hierarchy_parent_id';
}

# Release marker: 1.2.0rc2

$upgrade[179] = array( 'CreateIndexSQL', array( $t_index_name, db_get_table( 'project_hierarchy' ), 'parent_id' ) );

# ----------------------------------------------------------------------------
# Schema version: 180
#
$upgrade[180] = array( 'CreateIndexSQL', array( 'idx_tag_name', db_get_table( 'tag' ), 'name' ) );
$upgrade[181] = array( 'CreateIndexSQL', array( 'idx_bug_tag_tag_id', db_get_table( 'bug_tag' ), 'tag_id' ) );
$upgrade[182] = array( 'CreateIndexSQL', array( 'idx_email_id', db_get_table( 'email' ), 'email_id', array( 'DROP' ) ), array( 'db_index_exists', array( db_get_table( 'email' ), 'idx_email_id') ) );

# Release marker: 1.2.0

$upgrade[183] = array( 'UpdateFunction', 'correct_multiselect_custom_fields_db_format' );

# Release marker: 1.2.1 - 1.2.15

$upgrade[184] = array( 'UpdateFunction', "stored_filter_migrate" );
$upgrade[185] = array( 'AddColumnSQL', array( db_get_table( 'custom_field_string' ), "
	text		XL  			NULL DEFAULT NULL " ) );
$upgrade[186] = array( 'UpdateFunction', 'update_history_long_custom_fields' );
$upgrade[187] = array( 'CreateIndexSQL', array( 'idx_bug_id', db_get_table( 'bug_monitor' ), 'bug_id' ) );
$upgrade[188] = array( 'AlterColumnSQL', array( db_get_table( 'project' ), "inherit_global L NOTNULL DEFAULT '0'" ) );
$upgrade[189] = array( 'AlterColumnSQL', array( db_get_table( 'project_hierarchy' ), "inherit_parent L NOTNULL DEFAULT '0'" ) );

# Release marker: 1.3.0
