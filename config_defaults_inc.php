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
 * Default Configuration Variables
 *
 * This file should not be changed. If you want to override any of the values
 * defined here, define them in a file called config/config_inc.php, which will
 * be loaded after this file.
 *
 * In general a value of OFF means the feature is disabled and ON means the
 * feature is enabled.  Any other cases will have an explanation.
 *
 * For more details see https://www.mantisbt.org/docs/master/
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

##############################
# MantisBT Database Settings #
##############################

/**
 * hostname should be either a hostname or connection string to supply to adodb.
 * For example, if you would like to connect to a database server on the local machine,
 * set hostname to 'localhost'
 * If you need to supply a port to connect to, set hostname as 'localhost:3306'.
 * @global string $g_hostname
 */
$g_hostname				= 'localhost';
/**
 * User name to use for connecting to the database. The user needs to have
 * read/write access to the MantisBT database. The default user name is "root".
 * @global string $g_db_username
 */
$g_db_username			= 'root';
/**
 * Password for the specified user name. The default password is empty.
 * @global string $g_db_password
 */
$g_db_password			= '';
/**
 * Name of database that contains MantisBT tables.
 * The default database name is "bugtracker".
 * @global string $g_database_name
 */
$g_database_name		= 'bugtracker';

/**
 * Defines the database type. Supported types are listed below;
 * the corresponding PHP extension must be enabled.
 *
 * RDBMS           db_type       PHP ext   Comments
 * -----           -------       -------   --------
 * MySQL           mysqli        mysqli    default
 * PostgreSQL      pgsql         pgsql
 * MS SQL Server   mssqlnative   sqlsrv    experimental
 * Oracle          oci8          oci8      experimental
 *
 * @global string $g_db_type
 */
$g_db_type				= 'mysqli';

/**
 * adodb Data Source Name
 * This is an EXPERIMENTAL field.
 * If the above database settings, do not provide enough flexibility, it is
 * possible to specify a dsn for the database connection. For further details,
 * currently, you need to see the adodb manual at
 * http://phplens.com/adodb/code.initialization.html#dsnsupport. For example,
 * if db_type is odbc_mssql. The following is an example dsn:
 * "Driver={SQL Server Native Client 10.0};SERVER=.\sqlexpress;DATABASE=bugtracker;UID=mantis;PWD=password;"
 * NOTE: the installer does not yet fully support the use of dsn's
 */
$g_dsn = '';

/**
 * Database Table prefix.
 * The given string is added with an underscore before the base table name,
 * e.g. 'bug' => 'mantis_bug'.
 * To avoid the 30-char limit on identifiers in Oracle (< 12cR2), the prefix
 * should be set to blank or kept as short as possible (e.g. 'm')
 * @global string $g_db_table_prefix
 */
$g_db_table_prefix = 'mantis';

/**
 * Database Table suffix.
 * The given string is added with an underscore after the base table name,
 * e.g. 'bug' => 'bug_table'.
 * @see $g_db_table_prefix for size limitation recommendation
 * @global string $g_db_table_suffix
 */
$g_db_table_suffix = '_table';

/**
 * Plugin Table prefix.
 * The given string is added with an underscore between the table prefix and
 * the base table name, and the plugin basename is added after that
 * e.g. 'Example' plugin's table 'foo' => 'mantis_plugin_Example_foo_table'.
 * To avoid the 30-char limit on identifiers in Oracle (< 12cR2), the prefix
 * should be kept as short as possible (e.g. 'plg'); it is however strongly
 * recommended not to use an empty string here.
 * @see $g_db_table_prefix
 * @global string $g_db_table_prefix
 */
$g_db_table_plugin_prefix	= 'plugin';

####################
# Folder Locations #
####################

/**
 * Path to root MantisBT folder.  Requires trailing / or \
 * @global string $g_absolute_path
 */
$g_absolute_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

/**
 * Path to core folder. The default is usually OK,
 * unless you moved the 'core' directory out of your webroot (recommended).
 * @global string $g_core_path
 */
$g_core_path = $g_absolute_path . 'core' . DIRECTORY_SEPARATOR;

/**
 * Path to classes folder.  Requires trailing / or \
 * @global string $g_class_path
 */
$g_class_path = $g_core_path . 'classes' . DIRECTORY_SEPARATOR;

/**
 * Path to library folder for 3rd party libraries. Requires trailing / or \
 * @global string $g_library_path
 */
$g_library_path = $g_absolute_path . 'library' . DIRECTORY_SEPARATOR;

/**
 * Path to vendor folder for 3rd party libraries. Requires trailing / or \
 * @global string $g_library_path
 */
$g_vendor_path = $g_absolute_path . 'vendor' . DIRECTORY_SEPARATOR;

/**
 * Path to lang folder for language files. Requires trailing / or \
 * @global string $g_language_path
 */
$g_language_path = $g_absolute_path . 'lang' . DIRECTORY_SEPARATOR;

/**
 * Path to custom configuration folder. Requires trailing / or \
 * If MANTIS_CONFIG_FOLDER environment variable is set, it will be used.
 * This allows Apache vhost to be used to setup multiple instances serviced by
 * same code by multiple configs.
 * @global string $g_config_path
 */
$t_local_config = getenv( 'MANTIS_CONFIG_FOLDER' );
if( $t_local_config && is_dir( $t_local_config ) ) {
	$g_config_path = $t_local_config;
} else {
	$g_config_path = $g_absolute_path . 'config' . DIRECTORY_SEPARATOR;
}

unset( $t_local_config );

##########################
# MantisBT Path Settings #
##########################

$t_protocol = 'http';
$t_host = 'localhost';
if( isset ( $_SERVER['SCRIPT_NAME'] ) ) {
	$t_protocol = http_is_protocol_https() ? 'https' : 'http';

	# $_SERVER['SERVER_PORT'] is not defined in case of php-cgi.exe
	if( isset( $_SERVER['SERVER_PORT'] ) ) {
		$t_port = ':' . $_SERVER['SERVER_PORT'];
		if( ( ':80' == $t_port && 'http' == $t_protocol )
		  || ( ':443' == $t_port && 'https' == $t_protocol )) {
			$t_port = '';
		}
	} else {
		$t_port = '';
	}

	if( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) { # Support ProxyPass
		$t_hosts = explode( ',', $_SERVER['HTTP_X_FORWARDED_HOST'] );
		$t_host = $t_hosts[0];
	} else if( isset( $_SERVER['HTTP_HOST'] ) ) {
		$t_host = $_SERVER['HTTP_HOST'];
	} else if( isset( $_SERVER['SERVER_NAME'] ) ) {
		$t_host = $_SERVER['SERVER_NAME'] . $t_port;
	} else if( isset( $_SERVER['SERVER_ADDR'] ) ) {
		$t_host = $_SERVER['SERVER_ADDR'] . $t_port;
	}

	if( !isset( $_SERVER['SCRIPT_NAME'] )) {
		echo 'Invalid server configuration detected. Please set $g_path manually in ' . $g_config_path . 'config_inc.php.';
		if( isset( $_SERVER['SERVER_SOFTWARE'] ) && ( stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false ) )
			echo ' Please try to add "fastcgi_param SCRIPT_NAME $fastcgi_script_name;" to the nginx server configuration.';
		die;
	}
	$t_self = filter_var( $_SERVER['SCRIPT_NAME'], FILTER_SANITIZE_STRING );
	$t_path = str_replace( basename( $t_self ), '', $t_self );
	switch( basename( $t_path ) ) {
		case 'admin':
			$t_path = rtrim( dirname( $t_path ), '/\\' ) . '/';
			break;
		case 'check':		# admin checks dir
		case 'soap':
		case 'rest':
			$t_path = rtrim( dirname( dirname( $t_path ) ), '/\\' ) . '/';
			break;
		case 'swagger':
			$t_path = rtrim( dirname( dirname( dirname( $t_path ) ) ), '/\\' ) . '/';
			break;
		case '':
			$t_path = '/';
			break;
	}
	if( strpos( $t_path, '&#' ) ) {
		echo 'Can not safely determine $g_path. Please set $g_path manually in ' . $g_config_path . 'config_inc.php';
		die;
	}
} else {
	$t_path = 'mantisbt/';
}

/**
 * path to your installation as seen from the web browser
 * requires trailing /
 * @global string $g_path
 */
$g_path	= $t_protocol . '://' . $t_host . $t_path;

/**
 * Short web path without the domain name
 * requires trailing /
 * @global string $g_short_path
 */
$g_short_path = $t_path;

/**
 * Used to link to manual for User Documentation.
 * This can be either a full URL or a relative path to the MantisBT root.
 * If a relative path does not exist, the link will fall back to the online
 * documentation at http://www.mantisbt.org. No check is performed on URLs.
 * @global string $g_manual_url
 */
$g_manual_url = 'doc/en-US/Admin_Guide/html-desktop/';

##############
# Web Server #
##############

/**
 * Session save path.  If false, uses default value as set by session handler.
 * @global bool $g_session_save_path
 */
$g_session_save_path = false;

/**
 * Session validation
 * WARNING: Disabling this could be a potential security risk!!
 * @global integer $g_session_validation
 */
$g_session_validation = ON;

/**
 * Form security validation.
 * This protects against Cross-Site Request Forgery, but some proxy servers may
 * not correctly work with this option enabled because they cache pages
 * incorrectly.
 * WARNING: Disabling this is a security risk!!
 *
 * @global integer $g_form_security_validation
 */
$g_form_security_validation = ON;

#############################
# Security and Cryptography #
#############################

/**
 * Master salt value used for cryptographic hashing throughout MantisBT. This
 * value must be kept secret at all costs. You must generate a unique and
 * random salt value for each installation of MantisBT you control. The
 * minimum length of this string must be at least 16 characters.
 *
 * The value you select for this salt should be a long string generated using
 * a secure random number generator. An example for Linux systems is:
 *    cat /dev/urandom | head -c 64 | base64
 * Note that the number of bits of entropy per byte of output from /dev/urandom
 * is not 8. If you're particularly paranoid and don't mind waiting a long
 * time, you could use /dev/random to get much closer to 8 bits of entropy per
 * byte. Moving the mouse (if possible) while generating entropy via
 * /dev/random will greatly improve the speed at which /dev/random produces
 * entropy.
 *
 * WARNING: This configuration option has a profound impact on the security of
 * your MantisBT installation. Failure to set this configuration option
 * correctly could lead to your MantisBT installation being compromised. Ensure
 * that this value remains secret. Treat it with the same security that you'd
 * treat the password to your MantisDB database.
 *
 * This setting is blank by default. MantisBT will not operate in this state.
 * Hence you are forced to change the value of this configuration option.
 *
 * @global string $g_crypto_master_salt
 */
$g_crypto_master_salt = '';

############################
# Signup and Lost Password #
############################

/**
 * Allow users to signup for their own accounts.
 * If ON, then $g_send_reset_password must be ON as well, and mail settings
 * must be correctly configured
 * @see $g_send_reset_password
 * @global integer $g_allow_signup
 */
$g_allow_signup			= ON;

/**
 * Max. attempts to login using a wrong password before lock the account.
 * When locked, it's required to reset the password (lost password)
 * Value resets to zero at each successfully login
 * Set to OFF to disable this control
 * @global integer $g_max_failed_login_count
 */
$g_max_failed_login_count = OFF;

/**
 * access level required to be notified when a new user has been created using
 * the "signup form"
 * @global integer $g_notify_new_user_created_threshold_min
 */
$g_notify_new_user_created_threshold_min = ADMINISTRATOR;

/**
 * If ON, users will be sent their password when their account is created
 * or password reset (this requires mail settings to be correctly configured).
 * If OFF, then the Administrator will have to provide a password when
 * creating new accounts, and the password will be set to blank when reset.
 * @global integer $g_send_reset_password
 */
$g_send_reset_password	= ON;

/**
 * use captcha image to validate subscription it requires GD library installed
 * @global integer $g_signup_use_captcha
 */
$g_signup_use_captcha	= ON;

/**
 * absolute path (with trailing slash!) to folder which contains your
 * TrueType-Font files used for the Relationship Graphs,
 * and the Workflow Graphs
 * @global string $g_system_font_folder
 */
$g_system_font_folder	= '';

/**
 * Setting to disable the 'lost your password' feature.
 * @global integer $g_lost_password_feature
 */
$g_lost_password_feature = ON;

/**
 * Max. simultaneous requests of 'lost password'
 * When this value is reached, it's no longer possible to request new password
 * reset. Value resets to zero at each successfully login
 * @global integer $g_max_lost_password_in_progress_count
 */
$g_max_lost_password_in_progress_count = 3;

#############
# Anti-Spam #
#############

/**
 * Max number of events to allow for users with default access level when signup is enabled.
 * Use 0 for no limit.
 * @var integer
 * @see $g_default_new_account_access_level
 */
$g_antispam_max_event_count = 10;

/**
 * Time window to enforce max events within.  Default is 3600 seconds (1 hour).
 * @var integer
 */
$g_antispam_time_window_in_seconds = 3600;

###########################
# MantisBT Email Settings #
###########################

/**
 * Webmaster email address. This is shown publicly at the bottom of each page
 * and thus may be susceptible to being detected by spam email harvesters.
 * @global string $g_webmaster_email
 */
$g_webmaster_email		= 'webmaster@example.com';

/**
 * the sender email, part of 'From: ' header in emails
 * @global string $g_from_email
 */
$g_from_email			= 'noreply@example.com';

/**
 * the sender name, part of 'From: ' header in emails
 * @global string $g_from_name
 */
$g_from_name			= 'Mantis Bug Tracker';

/**
 * the return address for bounced mail
 * @global string $g_return_path_email
 */
$g_return_path_email	= 'admin@example.com';

/**
 * Allow email notification.
 * Set to ON to enable email notifications, OFF to disable them. Note that
 * disabling email notifications has no effect on emails generated as part
 * of the user signup process. When set to OFF, the password reset feature
 * is disabled. Additionally, notifications of administrators updating
 * accounts are not sent to users.
 * @global integer $g_enable_email_notification
 */
$g_enable_email_notification	= ON;

/**
 * When enabled, the email notifications will send the full issue with
 * a hint about the change type at the top, rather than using dedicated
 * notifications that are focused on what changed.  This change can be
 * overridden in the database per user.
 *
 * @global integer $g_email_notifications_verbose
 */
$g_email_notifications_verbose = OFF;

/**
 * The following two config options allow you to control who should get email
 * notifications on different actions/statuses.  The first option
 * (default_notify_flags) sets the default values for different user
 * categories.  The user categories are:
 *
 *      'reporter': the reporter of the bug
 *       'handler': the handler of the bug
 *       'monitor': users who are monitoring a bug
 *      'bugnotes': users who have added a bugnote to the bug
 *      'category': category owners
 *      'explicit': users who are explicitly specified by the code based on the
 *                  action (e.g. user added to monitor list).
 * 'threshold_max': all users with access <= max
 * 'threshold_min': ..and with access >= min
 *
 * The second config option (notify_flags) sets overrides for specific
 * actions/statuses. If a user category is not listed for an action, the
 * default from the config option above is used.  The possible actions are:
 *
 *             'new': a new bug has been added
 *           'owner': a bug has been assigned to a new owner
 *        'reopened': a bug has been reopened
 *         'deleted': a bug has been deleted
 *         'updated': a bug has been updated
 *         'bugnote': a bugnote has been added to a bug
 *         'sponsor': sponsorship has changed on this bug
 *        'relation': a relationship has changed on this bug
 *         'monitor': an issue is monitored.
 *        '<status>': eg: 'resolved', 'closed', 'feedback', 'acknowledged', etc.
 *                     this list corresponds to $g_status_enum_string
 *
 * If you wanted to have all developers get notified of new bugs you might add
 * the following lines to your config file:
 *
 * $g_notify_flags['new']['threshold_min'] = DEVELOPER;
 * $g_notify_flags['new']['threshold_max'] = DEVELOPER;
 *
 * You might want to do something similar so all managers are notified when a
 * bug is closed.  If you did not want reporters to be notified when a bug is
 * closed (only when it is resolved) you would use:
 *
 * $g_notify_flags['closed']['reporter'] = OFF;
 *
 * @global array $g_default_notify_flags
 */

$g_default_notify_flags = array(
	'reporter'      => ON,
	'handler'       => ON,
	'monitor'       => ON,
	'bugnotes'      => ON,
	'category'      => ON,
	'explicit'      => ON,
	'threshold_min' => NOBODY,
	'threshold_max' => NOBODY
);

/**
 * We don't need to send these notifications on new bugs
 * (see above for info on this config option)
 * @todo (though I'm not sure they need to be turned off anymore
 *      - there just won't be anyone in those categories)
 *      I guess it serves as an example and a placeholder for this
 *      config option
 * @see $g_default_notify_flags
 * @global array $g_notify_flags
 */
$g_notify_flags['new'] = array(
	'bugnotes' => OFF,
	'monitor'  => OFF
);

$g_notify_flags['monitor'] = array(
	'reporter'      => OFF,
	'handler'       => OFF,
	'monitor'       => OFF,
	'bugnotes'      => OFF,
	'explicit'      => ON,
	'threshold_min' => NOBODY,
	'threshold_max' => NOBODY
);

/**
 * Whether user's should receive emails for their own actions
 * @global integer $g_email_receive_own
 */
$g_email_receive_own = OFF;

/**
 * Email addresses validation
 *
 * Determines whether email addresses are validated.
 * - When ON (default), validation is performed using the pattern given by the
 *   HTML5 specification for 'email' type form input elements
 *   {@link http://www.w3.org/TR/html5/forms.html#valid-e-mail-address}
 * - When OFF, validation is disabled.
 *
 * NOTE: Regardless of how this option is set, validation is never performed
 * when using LDAP email (i.e. when $g_use_ldap_email = ON), as we assume that
 * it is handled by the directory.
 * @see $g_use_ldap_email
 *
 * @global integer $g_validate_email
 */
