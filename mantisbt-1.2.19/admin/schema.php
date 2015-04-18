<?php
# MantisBT - a php based bugtracking system

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

# Each entry below defines the schema. The upgrade array consists of
#  two elements
# The first is the function to generate SQL statements (see adodb schema doc for more details)
#  e.g., CreateTableSQL, DropTableSQL, ChangeTableSQL, RenameTableSQL, RenameColumnSQL,
#  DropTableSQL, ChangeTableSQL, RenameTableSQL, RenameColumnSQL, AlterColumnSQL, DropColumnSQL
#  A local function "InsertData" has been provided to add data to the db
# The second parameter is an array of the parameters to be passed to the function.

# An update identifier is inferred from the ordering of this table. ONLY ADD NEW CHANGES TO THE
#  END OF THE TABLE!!!

if ( !function_exists( 'db_null_date' ) ) {
	function db_null_date() {
		return 0;
	}
}


function installer_db_now() {
        global $g_db;
 
       return $g_db->BindTimeStamp( time() );
}

$upgrade[] = Array('CreateTableSQL',Array(db_get_table( 'mantis_config_table' ),"
			  config_id C(64) NOTNULL PRIMARY,
			  project_id I DEFAULT '0' PRIMARY,
			  user_id I DEFAULT '0' PRIMARY,
			  access_reqd I DEFAULT '0',
			  type I DEFAULT '90',
			  value XL NOTNULL",
Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_config',db_get_table( 'mantis_config_table' ),'config_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bug_file_table'),"
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
  content 		B NOTNULL
  ",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_file_bug_id',db_get_table('mantis_bug_file_table'),'bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bug_history_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  field_name 		C(32) NOTNULL DEFAULT \" '' \",
  old_value 		C(128) NOTNULL DEFAULT \" '' \",
  new_value 		C(128) NOTNULL DEFAULT \" '' \",
  type 			I2 NOTNULL DEFAULT '0'
  ",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_history_bug_id',db_get_table('mantis_bug_history_table'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_history_user_id',db_get_table('mantis_bug_history_table'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bug_monitor_table'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bug_relationship_table'),"
  id 			 I  UNSIGNED NOTNULL AUTOINCREMENT PRIMARY,
  source_bug_id		 I  UNSIGNED NOTNULL DEFAULT '0',
  destination_bug_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  relationship_type 	I2 NOTNULL DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_relationship_source',db_get_table('mantis_bug_relationship_table'),'source_bug_id'));
/* 10 */
$upgrade[] = Array('CreateIndexSQL',Array('idx_relationship_destination',db_get_table('mantis_bug_relationship_table'),'destination_bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bug_table'),"
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
  sticky		L  NOTNULL DEFAULT  \"'0'\"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_sponsorship_total',db_get_table('mantis_bug_table'),'sponsorship_total'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_fixed_in_version',db_get_table('mantis_bug_table'),'fixed_in_version'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_status',db_get_table('mantis_bug_table'),'status'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project',db_get_table('mantis_bug_table'),'project_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bug_text_table'),"
  id 			 I  PRIMARY UNSIGNED NOTNULL AUTOINCREMENT,
  description 		XL NOTNULL,
  steps_to_reproduce 	XL NOTNULL,
  additional_information XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bugnote_table'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  reporter_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bugnote_text_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  note_type 		 I  DEFAULT '0',
  note_attr 		C(250) DEFAULT \" '' \"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug',db_get_table('mantis_bugnote_table'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_last_mod',db_get_table('mantis_bugnote_table'),'last_modified'));
/* 20 */
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_bugnote_text_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  note 			XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_custom_field_project_table'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  sequence 		I2 NOTNULL DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_custom_field_string_table'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  value 		C(255) NOTNULL DEFAULT \" '' \"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_custom_field_bug',db_get_table('mantis_custom_field_string_table'),'bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_custom_field_table'),"
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
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_custom_field_name',db_get_table('mantis_custom_field_table'),'name'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_filters_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  NOTNULL DEFAULT '0',
  project_id 		 I  NOTNULL DEFAULT '0',
  is_public 		L DEFAULT NULL,
  name 			C(64) NOTNULL DEFAULT \" '' \",
  filter_string 	XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_news_table'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  poster_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_posted 		T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  view_state 		I2 NOTNULL DEFAULT '10',
  announcement 		L NOTNULL DEFAULT \" '0' \",
  headline 		C(64) NOTNULL DEFAULT \" '' \",
  body 			XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_project_category_table'),"
  project_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  category 		C(64) NOTNULL PRIMARY DEFAULT \" '' \",
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_project_file_table'),"
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
  content 		B NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 30 */
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_project_hierarchy_table'),"
			  child_id I UNSIGNED NOTNULL,
			  parent_id I UNSIGNED NOTNULL",
Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_project_table'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  name 			C(128) NOTNULL DEFAULT \" '' \",
  status 		I2 NOTNULL DEFAULT '10',
  enabled 		L NOTNULL DEFAULT \" '1' \",
  view_state 		I2 NOTNULL DEFAULT '10',
  access_min 		I2 NOTNULL DEFAULT '10',
  file_path 		C(250) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_id',db_get_table('mantis_project_table'),'id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_name',db_get_table('mantis_project_table'),'name',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_view',db_get_table('mantis_project_table'),'view_state'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_project_user_list_table'),"
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  user_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array( 'CreateIndexSQL',Array('idx_project_user',db_get_table('mantis_project_user_list_table'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_project_version_table'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  version 		C(64) NOTNULL DEFAULT \" '' \",
  date_order 		T NOTNULL DEFAULT '" . db_null_date() . "',
  description 		XL NOTNULL,
  released 		L NOTNULL DEFAULT \" '1' \"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_version',db_get_table('mantis_project_version_table'),'project_id,version',Array('UNIQUE')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_sponsorship_table'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  NOTNULL DEFAULT '0',
  user_id 		 I  NOTNULL DEFAULT '0',
  amount 		 I  NOTNULL DEFAULT '0',
  logo 			C(128) NOTNULL DEFAULT \" '' \",
  url 			C(128) NOTNULL DEFAULT \" '' \",
  paid 			L NOTNULL DEFAULT \" '0' \",
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_updated 		T NOTNULL DEFAULT '" . db_null_date() . "'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 40 */
$upgrade[] = Array('CreateIndexSQL',Array('idx_sponsorship_bug_id',db_get_table('mantis_sponsorship_table'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_sponsorship_user_id',db_get_table('mantis_sponsorship_table'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_tokens_table'),"
			  id I NOTNULL PRIMARY AUTOINCREMENT,
			  owner I NOTNULL,
			  type I NOTNULL,
			  timestamp T NOTNULL,
			  expiry T,
			  value XL NOTNULL",
Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_user_pref_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  default_profile 	 I  UNSIGNED NOTNULL DEFAULT '0',
  default_project 	 I  UNSIGNED NOTNULL DEFAULT '0',
  advanced_report 	L NOTNULL DEFAULT \" '0' \",
  advanced_view 	L NOTNULL DEFAULT \" '0' \",
  advanced_update 	L NOTNULL DEFAULT \" '0' \",
  refresh_delay 	 I  NOTNULL DEFAULT '0',
  redirect_delay 	L NOTNULL DEFAULT \" '0' \",
  bugnote_order 	C(4) NOTNULL DEFAULT 'ASC',
  email_on_new 		L NOTNULL DEFAULT \" '0' \",
  email_on_assigned 	L NOTNULL DEFAULT \" '0' \",
  email_on_feedback 	L NOTNULL DEFAULT \" '0' \",
  email_on_resolved	L NOTNULL DEFAULT \" '0' \",
  email_on_closed 	L NOTNULL DEFAULT \" '0' \",
  email_on_reopened 	L NOTNULL DEFAULT \" '0' \",
  email_on_bugnote 	L NOTNULL DEFAULT \" '0' \",
  email_on_status 	L NOTNULL DEFAULT \" '0' \",
  email_on_priority 	L NOTNULL DEFAULT \" '0' \",
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
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_user_print_pref_table'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  print_pref 		C(27) NOTNULL DEFAULT \" '' \"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_user_profile_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  platform 		C(32) NOTNULL DEFAULT \" '' \",
  os 			C(32) NOTNULL DEFAULT \" '' \",
  os_build 		C(32) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_user_table'),"
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
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_user_cookie_string',db_get_table('mantis_user_table'),'cookie_string',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_user_username',db_get_table('mantis_user_table'),'username',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_enable',db_get_table('mantis_user_table'),'enabled'));
/* 50 */
$upgrade[] = Array('CreateIndexSQL',Array('idx_access',db_get_table('mantis_user_table'),'access_level'));
$upgrade[] = Array('InsertData', Array( db_get_table('mantis_user_table'),
    "(username, realname, email, password, date_created, last_visit, enabled, protected, access_level, login_count, lost_password_request_count, failed_login_count, cookie_string) VALUES
        ('administrator', '', 'root@localhost', '63a9f0ea7bb98050796b649e85481845', '" . installer_db_now() . "', '" . installer_db_now() . "', '1', '0', 90, 3, 0, 0, '" .
             md5( mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() ) ) . md5( time() ) . "')" ) );
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'mantis_bug_history_table' ), "old_value C(255) NOTNULL" ) );
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'mantis_bug_history_table' ), "new_value C(255) NOTNULL" ) );

$upgrade[] = Array('CreateTableSQL',Array(db_get_table('mantis_email_table'),"
  email_id 		I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  email		 	C(64) NOTNULL DEFAULT \" '' \",
  subject		C(250) NOTNULL DEFAULT \" '' \",
  submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  metadata 		XL NOTNULL,
  body 			XL NOTNULL
  ",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_email_id',db_get_table('mantis_email_table'),'email_id'));
$upgrade[] = Array('AddColumnSQL',Array(db_get_table('mantis_bug_table'), "target_version C(64) NOTNULL DEFAULT \" '' \""));
$upgrade[] = Array('AddColumnSQL',Array(db_get_table('mantis_bugnote_table'), "time_tracking I UNSIGNED NOTNULL DEFAULT \" 0 \""));
$upgrade[] = Array('CreateIndexSQL',Array('idx_diskfile',db_get_table('mantis_bug_file_table'),'diskfile'));
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'mantis_user_print_pref_table' ), "print_pref C(64) NOTNULL" ) );
/* 60 */
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'mantis_bug_history_table' ), "field_name C(64) NOTNULL" ) );

