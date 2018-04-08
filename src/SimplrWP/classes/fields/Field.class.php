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
		'sub_fields' => [],
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
		if(is_array($value) && $this->has_sub_fields()) {
			foreach($value as $sub_field => $sub_value) {
				$this->set_sub_field_value($sub_field, $sub_value);
			}
		} else {
			$this->settings['value'] = $value;
		}
		
	}
	
	public function set_sub_field_value($sub_field, $value) {
		if(array_key_exists($sub_field, $this->get_sub_fields() ) && $this->settings['sub_fields'][$sub_field] instanceof \SimplrWP\Fields\Field) {
			$this->settings['sub_fields'][$sub_field]->set_value($value);
		}
	}
	
	public function has_sub_fields() {
		return !empty($this->get_sub_fields());
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
		if($this->has_sub_fields()) {
			return $this->render_sub_fields();
		} else {
			return $this->settings['render_value']($this->settings['value']);
		}
	}
	
	public function render_sub_fields() {
		return array_map(function($sub_field) {
			return $sub_field->render_value();
		}, $this->get_sub_fields());
	}
	
	public function get_label() {
		return $this->settings['label'];
	}
	
	public function get_sub_fields() {
		return $this->settings['sub_fields'];
	}
	
	public function get_db_config() {
		return $this->settings['db_config'];
	}
	
	public function get_before_save_validations() {
		return is_callable($this->settings['before_save_validations']) ? $this->settings['before_save_validations']($this->get_value()) : $this->settings['before_save_validations'];
	}
	
	public function get_wp_admin_list() {
		return $this->settings['wp_admin_list'];
	}
	
	public function wp_admin_render_field() {
		echo 'THIS CLASS NEEDS TO BE EXTENDED!';
	}
	
	public function wp_admin_enqueue_scripts() {
			
	}
	
	public function prepare_data_for_database($data) {
		return $data;
	}
	
	public function validate($validator, $only_validate_provided_fields = false) {
		$result = $validator->validate([
			$this->get_name() => [
			'value' => $this->get_value(),
			'label' => $this->get_label(),
			'validations' => $this->get_before_save_validations()
		] ], $only_validate_provided_fields);

		// let's validate sub fields
		if($this->has_sub_fields()) {
			@$result['data'][$this->get_name()] = [];
			foreach($this->get_sub_fields() as $sub_field_name => $sub_field) {
				if($sub_field instanceof \SimplrWP\Fields\Field) {
					$sub_field_result = $sub_field->validate($validator, $only_validate_provided_fields);
					if($sub_field_result['valid'] === false) {
						$result['valid'] = false;
					}
					if($sub_field_result['valid'])
						@$result['data'][$this->get_name()][$sub_field_name]= $sub_field_result['data'][$sub_field_name];
					else
						@$result['errors'][$this->get_name()][$sub_field_name]= $sub_field_result['errors'][$sub_field_name];
				}
			}
		}
		return $result;
	}
	
}