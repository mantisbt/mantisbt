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

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'profile_api.php' );
require_api( 'relationship_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_enum_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_issue_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_project_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * Sample:
 * {
 *   "query": {
 *   },
 *   "payload": {
 *      "issue": ... see rest issue update documentation
 *      "bugnote": ... see rest issue update documentation
 *   },
 *   "options: {
 *      "action_type": BUG_UPDATE_TYPE_NORMAL,
 *      "bug_id": 1234,
 *   }
 * }
 */

/**
 * A command that updates an issue.
 */
class IssueUpdateCommand extends Command {

    /**
     * The existing issue.
     *
     * @var BugData
     */
    private $existing_issue = null;

    /**
     * The updated issue.
     *
     * @var BugData
     */
    private $updated_issue = null;

    /**
     * The bugnote.
     *
     * @var BugNoteData
     */
    private $bug_note = null;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * The custom fields to set.
     */
    private $custom_fields_to_set = array();

    /**
     * The resolve issue status.
     *
     * * @var bool
     */
    private $resolve_issue = false;

    /**
     * The close issue status.
     *
     * * @var bool
     */
    private $close_issue = false;

    /**
     * The reopen issue status.
     *
     * * @var bool
     */
    private $reopen_issue = false;

    /**
     * Constructor
     *
     * @param array $p_data The command data.
     */
    function __construct( array $p_data ) {
        parent::__construct( $p_data );
    }

