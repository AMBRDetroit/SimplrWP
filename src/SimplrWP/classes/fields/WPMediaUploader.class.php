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
	
	// setup field
	public function __construct($options =  array()) {
		$options += [
			'restrict_mime_types' => false		
		];
		
		if(!empty($options['restrict_mime_types'])) {
			//add_filter( 'upload_mimes', array($this, 'restrict_mime_types') );
		
			//add_action( 'post-upload-ui', array($this, 'restrict_mime_types_hint') );
		}
		parent::__construct($options);
	}
	
	public function wp_admin_render_field() {
		$is_image = in_array(get_post_mime_type($this->get_value()), array('image/png', 'image/jpg', 'image/jpeg', 'image/gif'));
		$has_file = !empty($this->get_value());
		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
		?>
		<div class="field simplrwp--media_uploader">
			<label class="simplrwp--label"><?php  echo $this->get_label() . $required; ?></label>
			<?php if($this->settings['read_only']){ 
				echo $this->render_field();
			} else { ?>
				<div class="image-container">
					<?php if($is_image) { 
						$img_src = wp_get_attachment_image_src( $this->get_value(), 'medium' );
					?>
						<img src="<?php echo $img_src[0] ?>" alt="" style="max-width:100%;" />
					<?php } ?>
				</div>
				<div class="file-container">
					<?php if($has_file && !$is_image) { 
						$file_url = wp_get_attachment_url($this->get_value());
						$file_parts = explode('/', $file_url);
					?>
						<img class="file-image" src="<?php echo SIMPLRWP_URL . 'assets/images/document.png'; ?>" />
						<ul>
							<li><strong><span class="file-title"><?php echo get_the_title($this->get_value()); ?></span></strong></li>
							<li><strong>File Name:</strong> <span class="file-name"><?php echo sprintf('<a href="%s" target="_blank">%s</a>',$file_url, array_pop($file_parts)); ?></span></li>
							<li><strong>File Size:</strong> <span class="file-size"><?php echo $this->_get_file_size(filesize(get_attached_file($this->get_value()))); ?></span></li>
						</ul>
					<?php } ?>
				</div>
				<p class="hide-if-no-js">
					<div class="upload-media <?php echo $has_file ? 'hidden' : ''; ?>">
						No file selected.
					    <a class="button" href="<?php echo esc_url( get_upload_iframe_src( 'pdf' ) ) ?>" >
					        <?php _e('Add Media') ?>
					    </a>
					</div>
				    <a class="delete-media <?php echo !$has_file ? 'hidden' : ''; ?>" href="#" >
				        <?php _e('Remove this media') ?>
				    </a>
				</p>
				<input class="media-id" name="<?php echo $this->get_name(); ?>" type="hidden" value="<?php echo $this->get_value();?>" />
			<?php } ?>
		</div>
		<?php 
	}
	
	public function wp_admin_enqueue_scripts() {
		wp_enqueue_media();
		// load styles
		wp_enqueue_style( 'simplrwp_wp-media-uploader', SIMPLRWP_URL . 'assets/css/simplrwp-media_uploader.css' );
		
		wp_enqueue_script( 'simplrwp_wp-media-uploader', SIMPLRWP_URL . 'assets/js/fields/WPMediaUploader.js' );
		
		$js_options = array(
			'simplrwp_url' => SIMPLRWP_URL,
			'restrict_mime_types' => []
		);
		
		if(!empty($this->settings['restrict_mime_types'])) {
			$js_options['restrict_mime_types'][$this->get_name()] = is_array($this->settings['restrict_mime_types']) ? array_values($this->settings['restrict_mime_types']) : [];
		}
		wp_localize_script( 'simplrwp_wp-media-uploader', 'simplrwp_media_uploader',  $js_options);
	}

	public function get_img_url($size = 'medium'){
		return wp_get_attachment_image_src( $this->settings['value'], $size )[0];
	}
	
	protected function _get_file_size($bytes, $precision = 0) { 
	    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
	
	    $bytes = max($bytes, 0); 
	    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	    $pow = min($pow, count($units) - 1); 
	
	    $bytes /= pow(1024, $pow);
	    // $bytes /= (1 << (10 * $pow)); 
	
	    return round($bytes, $precision) . ' ' . $units[$pow]; 
	} 
	
	public function restrict_mime_types( $mime_types ) {
		return $this->settings['restrict_mime_types'];
	} 
	
	public function restrict_mime_types_hint() {
		echo '<br />';
		_e( 'Accepted MIME types: ' . implode(', ', array_values($this->settings['restrict_mime_types'])) );
	}
}

?>
