<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# Changes applied to 0.14 database to give us 0.15

	# --------------------------------------------------------
	# $Id: 0_14_inc.php,v 1.9.16.1 2007-10-13 22:35:04 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'db_table_names_inc.php' );

	$upgrades = array();

# None of these have descriptions...

	$upgrades[] = new SQLUpgrade(
			'0.14a-0',
			'',
			"ALTER TABLE $t_project_table CHANGE view_state view_state VARCHAR (32) DEFAULT 'public' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-1',
			'',
			"ALTER TABLE $t_project_table CHANGE status status VARCHAR (32) DEFAULT 'development' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-2',
			'',
			"ALTER TABLE $t_user_table CHANGE access_level access_level VARCHAR (32) DEFAULT 'viewer' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-3',
			'',
			"ALTER TABLE $t_bug_table CHANGE eta eta VARCHAR (32) DEFAULT 'none' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-4',
			'',
			"ALTER TABLE $t_bug_table CHANGE projection projection VARCHAR (32) DEFAULT 'none' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-5',
			'',
			"ALTER TABLE $t_bug_table CHANGE resolution resolution VARCHAR (32) DEFAULT 'open' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-6',
			'',
			"ALTER TABLE $t_bug_table CHANGE priority priority VARCHAR (32) DEFAULT 'none' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-7',
			'',
			"ALTER TABLE $t_bug_table CHANGE status status VARCHAR (32) DEFAULT 'new' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-8',
			'',
			"ALTER TABLE $t_bug_table CHANGE severity severity VARCHAR (32) DEFAULT 'minor' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14a-9',
			'',
			"ALTER TABLE $t_bug_table CHANGE reproducibility reproducibility VARCHAR (32) DEFAULT 'always' not null"
		);

# Change some of the TIMESTAMP fields to DATETIME

	$upgrades[] = new SQLUpgrade(
			'0.14-0',
			'Change some of the TIMESTAMP fields to DATETIME',
			"ALTER TABLE $t_bug_table CHANGE date_submitted date_submitted DATETIME"
		);
	$upgrades[] = new SQLUpgrade(
			'0.14-1',
			'Change some of the TIMESTAMP fields to DATETIME',
			"ALTER TABLE $t_bugnote_table CHANGE date_submitted date_submitted DATETIME"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-2',
			'Change some of the TIMESTAMP fields to DATETIME',
			"ALTER TABLE $t_news_table CHANGE date_posted date_posted DATETIME"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-3',
			'Change some of the TIMESTAMP fields to DATETIME',
			"ALTER TABLE $t_user_table CHANGE date_created date_created DATETIME"
		);

# INT(1) Updates (Before ALTERation)


	$upgrades[] = new SQLUpgrade(
			'0.14-4',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_project_table SET enabled='0' WHERE enabled=''"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-5',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_project_table SET enabled='1' WHERE enabled='on'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-6',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_pref_table SET advanced_report='0' WHERE advanced_report=''"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-7',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_pref_table SET advanced_report='1' WHERE advanced_report='on'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-8',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_pref_table SET advanced_view='0' WHERE advanced_view=''"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-9',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_pref_table SET advanced_view='1' WHERE advanced_view='on'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-10',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_profile_table SET default_profile='0' WHERE default_profile=''"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-11',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_profile_table SET default_profile='1' WHERE default_profile='on'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-12',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_table SET enabled='0' WHERE enabled=''"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-13',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_table SET enabled='1' WHERE enabled='on'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-14',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_table SET protected='0' WHERE protected=''"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-15',
			'INT(1) Updates (Before ALTERation)',
			"UPDATE $t_user_table SET protected='1' WHERE protected='on'"
		);

# Change CHAR(3) to INT(1)


	$upgrades[] = new SQLUpgrade(
			'0.14-16',
			'Change CHAR(3) to INT(1)',
			"ALTER TABLE $t_project_table CHANGE enabled enabled INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-17',
			'Change CHAR(3) to INT(1)',
			"ALTER TABLE $t_user_pref_table CHANGE advanced_report advanced_report INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-18',
			'Change CHAR(3) to INT(1)',
			"ALTER TABLE $t_user_pref_table CHANGE advanced_view advanced_view INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-19',
			'Change CHAR(3) to INT(1)',
			"ALTER TABLE $t_user_profile_table CHANGE default_profile default_profile INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-20',
			'Change CHAR(3) to INT(1)',
			"ALTER TABLE $t_user_table CHANGE enabled enabled INT (1) DEFAULT '1' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-21',
			'Change CHAR(3) to INT(1)',
			"ALTER TABLE $t_user_table CHANGE protected protected INT (1) not null"
		);