# Release marker: 1.1.0a4

$upgrade[] = Array('CreateTableSQL', Array( db_get_table( 'mantis_tag_table' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(100)	NOTNULL PRIMARY DEFAULT \" '' \",
	description		XL		NOTNULL,
	date_created	T		NOTNULL DEFAULT '" . db_null_date() . "',
	date_updated	T		NOTNULL DEFAULT '" . db_null_date() . "'
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[] = Array('CreateTableSQL', Array( db_get_table( 'mantis_bug_tag_table' ), "
	bug_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	tag_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	user_id			I	UNSIGNED NOTNULL DEFAULT '0',
	date_attached	T	NOTNULL DEFAULT '" . db_null_date() . "'
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

$upgrade[] = Array('CreateIndexSQL', Array( 'idx_typeowner', db_get_table( 'mantis_tokens_table' ), 'type, owner' ) );

# Release marker: 1.2.0-SVN

$upgrade[] = Array('CreateTableSQL', Array( db_get_table( 'mantis_plugin_table' ), "
	basename		C(40)	NOTNULL PRIMARY,
	enabled			L		NOTNULL DEFAULT \" '0' \"
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'mantis_user_pref_table' ), "redirect_delay 	I NOTNULL DEFAULT 0" ) );

/* apparently mysql now has a STRICT mode, where setting a DEFAULT value on a blob/text is now an error, instead of being silently ignored */
if ( isset( $f_db_type ) && ( $f_db_type == 'mysql' || $f_db_type == 'mysqli' ) ) {
	$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'mantis_custom_field_table' ), "possible_values X NOTNULL" ) );
} else {
	$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'mantis_custom_field_table' ), "possible_values X NOTNULL DEFAULT \" '' \"" ) );
}

