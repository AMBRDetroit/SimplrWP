<?php
namespace SimplrWp\WPAdmin;
 
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ObjectList extends \WP_List_Table {
	
	protected $object;
	
	protected $options = [
 		'allow_bulk_actions' => true
 	];
	
	public function __construct($options = array()) {
		$this->options = $options + $this->options;
		
		parent::__construct();
	}
	
	public function get_columns() {
		$columns = [];
		
		// if bulk options, add check box to select bulk rows
		if($this->options['allow_bulk_actions']) {
			$columns['cb'] = '<input type="checkbox" />';
		}
		
		// let's get the column data sets
		if(isset($this->options['fields']) && is_array($this->options['fields']) && !empty($this->options['fields'])) {
			foreach($this->options['fields'] as $field_name => $field_options) {
				if(array_key_exists('label', $field_options)) {
					$columns[$field_name] = $field_options['label'];
				} else {
					$columns = array_merge($columns, $this->object->get_data_labels([ $field_name ]) );
				}
			}
		} else {
			$columns = array_merge($columns, $this->object->get_data_labels() );
		}
		
 		return $columns;
	}
	
	public function prepare_items($query_object = null) {
		$this->object = $query_object->object;
		
		$action = $this->current_action();
		if($action == 'delete') {
			$this->delete_objects($_POST[$this->object->get_unique_name()]);
		}
	
		if(empty($this->options['primary_field'])) {
			reset($this->object->fields);
			$this->options['primary_field'] = key($this->object->fields);
		}
		$this->_column_headers = array($this->get_columns(), $query_object->get_admin_hidden_fields(), $query_object->get_admin_list_sortables());
		
		//query options
		$query_options = $this->options;
		if(isset($_GET['orderby'])) {
			$query_options['order_by'] = $_GET['orderby'];
		}
		if(isset($_GET['order'])) {
			$query_options['order'] = $_GET['order'];
		}
		if(isset($_GET['paged'])) {
			$query_options['offset'] = ($_GET['paged']-1) * $this->options['items_per_page'];
		}
		if(isset($_GET['s']) && !empty($_GET['s'])) {
			$query_options['where_args'] = array(
				'relation' => 'OR'
			);
			foreach($this->options['query_fields'] as $field) {
				$query_options['where_args'][] = array(
					'key' => $field,
					'value' => $_GET['s'],
					'compare' => 'LIKE'
				);
			}
		}
			
		$query_options['limit'] = $this->options['items_per_page'];
		$this->items = $query_object->query($query_options);
		
		foreach($this->items as &$item) {
			$object_name = $this->object->get_unique_name();
			$object = new $object_name($item['id']);
			foreach($item as $field => $value) {
				if($field==$this->options['sortable_field']) {
					$item[$field] = '<div class="dashicons-before dashicons-sort" data-id="' . $item['id'] . '"><br></div>';
					continue;
				}
				if(isset($object->fields[$field]) || in_array($field, ['id', 'created_at', 'updated_at'])) {
					if(isset($this->options['fields']) && 
						is_array($this->options['fields']) && 
						!empty($this->options['fields']) &&
						array_key_exists($field, $this->options['fields']) &&
						array_key_exists('value', $this->options['fields'][$field]) &&
						is_callable($this->options['fields'][$field]['value'])) {
							$value = in_array($field, ['id', 'created_at', 'updated_at']) ? $object->get_field($field) : $object->fields[$field];
							$item[$field] = $this->options['fields'][$field]['value']($value, $object);
					} else {
						$item[$field] = in_array($field, ['id', 'created_at', 'updated_at']) ? $object->get_field($field) : $object->fields[$field]->render_value();
					}
				}
			}
		}
		
		$this->set_pagination_args( array(
			'total_items' => $query_object->total_number_of_db_objects(),
			'per_page'    => $this->options['items_per_page']
		) );
	}
	
	public function column_default( $item, $column_name ) {
		if($column_name == $this->options['primary_field']) {
			$this->object->set_id_and_retrieve_data($item['id']);
			$item_url = '?page='.$this->object->get_unique_name().'&id=' . $item['id'];
			if(isset($_GET['post_type']))
				$item_url .= '&post_type=' . $_GET['post_type'];
			return '<a href="' . apply_filters('simplrwp_admin_list_primary_url-' . $this->object->get_unique_name(), $item_url, $this->object) . '">' . $item[$column_name] . '</a>';
		}

		return $item[ $column_name ];
	}
	
	public function column_cb($item) {
        return sprintf('<input type="checkbox" name="' . $this->object->get_unique_name() . '[]" value="%s" />', $item['id'] );    
    }
    
    public function get_bulk_actions() {
	  	if($this->options['allow_bulk_actions']) {
 		  	return array(
 		    	'delete' => 'Delete'
 		  	);
     	}
     	return false;
	}
	
	public function get_object() {
		return $this->object;
	}
	
	public function get_options() {
		return $this->options;
	}
	
	public function delete_objects($object_ids) {
		foreach($object_ids as $object_id) {
			$this->object->set_id_and_retrieve_data($object_id);
			$this->object->delete();
		}
	}
	
}

?>