    /**
     * Validate the data.
     */
    protected function validate() {
        $this->user_id = auth_get_current_user_id();

        $f_update_type = $this->option( 'action_type', BUG_UPDATE_TYPE_NORMAL );
        $f_bug_id      = $this->option( 'bug_id', 0 );

        $t_update_data  = $this->payload( 'issue' );
        $t_bugnote_data = $this->payload( 'bugnote' );

        if( !isset( $t_update_data['last_updated'] ) || is_blank( $t_update_data['last_updated'] ) ) {
            throw new ClientException(
            'Last updated not specified', ERROR_EMPTY_FIELD, array( 'last updated' ) );
        }

        $this->custom_fields_to_set = isset( $t_update_data['custom_fields'] ) ? $t_update_data['custom_fields'] : array();

        $this->existing_issue = bug_get( $f_bug_id, true );
        $this->updated_issue  = clone $this->existing_issue;

        if( isset( $t_update_data['due_date'] ) ) {
            $this->updated_issue->due_date = strtotime( $t_update_data['due_date'] );
        } else {
            $this->updated_issue->due_date = date_get_null();
        }

        $this->updated_issue->additional_information = isset( $t_update_data['additional_information'] ) ? $t_update_data['additional_information'] : $this->existing_issue->additional_information;
        $this->updated_issue->build                  = isset( $t_update_data['build'] ) ? $t_update_data['build'] : $this->existing_issue->build;
        $this->updated_issue->category_id            = isset( $t_update_data['category'] ) ? mci_get_category_id( $t_update_data['category'], $this->existing_issue->project_id ) : $this->existing_issue->category_id;
        $this->updated_issue->description            = isset( $t_update_data['description'] ) ? $t_update_data['description'] : $this->existing_issue->description;
        $this->updated_issue->duplicate_id           = isset( $t_update_data['duplicate_id'] ) ? $t_update_data['duplicate_id'] : 0;
        $this->updated_issue->eta                    = isset( $t_update_data['eta'] ) ? mci_get_eta_id( $t_update_data['eta'] ) : $this->existing_issue->eta;
        $this->updated_issue->fixed_in_version       = isset( $t_update_data['fixed_in_version'] ) ? mci_get_version_id( $t_update_data['fixed_in_version'], $this->existing_issue->project_id, 'fixed_in_version' ) : $this->existing_issue->fixed_in_version;
        $this->updated_issue->handler_id             = isset( $t_update_data['handler'] ) ? mci_get_user_id( $t_update_data['handler'] ) : $this->existing_issue->handler_id;
        $this->updated_issue->last_updated           = isset( $t_update_data['last_updated'] ) ? $t_update_data['last_updated'] : null;
        $this->updated_issue->os                     = isset( $t_update_data['os'] ) ? $t_update_data['os'] : $this->existing_issue->os;
        $this->updated_issue->os_build               = isset( $t_update_data['os_build'] ) ? $t_update_data['os_build'] : $this->existing_issue->os_build;
        $this->updated_issue->platform               = isset( $t_update_data['platform'] ) ? $t_update_data['platform'] : $this->existing_issue->platform;
        $this->updated_issue->priority               = isset( $t_update_data['priority'] ) ? mci_get_priority_id( $t_update_data['priority'] ) : $this->existing_issue->priority;
        $this->updated_issue->projection             = isset( $t_update_data['projection'] ) ? mci_get_projection_id( $t_update_data['projection'] ) : $this->existing_issue->projection;
        $this->updated_issue->reporter_id            = isset( $t_update_data['reporter'] ) ? mci_get_user_id( $t_update_data['reporter'] ) : $this->existing_issue->reporter_id;
        $this->updated_issue->reproducibility        = isset( $t_update_data['reproducibility'] ) ? mci_get_reproducibility_id( $t_update_data['reproducibility'] ) : $this->existing_issue->reproducibility;
        $this->updated_issue->resolution             = isset( $t_update_data['resolution'] ) ? mci_get_resolution_id( $t_update_data['resolution'] ) : $this->existing_issue->resolution;
        $this->updated_issue->severity               = isset( $t_update_data['severity'] ) ? mci_get_severity_id( $t_update_data['severity'] ) : $this->existing_issue->severity;
        $this->updated_issue->status                 = isset( $t_update_data['status'] ) ? mci_get_status_id( $t_update_data['status'] ) : $this->existing_issue->status;
        $this->updated_issue->steps_to_reproduce     = isset( $t_update_data['steps_to_reproduce'] ) ? $t_update_data['steps_to_reproduce'] : $this->existing_issue->steps_to_reproduce;
        $this->updated_issue->summary                = isset( $t_update_data['summary'] ) ? $t_update_data['summary'] : $this->existing_issue->summary;
        $this->updated_issue->target_version         = isset( $t_update_data['target_version'] ) ? mci_get_version_id( $t_update_data['target_version'], $this->existing_issue->project_id, 'target_version' ) : $this->existing_issue->target_version;
        $this->updated_issue->version                = isset( $t_update_data['version'] ) ? mci_get_version_id( $t_update_data['version'], $this->existing_issue->project_id, 'version' ) : $this->existing_issue->version;
        $this->updated_issue->view_state             = isset( $t_update_data['view_state'] ) ? mci_get_view_state_id( $t_update_data['view_state'] ) : $this->existing_issue->view_state;
        $this->updated_issue->sticky                 = isset( $t_update_data['sticky'] ) ? (bool) $t_update_data['sticky'] : $this->existing_issue->sticky;

        $this->bug_note = new BugNoteData();

        $this->bug_note->note          = isset( $t_bugnote_data['bugnote_text'] ) ? $t_bugnote_data['bugnote_text'] : '';
        $this->bug_note->view_state    = isset( $t_bugnote_data['private'] ) ? VS_PRIVATE : VS_PUBLIC;
        $this->bug_note->time_tracking = isset( $t_bugnote_data['time_tracking'] ) ? $t_bugnote_data['time_tracking'] : '0:00';

        if( $this->existing_issue->last_updated != $this->updated_issue->last_updated ) {
            throw new ClientException(
            'This issue has been updated by another user, please return to the issue and submit your changes again.', ERROR_BUG_CONFLICTING_EDIT );
        }

        # Determine whether the new status will reopen, resolve or close the issue.
        # Note that multiple resolved or closed states can exist and thus we need to
        # look at a range of statuses when performing this check.
        $t_resolved_status   = config_get( 'bug_resolved_status_threshold' );
        $t_closed_status     = config_get( 'bug_closed_status_threshold' );
        $t_reopen_resolution = config_get( 'bug_reopen_resolution' );
        if( $this->existing_issue->status < $t_resolved_status &&
                $this->updated_issue->status >= $t_resolved_status &&
                $this->updated_issue->status < $t_closed_status
        ) {
            $this->resolve_issue = true;
        } else if( $this->existing_issue->status < $t_closed_status &&
                $this->updated_issue->status >= $t_closed_status
        ) {
            $this->close_issue = true;
        } else if( $this->existing_issue->status >= $t_resolved_status &&
                $this->updated_issue->status <= config_get( 'bug_reopen_status' )
        ) {
            $this->reopen_issue = true;
        }

        $t_reporter_closing = ( $f_update_type == BUG_UPDATE_TYPE_CLOSE ) &&
                bug_is_user_reporter( $f_bug_id, $this->user_id ) &&
                access_can_close_bug( $this->existing_issue, $this->user_id );

        $t_reporter_reopening = ( ( $f_update_type == BUG_UPDATE_TYPE_REOPEN ) || $this->reopen_issue ) &&
                bug_is_user_reporter( $f_bug_id, $this->user_id ) &&
                access_can_reopen_bug( $this->existing_issue, $this->user_id );

        if( !$t_reporter_reopening && !$t_reporter_closing ) {
            switch( $f_update_type ) {
                case BUG_UPDATE_TYPE_ASSIGN:
                    if( !access_has_bug_level( 'update_bug_assign_threshold', $f_bug_id, null ) ) {
                        throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
                    }
                    $t_check_readonly = true;
                    break;
                case BUG_UPDATE_TYPE_CLOSE:
                case BUG_UPDATE_TYPE_REOPEN:
                    if( !access_has_bug_level( 'update_bug_status_threshold', $f_bug_id, null ) ) {
                        throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
                    }
                    $t_check_readonly = false;
                    break;
                case BUG_UPDATE_TYPE_CHANGE_STATUS:
                    if( !access_has_bug_level( 'update_bug_status_threshold', $f_bug_id, null ) ) {
                        throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
                    }
                    $t_check_readonly = true;
                    break;
                case BUG_UPDATE_TYPE_NORMAL:
                default:
                    if( !access_has_bug_level( 'update_bug_threshold', $f_bug_id, null ) ) {
                        throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
                    }
                    $t_check_readonly = true;
                    break;
            }

            if( $t_check_readonly ) {
                # Check if the bug is in a read-only state and whether the current user has
                # permission to update read-only bugs.
                if( bug_is_readonly( $f_bug_id ) ) {
                    throw new ClientException(
                    sprintf( "Issue '%d' is read-only.", $f_bug_id ), ERROR_BUG_READ_ONLY_ACTION_DENIED, array( $f_bug_id ) );
                }
            }
        }

        # If resolving or closing, ensure that all dependent issues have been resolved
        # unless config option enables closing parents with open children.
        if( ( $this->resolve_issue || $this->close_issue ) &&
                !relationship_can_resolve_bug( $f_bug_id ) &&
                OFF == config_get( 'allow_parent_of_unresolved_to_close' ) ) {
            throw new ClientException(
            sprintf( "Issue '%d' not all dependent issues have been resolved.", $f_bug_id ), ERROR_BUG_RESOLVE_DEPENDANTS_BLOCKING );
        }

        # Validate any change to the status of the issue.
        if( $this->existing_issue->status != $this->updated_issue->status ) {
            if( !bug_check_workflow( $this->existing_issue->status, $this->updated_issue->status ) ) {
                throw new ClientException(
                "Invalid value for field 'status'.", ERROR_CUSTOM_FIELD_INVALID_VALUE, array( lang_get( 'status' ) ) );
            }
            if( !access_has_bug_level( access_get_status_threshold( $this->updated_issue->status, $this->updated_issue->project_id ), $f_bug_id ) ) {
                # The reporter may be allowed to close or reopen the issue regardless.
                $t_can_bypass_status_access_thresholds = false;
                if( $this->close_issue &&
                        $this->existing_issue->status >= $t_resolved_status &&
                        $this->existing_issue->reporter_id == $this->user_id &&
                        config_get( 'allow_reporter_close' )
                ) {
                    $t_can_bypass_status_access_thresholds = true;
                } else if( $this->reopen_issue &&
                        $this->existing_issue->status >= $t_resolved_status &&
                        $this->existing_issue->status <= $t_closed_status &&
                        $this->existing_issue->reporter_id == $this->user_id &&
                        config_get( 'allow_reporter_reopen' ) ) {
                    $t_can_bypass_status_access_thresholds = true;
                }
                if( !$t_can_bypass_status_access_thresholds ) {
                    throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
                }
            }
            if( $this->reopen_issue ) {
                # for everyone allowed to reopen an issue, set the reopen resolution
                $this->updated_issue->resolution = $t_reopen_resolution;
            }
        }

        # Validate any change to the handler of an issue.
        if( $this->existing_issue->handler_id != $this->updated_issue->handler_id ) {
            $t_issue_is_sponsored = config_get( 'enable_sponsorship' ) && sponsorship_get_amount( sponsorship_get_all_ids( $f_bug_id ) ) > 0;
            if( !access_has_bug_level( config_get( 'update_bug_assign_threshold' ), $f_bug_id, null ) ) {
                throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
            }
            if( $t_issue_is_sponsored && !access_has_bug_level( config_get( 'handle_sponsored_bugs_threshold' ), $f_bug_id ) ) {
                throw new ClientException( 'Handler does not have the required access level to handle sponsored issues.', ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW );
            }
            if( $this->updated_issue->handler_id != NO_USER ) {
                if( !access_has_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id, $this->updated_issue->handler_id ) ) {
                    throw new ClientException( 'Issue handler does not have sufficient access rights to handle issue at this status.', ERROR_HANDLER_ACCESS_TOO_LOW );
                }
                if( $t_issue_is_sponsored && !access_has_bug_level( config_get( 'assign_sponsored_bugs_threshold' ), $f_bug_id ) ) {
                    trigger_error( ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW, ERROR );
                    throw new ClientException( 'Access Denied: Assigning sponsored issues requires higher access level.', ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW );
                }
            }
        }

