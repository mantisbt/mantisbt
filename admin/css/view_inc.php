<!-- Example file to see changes -->

		<!-- Menu -->

		<table class="width100" cellspacing="0">
		<tr>
			<td class="menu">
				<a href="">Main</a> |
				<a href="">View Bugs</a> |
				<a href="">Report Bug</a> |
				<a href="">Summary</a> |
				<a href="">Forums</a> |
				<a href="">Account</a> |
				<a href="">Users</a> |
				<a href="">Manage</a> |
				<a href="">Edit News</a> |
				<a href="">Documentation</a> |
				<a href="">Logout</a>
			</td>
			<td class="right">
				<input class="small" type="input" name="f_id" size="6"><input class="small" type="submit" value="Jump">
			</td>
		</tr>
		</table>

		<!-- News -->

		<br />
		<div align="center">
		<table class="width75" cellspacing="0">
		<tr>
			<td class="news-heading">
				<span class="bold">4.1 Released</span> -
				<span class="italic-small">09-06 11:15</span> -
				<a class="small" href="">prescience</a>
			</td>
		</tr>
		<tr>
			<td class="news-body">
				News Text
			</td>
		</tr>
		</table>
		</div>

		<!-- Form Item -->

		<br />
		<div align="center">
		<table class="width75" cellspacing="1">
		<tr>
			<td class="form-title">
				Edit Account
			</td>
			<td class="right">
				[ Account ][ <a href="">Change Preferences</a> ][ <a href="">Manage Profiles</a> ]
			</td>
		</tr>
		<tr class="row-1">
			<td class="category" width="25%">
				Username:
			</td>
			<td width="75%">
				<input type="text" size="16" maxlength="32" name="f_username" value="prescience">
			</td>
		</tr>
		<tr class="row-2">
			<td class="category">
				Password:
			</td>
			<td>
				<input type="password" size="32" maxlength="32" name="f_password">
			</td>
		</tr>
		<tr class="row-1">
			<td class="category">
				Confirm Password:
			</td>
			<td>
				<input type="password" size="32" maxlength="32" name="f_password_confirm">
			</td>
		</tr>
		<tr class="row-2">
			<td class="category">
			    Email:
			</td>
			<td>
			    <input type="text" size="32" maxlength="64" name="f_email" value="prescience@nowhere.com">
			</td>
		</tr>
		<tr class="row-1">
			<td class="category">
				Access Level:
			</td>
			<td>
				Administrator
			</td>
		</tr>
		<tr>
			<td class="left">
				<input type="submit" value="Update User">
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		</table>
		</div>

		<!-- View All Item -->

		<br />
		<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="8">
				Viewing Bugs		(1 - 50 / 335)
				<span class="small">[ <a target=_top href="">Print Reports</a> ]</span>
			</td>
			<td class="right">
				[
				1&nbsp;<a target=_top href="">2</a>&nbsp;<a target=_top href="">3</a>&nbsp;		]
			</td>
		</tr>
		<tr class="row-category">
			<td class="center" width="2%">
				&nbsp;
			</td>
			<td class="center" width="5%">
				<a target=_top href="">P</a>
			</td>
			<td class="center" width="8%">
				<a target=_top href="">ID</a>
			</td>
			<td class="center" width="3%">
				#
			</td>
			<td class="center" width="12%">
				<a target=_top href="">Category</a>
			</td>
			<td class="center" width="10%">
				<a target=_top href="">Severity</a>
			</td>
			<td class="center" width="10%">
				<a target=_top href="">Status</a>
			</td>
			<td class="center" width="12%">
				<a target=_top href="">Updated</a>
			</td>
			<td class="center" width="38%">
				<a target=_top href="">Summary</a>
			</td>
		</tr>
		<tr>
			<td class="spacer" colspan="9">
				&nbsp;
			</td>
		</tr>
		<tr>
			<td bgcolor="<?php echo $g_new_color ?>">
				<input type="checkbox" name="f_bug_arr[]" value="0002500">
			</td>
			<td class="center" bgcolor="<?php echo $g_new_color ?>">
			</td>
			<td class="center" bgcolor="<?php echo $g_new_color ?>">
				<a target=_top href="">0002500</a>
			</td>
			<td class="center" bgcolor="<?php echo $g_new_color ?>">
				&nbsp;
			</td>
			<td class="center" bgcolor="<?php echo $g_new_color ?>">
				bugtracker
			</td>
			<td class="center" bgcolor="<?php echo $g_new_color ?>">
				minor
			</td>
			<td class="center" bgcolor="<?php echo $g_new_color ?>">
				new
			</td>
			<td class="center" bgcolor="<?php echo $g_new_color ?>">
				09-10
			</td>
			<td class="left" bgcolor="<?php echo $g_new_color ?>">
				There is no response if file uploading fails
			</td>
		</tr>
		<tr>
			<td bgcolor="<?php echo $g_feedback_color ?>">
				<input type="checkbox" name="f_bug_arr[]" value="0002493">
			</td>
				<td class="center" bgcolor="<?php echo $g_feedback_color ?>">
			</td>
			<td class="center" bgcolor="<?php echo $g_feedback_color ?>">
				<a target=_top href="">0002493</a>
			</td>
			<td class="center" bgcolor="<?php echo $g_feedback_color ?>">
				4	</td>
			<td class="center" bgcolor="<?php echo $g_feedback_color ?>">
				bugtracker	</td>
			<td class="center" bgcolor="<?php echo $g_feedback_color ?>">
				<span class="bold">major</span>
			</td>
			<td class="center" bgcolor="<?php echo $g_feedback_color ?>">
				feedback	</td>
			<td class="center" bgcolor="<?php echo $g_feedback_color ?>">
				09-10	</td>
			<td class="left" bgcolor="<?php echo $g_feedback_color ?>">
				I am not able to report a bug
			</td>
		</tr>
		<tr>
			<td bgcolor="<?php echo $g_acknowledged_color ?>">
				<input type="checkbox" name="f_bug_arr[]" value="0002416">
			</td>
			<td class="center" bgcolor="<?php echo $g_acknowledged_color ?>">
			</td>
			<td class="center" bgcolor="<?php echo $g_acknowledged_color ?>">
				<a target=_top href="">0002416</a>
			</td>
			<td class="center" bgcolor="<?php echo $g_acknowledged_color ?>">
				4
			</td>
			<td class="center" bgcolor="<?php echo $g_acknowledged_color ?>">
				security
			</td>
			<td class="center" bgcolor="<?php echo $g_acknowledged_color ?>">
				feature
			</td>
			<td class="center" bgcolor="<?php echo $g_acknowledged_color ?>">
				acknowledged
			</td>
			<td class="center" bgcolor="<?php echo $g_acknowledged_color ?>">
				09-10
			</td>
			<td class="left" bgcolor="<?php echo $g_acknowledged_color ?>">
				Block Denied Access
			</td>
		</tr>
		<tr>
			<td bgcolor="<?php echo $g_confirmed_color ?>">
				<input type="checkbox" name="f_bug_arr[]" value="0002177">
			</td>
			<td class="center" bgcolor="<?php echo $g_confirmed_color ?>">
			</td>
			<td class="center" bgcolor="<?php echo $g_confirmed_color ?>">
				<a target=_top href="">0002177</a>
			</td>
			<td class="center" bgcolor="<?php echo $g_confirmed_color ?>">
				4
			</td>
			<td class="center" bgcolor="<?php echo $g_confirmed_color ?>">
				email
			</td>
			<td class="center" bgcolor="<?php echo $g_confirmed_color ?>">
				feature
			</td>
			<td class="center" bgcolor="<?php echo $g_confirmed_color ?>">
				new
			</td>
			<td class="center" bgcolor="<?php echo $g_confirmed_color ?>">
				09-06
			</td>
			<td class="left" bgcolor="<?php echo $g_confirmed_color ?>">
				Replying to an Email should make a comment in the bugnotes.
			</td>
		</tr>
		<tr>
			<td bgcolor="<?php echo $g_assigned_color ?>">
				<input type="checkbox" name="f_bug_arr[]" value="0002478">
			</td>
			<td class="center" bgcolor="<?php echo $g_assigned_color ?>">
			</td>
			<td class="center" bgcolor="<?php echo $g_assigned_color ?>">
				<a target=_top href="">0002478</a>
			</td>
			<td class="center" bgcolor="<?php echo $g_assigned_color ?>">
				&nbsp;
			</td>
			<td class="center" bgcolor="<?php echo $g_assigned_color ?>">
				bugtracker
			</td>
			<td class="center" bgcolor="<?php echo $g_assigned_color ?>">
				feature
			</td>
			<td class="center" bgcolor="<?php echo $g_assigned_color ?>">
				assigned
			</td>
			<td class="center" bgcolor="<?php echo $g_assigned_color ?>">
				09-05
			</td>
			<td class="left" bgcolor="<?php echo $g_assigned_color ?>">
				upload_file_threshold
			</td>
		</tr>
		<tr>
			<td bgcolor="<?php echo $g_resolved_color ?>">
				<input type="checkbox" name="f_bug_arr[]" value="0001158">
			</td>
			<td class="center" bgcolor="<?php echo $g_resolved_color ?>">
			</td>
			<td class="center" bgcolor="<?php echo $g_resolved_color ?>">
				<a target=_top href="">0001158</a>
			</td>
			<td class="center" bgcolor="<?php echo $g_resolved_color ?>">
				5
			</td>
			<td class="center" bgcolor="<?php echo $g_resolved_color ?>">
				bugtracker
			</td>
			<td class="center" bgcolor="<?php echo $g_resolved_color ?>">
				minor
			</td>
			<td class="center" bgcolor="<?php echo $g_resolved_color ?>">
				(prescience)
			</td>
			<td class="center" bgcolor="<?php echo $g_resolved_color ?>">
				09-09
			</td>
			<td class="left" bgcolor="<?php echo $g_resolved_color ?>">
				Manager should be able to manage categories/version etc.
			</td>
		</tr>
		<tr>
			<td bgcolor="<?php echo $g_closed_color ?>">
				<input type="checkbox" name="f_bug_arr[]" value="0002481">
			</td>
			<td class="center" bgcolor="<?php echo $g_closed_color ?>">
			</td>
			<td class="center" bgcolor="<?php echo $g_closed_color ?>">
				<a target=_top href="">0002481</a>
			</td>
			<td class="center" bgcolor="<?php echo $g_closed_color ?>">
				&nbsp;
			</td>
			<td class="center" bgcolor="<?php echo $g_closed_color ?>">
				bugtracker
			</td>
			<td class="center" bgcolor="<?php echo $g_closed_color ?>">
				trivial
			</td>
			<td class="center" bgcolor="<?php echo $g_closed_color ?>">
				closed
			</td>
			<td class="center" bgcolor="<?php echo $g_closed_color ?>">
				09-05
			</td>
			<td class="left" bgcolor="<?php echo $g_closed_color ?>">
				Print Summary Doesn't Provide 'Page Links'
			</td>
		</tr>
		</table>