<?php 
namespace SimplrWP\Core;
/**
 * ## Overview
 * The SimplrWP Object is similar to WordPress's Custom Post Type (CPT),
 * but with more flexibility and control over the database structure, how 
 * they work within the WordPress Admin Dashboard, and how the object's
 * data is validated.   
 *
 * This object serves as a base and can be extended to create more complex 
 * objects for your project. By extending this class you can create virtually
 * any time of object. While the object can be used directly, it is not recommended.
 * 
 * ## Examples
 * 
 * Here's an example on how you can extend this object to create your own:
 * ```php
 *  class Sample_Author extends SimplrWP\Core\Object {

		public function __construct($id = null) {
	
			$this->latest_db_version = 1;
	
			$this->fields = array(
				'first_name' => new SimplrWP\Fields\Text(array('label' => 'First Name')),
				'last_name' => new SimplrWP\Fields\Text(array('label' => 'Last Name'))
			);
			
			$this->labels = array('singular' => 'Author','plural' => 'Authors');
	
			parent::__construct($id);
		}
	
	}
 * ```
 * As you can see, when you create a new object you can setup the attributes (fields)
 * for the object.  Each field uses a SimplrWP Field type.  SimplrWP includes a set of default
 * field types, but you can easily extend the platform to support your own field type.
 * 
 * Once you've created your new object class, it's easy to use it.
 * 
 * Here's an example of how to use your new object:
 * ```php
 * $new_author = new Sample_Author();
 * ```
 * 
 * If you're trying to load an instance of the object from the database, you can instantiate
 * the object like this:
 * ```php
 * $current_author = new SampleAuthor(1); //pass in the ID of the object
 * ```
 */
class SObject {
	/*
	 * The id of the object
	 *
	 * @var integer
	 */
	protected $id = null;
	
	/*
	 * The date the object was created.
	 *
	 * @var string
	 */
	protected $created_at = null;
	
	/*
	 * The date the object was updated.
	 *
	 * @var string
	 */
	protected $updated_at = null;
	/*
	 * The name of the class as a variable for things such 
	 * as database table generation.
	 * 
	 * @var string
	 */
	protected $unique_name = null;
	
	/*
	 * The database table name for this object type.
	 *
	 * @var string
	 */
	protected $db_table_name = null;
	
	/*
	 * The latest version of the database table.  If a field
	 * is added, updated, or removed, the db version needs to 
	 * change to force the db updates.
	 *
	 *	@var integer
	 */
	protected $latest_db_version = 1;
	
	/*
	 * The field objects related to the object.  It should be
	 * an array of SimplrWP\Fields\Text (or any child of).
	 * 
	 * @var array
	 */
	public $fields = array();
	
	/*
	 * This is the visible labels related to the object.  Here we
	 * can provide both the singular and plural labels.
	 *
	 * @var array
	 */
	protected $labels = array(
		'singular' => 'Object',
		'plural' => 'Objects'	
	);
	
	/**
	 * This is the constructor to instantiate the object.  To create an
	 * object with data from the database, just pass the object $id.
	 *
	 * @param integer $id The ID of the object.
	 * 
	 * @since 2016-07-13
	 */
	public function __construct($id = null) {
		global $wpdb, $available_simplrwp_objects;
		
		// generate unique name
		$this->unique_name = $this->_get_class_name();
		
		// set the db table name
		$this->db_table_name = $wpdb->prefix . $this->unique_name;
		
		// check to see if the database version is the latest
		$current_db_version = get_option($this->unique_name . '_db_version');
		if($current_db_version != $this->latest_db_version) {
			$this->_update_db_table_structure();
		}
		
		// add this to available objects for polling
		$this_class = $this->get_unique_name();
		$available_simplrwp_objects[$this_class] = true;
		
		// if ID provided, retrieve object
		if(!empty($id)) {
			$this->id = $id;
			$this->_retrieve_db_data();
		}
	}
	
	/**
	 * This returns the name of the object.
	 *
	 * @return string
	 *
	 * @since 2016-07-13
	 */
	public function get_unique_name() {
		return $this->unique_name;
	}
	
	/**
	 * This returns the labels of the object (singular and plural).
	 *
	 * @return array
	 *
	 * @since 2016-07-13
	 */
	public function get_labels() {
		return $this->labels;
	}
	