        # Check whether the category has been undefined when it's compulsory.
        if( $this->existing_issue->category_id != $this->updated_issue->category_id ) {
            if( $this->updated_issue->category_id == 0 &&
                    !config_get( 'allow_no_category' )
            ) {
                throw new ClientException(
                "A necessary field 'category' was empty.", ERROR_EMPTY_FIELD, array( lang_get( 'category' ) ) );
            }
        }

        # Don't allow changing the Resolution in the following cases:
        # - new status < RESOLVED and resolution denoting completion (>= fixed_threshold)
        # - new status >= RESOLVED and resolution < fixed_threshold
        # - resolution = REOPENED and current status < RESOLVED and new status >= RESOLVED
        # Refer to #15653 for further details (particularly note 37180)
        $t_resolution_fixed_threshold = config_get( 'bug_resolution_fixed_threshold' );
        if( $this->existing_issue->resolution != $this->updated_issue->resolution && (
                ( $this->updated_issue->resolution >= $t_resolution_fixed_threshold && $this->updated_issue->resolution != $t_reopen_resolution && $this->updated_issue->status < $t_resolved_status
                ) || ( $this->updated_issue->resolution == $t_reopen_resolution && ( $this->existing_issue->status < $t_resolved_status || $this->updated_issue->status >= $t_resolved_status
                ) ) || ( $this->updated_issue->resolution < $t_resolution_fixed_threshold && $this->updated_issue->status >= $t_resolved_status
                )
                ) ) {
            throw new ClientException(
            sprintf( 'Resolution "%1$s" is not allowed for status "%2$s".', get_enum_element( 'resolution', $this->updated_issue->resolution ), get_enum_element( 'status', $this->updated_issue->status ) ), ERROR_INVALID_RESOLUTION, array( get_enum_element( 'resolution', $this->updated_issue->resolution ), get_enum_element( 'status', $this->updated_issue->status ) ) );
        }