# ENUM Updates (Before ALTERation)


	$upgrades[] = new SQLUpgrade(
			'0.14-22',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_project_table SET view_state='10' WHERE view_state='public'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-23',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_project_table SET view_state='50' WHERE view_state='private'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-24',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_project_table SET status='10' WHERE status='development'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-25',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_project_table SET status='30' WHERE status='release'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-26',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_project_table SET status='50' WHERE status='stable'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-27',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_project_table SET status='70' WHERE status='obsolete'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-28',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_user_table SET access_level='10' WHERE access_level='viewer'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-29',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_user_table SET access_level='25' WHERE access_level='reporter'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-30',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_user_table SET access_level='40' WHERE access_level='updater'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-31',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_user_table SET access_level='55' WHERE access_level='developer'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-32',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_user_table SET access_level='70' WHERE access_level='manager'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-33',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_user_table SET access_level='90' WHERE access_level='administrator'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-34',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET eta='10' WHERE eta='none'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-35',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET eta='20' WHERE eta='< 1 day'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-36',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET eta='30' WHERE eta='2-3 days'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-37',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET eta='40' WHERE eta='< 1 week'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-38',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET eta='50' WHERE eta='< 1 month'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-39',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET eta='60' WHERE eta='> 1 month'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-40',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET projection='10' WHERE projection='none'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-41',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET projection='30' WHERE projection='tweak'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-42',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET projection='50' WHERE projection='minor fix'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-43',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET projection='70' WHERE projection='major rework'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-44',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET projection='90' WHERE projection='redesign'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-45',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='10' WHERE resolution='open'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-46',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='20' WHERE resolution='fixed'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-47',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='30' WHERE resolution='reopened'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-48',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='40' WHERE resolution='unable to duplicate'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-49',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='50' WHERE resolution='not fixable'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-50',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='60' WHERE resolution='duplicate'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-51',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='70' WHERE resolution='not a bug'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-52',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET resolution='80' WHERE resolution='suspended'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-53',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET priority='10' WHERE priority='none'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-54',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET priority='20' WHERE priority='low'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-55',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET priority='30' WHERE priority='normal'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-56',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET priority='40' WHERE priority='high'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-57',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET priority='50' WHERE priority='urgent'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-58',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET priority='60' WHERE priority='immediate'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-59',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET status='10' WHERE status='new'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-60',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET status='20' WHERE status='feedback'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-61',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET status='30' WHERE status='acknowledged'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-62',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET status='40' WHERE status='confirmed'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-63',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET status='50' WHERE status='assigned'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-64',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET status='90' WHERE status='resolved'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-65',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET status='90' WHERE status='closed'"
		);


	$upgrades[] = new SQLUpgrade(
			'0.14-66',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='10' WHERE severity='feature'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-67',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='20' WHERE severity='trivial'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-68',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='30' WHERE severity='text'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-69',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='40' WHERE severity='tweak'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-70',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='50' WHERE severity='minor'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-71',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='60' WHERE severity='major'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-72',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='70' WHERE severity='crash'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-73',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET severity='80' WHERE severity='block'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-74',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET reproducibility='10' WHERE reproducibility='always'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-75',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET reproducibility='30' WHERE reproducibility='sometimes'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-76',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET reproducibility='50' WHERE reproducibility='random'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-77',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET reproducibility='70' WHERE reproducibility='have not tried'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-78',
			'ENUM Updates (Before ALTERation)',
			"UPDATE $t_bug_table SET reproducibility='90' WHERE reproducibility='unable to duplicate'"
		);

# Change ENUM to INT


	$upgrades[] = new SQLUpgrade(
			'0.14-79',
			'Change ENUM to INT',
			"ALTER TABLE $t_project_table CHANGE view_state view_state INT (2) DEFAULT '10' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-80',
			'Change ENUM to INT',
			"ALTER TABLE $t_project_table CHANGE status status INT (2) DEFAULT '10' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-81',
			'Change ENUM to INT',
			"ALTER TABLE $t_user_table CHANGE access_level access_level INT (2) DEFAULT '10' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-82',
			'Change ENUM to INT',
			"ALTER TABLE $t_bug_table CHANGE eta eta INT (2) DEFAULT '10' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-83',
			'Change ENUM to INT',
			"ALTER TABLE $t_bug_table CHANGE projection projection INT (2) DEFAULT '10' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-84',
			'Change ENUM to INT',
			"ALTER TABLE $t_bug_table CHANGE resolution resolution INT (2) DEFAULT '10' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-85',
			'Change ENUM to INT',
			"ALTER TABLE $t_bug_table CHANGE priority priority INT (2) DEFAULT '30' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-86',
			'Change ENUM to INT',
			"ALTER TABLE $t_bug_table CHANGE status status INT (2) DEFAULT '10' not null"
		);

