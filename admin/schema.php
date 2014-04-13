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
 *      CreateTable, DropTable, RenameTable, RenameColumn, AlterColumn, DropColumn
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

/**
 * Begin schema definition
 *
 * 'Release markers' are placed right AFTER the last schema step that is
 * included in the corresponding release
 */
$upgrade[  0] = array('CreateTable',array('{config}',"
 config_id C(64) NOTNULL PRIMARY,
 project_id I DEFAULT '0' PRIMARY,
 user_id I DEFAULT '0' PRIMARY,
 access_reqd I DEFAULT '0',
 type I DEFAULT '90',
 value XL NOTNULL"));
$upgrade[  1] = array('CreateIndex',array('idx_config','{config}','config_id'));
$upgrade[  2] = array('CreateTable',array('{bug_file}',"
  id			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT '',
  description 		C(250) NOTNULL DEFAULT '',
  diskfile 		C(250) NOTNULL DEFAULT '',
  filename 		C(250) NOTNULL DEFAULT '',
  folder 		C(250) NOTNULL DEFAULT '',
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT '',
  date_added 		T NOTNULL DEFAULT '" . db_null_date() . "',
  content 		B NOTNULL
  "));
$upgrade[  3] = array('CreateIndex',array('idx_bug_file_bug_id','{bug_file}','bug_id'));
$upgrade[  4] = array('CreateTable',array('{bug_history}',"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  field_name 		C(32) NOTNULL DEFAULT '',
  old_value 		C(128) NOTNULL DEFAULT '',
  new_value 		C(128) NOTNULL DEFAULT '',
  type 			I2 NOTNULL DEFAULT '0'
  "));
$upgrade[  5] = array('CreateIndex',array('idx_bug_history_bug_id','{bug_history}','bug_id'));
$upgrade[  6] = array('CreateIndex',array('idx_history_user_id','{bug_history}','user_id'));
$upgrade[  7] = array('CreateTable',array('{bug_monitor}',"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0'
"));
$upgrade[  8] = array('CreateTable',array('{bug_relationship}',"
  id 			 I  UNSIGNED NOTNULL AUTOINCREMENT PRIMARY,
  source_bug_id		 I  UNSIGNED NOTNULL DEFAULT '0',
  destination_bug_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  relationship_type 	I2 NOTNULL DEFAULT '0'
"));
$upgrade[  9] = array('CreateIndex',array('idx_relationship_source','{bug_relationship}','source_bug_id'));

# ----------------------------------------------------------------------------
# Schema version: 10
#
$upgrade[ 10] = array('CreateIndex',array('idx_relationship_destination','{bug_relationship}','destination_bug_id'));
$upgrade[ 11] = array('CreateTable',array('{bug}',"
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
  category 		C(64) NOTNULL DEFAULT '',
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_updated 		T NOTNULL DEFAULT '" . db_null_date() . "',
  eta 			I2 NOTNULL DEFAULT '10',
  bug_text_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  os 			C(32) NOTNULL DEFAULT '',
  os_build 		C(32) NOTNULL DEFAULT '',
  platform 		C(32) NOTNULL DEFAULT '',
  version 		C(64) NOTNULL DEFAULT '',
  fixed_in_version 	C(64) NOTNULL DEFAULT '',
  build 		C(32) NOTNULL DEFAULT '',
  profile_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  summary 		C(128) NOTNULL DEFAULT '',
  sponsorship_total 	 I  NOTNULL DEFAULT '0',
  sticky		L  NOTNULL DEFAULT  '0'
"));
$upgrade[ 12] = array('CreateIndex',array('idx_bug_sponsorship_total','{bug}','sponsorship_total'));
$upgrade[ 13] = array('CreateIndex',array('idx_bug_fixed_in_version','{bug}','fixed_in_version'));
$upgrade[ 14] = array('CreateIndex',array('idx_bug_status','{bug}','status'));
$upgrade[ 15] = array('CreateIndex',array('idx_project','{bug}','project_id'));
$upgrade[ 16] = array('CreateTable',array('{bug_text}',"
  id 			 I  PRIMARY UNSIGNED NOTNULL AUTOINCREMENT,
  description 		XL NOTNULL,
  steps_to_reproduce 	XL NOTNULL,
  additional_information XL NOTNULL
"));
$upgrade[ 17] = array('CreateTable',array('{bugnote}',"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  reporter_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bugnote_text_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  note_type 		 I  DEFAULT '0',
  note_attr 		C(250) DEFAULT ''
"));
$upgrade[ 18] = array('CreateIndex',array('idx_bug','{bugnote}','bug_id'));
$upgrade[ 19] = array('CreateIndex',array('idx_last_mod','{bugnote}','last_modified'));

# ----------------------------------------------------------------------------
# Schema version: 20
#
$upgrade[ 20] = array('CreateTable',array('{bugnote_text}',"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  note 			XL NOTNULL
"));
$upgrade[ 21] = array('CreateTable',array('{custom_field_project}',"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  sequence 		I2 NOTNULL DEFAULT '0'
"));
$upgrade[ 22] = array('CreateTable',array('{custom_field_string}',"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  value 		C(255) NOTNULL DEFAULT ''
"));
$upgrade[ 23] = array('CreateIndex',array('idx_custom_field_bug','{custom_field_string}','bug_id'));
$upgrade[ 24] = array('CreateTable',array('{custom_field}',"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  name 			C(64) NOTNULL DEFAULT '',
  type 			I2 NOTNULL DEFAULT '0',
  possible_values 	C(255) NOTNULL DEFAULT '',
  default_value 	C(255) NOTNULL DEFAULT '',
  valid_regexp 		C(255) NOTNULL DEFAULT '',
  access_level_r 	I2 NOTNULL DEFAULT '0',
  access_level_rw 	I2 NOTNULL DEFAULT '0',
  length_min 		 I  NOTNULL DEFAULT '0',
  length_max 		 I  NOTNULL DEFAULT '0',
  advanced 		L NOTNULL DEFAULT '0',
  require_report 	L NOTNULL DEFAULT '0',
  require_update 	L NOTNULL DEFAULT '0',
  display_report 	L NOTNULL DEFAULT '0',
  display_update 	L NOTNULL DEFAULT '1',
  require_resolved 	L NOTNULL DEFAULT '0',
  display_resolved 	L NOTNULL DEFAULT '0',
  display_closed 	L NOTNULL DEFAULT '0',
  require_closed 	L NOTNULL DEFAULT '0'
"));
$upgrade[ 25] = array('CreateIndex',array('idx_custom_field_name','{custom_field}','name'));
$upgrade[ 26] = array('CreateTable',array('{filters}',"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  NOTNULL DEFAULT '0',
  project_id 		 I  NOTNULL DEFAULT '0',
  is_public 		L DEFAULT NULL,
  name 			C(64) NOTNULL DEFAULT '',
  filter_string 	XL NOTNULL
"));
$upgrade[ 27] = array('CreateTable',array('{news}',"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  poster_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_posted 		T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  view_state 		I2 NOTNULL DEFAULT '10',
  announcement 		L NOTNULL DEFAULT '0',
  headline 		C(64) NOTNULL DEFAULT '',
  body 			XL NOTNULL
"));
$upgrade[ 28] = array('CreateTable',array('{project_category}',"
  project_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  category 		C(64) NOTNULL PRIMARY DEFAULT '',
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0'
"));
$upgrade[ 29] = array('CreateTable',array('{project_file}',"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT '',
  description 		C(250) NOTNULL DEFAULT '',
  diskfile 		C(250) NOTNULL DEFAULT '',
  filename 		C(250) NOTNULL DEFAULT '',
  folder 		C(250) NOTNULL DEFAULT '',
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT '',
  date_added 		T NOTNULL DEFAULT '" . db_null_date() . "',
  content 		B NOTNULL
"));

# ----------------------------------------------------------------------------
# Schema version: 30
#
$upgrade[ 30] = array('CreateTable',array('{project_hierarchy}',"
			  child_id I UNSIGNED NOTNULL,
			  parent_id I UNSIGNED NOTNULL"));
$upgrade[ 31] = array('CreateTable',array('{project}',"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  name 			C(128) NOTNULL DEFAULT '',
  status 		I2 NOTNULL DEFAULT '10',
  enabled 		L NOTNULL DEFAULT '1',
  view_state 		I2 NOTNULL DEFAULT '10',
  access_min 		I2 NOTNULL DEFAULT '10',
  file_path 		C(250) NOTNULL DEFAULT '',
  description 		XL NOTNULL
"));

$upgrade[ 32] = array('CreateIndex',array('idx_project_id','{project}','id'));
$upgrade[ 33] = array('CreateIndex',array('idx_project_name','{project}','name',array('UNIQUE')));
$upgrade[ 34] = array('CreateIndex',array('idx_project_view','{project}','view_state'));
$upgrade[ 35] = array('CreateTable',array('{project_user_list}',"
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  user_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10'
"));
$upgrade[ 36] = array( 'CreateIndex',array('idx_project_user','{project_user_list}','user_id'));
$upgrade[ 37] = array('CreateTable',array('{project_version}',"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  version 		C(64) NOTNULL DEFAULT '',
  date_order 		T NOTNULL DEFAULT '" . db_null_date() . "',
  description 		XL NOTNULL,
  released 		L NOTNULL DEFAULT '1'
"));
$upgrade[ 38] = array('CreateIndex',array('idx_project_version','{project_version}','project_id,version',array('UNIQUE')));
$upgrade[ 39] = array('CreateTable',array('{sponsorship}',"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  NOTNULL DEFAULT '0',
  user_id 		 I  NOTNULL DEFAULT '0',
  amount 		 I  NOTNULL DEFAULT '0',
  logo 			C(128) NOTNULL DEFAULT '',
  url 			C(128) NOTNULL DEFAULT '',
  paid 			L NOTNULL DEFAULT '0',
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_updated 		T NOTNULL DEFAULT '" . db_null_date() . "'
"));

# ----------------------------------------------------------------------------
# Schema version: 40
#
$upgrade[ 40] = array('CreateIndex',array('idx_sponsorship_bug_id','{sponsorship}','bug_id'));
$upgrade[ 41] = array('CreateIndex',array('idx_sponsorship_user_id','{sponsorship}','user_id'));
$upgrade[ 42] = array('CreateTable',array('{tokens}',"
			  id I NOTNULL PRIMARY AUTOINCREMENT,
			  owner I NOTNULL,
			  type I NOTNULL,
			  timestamp T NOTNULL,
			  expiry T,
			  value XL NOTNULL"));
$upgrade[ 43] = array('CreateTable',array('{user_pref}',"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  default_profile 	 I  UNSIGNED NOTNULL DEFAULT '0',
  default_project 	 I  UNSIGNED NOTNULL DEFAULT '0',
  advanced_report 	L NOTNULL DEFAULT '0',
  advanced_view 	L NOTNULL DEFAULT '0',
  advanced_update 	L NOTNULL DEFAULT '0',
  refresh_delay 	 I  NOTNULL DEFAULT '0',
  redirect_delay 	L NOTNULL DEFAULT '0',
  bugnote_order 	C(4) NOTNULL DEFAULT 'ASC',
  email_on_new 		L NOTNULL DEFAULT '0',
  email_on_assigned 	L NOTNULL DEFAULT '0',
  email_on_feedback 	L NOTNULL DEFAULT '0',
  email_on_resolved	L NOTNULL DEFAULT '0',
  email_on_closed 	L NOTNULL DEFAULT '0',
  email_on_reopened 	L NOTNULL DEFAULT '0',
  email_on_bugnote 	L NOTNULL DEFAULT '0',
  email_on_status 	L NOTNULL DEFAULT '0',
  email_on_priority 	L NOTNULL DEFAULT '0',
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
"));
$upgrade[ 44] = array('CreateTable',array('{user_print_pref}',"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  print_pref 		C(27) NOTNULL DEFAULT ''
"));
$upgrade[ 45] = array('CreateTable',array('{user_profile}',"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  platform 		C(32) NOTNULL DEFAULT '',
  os 			C(32) NOTNULL DEFAULT '',
  os_build 		C(32) NOTNULL DEFAULT '',
  description 		XL NOTNULL
"));
$upgrade[ 46] = array('CreateTable',array('{user}',"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  username 		C(32) NOTNULL DEFAULT '',
  realname 		C(64) NOTNULL DEFAULT '',
  email 		C(64) NOTNULL DEFAULT '',
  password 		C(32) NOTNULL DEFAULT '',
  date_created 		T NOTNULL DEFAULT '" . db_null_date() . "',
  last_visit 		T NOTNULL DEFAULT '" . db_null_date() . "',
  enabled		L NOTNULL DEFAULT '1',
  protected 		L NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10',
  login_count 		 I  NOTNULL DEFAULT '0',
  lost_password_request_count 	I2 NOTNULL DEFAULT '0',
  failed_login_count 	I2 NOTNULL DEFAULT '0',
  cookie_string 	C(64) NOTNULL DEFAULT ''
"));
$upgrade[ 47] = array('CreateIndex',array('idx_user_cookie_string','{user}','cookie_string',array('UNIQUE')));
$upgrade[ 48] = array('CreateIndex',array('idx_user_username','{user}','username',array('UNIQUE')));
$upgrade[ 49] = array('CreateIndex',array('idx_enable','{user}','enabled'));

# ----------------------------------------------------------------------------
# Schema version: 50
#
$upgrade[ 50] = array('CreateIndex',array('idx_access','{user}','access_level'));
$upgrade[ 51] = array( 'UpdateFunction', "do_nothing" );

# Release marker: 1.0.0 - 1.0.7

$upgrade[ 52] = array('AlterColumn', array( '{bug_history}', "old_value C(255) NOTNULL" ) );
$upgrade[ 53] = array('AlterColumn', array( '{bug_history}', "new_value C(255) NOTNULL" ) );

$upgrade[ 54] = array('CreateTable',array('{email}',"
  email_id 		I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  email		 	C(64) NOTNULL DEFAULT '',
  subject		C(250) NOTNULL DEFAULT '',
  submitted		T NOTNULL DEFAULT '" . db_null_date() . "',
  metadata 		XL NOTNULL,
  body 			XL NOTNULL
  "));

$upgrade[ 55] = array('CreateIndex',array('idx_email_id','{email}','email_id'));
$upgrade[ 56] = array('AddColumn',array('{bug}', "target_version C(64) NOTNULL DEFAULT ''"));
$upgrade[ 57] = array('AddColumn',array('{bugnote}', "time_tracking I UNSIGNED NOTNULL DEFAULT 0"));
$upgrade[ 58] = array('CreateIndex',array('idx_diskfile','{bug_file}','diskfile'));
$upgrade[ 59] = array('AlterColumn', array( '{user_print_pref}', 'print_pref C(64) NOTNULL' ) );

# ----------------------------------------------------------------------------
# Schema version: 60
#
$upgrade[ 60] = array('AlterColumn', array( '{bug_history}', 'field_name C(64) NOTNULL' ) );

# Release marker: 1.1.0a4

$upgrade[ 61] = array('CreateTable', array( '{tag}', "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(100)	NOTNULL PRIMARY DEFAULT '',
	description		XL		NOTNULL,
	date_created	T		NOTNULL DEFAULT '" . db_null_date() . "',
	date_updated	T		NOTNULL DEFAULT '" . db_null_date() . "'
	" ) );
$upgrade[ 62] = array('CreateTable', array( '{bug_tag}', "
	bug_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	tag_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	user_id			I	UNSIGNED NOTNULL DEFAULT '0',
	date_attached	T	NOTNULL DEFAULT '" . db_null_date() . "'
	" ) );

$upgrade[ 63] = array('CreateIndex', array( 'idx_typeowner', '{tokens}', 'type, owner' ) );

# Release marker: 1.1.0 - 1.1.8
# Release marker: 1.2.0-SVN

$upgrade[ 64] = array('CreateTable', array( '{plugin}', "
	basename		C(40)	NOTNULL PRIMARY,
	enabled			L		NOTNULL DEFAULT '0'
	" ) );

$upgrade[ 65] = array('AlterColumn', array( '{user_pref}', "redirect_delay 	I NOTNULL DEFAULT 0" ) );
$upgrade[ 66] = array('AlterColumn', array( '{custom_field}', "possible_values X NOTNULL" ) );
$upgrade[ 67] = array( 'CreateTable', array( '{category}', "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	project_id		I		UNSIGNED NOTNULL DEFAULT '0',
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(128)	NOTNULL DEFAULT '',
	status			I		UNSIGNED NOTNULL DEFAULT '0'
	" ) );
$upgrade[ 68] = array( 'CreateIndex', array( 'idx_category_project_name', '{category}', 'project_id, name', array( 'UNIQUE' ) ) );
$upgrade[ 69] = array( 'InsertData', array( '{category}', "
	( project_id, user_id, name, status ) VALUES
	( '0', '0', 'General', '0' ) " ) );

# ----------------------------------------------------------------------------
# Schema version: 70
#
$upgrade[ 70] = array( 'AddColumn', array( '{bug}', "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
$upgrade[ 71] = array( 'UpdateFunction', "category_migrate" );
$upgrade[ 72] = array( 'DropColumn', array( '{bug}', "category" ) );
$upgrade[ 73] = array( 'DropTable', array( '{project_category}' ) );
$upgrade[ 74] = array( 'AddColumn', array( '{project}', "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );

# remove unnecessary indexes
$upgrade[ 75] = array('CreateIndex',array('idx_project_id','{project}','id', array('DROP')), array( 'db_index_exists', array( '{project}', 'idx_project_id')));
$upgrade[ 76] = array('CreateIndex',array('idx_config','{config}','config_id', array('DROP')), array( 'db_index_exists', array( '{config}', 'idx_config')));

$upgrade[ 77] = array( 'InsertData', array( '{plugin}', "
	( basename, enabled ) VALUES
	( 'MantisCoreFormatting', '1' )" ) );

$upgrade[ 78] = array( 'AddColumn', array( '{project}', "inherit_global I UNSIGNED NOTNULL DEFAULT '0'" ) );
$upgrade[ 79] = array( 'AddColumn', array( '{project_hierarchy}', "inherit_parent I UNSIGNED NOTNULL DEFAULT '0'" ) );

# ----------------------------------------------------------------------------
# Schema version: 80
#
$upgrade[ 80] = array( 'AddColumn', array( '{plugin}', "
	protected		L		NOTNULL DEFAULT '0',
	priority		I		UNSIGNED NOTNULL DEFAULT '3'
	" ) );
$upgrade[ 81] = array( 'AddColumn', array( '{project_version}', "
	obsolete		L		NOTNULL DEFAULT '0'" ) );
$upgrade[ 82] = array( 'AddColumn', array( '{bug}', "
	due_date        T       NOTNULL DEFAULT '" . db_null_date() . "' " ) );

# Release marker: 1.2.0a1

$upgrade[ 83] = array( 'AddColumn', array( '{custom_field}', "filter_by 		L 		NOTNULL DEFAULT '1'" ) );

# Release marker: 1.2.0a2 - 1.2.0a3

$upgrade[ 84] = array( 'CreateTable', array( '{bug_revision}', "
	id			I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	bug_id		I		UNSIGNED NOTNULL,
	bugnote_id	I		UNSIGNED NOTNULL DEFAULT '0',
	user_id		I		UNSIGNED NOTNULL,
	timestamp	T		NOTNULL DEFAULT '" . db_null_date() . "',
	type		I		UNSIGNED NOTNULL,
	value		XL		NOTNULL
	" ) );
$upgrade[ 85] = array( 'CreateIndex', array( 'idx_bug_rev_id_time', '{bug_revision}', 'bug_id, timestamp' ) );
$upgrade[ 86] = array( 'CreateIndex', array( 'idx_bug_rev_type', '{bug_revision}', 'type' ) );

# Date conversion
$upgrade[ 87] = array( 'AddColumn', array( '{bug}', "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 88] = array( 'AddColumn', array( '{bug}', "
	due_date_int        			I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 89] = array( 'AddColumn', array( '{bug}', "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 90
#
$upgrade[ 90] = array( 'UpdateFunction', "date_migrate", array( '{bug}', 'id', array( 'date_submitted', 'due_date', 'last_updated' ), array( 'date_submitted_int', 'due_date_int', 'last_updated_int' ) ) );

$upgrade[ 91] = array( 'DropColumn', array( '{bug}', "date_submitted" ) );
$upgrade[ 92] = array( 'RenameColumn', array( '{bug}', "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 93] = array( 'DropColumn', array( '{bug}', "due_date" ) );
$upgrade[ 94] = array( 'RenameColumn', array( '{bug}', "due_date_int", "due_date", "due_date_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 95] = array( 'DropColumn', array( '{bug}', "last_updated" ) );
$upgrade[ 96] = array( 'RenameColumn', array( '{bug}', "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[ 97] = array('CreateIndex',array('idx_last_mod','{bugnote}','last_modified', array('DROP')), array( 'db_index_exists', array( '{bugnote}', 'idx_last_mod')));

$upgrade[ 98] = array( 'AddColumn', array( '{bugnote}', "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[ 99] = array( 'AddColumn', array( '{bugnote}', "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 100
#
$upgrade[100] = array( 'UpdateFunction', "date_migrate", array( '{bugnote}', 'id', array( 'last_modified', 'date_submitted' ), array( 'last_modified_int', 'date_submitted_int' ) ) );

$upgrade[101] = array( 'DropColumn', array( '{bugnote}', "last_modified" ) );
$upgrade[102] = array( 'RenameColumn', array( '{bugnote}', "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[103] = array('CreateIndex',array('idx_last_mod','{bugnote}','last_modified'));
$upgrade[104] = array( 'DropColumn', array( '{bugnote}', "date_submitted" ) );
$upgrade[105] = array( 'RenameColumn', array( '{bugnote}', "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[106] = array( 'AddColumn', array( '{bug_file}', "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[107] = array( 'UpdateFunction', "date_migrate", array( '{bug_file}', 'id', 'date_added', 'date_added_int' ) );
$upgrade[108] = array( 'DropColumn', array( '{bug_file}', "date_added" ) );
$upgrade[109] = array( 'RenameColumn', array( '{bug_file}', "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 110
#

$upgrade[110] = array( 'AddColumn', array( '{project_file}', "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[111] = array( 'UpdateFunction', "date_migrate", array( '{project_file}', 'id', 'date_added', 'date_added_int' ) );
$upgrade[112] = array( 'DropColumn', array( '{project_file}', "date_added" ) );
$upgrade[113] = array( 'RenameColumn', array( '{project_file}', "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[114] = array( 'AddColumn', array( '{bug_history}', "
	date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[115] = array( 'UpdateFunction', "date_migrate", array( '{bug_history}', 'id', 'date_modified', 'date_modified_int' ) );
$upgrade[116] = array( 'DropColumn', array( '{bug_history}', "date_modified" ) );
$upgrade[117] = array( 'RenameColumn', array( '{bug_history}', "date_modified_int", "date_modified", "date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[118] = array( 'AddColumn', array( '{user}', "last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[119] = array( 'AddColumn', array( '{user}', "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 120
#

$upgrade[120] = array( 'UpdateFunction', "date_migrate", array( '{user}', 'id', array( 'last_visit', 'date_created' ), array( 'last_visit_int', 'date_created_int' ) ) );

$upgrade[121] = array( 'DropColumn', array( '{user}', "date_created" ) );
$upgrade[122] = array( 'RenameColumn', array( '{user}', "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[123] = array( 'DropColumn', array( '{user}', "last_visit" ) );
$upgrade[124] = array( 'RenameColumn', array( '{user}', "last_visit_int", "last_visit", "last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[125] = array( 'AddColumn', array( '{email}', "
	submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[126] = array( 'UpdateFunction', "date_migrate", array( '{email}', 'email_id', 'submitted', 'submitted_int' ) );
$upgrade[127] = array( 'DropColumn', array( '{email}', "submitted" ) );
$upgrade[128] = array( 'RenameColumn', array( '{email}', "submitted_int", "submitted", "submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[129] = array( 'AddColumn', array( '{tag}', "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 130
#
$upgrade[130] = array( 'AddColumn', array( '{tag}', "
	date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[131] = array( 'UpdateFunction', "date_migrate", array( '{tag}', 'id', array( 'date_created', 'date_updated' ), array( 'date_created_int', 'date_updated_int' ) ) );

$upgrade[132] = array( 'DropColumn', array( '{tag}', "date_created" ) );
$upgrade[133] = array( 'RenameColumn', array( '{tag}', "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[134] = array( 'DropColumn', array( '{tag}', "date_updated" ) );
$upgrade[135] = array( 'RenameColumn', array( '{tag}', "date_updated_int", "date_updated", "date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[136] = array( 'AddColumn', array( '{bug_tag}', "
	date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[137] = array( 'UpdateFunction', "date_migrate", array( '{bug_tag}', 'bug_id', 'date_attached', 'date_attached_int' ) );
$upgrade[138] = array( 'DropColumn', array( '{bug_tag}', "date_attached" ) );
$upgrade[139] = array( 'RenameColumn', array( '{bug_tag}', "date_attached_int", "date_attached", "date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 140
#

$upgrade[140] = array( 'AddColumn', array( '{tokens}', "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[141] = array( 'AddColumn', array( '{tokens}', "
	expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[142] = array( 'UpdateFunction', "date_migrate", array( '{tokens}', 'id', array( 'timestamp', 'expiry' ), array( 'timestamp_int', 'expiry_int' ) ) );

$upgrade[143] = array( 'DropColumn', array( '{tokens}', "timestamp" ) );
$upgrade[144] = array( 'RenameColumn', array( '{tokens}', "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[145] = array( 'DropColumn', array( '{tokens}', "expiry" ) );
$upgrade[146] = array( 'RenameColumn', array( '{tokens}', "expiry_int", "expiry", "expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[147] = array( 'AddColumn', array( '{news}', "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[148] = array( 'AddColumn', array( '{news}', "
	date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[149] = array( 'UpdateFunction', "date_migrate", array( '{news}', 'id', array( 'date_posted', 'last_modified' ), array( 'date_posted_int', 'last_modified_int' ) ) );

# ----------------------------------------------------------------------------
# Schema version: 150
#

$upgrade[150] = array( 'DropColumn', array( '{news}', "last_modified" ) );
$upgrade[151] = array( 'RenameColumn', array( '{news}', "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[152] = array( 'DropColumn', array( '{news}', "date_posted" ) );
$upgrade[153] = array( 'RenameColumn', array( '{news}', "date_posted_int", "date_posted", "date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[154] = array('CreateIndex',array('idx_bug_rev_id_time','{bug_revision}','bug_id, timestamp', array('DROP')), array( 'db_index_exists', array( '{bug_revision}', 'idx_bug_rev_id_time')));
$upgrade[155] = array( 'AddColumn', array( '{bug_revision}', "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[156] = array( 'UpdateFunction', "date_migrate", array( '{bug_revision}', 'id', 'timestamp', 'timestamp_int' ) );
$upgrade[157] = array( 'DropColumn', array( '{bug_revision}', "timestamp" ) );
$upgrade[158] = array( 'RenameColumn', array( '{bug_revision}', "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[159] = array( 'CreateIndex', array( 'idx_bug_rev_id_time', '{bug_revision}', 'bug_id, timestamp' ) );

# ----------------------------------------------------------------------------
# Schema version: 160
#

$upgrade[160] = array( 'AddColumn', array( '{user_pref}', "
	 timezone C(32) NOTNULL DEFAULT '' " ) );

$upgrade[161] = array( 'AddColumn', array( '{project_version}', "
	date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[162] = array( 'UpdateFunction', "date_migrate", array( '{project_version}', 'id', 'date_order', 'date_order_int' ) );
$upgrade[163] = array( 'DropColumn', array( '{project_version}', "date_order" ) );
$upgrade[164] = array( 'RenameColumn', array( '{project_version}', "date_order_int", "date_order", "date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[165] = array( 'AddColumn', array( '{sponsorship}', "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
$upgrade[166] = array( 'AddColumn', array( '{sponsorship}', "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[167] = array( 'UpdateFunction', "date_migrate", array( '{sponsorship}', 'id', array( 'date_submitted', 'last_updated' ), array( 'date_submitted_int', 'last_updated_int' ) ) );

$upgrade[168] = array( 'DropColumn', array( '{sponsorship}', "last_updated" ) );
$upgrade[169] = array( 'RenameColumn', array( '{sponsorship}', "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

# ----------------------------------------------------------------------------
# Schema version: 170
#
$upgrade[170] = array( 'DropColumn', array( '{sponsorship}', "date_submitted" ) );
$upgrade[171] = array( 'RenameColumn', array( '{sponsorship}', "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

$upgrade[172] = array( 'AddColumn', array( '{project_file}', "user_id			I		UNSIGNED NOTNULL DEFAULT '0' " ) );
$upgrade[173] = array( 'AddColumn', array( '{bug_file}', "user_id		I  			UNSIGNED NOTNULL DEFAULT '0' " ) );

# Release marker: 1.2.0rc1

$upgrade[174] = array( 'DropColumn', array( '{custom_field}', "advanced" ) );
$upgrade[175] = array( 'DropColumn', array( '{user_pref}', "advanced_report" ) );
$upgrade[176] = array( 'DropColumn', array( '{user_pref}', "advanced_view" ) );
$upgrade[177] = array( 'DropColumn', array( '{user_pref}', "advanced_update" ) );
$upgrade[178] = array( 'CreateIndex', array( 'idx_project_hierarchy_child_id', '{project_hierarchy}', 'child_id' ) );

# Release marker: 1.2.0rc2

$upgrade[179] = array( 'CreateIndex', array( 'idx_project_hierarchy_parent_id', '{project_hierarchy}', 'parent_id' ) );

# ----------------------------------------------------------------------------
# Schema version: 180
#
$upgrade[180] = array( 'CreateIndex', array( 'idx_tag_name', '{tag}', 'name' ) );
$upgrade[181] = array( 'CreateIndex', array( 'idx_bug_tag_tag_id', '{bug_tag}', 'tag_id' ) );
$upgrade[182] = array( 'CreateIndex', array( 'idx_email_id', '{email}', 'email_id', array( 'DROP' ) ), array( 'db_index_exists', array( '{email}', 'idx_email_id') ) );

# Release marker: 1.2.0

$upgrade[183] = array( 'UpdateFunction', 'correct_multiselect_custom_fields_db_format' );

# Release marker: 1.2.1 - 1.2.15

$upgrade[184] = array( 'UpdateFunction', "stored_filter_migrate" );
$upgrade[185] = array( 'AddColumn', array( '{custom_field_string}', "text		XL  			NULL DEFAULT NULL " ) );
$upgrade[186] = array( 'UpdateFunction', 'update_history_long_custom_fields' );
$upgrade[187] = array( 'CreateIndex', array( 'idx_bug_id', '{bug_monitor}', 'bug_id' ) );
$upgrade[188] = array( 'AlterColumn', array( '{project}', "inherit_global L NOTNULL DEFAULT '0'" ) );
$upgrade[189] = array( 'AlterColumn', array( '{project_hierarchy}', "inherit_parent L NOTNULL DEFAULT '0'" ) );

# Release marker: 1.3.0
