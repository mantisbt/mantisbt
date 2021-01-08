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
 * List News
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

news_ensure_enabled();

access_ensure_project_level( config_get( 'view_bug_threshold' ) );

layout_page_header();

layout_page_begin( 'main_page.php' );
?>

<div class="col-md-12 col-xs-12">
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<?php print_icon( 'fa-archive', 'ace-icon' ); ?>
		<?php echo lang_get( 'archives' ) ?>
	</h4>
</div>

<div class="widget-body">
	<div class="widget-main">

<?php
# Select the news posts
$t_rows = news_get_rows( helper_get_current_project() );
$t_count = count( $t_rows );

if( $t_count > 0 ) {
	# Loop through results
	for( $i=0; $i < $t_count; $i++ ) {
		/**
		 * @var int $v_id
		 * @var int $v_project_id
		 * @var int $v_poster_id
		 * @var int $v_view_state
		 * @var int $v_announcement
		 * @var string $v_headline
		 * @var int $v_date_posted
		 */
		extract( $t_rows[$i], EXTR_PREFIX_ALL, 'v' );
		if( VS_PRIVATE == $v_view_state && !access_has_project_level( config_get( 'private_news_threshold' ), $v_project_id ) ) {
			continue;
		}

		$v_headline 	= string_display_line( $v_headline );
		$v_date_posted 	= date( config_get( 'complete_date_format' ), $v_date_posted ); ?>
		<p>
			<?php print_icon( 'fa-clock-o' ); ?>
			<span class="small"> <?php echo $v_date_posted; ?> </span>
			<a href="news_view_page.php?news_id=<?php echo $v_id; ?>"><?php echo $v_headline; ?></a>
			&#160;&#160;&#160;&#160;
		<?php
			print_icon( 'fa-edit' );
			echo ' ' . prepare_user_name( $v_poster_id );
			if( 1 == $v_announcement ) {
				echo lang_get( 'announcement' );
			}
			if( VS_PRIVATE == $v_view_state ) {
		?>
				<span class="label label-info"><?php echo lang_get( 'private' ); ?></span>
		<?php } ?>
		</p>
<?php
	}  	# end for loop
}
?>
	</div>
</div>
</div>
</div>

<?php
layout_page_end();
