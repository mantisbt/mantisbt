<?php
	require( "admin_inc.php" );
?>
<?php
# =================
# 0.14.x to 0.15.0
# =================

# preserve bug date information
PRINT "<p>Preserving Bug Date Information";
$save_bug_query = "SELECT id, last_updated FROM mantis_bug_table";
$save_bug_result = db_query( $save_bug_query );

# preserve user date information
PRINT "<p>Preserving User Date Information<p>";
$save_user_query = "SELECT id, date_created, last_visit FROM mantis_user_table ORDER BY id";
$save_user_result = db_query( $save_user_query );

$query0 = array();

$query0[0] = "ALTER TABLE mantis_project_table CHANGE view_state view_state VARCHAR (32) DEFAULT 'public' not null";
$query0[1] = "ALTER TABLE mantis_project_table CHANGE status status VARCHAR (32) DEFAULT 'development' not null";
$query0[2] = "ALTER TABLE mantis_user_table CHANGE access_level access_level VARCHAR (32) DEFAULT 'viewer' not null";
$query0[3] = "ALTER TABLE mantis_bug_table CHANGE eta eta VARCHAR (32) DEFAULT 'none' not null";
$query0[4] = "ALTER TABLE mantis_bug_table CHANGE projection projection VARCHAR (32) DEFAULT 'none' not null";
$query0[5] = "ALTER TABLE mantis_bug_table CHANGE resolution resolution VARCHAR (32) DEFAULT 'open' not null";
$query0[6] = "ALTER TABLE mantis_bug_table CHANGE priority priority VARCHAR (32) DEFAULT 'none' not null";
$query0[7] = "ALTER TABLE mantis_bug_table CHANGE status status VARCHAR (32) DEFAULT 'new' not null";
$query0[8] = "ALTER TABLE mantis_bug_table CHANGE severity severity VARCHAR (32) DEFAULT 'minor' not null";
$query0[9] = "ALTER TABLE mantis_bug_table CHANGE reproducibility reproducibility VARCHAR (32) DEFAULT 'always' not null";

# Make sure username is unique
# @@ err.. bad query.. just make it do nothing
$query0[10] = "SELECT id FROM mantis_project_table";


$query = array();

# Change some of the TIMESTAMP fields to DATETIME

$query[0] = "ALTER TABLE mantis_bug_table CHANGE date_submitted date_submitted DATETIME";
$query[1] = "ALTER TABLE mantis_bugnote_table CHANGE date_submitted date_submitted DATETIME";
$query[2] = "ALTER TABLE mantis_news_table CHANGE date_posted date_posted DATETIME";
$query[3] = "ALTER TABLE mantis_user_table CHANGE date_created date_created DATETIME";

# INT(1) Updates (Before ALTERation)

$query[4] = "UPDATE mantis_project_table SET enabled='0' WHERE enabled=''";
$query[5] = "UPDATE mantis_project_table SET enabled='1' WHERE enabled='on'";

$query[6] = "UPDATE mantis_user_pref_table SET advanced_report='0' WHERE advanced_report=''";
$query[7] = "UPDATE mantis_user_pref_table SET advanced_report='1' WHERE advanced_report='on'";

$query[8] = "UPDATE mantis_user_pref_table SET advanced_view='0' WHERE advanced_view=''";
$query[9] = "UPDATE mantis_user_pref_table SET advanced_view='1' WHERE advanced_view='on'";

$query[10] = "UPDATE mantis_user_profile_table SET default_profile='0' WHERE default_profile=''";
$query[11] = "UPDATE mantis_user_profile_table SET default_profile='1' WHERE default_profile='on'";

$query[12] = "UPDATE mantis_user_table SET enabled='0' WHERE enabled=''";
$query[13] = "UPDATE mantis_user_table SET enabled='1' WHERE enabled='on'";

$query[14] = "UPDATE mantis_user_table SET protected='0' WHERE protected=''";
$query[15] = "UPDATE mantis_user_table SET protected='1' WHERE protected='on'";

# Change CHAR(3) to INT(1)

$query[16] = "ALTER TABLE mantis_project_table CHANGE enabled enabled INT (1) not null";
$query[17] = "ALTER TABLE mantis_user_pref_table CHANGE advanced_report advanced_report INT (1) not null";
$query[18] = "ALTER TABLE mantis_user_pref_table CHANGE advanced_view advanced_view INT (1) not null";
$query[19] = "ALTER TABLE mantis_user_profile_table CHANGE default_profile default_profile INT (1) not null";
$query[20] = "ALTER TABLE mantis_user_table CHANGE enabled enabled INT (1) DEFAULT '1' not null";
$query[21] = "ALTER TABLE mantis_user_table CHANGE protected protected INT (1) not null";