$g_validate_email = ON;

/**
 * Enable support for logging in by email and password, in addition to
 * username and password.  This will only work as long as there is a single
 * user with the specified email address and the email address is not blank.
 *
 * @global integer $g_email_login_enabled
 */
$g_email_login_enabled = OFF;

/**
 * Ensure that email addresses are unique.
 *
 * @global integer $g_email_ensure_unique
 */
$g_email_ensure_unique = ON;

/**
 * set to OFF to disable email check
 * @global integer $g_check_mx_record
 */
$g_check_mx_record = OFF;

/**
 * if ON, allow the user to omit an email field
 * note if you allow users to create their own accounts, they
 * must specify an email at that point, no matter what the value
 * of this option is.  Otherwise they would not get their passwords.
 * @global integer $g_allow_blank_email
 */
$g_allow_blank_email = OFF;

/**
 * Only allow and send email to addresses in the given domain(s)
 * For example:
 * $g_limit_email_domains		= array( 'users.sourceforge.net', 'sourceforge.net' );
 * @global array $g_limit_email_domains
 */
$g_limit_email_domains = array();

/**
 * This specifies the access level that is needed to get the mailto: links.
 * @global integer $g_show_user_email_threshold
 */
$g_show_user_email_threshold = NOBODY;

/**
 * This specifies the access level that is needed to see realnames on user view
 * page
 * @see $g_show_realname
 * @global integer $g_show_user_realname_threshold
 */
$g_show_user_realname_threshold = NOBODY;

/**
 * select the method to mail by:
 * PHPMAILER_METHOD_MAIL - mail()
 * PHPMAILER_METHOD_SENDMAIL - sendmail
 * PHPMAILER_METHOD_SMTP - SMTP
 * @global integer $g_phpMailer_method
 */
$g_phpMailer_method = PHPMAILER_METHOD_MAIL;

/**
 * Remote SMTP Host(s)
 * Either a single hostname or multiple semicolon-delimited hostnames.
 * You can specify for each host a port other than the default, using format:
 * [hostname:port] (e.g. "smtp1.example.com:25;smtp2.example.com").
 * Hosts will be tried in the given order.
 * NOTE: This is only used with PHPMAILER_METHOD_SMTP.
 * @see $g_smtp_port
 * @global string $g_smtp_host
 */
$g_smtp_host = 'localhost';

/**
 * SMTP Server Authentication user
 * NOTE: must be set to '' if the SMTP host does not require authentication.
 * @see $g_smtp_password
 * @global string $g_smtp_username
 */
$g_smtp_username = '';

/**
 * SMTP Server Authentication password
 * Not used when $g_smtp_username = ''
 * @see $g_smtp_username
 * @global string $g_smtp_password
 */
$g_smtp_password = '';

/**
 * Allow secure connection to the SMTP server
 * Valid values are '' (no encryption), 'ssl' or 'tls'
 * @global string $g_smtp_connection_mode
 */
$g_smtp_connection_mode = '';

/**
 * Default SMTP port
 * Typical ports are 25 and 587.
 * This can be overridden individually for specific hosts.
 * @see $g_smtp_host
 * @global integer $g_smtp_port
 */
$g_smtp_port = 25;

/**
 * Enable DomainKeys Identified Mail (DKIM) Signatures (rfc6376)
 * To successfully sign mails you need to enable DKIM and provide at least:
 * - DKIM domain
 * - DKIM private key or key file path
 * - DKIM selector
 * - DKIM identity
 * @see $g_email_dkim_domain
 * @see $g_email_dkim_private_key_file_path
 * @see $g_email_dkim_private_key_string
 * @see $g_email_dkim_selector
 * @see $g_email_dkim_identity
 * @global integer $g_email_dkim_enable
 */
$g_email_dkim_enable = OFF;

/**
 * DomainKeys Identified Mail (DKIM) Signatures domain
 * This is usually the same as the domain of your from email
 * @see $g_from_email
 * @see $g_email_dkim_enable
 * @global string $g_email_dkim_domain
 */
$g_email_dkim_domain = 'example.com';

/**
 * DomainKeys Identified Mail (DKIM) Signatures private key path
 * Path to the private key. If $g_email_dkim_private_key_string is specified
 * this setting will not be used.
 * @see $g_email_dkim_private_key_string
 * @see $g_email_dkim_enable
 * @global string $g_email_dkim_private_key_file_path
 */
$g_email_dkim_private_key_file_path = '';


/**
 * DomainKeys Identified Mail (DKIM) Signatures private key value
 * This string should contain private key for signing. Leave empty
 * string if you wish to load the key from the file defined with
 * $g_email_dkim_private_key_file_path.
 * @see $g_email_dkim_enable
 * @see $g_email_dkim_private_key_file_path
 * @global string $g_email_dkim_private_key_string
 */
$g_email_dkim_private_key_string = '';

/**
 * DomainKeys Identified Mail (DKIM) Signatures selector
 * DNS selector for the signature (rfc6376)
 * DNS TXT field should have for instance:
 *   host mail.example._domainkey
 *   value v=DKIM1; t=s; n=core; k=rsa; p=[public key]
 * @see $g_email_dkim_enable
 * @global string $g_email_dkim_selector
 */
$g_email_dkim_selector = 'mail.example';

/**
 * DomainKeys Identified Mail (DKIM) Signatures private key password
 * Leave empty string if your private key does not have password
 * @see $g_email_dkim_enable
 * @global string $g_email_dkim_passphrase
 */
$g_email_dkim_passphrase = '';

/**
 * DomainKeys Identified Mail (DKIM) Signatures identity
 * Identity you are signing the mails with (rfc6376)
 * This is usually the same as your from email
 * @see $g_from_email
 * @see $g_email_dkim_enable
 * @global string $g_email_dkim_identity
 */
$g_email_dkim_identity = 'noreply@example.com';

/**
 * It is recommended to use a cronjob or a scheduler task to send emails. The
 * cronjob should typically run every 5 minutes.  If no cronjob is used,then
 * user will have to wait for emails to be sent after performing an action
 * which triggers notifications.  This slows user performance.
 * @global integer $g_email_send_using_cronjob
 */
$g_email_send_using_cronjob = OFF;

/**
 * email separator and padding
 * @global string $g_email_separator1
 */
$g_email_separator1 = str_pad('', 70, '=');
/**
 * email separator and padding
 * @global string $g_email_separator2
 */
$g_email_separator2 = str_pad('', 70, '-');
/**
 * email separator and padding
 * @global integer $g_email_padding_length
 */
$g_email_padding_length	= 28;

/**
 * Duration (in days) to retry failed emails before deleting them from queue.
 * @global integer $g_email_retry_in_days
 */
$g_email_retry_in_days = 7;

###########################
# MantisBT Version String #
###########################

/**
 * Set to off by default to not expose version to users
 * @global integer $g_show_version
 */
$g_show_version = OFF;

/**
 * String appended to the MantisBT version when displayed to the user
 * @global string $g_version_suffix
 */
$g_version_suffix = '';

/**
 * Custom copyright and licensing statement shown at the footer of each page.
 * Can contain HTML elements that are valid children of the <address> element.
 * This string is treated as raw HTML and thus you must use &amp; instead of &.
 * @global string $g_copyright_statement
 */
$g_copyright_statement = '';

##############################
# MantisBT Language Settings #
##############################

/**
 * If the language is set to 'auto', the actual language is determined by the
 * user agent (web browser) language preference.
 * @global string $g_default_language
 */
$g_default_language = 'auto';

/**
 * list the choices that the users are allowed to choose
 * @global array $g_language_choices_arr
 */
$g_language_choices_arr = array(
	'auto',
	'afrikaans',
	'amharic',
	'arabic',
	'arabicegyptianspoken',
	'asturian',
	'basque',
	'belarusian_tarask',
	'breton',
	'bulgarian',
	'catalan',
	'chinese_simplified',
	'chinese_traditional',
	'croatian',
	'czech',
	'danish',
	'dutch',
	'english',
	'estonian',
	'finnish',
	'french',
	'galician',
	'georgian',
	'german',
	'greek',
	'hebrew',
	'hungarian',
	'icelandic',
	'interlingua',
	'italian',
	'japanese',
	'korean',
	'latvian',
	'lithuanian',
	'luxembourgish',
	'macedonian',
	'norwegian_bokmal',
	'norwegian_nynorsk',
	'occitan',
	'persian',
	'polish',
	'portuguese_brazil',
	'portuguese_standard',
	'ripoarisch',
	'romanian',
	'russian',
	'serbian',
	'serbian_latin',
	'slovak',
	'slovene',
	'spanish',
	'swedish',
	'swissgerman',
	'tagalog',
	'turkish',
	'ukrainian',
	'urdu',
	'vietnamese',
	'volapuk',
	'zazaki',
);

/**
 * Browser language mapping for 'auto' language selection
 * @global array $g_language_auto_map
 */
$g_language_auto_map = array(
	'af' => 'afrikaans',
	'am' => 'amharic',
	'ar' => 'arabic',
	'arz' => 'arabicegyptianspoken',
	'ast' => 'asturian',
	'eu' => 'basque',
	'be-tarask' => 'belarusian_tarask',
	'bg' => 'bulgarian',
	'br' => 'breton',
	'ca' => 'catalan',
	'zh-cn, zh-sg, zh' => 'chinese_simplified',
	'zh-hk, zh-tw' => 'chinese_traditional',
	'hr' => 'croatian',
	'cs' => 'czech',
	'da' => 'danish',
	'nl-be, nl' => 'dutch',
	'en-us, en-gb, en-au, en' => 'english',
	'et' => 'estonian',
	'fi' => 'finnish',
	'fr-ca, fr-be, fr-ch, fr' => 'french',
	'gl' => 'galician',
	'de-de, de-at, de-ch, de' => 'german',
	'he' => 'hebrew',
	'hu' => 'hungarian',
	'is' => 'icelandic',
	'ia' => 'interlingua',
	'it-ch, it' => 'italian',
	'ja' => 'japanese',
	'ka' => 'georgian',
	'ko' => 'korean',
	'lv' => 'latvian',
	'lt' => 'lithuanian',
	'lb' => 'luxembourgish',
	'mk' => 'macedonian',
	'no' => 'norwegian_bokmal',
	'nn' => 'norwegian_nynorsk',
	'oc' => 'occitan',
	'fa' => 'persian',
	'pl' => 'polish',
	'pt-br' => 'portuguese_brazil',
	'pt' => 'portuguese_standard',
	'ksh' => 'ripoarisch',
	'ro-mo, ro' => 'romanian',
	'ru-mo, ru-ru, ru-ua, ru' => 'russian',
	'sr' => 'serbian',
	'sr-latn' => 'serbian_latin',
	'sk' => 'slovak',
	'sl' => 'slovene',
	'es-mx, es-co, es-ar, es-cl, es-pr, es' => 'spanish',
	'sv-fi, sv' => 'swedish',
	'gsw' => 'swissgerman',
	'tl' => 'tagalog',
	'tr' => 'turkish',
	'uk' => 'ukrainian',
	'vi' => 'vietnamese',
	'vo' => 'volapuk',
	'diq' => 'zazaki',
);

/**
 * Fallback for automatic language selection
 * @global string $g_fallback_language
 */
$g_fallback_language = 'english';

##########################
# MantisBT Font Settings #
##########################

/**
 * Name of one of google fonts available at https://fonts.google.com/
 * Chosen family must be part of in $g_font_family_choices_local such that it works
 * even if CDN option is disabled
 * @see $g_font_family_choices_local
 * @see $g_cdn_enabled
 * @global string $g_font_family
 */
$g_font_family = 'Open Sans';

/**
 * List the google fonts that the users are allowed to choose from.
 * Google offers over 800 fonts. The list below is limited to the ones tested on MantisBT UI
 * @global array $g_font_family_choices
 */
$g_font_family_choices = array(
	'Amiko',
	'Architects Daughter',
	'Archivo Narrow',
	'Arvo',
	'Bitter',
	'Cabin',
	'Cinzel',
	'Comfortaa',
	'Courgette',
	'Droid Sans',
	'Gloria Hallelujah',
	'Inconsolata',
	'Josefin Sans',
	'Kadwa',
	'Karla',
	'Kaushan Script',
	'Lato',
	'Montserrat',
	'Open Sans',
	'Orbitron',
	'Oregano',
	'Palanquin',
	'Poppins',
	'Raleway',
	'Rhodium Libre',
	'Sarala',
	'Scope One',
	'Secular One',
	'Ubuntu',
	'Vollkorn'
);

/**
 * List of fonts that are installed as part of MantisBT
 * This list is used when using CDN option is disabled
 * @global array $g_font_family_choices_local
 */
$g_font_family_choices_local = array(
	'Montserrat',
	'Open Sans',
	'Poppins'
);

#############################
# MantisBT Display Settings #
#############################

/**
 * browser window title
 * @global string $g_window_title
 */
$g_window_title = 'MantisBT';

/**
 * OpenSearch engine title prefix.
 * This is used to describe Browser Search entries, and must be short enough
 * so that when inserted into the 'opensearch_XXX_short' language string, the
 * resulting text is 16 characters or less, to be compliant with the limit for
 * the ShortName element as defined in the OpenSearch specification.
 * @link http://www.opensearch.org/Specifications/OpenSearch/1.1
 * @see $g_window_title
 * @global string $g_search_title
 */
$g_search_title = '%window_title%';

/**
 * Check for admin directory, database upgrades, etc.
 * @global integer $g_admin_checks
 */
$g_admin_checks = ON;

/**
 * Favicon image.
 * This icon should be of 'image/x-icon' MIME type, and its size 16x16 pixels.
 * It is also used to decorate OpenSearch Browser search entries.
 * @global string $g_favicon_image
 */
$g_favicon_image = 'images/favicon.ico';

/**
 * Logo
 * @global string $g_logo_image
 */
$g_logo_image = 'images/mantis_logo.png';

/**
 * Logo URL link
 * @global string $g_logo_url
 */
$g_logo_url = '%default_home_page%';

/**
 * Specifies whether to enable support for project documents or not.
 * This feature is deprecated and is expected to be moved to a plugin
 * in the future.
 * @see $g_view_proj_doc_threshold
 * @see $g_upload_project_file_threshold
 * @global integer $g_enable_project_documentation
 */
$g_enable_project_documentation = OFF;

/**
 * show extra menu bar with all available projects
 * @global integer $g_show_project_menu_bar
 */
$g_show_project_menu_bar = OFF;

/**
 * show assigned to names
 * This is in the view all pages
 * @global integer $g_show_assigned_names
 */
$g_show_assigned_names = ON;

/**
 * show priority as icon
 * OFF: Shows priority as icon in view all bugs page
 * ON:  Shows priority as text in view all bugs page
 * @global integer $g_show_priority_text
 */
$g_show_priority_text = OFF;

/**
 * Define the priority level at which a bug becomes significant. Significant
 * bugs are displayed with emphasis. Set this value to -1 to disable the
 * feature.
 * @global integer $g_priority_significant_threshold
 */
$g_priority_significant_threshold = HIGH;

/**
 * Define the severity level at which a bug becomes significant.
 * Significant bugs are displayed with emphasis. Set this value to -1 to
 * disable the feature.
 * @global integer $g_severity_significant_threshold
 */
$g_severity_significant_threshold = MAJOR;

/**
 * The default columns to be included in the View Issues Page.
 * This can be overridden using Manage -> Manage Configuration -> Manage Columns
 * Also each user can configure their own columns using My Account -> Manage
 * Columns. Some of the columns specified here can be removed automatically if
 * they conflict with other configuration. Or if the current user doesn't have
 * the necessary access level to view them. For example, sponsorship_total will
 * be removed if sponsorships are disabled. To include custom field 'xyz',
 * include the column name as 'custom_xyz'.
 *
 * Standard Column Names (i.e. names to choose from):
 * id, project_id, reporter_id, handler_id, duplicate_id, priority, severity,
 * reproducibility, status, resolution, category_id, date_submitted, last_updated,
 * os, os_build, platform, version, fixed_in_version, target_version, view_state,
 * summary, sponsorship_total, due_date, description, steps_to_reproduce,
 * additional_info, attachment_count, bugnotes_count, selection, edit,
 * overdue
 *
 * @global array $g_view_issues_page_columns
 */
$g_view_issues_page_columns = array(
	'selection', 'edit', 'priority', 'id', 'sponsorship_total',
	'bugnotes_count', 'attachment_count', 'category_id', 'severity', 'status',
	'last_updated', 'summary'
);

/**
 * The default columns to be included in the Print Issues Page. This can be
 * overridden using Manage -> Manage Configuration -> Manage Columns. Also each
 * user can configure their own columns using My Account -> Manage Columns.
 * @global array $g_print_issues_page_columns
 */
$g_print_issues_page_columns = array(
	'selection', 'priority', 'id', 'sponsorship_total', 'bugnotes_count',
	'attachment_count', 'category_id', 'severity', 'status', 'last_updated',
	'summary'
);

/**
 * The default columns to be included in the CSV export. This can be overridden
 * using Manage -> Manage Configuration -> Manage Columns. Also each user can
 * configure their own columns using My Account -> Manage Columns.
 * @global array $g_csv_columns
 */
$g_csv_columns = array(
	'id', 'project_id', 'reporter_id', 'handler_id', 'priority',
	'severity', 'reproducibility', 'version', 'projection', 'category_id',
	'date_submitted', 'eta', 'os', 'os_build', 'platform', 'view_state',
	'last_updated', 'summary', 'status', 'resolution', 'fixed_in_version'
);

