<?php 
namespace SimplrWP\Core;
/**
 * ## Overview
 * The SimplrWP ObjectQuery allows you to query a series of SimplrWP Objects.
 * 
 * To do this we first need to create a new ObjectQuery instance, passing in the SimplrWP Object that we want to query against.
 * 
 * ```php
 * $object_query = new ObjectQuery(new Object);
 * ```
 * 
 * ## Example Query
 * 
 * You can create some advance queries by adding multiple where_args.
 * 
 * ```relation``` can be either AND or OR.
 * 
 * ```compare``` can be either '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS','REGEXP', 'NOT REGEXP', 'RLIKE', 'BEGINS WITH', 'ENDS WITH'
 * 
 * Here's an example on how you would query the object:
 * ```php
 *  $results = $object_query->query(array(
 *  	'order_by' => key($this->object->fields),
		'order' => 'asc',
		'limit' => 5,
		'offset' => 0,
		'where_args' => array(
			'relation' => 'AND',
			array(
				'key' => 'id',
				'value' => 5,
				'compare' => '='
			),
			array(
				'key' => 'first_name',
				'value' => 'Anthony,
				'compare' => 'LIKE'
			)
		)	
 *  ));
 * ```
 * 
 */
class ObjectQuery {
	/*
	 * This is the total number of objects in the database
	 * 
	 * @var integer
	 */
	protected $total_num_of_db_objects = null;
	
	/*
	 * This is the total number of objects on last query
	 *
	 * @var integer
	 */
	protected $total_num_of_last_query_objects = null;
	
	/*
	 * This is the object type to be queried
	 *
	 * @var integer
	 */
	public $object = null;
	
	/**
	 * This is the constructor to instantiate the object query. Pass the
	 * object you wish to query upon.
	 *
	 * @param SimplrWP\Object $object The object to be queried.
	 * 
	 * @since 2016-07-15
	 */
	public function __construct($object = null) {
		$this->object = $object;
	}
	
	/**
	 * This returns an array of objects based on an array of query parameters, false if no results.
	 *
	 * @param array $options The key/value pairs of query options
	 *
	 * @return array|bool
	 * 
	 * @since 2016-07-13
	 */
	public function query($options = array()) {
		global $wpdb;
		
		reset($this->object->fields);
		$options += array(
			'order_by' => key($this->object->fields),
			'order' => 'asc',
			'limit' => 5,
			'offset' => 0,
			'data' => array(),
			'distinct' => false,
			'where_args' => array(),
			'group_by' => array()
		);
		
		$where_clause = '';
		if(!empty($options['where_args'])) {
			$where_clause = ' WHERE ' . $this->_generate_sql_from_parameters($options['where_args']);
		}
		
		$limit = '';
		if($options['limit']>0) {
			$limit = $wpdb->prepare(" LIMIT %d, %d" , array($options['offset'], $options['limit']));
		}
		
		// determine if you should group items
		$group_by = '';
		if(!empty($options['group_by'])) {
			$group_by = 'GROUP BY ' . implode(', ', $options['group_by']);
		}
		
		// determine which data to select
		$data = '*';
		if(!empty($options['data'])) {
			$select = [];
			foreach ( $options['data'] as $key => $value ) {
				$distinct = '';
			
				if ( $options['distinct'] ) {
					$distinct = 'DISTINCT';
				}
					
				if ( isset($value['function']) && !empty($value['function']) ) {
					$get = "{$value['function']}({$distinct} {$key})";
				} else {
					$get = "{$distinct} {$key}";
				}
				
				if($key == $value['name']) {
					$select[] = "{$get}";
				} else {
					$select[] = "{$get} as {$value['name']}";
				}
			}
			$data = implode(', ', $select);
		}
		
		$query = "SELECT " . $data . " FROM " . $this->object->get_db_table_name() . $where_clause . " " . $group_by . " ORDER BY " . $options['order_by'] . " " . $options['order'] . $limit; 
	
		// this sets the total number of objects on the query (no limit)
		$this->total_num_of_last_query_objects = $wpdb->get_var("SELECT COUNT(*) FROM " . $this->object->get_db_table_name() . $where_clause);
		
		return apply_filters('simplrwp_query_results-' . $this->object->get_unique_name(), $wpdb->get_results( $query, ARRAY_A ));
	}
	
	/**
	 * This returns the total number of objects based on the object type.
	 *
	 * @return integer
	 *
	 * @since 2016-07-13
	 */
	public function total_number_of_db_objects() {
		global $wpdb;
		
		$this->total_num_of_db_objects = $wpdb->get_var("SELECT COUNT(*) FROM " . $this->object->get_db_table_name());
		
		return $this->total_num_of_db_objects;
	}
	
	/**
	 * This returns the total number of objects based on the last query
	 *
	 * @return integer
	 *
	 * @since 2016-11-30
	 */
	public function total_number_of_last_query_objects() {
		return $this->total_num_of_last_query_objects;
	}
	
