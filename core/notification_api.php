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

/**
 * Notification Api
 *
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package CoreAPI
 * @subpackage NotificationAPI
 */

/**
 * require email_api for sending emails.
 */
require_once( 'email_api.php' );
require_once( 'bugnote_api.php' );
require_once( 'user_api.php' );

/**
 * A class that captures all information about a recipient that is to receive a notification.
 */
class RecipientNotificationInfo {
    /**
     * @var int The recipient (user) id.
     */
    public $id;

    /**
     * @var string The name of the recipient.
     */
    public $name;

    /**
     * @var string The email address of the recipient.
     */
    public $email;

    /**
     * @var int The access level of the user.
     */
    public $access_level;  // applicable to issue or project.

    /**
     * @var bool A flag indicating whether to ignore the access check for users allowed to receive the notification
     *           This is useful when a reporter is commenting on a private bug.  Such recipients are not filtered
     *           later based on access level.
     */
    public $bypass_access_check;  // for cases like reporters, customer adding private note, etc.
}

/**
 * A class that captures all information about a user.
 */
class NotificationUser {
    /**
     * @var int The user id.
     */
    public $id;

    /**
     * @var int The user login name.
     */
    public $username;

    /**
     * @var string The user realname.
     */
    public $realname;

    /**
     * @var int The user access level in context of the notification entity.
     */
    public $access_level;

    /**
     * @var string The user's email address.
     */
    public $email;

    /**
     * Gets the avatar image url for the user in the specified size.
     * @param $p_size The size of the avatar's width and height.
     * @return string The avatar url or empty string.
     */
    public function getAvatar( $p_size ) {
        $t_avatar = user_get_avatar( $this->id, $p_size );

        if ( false !== $t_avatar ) {
            $t_avatar_url = htmlspecialchars( $t_avatar[0] );
            return $t_avatar_url;
        }

        return '';
    }

    /**
     * Gets the realname if available, otherwise, login name.
     * @return string The real name or name of the user.
     */
    public function getName() {
        if ( !is_blank( $this->realname ) ) {
            return $this->realname;
        }

        return $this->username;
    }
}

/**
 * A class that captures all information about a comment for the sake of sending notifications.
 */
class CommentNotificationInfo {
    /**
     * @var int The comment id.
     */
    public $id;

    /**
     * @var string The comment text.
     */
    public $text;

    /**
     * @var bool true for private comments; false otherwise.
     */
    public $private;

    /**
     * @var NotificationUser The author of the comment.
     */
    public $author;

    /**
     * @var int The project id.
     */
    public $project_id;

    /**
     * @var int The issue id.
     */
    public $issue_id;

    /**
     * @var string The issue title.
     */
    public $issue_title;
}

/**
 * A class that captures information about an enum value for the sake of notifications.
 */
class EnumNotificationInfo {
    /**
     * @var int The notification id (code).
     */
    public $id;

    /**
     * @var string The notification language agnostic label.
     */
    public $name;

    /**
     * @var string The notification localized label.
     */
    public $localized_name;
}

/**
 * A class that captures information about a category for the sake of notifications.
 */
class CategoryNotificationInfo {
    /**
     * @var int The category id.
     */
    public $id;

    /**
     * @var string The category name.
     */
    public $name;
}

/**
 * A class that captures all information about an issue for sake of notifications.
 */
class IssueNotificationInfo {
    /**
     * @var int The issue id.
     */
    public $id;

    /**
     * @var int The project id.
     */
    public $project_id;

    /**
     * @var NotificationUser The issue reporter.
     */
    public $reporter;

    /**
     * @var NotificationUser The issue handler or null.
     */
    public $handler;

    /**
     * @var int The issue duplicate id.
     */
    public $duplicate_id;

    /**
     * @var EnumNotificationInfo The issue priority.
     */
    public $priority;

    /**
     * @var EnumNotificationInfo The issue severity.
     */
    public $severity;

    /**
     * @var EnumNotificationInfo The issue reproducibility.
     */
    public $reproducibility;

    /**
     * @var EnumNotificationInfo The issue status.
     */
    public $status;

    /**
     * @var EnumNotificationInfo The issue resolution.
     */
    public $resolution;

