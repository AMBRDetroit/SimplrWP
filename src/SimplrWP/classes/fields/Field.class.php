<?php 
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The Field is the base class for the rest of the fields.
 *
 *
 * ## Examples
 * ```php
 *``` 
 */
abstract class Field {
	
	// default settings
	protected $settings = array(
		'db_config' => 'text,',
		'label' => 'New Field',
		'read_only' => false,
		'wp_admin_list' => array(
			'sortable' => false,
			'hidden' => false,
		),
		'before_save_validations' => array('not_empty'),
		'value' => '',
		'name' => '',
		'render_value' => false
	);
	
	
	// setup field
	public function __construct($options =  array()) {
		// set default get value function
		$this->settings['render_value'] = function($value) { return $value; };
		// don't merge validations, replace
		if(isset($options['before_save_validations']))
			$this->settings['before_save_validations'] = $options['before_save_validations'];
		//update field settings
		$this->settings = array_replace_recursive($this->settings, $options);
		
		//enqueue wp_admin_scripts
		add_action( 'admin_enqueue_scripts', array(&$this, 'wp_admin_enqueue_scripts') );
	}
	
	public function is_required() {
		return in_array('not_empty', $this->settings['before_save_validations']);
	}
	
	public function set_value($value) {
		$this->settings['value'] = $value;
	}
	
	public function get_name() {
		return $this->settings['name'];
	}
	
	public function get_value() {
		return $this->settings['value'];
	}
	
	public function prepare_db_value($value) {
		return $value;
	}
	
	public function unprepare_db_value($value) {
		$result = @unserialize($value);
		$this->set_value($result===false ? $value : $result);
	}
	
	public function render_value() {
		return $this->settings['render_value']($this->settings['value']);
	}
	
	public function get_label() {
		return $this->settings['label'];
	}
	
	public function get_db_config() {
		return $this->settings['db_config'];
	}
	
	public function get_before_save_validations() {
		return $this->settings['before_save_validations'];
	}
	
	public function get_wp_admin_list() {
		return $this->settings['wp_admin_list'];
	}
	
	public function wp_admin_render_field() {
		echo 'THIS CLASS NEEDS TO BE EXTENDED!';
	}
	
	public function wp_admin_enqueue_scripts() {
			
	}
	
	public function render_field() {
		return $this->settings['value'];
	}
	
	public function prepare_data_for_database($data) {
		return $data;
	}
	
}

?>