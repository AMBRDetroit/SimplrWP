<?php
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The Select field extends the Text class, and has some additional configurations.
 * 
 *
 * ## Examples
 *
 * Here's an example on how you create a select field:
 * ```php
 *```
 */
class Select extends Field {
	
	protected $default_select_settings = array(
		'selectable_options' => array()
	);
		
	public function __construct($settings){
		// load defaults
		$this->settings = array_replace_recursive($this->settings, $this->default_select_settings);
		// pass along all settings to parent
		parent::__construct($settings);
	}

	public function wp_admin_render_field() {
		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . $required . '</label>';
			
			echo '<select name="'.$this->get_name().'" >';
				foreach($this->settings['selectable_options'] as $key => $value){
					$isSelected = $key == $this->get_value() ? 'selected' : '';
					echo '<option value="'.$key.'"  '.$isSelected.'>'.$value.'</option>'; 
				}
			echo '</select>';
		echo '</div>';
	}
	
	public function get_selectable_options() {
		return $this->settings['selectable_options'];
	}
}
?>