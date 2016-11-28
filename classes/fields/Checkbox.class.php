<?php
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The Checkbox field extends the Text class, but is very much identical to the Radio field..
 *
 * ## Examples
 *
 * Here's an example on how you create a checkbox field:
 * ```php
 *```
 */
class Checkbox extends Field {
	
	protected $default_checkbox_settings = array(
		'selectable_options' => array()
	);
		
	public function __construct($settings){
		// load defaults
		$this->settings = array_replace_recursive($this->settings, $this->default_checkbox_settings);
		// pass along all settings to parent
		parent::__construct($settings);
	}

	public function wp_admin_render_field() {
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . '</label>';
			if(is_string($this->get_value()))
				$choices = unserialize($this->get_value());
			else
				$choices = array();
			
			echo '<ul>';
				foreach($this->settings['selectable_options'] as $key => $value){
					$isSelected = in_array($key,$choices) ? 'checked' : '';
					echo '<li class="option">';
						echo '<input id="checkbox-' . $key . '-' . $this->get_name() . '" type="checkbox" name="'.$this->get_name().'[]" value="'.$key.'"  '.$isSelected.'>';
						echo '<label for="checkbox-' . $key . '-' . $this->get_name() . '">' . $value . '</label>';
					echo '</li>';
				}
			echo '</ul>';
		echo '</div>';
	}
}
?>