# ENUM Updates (Before ALTERation)

$query[22] = "UPDATE mantis_project_table SET view_state='10' WHERE view_state='public'";
$query[23] = "UPDATE mantis_project_table SET view_state='50' WHERE view_state='private'";

$query[24] = "UPDATE mantis_project_table SET status='10' WHERE status='development'";
$query[25] = "UPDATE mantis_project_table SET status='30' WHERE status='release'";
$query[26] = "UPDATE mantis_project_table SET status='50' WHERE status='stable'";
$query[27] = "UPDATE mantis_project_table SET status='70' WHERE status='obsolete'";

$query[28] = "UPDATE mantis_user_table SET access_level='10' WHERE access_level='viewer'";
$query[29] = "UPDATE mantis_user_table SET access_level='25' WHERE access_level='reporter'";
$query[30] = "UPDATE mantis_user_table SET access_level='40' WHERE access_level='updater'";
$query[31] = "UPDATE mantis_user_table SET access_level='55' WHERE access_level='developer'";
$query[32] = "UPDATE mantis_user_table SET access_level='70' WHERE access_level='manager'";
$query[33] = "UPDATE mantis_user_table SET access_level='90' WHERE access_level='administrator'";

$query[34] = "UPDATE mantis_bug_table SET eta='10' WHERE eta='none'";
$query[35] = "UPDATE mantis_bug_table SET eta='20' WHERE eta='< 1 day'";
$query[36] = "UPDATE mantis_bug_table SET eta='30' WHERE eta='2-3 days'";
$query[37] = "UPDATE mantis_bug_table SET eta='40' WHERE eta='< 1 week'";
$query[38] = "UPDATE mantis_bug_table SET eta='50' WHERE eta='< 1 month'";
$query[39] = "UPDATE mantis_bug_table SET eta='60' WHERE eta='> 1 month'";

$query[40] = "UPDATE mantis_bug_table SET projection='10' WHERE projection='none'";
$query[41] = "UPDATE mantis_bug_table SET projection='30' WHERE projection='tweak'";
$query[42] = "UPDATE mantis_bug_table SET projection='50' WHERE projection='minor fix'";
$query[43] = "UPDATE mantis_bug_table SET projection='70' WHERE projection='major rework'";
$query[44] = "UPDATE mantis_bug_table SET projection='90' WHERE projection='redesign'";

$query[45] = "UPDATE mantis_bug_table SET resolution='10' WHERE resolution='open'";
$query[46] = "UPDATE mantis_bug_table SET resolution='20' WHERE resolution='fixed'";
$query[47] = "UPDATE mantis_bug_table SET resolution='30' WHERE resolution='reopened'";
$query[48] = "UPDATE mantis_bug_table SET resolution='40' WHERE resolution='unable to duplicate'";
$query[49] = "UPDATE mantis_bug_table SET resolution='50' WHERE resolution='not fixable'";
$query[50] = "UPDATE mantis_bug_table SET resolution='60' WHERE resolution='duplicate'";
$query[51] = "UPDATE mantis_bug_table SET resolution='70' WHERE resolution='not a bug'";
$query[52] = "UPDATE mantis_bug_table SET resolution='80' WHERE resolution='suspended'";

$query[53] = "UPDATE mantis_bug_table SET priority='10' WHERE priority='none'";
$query[54] = "UPDATE mantis_bug_table SET priority='20' WHERE priority='low'";
$query[55] = "UPDATE mantis_bug_table SET priority='30' WHERE priority='normal'";
$query[56] = "UPDATE mantis_bug_table SET priority='40' WHERE priority='high'";
$query[57] = "UPDATE mantis_bug_table SET priority='50' WHERE priority='urgent'";
$query[58] = "UPDATE mantis_bug_table SET priority='60' WHERE priority='immediate'";

$query[59] = "UPDATE mantis_bug_table SET status='10' WHERE status='new'";
$query[60] = "UPDATE mantis_bug_table SET status='20' WHERE status='feedback'";
$query[61] = "UPDATE mantis_bug_table SET status='30' WHERE status='acknowledged'";
$query[62] = "UPDATE mantis_bug_table SET status='40' WHERE status='confirmed'";
$query[63] = "UPDATE mantis_bug_table SET status='50' WHERE status='assigned'";
$query[64] = "UPDATE mantis_bug_table SET status='90' WHERE status='resolved'";
$query[65] = "UPDATE mantis_bug_table SET status='90' WHERE status='closed'";

