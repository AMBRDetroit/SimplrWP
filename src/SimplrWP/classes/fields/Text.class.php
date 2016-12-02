<?php 
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The Text field is a single line text input.
 *
 *
 * ## Examples
 *
 * Here's an example on how you create a text field:
 * ```php
 *```
 */
class Text extends Field {
	
	public function wp_admin_render_field() {
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . '</label>';
			if($this->settings['read_only']){
				echo '<p>' . $this->render_value() . '</p>';
			}else{
				echo '<input class="large-text" name="' . $this->get_name() . '" type="text" value="' . $this->get_value() . '">';
			}
		echo '</div>';
	}
}

?>