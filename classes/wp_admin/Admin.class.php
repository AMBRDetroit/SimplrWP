<?php 
namespace SimplrWP\WPAdmin;

class Admin {
	
	protected $options = array(
		'object' => null,
		'is_manageable' => true,
		'capability' => 'manage_options',
		'icon' => 'dashicons-lightbulb',
		'position' => 30,
		'admin_list' => array(
			'primary_field' => null,
			'items_per_page' => 10
		)
	);
	
	protected $default_metabox_options = array(
		'id' => 0,
		'label' => 'Metabox',
		'context' => 'normal',
		'editable_fields' => array(),
		'before_fields_html_template' => '',
		'after_fields_html_template' => ''
	);
	
	protected $metaboxes = array();
	
	protected $admin_notices = array();
	
	protected $sub_menus = array();
	
	public function __construct($options = array()) {
		$this->options = array_replace_recursive($this->options, $options);
		
		add_action( 'admin_menu', array(&$this, 'render_menus') );
		
		add_action( 'add_meta_boxes', array(&$this, 'render_metaboxes') );
		
		add_action( 'admin_enqueue_scripts', array(&$this, 'load_admin_scripts'));
		
		add_action( 'admin_notices', array(&$this, 'display_admin_notices') );
		
		// delete the object
		if(isset($_GET['delete']) && !empty($_GET['id'])) {
			$this->options['object']->set_id_and_retrieve_data($_GET['id']);
			if($this->options['object']->delete()) {
				wp_redirect($_SERVER['PHP_SELF'] . '?page=' . $this->options['object']->get_unique_name() . '&deleted');
			}
		}
		
		// update/create the object
		if(!empty($_POST)) {
			if($this->save_data($_POST)) {
				wp_redirect($_SERVER['PHP_SELF'] . '?page=' . $this->options['object']->get_unique_name() . '&id=' . $this->options['object']->get()['id'] . '&updated');
			}
		}
		if($this->options['object'] && isset($_GET['page'])) {
			if(isset($_GET['updated']) && $_GET['page'] == $this->options['object']->get_unique_name()) {
				$this->admin_notices['success'][] = $this->options['object']->get_labels()['singular'] . ' successfully saved!';
			}
			if(isset($_GET['deleted']) && $_GET['page'] == $this->options['object']->get_unique_name()) {
				$this->admin_notices['success'][] = $this->options['object']->get_labels()['singular'] . ' successfully deleted!';
			}
		}
		$this->register_metabox(array(
			'id' => 'submitdiv',
			'label' => 'Save ' . $this->options['object']->get_labels()['singular'],
			'context' => 'side',
			'before_fields_html_template' => SIMPLRWP_PATH . 'templates/wp_admin/save_metabox.php'
		));
	}
	
	public function display_admin_notices() {
		foreach($this->admin_notices as $notice_type => $message) {
			$class = 'is-dismissible notice notice-' . $notice_type;
			$message = __( implode('<br />', $message));
			
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}
	}
	
	private function _render_error_messages($errors) {
		$error_messages = array();
		
		foreach($errors as $field_name => $error_message_group) {
			foreach($error_message_group as $error_message) {
				$error_messages[] = str_replace('[field_name]', $this->options['object']->fields[$field_name]->get_label(), $error_message);
			}	
		} 
		return $error_messages;
	}
	
	public function load_admin_scripts() {
		wp_enqueue_style( 'simplrwp-metabox', SIMPLRWP_URL . 'assets/css/simplrwp-metabox.css' );
		wp_enqueue_script( 'post' );
	}
	
	public function add_sub_menu($sub_menu = null) {
		$this->sub_menus[$sub_menu['id']] = $sub_menu;
		
	}
	
	public function register_metabox($options = array()) {
		if(isset($options['id']) && isset($options['label']) && isset($options['context'])) {
			$this->metaboxes[] = array_merge($this->default_metabox_options, $options);
			return true;
		}
		return false;
	}
	
	public function render_metaboxes() {
		// render all registered metaboxes
		foreach($this->metaboxes as $metabox) {
			add_filter('postbox_classes_' . $this->options['object']->get_unique_name() . '_' . $metabox['id'], array(&$this, 'add_metabox_classes') );
			
			add_meta_box(
				$metabox['id'],
				$metabox['label'],
				array(&$this, 'render_metabox_content'),
				$this->options['object']->get_unique_name(),
				$metabox['context'],
				'default',
				$metabox
			);
		}
	}
	
	public function render_metabox_content( $object, $box ) {
		wp_nonce_field(basename(__FILE__), $this->options['object']->get_unique_name() . '-nonce');
		// load a html template before editable fields
		if(!empty($box['args']['before_fields_html_template'])) {
			include $box['args']['before_fields_html_template'];
		}
		// if there are editable fields, load them
		foreach($box['args']['editable_fields'] as $field) {
			if(array_key_exists($field, $this->options['object']->fields)) {
				$this->options['object']->fields[$field]->wp_admin_render_field();
			}
		}
		// load a html template after editable fields
		if(!empty($box['args']['after_fields_html_template'])) {
			include $box['args']['after_fields_html_template'];
		}
	}
	
	public function add_metabox_classes($classes) {
		array_push($classes,'simplrwp-metabox');
		return $classes;
	}
	
	public function render_menus() {
		add_menu_page( $this->options['object']->get_labels()['plural'], $this->options['object']->get_labels()['plural'], $this->options['capability'], $this->options['object']->get_unique_name(), array(&$this, 'list_objects_callback'), $this->options['icon'], $this->options['position'] );
		
		foreach($this->sub_menus as $sub_menu) {
			add_submenu_page(
				$this->options['object']->get_unique_name(),
				$sub_menu['label'],
				$sub_menu['label'],
				'manage_options',
				$sub_menu['id'],
				array(&$this, 'render_sub_menu') );
		}
	}
	
	public function render_sub_menu() {
		// render submenu template
		include $this->sub_menus[$_GET['page']]['template'];
	}
	
	public function get_admin_unique_name() {
		return $this->options['object']->get_unique_name();
	}
	
	private function save_data($data = array()) {
		// protect against CSRF
		$nonce_id = $this->options['object']->get_unique_name() . '-nonce';
		if (!isset($_POST[$nonce_id]) || !wp_verify_nonce($_POST[$nonce_id], basename(__FILE__)))
			return false;
		
		// first, let's get the current data
		if(!empty($data['id']))
			$this->options['object']->set_id_and_retrieve_data($data['id']);
		
		// all looks good, let's attempt to save the data
		$results = $this->options['object']->update($data);
		if(isset($results['valid']) && !$results['valid']) {
			$this->admin_notices['error'] = $this->_render_error_messages($results['errors']->errors);
		}
		return $results['valid'];
	}
	
	public function list_objects_callback() {
		$object_admin_list = new ObjectList($this->options['admin_list']);
		$object_admin_list->prepare_items(new \SimplrWP\Core\ObjectQuery($this->options['object']));
		
		//include the admin header HTML
		include SIMPLRWP_PATH .'templates/wp_admin/admin_head.php';
		
		if(isset($_GET['id'])) {
			//get object details
			$this->options['object']->set_id_and_retrieve_data($_GET['id']);
			//include the body of editable object
			include SIMPLRWP_PATH . 'templates/wp_admin/manage_object.php';
		} else {
			//include the body of showing the list of objects
			include SIMPLRWP_PATH . 'templates/wp_admin/list_objects.php';
		}
		
		//include the admin footer HTML
		include SIMPLRWP_PATH . 'templates/wp_admin/admin_foot.php';
	}	
}
?>