/**
 * The default columns to be included in the Excel export. This can be
 * overridden using Manage -> Manage Configuration -> Manage Columns. Also each
 * user can configure their own columns using My Account -> Manage Columns
 * @global array $g_excel_columns
 */
$g_excel_columns = array(
	'id', 'project_id', 'reporter_id', 'handler_id', 'priority', 'severity',
	'reproducibility', 'version', 'projection', 'category_id',
	'date_submitted', 'eta', 'os', 'os_build', 'platform', 'view_state',
	'last_updated', 'summary', 'status', 'resolution', 'fixed_in_version'
);

/**
 * show projects when in All Projects mode
 * @global integer $g_show_bug_project_links
 */
$g_show_bug_project_links = ON;

/**
 * Position of the filter box, can be: POSITION_*
 * POSITION_TOP, POSITION_BOTTOM, or POSITION_NONE for none.
 * @global integer $g_filter_position
 */
$g_filter_position = FILTER_POSITION_TOP;

/**
 * Position of action buttons when viewing issues.
 * Can be: POSITION_TOP, POSITION_BOTTOM, or POSITION_BOTH.
 * @global integer $g_action_button_position
 */
$g_action_button_position = POSITION_BOTTOM;

/**
 * show product versions in create, view and update screens
 * ON forces display even if none are defined
 * OFF suppresses display
 * AUTO suppresses the display if there are no versions defined for the project
 * @global integer $g_show_product_version
 */
$g_show_product_version = AUTO;

/**
 * The access level threshold at which users will see the date of release
 * for product versions. Dates will be shown next to the product version,
 * target version and fixed in version fields. Set this threshold to NOBODY
 * to disable the feature.
 * @global integer $g_show_version_dates_threshold
 */
$g_show_version_dates_threshold = NOBODY;

/**
 * show users with their real name or not
 * @see $g_sort_by_last_name
 * @see $g_show_user_realname_threshold
 * @global integer $g_show_realname
 */
$g_show_realname = OFF;

/**
 * sorting for names in dropdown lists. If turned on, "Jane Doe" will be sorted
 * with the "D"s
 * @see $g_show_realname
 * @global integer $g_sort_by_last_name
 */
$g_sort_by_last_name = OFF;

/**
 * Show user avatars
 * @global integer $g_show_avatar
 * @see $g_show_avatar_threshold
 */
$g_show_avatar = OFF;

/**
 * Only users above this threshold will have their avatar shown
 * @global integer $g_show_avatar_threshold
 */
$g_show_avatar_threshold = DEVELOPER;

/**
 * Show release dates on changelog
 * @global integer $g_show_changelog_dates
 */
$g_show_changelog_dates = ON;

/**
 * Show release dates on roadmap
 * @global integer $g_show_roadmap_dates
 */
$g_show_roadmap_dates = ON;

##########################
# MantisBT Time Settings #
##########################

/**
 * Time for long lived cookie to live in seconds.  It is also used as the default for
 * permanent logins if $g_allow_permanent_cookie is enabled and selected.
 * @see $g_allow_permanent_cookie
 * @global integer $g_cookie_time_length
 */
$g_cookie_time_length = 60 * 60 * 24 * 365;

/**
 * Allow users to opt for a 'permanent' cookie when logging in
 * Controls the display of the 'Remember my login in this browser' checkbox
 * on the login page
 * @see $g_cookie_time_length
 * @global integer $g_allow_permanent_cookie
 */
$g_allow_permanent_cookie = ON;

/**
 * The time (in seconds) to allow for page execution during long processes
 *  such as upgrading your database.
 * The default value of 0 indicates that the page should be allowed to
 *  execute until it is finished.
 * @global integer $g_long_process_timeout
 */
$g_long_process_timeout = 0;

##########################
# MantisBT Date Settings #
##########################

/**
 * Date format strings defaults to ISO 8601 formatting.
 * For detailed instructions on date formatting
 * @see http://www.php.net/manual/en/function.date.php
 * @global string $g_short_date_format
 */
$g_short_date_format = 'Y-m-d';

/**
 * Date format strings defaults to ISO 8601 formatting.
 * For detailed instructions on date formatting
 * @see http://www.php.net/manual/en/function.date.php
 * @global string $g_normal_date_format
 */
$g_normal_date_format = 'Y-m-d H:i';

/**
 * Date format strings defaults to ISO 8601 formatting.
 * For detailed instructions on date formatting
 * @see http://www.php.net/manual/en/function.date.php
 * @global string $g_complete_date_format
 */
$g_complete_date_format = 'Y-m-d H:i T';

/**
 * Datetime picker widget format string.
 * This format needs needs to match the one defined in {@see $g_normal_date_format}
 * For detailed instructions on date formatting
 * @see http://momentjs.com/docs/#/displaying/format/
 * @global string $g_datetime_picker_format
 */
$g_datetime_picker_format = 'Y-MM-DD HH:mm';


##############################
# MantisBT TimeZone Settings #
##############################

/**
 * Default timezone to use in MantisBT
 *
 * This configuration is normally initialized when installing Mantis.
 * It should be set to one of the values specified in the
 * {@link http://php.net/timezones List of Supported Timezones}.
 * If this config is left blank, the timezone will be initialized by calling
 * {@link http://php.net/date-default-timezone-get date_default_timezone_get()}
 * (note that this function's behavior was modified in PHP 5.4.0), which will
 * fall back to 'UTC' if unable to determine the timezone.
 * Correct configuration of this variable can be confirmed by running the
 * administration checks.
 * Users can override the default timezone under their preferences.
 *
 * @global string $g_default_timezone
 */
$g_default_timezone = '';

##########################
# MantisBT News Settings #
##########################

/**
 * Indicates whether the news feature should be enabled or disabled.
 * This feature is deprecated and is expected to be moved to a plugin
 * in the future.
 *
 * @global integer $g_news_enabled
 */
$g_news_enabled = OFF;

/**
 * Limit News Items
 * limit by entry count or date
 * BY_LIMIT - entry limit
 * BY_DATE - by date
 * @global integer $g_news_limit_method
 */
$g_news_limit_method = BY_LIMIT;

/**
 * limit by last X entries
 * @global integer $g_news_view_limit
 */
$g_news_view_limit = 7;

/**
 * limit by days
 * @global integer $g_news_view_limit_days
 */
$g_news_view_limit_days = 30;

/**
 * threshold for viewing private news
 * @global integer $g_private_news_threshold
 */
$g_private_news_threshold = DEVELOPER;

################################
# MantisBT Default Preferences #
################################

/**
 * signup default
 * look in constant_inc.php for values
 * @global integer $g_default_new_account_access_level
 */
$g_default_new_account_access_level = REPORTER;

/**
 * Default Project View Status (VS_PUBLIC or VS_PRIVATE)
 * @global integer $g_default_project_view_status
 */
$g_default_project_view_status = VS_PUBLIC;

/**
 * Default Bug View Status (VS_PUBLIC or VS_PRIVATE)
 * @global integer $g_default_bug_view_status
 */
$g_default_bug_view_status = VS_PUBLIC;

/**
 * Default value for bug description field used on bug report page.
 *
 * @global string $g_default_bug_description
 */
$g_default_bug_description = '';

/**
 * Default value for steps to reproduce field.
 * @global string $g_default_bug_steps_to_reproduce
 */
$g_default_bug_steps_to_reproduce = '';

/**
 * Default value for addition information field.
 * @global string $g_default_bug_additional_info
 */
$g_default_bug_additional_info = '';

/**
 * Default Bugnote View Status (VS_PUBLIC or VS_PRIVATE)
 * @global integer $g_default_bugnote_view_status
 */
$g_default_bugnote_view_status = VS_PUBLIC;

/**
 * Default bug resolution when reporting a new bug
 * @global integer $g_default_bug_resolution
 */
$g_default_bug_resolution = OPEN;

/**
 * Default bug severity when reporting a new bug
 * @global integer $g_default_bug_severity
 */
$g_default_bug_severity = MINOR;

/**
 * Default bug priority when reporting a new bug
 * @global integer $g_default_bug_priority
 */
$g_default_bug_priority = NORMAL;

/**
 * Default bug reproducibility when reporting a new bug
 * @global integer $g_default_bug_reproducibility
 */
$g_default_bug_reproducibility = REPRODUCIBILITY_HAVENOTTRIED;

/**
 * Default bug projection when reporting a new bug
 * @global integer $g_default_bug_projection
 */
$g_default_bug_projection = PROJECTION_NONE;

/**
 * Default bug ETA when reporting a new bug
 * @global integer $g_default_bug_eta
 */
$g_default_bug_eta = ETA_NONE;

/**
 * Default relationship between a new bug and its parent when cloning it
 * @global integer $g_default_bug_relationship_clone
 */
$g_default_bug_relationship_clone = BUG_REL_NONE;

/**
 * Allow parent bug to close regardless of child status.
 * @global integer $g_allow_parent_of_unresolved_to_close
 */
$g_allow_parent_of_unresolved_to_close = OFF;

/**
 * Default for new bug relationships
 * @global integer $g_default_bug_relationship
 */
$g_default_bug_relationship = BUG_RELATED;

/**
 * Default global category to be used when an issue is moved from a project to another
 * that does not have a category with a matching name.  The default is 1 which is the "General"
 * category that is created in the default database.
 */
$g_default_category_for_moves = 1;

/**
 *
 * @global integer $g_default_limit_view
 */
$g_default_limit_view = 50;

/**
 *
 * @global integer $g_default_show_changed
 */
$g_default_show_changed = 6;

/**
 *
 * @global integer $g_hide_status_default
 */
$g_hide_status_default = CLOSED;

/**
 *
 * @global integer $g_show_sticky_issues
 */
$g_show_sticky_issues = ON;

/**
 * make sure people are not refreshing too often
 * in minutes
 * @global integer $g_min_refresh_delay
 */
$g_min_refresh_delay = 10;

/**
 * in minutes
 * @global integer $g_default_refresh_delay
 */
$g_default_refresh_delay = 30;

/**
 * in seconds
 * @global integer $g_default_redirect_delay
 */
$g_default_redirect_delay = 2;

/**
 *
 * @global string $g_default_bugnote_order
 */
$g_default_bugnote_order = 'ASC';

/**
 *
 * @global integer $g_default_email_on_new
 */
$g_default_email_on_new = ON;

/**
 *
 * @global integer $g_default_email_on_assigned
 */
$g_default_email_on_assigned = ON;

/**
 *
 * @global integer $g_default_email_on_feedback
 */
$g_default_email_on_feedback = ON;

/**
 *
 * @global integer $g_default_email_on_resolved
 */
$g_default_email_on_resolved = ON;

/**
 *
 * @global integer $g_default_email_on_closed
 */
$g_default_email_on_closed = ON;

/**
 *
 * @global integer $g_default_email_on_reopened
 */
$g_default_email_on_reopened = ON;

/**
 *
 * @global integer $g_default_email_on_bugnote
 */
$g_default_email_on_bugnote = ON;

/**
 * @global integer $g_default_email_on_status
 */
$g_default_email_on_status = OFF;

/**
 * @global integer $g_default_email_on_priority
 */
$g_default_email_on_priority = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_new_minimum_severity
 */
$g_default_email_on_new_minimum_severity = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_assigned_minimum_severity
 */
$g_default_email_on_assigned_minimum_severity = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_feedback_minimum_severity
 */
$g_default_email_on_feedback_minimum_severity = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_resolved_minimum_severity
 */
$g_default_email_on_resolved_minimum_severity = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_closed_minimum_severity
 */
$g_default_email_on_closed_minimum_severity = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_reopened_minimum_severity
 */
$g_default_email_on_reopened_minimum_severity = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_bugnote_minimum_severity
 */
$g_default_email_on_bugnote_minimum_severity = OFF;

/**
 * 'any'
 * @global integer $g_default_email_on_status_minimum_severity
 */
$g_default_email_on_status_minimum_severity = OFF;

/**
 * @todo Unused
 * @global integer $g_default_email_on_priority_minimum_severity
 */
$g_default_email_on_priority_minimum_severity = OFF;

/**
 *
 * @global integer $g_default_email_bugnote_limit
 */
$g_default_email_bugnote_limit = 0;

#############################
# MantisBT Summary Settings #
#############################

/**
 * how many reporters to show
 * this is useful when there are hundreds of reporters
 * @global integer $g_reporter_summary_limit
 */
$g_reporter_summary_limit = 10;

/**
 * summary date displays
 * date lengths to count bugs by (in days)
 * @global array $g_date_partitions
 */
$g_date_partitions = array( 1, 2, 3, 7, 30, 60, 90, 180, 365);

/**
 * shows project '[project] category' when 'All Projects' is selected
 * otherwise only 'category name'
 * @global integer $g_summary_category_include_project
 */
$g_summary_category_include_project = OFF;

/**
 * threshold for viewing summary
 * @global integer $g_view_summary_threshold
 */
$g_view_summary_threshold = MANAGER;

/**
 * Define the multipliers which are used to determine the effectiveness
 * of reporters based on the severity of bugs. Higher multipliers will
 * result in an increase in reporter effectiveness.
 * @global array $g_severity_multipliers
 */
$g_severity_multipliers = array(
	FEATURE => 1,
	TRIVIAL => 2,
	TEXT    => 3,
	TWEAK   => 2,
	MINOR   => 5,
	MAJOR   => 8,
	CRASH   => 8,
	BLOCK   => 10
);

/**
 * Define the resolutions which are used to determine the effectiveness
 * of reporters based on the resolution of bugs. Higher multipliers will
 * result in a decrease in reporter effectiveness. The only resolutions
 * that need to be defined here are those which match or exceed
 * $g_bug_resolution_not_fixed_threshold.
 * @global array $g_resolution_multipliers
 */
$g_resolution_multipliers = array(
	UNABLE_TO_REPRODUCE => 2,
	NOT_FIXABLE         => 1,
	DUPLICATE           => 3,
	NOT_A_BUG           => 5,
	SUSPENDED           => 1,
	WONT_FIX            => 1
);

#############################
# MantisBT Bugnote Settings #
#############################

/**
 * bugnote ordering
 * change to ASC or DESC
 * @global string $g_bugnote_order
 */
$g_bugnote_order = 'DESC';

#################################
# MantisBT Bug History Settings #
#################################

/**
 * bug history visible by default when you view a bug
 * change to ON or OFF
 * @global integer $g_history_default_visible
 */
$g_history_default_visible = ON;

/**
 * bug history ordering
 * change to ASC or DESC
 * @global string $g_history_order
 */
$g_history_order = 'ASC';

##########################################
# MantisBT Reminder and Mention Settings #
##########################################

/**
 * are reminders stored as bugnotes
 * @global integer $g_store_reminders
 */
$g_store_reminders = ON;

/**
 * Automatically add recipients of reminders to monitor list, if they are not
 * the handler or the reporter (since they automatically get notified, if required)
 * If recipients of the reminders are below the monitor threshold, they will not be added.
 * @global integer $g_reminder_recipients_monitor_bug
 */
$g_reminder_recipients_monitor_bug = ON;

/**
 * Default Reminder View Status (VS_PUBLIC or VS_PRIVATE)
 * @global integer $g_default_reminder_view_status
 */
$g_default_reminder_view_status = VS_PUBLIC;

/**
 * The minimum access level required to show up in the list of users who can receive a reminder.
 * The access level is that of the project to which the issue belongs.
 * @global integer $g_reminder_receive_threshold
 */
$g_reminder_receive_threshold = DEVELOPER;

/**
 * Enables or disables @ mentions feature.
 *
 * @global integer $g_mentions_enabled
 */
$g_mentions_enabled = ON;

/**
 * The tag to use for mentions.
 * @var string $g_mentions_tag
 */
$g_mentions_tag = '@';

#################################
# MantisBT Sponsorship Settings #
#################################

/**
 * Whether to enable/disable the whole issue sponsorship feature
 * @global integer $g_enable_sponsorship
 */
$g_enable_sponsorship = OFF;

/**
 * Currency used for all sponsorships.
 * @global string $g_sponsorship_currency
 */
$g_sponsorship_currency = 'US$';

/**
 * Access level threshold needed to view the total sponsorship for an issue by
 * all users.
 * @global integer $g_view_sponsorship_total_threshold
 */
$g_view_sponsorship_total_threshold = VIEWER;

/**
 * Access level threshold needed to view the users sponsoring an issue and the
 * sponsorship amount for each.
 * @global integer $g_view_sponsorship_details_threshold
 */
$g_view_sponsorship_details_threshold = VIEWER;

/**
 * Access level threshold needed to allow user to sponsor issues.
 * @global integer $g_sponsor_threshold
 */
$g_sponsor_threshold = REPORTER;

/**
 * Access level required to be able to handle sponsored issues.
 * @global integer $g_handle_sponsored_bugs_threshold
 */
$g_handle_sponsored_bugs_threshold = DEVELOPER;

/**
 * Access level required to be able to assign a sponsored issue to a user with
 * access level greater or equal to 'handle_sponsored_bugs_threshold'.
 * @global integer $g_assign_sponsored_bugs_threshold
 */
$g_assign_sponsored_bugs_threshold = MANAGER;

/**
 * Minimum sponsorship amount. If the user enters a value less than this, an
 * error will be prompted.
 * @global integer $g_minimum_sponsorship_amount
 */
$g_minimum_sponsorship_amount = 5;

#################################
# MantisBT File Upload Settings #
#################################

/**
 * --- file upload settings --------
 * This is the master setting to disable *all* file uploading functionality
 *
 * If you want to allow file uploads, you must also make sure that they are
 *  enabled in php.  You may need to add 'file_uploads = TRUE' to your php.ini
 *
 * See also: $g_upload_project_file_threshold, $g_upload_bug_file_threshold,
 *   $g_allow_reporter_upload
 * @global integer $g_allow_file_upload
 */