    /**
     * @var CategoryNotificationInfo The project category under which issue was filed.
     */
    public $category;

    /**
     * @var string The project version on which issue was found.
     */
    public $version;

    /**
     * @var string The project version where the issue was fixed.
     */
    public $fixed_in_version;

    /**
     * @var string The project version where the issue is targetted to be fixed.
     */
    public $target_version;

    /**
     * @var string The build on which the issue was found.
     */
    public $build;

    /**
     * @var EnumNotificationInfo The issue view state.
     */
    public $view_state;

    /**
     * @var string The issue summary.
     */
    public $summary;

    /**
     * @var string The issue description.
     */
    public $description;

    /**
     * @var string The steps to reproduce the issue.
     */
    public $steps_to_reproduce;

    /**
     * @var string Additional information about the issue.
     */
    public $additional_information;
}

/**
 * Loads information about an enum value into a notification object.
 * @param $p_enum_name The enum type name.
 * @param $p_enum_value The enum value.
 * @return EnumNotificationInfo The notification object.
 */
function notification_load_enum( $p_enum_name, $p_enum_value ) {
    $t_localized_enum = lang_get( $p_enum_name . '_enum_string' );

    $t_enum_info = new EnumNotificationInfo();
    $t_enum_info->id = $p_enum_value;
    $t_enum_info->name = MantisEnum::getLabel( $p_enum_name, $p_enum_value );
    $t_enum_info->localized_name = MantisEnum::getLocalizedLabel( $p_enum_name, $t_localized_enum, $p_enum_value );

    return $t_enum_info;
}

/**
 * Loads information about the specified category into a notification object.
 * @param $p_category_id The category id.
 * @return CategoryNotificationInfo The notification object.
 */
function notification_load_category( $p_category_id ) {
    $t_category_info = new CategoryNotificationInfo();

    $t_category_info->id = $p_category_id;
    $t_category_info->name = category_get_name( $p_category_id );

    return $t_category_info;
}

/**
 * Loads informaiton about the specified issue into a notification object.
 * @param $p_issue_id The issue id.
 * @return IssueNotificationInfo The notification object.
 */
function notification_load_issue( $p_issue_id ) {
    $t_issue = bug_get( $p_issue_id, true );

    $t_issue_info = new IssueNotificationInfo();

    $t_issue_info->id = $t_issue->id;
    $t_issue_info->project_id = $t_issue->project_id;
    $t_issue_info->reporter = notification_load_user( $t_issue->reporter_id );
    $t_issue_info->handler = notification_load_user( $t_issue->handler_id );
    $t_issue_info->duplicate_id = $t_issue->duplicate_id;
    $t_issue_info->priority = notification_load_enum( 'priority', $t_issue->priority );
    $t_issue_info->severity = notification_load_enum( 'severity', $t_issue->severity );
    $t_issue_info->reproducibility = notification_load_enum( 'reproducibility', $t_issue->reproducibility );
    $t_issue_info->status = notification_load_enum( 'status', $t_issue->status );
    $t_issue_info->resolution = notification_load_enum( 'resolution', $t_issue->resolution );
    $t_issue_info->category = notification_load_category( $t_issue->category_id );
    $t_issue_info->version = $t_issue->version;
    $t_issue_info->fixed_in_version = $t_issue->fixed_in_version;
    $t_issue_info->target_version = $t_issue->target_version;
    $t_issue_info->build = $t_issue->build;
    $t_issue_info->view_state = $t_issue->view_state;
    $t_issue_info->summary = $t_issue->summary;
    $t_issue_info->description = $t_issue->description;
    $t_issue_info->steps_to_reproduce = $t_issue->steps_to_reproduce;
    $t_issue_info->additional_information = $t_issue->additional_information;

    return $t_issue_info;
}

/**
 * Loads information about the specified comment into a notification object.
 * @param $p_note_id The note id.
 * @return CommentNotificationInfo The notification object.
 */
