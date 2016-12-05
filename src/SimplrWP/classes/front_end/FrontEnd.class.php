<?php
namespace SimplrWP\FrontEnd;

class FrontEnd {

	protected $settings = array(
		'object' => null,
		'base_slug' => 'object',
    	'object_slug' => '[id]',
		'single_template_file' => '',
		'object_not_found_template_file' => '',
		'list_page_settings' => array(
			'page_slug' => array('page'),
			'page_order_by' => 'id',
			'page_order' => 'asc',
			'objects_per_page' => 10,
			'template_file' => '',
			'prepare_query_callback' => null
		),
		'single_template_sub_pages' => array()
	);
	
	protected $sub_page_settings = array(
		'template_file' => '',
		'slug_keys' => array('page'),
		'prepare_query_callback' => null
	);
	
	protected $sub_page_query_settings = array(
		'order_by' => 'id',
		'order' => 'asc',
		'limit' => -1,
		'offset' => 0,
		'where_args' => array()
	);
	
	protected $sub_pages = array();
	
	protected $pagination_params = array(
		'total_pages' => 0,
		'current_page' => 0
	);

	public function __construct($settings = array()) {
		// set default template files
		$this->settings['single_template_file'] = SIMPLRWP_PATH . 'templates/front_end/view_object.php';
		$this->settings['object_not_found_template_file'] = SIMPLRWP_PATH . 'templates/front_end/not_found_object.php';
		$this->settings['list_page_settings']['template_file'] = SIMPLRWP_PATH . 'templates/front_end/list_objects.php';
		$this->sub_page_settings['template_file'] = SIMPLRWP_PATH . 'templates/front_end/list_objects.php';
		
		// set default callback query function
		$this->settings['list_page_settings']['prepare_query_callback'] = function($query_params) { return $query_params; };
		
		// update front end settings
		$this->settings = array_replace_recursive($this->settings, $settings);

		$this->sub_page_settings['prepare_query_callback'] = function() {
			return $this->sub_page_query_settings;
		};

		// register new rewrite endpoints
		add_action('init', array(&$this, 'add_rewrite_rules') );
		// register template include based on rewrite rules
		add_filter('template_include', array(&$this, 'load_template') );
		// should the list of objects template be available?
		if($this->settings['list_page_settings']) {
			// add sub page for list of objects
			$this->add_sub_page(array(
				'template_file' => $this->settings['list_page_settings']['template_file'],
				'slug_keys' => $this->settings['list_page_settings']['page_slug'],
				'prepare_query_callback' => function($query_params) {
					return array_replace_recursive(array(
						'order_by' => $this->settings['list_page_settings']['page_order_by'],
						'order' => $this->settings['list_page_settings']['page_order'],
						'limit' => $this->settings['list_page_settings']['objects_per_page'],
						'offset' => $this->settings['list_page_settings']['objects_per_page']*($query_params['page']-1)
					), $this->settings['list_page_settings']['prepare_query_callback']($query_params));
				}	
			));
		}
	}

	public function get_object() {
		return $this->settings['object'];
	}

	public function add_rewrite_rules() {
		add_rewrite_endpoint($this->settings['base_slug'], EP_ROOT);
	}