$g_allow_file_upload = ON;

/**
 * Upload destination: specify actual location in project settings
 * DISK or DATABASE. FTP is now deprecated and will map to DISK.
 * @global integer $g_file_upload_method
 */
$g_file_upload_method = DATABASE;

/**
 * Use File dropzone: enable drag and drop into a drop zone functionality for
 * file upload fields
 * @global integer $g_dropzone_enabled
 */
$g_dropzone_enabled = ON;

/**
 * When using DISK for storing uploaded files, this setting control
 * the access permissions they will have on the web server: with the default
 * value (0400) files will be read-only, and accessible only by the user
 * running the apache process (probably "apache" in Linux and "Administrator"
 * in Windows).
 * For more details on unix style permissions:
 * http://www.perlfect.com/articles/chmod.shtml
 * @global integer $g_attachments_file_permissions
 */
$g_attachments_file_permissions = 0400;

/**
 * Maximum file size (bytes) that can be uploaded.
 * Also check your PHP settings (default is usually 2MBs)
 * @global integer $g_max_file_size
 */
$g_max_file_size = 5000000;

/**
 * Maximum number of files that can be uploaded simultaneously
 * @global integer $g_file_upload_max_num
 */
$g_file_upload_max_num = 10;

/**
 * Files that are allowed or not allowed.  Separate items by commas.
 * eg. 'php,html,java,exe,pl'
 * if $g_allowed_files is filled in NO other file types will be allowed.
 * $g_disallowed_files takes precedence over $g_allowed_files
 * @global string $g_allowed_files
 */
$g_allowed_files = '';

/**
 *
 * @global string $g_disallowed_files
 */
$g_disallowed_files = '';

/**
 * prefix to be used for the file system names of files uploaded to projects.
 * Eg: doc-001-myprojdoc.zip
 * @global string $g_document_files_prefix
 * @deprecated since 1.0, file names have been stored in a new format
 */
$g_document_files_prefix = 'doc';

/**
 * absolute path to the default upload folder.  Requires trailing / or \
 * @global string $g_absolute_path_default_upload_folder
 */
$g_absolute_path_default_upload_folder = '';

/**
 * Enable support for sending files to users via a more efficient X-Sendfile
 * method. HTTP server software supporting this technique includes Lighttpd,
 * Cherokee, Apache with mod_xsendfile and nginx. You may need to set the
 * proceeding file_download_xsendfile_header_name option to suit the server you
 * are using.
 * @global integer $g_file_download_method
 */
$g_file_download_xsendfile_enabled = OFF;

/**
 * The name of the X-Sendfile header to use. Each server tends to implement
 * this functionality in a slightly different way and thus the naming
 * conventions for the header differ between each server. Lighttpd from v1.5,
 * Apache with mod_xsendfile and Cherokee web servers use X-Sendfile. nginx
 * uses X-Accel-Redirect and Lighttpd v1.4 uses X-LIGHTTPD-send-file.
 * @global string $g_file_download_xsendfile_header_name
 */
$g_file_download_xsendfile_header_name = 'X-Sendfile';

##########################
# MantisBT HTML Settings #
##########################

/**
 * Convert URLs and e-mail addresses to html links.
 * This flag controls whether www URLs and email addresses are automatically
 * converted to clickable links as well as where the www links open when
 * clicked. Valid options are:
 * - OFF                Do not convert URLs or emails
 * - LINKS_SAME_WINDOW  Convert to links that open in the current window (DEFAULT)
 * - LINKS_NEW_WINDOW   Convert to links that open in a new window
 * @global integer $g_html_make_links
 */
$g_html_make_links = LINKS_SAME_WINDOW;

/**
 * These are the valid html tags for multi-line fields (e.g. description)
 * do NOT include a or img tags here
 * do NOT include tags that require attributes
 * @global string $g_html_valid_tags
 */
$g_html_valid_tags = 'p, li, ul, ol, br, pre, i, b, u, em, strong';

/**
 * These are the valid html tags for single line fields (e.g. issue summary).
 * do NOT include a or img tags here
 * do NOT include tags that require attributes
 * @global string $g_html_valid_tags_single_line
 */
$g_html_valid_tags_single_line = 'i, b, u, em, strong';

/**
 * maximum length of the description in a dropdown menu (for search)
 * set to 0 to disable truncations
 * @global integer $g_max_dropdown_length
 */
$g_max_dropdown_length = 40;

/**
 * This flag controls whether pre-formatted text (delimited by HTML pre tags
 * is wrapped to a maximum linelength (defaults to 100 chars in strings_api)
 * If turned off, the display may be wide when viewing the text
 * @global integer $g_wrap_in_preformatted_text
 */
$g_wrap_in_preformatted_text = ON;

#############################################
# MantisBT Authentication and LDAP Settings #
#############################################

/**
 * Login authentication method. Must be one of
 * MD5, LDAP, BASIC_AUTH or HTTP_AUTH.
 * Note: you may not be able to easily switch encryption methods, so this
 * should be carefully chosen at install time. However, MantisBT will attempt
 * to "fall back" to older methods if possible.
 * @global integer $g_login_method
 */
$g_login_method = MD5;

/**
 * Re-authentication required for admin areas
 * @global integer $g_reauthentication
 */
$g_reauthentication = ON;

/**
 * Duration of the reauthentication timeout, in seconds
 * @global integer $g_reauthentication_expiry
 */
$g_reauthentication_expiry = TOKEN_EXPIRY_AUTHENTICATED;


/**
 * Specifies the LDAP or Active Directory server to connect to.
 *
 * This must be a full LDAP URI (ldap[s]://hostname:port)
 * - Protocol can be either ldap or ldaps (for SSL encryption). If omitted,
 *   then an unencrypted connection will be established on port 389.
 * - Port number is optional, and defaults to 389. If this doesn't work, try
 *   using one of the following standard port numbers: 636 (ldaps); for Active
 *   Directory Global Catalog forest-wide search, use 3268 (ldap) or 3269 (ldaps)
 *
 * Examples of valid URI:
 *   ldap.example.com
 *   ldap://ldap.example.com
 *   ldaps://ldap.example.com:3269/
 *
 * @global string $g_ldap_server
 */
$g_ldap_server = 'ldaps://ldap.example.com/';

/**
 * The root distinguished name for LDAP searches
 * @global string $g_ldap_root_dn
 */
$g_ldap_root_dn = 'dc=example,dc=com';

/**
 * LDAP search filter for the organization
 * e.g. '(organizationname=*Traffic)'
 * @global string $g_ldap_organization
 */
$g_ldap_organization = '';

/**
 * The LDAP Protocol Version, if 0, then the protocol version is not set.
 * For Active Directory use version 3.
 *
 * @global integer $g_ldap_protocol_version
 */
$g_ldap_protocol_version = 0;

/**
 * Duration of the timeout for TCP connection to the LDAP server (in seconds).
 * Set this to a low value when the hostname defined in $g_ldap_server resolves
 * to multiple IP addresses, allowing rapid failover to the next available LDAP
 * server.
 * Defaults to 0 (infinite)
 *
 * @global int $g_ldap_network_timeout
 */
$g_ldap_network_timeout = 0;

/**
 * Determines whether the LDAP library automatically follows referrals returned
 * by LDAP servers or not. This maps to LDAP_OPT_REFERRALS ldap library option.
 * For Active Directory, this should be set to OFF.
 *
 * @global integer $g_ldap_follow_referrals
 */
$g_ldap_follow_referrals = ON;

/**
 * The distinguished name of the service account to use for binding to the
 * LDAP server.
 * For example, 'CN=ldap,OU=Administrators,DC=example,DC=com'.
 *
 * @global string $g_ldap_bind_dn
 */
$g_ldap_bind_dn = '';

/**
 * The password for the service account used to establish the connection to
 * the LDAP server.
 *
 * @global string $g_ldap_bind_passwd
 */
$g_ldap_bind_passwd = '';

/**
 * The LDAP field for username
 * Use 'sAMAccountName' for Active Directory
 * @global string $g_ldap_uid_field
 */
$g_ldap_uid_field = 'uid';

/**
 * The LDAP field for the user's real name (i.e. common name).
 * @global string $g_ldap_realname_field
 */
$g_ldap_realname_field = 'cn';

/**
 * Use the realname specified in LDAP (ON) rather than the one stored in the
 * database (OFF).
 * @global integer $g_use_ldap_realname
 */
$g_use_ldap_realname = OFF;

/**
 * Use the email address specified in LDAP (ON) rather than the one stored
 * in the database (OFF).
 * @global integer $g_use_ldap_email
 */
$g_use_ldap_email = OFF;

/**
 * This configuration option allows replacing the ldap server with a comma-
 * delimited text file for development or testing purposes.
 * The LDAP simulation file format is as follows:
 *   - One line per user
 *   - Each line has 4 comma-delimited fields
 *        - username,
 *        - realname,
 *        - e-mail,
 *        - password
 *   - Any extra fields are ignored
 * On production systems, this option should be set to ''.
 * @global integer $g_ldap_simulation_file_path
 */
$g_ldap_simulation_file_path = '';

###################
# Status Settings #
###################

/**
 * Status to assign to the bug when submitted.
 * @global integer $g_bug_submit_status
 */
$g_bug_submit_status = NEW_;

/**
 * Status to assign to the bug when assigned.
 * @global integer $g_bug_assigned_status
 */
$g_bug_assigned_status = ASSIGNED;

/**
 * Status to assign to the bug when reopened.
 * @global integer $g_bug_reopen_status
 */
$g_bug_reopen_status = FEEDBACK;

/**
 * Status to assign to the bug when feedback is required from the issue
 * reporter. Once the reporter adds a note the status moves back from feedback
 * to $g_bug_assigned_status or $g_bug_submit_status.
 * @global integer $g_bug_feedback_status
 */
$g_bug_feedback_status = FEEDBACK;

/**
 * When a note is added to a bug currently in $g_bug_feedback_status, and the note
 * author is the bug's reporter, this option will automatically set the bug status
 * to $g_bug_submit_status or $g_bug_assigned_status if the bug is assigned to a
 * developer.  Defaults to enabled.
 * @global boolean $g_reassign_on_feedback
 */
$g_reassign_on_feedback = ON;

/**
 * Resolution to assign to the bug when reopened.
 * @global integer $g_bug_reopen_resolution
 */
$g_bug_reopen_resolution = REOPENED;

/**
 * Default resolution to assign to a bug when it is resolved as being a
 * duplicate of another issue.
 * @global integer $g_bug_duplicate_resolution
 */
$g_bug_duplicate_resolution = DUPLICATE;

/**
 * Bug becomes readonly if its status is >= this status.  The bug becomes
 * read/write again if re-opened and its status becomes less than this
 * threshold.
 * @global integer $g_bug_readonly_status_threshold
 */
$g_bug_readonly_status_threshold = RESOLVED;

/**
 * Bug is resolved, ready to be closed or reopened.  In some custom
 * installations a bug may be considered as resolved when it is moved to a
 * custom (FIXED or TESTED) status.
 * @global integer $g_bug_resolved_status_threshold
 */
$g_bug_resolved_status_threshold = RESOLVED;

/**
 * Threshold resolution which denotes that a bug has been resolved and
 * successfully fixed by developers. Resolutions above this threshold
 * and below $g_bug_resolution_not_fixed_threshold are considered to be
 * resolved successfully.
 * @global integer $g_bug_resolution_fixed_threshold
 */
$g_bug_resolution_fixed_threshold = FIXED;

/**
 * Threshold resolution which denotes that a bug has been resolved without
 * being successfully fixed by developers. Resolutions above this
 * threshold are considered to be resolved in an unsuccessful way.
 * @global integer $g_bug_resolution_not_fixed_threshold
 */
$g_bug_resolution_not_fixed_threshold = UNABLE_TO_REPRODUCE;

/**
 * Bug is closed.  In some custom installations a bug may be considered as
 * closed when it is moved to a custom (COMPLETED or IMPLEMENTED) status.
 * @global integer $g_bug_closed_status_threshold
 */
$g_bug_closed_status_threshold = CLOSED;

/**
 * Automatically set status to ASSIGNED whenever a bug is assigned to a person.
 * This is useful for installations where assigned status is to be used when
 * the bug is in progress, rather than just put in a person's queue.
 * @global integer $g_auto_set_status_to_assigned
 */
$g_auto_set_status_to_assigned	= ON;

/**
 * 'status_enum_workflow' defines the workflow, and reflects a simple
 *  2-dimensional matrix. For each existing status, you define which
 *  statuses you can go to from that status, e.g. from NEW_ you might list statuses
 *  '10:new,20:feedback,30:acknowledged' but not higher ones.
 * The following example can be transferred to config/config_inc.php
 * $g_status_enum_workflow[NEW_]='20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved';
 * $g_status_enum_workflow[FEEDBACK] ='10:new,30:acknowledged,40:confirmed,50:assigned,80:resolved';
 * $g_status_enum_workflow[ACKNOWLEDGED] ='20:feedback,40:confirmed,50:assigned,80:resolved';
 * $g_status_enum_workflow[CONFIRMED] ='20:feedback,50:assigned,80:resolved';
 * $g_status_enum_workflow[ASSIGNED] ='20:feedback,80:resolved,90:closed';
 * $g_status_enum_workflow[RESOLVED] ='50:assigned,90:closed';
 * $g_status_enum_workflow[CLOSED] ='50:assigned';
 * @global array $g_status_enum_workflow
 */
$g_status_enum_workflow = array();

############################
# Bug Attachments Settings #
############################

/**
 * Specify the filename of the magic database file. This is used to
 * guess what the MIME type of a file is. Usually it is safe to leave this
 * setting as the default (blank) as PHP is usually able to find this file
 * by itself.
 * @global string $g_fileinfo_magic_db_file
 */
$g_fileinfo_magic_db_file = '';

/**
 * Specifies the maximum size (in bytes) below which an attachment is
 * previewed in the bug view pages.
 * To disable the previewing of attachments, set max size to 0.
 * @global integer $g_preview_attachments_inline_max_size
 */
$g_preview_attachments_inline_max_size = 256 * 1024;

/**
 * Extensions for text files that can be expanded inline.
 * @global array $g_preview_text_extensions
 */
$g_preview_text_extensions = array(
	'', 'txt', 'diff', 'patch'
);

/**
 * Extensions for images that can be expanded inline.
 * @global array $g_preview_image_extensions
 */
$g_preview_image_extensions = array(
	'bmp', 'png', 'gif', 'jpg', 'jpeg'
);

/**
 * Specifies the maximum width for the auto-preview feature. If no maximum
 * width should be imposed then it should be set to 0.
 * @global integer $g_preview_max_width
 */
$g_preview_max_width = 0;

/**
 * Specifies the maximum height for the auto-preview feature. If no maximum
 * height should be imposed then it should be set to 0.
 * @global integer $g_preview_max_height
 */
$g_preview_max_height = 250;

/**
 * access level needed to view bugs attachments.  View means to see the file
 * names, sizes, and timestamps of the attachments.
 * @global integer $g_view_attachments_threshold
 */
$g_view_attachments_threshold = VIEWER;

/**
 * access level needed to download bug attachments
 * @global integer $g_download_attachments_threshold
 */
$g_download_attachments_threshold = VIEWER;

/**
 * access level needed to delete bug attachments
 * @global integer $g_delete_attachments_threshold
 */
$g_delete_attachments_threshold = DEVELOPER;

/**
 * allow users to view attachments uploaded by themselves even if their access
 * level is below view_attachments_threshold.
 * @global integer $g_allow_view_own_attachments
 */
$g_allow_view_own_attachments = ON;

/**
 * allow users to download attachments uploaded by themselves even if their
 * access level is below download_attachments_threshold.
 * @global integer $g_allow_download_own_attachments
 */
$g_allow_download_own_attachments = ON;

/**
 * allow users to delete attachments uploaded by themselves even if their access
 * level is below delete_attachments_threshold.
 * @global integer $g_allow_delete_own_attachments
 */
$g_allow_delete_own_attachments = OFF;

####################
# Field Visibility #
####################

/**
 * Enable or disable usage of the ETA field.
 * @global integer $g_enable_eta
 */
$g_enable_eta = OFF;

/**
 * Enable or disable usage of the Projection field.
 * @global integer $g_enable_projection
 */
$g_enable_projection = OFF;

/**
 * Enable or disable usage of the Product Build field.
 * @global integer $g_enable_product_build
 */
$g_enable_product_build = OFF;

/**
 * An array of optional fields to show on the bug report page.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - attachments
 *   - category_id
 *   - due_date
 *   - handler
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - target_version
 *   - view_state
 *
 * The summary and description fields are always shown and do not need to be
 * listed in this option. Fields not listed above cannot be shown on the bug
 * report page. Visibility of custom fields is handled via the Manage =>
 * Manage Custom Fields administrator page.
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $g_bug_report_page_fields
 */
$g_bug_report_page_fields = array(
	'additional_info',
	'attachments',
	'category_id',
	'due_date',
	'handler',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'reproducibility',
	'severity',
	'steps_to_reproduce',
	'tags',
	'target_version',
	'view_state',
);

/**
 * An array of optional fields to show on the bug view page.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - attachments
 *   - category_id
 *   - date_submitted
 *   - description
 *   - due_date
 *   - eta
 *   - fixed_in_version
 *   - handler
 *   - id
 *   - last_updated
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - project
 *   - projection
 *   - reporter
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - summary
 *   - tags
 *   - target_version
 *   - view_state
 *
 * Fields not listed above cannot be shown on the bug view page. Visibility of
 * custom fields is handled via the Manage => Manage Custom Fields
 * administrator page.
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $g_bug_view_page_fields
 */
