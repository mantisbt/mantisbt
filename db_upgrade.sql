#
#	Run these queries to upgrade your bugtracker
#

#   Here's the basic command to login to your database via telnet/ssh.
#	mysql -u<username> -p<password> <databasename>
#

# =================
# 0.9.x to 0.10.x
# =================

# You will need to add a 'protected' field in the mantis_user_table.
# This command will do the trick:

ALTER TABLE mantis_user_table ADD protected VARCHAR (3) not null;

# =================
# 0.10.2 to 0.11.0
# =================

# inserted feedback as a status

ALTER TABLE mantis_bug_table CHANGE status status ENUM ('new','feedback',
	'acknowledged','confirmed','assigned','resolved') DEFAULT 'new' not null;

# New user profile tables

CREATE TABLE mantis_user_profile_table (
   id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL auto_increment,
   user_id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL,
   platform varchar(32) NOT NULL,
   os varchar(32) NOT NULL,
   os_build varchar(16) NOT NULL,
   description text NOT NULL,
   default_profile char(3) NOT NULL,
   PRIMARY KEY (id)
);

# Versions has been changed to be an enum(edit by hand or through the manage section

ALTER TABLE mantis_bug_table CHANGE version version ENUM ('none') not null;

# New table for default user preferences

CREATE TABLE mantis_user_pref_table (
   id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL auto_increment,
   user_id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL,
   advanced_report char(3) NOT NULL,
   advanced_view char(3) NOT NULL,
   PRIMARY KEY (id)
);

# =================
# 0.11.x to 0.12.0
# =================

# Add a new field to store profile descriptions

ALTER TABLE mantis_bug_table ADD profile_id INT (7) UNSIGNED ZEROFILL not null AFTER votes

# =================
# 0.12.x to 0.14.0
# =================

#
# Table structure for table 'mantis_projects_table'
#
# Supports multiple projects

CREATE TABLE mantis_project_table (
   id int(7) unsigned zerofill DEFAULT '0000001' NOT NULL auto_increment,
   name varchar(128) NOT NULL,
   status enum('development','release','stable','obsolete') DEFAULT 'development' NOT NULL,
   enabled char(3) NOT NULL,
   view_state set('public','private') DEFAULT 'public' NOT NULL,
   description text NOT NULL,
   PRIMARY KEY (id),
   KEY id (id),
   UNIQUE name (name)
);

INSERT INTO mantis_project_table VALUES ( '0000001', 'mantis', 'development', 'on', 'public', 'Mantis.  Report problems with the actual bug tracker here. (Do not remove this account.  You can set it to be disabled or private if you do not wish to see it)');

#
# Table structure for table 'mantis_project_category_table'
#

CREATE TABLE mantis_project_category_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   category varchar(32) NOT NULL
);

#
# Table structure for table 'mantis_project_version_table'
#

CREATE TABLE mantis_project_version_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   version varchar(32) NOT NULL
);

# Add new project_id fields for our tables

ALTER TABLE mantis_bug_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id;
ALTER TABLE mantis_news_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id;

# make changes to bug_table

ALTER TABLE mantis_bug_table CHANGE category category VARCHAR (32) not null;
ALTER TABLE mantis_bug_table CHANGE version version VARCHAR (32) DEFAULT 'none' not null;

# make changes to user_table

ALTER TABLE mantis_user_table ADD login_count INT not null DEFAULT '0' AFTER access_level;
ALTER TABLE mantis_user_table CHANGE access_level access_level ENUM ('viewer','reporter','updater','developer','manager','administrator') DEFAULT 'viewer' not null;
ALTER TABLE mantis_user_table ADD UNIQUE(username);

# =================
# 0.14.x to 0.15.0
# =================

# Change some of the TIMESTAMP fields to DATETIME

ALTER TABLE mantis_bug_table CHANGE date_submitted date_submitted DATETIME;
ALTER TABLE mantis_bugnote_table CHANGE date_submitted date_submitted DATETIME;
ALTER TABLE mantis_news_table CHANGE date_posted date_posted DATETIME;
ALTER TABLE mantis_user_table CHANGE date_created date_created DATETIME;