$query[66] = "UPDATE mantis_bug_table SET severity='10' WHERE severity='feature'";
$query[67] = "UPDATE mantis_bug_table SET severity='20' WHERE severity='trivial'";
$query[68] = "UPDATE mantis_bug_table SET severity='30' WHERE severity='text'";
$query[69] = "UPDATE mantis_bug_table SET severity='40' WHERE severity='tweak'";
$query[70] = "UPDATE mantis_bug_table SET severity='50' WHERE severity='minor'";
$query[71] = "UPDATE mantis_bug_table SET severity='60' WHERE severity='major'";
$query[72] = "UPDATE mantis_bug_table SET severity='70' WHERE severity='crash'";
$query[73] = "UPDATE mantis_bug_table SET severity='80' WHERE severity='block'";

$query[74] = "UPDATE mantis_bug_table SET reproducibility='10' WHERE reproducibility='always'";
$query[75] = "UPDATE mantis_bug_table SET reproducibility='30' WHERE reproducibility='sometimes'";
$query[76] = "UPDATE mantis_bug_table SET reproducibility='50' WHERE reproducibility='random'";
$query[77] = "UPDATE mantis_bug_table SET reproducibility='70' WHERE reproducibility='have not tried'";
$query[78] = "UPDATE mantis_bug_table SET reproducibility='90' WHERE reproducibility='unable to duplicate'";

# Change ENUM to INT

$query[79] = "ALTER TABLE mantis_project_table CHANGE view_state view_state INT (2) DEFAULT '10' not null";
$query[80] = "ALTER TABLE mantis_project_table CHANGE status status INT (2) DEFAULT '10' not null";
$query[81] = "ALTER TABLE mantis_user_table CHANGE access_level access_level INT (2) DEFAULT '10' not null";
$query[82] = "ALTER TABLE mantis_bug_table CHANGE eta eta INT (2) DEFAULT '10' not null";
$query[83] = "ALTER TABLE mantis_bug_table CHANGE projection projection INT (2) DEFAULT '10' not null";
$query[84] = "ALTER TABLE mantis_bug_table CHANGE resolution resolution INT (2) DEFAULT '10' not null";
$query[85] = "ALTER TABLE mantis_bug_table CHANGE priority priority INT (2) DEFAULT '30' not null";
$query[86] = "ALTER TABLE mantis_bug_table CHANGE status status INT (2) DEFAULT '10' not null";

# Update dates to be legal

$query[87] = "UPDATE mantis_user_table SET date_created='1970-01-01 00:00:01' WHERE date_created='0000-00-00 00:00:00'";
$query[88] = "UPDATE mantis_bug_table SET date_submitted='1970-01-01 00:00:01' WHERE date_submitted='0000-00-00 00:00:00'";
$query[89] = "UPDATE mantis_news_table SET date_posted='1970-01-01 00:00:01' WHERE date_posted='0000-00-00 00:00:00'";

# Shorten cookie string to 64 characters

$query[90] = "ALTER TABLE mantis_user_table CHANGE cookie_string cookie_string VARCHAR (64) not null";

# Add file_path to projects, also min access

$query[91] = "ALTER TABLE mantis_project_table ADD file_path VARCHAR (250) not null";
$query[92] = "ALTER TABLE mantis_project_table ADD access_min INT (2) DEFAULT '10' not null";

# Add new user prefs

$query[93] = "ALTER TABLE mantis_user_pref_table ADD refresh_delay INT (4) not null";
$query[94] = "ALTER TABLE mantis_user_pref_table ADD language VARCHAR (16)DEFAULT 'english' not null";
$query[95] = "ALTER TABLE mantis_user_pref_table ADD email_on_new INT (1) not null";
$query[96] = "ALTER TABLE mantis_user_pref_table ADD email_on_assigned INT (1) not null";
$query[97] = "ALTER TABLE mantis_user_pref_table ADD email_on_feedback INT (1) not null";
$query[98] = "ALTER TABLE mantis_user_pref_table ADD email_on_resolved INT (1) not null";
$query[99] = "ALTER TABLE mantis_user_pref_table ADD email_on_closed INT (1) not null";
$query[100] = "ALTER TABLE mantis_user_pref_table ADD email_on_reopened INT (1) not null";
$query[101] = "ALTER TABLE mantis_user_pref_table ADD email_on_bugnote INT (1) not null";
$query[102] = "ALTER TABLE mantis_user_pref_table ADD email_on_status INT (1) not null";
$query[103] = "ALTER TABLE mantis_user_pref_table ADD redirect_delay INT (1) not null";
$query[104] = "ALTER TABLE mantis_user_pref_table ADD email_on_priority INT (1) not null";
$query[105] = "ALTER TABLE mantis_user_pref_table ADD advanced_update INT (1) not null";
$query[106] = "ALTER TABLE mantis_user_pref_table ADD default_profile INT (7) UNSIGNED ZEROFILL DEFAULT '0' not null";
$query[107] = "ALTER TABLE mantis_user_pref_table ADD default_project INT (7) UNSIGNED ZEROFILL not null";
$query[108] = "ALTER TABLE mantis_user_profile_table DROP default_profile";