function notification_load_comment( $p_note_id ) {
    $t_notification = new CommentNotificationInfo();

    // Load the note information
    $t_note_text = bugnote_get_text( $p_note_id );
    $t_notification->id = $p_note_id;
    $t_notification->text = $t_note_text;

    // Load the author information
    $t_author_id = bugnote_get_field( $p_note_id, 'reporter_id' );
    $t_notification->author = notification_load_user( $t_author_id );

    // Load the view state
    $t_view_state = bugnote_get_field( $p_note_id, 'view_state' );
    $t_notification->private = $t_view_state == VS_PRIVATE;

    // Load the bug information
    $t_bug_id = bugnote_get_field( $p_note_id, 'bug_id' );
    $t_bug = bug_get( $t_bug_id );
    $t_notification->project_id = $t_bug->project_id;
    $t_notification->issue_id = $t_bug->id;
    $t_notification->issue_title = $t_bug->summary;

    return $t_notification;
}

/**
 * Loads information about the specified user into a recipient notification object.
 * @param $p_user_id The user id.
 * @param int $p_project_id The proejct id.
 * @return null|RecipientNotificationInfo The recipient notification object or null.
 */
function notification_load_recipient( $p_user_id, $p_project_id = ALL_PROJECTS ) {
    if ( $p_user_id === 0 || $p_user_id === null ) {
        return null;
    }

    // load user and create recipient
    $t_user = user_get_row( $p_user_id );

    $t_notification_recipient = new RecipientNotificationInfo();
    $t_notification_recipient->id = $p_user_id;
    $t_notification_recipient->email = $t_user['email'];
    $t_notification_recipient->bypass_access_check = false;
    $t_notification_recipient->name = is_blank( $t_user['realname'] ) ? $t_user['username'] : $t_user['realname'];
    $t_notification_recipient->access_level = user_get_access_level( $p_user_id, $p_project_id );

    return $t_notification_recipient;
}

/**
 * Loads information about the specified user into a user notification object.
 * @param $p_user_id The user id.
 * @param int $p_project_id The project id.
 * @return NotificationUser|null The user notification object or null.
 */
function notification_load_user( $p_user_id, $p_project_id = ALL_PROJECTS ) {
    if ( $p_user_id === 0 || $p_user_id === null ) {
        return null;
    }

    $t_user = user_get_row( $p_user_id );

    $t_notification_user = new NotificationUser();
    $t_notification_user->id = $p_user_id;
    $t_notification_user->username = $t_user['username'];
    $t_notification_user->realname = $t_user['realname'];
    $t_notification_user->email = $t_user['email'];
    $t_notification_user->access_level = user_get_access_level( $p_user_id, $p_project_id );

    return $t_notification_user;
}

/**
 * Store the email and send it in case there is no cronjob.
 * @param $p_recipient_email The recipient email address.
 * @param $p_subject The subject of the email.
 * @param $p_message The html body of the email.
 * @param $p_metadata The metadata including headers of the email.
 */
function _notification_email( $p_recipient_email, $p_subject, $p_message, $p_metadata ) {
    email_store( $p_recipient_email, $p_subject, $p_message, $p_metadata['headers'], $p_metadata );

    if ( OFF == config_get( 'email_send_using_cronjob' ) ) {
        email_send_all();
    }
}

/**
 * Create the notification email metadata based on issue id and notification type.
 * @param $p_issue_id The issue id.
 * @param $p_notify_type The notification type (see config_defaults_inc.php for list of types).
 * @return array The array containing the metadata.
 */
function _notification_metadata( $p_issue_id, $p_notify_type ) {
    $t_date_submitted = bug_get_field( $p_issue_id, 'date_submitted' );
    $t_message_md5 = md5( $p_issue_id . $t_date_submitted );

    $t_metadata = array();
    $t_metadata['headers'] = array();
    $t_metadata['priority'] = config_get( 'mail_priority' );
    $t_metadata['charset'] = 'utf-8';
    $t_metadata['html'] = true;
    $t_metadata['message_id'] = $t_message_md5;
    $t_metadata['images'] = array( 'mantis_avatar_48x48.png' );

    if ( $p_notify_type == 'email_notification_title_for_action_bug_submitted' ) {
        $t_metadata['headers']['Message-ID'] = $t_message_md5;
    } else {
        $t_metadata['headers']['In-Reply-To'] = $t_message_md5;
    }

    $t_hostname = '';
    $t_address = explode( '@', config_get( 'from_email' ) );

    if ( isset( $t_address[1] ) ) {
        $t_hostname = $t_address[1];
    }

    $t_metadata['hostname'] = $t_hostname;

    return $t_metadata;
}

