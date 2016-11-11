<?php
/**
 * Displays paginated links for the directory [<< < 1 2 [3] 4 5 > >>].
 */

if( ! defined( 'ABSPATH' ) ) exit;

// Initialize the user directory (populates $eca_users). Will reuse the same result if called multiple times.
$eca_users = eca_directory_users();

$count_total = $eca_users->get_total();
$count_this_page = count($eca_users->get_results());
$count_per_page = eca_get_users_per_page();
$total_pages = ceil( $count_total / $count_per_page ); // Example: 32 users, 10 per page == ceil(32/10) = ceil(3.2) = 4 pages.

$directory_page = (int) get_query_var( "directory_page", 1 );

$query_args = array();

$prev_url = $directory_page > 1 ? add_query_arg( $query_args, eca_get_member_directory_page( $directory_page - 1 ) ) : false;
$next_url = $directory_page < $total_pages ? add_query_arg( $query_args, eca_get_member_directory_page( $directory_page + 1 ) ) : false;

?>
<div class="eca-pagination eca-menu-list-wrapper">
	<ol class="eca-menu-list">

		<li class="eca-menu-list-item eca-page-number eca-page-previous <?php echo $prev_url ? '' : 'no-link'; ?>" title="Previous page">
			<?php
			if ( $prev_url ) echo '<a href="'.esc_attr($prev_url).'">&lt;</a>';
			else echo '<span>&lt;</span>';
			?>
		</li>

		<?php
		for( $i = 1; $i <= $total_pages; $i++ ) {
			$classes = array( 'eca-menu-list-item', 'eca-page-number' );
			if ( $directory_page === $i ) $classes[] = 'current-menu-item';

			$url = add_query_arg( $query_args, eca_get_member_directory_page( $i ) );
			?>
			<li class="<?php echo esc_attr(implode(" ", $classes)); ?>" title="<?php echo sprintf( "Go to page %s of %s", $i, $total_pages ); ?>">
				<a href="<?php echo esc_attr($url); ?>"><?php echo $i; ?></a>
			</li>
			<?php
		}
		?>

		<li class="eca-menu-list-item eca-page-number eca-page-next <?php echo $prev_url ? '' : 'no-link'; ?>" title="Next page">
			<?php
			if ( $next_url ) echo '<a href="'.esc_attr($next_url).'">&gt;</a>';
			else echo '<span>&gt;</span>';
			?>
		</li>
	</ol>
</div>