$g_bug_view_page_fields = array(
	'additional_info',
	'attachments',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
	'eta',
	'fixed_in_version',
	'handler',
	'id',
	'last_updated',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'project',
	'projection',
	'reporter',
	'reproducibility',
	'resolution',
	'severity',
	'status',
	'steps_to_reproduce',
	'summary',
	'tags',
	'target_version',
	'view_state',
);

/**
 * An array of optional fields to show on the bug update page.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - category_id
 *   - date_submitted
 *   - description
 *   - due_date
 *   - eta
 *   - fixed_in_version
 *   - handler
 *   - id
 *   - last_updated
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - project
 *   - projection
 *   - reporter
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - summary
 *   - target_version
 *   - view_state
 *
 * Fields not listed above cannot be shown on the bug update page. Visibility
 * of custom fields is handled via the Manage => Manage Custom Fields
 * administrator page.
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $g_bug_update_page_fields
 */
$g_bug_update_page_fields = array(
	'additional_info',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
	'eta',
	'fixed_in_version',
	'handler',
	'id',
	'last_updated',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'project',
	'projection',
	'reporter',
	'reproducibility',
	'resolution',
	'severity',
	'status',
	'steps_to_reproduce',
	'summary',
	'target_version',
	'view_state',
);

/**
 * An array of optional fields to show on the bug change status page. This
 * only changes the visibility of fields shown below the form used for
 * updating the status of an issue.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - attachments
 *   - category_id
 *   - date_submitted
 *   - description
 *   - due_date
 *   - eta
 *   - fixed_in_version
 *   - handler
 *   - id
 *   - last_updated
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - project
 *   - projection
 *   - reporter
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - summary
 *   - tags
 *   - target_version
 *   - view_state
 *
 * Fields not listed above cannot be shown on the bug change status page.
 * Visibility of custom fields is handled via the Manage =>
 * Manage Custom Fields administrator page (use the same settings as the
 * bug view page).
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $g_bug_change_status_page_fields
 */
$g_bug_change_status_page_fields = array(
	'additional_info',
	'attachments',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
	'eta',
	'fixed_in_version',
	'handler',
	'id',
	'last_updated',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'project',
	'projection',
	'reporter',
	'reproducibility',
	'resolution',
	'severity',
	'status',
	'steps_to_reproduce',
	'summary',
	'tags',
	'target_version',
	'view_state',
);

##########################
# MantisBT Misc Settings #
##########################

/**
 * access level needed to report a bug
 * @global integer $g_report_bug_threshold
 */
$g_report_bug_threshold = REPORTER;

/**
 * access level needed to update bugs (i.e., the update_bug_page)
 * This controls whether the user sees the "Update Bug" button in bug_view*_page
 * and the pencil icon in view_all_bug_page
 * @global integer $g_update_bug_threshold
 */
$g_update_bug_threshold = UPDATER;

/**
 * access level needed to view bugs
 * @global integer $g_view_bug_threshold
 */
$g_view_bug_threshold = VIEWER;

/**
 * Access level needed to monitor bugs.
 * Look in the constant_inc.php file if you want to set a different value.
 * @global integer $g_monitor_bug_threshold
 */
$g_monitor_bug_threshold = REPORTER;

/**
 * Access level needed to add other users to the list of users monitoring
 * a bug.
 * Look in the constant_inc.php file if you want to set a different value.
 * @global integer $g_monitor_add_others_bug_threshold
 */
$g_monitor_add_others_bug_threshold = DEVELOPER;

/**
 * Access level needed to delete other users from the list of users
 * monitoring a bug.
 * Look in the constant_inc.php file if you want to set a different value.
 * @global integer $g_monitor_delete_others_bug_threshold
 */
$g_monitor_delete_others_bug_threshold = DEVELOPER;

/**
 * access level needed to view private bugs
 * Look in the constant_inc.php file if you want to set a different value
 * @global integer $g_private_bug_threshold
 */
$g_private_bug_threshold = DEVELOPER;

/**
 * access level needed to be able to be listed in the assign to field.
 * @global integer $g_handle_bug_threshold
 */
$g_handle_bug_threshold = DEVELOPER;

/**
 * access level needed to show the Assign To: button bug_view*_page or
 *  the Assigned list in bug_update*_page.
 *  This allows control over who can route bugs
 * This defaults to $g_handle_bug_threshold
 * @global integer $g_update_bug_assign_threshold
 */
$g_update_bug_assign_threshold = '%handle_bug_threshold%';

/**
 * access level needed to view private bugnotes
 * Look in the constant_inc.php file if you want to set a different value
 * @global integer $g_private_bugnote_threshold
 */
$g_private_bugnote_threshold = DEVELOPER;

/**
 * access level needed to view handler
 * @global integer $g_view_handler_threshold
 */
$g_view_handler_threshold = VIEWER;

/**
 * access level needed to view history
 * @global integer $g_view_history_threshold
 */
$g_view_history_threshold = VIEWER;

/**
 * access level needed to send a reminder from the bug view pages
 * set to NOBODY to disable the feature
 * @global integer $g_bug_reminder_threshold
 */
$g_bug_reminder_threshold = DEVELOPER;

/**
 * Access lever required to drop bug history revisions
 * @global integer $g_bug_revision_drop_threshold
 */
$g_bug_revision_drop_threshold = MANAGER;

/**
 * access level needed to upload files to the project documentation section
 * You can set this to NOBODY to prevent uploads to projects
 * @see $g_enable_project_documentation
 * @see $g_view_proj_doc_threshold
 * @see $g_allow_file_upload
 * @see $g_upload_bug_file_threshold
 * @global integer $g_upload_project_file_threshold
 */
$g_upload_project_file_threshold = MANAGER;

/**
 * access level needed to upload files to attach to a bug
 * You can set this to NOBODY to prevent uploads to bugs but note that
 *  the reporter of the bug will still be able to upload unless you set
 *  $g_allow_reporter_upload or $g_allow_file_upload to OFF
 * See also: $g_upload_project_file_threshold, $g_allow_file_upload,
 *			$g_allow_reporter_upload
 * @global integer $g_upload_bug_file_threshold
 */
$g_upload_bug_file_threshold = REPORTER;

/**
 * Add bugnote threshold
 * @global integer $g_add_bugnote_threshold
 */
$g_add_bugnote_threshold = REPORTER;

/**
 * Threshold at which a user can edit the bugnotes of other users
 * @global integer $g_update_bugnote_threshold
 */
$g_update_bugnote_threshold = DEVELOPER;

/**
 * Threshold needed to view project documentation
 * Note: setting this to ANYBODY will let any user download attachments
 * from private projects, regardless of their being a member of it.
 * @see $g_enable_project_documentation
 * @see $g_upload_project_file_threshold
 * @global integer $g_view_proj_doc_threshold
 */
$g_view_proj_doc_threshold = VIEWER;

/**
 * Site manager
 * @global integer $g_manage_site_threshold
 */
$g_manage_site_threshold = MANAGER;

/**
 * Threshold at which a user is considered to be a site administrator.
 * These users have "superuser" access to all aspects of MantisBT including
 * the admin/ directory. WARNING: DO NOT CHANGE THIS VALUE UNLESS YOU
 * ABSOLUTELY KNOW WHAT YOU'RE DOING! Users at this access level have the
 * ability to damage your MantisBT installation and data within the database.
 * It is strongly advised you leave this option alone.
 * @global integer $g_admin_site_threshold
 */
$g_admin_site_threshold = ADMINISTRATOR;

/**
 * Threshold needed to manage a project: edit project
 * details (not to add/delete projects) ...etc.
 * @global integer $g_manage_project_threshold
 */
$g_manage_project_threshold = MANAGER;

/**
 * Threshold needed to add/delete/modify news
 * @global integer $g_manage_news_threshold
 */
$g_manage_news_threshold = MANAGER;

/**
 * Threshold required to delete a project
 * @global integer $g_delete_project_threshold
 */
$g_delete_project_threshold = ADMINISTRATOR;

/**
 * Threshold needed to create a new project
 * @global integer $g_create_project_threshold
 */
$g_create_project_threshold = ADMINISTRATOR;

/**
 * Threshold needed to be automatically included in private projects
 * @global integer $g_private_project_threshold
 */
$g_private_project_threshold = ADMINISTRATOR;

/**
 * Threshold needed to manage user access to a project
 * @global integer $g_project_user_threshold
 */
$g_project_user_threshold = MANAGER;

/**
 * Threshold needed to manage user accounts
 * @global integer $g_manage_user_threshold
 */
$g_manage_user_threshold = ADMINISTRATOR;

/**
 * Threshold needed to impersonate a user or NOBODY to disable the feature.
 * @global integer $g_impersonate_user_threshold
 */
$g_impersonate_user_threshold = ADMINISTRATOR;

/**
 * Delete bug threshold
 * @global integer $g_delete_bug_threshold
 */
$g_delete_bug_threshold = DEVELOPER;

/**
 * Threshold at which a user can delete the bugnotes of other users.
 * The default value is equal to the configuration setting
 * $g_delete_bug_threshold.
 * @global string $g_delete_bugnote_threshold
 */
$g_delete_bugnote_threshold = '%delete_bug_threshold%';

/**
 * Move bug threshold
 * @global integer $g_move_bug_threshold
 */
$g_move_bug_threshold = DEVELOPER;

/**
 * Threshold needed to set the view status while reporting a bug or a bug note.
 * @global integer $g_set_view_status_threshold
 */
$g_set_view_status_threshold = REPORTER;

/**
 * Threshold needed to update the view status while updating a bug or a bug note.
 * This threshold should be greater or equal to $g_set_view_status_threshold.
 * @global integer $g_change_view_status_threshold
 */
$g_change_view_status_threshold = UPDATER;

/**
 * Threshold needed to show the list of users monitoring a bug on the bug view pages.
 * @global integer $g_show_monitor_list_threshold
 */
$g_show_monitor_list_threshold = DEVELOPER;

/**
 * Threshold needed to be able to use stored queries
 * @global integer $g_stored_query_use_threshold
 */
$g_stored_query_use_threshold = REPORTER;

/**
 * Threshold needed to be able to create stored queries
 * @global integer $g_stored_query_create_threshold
 */
$g_stored_query_create_threshold = DEVELOPER;

/**
 * Threshold needed to be able to create shared stored queries
 * @global integer $g_stored_query_create_shared_threshold
 */
$g_stored_query_create_shared_threshold = MANAGER;

/**
 * Threshold needed to update readonly bugs.  Readonly bugs are identified via
 * $g_bug_readonly_status_threshold.
 * @global integer $g_update_readonly_bug_threshold
 */
$g_update_readonly_bug_threshold = MANAGER;

/**
 * threshold for viewing changelog
 * @global integer $g_view_changelog_threshold
 */
$g_view_changelog_threshold = VIEWER;

/**
* threshold for viewing timeline
* @global integer $g_timeline_view_threshold
*/
$g_timeline_view_threshold = VIEWER;

/**
 * threshold for viewing roadmap
 * @global integer $g_roadmap_view_threshold
 */
$g_roadmap_view_threshold = VIEWER;

/**
 * threshold for updating roadmap, target_version, etc
 * @global integer $g_roadmap_update_threshold
 */
$g_roadmap_update_threshold = DEVELOPER;

/**
 * status change thresholds
 * @global integer $g_update_bug_status_threshold
 */
$g_update_bug_status_threshold = DEVELOPER;

/**
 * access level needed to re-open bugs
 * @global integer $g_reopen_bug_threshold
 */
$g_reopen_bug_threshold = DEVELOPER;

/**
 * access level needed to assign bugs to unreleased product versions
 * @global integer $g_report_issues_for_unreleased_versions_threshold
 */
$g_report_issues_for_unreleased_versions_threshold = DEVELOPER;

/**
 * access level needed to set a bug sticky
 * @global integer $g_set_bug_sticky_threshold
 */
$g_set_bug_sticky_threshold = MANAGER;

/**
 * The minimum access level for someone to be a member of the development team
 * and appear on the project information page.
 * @global integer $g_development_team_threshold
 */
$g_development_team_threshold = DEVELOPER;

/**
 * this array sets the access thresholds needed to enter each status listed.
 * if a status is not listed, it falls back to $g_update_bug_status_threshold
 * example:
 * $g_set_status_threshold = array(
 *     ACKNOWLEDGED => MANAGER,
 *     CONFIRMED => DEVELOPER,
 *     CLOSED => MANAGER
 * );
 * @global array $g_set_status_threshold
 */
$g_set_status_threshold = array( NEW_ => REPORTER );

/**
 * Threshold at which a user can edit his/her own bugnotes.
 * The default value is equal to the configuration setting
 * $g_update_bugnote_threshold.
 * @global integer $g_bugnote_user_edit_threshold
 */
$g_bugnote_user_edit_threshold = '%update_bugnote_threshold%';

/**
 * Threshold at which a user can delete his/her own bugnotes.
 * The default value is equal to the configuration setting
 * $g_delete_bugnote_threshold.
 * @global integer $g_bugnote_user_delete_threshold
 */
$g_bugnote_user_delete_threshold = '%delete_bugnote_threshold%';

/**
 * Threshold at which a user can change the view state of his/her own bugnotes.
 * The default value is equal to the configuration setting
 * $g_change_view_status_threshold.
 * @global integer $g_bugnote_user_change_view_state_threshold
 */
$g_bugnote_user_change_view_state_threshold = '%change_view_status_threshold%';

/**
 * Allow a bug to have no category
 * @global integer $g_allow_no_category
 */
$g_allow_no_category = OFF;

/**
 * limit reporters. Set to ON if you wish to limit reporters to only viewing
 * bugs that they report.
 * @global integer $g_limit_reporters
 */
$g_limit_reporters = OFF;

/**
 * reporter can close. Allow reporters to close the bugs they reported, after
 * they are marked resolved.
 * @global integer $g_allow_reporter_close
 */
$g_allow_reporter_close	 = OFF;

/**
 * reporter can reopen. Allow reporters to reopen the bugs they reported, after
 * they are marked resolved.
 * @global integer $g_allow_reporter_reopen
 */
$g_allow_reporter_reopen = ON;

/**
 * reporter can upload
 * Allow reporters to upload attachments to bugs they reported.
 * @global integer $g_allow_reporter_upload
 */
$g_allow_reporter_upload = ON;

/**
 * account delete
 * Allow users to delete their own accounts
 * @global integer $g_allow_account_delete
 */
$g_allow_account_delete = OFF;

/**
 * Enable anonymous access to MantisBT. You must also specify
 * $g_anonymous_account as the account which anonymous users will browse
 * MantisBT with. The default setting is OFF.
 * @global integer $g_allow_anonymous_login
 */
$g_allow_anonymous_login = OFF;

/**
 * Define the account which anonymous users will assume when using MantisBT.
 * You only need to define this setting when $g_allow_anonymous_login is set to
 * ON. This account will always be treated as a protected account and thus
 * anonymous users will not be able to update the preferences or settings of
 * this account. It is suggested that the access level of this account have
 * read only access to your MantisBT installation (VIEWER). Please read the
 * documentation on this topic before setting up anonymous access to your
 * MantisBT installation.
 * @global string $g_anonymous_account
 */
$g_anonymous_account = '';

/**
 * Bug Linking
 * if a number follows this tag it will create a link to a bug.
 * eg. for # a link would be #45
 * eg. for bug: a link would be bug:98
 * @global string $g_bug_link_tag
 */
$g_bug_link_tag = '#';

/**
 * Bugnote Linking
 * if a number follows this tag it will create a link to a bugnote.
 * eg. for ~ a link would be ~45
 * eg. for bugnote: a link would be bugnote:98
 * @global string $g_bugnote_link_tag
 */
$g_bugnote_link_tag = '~';

/**
 * Bug Count Linking
 * this is the prefix to use when creating links to bug views from bug counts
 * (eg. on the main page and the summary page).
 * Default is a temporary filter
 * only change the filter this time - 'view_all_set.php?type=' . FILTER_ACTION_PARSE_NEW . '&amp;temporary=y'
 * permanently change the filter - 'view_all_set.php?type=' . FILTER_ACTION_PARSE_NEW;
 * (FILTER_ACTION_xxx constants are defined in core/constant_inc.php)
 * @global string $g_bug_count_hyperlink_prefix
 */
$g_bug_count_hyperlink_prefix = 'view_all_set.php?type=' . FILTER_ACTION_PARSE_NEW . '&amp;temporary=y';

/**
 * The regular expression to use when validating new user login names
 * The default regular expression allows a-z, A-Z, 0-9, +, -, dot, space and
 * underscore.  If you change this, you may want to update the
 * ERROR_USER_NAME_INVALID string in the language files to explain
 * the rules you are using on your site
 * See http://en.wikipedia.org/wiki/Regular_Expression for more details about
 * regular expressions. For testing regular expressions, use
 * http://rubular.com/.
 * @global string $g_user_login_valid_regex
 */
$g_user_login_valid_regex = '/^([a-z\d\-.+_ ]+(@[a-z\d\-.]+\.[a-z]{2,4})?)$/i';

/**
 * Default tag prefix used to filter the list of tags in
 * manage_tags_page.php.  Change this to 'A' (or any other
 * letter) if you have a lot of tags in the system and loading
 * the manage tags page takes a long time.
 * @global string $g_default_manage_tag_prefix
 */
$g_default_manage_tag_prefix = 'ALL';

/**
 * CSV Export
 * Set the csv separator
 * @global string $g_csv_separator
 */
$g_csv_separator = ',';

/**
 * The threshold required for users to be able to manage configuration of a project.
 * This includes workflow, email notifications, columns to view, and others.
 */