/**
 * Send a notification that an issue has been added.
 * @param $p_notification_issue The issue notification object.
 */
function notification_issue_added( $p_notification_issue ) {
    log_event( LOG_EMAIL, sprintf( "Issue id #%d updated by user @U%d.", $p_notification_issue->id, $p_notification_issue->reporter->id ) );
    $t_notify_type = 'new';

    $t_explicit_user_id = array();
    $t_explicit_user_id[] = $p_notification_issue->reporter->id;

    if ( $p_notification_issue->handler !== null ) {
        $t_explicit_user_id[] = $p_notification_issue->handler->id;
    }

    $t_recipients = _notification_build_recipients(
        $p_notification_issue->id,
        $t_notify_type,
        $t_explicit_user_id );

    foreach ( $t_recipients as $t_recipient ) {
        # load (push) user language here as build_visible_bug_data assumes current language
        lang_push( user_pref_get_language( $t_recipient->id, $p_notification_issue->project_id ) );

        $t_params = _notification_build_template_parameters();

        // Add event specific parameters
        $t_subject = email_build_subject( $p_notification_issue->id );

        $t_params['reporter'] = notification_load_user( $p_notification_issue->reporter->id );
        $t_params['reporter.name'] = $t_params['reporter']->getName();
        $t_params['reporter.avatar'] = $t_params['reporter']->getAvatar( 48 );
        $t_params['description'] = nl2br( $p_notification_issue->description );
        $t_params['id'] = $p_notification_issue->id;
        $t_params['title'] = $p_notification_issue->summary;
        $t_params['subject'] = $t_subject;

        // Load and evaluate the template
        $t_html = _notification_load_template( 'issue_added' );
        $t_html = _notification_evaluate( $t_html, $t_params );

        $t_metadata = _notification_metadata( $p_notification_issue->id, $t_notify_type );
        _notification_email( $t_recipient->email, $t_subject, $t_html, $t_metadata );

        lang_pop();
    }
}

/**
 * Send notification that an issue has been updated.
 *
 * @param IssueNotificationInfo $p_notification_issue_old The snapshot of issue information before the update.
 * @param IssueNotificationInfo $p_notification_issue The snapshot of issue information after the update.
 * @param CommentNotificationInfo $p_notification_comment The comment associated with the update.
 * @param string $p_change_hint The update type: 'assigned', 'resolved', 'closed', or otherwise normal update.  See documentation for $g_default_notify_flags for more details.
 */
function notification_issue_updated( $p_notification_issue_old, $p_notification_issue, $p_change_hint = '', $p_notification_comment = null ) {
    $t_notify_type = $p_change_hint;

    $t_user_id = auth_get_current_user_id();

    log_event( LOG_EMAIL, sprintf( "Issue id #%d updated by user @U%d.", $p_notification_issue->id, $t_user_id ) );

    switch ( $p_change_hint ) {
        default:
            $t_template_name = 'issue_updated';
            break;
    }

    $t_explicit_user_id = array();

    // Add reporter(s) - don't worry about duplicates
    $t_explicit_user_id[] = $p_notification_issue->reporter->id;
    if ( $p_notification_issue_old->reporter->id != $p_notification_issue->reporter->id ) {
        $t_explicit_user_id[] = $p_notification_issue_old->reporter->id;
    }

    // Add handler(s) - don't worry about duplicates
    if ( $p_notification_issue_old->handler !== null ) {
        $t_explicit_user_id[] = $p_notification_issue->handler->id;
    }

    if ( $p_notification_issue->handler !== null ) {
        $t_explicit_user_id[] = $p_notification_issue->handler->id;
    }

    // Build full recipients list - this will also de-dupe.
    $t_recipients = _notification_build_recipients(
        $p_notification_issue->id,
        $t_notify_type,
        $t_explicit_user_id );

    foreach ( $t_recipients as $t_recipient ) {
        # load (push) user language here as build_visible_bug_data assumes current language
        lang_push( user_pref_get_language( $t_recipient->id, $p_notification_issue->project_id ) );

        $t_params = _notification_build_template_parameters();

        // Add event specific parameters
        $t_subject = email_build_subject( $p_notification_issue->id );

        $t_params['user'] = notification_load_user( $t_user_id );
        $t_params['user.name'] = $t_params['user']->getName();
        $t_params['user.avatar'] = $t_params['user']->getAvatar( 48 );

        $t_params['reporter'] = notification_load_user( $p_notification_issue->reporter->id );
        $t_params['reporter.name'] = $t_params['reporter']->getName();
        $t_params['reporter.avatar'] = $t_params['reporter']->getAvatar( 48 );

        $t_params['description'] = nl2br( $p_notification_issue->description );
        $t_params['id'] = $p_notification_issue->id;
        $t_params['title'] = $p_notification_issue->summary;
        $t_params['subject'] = $t_subject;

        // Load and evaluate the template
        $t_html = _notification_load_template( $t_template_name );
        $t_html = _notification_evaluate( $t_html, $t_params );

        $t_metadata = _notification_metadata( $p_notification_issue->id, $t_notify_type );
        _notification_email( $t_recipient->email, $t_subject, $t_html, $t_metadata );

        lang_pop();
    }
}

