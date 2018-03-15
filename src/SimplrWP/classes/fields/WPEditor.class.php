<?php 
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The WP Editor extends the Text field, but also leverages the wp_editor built-in functionality.
 *
 *
 * ## Examples
 *
 * Here's an example on how you create a WPEditor field:
 * ```php
 *```
 */
class WPEditor extends Field {
	
	protected $default_wpeditor_settings = array(
		'content' => '',
		'editor_id' => '',
		'wpeditor_settings' => array('media_buttons' => false)
	);
	
	public function __construct($settings){
		// load defaults
		$this->settings = array_replace_recursive($this->settings, $this->default_wpeditor_settings);
		// pass along all settings to parent
		parent::__construct($settings);
	}
	
	public function wp_admin_render_field() {
		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . $required . '</label>';
			$this->settings['content'] = stripslashes($this->get_value());
			$this->settings['editor_id'] = $this->get_name();
			wp_editor(   $this->settings['content'], $this->settings['editor_id'] , $this->settings['wpeditor_settings'] );
		echo '</div>';
	}
	
	public function render_wp_editor_field($uniqid = false) {
		$this->settings['content'] = stripslashes($this->get_value());
		$this->settings['editor_id'] = $this->get_name() . ($uniqid ? ('-' . $uniqid): '');
		wp_editor(   $this->settings['content'], $this->settings['editor_id'] , $this->settings['wpeditor_settings'] );
	}
	
	public function get_value() {
		if(is_string($this->settings['value'])) {
			return stripslashes($this->settings['value']);
		}
		return $this->settings['value'];
	}
	
	public function render_value() {
		return $this->get_value();
	}
	
	public function wp_admin_enqueue_scripts() {
		// load styles
		wp_enqueue_style( 'simplrwp_wp-media-uploader', SIMPLRWP_URL . 'assets/css/simplrwp-wpeditor.css' );
	}
}