	/**
	 * This returns the object's database table name
	 *
	 * @return string
	 *
	 * @since 2016-07-15
	 */
	public function get_db_table_name() {
		return $this->db_table_name;
	}
	
	/**
	 * This returns the object's ID
	 *
	 * @return integer
	 *
	 * @since 2016-07-15
	 */
	public function get_id() {
		return $this->id;
	}
	
	/**
	 * This returns the object's data in key/value array
	 *
	 * @return array
	 *
	 * @since 2016-07-15
	 */
	public function get() {
		$data = array(
			'id' => $this->id,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at
		);
		foreach($this->fields as $field) {
			$data[$field->get_name()] = $field->get_value();
		}
		return $data;
	}
	
	/**
	 * This returns the object's updated at value, with formatting
	 *
	 * @return string
	 *
	 * @since 2016-10-03
	 */
	public function get_updated_at($format = 'F j, Y') {
		if(!$this->updated_at)
			return $this->get_created_at($format);
		return date($format, strtotime($this->updated_at));
	}
	
	/**
	 * This returns the object's create at value, with formatting
	 *
	 * @return string
	 *
	 * @since 2016-10-03
	 */
	public function get_created_at($format = 'F j, Y') {
		return date($format, strtotime($this->created_at));
	}
	
	/**
	 * This gets the labels of all the fields associated to this object.
	 *
	 * @return array
	 *
	 * @since 2016-07-13
	 */
	public function get_data_labels($only_fields = false) {
		$data_attributes = [];
		if(is_array($only_fields)) {
			foreach($only_fields as $name) {
				if($name == 'created_at') {
					$data_attributes[$name] = 'Created At';
				} elseif($name == 'updated_at') {
					$data_attributes[$name] = 'Updated At';
				} else {
					$data_attributes[$name] = $this->fields[$name]->get_label();
				}
			}
		} else {
			foreach($this->fields as $name => $field) {
				$data_attributes[$name] = $field->get_label();
			}
		}
		return $data_attributes;
	}
	
	/**
	 * This sets the objects ID and updates it with the most recent data
	 * from the database.
	 *
	 * @param  integer $id The ID of the object.
	 *
	 * @since 2016-07-13
	 */
	public function set_id_and_retrieve_data($id = null) {
		$this->id = $id;
		$this->_retrieve_db_data();
	}
	
	/**
	 * This update an object in the database, given the key/value pairs
	 * of data.  Before it is updated, the data is validated. If the object
	 * doesn't exist, it will br created.
	 *
	 * @param  array $data The key/value pairs of data to update the object.
	 *
	 * @return array|bool
	 * 
	 * @since 2016-07-13
	 */
	public function update($data = array()) {
		global $wpdb;
	
		// first, let's validate the data before updating the object
		$result = $this->_validate_data($data);
		if($result['valid'] && sizeof($result['data'])>0) {

			// data looks good, let's update it in the database
			$updated = false;
			$db_data = $this->_prepare_data_for_db($result['data']);
			
			// give the ID is present, let's update this object
			if(!empty($this->id)) {
				$updated = $wpdb->update($this->db_table_name,$db_data, array('id' => $this->id) ) > 0;
			// since no ID is present, let's create this object
			} else {
				$updated = $wpdb->insert($this->db_table_name, $db_data) === 1;
				$this->id = $wpdb->insert_id;
			}
			$this->_retrieve_db_data();
			
			// get all the field data
			$result['data']  = $this->get();
		}
		// data didn't validate, return error results
		return $result;
	}
	
	/**
	 * This deletes the object from the database.
	 *
	 * @return bool
	 *
	 * @since 2016-07-13
	 */
	public function delete() {
		global $wpdb;
		
		if($wpdb->delete( $this->db_table_name, array( 'id' => $this->id ) ) ) {
			$this->data = array();
			return true;
		}
		return false;
	}
	
	/**
	 * This returns the fields raw value
	 *
	 * @return mixed
	 *
	 * @since 2016-12-08
	 */
	public function get_field($field = '') {
		if($field == 'created_at')
			return $this->get_created_at();
		
		if($field == 'updated_at')
			return $this->get_updated_at();
		
		if($field) {
			return $this->fields[$field]->get_value();
		}
		return false;
	}
	