/**
 * Sends a notification that an issue has been deleted.
 * @param $p_notification_issue The snapshot of the issue information before it was deleted.
 */
function notification_issue_deleted( $p_notification_issue ) {
    $t_notify_type = 'deleted';

    $t_explicit_user_id = array();
    $t_explicit_user_id[] = $p_notification_issue->reporter->id;

    if ( $p_notification_issue->handler !== null ) {
        $t_explicit_user_id[] = $p_notification_issue->handler->id;
    }

    $t_recipients = _notification_build_recipients(
        $p_notification_issue->id,
        $t_notify_type,
        $t_explicit_user_id );

    $t_user_id = auth_get_current_user_id();

    log_event( LOG_EMAIL, sprintf( "Issue id #%d deleted by user @U%d.", $p_notification_issue->id, $p_notification_issue->reporter->id ) );

    foreach ( $t_recipients as $t_recipient ) {
        # load (push) user language here as build_visible_bug_data assumes current language
        lang_push( user_pref_get_language( $t_recipient->id, $p_notification_issue->project_id ) );

        $t_params = _notification_build_template_parameters();

        // Add event specific parameters
        $t_subject = email_build_subject( $p_notification_issue->id );

        $t_params['user'] = notification_load_user( $t_user_id );
        $t_params['user.name'] = $t_params['user']->getName();
        $t_params['user.avatar'] = $t_params['user']->getAvatar( 48 );

        $t_params['reporter'] = notification_load_user( $p_notification_issue->reporter->id );
        $t_params['reporter.name'] = $t_params['reporter']->getName();
        $t_params['reporter.avatar'] = $t_params['reporter']->getAvatar( 48 );
        $t_params['description'] = nl2br( $p_notification_issue->description );
        $t_params['id'] = $p_notification_issue->id;
        $t_params['title'] = $p_notification_issue->summary;
        $t_params['subject'] = $t_subject;

        // Load and evaluate the template
        $t_html = _notification_load_template( 'issue_deleted' );
        $t_html = _notification_evaluate( $t_html, $t_params );

        $t_metadata = _notification_metadata( $p_notification_issue->id, $t_notify_type );
        _notification_email( $t_recipient->email, $t_subject, $t_html, $t_metadata );

        lang_pop();
    }
}

/**
 * Sends a notification than a comment has been added.
 * @param CommentNotificationInfo $p_notification_comment The snapshot of the comment information.
 */
