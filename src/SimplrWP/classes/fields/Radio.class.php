<?php
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The Radio field extends the Text class, but is very much identical to the Select field..
 *
 * ## Examples
 *
 * Here's an example on how you create a radio field:
 * ```php
 *```
 */
class Radio extends Field {
	
	protected $default_radio_settings = array(
		'selectable_options' => array()
	);
		
	public function __construct($settings){
		// load defaults
		$this->settings = array_replace_recursive($this->settings, $this->default_radio_settings);
		// pass along all settings to parent
		parent::__construct($settings);
	}

	public function wp_admin_render_field() {
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . '</label>';
			echo '<ul>';
				foreach($this->settings['selectable_options'] as $key => $value){
					$isSelected = ($key == $this->get_value()) ? 'checked' : '';
					echo '<li class="option">';
						echo '<input id="radio-' . $key . '-' . $this->get_name() . '" type="radio" name="' . $this->get_name() . '" value="' . $key . '"  ' .$isSelected . '>';
						echo '<label for="radio-' . $key . '-' . $this->get_name() . '">' . $value . '</label>';
					echo '</li>';
				}
			echo '</ul>';
		echo '</div>';
	}
}
?>