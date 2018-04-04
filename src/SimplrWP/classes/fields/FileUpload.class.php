<?php 
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The FileUpload field is a customizable file uplaod field.
 *
 * ## Examples
 *
 * Here's an example on how you create a file upload field:
 * ```php
 *```
 */
class FileUpload extends Field {
	public $file_name, $this_product_dir;
	protected $default_file_upload_settings = array(
		'upload_dir' => array(
			'basedir' => WP_CONTENT_DIR,
			'baseurl' => WP_CONTENT_URL,
			'subdir' => '/wdi_product_pdfs',
			'path' => '',
			'url' => ''
		)
 	);
	
	public function __construct($options){
		// set default path and url for uploads
		$this->default_file_upload_settings['path'] = WP_CONTENT_DIR . '/uploads';
		$this->default_file_upload_settings['url'] = WP_CONTENT_URL . '/uploads';

		// load defaults
		$this->settings = array_replace_recursive($this->settings, $this->default_file_upload_settings);
		// pass along all options to parent
		parent::__construct($options);
	}
	
	public function wp_admin_render_field() {
		wp_nonce_field(plugin_basename(__FILE__), 'simplrwp_file_upload_nonce');

		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
	?>
		<div class="field">
			<label class="simplrwp--label"><?php echo $this->get_label() . $required; ?></label>
			<input type="file" name="<?php echo $this->get_label(); ?>"  value=""/>
			<button type="submit" name="<?php echo $this->get_name() . '_submit'; ?>">Upload</button> 

			<div id="simplrwp_files"></div>
		</div>
	<?php 	
	}
		
	
	public function wp_admin_enqueue_scripts() {
		wp_enqueue_script('simplrwp_wp-file-upload', SIMPLRWP_URL . 'assets/js/fields/FileUpload.js');
	}
	
	public function get_upload_dir($dir){
		$dir['basedir'] = $this->settings['upload_dir']['basedir'];
		$dir['baseurl'] = $this->settings['upload_dir']['baseurl'];
		$dir['subdir'] = $this->settings['upload_dir']['subdir'];
		$dir['path'] = $this->settings['upload_dir']['path'] . $this->settings['upload_dir']['subdir'] .$this->this_product_dir;
		$dir['url'] =  $this->settings['upload_dir']['url']  .  $this->settings['upload_dir']['subdir'] . $this->this_product_dir; 
		
		return $dir;
	}
	
	public function get_this_product_dir(){
		return $this->this_product_dir;
	}
	
	public function upload_file($file,$nounce,$post_id){
		if(isset($nounce)){
			//verify this came from our screen and with proper authorization.
			if (!wp_verify_nonce( $nounce,  plugin_basename(__FILE__) )) {
 				return array('error' => __('Permission denied'));
				//return new WP_ERROR('wdi_file_upload_error',__('Permission denied'),'wdi_file_upload_error');
			}else{
				// verified, continue
				if(isset($file) && $file['size'] > 0){
										
					// Get the type of the uploaded file. This is returned as "type/extension"
					$arr_file_type = wp_check_filetype(basename($file['name']));
					$uploaded_file_type = $arr_file_type['type'];
												
					// Set an array containing a list of acceptable formats
					$allowed_file_types = array('application/pdf');
				
					// If the uploaded file is the right format
					if(in_array($uploaded_file_type, $allowed_file_types)) {
						// set file name to be used to create special directory
						$this->file_name = 'master_'.$file['name'];
						$this->this_product_dir = '/product_' . $post_id . '_files';
						$file['name'] = $this->file_name;
						// Register our path override.
						add_filter( 'upload_dir', array($this, 'get_upload_dir'));
					
						// Options array for the wp_handle_upload function. 'test_upload' => false
						$upload_overrides = array( 'test_form' => false );
						// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
						$uploaded_file = wp_handle_upload($file, $upload_overrides);
						// If the wp_handle_upload call returned a local path for the image
					}
					// Set everything back to normal.
					remove_filter( 'upload_dir',  array($this, 'get_upload_dir') );
				}
				// returns path and url of file
				return $uploaded_file;
			}
			
		}
			
	}

}

?>