$g_manage_configuration_threshold = MANAGER;

/**
 * threshold for users to view the system configurations
 * @global integer $g_view_configuration_threshold
 */
$g_view_configuration_threshold = ADMINISTRATOR;

/**
 * threshold for users to set the system configurations generically via
 * MantisBT web interface.
 * WARNING: Users who have access to set configuration via the interface MUST
 * be trusted.  This is due to the fact that such users can set configurations
 * to PHP code and hence there can be a security risk if such users are not
 * trusted.
 * @global integer $g_set_configuration_threshold
 */
$g_set_configuration_threshold = ADMINISTRATOR;

####################################
# MantisBT Look and Feel Variables #
####################################

/**
 * status color codes, using the Tango color palette
 * @global array $g_status_colors
 */
$g_status_colors = array(
	'new'          => '#fcbdbd', # red    (scarlet red #ef2929)
	'feedback'     => '#e3b7eb', # purple (plum        #75507b)
	'acknowledged' => '#ffcd85', # orange (orango      #f57900)
	'confirmed'    => '#fff494', # yellow (butter      #fce94f)
	'assigned'     => '#c2dfff', # blue   (sky blue    #729fcf)
	'resolved'     => '#d2f5b0', # green  (chameleon   #8ae234)
	'closed'       => '#c9ccc4'  # grey   (aluminum    #babdb6)
);

/**
 * The padding level when displaying project ids
 *  The project id will be padded with 0's up to the size given
 * @global integer $g_display_project_padding
 */
$g_display_project_padding = 3;

/**
 * The padding level when displaying bug ids
 *  The bug id will be padded with 0's up to the size given
 * @global integer $g_display_bug_padding
 */
$g_display_bug_padding = 7;

/**
 * The padding level when displaying bugnote ids
 *  The bugnote id will be padded with 0's up to the size given
 * @global integer $g_display_bugnote_padding
 */
$g_display_bugnote_padding = 7;

#############################
# MantisBT Cookie Variables #
#############################

/**
 * Specifies the path under which a cookie is visible
 * All scripts in this directory and its sub-directories will be able
 * to access MantisBT cookies.
 * It is recommended to set this to the actual MantisBT path.
 * @link http://php.net/function.setcookie
 * @global string $g_cookie_path
 */
$g_cookie_path = '/';

/**
 * The domain that the MantisBT cookies are available to
 * @global string $g_cookie_domain
 */
$g_cookie_domain = '';

/**
 * Prefix for all MantisBT cookies
 * This should be an identifier which does not include spaces or periods,
 * and should be unique per MantisBT installation, especially if
 * $g_cookie_path is not restricting the cookies' scope to the actual
 * MantisBT directory.
 * @see $g_cookie_path
 * @global string $g_cookie_prefix
 */
$g_cookie_prefix = 'MANTIS';

/**
 *
 * @global string $g_string_cookie
 */
$g_string_cookie = '%cookie_prefix%_STRING_COOKIE';

/**
 *
 * @global string $g_project_cookie
 */
$g_project_cookie = '%cookie_prefix%_PROJECT_COOKIE';

/**
 *
 * @global string $g_view_all_cookie
 */
$g_view_all_cookie = '%cookie_prefix%_VIEW_ALL_COOKIE';

/**
 * Stores the filter criteria for the Manage User page
 * @global string $g_manage_users_cookie
 */
$g_manage_users_cookie		= '%cookie_prefix%_MANAGE_USERS_COOKIE';

/**
 * Stores the filter criteria for the Manage Config Report page
 * @global string $g_manage_config_cookie
 */
$g_manage_config_cookie		= '%cookie_prefix%_MANAGE_CONFIG_COOKIE';

/**
 *
 * @global string $g_logout_cookie
 */
$g_logout_cookie = '%cookie_prefix%_LOGOUT_COOKIE';

/**
 *
 * @global string $g_bug_list_cookie
 */
$g_bug_list_cookie = '%cookie_prefix%_BUG_LIST_COOKIE';

#############################
# MantisBT Filter Variables #
#############################

/**
 * Show custom fields in the filter dialog and use these in filtering.
 * @global integer $g_filter_by_custom_fields
 */
$g_filter_by_custom_fields = ON;

/**
 * The number of filter fields to display per row.
 * The default is 8.
 * @global integer $g_filter_custom_fields_per_row
 */
$g_filter_custom_fields_per_row = 8;

/**
 * Controls the display of the filter pages.
 * Possible values are:
 * - SIMPLE_ONLY - only simple view
 * - ADVANCED_ONLY - only advanced view (allows multiple value selections)
 * - SIMPLE_DEFAULT - defaults to simple view, but shows a link for advanced
 * - ADVANCED_DEFAULT - defaults to advanced view, but shows a link for simple
 * @global integer $g_view_filters
 */
$g_view_filters = SIMPLE_DEFAULT;

/**
 * This switch enables the use of AJAX to dynamically load and create filter
 * form controls upon request. This method will reduce the amount of data that
 * needs to be transferred upon each page load dealing with filters and thus
 * will result in speed improvements and bandwidth reduction.
 * @global integer $g_use_dynamic_filters
 */
$g_use_dynamic_filters = ON;

/**
 * The threshold required for users to be able to create permalinks.  To turn
 * off this feature use NOBODY.
 * @global integer $g_create_permalink_threshold
 */
$g_create_permalink_threshold = DEVELOPER;

/**
 * The service to use to create a short URL.  The %s will be replaced by the
 * long URL. To disable the feature set to ''.
 * @global string $g_create_short_url
 */
$g_create_short_url = 'http://tinyurl.com/create.php?url=%s';

#########################
# MantisBT Enum Strings #
#########################

/**
 * status from $g_status_index-1 to 79 are used for the onboard customization
 * (if enabled) directly use MantisBT to edit them.
 * @global string $g_access_levels_enum_string
 */
$g_access_levels_enum_string = '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator';

/**
 *
 * @global string $g_project_status_enum_string
 */
$g_project_status_enum_string = '10:development,30:release,50:stable,70:obsolete';

/**
 *
 * @global string $g_project_view_state_enum_string
 */
$g_project_view_state_enum_string = '10:public,50:private';

/**
 *
 * @global string $g_view_state_enum_string
 */
$g_view_state_enum_string = '10:public,50:private';

/**
 *
 * @global string $g_priority_enum_string
 */
$g_priority_enum_string = '10:none,20:low,30:normal,40:high,50:urgent,60:immediate';
/**
 *
 * @global string $g_severity_enum_string
 */
$g_severity_enum_string = '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block';

/**
 *
 * @global string $g_reproducibility_enum_string
 */
$g_reproducibility_enum_string = '10:always,30:sometimes,50:random,70:have not tried,90:unable to duplicate,100:N/A';

/**
 *
 * @global string $g_status_enum_string
 */
$g_status_enum_string = '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed';

/**
 * @@@ for documentation, the values in this list are also used to define
 * variables in the language files (e.g., $s_new_bug_title referenced in
 * bug_change_status_page.php ). Embedded spaces are converted to underscores
 * (e.g., "working on" references $s_working_on_bug_title). They are also
 * expected to be English names for the states
 * @global string $g_resolution_enum_string
 */
$g_resolution_enum_string = '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix';

/**
 *
 * @global string $g_projection_enum_string
 */
$g_projection_enum_string = '10:none,30:tweak,50:minor fix,70:major rework,90:redesign';

/**
 *
 * @global string $g_eta_enum_string
 */
$g_eta_enum_string = '10:none,20:< 1 day,30:2-3 days,40:< 1 week,50:< 1 month,60:> 1 month';

/**
 *
 * @global string $g_sponsorship_enum_string
 */
$g_sponsorship_enum_string = '0:Unpaid,1:Requested,2:Paid';

/**
 *
 * @global string $g_custom_field_type_enum_string
 */
$g_custom_field_type_enum_string = '0:string,1:numeric,2:float,3:enum,4:email,5:checkbox,6:list,7:multiselection list,8:date,9:radio,10:textarea';

###############################
# MantisBT Speed Optimisation #
###############################

/**
 * Use compression of generated html if browser supports it. If you already
 * have compression enabled in your php.ini file (either with
 * zlib.output_compression or output_handler=ob_gzhandler) this option will be
 * ignored.
 *
 * If you do not have zlib enabled in your PHP installation this option will
 * also be ignored.  PHP 4.3.0 and later have zlib included by default. Windows
 * users should uncomment the appropriate line in their php.ini files to load
 * the zlib DLL. You can check what extensions are loaded by running "php -m"
 * at the command line (look for 'zlib')
 * @global integer $g_compress_html
 */
$g_compress_html = ON;

/**
 * Use persistent database connections
 * @global integer $g_use_persistent_connections
 */
$g_use_persistent_connections = OFF;

#################
# Include files #
#################

/**
 * Specify your top/bottom include file (logos, banners, etc)
 * @global string $g_bottom_include_page
 */
$g_bottom_include_page = '%absolute_path%';

/**
 * Specify your top/bottom include file (logos, banners, etc). If a top file is
 * supplied, the default MantisBT logo at the top will be hidden.
 * For example, you could include a centered title at the top of the page with:
 *
 * <div class="center"><span class="pagetitle">TITLE</span></div>
 *
 * The default banner which is removed if you use an include file can be found in html_api.php in
 * the function called html_top_banner.
 *
 * @global string $g_top_include_page
 */
$g_top_include_page = '%absolute_path%';

/**
 * CSS file
 * @global string $g_css_include_file
 */
$g_css_include_file = 'default.css';

/**
 * RTL CSS file
 * @global string $g_css_rtl_include_file
 */
$g_css_rtl_include_file = 'rtl.css';

/**
 * A flag that indicates whether to use CDN (content delivery networks) for loading
 * javascript libraries and their associated CSS.  This improves performance for
 * loading MantisBT pages.  This can be disabled if it is desired that MantisBT
 * doesn't reach out outside corporate network.
 * @global integer $g_cdn_enabled
 */
$g_cdn_enabled = OFF;

################
# Redirections #
################

/**
 * Default page after Login or Set Project
 * @global string $g_default_home_page
 */
$g_default_home_page = 'my_view_page.php';

/**
 * Specify where the user should be sent after logging out.
 * @global string $g_logout_redirect_page
 */
$g_logout_redirect_page = AUTH_PAGE_USERNAME;

###########
# Headers #
###########

/**
 * An array of custom headers to be sent with each page.
 *
 * For example, to allow your MantisBT installation to be viewed in a frame in
 * IE6 when the frameset is not at the same hostname as the MantisBT install,
 * you need to add a P3P header. You could try something like
 * 'P3P: CP="CUR ADM"' in your config file, but make sure to check that the
 * your policy actually matches with what you are promising. See
 * http://msdn.microsoft.com/en-us/library/ms537343.aspx for more information.
 *
 * Even though this is not recommended, you could use this setting to disable
 * previously sent headers. For example, assuming you didn't want to benefit
 * from Content Security Policy, you could add 'Content-Security-Policy:' to
 * the array.
 *
 * @global array $g_custom_headers
 */
$g_custom_headers = array();

/**
 * Browser Caching Control
 * By default, we try to prevent the browser from caching anything. These two
 * settings will defeat this for some cases.
 *
 * Browser Page caching - This will allow the browser to cache all pages. The
 * upside will be better performance, but there may be cases where obsolete
 * information is displayed. Note that this will be bypassed (and caching is
 * allowed) for the bug report pages.
 *
 * @global integer $g_allow_browser_cache
 */
# $g_allow_browser_cache = ON;
/**
 * File caching - This will allow the browser to cache downloaded files.
 * Without this set, there may be issues with IE receiving files, and launching
 * support programs.
 * @global integer $g_allow_file_cache
 */
# $g_allow_file_cache = ON;

#################
# Custom Fields #
#################

/**
 * Threshold needed to manage custom fields
 * @global integer $g_manage_custom_fields_threshold
 */
$g_manage_custom_fields_threshold = ADMINISTRATOR;

/**
 * Threshold needed to link/unlink custom field to/from a project
 * @global integer $g_custom_field_link_threshold
 */
$g_custom_field_link_threshold = MANAGER;

/**
 * Whether to start editng a custom field immediately after creating it
 * @global integer $g_custom_field_edit_after_create
 */
$g_custom_field_edit_after_create = ON;

################
# Custom Menus #
################

/**
 * Add custom options to the main menu.  For example:
 *
 * $g_main_menu_custom_options = array(
 *     array(
 *         'title'        => 'My Link',
 *         'access_level' => MANAGER,
 *         'url'          => 'my_link.php',
 *         'icon'         => 'fa-plug'
 *     ),
 *     array(
 *         'title'        => 'My Link2',
 *         'access_level' => ADMINISTRATOR,
 *         'url'          => 'my_link2.php',
 *         'icon'         => 'fa-plug'
 *     )
 * );
 *
 * Note that if the caption is a localized string name (in strings_english.txt
 * or custom_strings_inc.php), then it will be replaced by the translated
 * string.  Options will only be added to the menu if the current logged in
 * user has the appropriate access level.
 *
 * Access level is an optional field, and no check will be done if it is not set.
 * Icon is an optional field, and 'fa-plug' will be used if it is not set.
 *
 * Use icons from http://fontawesome.io/icons/ - Add "fa-" prefix to icon name.
 *
 * @global array $g_main_menu_custom_options
 */
$g_main_menu_custom_options = array();

#########
# Icons #
#########

/**
 * Maps a file extension to a file type icon.  These icons are printed
 * next to project documents and bug attachments.
 * Note:
 * - Extensions must be in lower case
 * - All icons will be displayed as 16x16 pixels.
 * @global array $g_file_type_icons
 */
$g_file_type_icons = array(
	''		=> 'fa-file-text-o',
	'7z'	=> 'fa-file-archive-o',
	'ace'	=> 'fa-file-archive-o',
	'arj'	=> 'fa-file-archive-o',
	'bz2'	=> 'fa-file-archive-o',
	'c'		=> 'fa-file-code-o',
	'chm'	=> 'fa-file-o',
	'cpp'	=> 'fa-file-code-o',
	'css'	=> 'fa-file-code-o',
	'csv'	=> 'fa-file-text-o',
	'cxx'	=> 'fa-file-code-o',
	'diff'	=> 'fa-file-text-o',
	'doc'	=> 'fa-file-word-o',
	'docx'	=> 'fa-file-word-o',
	'dot'	=> 'fa-file-word-o',
	'eml'	=> 'fa-envelope-o',
	'htm'	=> 'fa-file-code-o',
	'html'	=> 'fa-file-code-o',
	'gif'	=> 'fa-file-image-o',
	'gz'	=> 'fa-file-archive-o',
	'jpe'	=> 'fa-file-image-o',
	'jpg'	=> 'fa-file-image-o',
	'jpeg'	=> 'fa-file-image-o',
	'log'	=> 'fa-file-text-o',
	'lzh'	=> 'fa-file-archive-o',
	'mhtml'	=> 'fa-file-code-o',
	'mid'	=> 'fa-file-audio-o',
	'midi'	=> 'fa-file-audio-o',
	'mov'	=> 'fa-file-movie-o',
	'msg'	=> 'fa-envelope-o',
	'one'	=> 'fa-file-o',
	'patch'	=> 'fa-file-text-o',
	'pcx'	=> 'fa-file-image-o',
	'pdf'	=> 'fa-file-pdf-o',
	'png'	=> 'fa-file-image-o',
	'pot'	=> 'fa-file-word-o',
	'pps'	=> 'fa-file-powerpoint-o',
	'ppt'	=> 'fa-file-powerpoint-o',
	'pptx'	=> 'fa-file-powerpoint-o',
	'pub'	=> 'fa-file-o',
	'rar'	=> 'fa-file-archive-o',
	'reg'	=> 'fa-file',
	'rtf'	=> 'fa-file-word-o',
	'tar'	=> 'fa-file-archive-o',
	'tgz'	=> 'fa-file-archive-o',
	'txt'	=> 'fa-file-text-o',
	'uc2'	=> 'fa-file-archive-o',
	'vsd'	=> 'fa-file-o',
	'vsl'	=> 'fa-file-o',
	'vss'	=> 'fa-file-o',
	'vst'	=> 'fa-file-o',
	'vsu'	=> 'fa-file-o',
	'vsw'	=> 'fa-file-o',
	'vsx'	=> 'fa-file-o',
	'vtx'	=> 'fa-file-o',
	'wav'	=> 'fa-file-audio-o',
	'wbk'	=> 'fa-file-word-o',
	'wma'	=> 'fa-file-audio-o',
	'wmv'	=> 'fa-file-movie-o',
	'wri'	=> 'fa-file-word-o',
	'xlk'	=> 'fa-file-excel-o',
	'xls'	=> 'fa-file-excel-o',
	'xlsx'	=> 'fa-file-excel-o',
	'xlt'	=> 'fa-file-excel-o',
	'xml'	=> 'fa-file-code-o',
	'zip'	=> 'fa-file-archive-o',
	'?'	=> 'fa-file-o' );

/**
 *
 * Content types which will be overridden when downloading files
 *
 * @global array $g_file_download_content_type_overrides
 */
$g_file_download_content_type_overrides = array(
	'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
	'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
	'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
	'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template'
);

/**
 * Icon associative arrays
 * Status to icon mapping
 * @global array $g_status_icon_arr
 */
$g_status_icon_arr = array(
	NONE      => '',
	LOW       => 'fa-chevron-down fa-lg green',
	NORMAL    => 'fa-minus fa-lg orange2',
	HIGH      => 'fa-chevron-up fa-lg red',
	URGENT    => 'fa-arrow-up fa-lg red',
	IMMEDIATE => 'fa-exclamation-triangle fa-lg red'
);