# Make new project level user access table

$query[109] = "CREATE TABLE mantis_project_user_list_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   user_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   access_level int(2) DEFAULT '10' NOT NULL)";

# Make new project file table

$query[110] = "CREATE TABLE mantis_project_file_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   title varchar(250) NOT NULL,
   description varchar(250) NOT NULL,
   diskfile varchar(250) NOT NULL,
   filename varchar(250) NOT NULL,
   folder varchar(250) NOT NULL,
   filesize int(11) DEFAULT '0' NOT NULL,
   date_added datetime DEFAULT '1970-01-01 00:00:01' NOT NULL,
   content blob NOT NULL,
   PRIMARY KEY (id))";

# Make new bug file table

$query[111] = "CREATE TABLE mantis_bug_file_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   bug_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   title varchar(250) NOT NULL,
   description varchar(250) NOT NULL,
   diskfile varchar(250) NOT NULL,
   filename varchar(250) NOT NULL,
   folder varchar(250) NOT NULL,
   filesize int(11) DEFAULT '0' NOT NULL,
   date_added datetime DEFAULT '1970-01-01 00:00:01' NOT NULL,
   content blob NOT NULL,
   PRIMARY KEY (id))";

# more varchar to enum conversions

$query[112] = "ALTER TABLE mantis_bug_table CHANGE severity severity INT (2) DEFAULT '50' not null";
$query[113] = "ALTER TABLE mantis_bug_table CHANGE reproducibility reproducibility INT (2) DEFAULT '10' not null";

# Need this entry for the project listing to work

$query[114] = "INSERT INTO mantis_project_user_list_table (project_id, user_id, access_level) VALUES ('0000000','0000000','00')";

# Add ordering field for versions

$query[115] = "ALTER TABLE mantis_project_version_table ADD ver_order INT (7) not null";

# Make the cookie string unique

$query[116] = "ALTER TABLE mantis_user_table ADD UNIQUE(cookie_string)";

# ---------------
# run queries
# ---------------

for ($i=0;$i<count($query0);$i++) {
	$result = db_query( $query0[$i] );
	if ( $result <= 0 ) {
		PRINT "ERROR: $query0[$i]<br />";
	} else {
		PRINT "<span class=\"bold\">$i</span>: $query0[$i]<br />";
	}
}

PRINT "<p>";
for ($i=0;$i<count($query);$i++) {
	$result = db_query( $query[$i] );
	if ( $result <= 0 ) {
		PRINT "ERROR: $query[$i]<br />";
	} else {
		PRINT "<span class=\"bold\">$i</span>: $query[$i]<br />";
	}
}

PRINT "<p>Restoring Bug Date Information";
# Restore bug data information

$save_bug_count = db_num_rows( $save_bug_result );
for ($i=0;$i<$save_bug_count;$i++) {
	$row = db_fetch_array( $save_bug_result );
	extract( $row );
	$run_query = "UPDATE mantis_bug_table SET last_updated='$last_updated' WHERE id='$id'";
	$run_result = db_query( $run_query );
}

PRINT "<p>Restoring User Date Information";
# Restore bug data information

$save_user_count = db_num_rows( $save_user_result );
for ($i=0;$i<$save_user_count;$i++) {
	$row = db_fetch_array( $save_user_result );
	extract( $row );
	if ( isset( $last_visit ) ) {
		$run_query = "UPDATE mantis_user_table SET last_visit='$last_visit' WHERE id='$id'";
	} else {
		$run_query = "UPDATE mantis_user_table SET last_visit=NOW() WHERE id='$id'";
	}
	$run_result = db_query( $run_query );
}

?>
<p>Finished