	/**
	 * This returns the fields rendered value
	 *
	 * @return mixed
	 *
	 * @since 2016-12-08
	 */
	public function render_field($field = '') {
		if($field == 'created_at' || $field == 'updated_at')
			return $this->render_field($field);
		
		if($field) {
			return $this->fields[$field]->render_value();
		}
		return false;
	}
	
	/**
	 * This validates the potential data to be saved into the database.
	 *
	 * @param array $potential_data The potential data to be saved.
	 *
	 * @return array
	 *
	 * @since 2016-07-13
	 */
	private function _prepare_data_for_db($data) {
		return array_map(function(&$value) {
			// if array, return as array
			if(is_array($value)) { return serialize($value); }
			// return raw value
			return $value;
		}, $data);
	}
	
	/**
	 * This validates the potential data to be saved into the database.
	 *
	 * @param array $potential_data The potential data to be saved.
	 * 
	 * @return array
	 *
	 * @since 2016-07-13
	 */
	public function _validate_data($potential_data = array()) {
		$data_to_verify = array();
		
		// add created at
		if(isset($potential_data['created_at'])) {
			$data_to_verify['created_at'] = array(
				'value' => $potential_data['created_at'],
				'validations' => []
			);
		}
		
		// add updated at
		if(isset($potential_data['updated_at'])) {
			$data_to_verify['updated_at'] = array(
				'value' => $potential_data['updated_at'],
				'validations' => []
			);
		}
		
		// remove unavailable fields and get validations
		foreach($this->fields as $field_name => $field) {
			if(isset($potential_data[$field_name]))
				$field->set_value($potential_data[$field_name]);
			
			$data_to_verify[$field_name] = array(
				'value' => $field->get_value(),
				'validations' => $field->get_before_save_validations()
			);
		}
		
		// validate data
		$validator = new Validator;
		return $validator->validate($data_to_verify);
	}
	
	/**
	 * This updates the object with the latest data from the database.
	 *
	 * @return bool
	 *
	 * @since 2016-07-13
	 */
	private function _retrieve_db_data() {
		global $wpdb;
		
		if($this->id) {
			if($values = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->db_table_name WHERE id = %d", $this->id), ARRAY_A )) {
				$this->created_at = $values['created_at'];
				$this->updated_at = $values['updated_at'];
				
				//set value of each field based on DB data
				foreach($this->fields as $field_name => &$field) {
					$field->set_value($values[$field_name]);
				}
			}
			
			return true;
		}
		return false;
	}
	
	/**
	 * This is an internal function that will update the object's
	 * database architecture.
	 *
	 * @since   2016-07-13
	 */
	private function _update_db_table_structure(){
		global $wpdb;
	
		// get character set collation
		$charset_collate = $wpdb->get_charset_collate();
	
		// generate the sql to create the table
		$fields = array_merge(array(
			'id bigint(20) NOT NULL AUTO_INCREMENT,',
			'created_at datetime DEFAULT CURRENT_TIMESTAMP,',
			'updated_at datetime ON UPDATE CURRENT_TIMESTAMP,'
		), $this->_flatten_custom_table_fields(), array('UNIQUE KEY id (id)') );
			
		$sql = 'CREATE TABLE ' . $this->db_table_name . ' (' . implode("\n", $fields) . ') ' . $charset_collate . ';';
	
		// use the dbDelta function to update the table
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	
		// update to the current database version for this object
		update_option( $this->unique_name . '_db_version', $this->latest_db_version );
	}
	
	
	/**
	 * This generates the table fields when creating/update the database table architecture.
	 *
	 * @return array
	 *
	 * @since 2016-07-13
	 */
	private function _flatten_custom_table_fields() {
		$flatten_fields = array();
		foreach($this->fields as $name => $field) {
			$flatten_fields[] = $name . ' ' . $field->get_db_config();
		}
		return $flatten_fields;
	}
	
	/**
	 * This generates the class name/unique name for the class object.
	 *
	 * @return string
	 *
	 * @since 2016-07-13
	 */
	private function _get_class_name() {
		$path = explode('\\', get_class($this));
		return strtolower(array_pop($path));
	}
}
