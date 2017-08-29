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

require_once( 'core.php' );

if( current_user_is_anonymous() ) {
	access_denied();
}

layout_page_header( lang_get( 'filters' ) );
layout_page_begin();
print_account_menu( 'account_filters_page.php' );

$t_project_id = helper_get_current_project();
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div id="current_filters_reset-div" class="form-container">
		<div class="widget-box widget-color-blue2">

			<div class="widget-body">
				<div class="widget-main">

					<form id="current_filters_reset-form" method="post" action="account_filters_reset.php">
						<h4>
							<?php echo lang_get( 'reset_current_filter_for_project' ) . ': ' . project_get_name( $t_project_id ) ?>
						</h4>
						<?php echo form_security_field( 'reset_current_filter_form' ); ?>
						<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>">
						<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'reset_query' ) ?>" >
					</form>

				</div>
			</div>

		</div>
	</div>
</div>

<?php
layout_page_end();