$upgrade[] = Array( 'CreateTableSQL', Array( db_get_table( 'mantis_category_table' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	project_id		I		UNSIGNED NOTNULL DEFAULT '0',
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(128)	NOTNULL DEFAULT \" '' \",
	status			I		UNSIGNED NOTNULL DEFAULT '0'
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_category_project_name', db_get_table( 'mantis_category_table' ), 'project_id, name', array( 'UNIQUE' ) ) );
$upgrade[] = Array( 'InsertData', Array( db_get_table( 'mantis_category_table' ), "
	( project_id, user_id, name, status ) VALUES
	( '0', '0', 'General', '0' ) " ) );
/* 70 */
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
$upgrade[] = Array( 'UpdateFunction', "category_migrate" );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "category" ) );
$upgrade[] = Array( 'DropTableSQL', Array( db_get_table( 'mantis_project_category_table' ) ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_project_table' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
// remove unnecessary indexes
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_id',db_get_table('mantis_project_table'),'id', array('DROP')), Array( 'db_index_exists', Array( db_get_table('mantis_project_table'), 'idx_project_id')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_config',db_get_table( 'mantis_config_table' ),'config_id', array('DROP')), Array( 'db_index_exists', Array( db_get_table('mantis_config_table'), 'idx_config')));

$upgrade[] = Array( 'InsertData', Array( db_get_table( 'mantis_plugin_table' ), "
	( basename, enabled ) VALUES
	( 'MantisCoreFormatting', '1' )" ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_project_table' ), "inherit_global I UNSIGNED NOTNULL DEFAULT '0'" ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_project_hierarchy_table' ), "inherit_parent I UNSIGNED NOTNULL DEFAULT '0'" ) );
/* 80 */
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_plugin_table' ), "
	protected		L		NOTNULL DEFAULT \" '0' \",
	priority		I		UNSIGNED NOTNULL DEFAULT '3'
	" ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_project_version_table' ), "
	obsolete		L		NOTNULL DEFAULT \" '0' \"" ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "
    due_date        T       NOTNULL DEFAULT '" . db_null_date() . "' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_custom_field_table' ), "
  filter_by 		L 		NOTNULL DEFAULT \" '1' \"" ) );
$upgrade[] = Array( 'CreateTableSQL', Array( db_get_table( 'mantis_bug_revision_table' ), "
	id			I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	bug_id		I		UNSIGNED NOTNULL,
	bugnote_id	I		UNSIGNED NOTNULL DEFAULT '0',
	user_id		I		UNSIGNED NOTNULL,
	timestamp	T		NOTNULL DEFAULT '" . db_null_date() . "',
	type		I		UNSIGNED NOTNULL,
	value		XL		NOTNULL
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_rev_id_time', db_get_table( 'mantis_bug_revision_table' ), 'bug_id, timestamp' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_rev_type', db_get_table( 'mantis_bug_revision_table' ), 'type' ) );

#date conversion

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "
	due_date_int        			I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 90 */
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_bug_table', 'id', array( 'date_submitted', 'due_date', 'last_updated' ), array( 'date_submitted_int', 'due_date_int', 'last_updated_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "date_submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "due_date" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "due_date_int", "due_date", "due_date_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "last_updated" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bug_table' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array('CreateIndexSQL',Array('idx_last_mod',db_get_table( 'mantis_bugnote_table' ),'last_modified', array('DROP')), Array( 'db_index_exists', Array( db_get_table('mantis_bugnote_table'), 'idx_last_mod')));

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bugnote_table' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bugnote_table' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 100 */
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_bugnote_table', 'id', array( 'last_modified', 'date_submitted' ), array( 'last_modified_int', 'date_submitted_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bugnote_table' ), "last_modified" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bugnote_table' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array('CreateIndexSQL',Array('idx_last_mod',db_get_table('mantis_bugnote_table'),'last_modified'));
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bugnote_table' ), "date_submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bugnote_table' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
	

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_file_table' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_bug_file_table', 'id', 'date_added', 'date_added_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_file_table' ), "date_added" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bug_file_table' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 110 */

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_project_file_table' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_project_file_table', 'id', 'date_added', 'date_added_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_project_file_table' ), "date_added" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_project_file_table' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_history_table' ), "
	date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_bug_history_table', 'id', 'date_modified', 'date_modified_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_history_table' ), "date_modified" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bug_history_table' ), "date_modified_int", "date_modified", "date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_user_table' ), "
	last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_user_table' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 120 */

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_user_table', 'id', array( 'last_visit', 'date_created' ), array( 'last_visit_int', 'date_created_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_user_table' ), "date_created" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_user_table' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_user_table' ), "last_visit" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_user_table' ), "last_visit_int", "last_visit", "last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_email_table' ), "
	submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_email_table', 'email_id', 'submitted', 'submitted_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_email_table' ), "submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_email_table' ), "submitted_int", "submitted", "submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_tag_table' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 130 */
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_tag_table' ), "
	date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_tag_table', 'id', array( 'date_created', 'date_updated' ), array( 'date_created_int', 'date_updated_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_tag_table' ), "date_created" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_tag_table' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_tag_table' ), "date_updated" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_tag_table' ), "date_updated_int", "date_updated", "date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_tag_table' ), "
	date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_bug_tag_table', 'bug_id', 'date_attached', 'date_attached_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_tag_table' ), "date_attached" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bug_tag_table' ), "date_attached_int", "date_attached", "date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 140 */