	/**
	 * This returns all fields that are sortable for the wp-admin list. Meant to be used by WordPress WP_List_Table.
	 *
	 * @return array
	 *
	 * @since 2016-07-13
	 */
	public function get_admin_list_sortables() {
		$data_attributes = array();
		foreach($this->object->fields as $name => $field) {
			if($field->get_wp_admin_list()['sortable'])
				$data_attributes[$name] = array($name, false);
		}
		return $data_attributes;
	}
	
	/**
	 * This returns all fields that are hidden from the wp-admin list. Meant to be used by WordPress WP_List_Table.
	 *
	 * @return  array
	 *
	 * @since 2016-07-13
	 */
	public function get_admin_hidden_fields() {
		$hidden_fields = array();
		foreach($this->object->fields as $name => $field) {
			if($field->get_wp_admin_list()['hidden'])
				$hidden_fields[] = $name;
		}
		return $hidden_fields;
	}

	/**
	 * This returns a SQL query based on an array of arguments
	 *
	 * @param array $options The array of options to query against
	 *
	 * @return string
	 *
	 * @since 2016-07-18
	 */
	protected function _generate_sql_from_parameters($options) {
		$relation = 'AND';
		if(array_key_exists('relation', $options) ) {
			$relation = $options['relation'];
			unset($options['relation']);
		}
		
		$sql = array();
		foreach($options as $clause) {
			$is_sub_clause = !array_key_exists('key', $clause) && !array_key_exists('value', $clause);
			if($is_sub_clause) {
				$sql[] = '('. $this->_generate_sql_from_parameters($clause) . ')';
			} else {
				$sql[] = $this->_generate_sql_where_clause($clause);
			}
		}
		$sql = implode(' ' . $relation . ' ', $sql);
	
		return $sql;
	}
	
	
	/**
	 * This returns a SQL query based on an array of arguments
	 *
	 * @param array $options The array of options to query against
	 *
	 * @return string
	 *
	 * @since 2016-07-18
	 */
	protected function _generate_sql_where_clause($clause) {
		global $wpdb;
	
		if ( isset( $clause['compare'] ) ) {
			$clause['compare'] = strtoupper( $clause['compare'] );
		} else {
			$clause['compare'] = isset( $clause['value'] ) && is_array( $clause['value'] ) ? 'IN' : '=';
		}
	
		if ( ! in_array( $clause['compare'], array(
				'=', '!=', '>', '>=', '<', '<=',
				'LIKE', 'NOT LIKE',
				'IN', 'NOT IN',
				'BETWEEN', 'NOT BETWEEN',
				'EXISTS', 'NOT EXISTS',
				'REGEXP', 'NOT REGEXP', 'RLIKE',
				'BEGINS WITH', 'ENDS WITH',
				'FIND IN SET'
		) ) ) {
			$clause['compare'] = '=';
		}
	
		$meta_compare = $clause['compare'];
	
		// meta_value
		if ( array_key_exists( 'key', $clause ) && array_key_exists( 'value', $clause ) ) {
			$meta_key = $clause['key'];
			$meta_value = $clause['value'];
			
			if( $meta_compare == 'FIND IN SET' ) {
				if( !empty($clause['delimiter'])) {
					$meta_key = sprintf("REPLACE('%s',',', %s)", $clause['delimiter'], $meta_key);
				}
				return $wpdb->prepare( 'FIND_IN_SET(%s, %s)', $meta_value, $meta_key );
			}
	
			if ( in_array( $meta_compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
				if ( ! is_array( $meta_value ) ) {
					$meta_value = preg_split( '/[,\s]+/', $meta_value );
				}
			} else {
				$meta_value = trim( $meta_value );
			}
			switch ( $meta_compare ) {
				case 'IN' :
				case 'NOT IN' :
					$meta_compare_string = '(' . substr( str_repeat( ',%s', count( $meta_value ) ), 1 ) . ')';
					$where = $wpdb->prepare( $meta_compare_string, $meta_value );
					break;
	
				case 'BETWEEN' :
				case 'NOT BETWEEN' :
					$meta_value = array_slice( $meta_value, 0, 2 );
					$where = $wpdb->prepare( ' %s AND %s', $meta_value );
					break;
	
				case 'LIKE' :
				case 'NOT LIKE' :
					$meta_value = '%' . $wpdb->esc_like( $meta_value ) . '%';
					$where = $wpdb->prepare( ' %s', $meta_value );
					break;
						
				case 'BEGINS WITH' :
					$meta_compare = 'LIKE';
					$meta_value = $wpdb->esc_like( $meta_value ) . '%';
					$where = $wpdb->prepare( ' %s', $meta_value );
					break;
						
				case 'ENDS WITH' :
					$meta_compare = 'LIKE';
					$meta_value = '%' . $wpdb->esc_like( $meta_value );
					$where = $wpdb->prepare( ' %s', $meta_value );
					break;
	
				case 'NOT EXISTS' :
				case 'EXISTS' :
				default :
					$where = $wpdb->prepare( ' %s', $meta_value );
					break;
	
			}
		}
		return $meta_key . ' ' . $meta_compare . $where;
	}
}
