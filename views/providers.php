<?php 

global $pronamic_pay_providers;

?>
<div class="wrap">
	<?php screen_icon( 'pronamic_ideal' ); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>

	<table class="wp-list-table widefat" cellspacing="0">

		<?php foreach ( array( 'thead', 'tfoot' ) as $tag ) : ?>

			<<?php echo $tag; ?>>
				<tr>
					<th scope="col" class="manage-column"><?php _e( 'Provider', 'pronamic_ideal' ); ?></th>
				</tr>
			</<?php echo $tag; ?>>

		<?php endforeach; ?>

		<tbody>

			<?php foreach ( $pronamic_pay_providers as $provider ) : ?>

				<tr>
					<td>
						<a href="<?php echo $provider['url']; ?>">
							<?php echo $provider['name']; ?>
						</a>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>

	<?php include 'pronamic.php'; ?>
</div>