# Update dates to be legal

	$upgrades[] = new SQLUpgrade(
			'0.14-87',
			'Update dates to be legal',
			"UPDATE $t_user_table SET date_created='1970-01-01 00:00:01' WHERE date_created='0000-00-00 00:00:00'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-88',
			'Update dates to be legal',
			"UPDATE $t_bug_table SET date_submitted='1970-01-01 00:00:01' WHERE date_submitted='0000-00-00 00:00:00'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-89',
			'Update dates to be legal',
			"UPDATE $t_news_table SET date_posted='1970-01-01 00:00:01' WHERE date_posted='0000-00-00 00:00:00'"
		);

# Shorten cookie string to 64 characters


	$upgrades[] = new SQLUpgrade(
			'0.14-90',
			'Shorten cookie string to 64 characters',
			"ALTER TABLE $t_user_table CHANGE cookie_string cookie_string VARCHAR (64) not null"
		);

# Add file_path to projects, also min access

	$upgrades[] = new SQLUpgrade(
			'0.14-91',
			'Add file_path to projects',
			"ALTER TABLE $t_project_table ADD file_path VARCHAR (250) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-92',
			'Add access_min to projects',
			"ALTER TABLE $t_project_table ADD access_min INT (2) DEFAULT '10' not null"
		);

# Add new user prefs

	$upgrades[] = new SQLUpgrade(
			'0.14-93',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD refresh_delay INT (4) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-94',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD language VARCHAR (16)DEFAULT 'english' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-95',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_new INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-96',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_assigned INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-97',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_feedback INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-98',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_resolved INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-99',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_closed INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-100',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_reopened INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-101',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_bugnote INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-102',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_status INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-103',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD redirect_delay INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-104',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD email_on_priority INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-105',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD advanced_update INT (1) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-106',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD default_profile INT (7) UNSIGNED ZEROFILL DEFAULT '0' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-107',
			'Add new user prefs',
			"ALTER TABLE $t_user_pref_table ADD default_project INT (7) UNSIGNED ZEROFILL not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-108',
			'Add new user prefs',
			"ALTER TABLE $t_user_profile_table DROP default_profile"
		);


# Make new project level user access table


	$upgrades[] = new SQLUpgrade(
			'0.14-109',
			'Make new project level user access table',
			"CREATE TABLE $t_project_user_list_table (
			  project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
			  user_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
			  access_level int(2) DEFAULT '10' NOT NULL)"
		);

# Make new project file table

	$upgrades[] = new SQLUpgrade(
			'0.14-110',
			'Make new project file table',
			"CREATE TABLE IF NOT EXISTS $t_project_file_table (
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
			  PRIMARY KEY (id))"
		);

# Make new bug file table

	$upgrades[] = new SQLUpgrade(
			'0.14-111',
			'Make new bug file table',
			"CREATE TABLE IF NOT EXISTS $t_bug_file_table (
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
			  PRIMARY KEY (id))"
		);

# more varchar to enum conversions

	$upgrades[] = new SQLUpgrade(
			'0.14-112',
			'more varchar to enum conversions',
			"ALTER TABLE $t_bug_table CHANGE severity severity INT (2) DEFAULT '50' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.14-113',
			'more varchar to enum conversions',
			"ALTER TABLE $t_bug_table CHANGE reproducibility reproducibility INT (2) DEFAULT '10' not null"
		);

# Need this entry for the project listing to work

	$upgrades[] = new SQLUpgrade(
			'0.14-114',
			'Need this entry for the project listing to work',
			"INSERT INTO $t_project_user_list_table (project_id, user_id, access_level) VALUES ('0000000','0000000','00')"
		);

# Add ordering field for versions

	$upgrades[] = new SQLUpgrade(
			'0.14-115',
			'Add ordering field for versions',
			"ALTER TABLE $t_project_version_table ADD ver_order INT (7) not null"
		);

# Make the cookie string unique

	$upgrades[] = new SQLUpgrade(
			'0.14-116',
			'Make the cookie string unique',
			"ALTER IGNORE TABLE $t_user_table ADD UNIQUE(cookie_string)"
		);


	return $upgrades;
?>
