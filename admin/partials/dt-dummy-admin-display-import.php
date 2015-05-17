<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    DT_Dummy
 * @subpackage DT_Dummy/admin/partials
 */

// $checkbox_status = ' checked="checked"';
$checkbox_status = '';

$dummy_content = new DT_Dummy_Content( $this->get_dummy_list(), $this->theme_name );
?>
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php foreach( $dummy_content->get_content_parts_ids() as $content_part_id ) : ?>

		<?php

		$dummy_info = $dummy_content->get_content_info( $content_part_id );
		?>

		<div class="dt-dummy-content">

			<?php if ( $dummy_info->get( 'title' ) ) : ?>

				<h3><?php echo esc_html( $dummy_info->get( 'title' ) ); ?></h3>

			<?php endif; ?>

			<div class="dt-dummy-import-item">

				<?php if ( $dummy_info->get( 'screenshot' ) ) : ?>

					<?php

					$screenshot = $dummy_info->get( 'screenshot' );

					?>
					<div class="dt-dummy-screenshot">
						<img src="<?php echo esc_url( $this->images_url . $screenshot['src'] ); ?>" width="<?php echo esc_attr( $screenshot['width'] ); ?>" height="<?php echo esc_attr( $screenshot['height'] ); ?>" alt="" />
					</div>

				<?php endif; ?>

				<div class="dt-dummy-controls" data-dt-dummy-content-part-id="<?php echo esc_attr( $content_part_id ); ?>">

					<?php
					/* Dummy info content */
					$top_content = $dummy_info->get( 'top_content' );

					if ( $top_content ) :
					?>

						<div class="dt-dummy-controls-block dt-dummy-info-content">

							<?php echo $top_content; ?>

						</div>

					<?php endif; ?>

					<?php

					$main_content = $dummy_content->get_main_content( $content_part_id );

					?>

					<?php if ( ! $main_content->is_empty() ) : ?>

						<div class="dt-dummy-controls-block">
							<h4><?php _e( 'Main content:', $this->plugin_name ); ?></h4>

							<?php foreach ( $main_content->as_array() as $dummy_id=>$dummy ) : ?>

								<label><input type="checkbox" name="<?php echo esc_attr( $dummy_id ); ?>"<?php echo $checkbox_status; ?> value="1" /><?php echo dt_dummy_get_content_nice_name( $dummy_id, $dummy ); ?></label>

							<?php endforeach; ?>

						</div>

					<?php endif; ?>

					<?php

					$wc_content = $dummy_content->get_wc_content( $content_part_id );

					?>

					<?php if ( $this->wc_is_enabled() && ! $wc_content->is_empty() ) : ?>

						<div class="dt-dummy-controls-block">
							<h4><?php _e( 'Woocommerce content:', $this->plugin_name ); ?></h4>

							<?php foreach ( $wc_content->as_array() as $dummy_id=>$dummy ) : ?>

								<label><input type="checkbox" name="<?php echo esc_attr( $dummy_id ); ?>"<?php echo $checkbox_status; ?> value="1" /><?php echo dt_dummy_get_content_nice_name( $dummy_id, $dummy ); ?></label>

							<?php endforeach; ?>

						</div>

					<?php endif; ?>

					<div class="dt-dummy-controls-block">
						<h4><?php _e( 'Assign posts to an existing user:', $this->plugin_name ); ?></h4>

						<?php wp_dropdown_users( array(
							'class' => 'dt-dummy-content-user',
							'id' => 'dt-dummy-content-user-' . $content_part_id,
							'multi' => true,
							'show_option_all' => __( '- Select -', $this->plugin_name )
						) ); ?>

					</div>

					<div class="dt-dummy-controls-block dt-dummy-control-buttons">
						<div class="dt-dummy-button-wrap">
							<a href="#" class="button button-primary dt-dummy-button-import"><?php _e( 'Import content', $this->plugin_name ); ?></a><span class="spinner"></span>
						</div>
					</div>

					<?php
					/* Dummy info content */
					$bottom_content = $dummy_info->get( 'bottom_content' );

					if ( $bottom_content ) :
					?>

						<div class="dt-dummy-controls-block dt-dummy-info-content">

							<?php echo $bottom_content; ?>

						</div>

					<?php endif; ?>

				</div>

			</div>

		</div>

	<?php endforeach; ?>

</div>