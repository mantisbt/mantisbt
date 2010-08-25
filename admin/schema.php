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

$upgrade[] = Array('CreateTableSQL',Array(db_get_table( 'config' ),"
			  config_id C(64) NOTNULL PRIMARY,
			  project_id I DEFAULT '0' PRIMARY,
			  user_id I DEFAULT '0' PRIMARY,
			  access_reqd I DEFAULT '0',
			  type I DEFAULT '90',
			  value XL NOTNULL",
Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_config',db_get_table( 'config' ),'config_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bug_file'),"
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
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_file_bug_id',db_get_table('bug_file'),'bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bug_history'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  field_name 		C(32) NOTNULL DEFAULT \" '' \",
  old_value 		C(128) NOTNULL DEFAULT \" '' \",
  new_value 		C(128) NOTNULL DEFAULT \" '' \",
  type 			I2 NOTNULL DEFAULT '0'
  ",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_history_bug_id',db_get_table('bug_history'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_history_user_id',db_get_table('bug_history'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bug_monitor'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bug_relationship'),"
  id 			 I  UNSIGNED NOTNULL AUTOINCREMENT PRIMARY,
  source_bug_id		 I  UNSIGNED NOTNULL DEFAULT '0',
  destination_bug_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  relationship_type 	I2 NOTNULL DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_relationship_source',db_get_table('bug_relationship'),'source_bug_id'));
/* 10 */
$upgrade[] = Array('CreateIndexSQL',Array('idx_relationship_destination',db_get_table('bug_relationship'),'destination_bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bug'),"
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
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_sponsorship_total',db_get_table('bug'),'sponsorship_total'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_fixed_in_version',db_get_table('bug'),'fixed_in_version'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_status',db_get_table('bug'),'status'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project',db_get_table('bug'),'project_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bug_text'),"
  id 			 I  PRIMARY UNSIGNED NOTNULL AUTOINCREMENT,
  description 		XL NOTNULL,
  steps_to_reproduce 	XL NOTNULL,
  additional_information XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bugnote'),"
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
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug',db_get_table('bugnote'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_last_mod',db_get_table('bugnote'),'last_modified'));
/* 20 */
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('bugnote_text'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  note 			XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('custom_field_project'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  sequence 		I2 NOTNULL DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('custom_field_string'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  value 		C(255) NOTNULL DEFAULT \" '' \"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_custom_field_bug',db_get_table('custom_field_string'),'bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('custom_field'),"
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
$upgrade[] = Array('CreateIndexSQL',Array('idx_custom_field_name',db_get_table('custom_field'),'name'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('filters'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  NOTNULL DEFAULT '0',
  project_id 		 I  NOTNULL DEFAULT '0',
  is_public 		L DEFAULT NULL,
  name 			C(64) NOTNULL DEFAULT \" '' \",
  filter_string 	XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('news'),"
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
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('project_category'),"
  project_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  category 		C(64) NOTNULL PRIMARY DEFAULT \" '' \",
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('project_file'),"
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
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('project_hierarchy'),"
			  child_id I UNSIGNED NOTNULL,
			  parent_id I UNSIGNED NOTNULL",
Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('project'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  name 			C(128) NOTNULL DEFAULT \" '' \",
  status 		I2 NOTNULL DEFAULT '10',
  enabled 		L NOTNULL DEFAULT \" '1' \",
  view_state 		I2 NOTNULL DEFAULT '10',
  access_min 		I2 NOTNULL DEFAULT '10',
  file_path 		C(250) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_id',db_get_table('project'),'id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_name',db_get_table('project'),'name',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_view',db_get_table('project'),'view_state'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('project_user_list'),"
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  user_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10'
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array( 'CreateIndexSQL',Array('idx_project_user',db_get_table('project_user_list'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('project_version'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  version 		C(64) NOTNULL DEFAULT \" '' \",
  date_order 		T NOTNULL DEFAULT '" . db_null_date() . "',
  description 		XL NOTNULL,
  released 		L NOTNULL DEFAULT \" '1' \"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_version',db_get_table('project_version'),'project_id,version',Array('UNIQUE')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('sponsorship'),"
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
$upgrade[] = Array('CreateIndexSQL',Array('idx_sponsorship_bug_id',db_get_table('sponsorship'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_sponsorship_user_id',db_get_table('sponsorship'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('tokens'),"
			  id I NOTNULL PRIMARY AUTOINCREMENT,
			  owner I NOTNULL,
			  type I NOTNULL,
			  timestamp T NOTNULL,
			  expiry T,
			  value XL NOTNULL",
Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('user_pref'),"
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
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('user_print_pref'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  print_pref 		C(27) NOTNULL DEFAULT \" '' \"
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('user_profile'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  platform 		C(32) NOTNULL DEFAULT \" '' \",
  os 			C(32) NOTNULL DEFAULT \" '' \",
  os_build 		C(32) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(db_get_table('user'),"
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
$upgrade[] = Array('CreateIndexSQL',Array('idx_user_cookie_string',db_get_table('user'),'cookie_string',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_user_username',db_get_table('user'),'username',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_enable',db_get_table('user'),'enabled'));
/* 50 */
$upgrade[] = Array('CreateIndexSQL',Array('idx_access',db_get_table('user'),'access_level'));
$upgrade[] = Array('InsertData', Array( db_get_table('user'),
    "(username, realname, email, password, date_created, last_visit, enabled, protected, access_level, login_count, lost_password_request_count, failed_login_count, cookie_string) VALUES
        ('administrator', '', 'root@localhost', '63a9f0ea7bb98050796b649e85481845', '" . installer_db_now() . "', '" . installer_db_now() . "', '1', '0', 90, 3, 0, 0, '" .
             md5( mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() ) ) . md5( time() ) . "')" ) );
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'bug_history' ), "old_value C(255) NOTNULL" ) );
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'bug_history' ), "new_value C(255) NOTNULL" ) );

$upgrade[] = Array('CreateTableSQL',Array(db_get_table('email'),"
  email_id 		I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  email		 	C(64) NOTNULL DEFAULT \" '' \",
  subject		C(250) NOTNULL DEFAULT \" '' \",
  submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  metadata 		XL NOTNULL,
  body 			XL NOTNULL
  ",Array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_email_id',db_get_table('email'),'email_id'));
$upgrade[] = Array('AddColumnSQL',Array(db_get_table('bug'), "target_version C(64) NOTNULL DEFAULT \" '' \""));
$upgrade[] = Array('AddColumnSQL',Array(db_get_table('bugnote'), "time_tracking I UNSIGNED NOTNULL DEFAULT \" 0 \""));
$upgrade[] = Array('CreateIndexSQL',Array('idx_diskfile',db_get_table('bug_file'),'diskfile'));
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'user_print_pref' ), "print_pref C(64) NOTNULL" ) );
/* 60 */
$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'bug_history' ), "field_name C(64) NOTNULL" ) );

# Release marker: 1.1.0a4

$upgrade[] = Array('CreateTableSQL', Array( db_get_table( 'tag' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(100)	NOTNULL PRIMARY DEFAULT \" '' \",
	description		XL		NOTNULL,
	date_created	T		NOTNULL DEFAULT '" . db_null_date() . "',
	date_updated	T		NOTNULL DEFAULT '" . db_null_date() . "'
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[] = Array('CreateTableSQL', Array( db_get_table( 'bug_tag' ), "
	bug_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	tag_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	user_id			I	UNSIGNED NOTNULL DEFAULT '0',
	date_attached	T	NOTNULL DEFAULT '" . db_null_date() . "'
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

$upgrade[] = Array('CreateIndexSQL', Array( 'idx_typeowner', db_get_table( 'tokens' ), 'type, owner' ) );

# Release marker: 1.2.0-SVN

$upgrade[] = Array('CreateTableSQL', Array( db_get_table( 'plugin' ), "
	basename		C(40)	NOTNULL PRIMARY,
	enabled			L		NOTNULL DEFAULT \" '0' \"
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'user_pref' ), "redirect_delay 	I NOTNULL DEFAULT 0" ) );

/* apparently mysql now has a STRICT mode, where setting a DEFAULT value on a blob/text is now an error, instead of being silently ignored */
if ( isset( $f_db_type ) && ( $f_db_type == 'mysql' || $f_db_type == 'mysqli' ) ) {
	$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'custom_field' ), "possible_values X NOTNULL" ) );
} else {
	$upgrade[] = Array('AlterColumnSQL', Array( db_get_table( 'custom_field' ), "possible_values X NOTNULL DEFAULT \" '' \"" ) );
}

$upgrade[] = Array( 'CreateTableSQL', Array( db_get_table( 'category' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	project_id		I		UNSIGNED NOTNULL DEFAULT '0',
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(128)	NOTNULL DEFAULT \" '' \",
	status			I		UNSIGNED NOTNULL DEFAULT '0'
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_category_project_name', db_get_table( 'category' ), 'project_id, name', array( 'UNIQUE' ) ) );
$upgrade[] = Array( 'InsertData', Array( db_get_table( 'category' ), "
	( project_id, user_id, name, status ) VALUES
	( '0', '0', 'General', '0' ) " ) );
/* 70 */
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
$upgrade[] = Array( 'UpdateFunction', "category_migrate" );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug' ), "category" ) );
$upgrade[] = Array( 'DropTableSQL', Array( db_get_table( 'project_category' ) ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'project' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
// remove unnecessary indexes
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_id',db_get_table('project'),'id', array('DROP')), Array( 'db_index_exists', Array( db_get_table('project'), 'idx_project_id')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_config',db_get_table( 'config' ),'config_id', array('DROP')), Array( 'db_index_exists', Array( db_get_table('config'), 'idx_config')));

$upgrade[] = Array( 'InsertData', Array( db_get_table( 'plugin' ), "
	( basename, enabled ) VALUES
	( 'MantisCoreFormatting', '1' )" ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'project' ), "inherit_global I UNSIGNED NOTNULL DEFAULT '0'" ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'project_hierarchy' ), "inherit_parent I UNSIGNED NOTNULL DEFAULT '0'" ) );
/* 80 */
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'plugin' ), "
	protected		L		NOTNULL DEFAULT \" '0' \",
	priority		I		UNSIGNED NOTNULL DEFAULT '3'
	" ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'project_version' ), "
	obsolete		L		NOTNULL DEFAULT \" '0' \"" ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug' ), "
    due_date        T       NOTNULL DEFAULT '" . db_null_date() . "' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'custom_field' ), "
  filter_by 		L 		NOTNULL DEFAULT \" '1' \"" ) );
$upgrade[] = Array( 'CreateTableSQL', Array( db_get_table( 'bug_revision' ), "
	id			I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	bug_id		I		UNSIGNED NOTNULL,
	bugnote_id	I		UNSIGNED NOTNULL DEFAULT '0',
	user_id		I		UNSIGNED NOTNULL,
	timestamp	T		NOTNULL DEFAULT '" . db_null_date() . "',
	type		I		UNSIGNED NOTNULL,
	value		XL		NOTNULL
	", Array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_rev_id_time', db_get_table( 'bug_revision' ), 'bug_id, timestamp' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_rev_type', db_get_table( 'bug_revision' ), 'type' ) );

#date conversion

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug' ), "
	due_date_int        			I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 90 */
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug' ), 'id', array( 'date_submitted', 'due_date', 'last_updated' ), array( 'date_submitted_int', 'due_date_int', 'last_updated_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug' ), "date_submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bug' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug' ), "due_date" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bug' ), "due_date_int", "due_date", "due_date_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug' ), "last_updated" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bug' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array('CreateIndexSQL',Array('idx_last_mod',db_get_table( 'bugnote' ),'last_modified', array('DROP')), Array( 'db_index_exists', Array( db_get_table('bugnote'), 'idx_last_mod')));

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bugnote' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bugnote' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 100 */
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bugnote' ), 'id', array( 'last_modified', 'date_submitted' ), array( 'last_modified_int', 'date_submitted_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bugnote' ), "last_modified" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bugnote' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array('CreateIndexSQL',Array('idx_last_mod',db_get_table('bugnote'),'last_modified'));
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bugnote' ), "date_submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bugnote' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );


$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug_file' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_file' ), 'id', 'date_added', 'date_added_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug_file' ), "date_added" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bug_file' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 110 */

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'project_file' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'project_file' ), 'id', 'date_added', 'date_added_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'project_file' ), "date_added" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'project_file' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug_history' ), "
	date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_history' ), 'id', 'date_modified', 'date_modified_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug_history' ), "date_modified" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bug_history' ), "date_modified_int", "date_modified", "date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'user' ), "
	last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'user' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 120 */

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'user' ), 'id', array( 'last_visit', 'date_created' ), array( 'last_visit_int', 'date_created_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'user' ), "date_created" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'user' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'user' ), "last_visit" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'user' ), "last_visit_int", "last_visit", "last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'email' ), "
	submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'email' ), 'email_id', 'submitted', 'submitted_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'email' ), "submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'email' ), "submitted_int", "submitted", "submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'tag' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 130 */
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'tag' ), "
	date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'tag' ), 'id', array( 'date_created', 'date_updated' ), array( 'date_created_int', 'date_updated_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'tag' ), "date_created" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'tag' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'tag' ), "date_updated" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'tag' ), "date_updated_int", "date_updated", "date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug_tag' ), "
	date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_tag' ), 'bug_id', 'date_attached', 'date_attached_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug_tag' ), "date_attached" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bug_tag' ), "date_attached_int", "date_attached", "date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 140 */


$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'tokens' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'tokens' ), "
	expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'tokens' ), 'id', array( 'timestamp', 'expiry' ), array( 'timestamp_int', 'expiry_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'tokens' ), "timestamp" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'tokens' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'tokens' ), "expiry" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'tokens' ), "expiry_int", "expiry", "expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'news' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'news' ), "
	date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'news' ), 'id', array( 'date_posted', 'last_modified' ), array( 'date_posted_int', 'last_modified_int' ) ) );
