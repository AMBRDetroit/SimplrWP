<?php 
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The SimplrWPObject field is an association to a different SimplrWP Object in the system
 *
 *
 * ## Examples
 *
 * Here's an example on how you create a SimplrWPObject field:
 * ```php
 *```
 */
class SimplrWPObject extends Field {
		
	protected $default_simplrwpobject_settings = array(
		'simplrwp_object' => false,
		'allow_multiple' => 1
	);
	
	public function __construct($settings){
		// load defaults
		$this->settings = array_replace_recursive($this->settings, $this->default_simplrwpobject_settings);

		//add ajax filters
		add_action('wp_ajax_simplrwp/fields/simplrwp_object/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_simplrwp/fields/simplrwp_object/query',	array($this, 'ajax_query'));
		
		// pass along all settings to parent
		parent::__construct($settings);
	}
	
	public function get_simplrwp_object_types() {
		return $this->settings['simplrwp_object_types'];
	}
	
	public function wp_admin_render_field() {
		
		// Change Field into a select
		$field['name'] = $this->get_name();
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['multiple'] = $this->settings['allow_multiple'];
		$field['choices'] = array();
		
		if (($field['value'] = @unserialize($this->settings['value'])) === false) {
			$field['value'] = [ $this->settings['value'] ];
		}
		
		// populate choices if value exists
		if( !empty($field['value']) ) {
			
			foreach( $field['value'] as $i ) {
				
				$saved_object_parts = explode('=::=', $i);
				if(sizeof($saved_object_parts)==2) {
					$object_class = $saved_object_parts[0];
					$object_id = $saved_object_parts[1];
	
					$object = new $object_class($object_id);
					
					//generate text label from fields
					$text_array = array();
					foreach($this->settings['simplrwp_object_types'][$object_class] as $current_field) {
						$text_array[] = $object->fields[$current_field]->get_value();
					}
					
					// append to choices
					$field['choices'][ $i ] = implode(' ', $text_array);
				}
			}
		}
	
		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
		
		// render
		echo '<div class="field simplrwp--simplrwpobject">';
			echo '<label class="simplrwp--label">' . $this->get_label() . $required . '</label>';
			echo '<div class="simplrwp-input">';
				acf_render_field( $field );
			echo '</div>';
		echo '</div>';
		
	}
	
	public function get_choices( $options = array() ) {
		global $available_simplrwp_objects;
	
		// defaults
   		$options = acf_parse_args($options, array(
			'object_id'			=> 0,
			's'					=> '',
			'field_key'			=> '',
			'paged'				=> 1,
   			'simplrwp_object' 	=> false
		));
		
		// vars
   		$r = [];
   		$args = array();
   		
   		if($options['simplrwp_object']) {
   			$some_object = new $options['simplrwp_object']();
   			$this->settings['simplrwp_object_types'] = $some_object->fields[$options['field_key']]->get_simplrwp_object_types();
   		}
   		
		// paged
   		$args['limit'] = 20;
   		$args['offset'] = ($options['paged']-1) * $args['limit'];
   		
		if(!empty($this->settings['simplrwp_object_types'])) {
			$simplrwp_object_types = array_keys($this->settings['simplrwp_object_types']);
		} else {
			$simplrwp_object_types = array();
			foreach($available_simplrwp_objects as $class => $object) {
				$simplrwp_object_types[] = $class;
			}
		}
		
		// get objects
		foreach($simplrwp_object_types as $object_class) {
			$query_params = $args;
			if( $options['s'] ) {
				$query_params['where_args'] = array('relation' => 'OR');
				foreach($this->settings['simplrwp_object_types'][$object_class] as $current_field) {
					$query_params['where_args'][] = array(
						'compare' => 'LIKE',
						'key' => $current_field,
						'value' => $options['s']	
					);
				}
			}
			
			
			$object_query = new \SimplrWP\Core\ObjectQuery(new $object_class());
			$found_objects = $object_query->query($query_params);
			
			if(!empty($found_objects)) {
				$simplrwp_object_type = new $object_class();
				$data = array(
					'text'		=> $simplrwp_object_type->get_labels()['plural'],
					'children'	=> array()
				);
				foreach($found_objects as $current_object_data) {
					$current_object = new $object_class($current_object_data['id']);
					//generate text label from fields
					$text_array = array();
					foreach($this->settings['simplrwp_object_types'][$object_class]  as $current_field) {
						$text_array[] = $current_object->fields[$current_field]->get_value();
					}
					
					$data['children'][] = array(
						'id'	=> $object_class . '=::=' . $current_object->get_id(),
						'text'	=> implode(' ', $text_array)
					);
					
				}
				$r[] = $data;
			}
		}
		
		// optgroup or single
		if( count($simplrwp_object_types) == 1) {
			$r = $r[0]['children'];
		}
		
		// return
		return $r;
		
	}
	
	public function ajax_query() {
	
		// get choices
		$choices = $this->get_choices( $_POST );
	
		// validate
		if( !$choices ) {
			echo json_encode( [
				'items' => [],
				'total_count' => 0
			] );
			die();
				
		}
	
		// return JSON
		echo json_encode( [
			'items' => $choices,
			'total_count' => sizeof($choices)
		]);
		die();
	}
	
	public function get_objects() {
		$value = is_serialized($this->settings['value']) ? unserialize($this->settings['value']) : [$this->settings['value']];
		
		$simplrwp_objects = array();
		if(!empty($value)){
			foreach($value as $i) {
				$value_parts = explode('=::=', $i);
				$simplrwp_objects[$value_parts[0]][] = intval($value_parts[1]);
			}			
		}
		
		return $simplrwp_objects;
	}

	
	public function wp_admin_enqueue_scripts() {
		wp_enqueue_script('simplrwp-select2', SIMPLRWP_URL . 'assets/js/third_party/select2.min.js' ,array('jquery'),AMBR_VERSION,true);
		wp_enqueue_style('simplrwp-select2', SIMPLRWP_URL . 'assets/css/third_party/select2.min.css', '' );
		
		wp_enqueue_style('simplrwp-simplrwp-object', SIMPLRWP_URL . 'assets/css/simplrwp-simplrwp_object.css');
		wp_enqueue_script('simplrwp_wp-simplrwp-object', SIMPLRWP_URL . 'assets/js/fields/SimplrWPObject.js');
	}
}