<?php 
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The WPMediaUploader field uses WordPress' built in Media Upload functionality
 *
 *
 * ## Examples
 *
 * Here's an example on how you create a WPMediaUploader field:
 * ```php
 *```
 */
class WPMediaUploader extends Field {
	
	public function wp_admin_render_field() {
		$file_src = wp_get_attachment_image_src( $this->get_value(), 'medium' );
		$has_image = is_array( $file_src );
		?>
		<div class="field">
			<label class="simplrwp--label"><?php  echo $this->get_label(); ?></label>
			<div class="image-container">
			    <?php if ( $has_image ) : ?>
			        <img src="<?php echo $file_src[0] ?>" alt="" style="max-width:100%;" />
			    <?php endif; ?>
			</div>
	
			<p class="hide-if-no-js">
				<div class="upload-image <?php echo $has_image ? 'hidden' : ''; ?>">
					No file selected.
				    <a class="button" href="<?php echo esc_url( get_upload_iframe_src( 'image' ) ) ?>" >
				        <?php _e('Add Image') ?>
				    </a>
				</div>
			    <a class="delete-image <?php echo !$has_image ? 'hidden' : ''; ?>" href="#" >
			        <?php _e('Remove this image') ?>
			    </a>
			</p>
			<input class="image-id" name="<?php echo $this->get_name(); ?>" type="hidden" value="<?php echo $this->get_value();?>" />
		</div>
		<?php 
	}
	
	public function wp_admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'simplrwp_wp-media-uploader', SIMPLRWP_URL . 'assets/js/fields/WPMediaUploader.js' );
	}

	public function get_img_url($id){
		return wp_get_attachment_image_src( $this->settings['value'], 'medium' )[0];
	}		
}

?>