/* 150 */

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'news' ), "last_modified" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'news' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'news' ), "date_posted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'news' ), "date_posted_int", "date_posted", "date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_rev_id_time',db_get_table( 'bug_revision' ),'bug_id, timestamp', array('DROP')), Array( 'db_index_exists', Array( db_get_table('bug_revision'), 'idx_bug_rev_id_time')));
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug_revision' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_revision' ), 'id', 'timestamp', 'timestamp_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'bug_revision' ), "timestamp" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'bug_revision' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_rev_id_time', db_get_table( 'bug_revision' ), 'bug_id, timestamp' ) );
/* 160 */

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'user_pref' ), "
	 timezone C(32) NOTNULL DEFAULT '' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'project_version' ), "
	date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'project_version' ), 'id', 'date_order', 'date_order_int' ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'project_version' ), "date_order" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'project_version' ), "date_order_int", "date_order", "date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'sponsorship' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'sponsorship' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'UpdateFunction', "date_migrate", array( db_get_table( 'sponsorship' ), 'id', array( 'date_submitted', 'last_updated' ), array( 'date_submitted_int', 'last_updated_int' ) ) );

$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'sponsorship' ), "last_updated" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'sponsorship' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 170 */
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'sponsorship' ), "date_submitted" ) );
$upgrade[] = Array( 'RenameColumnSQL', Array( db_get_table( 'sponsorship' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'project_file' ), "
	user_id			I		UNSIGNED NOTNULL DEFAULT '0' " ) );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'bug_file' ), "
	user_id		I  			UNSIGNED NOTNULL DEFAULT '0' " ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'custom_field'), "advanced" ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'user_pref'), "advanced_report" ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'user_pref'), "advanced_view" ) );
$upgrade[] = Array( 'DropColumnSQL', Array( db_get_table( 'user_pref'), "advanced_update" ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_project_hierarchy_child_id', db_get_table( 'project_hierarchy' ), 'child_id' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_project_hierarchy_parent_id', db_get_table( 'project_hierarchy' ), 'parent_id' ) );

/* 180 */
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_tag_name', db_get_table( 'tag' ), 'name' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_bug_tag_tag_id', db_get_table( 'bug_tag' ), 'tag_id' ) );
$upgrade[] = Array( 'CreateIndexSQL', Array( 'idx_email_id', db_get_table( 'email' ), 'email_id', array( 'DROP' ) ), Array( 'db_index_exists', Array( db_get_table( 'email' ), 'idx_email_id') ) );
$upgrade[] = Array( 'UpdateFunction', 'correct_multiselect_custom_fields_db_format' );
$upgrade[] = Array( 'UpdateFunction', "stored_filter_migrate" );
$upgrade[] = Array( 'AddColumnSQL', Array( db_get_table( 'custom_field_string' ), "
	text		XL  			NULL DEFAULT NULL " ) );