	public function load_template($template) {
		global $wp_query, $single_object;

		// if the correct query parameter exists, load a template
		if ( isset( $wp_query->query_vars[$this->settings['base_slug']] ) ) {
			$query_var = explode('/',$wp_query->query_vars[$this->settings['base_slug']]);

			// let's assume the first query var is the ID
			$id = $query_var[0];

			$url_parameters = array();
			if(empty($id)) {
				$url_parameters[$this->settings['list_page_settings']['page_slug'][0]] = 1;
			} else {
				// generate key/value pairs from query vars
				for($i=0; $i<sizeof($query_var); $i+=2) {
					$value = (($i+1)==sizeof($query_var)) ? null : $query_var[$i+1];
					$url_parameters[$query_var[$i]] = $value;
					set_query_var($query_var[$i], $value);
				}
			}

			// if the value of the query parameter exists, load the single template
			if( !empty($id) && !in_array(implode('-',array_keys($url_parameters)), array_keys($this->sub_pages)) ) {
				 
				// get query fields for object
				preg_match_all('/\[([\w-_]*)\]*([\w-_]*)/i', $this->settings['object_slug'], $query_fields);
				$query_keys = $query_fields[1];
				 
				// generate regex for getting the values from query var
				$values_regex = '/';
				for($i=0; $i<sizeof($query_keys); $i++ ) {
					$values_regex .= '(.*)' . $query_fields[2][$i];
				}
				$values_regex .= '/i';
				 
				// get query values for object
				preg_match_all($values_regex, $id, $query_values);
				array_shift($query_values);
				 
				// if no matches, it means it's an incorrect URL, so redirect to not found template
				if(!empty($query_values[0])) {
					$query_values = array_map(function($val) { return $val[0]; }, $query_values);

					// create array of query parameters
					$query_parameters = array_combine($query_keys, $query_values);

					// get object based on query parameters
					$object_query = new \SimplrWP\Core\ObjectQuery($this->settings['object']);
					$query_args = array( 'relation' => 'AND' );
					foreach($query_parameters as $key => $value) {
						$query_args[] = array(
								'key' => $key,
								'value' => $value
						);
					}

					// does this object exist in the db?
					$results = $object_query->query(array('where_args' => $query_args ));
					if(!empty($results)) {
						$single_object = $this->settings['object'];
						$single_object->set_id_and_retrieve_data($results[0]['id']);

						// run the base slug hook before loading the template
						do_action('before_single_template_' . $this->settings['base_slug'], $this );

						// return a single sub page template, if exists
						if(sizeof($query_var)>1) {
							if(isset($this->settings['single_template_sub_pages'][$query_var[1]])) {
								$this->_set_wp_page_template($this->settings['single_template_sub_pages'][$query_var[1]]);
								return $this->settings['single_template_sub_pages'][$query_var[1]];
							}
						}
						// else just return the single template
						$this->_set_wp_page_template($this->settings['single_template_file']);
						return $this->settings['single_template_file'];
					}
				}
				// object not found, return not found template
				$this->_set_wp_page_template($this->settings['object_not_found_template_file']);
				return $this->settings['object_not_found_template_file'];
				 
				// since this is a sub page, render accordingly
			} else {
				$single_object = $this->settings['object'];
				$sub_page_template = $this->_render_sub_page($url_parameters);
				$this->_set_wp_page_template($sub_page_template);
				return $sub_page_template;
			}
		}
		return $template;
	}

	public function generate_object_permalink($id = null) {
		if($id) {
			$current_object = $this->settings['object'];
			$current_object->set_id_and_retrieve_data($id);
				
			// get object fields from object slug
			preg_match_all('/\[([\w-_]*)\]*([\w-_]*)/i', $this->settings['object_slug'], $object_matches);
			$object_fields = $object_matches[1];

			// generate regex for getting the values from query var
			$object_url = $this->settings['base_slug'] . '/';
			for($i=0; $i<sizeof($object_fields); $i++ ) {
				$object_slug_part = ($object_fields[$i] == 'id') ? $current_object->get_id() : $current_object->fields[$object_fields[$i]]->get_value();
				$object_url .= strtolower($object_slug_part) . $object_matches[2][$i];
			}

			return home_url($object_url . '/');
		}
		return false;
	}

	public function add_sub_page($settings = array()) {
		// update sub page settings
		$this->sub_pages[implode('-',$settings['slug_keys'])] = array_replace_recursive($this->sub_page_settings, $settings);
	}

	public function before_single_front_end_template($callback) {
		add_action('before_single_template_' . $this->settings['base_slug'], $callback, 10, 1);
	}

	public function before_sub_page_front_end_template($slug_keys = array(), $callback) {
		add_action('before_single_template_' . $this->settings['base_slug'] . '_' . implode('_', $slug_keys), $callback, 10, 1);
	}

	private function _set_wp_page_template($template = null) {
		global $simplrwp_template;
		if($template) {
			$simplrwp_template = $template;
			add_filter('page_template', function($template) { global $simplrwp_template; return $simplrwp_template; });
		}
	}

	private function _render_sub_page($url_parameters = array()) {
		global $object_collection;

		if(!empty($url_parameters)) {
			// get call back for generating the query args
			$query_settings = $this->sub_pages[implode('-',array_keys($url_parameters))]['prepare_query_callback'];
				
			$object_query = new \SimplrWP\Core\ObjectQuery($this->settings['object']);
				
			$query_results = $object_query->query(array_replace_recursive($this->sub_page_query_settings, $query_settings($url_parameters)) );
			
			if($this->settings['list_page_settings']['objects_per_page']) {
				$this->pagination_params = array(
					'total_pages' => ceil($object_query->total_number_of_last_query_objects()/$this->settings['list_page_settings']['objects_per_page']),
					'current_page' => $url_parameters['page']
				);
			}
			
			// create objects for each result
			$object_collection = array();
			foreach($query_results as $key => $current_object) {
				$new_object = $this->settings['object']->get_unique_name();
				$object_collection[$key] = new $new_object($current_object['id']);
			}
				
			// run the sub page hook before loading the template
			do_action('before_single_template_' . $this->settings['base_slug'] . '_' . implode('_', array_keys($url_parameters)), $this );
				
			// return the associated list template
			return $this->sub_pages[implode('-',array_keys($url_parameters))]['template_file'];
		}
		return $this->settings['object_not_found_template_file'];
	}
	
	public function get_pagination_params() {
		return $this->pagination_params;
	}
}