# INT(1) Updates (Before ALTERation)

UPDATE mantis_project_table SET enabled='0' WHERE enabled='';
UPDATE mantis_project_table SET enabled='1' WHERE enabled='on';

UPDATE mantis_user_pref_table SET advanced_report='0' WHERE advanced_report='';
UPDATE mantis_user_pref_table SET advanced_report='1' WHERE advanced_report='on';

UPDATE mantis_user_pref_table SET advanced_view='0' WHERE advanced_view='';
UPDATE mantis_user_pref_table SET advanced_view='1' WHERE advanced_view='on';

UPDATE mantis_user_profile_table SET default_profile='0' WHERE default_profile='';
UPDATE mantis_user_profile_table SET default_profile='1' WHERE default_profile='on';

UPDATE mantis_user_table SET enabled='0' WHERE enabled='';
UPDATE mantis_user_table SET enabled='1' WHERE enabled='on';

UPDATE mantis_user_table SET protected='0' WHERE protected='';
UPDATE mantis_user_table SET protected='1' WHERE protected='on';

# Change CHAR(3) to INT(1)

ALTER TABLE mantis_project_table CHANGE enabled enabled INT (1) not null;
ALTER TABLE mantis_user_pref_table CHANGE advanced_report advanced_report INT (1) not null;
ALTER TABLE mantis_user_pref_table CHANGE advanced_view advanced_view INT (1) not null;
ALTER TABLE mantis_user_profile_table CHANGE default_profile default_profile INT (1) not null;
ALTER TABLE mantis_user_table CHANGE enabled enabled INT (1) DEFAULT '1' not null;
ALTER TABLE mantis_user_table CHANGE protected protected INT (1) not null;

# ENUM Updates (Before ALTERation)

ALTER TABLE mantis_project_table CHANGE view_state view_state VARCHAR (32) DEFAULT 'public' not null;
UPDATE mantis_project_table SET view_state='10' WHERE view_state='public';
UPDATE mantis_project_table SET view_state='50' WHERE view_state='private';

ALTER TABLE mantis_project_table CHANGE status status VARCHAR (32) DEFAULT 'development' not null;
UPDATE mantis_project_table SET status='10' WHERE status='development';
UPDATE mantis_project_table SET status='30' WHERE status='release';
UPDATE mantis_project_table SET status='50' WHERE status='stable';
UPDATE mantis_project_table SET status='70' WHERE status='obsolete';

ALTER TABLE mantis_user_table CHANGE access_level access_level VARCHAR (32) DEFAULT 'viewer' not null;
UPDATE mantis_user_table SET access_level='10' WHERE access_level='viewer';
UPDATE mantis_user_table SET access_level='25' WHERE access_level='reporter';
UPDATE mantis_user_table SET access_level='40' WHERE access_level='updater';
UPDATE mantis_user_table SET access_level='55' WHERE access_level='developer';
UPDATE mantis_user_table SET access_level='70' WHERE access_level='manager';
UPDATE mantis_user_table SET access_level='90' WHERE access_level='administrator';

ALTER TABLE mantis_bug_table CHANGE eta eta VARCHAR (32) DEFAULT 'none' not null;
UPDATE mantis_bug_table SET eta='10' WHERE eta='none';
UPDATE mantis_bug_table SET eta='20' WHERE eta='< 1 day';
UPDATE mantis_bug_table SET eta='30' WHERE eta='2-3 days';
UPDATE mantis_bug_table SET eta='40' WHERE eta='< 1 week';
UPDATE mantis_bug_table SET eta='50' WHERE eta='< 1 month';
UPDATE mantis_bug_table SET eta='60' WHERE eta='> 1 month';

ALTER TABLE mantis_bug_table CHANGE projection projection VARCHAR (32) DEFAULT 'none' not null;
UPDATE mantis_bug_table SET projection='10' WHERE projection='none';
UPDATE mantis_bug_table SET projection='30' WHERE projection='tweak';
UPDATE mantis_bug_table SET projection='50' WHERE projection='minor fix';
UPDATE mantis_bug_table SET projection='70' WHERE projection='major rework';
UPDATE mantis_bug_table SET projection='90' WHERE projection='redesign';

