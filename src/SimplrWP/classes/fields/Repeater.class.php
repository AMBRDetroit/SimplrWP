<?php
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The Repeater field allows you to create multiple series of other fields,
 *
 *
 * ## Examples
 *
 * Here's an example on how you create a Repeater field:
 * ```php
 *```
 */
class Repeater extends Field {
	
	protected $default_repeater_settings = array(
		'fields' => array(),
		'add_button_label' => 'Add New'
	);
	
	public function __construct($settings){
		// load defaults
		$this->settings = array_replace_recursive($this->settings, $this->default_repeater_settings);
		// pass along all settings to parent
		parent::__construct($settings);
	}

	public function wp_admin_render_field() {
		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
	?>
		<div class="field simplrwp--repeater" id="<?php echo $this->get_name(); ?>" >
			<label class="simplrwp--label"><?php echo $this->get_label() . $required; ?></label>
			<div class="simplrwp--all_instances">
				<?php // let's load the existing values
					if( !($values = @unserialize($this->get_value()) ) ) {
						$values = array();
					}
					$i=0;
					foreach($values as $repeater) { ?>
						<div class="simplrwp--repeater_field">
							<?php
							foreach($this->get_fields() as $field) {
								ob_start();
								
								$current_value = array_key_exists($field->get_name(), $repeater) ? $repeater[$field->get_name()] : '';
								$field->set_value($current_value);
								$field->wp_admin_render_field();
								$html = ob_get_contents();
								
								ob_end_clean();
								echo $this->_prepare_repeater_field($html, $i);
							} ?>
							<div class="field">
								<input type="button" class="button button-secondary js-remove-field" value="Remove" />
							</div>
							<div class="clear"></div>
						</div>
						<?php 
						$i++;
					} // end foreach
				?>
			</div>						
			<input type="button" class="button button-primary simplrwp--repeater_add_new js-repeat-field" value="<?php echo $this->get_add_button_label(); ?>" data-name="<?php echo $this->get_name(); ?>" />
			<div class="clear"></div>
		</div>	
		
		<script id="template-field-<?php echo $this->get_name(); ?>" type="text/x-simplr-template">
			<div class="simplrwp--repeater_field">
				<?php 
					foreach($this->settings['fields'] as $field) {
						ob_start();
						
						$field->set_value('');
						$field->wp_admin_render_field();
						$html = ob_get_contents();
						
						ob_end_clean();
						echo $this->_template_repeater_field($html);
					} 
				?>
				<div class="field">
					<input type="button" class="button button-secondary js-remove-field" value="Remove" />
				</div>
				<div class="clear"></div>
			</div>
		</script>
	<?php
	}
	
	public function get_fields() {
		return $this->settings['fields'];
	}
	
	public function get_add_button_label() {
		return $this->settings['add_button_label'];
	}
	
	public function wp_admin_enqueue_scripts() {
		// load styles
		wp_enqueue_style( 'simplrwp_wp-repeater', SIMPLRWP_URL . 'assets/css/simplrwp-repeater.css' );
		// load scripts
		wp_enqueue_script( 'simplrjs', SIMPLRWP_URL . 'assets/js/third_party/simplr.min.js' ,array(),AMBR_VERSION,true);
		wp_enqueue_script( 'simplrwp_wp-repeater', SIMPLRWP_URL . 'assets/js/fields/Repeater.js' ,array(),AMBR_VERSION,true);
	}
	
	protected function _template_repeater_field($html = '') {
		return preg_replace('/name=\"([\w-_\[\]]*)\"/i', 'name="' . $this->get_name() . '[$[instance]][$1]" data-fieldname="$1"', $html);
	}
	
	protected function _prepare_repeater_field($html = '', $i = 0) {
		return preg_replace('/name=\"([\w-_\[\]]*)\"/i', 'name="' . $this->get_name() . '[' . $i . '][$1]" data-fieldname="$1"', $html);
	}
}
?>