$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_tokens_table' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_tokens_table' ), "
	expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_tokens_table', 'id', array( 'timestamp', 'expiry' ), array( 'timestamp_int', 'expiry_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_tokens_table' ), "timestamp" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_tokens_table' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_tokens_table' ), "expiry" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_tokens_table' ), "expiry_int", "expiry", "expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_news_table' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_news_table' ), "
	date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_news_table', 'id', array( 'date_posted', 'last_modified' ), array( 'date_posted_int', 'last_modified_int' ) ) );
/* 150 */

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_news_table' ), "last_modified" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_news_table' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_news_table' ), "date_posted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_news_table' ), "date_posted_int", "date_posted", "date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_rev_id_time',db_get_table( 'mantis_bug_revision_table' ),'bug_id, timestamp', array('DROP')), Array( 'db_index_exists', Array( db_get_table('mantis_bug_revision_table'), 'idx_bug_rev_id_time')));
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_revision_table' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_bug_revision_table', 'id', 'timestamp', 'timestamp_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_bug_revision_table' ), "timestamp" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_bug_revision_table' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_rev_id_time', db_get_table( 'mantis_bug_revision_table' ), 'bug_id, timestamp' ) );
/* 160 */

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_user_pref_table' ), "
	 timezone C(32) NOTNULL DEFAULT '' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_project_version_table' ), "
	date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_project_version_table', 'id', 'date_order', 'date_order_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_project_version_table' ), "date_order" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_project_version_table' ), "date_order_int", "date_order", "date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_sponsorship_table' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_sponsorship_table' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( 'mantis_sponsorship_table', 'id', array( 'date_submitted', 'last_updated' ), array( 'date_submitted_int', 'last_updated_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_sponsorship_table' ), "last_updated" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_sponsorship_table' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 170 */
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_sponsorship_table' ), "date_submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'mantis_sponsorship_table' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_project_file_table' ), "
	user_id			I		UNSIGNED NOTNULL DEFAULT '0' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'mantis_bug_file_table' ), "
	user_id		I  			UNSIGNED NOTNULL DEFAULT '0' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_custom_field_table'), "advanced" ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_user_pref_table'), "advanced_report" ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_user_pref_table'), "advanced_view" ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'mantis_user_pref_table'), "advanced_update" ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_project_hierarchy_child_id', db_get_table( 'mantis_project_hierarchy_table' ), 'child_id' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_project_hierarchy_parent_id', db_get_table( 'mantis_project_hierarchy_table' ), 'parent_id' ) );

/* 180 */
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_tag_name', db_get_table( 'mantis_tag_table' ), 'name' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_tag_tag_id', db_get_table( 'mantis_bug_tag_table' ), 'tag_id' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_email_id', db_get_table( 'mantis_email_table' ), 'email_id', array( 'DROP' ) ), Array( 'db_index_exists', Array( db_get_table( 'mantis_email_table' ), 'idx_email_id') ) );
$upgrade[] = Array( 'UpdateFunction', 'correct_multiselect_custom_fields_db_format' );