ALTER TABLE mantis_bug_table CHANGE resolution resolution VARCHAR (32) DEFAULT 'open' not null;
UPDATE mantis_bug_table SET resolution='10' WHERE resolution='open';
UPDATE mantis_bug_table SET resolution='20' WHERE resolution='fixed';
UPDATE mantis_bug_table SET resolution='30' WHERE resolution='reopened';
UPDATE mantis_bug_table SET resolution='40' WHERE resolution='unable to duplicate';
UPDATE mantis_bug_table SET resolution='50' WHERE resolution='not fixable';
UPDATE mantis_bug_table SET resolution='60' WHERE resolution='duplicate';
UPDATE mantis_bug_table SET resolution='70' WHERE resolution='not a bug';
UPDATE mantis_bug_table SET resolution='80' WHERE resolution='suspended';

ALTER TABLE mantis_bug_table CHANGE priority priority VARCHAR (32) DEFAULT 'none' not null;
UPDATE mantis_bug_table SET priority='10' WHERE priority='none';
UPDATE mantis_bug_table SET priority='20' WHERE priority='low';
UPDATE mantis_bug_table SET priority='30' WHERE priority='normal';
UPDATE mantis_bug_table SET priority='40' WHERE priority='high';
UPDATE mantis_bug_table SET priority='50' WHERE priority='urgent';
UPDATE mantis_bug_table SET priority='60' WHERE priority='immediate';

ALTER TABLE mantis_bug_table CHANGE status status VARCHAR (32) DEFAULT 'new' not null;
UPDATE mantis_bug_table SET status='10' WHERE status='new';
UPDATE mantis_bug_table SET status='20' WHERE status='feedback';
UPDATE mantis_bug_table SET status='30' WHERE status='acknowledged';
UPDATE mantis_bug_table SET status='40' WHERE status='confirmed';
UPDATE mantis_bug_table SET status='50' WHERE status='assigned';
UPDATE mantis_bug_table SET status='90' WHERE status='resolved';
UPDATE mantis_bug_table SET status='90' WHERE status='closed';

ALTER TABLE mantis_bug_table CHANGE severity severity VARCHAR (32) DEFAULT 'minor' not null;
UPDATE mantis_bug_table SET severity='10' WHERE severity='feature';
UPDATE mantis_bug_table SET severity='20' WHERE severity='trivial';
UPDATE mantis_bug_table SET severity='30' WHERE severity='text';
UPDATE mantis_bug_table SET severity='40' WHERE severity='tweak';
UPDATE mantis_bug_table SET severity='50' WHERE severity='minor';
UPDATE mantis_bug_table SET severity='60' WHERE severity='major';
UPDATE mantis_bug_table SET severity='70' WHERE severity='crash';
UPDATE mantis_bug_table SET severity='80' WHERE severity='block';

ALTER TABLE mantis_bug_table CHANGE reproducibility reproducibility VARCHAR (32) DEFAULT 'always' not null;
UPDATE mantis_bug_table SET reproducibility='10' WHERE reproducibility='always';
UPDATE mantis_bug_table SET reproducibility='30' WHERE reproducibility='sometimes';
UPDATE mantis_bug_table SET reproducibility='50' WHERE reproducibility='random';
UPDATE mantis_bug_table SET reproducibility='70' WHERE reproducibility='have not tried';
UPDATE mantis_bug_table SET reproducibility='90' WHERE reproducibility='unable to duplicate';

# Change ENUM to INT