function notification_comment_added( $p_notification_comment ) {
    $t_project_id = $p_notification_comment->project_id;
    $t_issue_id = $p_notification_comment->issue_id;
    $t_author_id = $p_notification_comment->author->id;
    $t_notify_type = 'bugnote';

    log_event( LOG_EMAIL, sprintf( "Comment id %d added by user @U%d.", $p_notification_comment->id, $t_author_id ) );

    $t_recipients = _notification_build_recipients( $t_issue_id, $t_notify_type, array( $t_author_id ) );

	foreach ( $t_recipients as $t_recipient ) {
		# load (push) user language here as build_visible_bug_data assumes current language
		lang_push( user_pref_get_language( $t_recipient->id, $t_project_id ) );

		$t_params = _notification_build_template_parameters();

		// Add event specific parameters
        $t_subject = email_build_subject( $p_notification_comment->issue_id );

		$t_params['author'] = notification_load_user( $t_author_id );
        $t_params['author.name'] = $t_params['author']->getName();
		$t_params['text'] = nl2br( $p_notification_comment->text );
        $t_params['issue_id'] = $p_notification_comment->issue_id;
        $t_params['issue_title'] = $p_notification_comment->issue_title;
        $t_params['author.avatar'] = $t_params['author']->getAvatar( 48 );
        $t_params['subject'] = $t_subject;

		// Load and evaluate the template
		$t_html = _notification_load_template( 'note_added' );
		$t_html = _notification_evaluate( $t_html, $t_params );

        $t_metadata = _notification_metadata( $t_issue_id, $t_notify_type );
        _notification_email( $t_recipient->email, $t_subject, $t_html, $t_metadata );

		lang_pop();
	}
}

/**
 * Sends a notification that a note has been updated.
 * @param CommentNotificationInfo $p_notification_comment The snapshot of the comment after the update.
 * @param CommentNotificationInfo $p_old_notification_comment The snapshot of the comment before the update.
 * @param null|int $p_user_id The user id or null for logged in user.
 */
function notification_comment_updated( $p_notification_comment, $p_old_notification_comment, $p_user_id = null ) {
    $t_project_id = $p_notification_comment->project_id;
    $t_issue_id = $p_notification_comment->issue_id;
    $t_author_id = $p_notification_comment->author->id;
    $t_notify_type = 'bugnote';
    $t_user_id = $p_user_id === null ? auth_get_current_user_id() : $p_user_id;

    log_event( LOG_EMAIL, sprintf( "Comment id %d updated by user @U%d.", $p_notification_comment->id, $t_user_id ) );

    $t_recipients = _notification_build_recipients( $t_issue_id, $t_notify_type, array( $t_author_id ) );

    foreach ( $t_recipients as $t_recipient ) {
        # load (push) user language here as build_visible_bug_data assumes current language
        lang_push( user_pref_get_language( $t_recipient->id, $t_project_id ) );

        $t_params = _notification_build_template_parameters();

        // Add event specific parameters
        $t_subject = email_build_subject( $p_notification_comment->issue_id );

        $t_params['author'] = notification_load_user( $t_author_id );
        $t_params['author.name'] = $t_params['author']->getName();
        $t_params['user'] = notification_load_user( $t_user_id );
        $t_params['user.name'] = $t_params['user']->getName();
        $t_params['text'] = nl2br( $p_notification_comment->text );
        $t_params['issue_id'] = $p_notification_comment->issue_id;
        $t_params['issue_title'] = $p_notification_comment->issue_title;
        $t_params['author.avatar'] = $t_params['author']->getAvatar( 48 );
        $t_params['subject'] = $t_subject;

        // Load and evaluate the template
        $t_html = _notification_load_template( 'note_updated' );
        $t_html = _notification_evaluate( $t_html, $t_params );

        $t_metadata = _notification_metadata( $t_issue_id, $t_notify_type );
        _notification_email( $t_recipient->email, $t_subject, $t_html, $t_metadata );

        lang_pop();
    }
}

/**
 * Sends a notification that a comment was deleted.
 * @param CommentNotificationInfo $p_notification_comment The snapshot of the comment info before it was deleted.
 * @param null|int $p_user_id The user id or null for logged in user.
 */
