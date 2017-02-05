<?php

/*
*  ACF Post Object Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_simplrwp_object
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_simplrwp_object') && class_exists('acf_field') ) :

class acf_field_simplrwp_object extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		global $available_simplrwp_objects;
		
		// vars
		$this->name = 'simplrwp_object';
		$this->label = __("SimplrWP Object",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'		=> array(),
			'taxonomy'		=> array(),
			'allow_null' 	=> 0,
			'multiple'		=> 1,
			'duplicates'	=> 1,
			'return_format'	=> 'object',
			'ui'			=> 1,
		);
		
		
		// extra
		add_action('wp_ajax_acf/fields/simplrwp_object/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/simplrwp_object/query',	array($this, 'ajax_query'));
		
		
		// do not delete!
    	parent::__construct();
		
	}
	
	
	/*
	*  get_choices
	*
	*  This function will return an array of data formatted for use in a select2 AJAX response
	*
	*  @type	function
	*  @date	15/10/2014
	*  @since	5.0.9
	*
	*  @param	$options (array)
	*  @return	(array)
	*/
	
	function get_choices( $options = array() ) {
		global $available_simplrwp_objects;
		
		// defaults
   		$options = acf_parse_args($options, array(
			'object_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
			'paged'			=> 1,
   			'current_value' => ''
		));
		
		// vars
   		$r = array();
   		$args = array();
   		
		
		// paged
   		$args['limit'] = 20;
   		$args['offset'] = ($options['paged']-1) * $args['limit'];
   		
   		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		// bail early if no field
		if( !$field ) return false;
		
		
		if(!empty($field['simplrwp_object_types'])) {
			$simplrwp_object_types = $field['simplrwp_object_types'];
		} else {
			$simplrwp_object_types = array();
			foreach($available_simplrwp_objects as $class => $object) {
				$simplrwp_object_types[] = $class;
			}
		}
		
		// get values to omit based on current value
		$omit_values = [];
		foreach(explode('||', $options['current_value']) as $value) {
			$value_parts = explode('=::=', $value);
			$omit_values[$value_parts[0]][] = intval($value_parts[1]); 
		}
		
		// get objects
		foreach($simplrwp_object_types as $object_class) {
			$query_params = $args;
			if($field['duplicates']!=1) {
				$query_params['where_args'] = array(
					'relation' => 'AND',
					array(
						'compare' => 'NOT IN',
						'key' => 'id',
						'value' => $omit_values[$object_class]
					)
				);
			}
			
			if( $options['s'] ) {
				$search_query = array(
					'relation' => 'OR',
				);
				foreach($field[$object_class . '-object_fields'] as $current_field) {
					$search_query[] = array(
						'compare' => 'LIKE',
						'key' => $current_field,
						'value' => $options['s']	
					);
				}
				
				$query_params['where_args'][] = $search_query;
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
					foreach($field[$object_class . '-object_fields'] as $current_field) {
						$text_array[] = $current_object->get_field($current_field);
					}
					
					if($format = $field[$object_class . '-object_field_display']) {
						$text_array = [ vsprintf($format, $text_array) ];
					}
					
					$data['children'][] = array(
						'id'	=> $object_class . '=::=' . $current_object->get_id(),
						'text'	=> implode(' ', $text_array)
					);
					
				}
			}
			
			$r[] = $data;
		}
		
		// optgroup or single
		if( count($simplrwp_object_types) == 1 ) {
			
			$r = $r[0]['children'];
			
		}
		
		// return
		return $r;
		
	}
	
	
	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		// validate
		if( !acf_verify_ajax() ) {
		
			die();
			
		}
		
		// get choices
		$choices = $this->get_choices( $_POST );
		
		
		// validate
		if( !$choices ) {
			
			die();
			
		}
		
		
		// return JSON
		echo json_encode( $choices );
		die();
			
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		// populate choices if value exists
		if( !empty($field['value']) ) {
			// always make sure the value is an array
			$field['value'] = is_string($field['value']) ? array($field['value']) : $field['value'];
			foreach( $field['value'] as $i ) {
				
				$saved_object_parts = explode('=::=', $i);
				if(sizeof($saved_object_parts)==2) {
					$object_class = $saved_object_parts[0];
					$object_id = $saved_object_parts[1];
	
					$object = new $object_class($object_id);
					
					//generate text label from fields
					$text_array = array();
					foreach($field[$object_class . '-object_fields'] as $current_field) {
						$text_array[] = $object->fields[$current_field]->get_value();
					}
					
					// append to choices
					$field['choices'][ $i ] = implode(' ', $text_array);
				}
			}
		}

		// render
		acf_render_field( $field );
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		global $available_simplrwp_objects;
		
		$simplrwp_objects = array();
		foreach($available_simplrwp_objects as $class => $object) {
			$simplrwp_object = new $class();
			$simplrwp_objects[$class] = $simplrwp_object->get_labels()['plural'];
		}
		
		// simplewp object types
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by SimplrWP Object type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'simplrwp_object_types',
			'choices'		=> $simplrwp_objects,
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All SimplrWP Object types",'acf'),
		));
		
		foreach($simplrwp_objects as $object_class => $simplrwp_object) {
			
			$object_query_fields = array();
			$object = new $object_class();
			foreach($object->fields as $object_field) {
				// only allow field types of Text and TextArea queryable
				if($object_field instanceof \SimplrWP\Fields\Text || $object_field instanceof \SimplrWP\Fields\TextArea) {
					$object_query_fields[$object_field->get_name()] = $object_field->get_label();
				}
			}
			
			acf_render_field_setting( $field, array(
				'label'			=> __('Query ' . strtolower($simplrwp_object) . ' by these fields.','acf'),
				'instructions'	=> '',
				'type'			=> 'select',
				'choices'		=> $object_query_fields,
				'name'			=> $object_class . '-object_fields',
				'multiple'		=> 1,
				'ui'			=> 1,
				'allow_null'	=> 0,
				'placeholder'	=> __("Select a query field.",'acf'),
			));
			
			acf_render_field_setting( $field, array(
				'label'			=> __($simplrwp_object . ' Fields Display Mask','acf'),
				'instructions'	=> '',
				'type'			=> 'text',
				'name'			=> $object_class . '-object_field_display',
				'multiple'		=> 1,
				'ui'			=> 1,
				'allow_null'	=> 0,
				'placeholder'	=> __("Use %s for each field to choose how to display an object. (i.e. - %s %s).",'acf'),
			));
		}
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'allow_null',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'multiple',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow duplicates values?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'duplicates',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=> __("SimplrWP Object",'acf'),
				'id'			=> __("SimplrWP ID",'acf'),
			),
			'layout'	=>	'horizontal',
		));
				
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// ACF4 null
		if( $value === 'null' ) {
		
			return false;
			
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) )
			return $value;
		
		// if not multiple make array of single value
		if( !$field['multiple'] )
			$value = array( $value );
		
		if(!is_array($value))
			return $value;
		
		$simplrwp_objects = array();
		foreach($value as $i) {
			$value_parts = explode('=::=', $i);
			$simplrwp_objects[$value_parts[0]][] = intval($value_parts[1]);
		}
		
		return $simplrwp_objects;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		// return
		return $value;
		
	}
	
	function input_admin_enqueue_scripts() {
		wp_enqueue_script( 'acf-simplrwp_object', SIMPLRWP_URL . 'assets/js/third_party/acf-simplrwp_object.js' ,array('acf-input'),AMBR_VERSION,true);
	}
	
}

endif;

?>
