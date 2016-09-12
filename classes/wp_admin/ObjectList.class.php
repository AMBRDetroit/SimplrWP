<?php
namespace SimplrWp\WPAdmin;
 
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ObjectList extends \WP_List_Table {
	
	protected $object;
	
	protected $options;
	
	public function __construct($options = array()) {
		$this->options = $options;
		
		parent::__construct();
	}
	
	public function get_columns() {
		return array_merge(array('cb' => '<input type="checkbox" />'), $this->object->get_data_labels() );
	}
	
	public function prepare_items($query_object = null) {
		$this->object = $query_object->object;
	
		if(empty($this->options['primary_field'])) {
			reset($this->object->fields);
			$this->options['primary_field'] = key($this->object->fields);
		}
		$this->_column_headers = array($this->get_columns(), $query_object->get_admin_hidden_fields(), $query_object->get_admin_list_sortables());
		
		//query options
		$query_options = array();
		if(isset($_GET['orderby'])) {
			$query_options['order_by'] = $_GET['orderby'];
		}
		if(isset($_GET['order'])) {
			$query_options['order'] = $_GET['order'];
		}
		if(isset($_GET['paged'])) {
			$query_options['offset'] = ($_GET['paged']-1) * $this->options['items_per_page'];
		}
		$query_options['limit'] = $this->options['items_per_page'];
		$this->items = $query_object->query($query_options);
		
		$this->set_pagination_args( array(
			'total_items' => $query_object->total_number_of_db_objects(),
			'per_page'    => $this->options['items_per_page']
		) );
	}
	
	public function column_default( $item, $column_name ) {
		if($column_name == $this->options['primary_field']) {
	
			return '<a href="?page='.$this->object->get_unique_name().'&id=' . $item['id'] . '">' . $item[$column_name] . '</a>';
		}
		return $item[ $column_name ];
	}
	
	public function column_cb($item) {
        return sprintf('<input type="checkbox" name="' . $this->object->get_unique_name() . '[]" value="%s" />', $item['id'] );    
    }
    
    public function get_bulk_actions() {
	  	return array(
	    	'delete' => 'Delete'
	  	);
	}
	
}

?>