        # Ensure that the user has permission to change the target version of the issue.
        if( $this->existing_issue->target_version !== $this->updated_issue->target_version ) {
            if( !access_has_bug_level( config_get( 'roadmap_update_threshold' ), $f_bug_id, null ) ) {
                throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
            }
        }

        # Ensure that the user has permission to change the view status of the issue.
        if( $this->existing_issue->view_state != $this->updated_issue->view_state ) {
            if( !access_has_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id, null ) ) {
                throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
            }
        }

        # Ensure that the user has permission to change the sticky of the issue.
        if( $this->existing_issue->sticky != $this->updated_issue->sticky ) {
            if( !access_has_bug_level( config_get( 'set_bug_sticky_threshold' ), $f_bug_id, null ) ) {
                throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
            }
        }

        mci_project_custom_fields_validate( $this->updated_issue->project_id, $this->custom_fields_to_set );

        # Perform validation of the duplicate ID of the bug.
        if( $this->updated_issue->duplicate_id != 0 ) {
            if( $this->updated_issue->duplicate_id == $f_bug_id ) {
                throw new ClientException( 'You cannot set an issue as a duplicate of itself.', ERROR_BUG_DUPLICATE_SELF );
            }

            bug_ensure_exists( $this->updated_issue->duplicate_id );

            if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $this->updated_issue->duplicate_id ) ) {
                throw new ClientException( sprintf( 'Access denied: The issue %1$d requires higher access level.', $this->updated_issue->duplicate_id ), ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, array( $this->updated_issue->duplicate_id ) );
            }
        }

        # Validate the new bug note (if any is provided).
        if( $this->bug_note->note ||
                ( config_get( 'time_tracking_enabled' ) &&
                helper_duration_to_minutes( $this->bug_note->time_tracking ) > 0 )
        ) {
            if( !access_has_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id, null ) ) {
                throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
            }
            if( !$this->bug_note->note &&
                    !config_get( 'time_tracking_without_note' )
            ) {
                throw new ClientException( sprintf( 'A necessary field "%1$s" was empty. Please recheck your inputs.', lang_get( 'bugnote' ) ), ERROR_EMPTY_FIELD, array( lang_get( 'bugnote' ) ) );
            }
            if( $this->bug_note->view_state != config_get( 'default_bugnote_view_status' ) ) {
                if( !access_has_bug_level( config_get( 'set_view_status_threshold' ), $f_bug_id, null ) ) {
                    throw new ClientException( 'Access Denied.', ERROR_ACCESS_DENIED );
                }
            }
        }

        # Handle the reassign on feedback feature. Note that this feature generally
        # won't work very well with custom workflows as it makes a lot of assumptions
        # that may not be true. It assumes you don't have any statuses in the workflow
        # between 'bug_submit_status' and 'bug_feedback_status'. It assumes you only
        # have one feedback, assigned and submitted status.
        if( $this->bug_note->note &&
                config_get( 'reassign_on_feedback' ) &&
                $this->existing_issue->status == config_get( 'bug_feedback_status' ) &&
                $this->updated_issue->status == $this->existing_issue->status &&
                $this->updated_issue->handler_id != $this->user_id &&
                $this->updated_issue->reporter_id == $this->user_id
        ) {
            if( $this->updated_issue->handler_id != NO_USER ) {
                $this->updated_issue->status = config_get( 'bug_assigned_status' );
            } else {
                $this->updated_issue->status = config_get( 'bug_submit_status' );
            }
        }

        # Handle automatic assignment of issues.
        $this->updated_issue->status = bug_get_status_for_assign( $this->existing_issue->handler_id, $this->updated_issue->handler_id, $this->existing_issue->status, $this->updated_issue->status );

        # Allow a custom function to validate the proposed bug updates. Note that
        # custom functions are being deprecated in MantisBT. You should migrate to
        # the new plugin system instead.
        helper_call_custom_function( 'issue_update_validate', array( $f_bug_id, $this->updated_issue, $this->bug_note->note ) );

        # Allow plugins to validate/modify the update prior to it being committed.
        $this->updated_issue = event_signal( 'EVENT_UPDATE_BUG_DATA', $this->updated_issue, $this->existing_issue );
    }

    /**
     * Process the command.
     *
     * @returns array Command response
     */
    protected function process() {
        # Commit the bug updates to the database.
        $t_text_field_update_required = ( $this->existing_issue->description != $this->updated_issue->description ) ||
                ( $this->existing_issue->additional_information != $this->updated_issue->additional_information ) ||
                ( $this->existing_issue->steps_to_reproduce != $this->updated_issue->steps_to_reproduce );
        $this->updated_issue->update( $t_text_field_update_required, true );

        # Update custom field values.
        mci_issue_set_custom_fields( $this->updated_issue->id, $this->custom_fields_to_set );

        # Add a bug note if there is one.
        if( $this->bug_note->note || helper_duration_to_minutes( $this->bug_note->time_tracking ) > 0 ) {
            $t_bugnote_id = bugnote_add( $this->updated_issue->id, $this->bug_note->note, $this->bug_note->time_tracking, $this->bug_note->view_state == VS_PRIVATE, 0, '', null, false );
            bugnote_process_mentions( $this->updated_issue->id, $t_bugnote_id, $this->bug_note->note );
        }

        # Add a duplicate relationship if requested.
        if( $this->updated_issue->duplicate_id != 0 ) {
            relationship_upsert( $this->updated_issue->id, $this->updated_issue->duplicate_id, BUG_DUPLICATE, /* email_for_source */ false );

            if( user_exists( $this->existing_issue->reporter_id ) ) {
                bug_monitor( $this->updated_issue->duplicate_id, $this->existing_issue->reporter_id );
            }
            if( user_exists( $this->existing_issue->handler_id ) ) {
                bug_monitor( $this->updated_issue->duplicate_id, $this->existing_issue->handler_id );
            }

            bug_monitor_copy( $this->updated_issue->id, $this->updated_issue->duplicate_id );
        }

        event_signal( 'EVENT_UPDATE_BUG', array( $this->existing_issue, $this->updated_issue ) );

        # Allow a custom function to respond to the modifications made to the bug. Note
        # that custom functions are being deprecated in MantisBT. You should migrate to
        # the new plugin system instead.
        helper_call_custom_function( 'issue_update_notify', array( $this->updated_issue->id ) );

        # Send a notification of changes via email.
        if( $this->resolve_issue ) {
            email_resolved( $this->updated_issue->id );
            email_relationship_child_resolved( $this->updated_issue->id );
        } else if( $this->close_issue ) {
            email_close( $this->updated_issue->id );
            email_relationship_child_closed( $this->updated_issue->id );
        } else if( $this->reopen_issue ) {
            email_bug_reopened( $this->updated_issue->id );
        } else if( $this->existing_issue->handler_id != $this->updated_issue->handler_id ) {
            email_owner_changed( $this->updated_issue->id, $this->existing_issue->handler_id, $this->updated_issue->handler_id );
        } else if( $this->existing_issue->status != $this->updated_issue->status ) {
            $t_new_status_label = MantisEnum::getLabel( config_get( 'status_enum_string' ), $this->updated_issue->status );
            $t_new_status_label = str_replace( ' ', '_', $t_new_status_label );
            email_bug_status_changed( $this->updated_issue->id, $t_new_status_label );
        } else {
            email_bug_updated( $this->updated_issue->id );
        }

        return array( TRUE );
    }

}
