<?php 
namespace SimplrWP\Core;
/**
* ## Overview
* The SimplrWP Validators allows you to validate data.  This is often used with SimplrWP Objects before saving to the database.
*
* To validate data, start by creating a new Validator instance.
*
* ```php
* $validator = new Validator;
* ```
* 
* Next we can add a rule:
* 
* ```php
* $validator->add_rule('greater_than_five', function($value) {
* 		return $value>5; }, 'greater_than_five_error');
* ```
* 
* We can also add a new error label to be used:
* 
* ```php
* $validator->add_label('greater_than_five_error', "[field_name] must be greater than 5");
* ```
* 
* Finally, let's validate some data against some rules.
* ```php
* $validator->validate(array(
	'first_name' => array(
		'value' => 'Bob',
		'validations' => array('not_empty')
	),
	'email_address' => array(
		'value' => 'bob@bob.com',
		'validations' => array('is_email_address')
	)
* ));
* ```
*/
class Validator {
	/*
	 * The errors found in the form of a WP_Error instance.
	 *
	 * @var WP_Error
	 */
	protected $error_results;
	
	/*
	 * The available validation rules to be used to validate data.
	 *
	 * @var integer
	 */
	protected $rules;
	
	/*
	 * The available validation labels that is displayed to a user when the validation fails
	 *
	 * @var array
	 */
	protected $error_labels = array();
	
	/**
	 * This is the constructor to instantiate the validator.  There are no parameters.
	 *
	 * @since 2016-07-13
	 */
	public function __construct() {
		// loads the default rules to be used
		$this->_load_default_rules();
		// loads the default error labels to be used
		$this->_load_default_error_labels();
	}
	
	/**
	 * This function allows you to validate data against a series of rules.
	 *
	 * @param array $data The data to be validated.
	 *
	 * @return array $error_results The results of the validation
	 * 
	 * @since 2016-07-13
	 */
	public function validate($fields = array()) {
		$results = ['valid' => true, 'errors' => [], 'data' => [] ];
		foreach($fields as $field_name => $options) {
			foreach($options['validations'] as $validation_key => $validation) {
				if($validation instanceof \SimplrWP\Fields\Field) {

					if(isset($options['value'][$validation_key]))
						$validation->set_value($options['value'][$validation_key]);
						
					$field_result = $this->validate([ $validation_key => [
						'value' => $validation->get_value(),
						'label' => $validation->get_label(),
						'validations' => $validation->get_before_save_validations()
					]]);
					
					if(!$field_result['valid']) {
						$results['valid'] = false;
						
						$results['errors'][$field_name][$validation_key] = $field_result['errors'][$validation_key];
					} else {
						$results['data'][$field_name][$validation_key]= $validation->get_value();
					}
				} else {
					// this means it's an validation rule so we test against it
					if(isset($this->rules[$validation]) && !$this->rules[$validation]['function']($options['value'])) {
						// validation failed so the whole object fails
						$results['valid'] = false;
						
						// lets create the error object for the key, if it hasn't been created yet
						if(!isset($results['errors'][$field_name]))
							$results['errors'][$field_name] = [];
							
						// let's add the error in the results
						$results['errors'][$field_name][] = new \WP_Error($validation, str_replace('[field_name]', $options['label'], $this->error_labels[$this->rules[$validation]['error_label']]) );
					} else {
						$results['data'][$field_name] = $options['value'];
					}
				}
			}
			if(empty($options['validations'])) {
				$results['data'][$field_name] = $options['value'];
			}
		}
		return $results;
	}
	
	/**
	 * This function allows you to add new rules to test data against.
	 *
	 * @param  string $name The name of the rule.
	 *
	 * @param  function $function The function which validates the value.
	 *
	 * @param  string $error_label The error label associated to this validation rule
	 *
	 * @since 2016-08-10
	 */
	public function add_rule($name, $function = null, $error_label) {
		$this->rules[$name] = array(
			'function' => $function,
			'error_label' => $error_label
		);
	}
	
	/**
	 * This function allows you to add new error labels
	 *
	 * @param  string $error_label The error label associated to this validation rule
	 *
	 * @param  string $message The message to users.  You can display the field name into the message like this: [field_name].
	 *
	 * @since 2016-08-10
	 */
	public function add_error_label($error_label, $message) {
		$this->error_labels[$error_label] =  $message;
	}
	
	/**
	 * This function allows you generate the rendered messages for the data based on a SimplrWP Object
	 *
	 * @param  array $validated_results The results of the validated data
	 *
	 * @param  object $object This is the SimplrWP Object to render the error messages with
	 *
	 * @since 2016-09-16
	 */
	public function render($wp_error, $object) {
		$rendered = new \WP_Error;
		if(!isset($wp_error))
			return $rendered;
			
			foreach($wp_error->errors as $field => $message) {
				$rendered->add($field, str_replace('[field_name]', $object->fields[$field]->get_label(), $message));
			}
			return $rendered;
	}
	
	
	/**
	 * This function loads the default rules.  Called upon instantiation of the Validator.
	 *
	 * @since 2016-08-10
	 */
	protected function _load_default_rules() {
		// is value an email address?
		$this->add_rule('is_email_address', function($value='') {
			return is_email($value);
		}, 'not_email_address');
		
		// is value not empty
		$this->add_rule('not_empty', function($value='') {
			return !empty($value);
		}, 'is_empty');
		
		// is value a phone number
		$this->add_rule('is_phone_number', function($value='') {
			return $this->is_phone_number($value);
		}, 'not_phone_number');
	}
	
	/**
	 * This function loads the default error labels.  Called upon instantiation of the Validator.
	 *
	 * @since 2016-08-10
	 */
	protected function _load_default_error_labels() {
		// loads the default error labels
		$this->add_error_label('not_email_address', "[field_name] is not a valid email address");
		$this->add_error_label('is_empty', "[field_name] is empty");
		$this->add_error_label('not_phone_number', "[field_name] is not a valid phone number");
	}
	
	/**
	 * This function is used for internal use only.  It validates if a value is a valid phone number.
	 *
	 * @since 2016-08-10
	 */
	protected function _is_phone_number($value){
		$pattern = '^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$^';
		return preg_match($pattern,$value);
	}
	
}



?>