ALTER TABLE mantis_project_table CHANGE view_state view_state INT (2) DEFAULT '10' not null;
ALTER TABLE mantis_project_table CHANGE status status INT (2) DEFAULT '10' not null;
ALTER TABLE mantis_user_table CHANGE access_level access_level INT (2) DEFAULT '10' not null;
ALTER TABLE mantis_bug_table CHANGE eta eta INT (2) DEFAULT '10' not null;
ALTER TABLE mantis_bug_table CHANGE projection projection INT (2) DEFAULT '10' not null;
ALTER TABLE mantis_bug_table CHANGE resolution resolution INT (2) DEFAULT '10' not null;
ALTER TABLE mantis_bug_table CHANGE priority priority INT (2) DEFAULT '30' not null;
ALTER TABLE mantis_bug_table CHANGE status status INT (2) DEFAULT '10' not null;
ALTER TABLE mantis_bug_table CHANGE severity severity INT (2) DEFAULT '50' not null;
ALTER TABLE mantis_bug_table CHANGE reproducibility reproducibility INT (2) DEFAULT '10' not null;


# Update dates to be legal

UPDATE mantis_user_table SET date_created='1970-01-01 00:00:01' WHERE date_created='0000-00-00 00:00:00';
UPDATE mantis_bug_table SET date_submitted='1970-01-01 00:00:01' WHERE date_submitted='0000-00-00 00:00:00';
UPDATE mantis_news_table SET date_posted='1970-01-01 00:00:01' WHERE date_posted='0000-00-00 00:00:00';

# Shorten cookie string to 64 characters

ALTER TABLE mantis_user_table CHANGE cookie_string cookie_string VARCHAR (64) not null;

# Add file_path to projects, also min access

ALTER TABLE mantis_project_table ADD file_path VARCHAR (250) not null AFTER view_state;
ALTER TABLE mantis_project_table ADD access_min INT (2) DEFAULT '10' not null AFTER view_state;

# Add new user prefs

ALTER TABLE mantis_user_pref_table ADD refresh_delay INT (4) not null;
ALTER TABLE mantis_user_pref_table ADD language VARCHAR (16)DEFAULT 'english' not null;
ALTER TABLE mantis_user_pref_table ADD email_on_new INT (1) not null AFTER refresh_wait;
ALTER TABLE mantis_user_pref_table ADD email_on_assigned INT (1) not null AFTER email_on_new;
ALTER TABLE mantis_user_pref_table ADD email_on_feedback INT (1) not null AFTER email_on_assigned;
ALTER TABLE mantis_user_pref_table ADD email_on_resolved INT (1) not null AFTER email_on_feedback;
ALTER TABLE mantis_user_pref_table ADD email_on_closed INT (1) not null AFTER email_on_resolved;
ALTER TABLE mantis_user_pref_table ADD email_on_reopened INT (1) not null AFTER email_on_closed;
ALTER TABLE mantis_user_pref_table ADD email_on_bugnote INT (1) not null AFTER email_on_reopened;
ALTER TABLE mantis_user_pref_table ADD email_on_status INT (1) not null AFTER email_on_bugnote;
ALTER TABLE mantis_user_pref_table ADD redirect_delay INT (1) not null AFTER refresh_wait;
ALTER TABLE mantis_user_pref_table ADD email_on_priority INT (1) not null AFTER email_on_status;
ALTER TABLE mantis_user_pref_table ADD advanced_update INT (1) not null AFTER advanced_view;
ALTER TABLE mantis_user_pref_table ADD default_profile INT (7) UNSIGNED ZEROFILL DEFAULT '0' not null AFTER user_id;
ALTER TABLE mantis_user_pref_table ADD default_project INT (7) UNSIGNED ZEROFILL not null AFTER default_profile;
ALTER TABLE mantis_user_profile_table DROP default_profile;
ALTER TABLE mantis_user_pref_table DROP default_project_id;

# Make new project level user access table

CREATE TABLE mantis_project_user_list_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   user_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   access_level int(2) DEFAULT '10' NOT NULL
);
INSERT INTO mantis_project_user_list_table (project_id, user_id, access_level) VALUES ('0000000','0000000','00');


# Make new project file table

CREATE TABLE mantis_project_file_table (
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
   PRIMARY KEY (id)
);

# Make new bug file table

CREATE TABLE mantis_bug_file_table (
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
   PRIMARY KEY (id)
);

# Add a ordering field for versions

ALTER TABLE mantis_project_version_table ADD ver_order INT (7) not null

# Make the cookie string unique

ALTER TABLE mantis_user_table ADD UNIQUE(cookie_string) 