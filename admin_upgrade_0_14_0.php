<?php include( "core_API.php" ) ?>
<?php
        db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>
<?php
	if ( $f_action=="upgrade" ) {

$query3 = "CREATE TABLE mantis_project_table (
   id int(7) unsigned zerofill DEFAULT '0000001' NOT NULL auto_increment,
   name varchar(128) NOT NULL,
   status enum('development','release','stable','obsolete') DEFAULT 'development' NOT NULL,
   enabled char(3) NOT NULL,
   view_state set('public','private') DEFAULT 'public' NOT NULL,
   description text NOT NULL,
   PRIMARY KEY (id),
   KEY id (id),
   UNIQUE name (name)
)";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Added mantis_project_table<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

$query3 = "INSERT INTO mantis_project_table VALUES ( '0000001', 'mantis', 'development', 'on', 'public', 'Mantis.  Report problems with t
he actual bug tracker here. (Do not remove this account.  You can set it to be disabled or private if you do not wish to see i
t)')";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>inserted default project into mantis_project_table<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

$query3 = "CREATE TABLE mantis_project_category_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   category varchar(32) NOT NULL
)";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Added mantis_project_category_table<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

$query3 = "CREATE TABLE mantis_project_version_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   version varchar(32) NOT NULL
)";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Added mantis_project_version_table<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

PRINT "UPDATED PROJECTS";

		$query = "SELECT id, date_submitted, last_updated
			FROM $g_mantis_bug_table";
		$result = db_query( $query );
		$bug_count = db_num_rows( $result );

# update bug table
$query3 = "ALTER TABLE mantis_bug_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Added project_id to mantis_bug_table<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

$query3 = "ALTER TABLE mantis_bug_table CHANGE category category VARCHAR (32) not null";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Changed category to varchar<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

$query3 = "ALTER TABLE mantis_bug_table CHANGE version version VARCHAR (32) DEFAULT 'none' not null";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Changed version to varchar<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

		for ($i;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$query2 = "UPDATE $g_mantis_bug_table
				SET date_submitted='$v_date_submitted', last_updated='$v_last_updated', project_id='0000001'
				WHERE id='$v_id'";
			$result2 = db_query( $query2 );
		}


PRINT "UPGRADED BUGS";

		$query = "SELECT id, date_posted, last_modified
			FROM $g_mantis_news_table";
		$result = db_query( $query );
		$news_count = db_num_rows( $result );

# update news table
$query3 = "ALTER TABLE mantis_news_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Added project_id to news table<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

		for ($i;$i<$news_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$query2 = "UPDATE $g_mantis_news_table
				SET date_posted='$v_date_posted', last_modified='$v_last_modified', project_id='0000001'
				WHERE id='$v_id'";
			$result2 = db_query( $query2 );
		}


PRINT "UPGRADED NEWS";

		$query = "SELECT id, date_created, last_visit
			FROM $g_mantis_user_table";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

# update user tables
$query3 = "ALTER TABLE mantis_user_table ADD login_count INT not null DEFAULT '0' AFTER access_level";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Added login count to user_table<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

$query3 = "ALTER TABLE mantis_user_table CHANGE access_level access_level ENUM ('viewer','reporter','updater','developer','manager','administrator') DEFAULT 'viewer' not null";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Added manager to access_levels<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

$query3 = "ALTER TABLE mantis_user_table ADD UNIQUE(username)";
$result3 = db_query( $query3 );
if ( $result3 ) {
	PRINT "<p>Made username unique<p>";
} else {
	PRINT "FAILED $query3";
	exit;
}

		for ($i;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$query2 = "UPDATE $g_mantis_user_table
				SET date_created='$v_date_created', last_visit='$v_last_visit'
				WHERE id='$v_id'";
			$result2 = db_query( $query2 );
		}

PRINT "UPGRADED USER";

	}
?>
<?php if (!isset($f_action) ) { ?>
<p>
Upgrading your Mantis version from 0.12.x and 0.13.x to 0.14.0
<p>
<a href="<?php echo $PHP_SELF ?>?f_action=upgrade">Click here to upgrade</a>
<?php } else { ?>
<p>**** Upgrade is complete.
<?php } ?>
