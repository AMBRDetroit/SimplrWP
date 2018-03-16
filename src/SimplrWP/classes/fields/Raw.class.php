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
class Raw extends Field {
	
	public function wp_admin_render_field() {
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . '</label>';			
			echo '<p>' . $this->render_value() . '</p>';
		echo '</div>';
	}
	
}