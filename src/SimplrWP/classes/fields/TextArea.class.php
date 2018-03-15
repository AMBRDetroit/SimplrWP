<?php
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The TextArea field is multiline text area field
 *
 *
 * ## Examples
 *
 * Here's an example on how you create a TextArea field:
 * ```php
 *```
 */
class TextArea extends Field {
	
	public function wp_admin_render_field() {
		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . $required . '</label>';
			echo '<textarea class="textarea" name="' . $this->get_name() . '">'. stripslashes($this->get_value()) .'</textarea>';
		echo '</div>';
	}
	
	public function get_value() {
		if(is_string($this->settings['value'])) {
			return stripslashes(htmlentities($this->settings['value']));
		}
		return $this->settings['value'];
	}
	
	public function set_value($value) {
		$this->settings['value'] = html_entity_decode($value);
	}
}