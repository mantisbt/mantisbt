<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This file is to aid in localization

	### General Strings
	$s_go_back = "Go Back";
	$s_proceed = "Click here to proceed";
	$s_sql_error_detected = "ERROR: SQL error detected.  Please report this to ";

	$s_switch = "Switch";
	$s_logged_in_as = "Logged in as";

	### Email Strings
	$s_new_account_subject = "Your new user account";
	$s_new_account_greeting = "Greetings and welcome to the bugtracker.  Here is the information you need to login\n\n";
	$s_new_account_url = "You can login to the site here: ";
	$s_new_account_username = "Username: ";
	$s_new_account_password = "Password: ";
	$s_new_account_message = "After logging into the site please change your password.  Also note that your password is stored via one way encryption.  The staff cannot retrieve your password.  If you forget your password it will have to be reset.\n\n";
	$s_new_account_do_not_reply = "Do not reply to this message.\n";

	### account_delete.php3
	$s_account_protected_msg = "Account protected. Cannot change settings...";
	$s_account_removed_msg = "Your account has been removed...";

	### account_delete_page.php3
	$s_confirm_delete_msg = "Are you sure you wish to delete your account?";
	$s_delete_account_button = "Delete Account";

	### account_page.php3
	$s_manage_profiles_link = "Manage Profiles";
	$s_change_preferences_link = "Change Preferences";
	$s_edit_account_title = "Edit Account";
	$s_username = "Username";
	$s_email = "Email";
	$s_password = "Password";
	$s_confirm_password  = "Confirm Password";
	$s_access_level = "Access Level";
	$s_update_user_button = "Update User";
	### $s_delete_account_button # defined above

	### account_prefs_page.php3
	$s_default_account_preferences_title = "Default Account Preferences";
	$s_advanced_report = "Advanced Report";
	$s_advanced_view = "Advanced View";
	$s_default_project = "Default Project";
	$s_update_prefs_button = "Update Prefs";
	$s_reset_prefs_button = "Reset Prefs";

	### account_prefs_reset.php3
	$s_prefs_reset_msg = "Preferences have been reset...";

	### account_prefs_update.php3
	$s_prefs_updated_msg = "Preferences have been updated...";

	### account_profile_add.php3
	$s_profile_added_msg = "Profile added...";

	### account_profile_delete.php3
	$s_profile_deleted_msg = "Deleted profile...";

	### account_profile_edit_page.php3
	$s_edit_profile_title = "Edit Profile";
	$s_platform = "Platform";
	$s_operating_system = "Operating System";
	$s_version = "Version/Build";
	$s_additional_description = "Additional Description";
	$s_update_profile_button = "Update Profile";

	### account_profile_make_default.php3
	$s_profile_defaulted_msg = "Default profile updated...";

	### account_profile_manage_page.php3
	$s_add_profile_msg = "Add Profile";
	### $s_platform # defined above
	### $s_operating_system # defined above
	### $s_version # defined above
	### $s_additional_description # defined above
	$s_add_profile_button = "Add Profile";
	$s_edit_or_delete_profiles_title = "Edit or Delete Profiles";
	$s_edit_profile = "Edit Profile";
	$s_make_default = "Make Default";
	$s_delete_profile = "Delete Profile";
	$s_select_profile = "Select Profile";
	$s_submit_button = "Submit";

	### account_profile_update.php3
	$s_profile_updated_msg = "Profile updated...";

	### account_update.php3
	### $s_account_protected_msg # defined above
	$s_account_updated_msg = "Your account has been successfully updated...";

    ### bug_assign.php3
    $s_bug_assign_msg       = "Bug has been successfully assigned...";

	### bug_delete.php3
	$s_bug_deleted_msg = "Bug has been deleted...";

	### bug_delete_page.php3
	$s_delete_bug_sure_msg = "Are you sure you wish to delete this bug?";
	$s_delete_bug_button = "Delete Bug";

	### bug_reopen_page.php3
	$s_bug_reopened_msg = "Bug has been reopened...";
	$s_reopen_add_bugnote_title = "Add Bugnote Reason For Reopening Bug";
	$s_bugnote_add_reopen_button = "Add Bugnote and Reopen Bug";

	### bug_resolve_page.php3
	$s_resolve_bug_title = "Resolve Bug";
	### $s_resolution # defined above
	### $s_duplicate_id # defined above
	$s_resolve_bug_button = "Resolve Bug";

	### bug_resolve_page2.php3
	$s_bug_resolved_msg = "Bug has been resolved. Enter bugnote below...";
	$s_resolve_add_bugnote_title = "Add Bugnote Reason For Resolving Bug";
	$s_bugnote_add_resolve_button = "Add Bugnote";

	### bug_update.php3
	$s_bug_updated_msg = "Bug has been successfully updated...";

	### bug_update_advanced_page.php3
	$s_back_to_bug_link = "Back To Bug";
	$s_update_simple_link = "Update Simple";
	$s_updating_bug_advanced_title = "Updating Bug Information";
	$s_id = "ID";
	$s_category = "Category";
	$s_severity = "Severity";
	$s_reproducibility = "Reproducibility";
	$s_date_submitted = "Date Submitted";
	$s_last_update = "Last Update";
	$s_reporter = "Reporter";
	$s_assigned_to = "Assigned To";
	$s_priority = "Priority";
	$s_resolution = "Resolution";
	### $s_platform # defined above
	$s_status = "Status";
	$s_duplicate_id = "Duplicate ID";
	$s_os = "OS";
	$s_projection = "Projection";
	$s_os_version = "Version";
	$s_eta = "ETA";
	$s_product_version = "Product Version";
	$s_build = "Build";
	$s_votes = "Votes";
	$s_summary = "Summary";
	$s_description = "Description";
	$s_steps_to = "Steps To";
	$s_reproduce = "Reproduce";
	$s_additional = "Additional";
	$s_information = "Information";
	$s_update_information_button = "Update Information";

	### bug_update_page.php3
	### $s_back_to_bug_link # defined above
	$s_update_advanced_link = "Update Advanced";
	$s_updating_bug_simple_title = "Updating Bug Information";
	### $s_id # defined above
	### $s_category # defined above
	### $s_severity # defined above
	### $s_reproducibility # defined above
	### $s_date_submitted # defined above
	### $s_last_update # defined above
	### $s_reporter # defined above
	### $s_assigned_to # defined above
	### $s_priority # defined above
	### $s_resolution # defined above
	### $s_status # defined above
	### $s_duplicate_id # defined above
	### $s_summary # defined above
	### $s_description # defined above
	### $s_additional # defined above
	### $s_information # defined above
	### $s_update_bug_button # defined above
	### $s_resolve_bug_button # defined above
	### $s_delete_bug_button # defined above
	### $s_reopen_bug_button # defined above
	### $s_update_information_button # defined above

	### bug_vote_add.php3
	$s_vote_added_msg = "Vote has been added...";

	### bugnote_add.php3
	$s_bugnote_added_msg = "Bugnote added...";

	### bugnote_add_inc.php
	$s_add_bugnote_button = "Add Bugnote";

	### bugnote_add_page.php3
	$s_add_bugnote_title = "Add Bugnote";
	### $s_add_bugnote_button # defined above

	### bugnote_delete.php3
	$s_bugnote_deleted_msg = "Bugnote has been successfully deleted...";
	$s_bug_notes_title = "Bug Notes";

	### bugnote_inc.php
	$s_no_bugnotes_msg = "There are no notes attached to this bug.";
	### $s_bug_notes_title # defined above
	$s_delete_link = "Delete";

	### choose_project_page.php3
	$s_choose_project_title = "Choose Project";
	$s_projects = "Projects";
	$s_select_project_button = "Select Project";

	### index.php3
	$s_click_to_login = "Click here to login";

	### login.php3

	### login_error_page.php3
	$s_login_error_msg = "There was an error: your account may be disabled or the username/password you entered is incorrect.";
	$s_login_title = "Login";
	### $s_username # defined above
	### $s_password # defined above
	$s_save_login = "Save Login";
	$s_login_button = "Login";

	### login_page.php3
	$s_login_page_info = "Welcome to the bugtracker.";
	### $s_login_title # defined above
	### $s_username # defined above
	### $s_password # defined above
	### $s_save_login # defined above
	$s_choose_project = "Choose Project";
	### $s_login_button # defined above
	$s_signup_link = "signup for a new account";

	### logout_page.php3
	$s_logged_out_title = "Logged Out...";
	$s_redirecting = "...Redirecting";
	$s_here = "Here";

	### main_page.php3
	$s_newer_news_link = "Newer News";
	$s_older_news_link = "Older News";

	### manage_category_page.php3
	$s_edit_categories_title = "Edit Categories";
	$s_category_names = "Category Names";
	$s_update_categories_button = "Update Categories";

	### manage_category_update.php3
	$s_categories_updated_msg = "Cateogries successfully updated...";

	### manage_create_new_user.php3
	$s_created_user_part1 = "Created user";
	$s_created_user_part2 = "with an access level of";

	### manage_create_user_page.php3
	$s_create_new_account_title = "Create New Account";
	### $s_username # defined above
	### $s_email # defined above
	### $s_password # defined above
	$s_verify_password = "Verify Password";
	### $s_access_level # defined above
	$s_enabled = "Enabled";
	$s_protected = "Protected";
	$s_create_user_button = "Create User";

	### manage_project_add.php3
	$s_project_added_msg = "Project has been successfully added...";

	### manage_project_category_add.php3
	$s_category_added_msg = "Category hass been successfully added...";

	### manage_project_category_delete.php3
	$s_category_deleted_msg = "Category has been successfully deleted...";

	### manage_project_category_delete_page.php3
	$s_category_delete_sure_msg = "Are you sure you want to delete this category?";
	$s_delete_category_button = "Delete Category";

	### manage_project_category_edit_page.php3
	$s_edit_project_category_title = "Edit Project Category";
	$s_update_category_button = "Update Category";
	### $s_delete_category_button # defined above

	### manage_project_category_update.php3
	$s_category_updated_msg = "Category has been successfully updated...";

	### manage_page.php3
	$s_create_new_account_link = "Create Account";
	$s_projects_link = "Projects";
	$s_documentation_link = "Documentation";
	$s_new_accounts_title = "New Accounts";
	$s_1_week_title = "1 Week";
	$s_never_logged_in_title = "Never Logged In";
	$s_manage_accounts_title = "Manage Accounts";
	### $s_username # defined above
	### $s_email # defined above
	### $s_access_level # defined above
	### $s_enabled # defined above
	$s_p = "p";
	$s_date_created = "Date Created";
	$s_last_visit = "Last Visit";
	$s_edit_user_link = "Edit User";

	### manage_project_delete.php3
	$s_project_deleted_msg = "Project successfully removed...";

	### manage_project_delete_page.php3
	$s_project_delete_msg = "Are you sure you want to delete this project and all attached bug reports?";
	$s_project_delete_button = "Delete Project";

	### manage_project_edit_page.php3
	$s_edit_project_title = "Edit Project";
	$s_project_name = "Project Name";
	### $s_status # defined above
	### $s_enbaled # defined above
	$s_view_status = "View Status";
	$s_public = "public";
	$s_private = "private";
	### $s_description # defined above;
	$s_update_project_button = "Update Project";
	$s_delete_project_button = "Delete Project";
	$s_categories_and_version_title = "Categories and Versions";
	$s_categories = "Categories";
	$s_add_category_button = "Add Category";
	$s_versions = "Versions";
	$s_add_version_button = "Add Version";
	$s_edit_link = "edit";

	### manage_project_menu_page.php3
	$s_add_project_title = "Add Project";
	### $s_project_name # defined above;
	### $s_status # defined above;
	### $s_view_status # defined above;
	### $s_public # defined above;
	### $s_private # defined above;
	### $s_description # defined above;
	$s_name = "Name";
	### $s_status # defined above;
	### $s_enabed # defined above;
	### $s_view_status # defined above;
	### $s_description # defined above;
	### $s_edit_link # defined above;

	### manage_project_update.php3
	$s_project_updated_msg = "Project has been successfully updated...";

	### manage_project_version_add.php3
	$s_version_added_msg = "Version has been successfully added...";

	### manage_project_version_delete.php3
	$s_version_deleted_msg = "Version has been successfully deleted...";

	### manage_project_version_delete_page.php3
	$s_version_delete_sure = "Are you sure you want to delete this version?";
	$s_delete_version_button = "Delete Version";

	### manage_project_version_edit_page.php3
	$s_edit_project_version_title = "Edit Project Version";
	$s_update_version_button = "Update Version";
	### $s_delete_version_button # defined above;

	### manage_project_version_update.php3
	$s_version_updated_msg = "Version has been successfully updated...";

	### manage_user_delete.php3
	$s_account_delete_protected_msg = "Account protected. Cannot delete this account.";
	$s_account_deleted_msg = "Account deleted...";

	### manage_user_delete_page.php3
	$s_delete_account_sure_msg = "Are you sure you wish to delete this account?";
	### $s_delete_account_button # defined above

	### manage_user_page.php3
	$s_edit_user_title = "Edit User";
	### $s_username # defined above
	### $s_email # defined above
	### $s_access_level # defined above
	### $s_enabled # defined above
	### $s_protected # defined above
	### $s_update_user_button # defined above
	$s_reset_password_button = "Reset Password";
	$s_delete_user_button = "Delete User";
	$s_reset_password_msg = "Reset Password sets the password to be blank.";

	### manage_user_reset.php3
	$s_account_reset_protected_msg = "Account protected. Cannot reset the password.";
	$s_account_reset_msg = "Account password reset...";

	### manage_user_update.php3
	$s_manage_user_protected_msg = "Account protected. Access level and enabled protected. Otherwise, account has been updated...";
	$s_manage_user_updated_msg = "Account successfully updated...";

	### menu_inc.php
	$s_main_link = "Main";
	$s_view_bugs_link = "View Bugs";
	$s_report_bug_link = "Report Bug";
	$s_summary_link = "Summary";
	$s_account_link = "Account";
	$s_manage_link = "Manage";
	$s_edit_news_link = "Edit News";
	$s_docs_link = "Docs";
	$s_logout_link = "Logout";

	### meta_inc.php
	### news_add.php3

	### news_delete.php3
	$s_news_deleted_msg = "Deleted news item...";

	### news_delete_page.php3
	$s_delete_news_sure_msg = "Are you sure you wish to delete this news item?";
	$s_delete_news_item_button = "Delete News Item";

	### news_edit_page.php3
	$s_edit_news_title = "Edit News";
	$s_headline = "Headline";
	$s_body = "Body";
	$s_update_news_button = "Update News";

	### news_menu_page.php3
	$s_add_news_title = "Add News";
	### $s_headline # defined above
	$s_do_not_use = "Do not use";
	### $s_body # defined above
	$s_post_to = "Post to";
	$s_post_news_button = "Post News";
	$s_edit_or_delete_news_title = "Edit or Delete News";
	$s_edit_post = "Edit Post";
	$s_delete_post = "Delete Post";
	$s_select_post = "Select Post";
	### $s_submit_button # defined above

	### news_update.php3
	$s_news_updated_msg = "News item updated...";

	### project_menu_page.php3
	$s_project_selection_title = "Project Selection";
	### $s_projects # defined above
	### $s_select_project_button # defined above

	### report_add.php3
	$s_report_add_error_msg = "ERROR: There was an error in your report.";
	$s_must_enter_category = "You must select a category";
	$s_must_enter_severity = "You must select a severity";
	$s_must_enter_reproducibility = "You must select a reproducibility";
	$s_must_enter_summary = "You must enter a summary";
	$s_must_enter_description = "You must enter a description";
	$s_hit_back_msg = "Please hit back and renter the required fields.";
	$s_submission_thanks_msg = "Thank you for your submission.";

	### report_bug_advanced_page.php3
	$s_simple_report_link = "Simple Report";
	$s_enter_report_details_title = "Enter Report Details";
	### $s_category
	$s_required = "required";
	$s_select_category = "Select Category";
	$s_select_reproducibility = "Select Reproducibility";
	$s_select_severity = "Select Severity";
	### $s_reproducibility
	### $s_severity
	### $s_select_profile # defined above
	$s_or_fill_in = "OR Fill In";
	### $s_platform # defined above
	### $s_os # defined above
	### $s_os_version # defined above
	### $s_product_version # defined above
	### $s_product_build # defined above
	$s_assign_to = "Assign To";
	### $s_summary # defined above
	### $s_description # defined above
	$s_steps_to_reproduce = "Steps To Reproduce";
	$s_additional_information = "Additional Information";
	$s_submit_report_button = "Submit Report";

	### report_bug_page.php3
	$s_advanced_report_link = "Advanced Report";
	### $s_enter_report_details_title # defined above
	### $s_category # defined above
	### $s_required # defined above
	### $s_select_category # defined above
	### $s_select_reproducibility # defined above
	### $s_select_severity # defined above
	### $s_reproducibility
	### $s_severity
	### $s_summary # defined above
	### $s_description # defined above
	### $s_additional_information # defined above
	### $s_submit_report_button # defined above

	### show_source_page.php3
	$s_show_source_for_msg = "Showing source for";
	$s_not_supported_part1 = "This version";
	$s_not_supported_part2 = "of php does not support";

	### signup.php3
	$s_invalid_email = "IS AN INVALID EMAIL ADDRESS";
	$s_duplicate_username = "IS A DUPLICATE USERNAME.  CHOOSE ANOTHER USERNAME";
	$s_account_create_fail = "FAILED TO CREATE USER ACCOUNT";

	### signup_page.php3
	$s_signup_info = "Choose your login name and enter a valid email address.  A randomnly generated address will be send to your address.";
	$s_signup_title = "Signup";
	### $s_username = ""; # defined above
	### $s_email = ""; # defined above
	$s_signup_button = "Signup";

	### summary_page.php3
	$s_summary_title = "Summary";
	$s_by_status = "by status";
	$s_by_date = "by date";
	$s_by_severity = "by severity";
	$s_by_resolution = "by resolution";
	$s_by_category = "by category";
	$s_by_priority = "by priority";
	$s_time_stats = "time stats for resolved bugs(days)";
	$s_longest_open_bug = "longest open bug";
	$s_longest_open = "longest open";
	$s_average_time = "average time";
	$s_total_time = "total time";
	$s_developer_stats = "developer stats (open/resolved/total)";

	### view_bug_advanced_page.php3
	$s_view_simple_link = "View Simple";
	$s_viewing_bug_advanced_details_title = "Viewing Bug Advanced Details";
	### $s_id # defined above
	### $s_category # defined above
	### $s_severity # defined above
	### $s_reproducibility # defined above
	### $s_date_submitted # defined above
	### $s_last_update # defined above
	### $s_reporter # defined above
	### $s_assigned_to # defined above
	### $s_priority # defined above
	### $s_resolution # defined above
	### $s_platform # defined above
	### $s_status # defined above
	### $s_duplicate_id # defined above
	### $s_os # defined above
	### $s_projection # defined above
	### $s_os_version # defined above
	### $s_eta # defined above
	### $s_product_version # defined above
	$s_product_build = "Product Build";
	### $s_votes # defined above
	### $s_summary # defined above
	### $s_description # defined above
	### $s_steps_to # defined above
	### $s_reproduce # defined above
	### $s_additional # defined above
	### $s_information # defined above
	$s_system_profile = "System Description";
	$s_update_bug_button = "Update Bug";
    $s_bug_assign_button    = "Assign to Me";
	### $s_resolve_bug_button # defined above
	### $s_delete_bug_button # defined above
	$s_reopen_bug_button = "Reopen Bug";

	### view_bug_all_page.php3
	$s_all_bugs_link = "All Bugs";
	$s_reported_bugs_link = "Reported Bugs";
	$s_assigned_bugs_link = "Assigned Bugs";
	$s_any = "any";
	$s_show = "Show";
	$s_changed = "Changed(hrs)";
	$s_hide_resolved = "Hide Resolved";
	$s_filter_button = "Filter";
	$s_viewing_bugs_title = "Viewing Bugs";
	### $s_id # defined above
	### $s_category # defined above
	### $s_severity # defined above
	### $s_status # defined above
	$s_updated = "Updated";
	### $s_summary # defined above

	### view_bug_page.php3
	$s_view_advanced_link = "View Advanced";
	### $s_viewing_bug_details_title # defined above
	### $s_id # defined above
	### $s_category # defined above
	### $s_severity # defined above
	### $s_reproducibility # defined above
	### $s_date_submitted # defined above
	### $s_last_update # defined above
	### $s_reporter # defined above
	### $s_assigned_to # defined above
	### $s_priority # defined above
	### $s_resolution # defined above
	### $s_status # defined above
	### $s_duplicate_id # defined above
	### $s_summary # defined above
	### $s_description # defined above
	### $s_additional # defined above
	### $s_information # defined above
	### $s_update_bug_button # defined above
	### $s_bug_assign_button # defined above
	### $s_resolve_bug_button # defined above
	### $s_delete_bug_button # defined above
	### $s_reopen_bug_button # defined above

	### view_user_assigned_bug_page.php3
	### $s_all_bugs_link # defined above
	### $s_reported_bugs_link # defined above
	### $s_any # defined above
	### $s_show # defined above
	### $s_changed # defined above
	### $s_hide_resolved # defined above
	### $s_filter_button # defined above
	### $s_viewing_bugs_title # defined above
	### $s_id # defined above
	### $s_category # defined above
	### $s_severity # defined above
	### $s_status # defined above
	### $s_updated # defined above
	### $s_summary # defined above

	### view_user_reported_bug_page.php3
	### $s_all_bugs_link # defined above
	### $s_assigned_bugs_link # defined above
	### $s_any # defined above
	### $s_show # defined above
	### $s_changed # defined above
	### $s_hide_resolved # defined above
	### $s_filter_button # defined above
	### $s_viewing_bugs_title # defined above
	### $s_id # defined above
	### $s_category # defined above
	### $s_severity # defined above
	### $s_status # defined above
	### $s_updated # defined above
	### $s_summary # defined above
?>