/**
 * Sort direction to icon mapping
 * @global array $g_sort_icon_arr
 */
$g_sort_icon_arr = array(
	ASCENDING  => 'fa-caret-up',
	DESCENDING => 'fa-caret-down'
);

####################
# My View Settings #
####################

/**
 * Number of bugs shown in each box
 * @global integer $g_my_view_bug_count
 */
$g_my_view_bug_count = 10;

/**
 * Boxes to be shown and their order
 * A box that is not to be shown can have its value set to 0
 * @global array $g_my_view_boxes
 */
$g_my_view_boxes = array(
	'assigned'      => '1',
	'unassigned'    => '2',
	'reported'      => '3',
	'resolved'      => '4',
	'recent_mod'    => '5',
	'monitored'     => '6',
	'feedback'      => '0',
	'verify'        => '0',
	'my_comments'   => '0'
);


#############
# RSS Feeds #
#############

/**
 * This flag enables or disables RSS syndication.  In the case where RSS
 * syndication is not used, it is recommended to set it to OFF.
 * @global integer $g_rss_enabled
 */
$g_rss_enabled = ON;


#####################
# Bug Relationships #
#####################

/**
 * Enable relationship graphs support.
 * Show issue relationships using graphs.
 *
 * In order to use this feature, you must first install GraphViz.
 *
 * Graphviz homepage:    http://www.research.att.com/sw/tools/graphviz/
 *
 * Refer to the notes near the top of core/graphviz_api.php and
 * core/relationship_graph_api.php for more information.
 * @global integer $g_relationship_graph_enable
 */
$g_relationship_graph_enable = OFF;

/**
 * Complete path to dot and neato tools. Your webserver must have execute
 * permission to these programs in order to generate relationship graphs.
 * NOTE: On windows, the IIS user may require permissions to cmd.exe to be able to use PHP's proc_open
 * @global string $g_dot_tool
 */
$g_dot_tool = '/usr/bin/dot';
/**
 * Complete path to dot and neato tools. Your webserver must have execute
 * permission to these programs in order to generate relationship graphs.
 * NOTE: On windows, the IIS user may require permissions to cmd.exe to be able to use PHP's proc_open
 * @global string $g_neato_tool
 */
$g_neato_tool = '/usr/bin/neato';

/**
 * Font name and size, as required by Graphviz. If Graphviz fails to run
 * for you, you are probably using a font name that gd can't find. On
 * Linux, try the name of the font file without the extension.
 * @global string $g_relationship_graph_fontname
 */
$g_relationship_graph_fontname = 'Arial';

/**
 *
 * @global integer $g_relationship_graph_fontsize
 */
$g_relationship_graph_fontsize = 8;

/**
 * Default dependency orientation. If you have issues with lots of children
 * or parents, leave as 'horizontal', otherwise, if you have lots of
 * "chained" issue dependencies, change to 'vertical'.
 * @global string $g_relationship_graph_orientation
 */
$g_relationship_graph_orientation = 'horizontal';

/**
 * Max depth for relation graphs. This only affects relation graphs,
 * dependency graphs are drawn to the full depth. A value of 3 is already
 * enough to show issues really unrelated to the one you are currently
 * viewing.
 * @global integer $g_relationship_graph_max_depth
 */
$g_relationship_graph_max_depth = 2;

/**
 * If set to ON, clicking on an issue on the relationship graph will open
 * the bug view page for that issue, otherwise, will navigate to the
 * relationship graph for that issue.
 *
 * @global integer $g_relationship_graph_view_on_click
 */
$g_relationship_graph_view_on_click = OFF;

/**
 * Number of years in the past that custom date fields will display in
 * drop down boxes.
 * @global integer $g_backward_year_count
 */
$g_backward_year_count = 4;

/**
 * Number of years in the future that custom date fields will display in
 * drop down boxes.
 * @global integer $g_forward_year_count
 */
$g_forward_year_count = 4;

/**
 * Custom Group Actions
 *
 * This extensibility model allows developing new group custom actions.  This
 * can be implemented with a totally custom form and action pages or with a
 * pre-implemented form and action page and call-outs to some functions.  These
 * functions are to be implemented in a predefined file whose name is based on
 * the action name. For example, for an action to add a note, the action would
 * be EXT_ADD_NOTE and the file implementing it would be
 * bug_actiongroup_add_note_inc.php. See implementation of this file for
 * details.
 *
 * Sample:
 *
 * array(
 *	array(
 *		'action' => 'my_custom_action',
 *		'label' => 'my_label',   # string to be passed to lang_get_defaulted()
 *		'form_page' => 'my_custom_action_page.php',
 *		'action_page' => 'my_custom_action.php'
 *	)
 *	array(
 *		'action' => 'my_custom_action2',
 *		'form_page' => 'my_custom_action2_page.php',
 *		'action_page' => 'my_custom_action2.php'
 *	)
 *	array(
 *		'action' => 'EXT_ADD_NOTE',  # you need to implement bug_actiongroup_<action_without_'EXT_')_inc.php
 *		'label' => 'actiongroup_menu_add_note' # see strings_english.txt for this label
 *	)
 * );
 *
 * @global array $g_custom_group_actions
 */
$g_custom_group_actions = array();

####################
# Wiki Integration #
####################

/**
 * Wiki Integration Enabled?
 * @global integer $g_wiki_enable
 */
$g_wiki_enable = OFF;

/**
 * Wiki Engine.
 * Supported engines: 'dokuwiki', 'mediawiki', 'twiki', 'wikka', 'xwiki'
 * @global string $g_wiki_engine
 */
$g_wiki_engine = '';

/**
 * Wiki namespace to be used as root for all pages relating to this MantisBT
 * installation.
 * @global string $g_wiki_root_namespace
 */
$g_wiki_root_namespace = 'mantis';

/**
 * URL under which the wiki engine is hosted.
 * Must be on the same server as MantisBT, requires trailing '/'.
 * By default, this is derived from the global MantisBT path ($g_path),
 * replacing the URL's path component by the wiki engine string (i.e. if
 * $g_path = 'http://example.com/mantis/' and $g_wiki_engine = 'dokuwiki',
 * the wiki URL will be 'http://example.com/dokuwiki/')
 * @global string $g_wiki_engine_url
 */
$g_wiki_engine_url = '';

####################
# Recently Visited #
####################

/**
 * This controls whether to show the most recently visited issues by the current user or not.
 * If set to 0, this feature is disabled. Otherwise it is the maximum number of issues to
 * keep in the recently visited list.
 * @global integer $g_recently_visited_count
 */
$g_recently_visited_count = 5;

###############
# Bug Tagging #
###############

/**
 * String that will separate tags as entered for input
 * @global integer $g_tag_separator
 */
$g_tag_separator = ',';

/**
 * Access level required to view tags attached to a bug
 * @global integer $g_tag_view_threshold
 */
$g_tag_view_threshold = VIEWER;

/**
 * Access level required to attach tags to a bug
 * @global integer $g_tag_attach_threshold
 */
$g_tag_attach_threshold = REPORTER;

/**
 * Access level required to detach tags from a bug
 * @global integer $g_tag_detach_threshold
 */
$g_tag_detach_threshold = DEVELOPER;

/**
 * Access level required to detach tags attached by the same user
 * @global integer $g_tag_detach_own_threshold
 */
$g_tag_detach_own_threshold = REPORTER;

/**
 * Access level required to create new tags
 * @global integer $g_tag_create_threshold
 */
$g_tag_create_threshold = REPORTER;

/**
 * Access level required to edit tag names and descriptions
 * @global integer $g_tag_edit_threshold
 */
$g_tag_edit_threshold = DEVELOPER;

/**
 * Access level required to edit descriptions by the creating user
 * @global integer $g_tag_edit_own_threshold
 */
$g_tag_edit_own_threshold = REPORTER;

#################
# Time tracking #
#################

/**
 * Turn on Time Tracking accounting
 * @global integer $g_time_tracking_enabled
 */
$g_time_tracking_enabled = OFF;

/**
 * A billing sums
 * @global integer $g_time_tracking_with_billing
 */
$g_time_tracking_with_billing = OFF;

/**
 * Stop watch to build time tracking field
 * @global integer $g_time_tracking_stopwatch
 */
$g_time_tracking_stopwatch = OFF;

/**
 * access level required to view time tracking information
 * @global integer $g_time_tracking_view_threshold
 */
$g_time_tracking_view_threshold = DEVELOPER;

/**
 * access level required to add/edit time tracking information
 * @global integer $g_time_tracking_edit_threshold
 */
$g_time_tracking_edit_threshold = DEVELOPER;

/**
 * access level required to run reports
 * @global integer $g_time_tracking_reporting_threshold
 */
$g_time_tracking_reporting_threshold = MANAGER;

/**
 * allow time tracking to be recorded without a bugnote
 * @global integer $g_time_tracking_without_note
 */
$g_time_tracking_without_note = ON;

/**
 * default billing rate per hour
 * @global integer $g_time_tracking_billing_rate
 */
$g_time_tracking_billing_rate = 0;

############################
# Profile Related Settings #
############################

/**
 * Enable Profiles
 * @global integer $g_enable_profiles
 */
$g_enable_profiles = ON;

/**
 * Add profile threshold
 * @global integer $g_add_profile_threshold
 */
$g_add_profile_threshold = REPORTER;

/**
 * Threshold needed to be able to create and modify global profiles
 * @global integer $g_manage_global_profile_threshold
 */
$g_manage_global_profile_threshold = MANAGER;

/**
 * Allows the users to enter free text when reporting/updating issues
 * for the profile related fields (i.e. platform, os, os build)
 * @global integer $g_allow_freetext_in_profile_fields
 */
$g_allow_freetext_in_profile_fields = ON;

#################
# Plugin System #
#################

/**
 * enable/disable plugins
 * @global integer $g_plugins_enabled
 */
$g_plugins_enabled = ON;

/**
 * absolute path to plugin files.
 * @global string $g_plugin_path
 */
$g_plugin_path = $g_absolute_path . 'plugins' . DIRECTORY_SEPARATOR;

/**
 * management threshold.
 * @global integer $g_manage_plugin_threshold
 */
$g_manage_plugin_threshold = ADMINISTRATOR;

/**
* A mapping of file extensions to mime types, used when serving resources from plugins
*
* @global array $g_plugin_mime_types
*/
$g_plugin_mime_types = array(
	    'css' => 'text/css',
	    'js'  => 'text/javascript',
	    'gif' => 'image/gif',
	    'png' => 'image/png',
	    'jpg' => 'image/jpeg',
	    'jpeg' => 'image/jpeg'
);

/**
 * Force installation and protection of certain plugins.
 * Note that this is not the preferred method of installing plugins,
 * which should generally be done directly through the plugin management
 * interface.  However, this method will prevent users with admin access
 * from uninstalling plugins through the plugin management interface.
 *
 * Entries in the array must be in the form of a key/value pair
 * consisting of the plugin basename and priority, as such:
 *
 * = array(
 *     'PluginA' => 5,
 *     'PluginB' => 5,
 *     ...
 *
 * @global $g_plugins_force_installed
 */
$g_plugins_force_installed = array();

############
# Due Date #
############

/**
 * threshold to update due date submitted
 * @global integer $g_due_date_update_threshold
 */
$g_due_date_update_threshold = NOBODY;

/**
 * threshold to see due date
 * @global integer $g_due_date_view_threshold
 */
$g_due_date_view_threshold = NOBODY;

/**
 * Default due date value for newly submitted issues:
 * Empty string for no due date set.
 * Related date that is accepted by strtotime (http://php.net/manual/en/function.strtotime.php), e.g. 'today' or '+2 days'.
 * @global string $g_due_date_default
 */
$g_due_date_default = '';

################
# Sub-projects #
################

/**
 * Whether sub-projects feature should be enabled.  Before turning this flag OFF,
 * make sure all sub-projects are moved to top level projects, otherwise
 * they won't be accessible.
 *
 * @global integer $g_subprojects_enabled
 */
$g_subprojects_enabled = ON;

/**
 * Sub-projects should inherit categories from parent projects.
 */
$g_subprojects_inherit_categories = ON;

/**
 * Sub-projects should inherit versions from parent projects.
 *
 * @global integer $g_subprojects_inherit_versions
 */
$g_subprojects_inherit_versions = ON;

##################################
# Debugging / Developer Settings #
##################################

/**
 * Time page loads.
 * The page execution timer shows at the bottom of each page.
 *
 * @global integer $g_show_timer
 */
$g_show_timer = OFF;

/**
 * Show memory usage for each page load in the footer.
 *
 * @global integer $g_show_memory_usage
 */
$g_show_memory_usage = OFF;

/**
 * This is used for debugging the e-mail features in mantis. By default this is blank.
 * This can be set to a valid email address when diagnosing problems with emails.
 * All e-mails are sent to this address with the original To, Cc, Bcc included in the message body.
 * Note: The email is NOT send to the recipients, only to the debug email address.
 * @global string $g_debug_email
 */
$g_debug_email = '';

/**
 * Shows the total number/unique number of queries executed to serve the page.
 *
 * @global integer $g_show_queries_count
 */
$g_show_queries_count = OFF;

/**
 * Errors Display method
 * Defines what {@link http://php.net/errorfunc.constants errors}
 * are displayed and how. Available options are:
 * - DISPLAY_ERROR_HALT    Stop and display error message (including
 *                         variables and backtrace if
 *                         {@link $g_show_detailed_errors} is ON).
 * - DISPLAY_ERROR_INLINE  Display a one line error and continue execution.
 * - DISPLAY_ERROR_NONE    Suppress the error (no display). This is the default
 *                         behavior for unspecified errors constants.
 *
 * The default settings are recommended for use in Production, and will only
 * display MantisBT fatal errors, suppressing output of all other error types.
 *
 * Recommended config_inc.php settings for developers (these are automatically
 * set if the server is localhost):
 * $g_display_errors = array(
 *     E_RECOVERABLE_ERROR => DISPLAY_ERROR_HALT,
 *     E_WARNING           => DISPLAY_ERROR_HALT,
 *     E_ALL               => DISPLAY_ERROR_INLINE,
 * );
 *
 * NOTICE: E_USER_ERROR, E_RECOVERABLE_ERROR and E_ERROR will always be internally
 * set DISPLAY_ERROR_HALT independent of value configured.
 *
 * @global array $g_display_errors
 */
$g_display_errors = array();

# Add developers defaults when server is localhost
# Note: intentionally not using SERVER_ADDR as it's not guaranteed to exist
if( isset( $_SERVER['SERVER_NAME'] ) &&
	( strcasecmp( $_SERVER['SERVER_NAME'], 'localhost' ) == 0 ||
	  $_SERVER['SERVER_NAME'] == '127.0.0.1' ) ) {
	$g_display_errors[E_USER_WARNING] = DISPLAY_ERROR_HALT;
	$g_display_errors[E_WARNING] = DISPLAY_ERROR_HALT;
	$g_display_errors[E_ALL] = DISPLAY_ERROR_INLINE;
}

/**
 * Detailed error messages
 * Shows a list of variables and their values when an error is triggered.
 * Only applies to error types configured to DISPLAY_ERROR_HALT in
 * {@link $g_display_errors}
 *
 * WARNING: Potential security hazard.  Only turn this on when you really
 * need it for debugging
 *
 * @global integer $g_show_detailed_errors
 */
$g_show_detailed_errors = OFF;

/**
 * Debug messages
 * If this option is turned OFF (default) page redirects will continue to
 * function even if a non-fatal error occurs.  For debugging purposes, you
 * can set this to ON so that any non-fatal error will prevent page redirection,
 * allowing you to see the errors.
 * Only turn this option on when debugging
 *
 * @global integer $g_stop_on_errors
 */
$g_stop_on_errors = OFF;

/**
 * System logging
 * This controls the type of logging information recorded.
 * The available log channels are:
 *
 * LOG_NONE, LOG_EMAIL, LOG_EMAIL_RECIPIENT, LOG_EMAIL_VERBOSE, LOG_FILTERING,
 * LOG_AJAX, LOG_LDAP, LOG_DATABASE, LOG_WEBSERVICE, LOG_PLUGIN, LOG_ALL
 *
 * and can be combined using
 * {@link http://php.net/language.operators.bitwise PHP bitwise operators}
 * Refer to {@link $g_log_destination} for details on where to save the logs.
 *
 * @global integer $g_log_level
 */
$g_log_level = LOG_NONE;

/**
 * Specifies where the log data goes
 *
 * The following five options are available:
 * - '':        The empty string means {@link http://php.net/error_log
 *              default PHP error log settings}
 * - 'none':    Don't output the logs, but would still trigger EVENT_LOG
 *              plugin event.
 * - 'file':    Log to a specific file, specified as an absolute path, e.g.
 *              'file:/var/log/mantis.log' (Unix) or
 *              'file:c:/temp/mantisbt.log' (Windows)
 * - 'firebug': make use of Firefox {@link http://getfirebug.com/ Firebug Add-on}.
 *              If user is not running firefox, this options falls back to
 *              the default php error log settings.
 * - 'page':    Display log output at bottom of the page. See also
 *              {@link $g_show_log_threshold} to restrict who can see log data.
 *
 * @global string $g_log_destination
 */
$g_log_destination = '';

/**
 * Indicates the access level required for a user to see the log output
 * (if {@link $g_log_destination} is 'page').
 * Note that this threshold is compared against the user's global access level,
 * rather than the one from the currently active project.
 *
 * @global integer $g_show_log_threshold
 */
$g_show_log_threshold = ADMINISTRATOR;

##########################
# Configuration Settings #
##########################

/**
 * The following list of variables should never be in the database.
 * It is used to bypass the database lookup and look here for appropriate global settings.
 * @global array $g_global_settings
 */