function notification_comment_deleted( $p_notification_comment, $p_user_id = null ) {
    $t_project_id = $p_notification_comment->project_id;
    $t_issue_id = $p_notification_comment->issue_id;
    $t_author_id = $p_notification_comment->author->id;
    $t_notify_type = 'bugnote';
    $t_user_id = $p_user_id === null ? auth_get_current_user_id() : $p_user_id;

    log_event( LOG_EMAIL, sprintf( "Comment id %d deleted by user @U%d.", $p_notification_comment->id, $t_user_id ) );

    $t_recipients = _notification_build_recipients( $t_issue_id, $t_notify_type, array( $t_author_id ) );

    foreach ( $t_recipients as $t_recipient ) {
        # load (push) user language here as build_visible_bug_data assumes current language
        lang_push( user_pref_get_language( $t_recipient->id, $t_project_id ) );

        $t_params = _notification_build_template_parameters();

        // Add event specific parameters
        $t_subject = email_build_subject( $p_notification_comment->issue_id );

        $t_params['author'] = notification_load_user( $t_author_id );
        $t_params['author.name'] = $t_params['author']->getName();
        $t_params['user'] = notification_load_user( $t_user_id );
        $t_params['user.name'] = $t_params['user']->getName();
        $t_params['text'] = nl2br( $p_notification_comment->text );
        $t_params['issue_id'] = $p_notification_comment->issue_id;
        $t_params['issue_title'] = $p_notification_comment->issue_title;
        $t_params['author.avatar'] = $t_params['author']->getAvatar( 48 );
        $t_params['subject'] = $t_subject;

        // Load and evaluate the template
        $t_html = _notification_load_template( 'note_deleted' );
        $t_html = _notification_evaluate( $t_html, $t_params );

        $t_metadata = _notification_metadata( $t_issue_id, $t_notify_type );
        _notification_email( $t_recipient->email, $t_subject, $t_html, $t_metadata );

        lang_pop();
    }
}

/**
 * Creates a array of template parameters defaulted with common parameters.
 * @return array Associative array of parameters.
 */
function _notification_build_template_parameters() {
	$t_params = array();
	$t_params['instance_title'] = config_get( 'window_title' );
	$t_params['instance_url'] = config_get( 'path' );
	$t_params['mantis_avatar_48x48'] = 'cid:mantis_avatar_48x48.png';
	$t_params['mantis_product_name'] = 'Mantis Bug Tracker';
	$t_params['mantis_product_url'] = 'http://www.mantisbt.org/';
    $t_params['timestamp'] = date( config_get( 'complete_date_format' ) );

    return $t_params;
}

/**
 * Loads the template with the specified name.
 * @param string $p_template_name The template file name not including path or extension.
 * @return string The template file content.
 */
function _notification_load_template( $p_template_name ) {
	$t_template_file = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $p_template_name . '.tpl';

	$t_template = file_get_contents( $t_template_file );

	return $t_template;
}

/**
 * Evaluates templates based on provided parameters and returns expanded content.
 * @param string $p_template_content The template content.
 * @param array $p_params The parameters to expand.
 * @return string The expanded template contents.
 */
function _notification_evaluate( $p_template_content, $p_params ) {
	$t_template_content = $p_template_content;

	foreach ( $p_params as $t_param => $t_value ) {
        if ( is_object( $t_value ) ) {
            $t_value = get_object_vars( $t_value );
        }

		if ( is_array( $t_value ) ) {
			foreach ( $t_value as $t_sub_key => $t_sub_value ) {
                if ( is_object( $t_sub_value ) ) {
                    $t_sub_value = get_object_vars( $t_sub_value );
                }

				$t_decorated_param = '{{' . $t_param . '.' . $t_sub_key . '}}';
				$t_template_content = str_replace( $t_decorated_param, $t_sub_value, $t_template_content );
			}			
		} else {
			$t_decorated_param = '{{' . $t_param . '}}';
			$t_template_content = str_replace( $t_decorated_param, $t_value, $t_template_content );
		}
	}

	return $t_template_content;
}

/**
 * Builds the list of recipients that should receive the notification.
 * @param int $p_issue_id The issue id.
 * @param string $p_notify_type The notification type.
 * @param array $p_extra_user_ids_to_email Array of user ids.
 * @return array An array of RecipientNotificationInfo instances.
 */
function _notification_build_recipients( $p_issue_id, $p_notify_type, $p_extra_user_ids_to_email ) {
    $t_recipients = email_collect_recipients( $p_issue_id, $p_notify_type, $p_extra_user_ids_to_email );

    $t_recipients_to_notify = array();
    foreach ( $t_recipients as $t_recipient_id => $t_recipient_email ) {
        $t_recipients_to_notify[] = notification_load_recipient( $t_recipient_id );
    }

    return $t_recipients_to_notify;
}

// TODO: email notification by priority
// - user created
// - user updated
// - user deleted
//
// TODO: Are the following even needed?  Before monitor had one, but not unmonitor.
// - issue monitored
// - issue unmonitored
