<?php 
namespace SimplrWP\FrontEnd;

class General {
	
	protected $url_routes = array();
	
	protected $default_url_route_settings = array(
		'template_file' => '',
		'route' => 'simplrwp',
		'base_route' => 'simplrwp'
	);
	
	public function __construct() {
		// register new rewrite endpoints
		add_action('init', array($this, 'add_rewrite_rules') );
		
		// register template include based on rewrite rules
		add_filter('template_include', array($this, 'load_template') );
	}

	public function add_url_route($settings = array()) {
		// load settings with defaults (if necessary)
		$settings += $this->default_url_route_settings;
		
		if(preg_match("/^[a-z0-9\/\-\_]+$/i", $settings['route'])==0) {
			trigger_error('The route [' . $settings['route'] . '] is not a valid route. Routes can have A-Z, 0-9, and the characters /, -, or _. Route not added!', E_USER_WARNING);
			return;
		}
		$route_depths = explode('/', $settings['route']);
		
		// let's determine the base route
		$base_route = array_shift($route_depths);
		
		// let's create the sub route (if exists)
		$sub_route = sizeof($route_depths)==0 ? '' : implode('/',$route_depths);
		
		// add route to available routes to be rendered
		$this->url_routes[$base_route][$sub_route] = $settings;
	}
	
	public function add_rewrite_rules() {
		foreach($this->url_routes as $base_route => $url_route){
			add_rewrite_endpoint($base_route, EP_ROOT);
		}
	}
	
	public function load_template($template) {
		global $wp_query;
		
		foreach($this->url_routes as $base_route => $url_route){
			// is the base route in the URL?
			if(in_array($base_route, array_keys($wp_query->query_vars))) {
				// is the remaining route in the URL?
				if(isset($this->url_routes[$base_route][$wp_query->query_vars[$base_route]])) {
					$settings = $this->url_routes[$base_route][$wp_query->query_vars[$base_route]];
					
					return $settings['template_file'];
				}
			}
		}
		
		return $template;
	}
}