$g_global_settings = array(
	'global_settings', 'admin_checks', 'allow_signup', 'allow_anonymous_login',
	'anonymous_account', 'compress_html', 'allow_permanent_cookie',
	'cookie_time_length', 'cookie_path', 'cookie_domain',
	'cookie_prefix', 'string_cookie', 'project_cookie', 'view_all_cookie',
	'manage_config_cookie', 'logout_cookie',
	'bug_list_cookie', 'crypto_master_salt', 'custom_headers',
	'database_name', 'db_username', 'db_password', 'db_type',
	'db_table_prefix','db_table_suffix', 'display_errors', 'form_security_validation',
	'hostname','html_valid_tags', 'html_valid_tags_single_line', 'default_language',
	'language_auto_map', 'fallback_language', 'login_method', 'plugins_enabled',
	'session_save_path', 'session_validation', 'show_detailed_errors', 'show_queries_count',
	'show_timer', 'show_memory_usage', 'stop_on_errors', 'version_suffix', 'debug_email',
	'fileinfo_magic_db_file', 'css_include_file', 'css_rtl_include_file',
	'file_type_icons', 'path', 'short_path', 'absolute_path', 'core_path',
	'class_path','library_path', 'language_path', 'absolute_path_default_upload_folder',
	'ldap_simulation_file_path', 'plugin_path', 'bottom_include_page', 'top_include_page',
	'default_home_page', 'logout_redirect_page', 'manual_url', 'logo_url', 'wiki_engine_url',
	'cdn_enabled', 'public_config_names', 'email_login_enabled', 'email_ensure_unique',
	'impersonate_user_threshold', 'email_retry_in_days'
);

/**
 * List of config options available via SOAP API.
 * The following list of configuration options is used to check if it is
 * allowed to query a specific configuration option via SOAP API.
 * @global array $g_public_config_names
 */
$g_public_config_names = array(
	'access_levels_enum_string',
	'action_button_position',
	'add_bugnote_threshold',
	'add_profile_threshold',
	'admin_site_threshold',
	'allow_account_delete',
	'allow_anonymous_login',
	'allow_blank_email',
	'allow_delete_own_attachments',
	'allow_download_own_attachments',
	'allow_file_upload',
	'allow_freetext_in_profile_fields',
	'allow_no_category',
	'allow_parent_of_unresolved_to_close',
	'allow_permanent_cookie',
	'allow_reporter_close',
	'allow_reporter_reopen',
	'allow_reporter_upload',
	'allow_signup',
	'allowed_files',
	'anonymous_account',
	'antispam_max_event_count',
	'antispam_time_window_in_seconds',
	'assign_sponsored_bugs_threshold',
	'auto_set_status_to_assigned',
	'backward_year_count',
	'bottom_include_page',
	'bug_assigned_status',
	'bug_change_status_page_fields',
	'bug_closed_status_threshold',
	'bug_count_hyperlink_prefix',
	'bug_duplicate_resolution',
	'bug_feedback_status',
	'bug_link_tag',
	'bug_list_cookie',
	'bug_readonly_status_threshold',
	'bug_reminder_threshold',
	'bug_reopen_resolution',
	'bug_reopen_status',
	'bug_report_page_fields',
	'bug_resolution_fixed_threshold',
	'bug_resolution_not_fixed_threshold',
	'bug_resolved_status_threshold',
	'bug_revision_drop_threshold',
	'bug_submit_status',
	'bug_update_page_fields',
	'bug_view_page_fields',
	'bugnote_link_tag',
	'bugnote_order',
	'bugnote_user_change_view_state_threshold',
	'bugnote_user_delete_threshold',
	'bugnote_user_edit_threshold',
	'cdn_enabled',
	'change_view_status_threshold',
	'check_mx_record',
	'complete_date_format',
	'compress_html',
	'cookie_prefix',
	'cookie_time_length',
	'copyright_statement',
	'create_permalink_threshold',
	'create_project_threshold',
	'create_short_url',
	'css_include_file',
	'css_rtl_include_file',
	'csv_columns',
	'csv_separator',
	'custom_field_edit_after_create',
	'custom_field_link_threshold',
	'custom_field_type_enum_string',
	'custom_group_actions',
	'custom_headers',
	'date_partitions',
	'datetime_picker_format',
	'default_bug_additional_info',
	'default_bug_description',
	'default_bug_eta',
	'default_bug_priority',
	'default_bug_projection',
	'default_bug_relationship_clone',
	'default_bug_relationship',
	'default_bug_reproducibility',
	'default_bug_resolution',
	'default_bug_severity',
	'default_bug_steps_to_reproduce',
	'default_bug_view_status',
	'default_bugnote_order',
	'default_bugnote_view_status',
	'default_category_for_moves',
	'default_email_bugnote_limit',
	'default_email_on_assigned_minimum_severity',
	'default_email_on_assigned',
	'default_email_on_bugnote_minimum_severity',
	'default_email_on_bugnote',
	'default_email_on_closed_minimum_severity',
	'default_email_on_closed',
	'default_email_on_feedback_minimum_severity',
	'default_email_on_feedback',
	'default_email_on_new_minimum_severity',
	'default_email_on_new',
	'default_email_on_priority_minimum_severity',
	'default_email_on_priority',
	'default_email_on_reopened_minimum_severity',
	'default_email_on_reopened',
	'default_email_on_resolved_minimum_severity',
	'default_email_on_resolved',
	'default_email_on_status_minimum_severity',
	'default_email_on_status',
	'default_home_page',
	'default_language',
	'default_limit_view',
	'default_manage_tag_prefix',
	'default_new_account_access_level',
	'default_notify_flags',
	'default_project_view_status',
	'default_redirect_delay',
	'default_refresh_delay',
	'default_reminder_view_status',
	'default_show_changed',
	'default_timezone',
	'delete_bug_threshold',
	'delete_bugnote_threshold',
	'delete_project_threshold',
	'development_team_threshold',
	'disallowed_files',
	'display_bug_padding',
	'display_bugnote_padding',
	'display_errors',
	'display_project_padding',
	'download_attachments_threshold',
	'due_date_default',
	'due_date_update_threshold',
	'due_date_view_threshold',
	'email_ensure_unique',
	'email_dkim_domain',
	'email_dkim_enable',
	'email_dkim_identity',
	'email_dkim_selector',
	'email_login_enabled',
	'email_notifications_verbose',
	'email_padding_length',
	'email_receive_own',
	'email_separator1',
	'email_separator2',
	'enable_email_notification',
	'enable_eta',
	'enable_product_build',
	'enable_profiles',
	'enable_project_documentation',
	'enable_projection',
	'enable_sponsorship',
	'eta_enum_string',
	'excel_columns',
	'fallback_language',
	'favicon_image',
	'file_download_content_type_overrides',
	'file_type_icons',
	'file_upload_max_num',
	'filter_by_custom_fields',
	'filter_custom_fields_per_row',
	'filter_position',
	'font_family',
	'font_family_choices',
	'font_family_choices_local',
	'forward_year_count',
	'from_email',
	'from_name',
	'handle_bug_threshold',
	'handle_sponsored_bugs_threshold',
	'hide_status_default',
	'history_default_visible',
	'history_order',
	'html_make_links',
	'html_valid_tags_single_line',
	'html_valid_tags',
	'impersonate_user_threshold',
	'issue_activity_note_attachments_seconds_threshold',
	'language_auto_map',
	'language_choices_arr',
	'limit_email_domains',
	'limit_reporters',
	'logo_image',
	'logo_url',
	'logout_cookie',
	'logout_redirect_page',
	'long_process_timeout',
	'lost_password_feature',
	'main_menu_custom_options',
	'manage_config_cookie',
	'manage_configuration_threshold',
	'manage_custom_fields_threshold',
	'manage_global_profile_threshold',
	'manage_news_threshold',
	'manage_plugin_threshold',
	'manage_project_threshold',
	'manage_site_threshold',
	'manage_user_threshold',
	'manage_users_cookie',
	'max_dropdown_length',
	'max_failed_login_count',
	'max_file_size',
	'max_lost_password_in_progress_count',
	'mentions_enabled',
	'mentions_tag',
	'min_refresh_delay',
	'minimum_sponsorship_amount',
	'monitor_add_others_bug_threshold',
	'monitor_bug_threshold',
	'monitor_delete_others_bug_threshold',
	'move_bug_threshold',
	'my_view_boxes',
	'my_view_bug_count',
	'news_enabled',
	'news_limit_method',
	'news_view_limit_days',
	'news_view_limit',
	'normal_date_format',
	'notify_flags',
	'notify_new_user_created_threshold_min',
	'plugin_mime_types',
	'plugins_enabled',
	'plugins_force_installed',
	'preview_attachments_inline_max_size',
	'preview_image_extensions',
	'preview_max_height',
	'preview_max_width',
	'preview_text_extensions',
	'print_issues_page_columns',
	'priority_enum_string',
	'priority_significant_threshold',
	'private_bug_threshold',
	'private_bugnote_threshold',
	'private_news_threshold',
	'private_project_threshold',
	'project_cookie',
	'project_status_enum_string',
	'project_user_threshold',
	'project_view_state_enum_string',
	'projection_enum_string',
	'reassign_on_feedback',
	'reauthentication_expiry',
	'reauthentication',
	'recently_visited_count',
	'relationship_graph_enable',
	'relationship_graph_fontname',
	'relationship_graph_fontsize',
	'relationship_graph_max_depth',
	'relationship_graph_orientation',
	'relationship_graph_view_on_click',
	'reminder_receive_threshold',
	'reminder_recipients_monitor_bug',
	'reopen_bug_threshold',
	'report_bug_threshold',
	'report_issues_for_unreleased_versions_threshold',
	'reporter_summary_limit',
	'reproducibility_enum_string',
	'resolution_enum_string',
	'resolution_multipliers',
	'return_path_email',
	'roadmap_update_threshold',
	'roadmap_view_threshold',
	'rss_enabled',
	'search_title',
	'set_bug_sticky_threshold',
	'set_configuration_threshold',
	'set_status_threshold',
	'set_view_status_threshold',
	'severity_enum_string',
	'severity_multipliers',
	'severity_significant_threshold',
	'short_date_format',
	'show_assigned_names',
	'show_avatar_threshold',
	'show_avatar',
	'show_bug_project_links',
	'show_changelog_dates',
	'show_detailed_errors',
	'show_log_threshold',
	'show_memory_usage',
	'show_monitor_list_threshold',
	'show_priority_text',
	'show_product_version',
	'show_project_menu_bar',
	'show_queries_count',
	'show_realname',
	'show_roadmap_dates',
	'show_sticky_issues',
	'show_timer',
	'show_user_email_threshold',
	'show_user_realname_threshold',
	'show_version_dates_threshold',
	'show_version',
	'signup_use_captcha',
	'sort_by_last_name',
	'sort_icon_arr',
	'sponsor_threshold',
	'sponsorship_currency',
	'sponsorship_enum_string',
	'status_colors',
	'status_enum_string',
	'status_enum_workflow',
	'status_icon_arr',
	'stop_on_errors',
	'store_reminders',
	'stored_query_create_shared_threshold',
	'stored_query_create_threshold',
	'stored_query_use_threshold',
	'string_cookie',
	'subprojects_enabled',
	'subprojects_inherit_categories',
	'subprojects_inherit_versions',
	'summary_category_include_project',
	'tag_attach_threshold',
	'tag_create_threshold',
	'tag_detach_own_threshold',
	'tag_detach_threshold',
	'tag_edit_own_threshold',
	'tag_edit_threshold',
	'tag_separator',
	'tag_view_threshold',
	'time_tracking_billing_rate',
	'time_tracking_edit_threshold',
	'time_tracking_enabled',
	'time_tracking_reporting_threshold',
	'time_tracking_stopwatch',
	'time_tracking_view_threshold',
	'time_tracking_with_billing',
	'time_tracking_without_note',
	'timeline_view_threshold',
	'top_include_page',
	'update_bug_assign_threshold',
	'update_bug_status_threshold',
	'update_bug_threshold',
	'update_bugnote_threshold',
	'update_readonly_bug_threshold',
	'upload_bug_file_threshold',
	'upload_project_file_threshold',
	'use_dynamic_filters',
	'user_login_valid_regex',
	'validate_email',
	'version_suffix',
	'view_all_cookie',
	'view_attachments_threshold',
	'view_bug_threshold',
	'view_changelog_threshold',
	'view_configuration_threshold',
	'view_filters',
	'view_handler_threshold',
	'view_history_threshold',
	'view_issues_page_columns',
	'view_proj_doc_threshold',
	'view_sponsorship_details_threshold',
	'view_sponsorship_total_threshold',
	'view_state_enum_string',
	'view_summary_threshold',
	'webmaster_email',
	'webservice_admin_access_level_threshold',
	'webservice_error_when_version_not_found',
	'webservice_eta_enum_default_when_not_found',
	'webservice_priority_enum_default_when_not_found',
	'webservice_projection_enum_default_when_not_found',
	'webservice_readonly_access_level_threshold',
	'webservice_readwrite_access_level_threshold',
	'webservice_resolution_enum_default_when_not_found',
	'webservice_rest_enabled',
	'webservice_severity_enum_default_when_not_found',
	'webservice_specify_reporter_on_add_access_level_threshold',
	'webservice_status_enum_default_when_not_found',
	'webservice_version_when_not_found',
	'wiki_enable',
	'wiki_engine_url',
	'wiki_engine',
	'wiki_root_namespace',
	'window_title',
	'wrap_in_preformatted_text'
);

# Temporary variables should not remain defined in global scope
unset( $t_protocol, $t_host, $t_hosts, $t_port, $t_self, $t_path );


############################
# Webservice Configuration #
############################

/**
 * Minimum global access level required to access webservice for readonly operations.
 *
 * @global integer $g_webservice_readonly_access_level_threshold
 */
$g_webservice_readonly_access_level_threshold = VIEWER;

/**
 * Minimum global access level required to access webservice for read/write operations.
 *
 * @global integer $g_webservice_readwrite_access_level_threshold
 */
$g_webservice_readwrite_access_level_threshold = REPORTER;

/**
 * Minimum global access level required to access the administrator webservices
 *
 * @global integer $g_webservice_admin_access_level_threshold
 */
$g_webservice_admin_access_level_threshold = MANAGER;

/**
 * Minimum project access level required to be able to specify a reporter name when
 * adding an issue.  Otherwise, the current user is used as the reporter.  Users
 * who don't have this access level can always do another step to modify the issue
 * and specify a different name, but in this case it will be logged in the history
 * who original reported the issue.
 *
 * @global integer $g_webservice_specify_reporter_on_add_access_level_threshold
 */
$g_webservice_specify_reporter_on_add_access_level_threshold = DEVELOPER;

/**
 * The following enum id is used when the webservices get enum labels that are not
 * defined in the associated MantisBT installation.  In this case, the enum id is set
 * to the value specified by the corresponding configuration option.
 *
 * @global integer $g_webservice_priority_enum_default_when_not_found
 */
$g_webservice_priority_enum_default_when_not_found = 0;

/**
 * The following enum id is used when the webservices get enum labels that are not
 * defined in the associated MantisBT installation.  In this case, the enum id is set
 * to the value specified by the corresponding configuration option.
 *
 * @global integer $g_webservice_severity_enum_default_when_not_found
 */
$g_webservice_severity_enum_default_when_not_found = 0;

/**
 * The following enum id is used when the webservices get enum labels that are not
 * defined in the associated MantisBT installation.  In this case, the enum id is set
 * to the value specified by the corresponding configuration option.
 *
 * @global integer $g_webservice_status_enum_default_when_not_found
 */
$g_webservice_status_enum_default_when_not_found = 0;

/**
 * The following enum id is used when the webservices get enum labels that are not
 * defined in the associated MantisBT installation.  In this case, the enum id is set
 * to the value specified by the corresponding configuration option.
 *
 * @global integer $g_webservice_resolution_enum_default_when_not_found
 */
$g_webservice_resolution_enum_default_when_not_found = 0;

/**
 * The following enum id is used when the webservices get enum labels that are not
 * defined in the associated MantisBT installation.  In this case, the enum id is set
 * to the value specified by the corresponding configuration option.
 *
 * @global integer $g_webservice_projection_enum_default_when_not_found
 */
$g_webservice_projection_enum_default_when_not_found = 0;

/**
 * The following enum id is used when the webservices get enum labels that are not
 * defined in the associated MantisBT installation.  In this case, the enum id is set
 * to the value specified by the corresponding configuration option.
 *
 * @global integer $g_webservice_eta_enum_default_when_not_found
 */
$g_webservice_eta_enum_default_when_not_found = 0;

/**
 * If ON and the supplied version is not found, then a SoapException will be raised.
 *
 * @global integer $g_webservice_error_when_version_not_found
 */
$g_webservice_error_when_version_not_found = ON;

/**
 * Default version to be used if the specified version is not found and
 * $g_webservice_error_when_version_not_found == OFF.
 * (at the moment this value does not depend on the project).
 *
 * @global string $g_webservice_version_when_not_found
 */
$g_webservice_version_when_not_found = '';

/**
 * Whether the REST API is enabled or not.  Note that this flag only
 * impacts API Token based auth.  Hence, even if the API is disabled, it can still be
 * used from the Web UI using cookie based authentication.
 *
 * @global integer $g_webservice_rest_enabled
 */
$g_webservice_rest_enabled = ON;

####################
# Issue Activities #
####################

/**
 * If a user submits a note with an attachments (with the specified # of seconds)
 * the attachment is linked to the note.  Or 0 for disabling this feature.
 */
$g_issue_activity_note_attachments_seconds_threshold = 3;
