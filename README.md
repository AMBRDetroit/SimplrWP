## SimplrWP - Latest Release v2.1.2
A developer's framework for WordPress.

# Overview
 The core behind SimplrWP is the SimplrWP Object.  This is similar to WordPress's Custom Post Type (CPT), but with more flexibility (think ACF) and control over the database structure, how they work within the WordPress Admin Dashboard, and how the object's data is validated. 
 
 The framework allows you to create your own database tables, provide validation on the data going into the database and utilize WordPress' amazing content management dashboard to manage the content.
 
 It's important to understand how SimplrWP works.  SimplrWP allows you to create objects within the WordPress ecosystem.  These objects, by default, do not have any interface (in the theme or in wp-admin).  Once the object is created, you can create the wp-admin interface or the theme interface to these object. The beautiful thing is, the two user interfaces are independent of each other and you can enable either or both.
 
 In short here's how you use and interact with SimplrWP:
 1. Create a SimplrWP object by extending core SimplrWP Object
 2. Add fields (attributes) to the object
 3. Add a wp-admin interface to the objects (optional)
 4. Add a front-end interface to the objects with routing and templates (optional)
 
**Important: This project is still in it's early stages and isn't ready for production sites yet**
  
# Quick Example
 
 First include the library in your theme's functions.php file or in a plugin file:
 ```php
 require_once('SimplrWP/init.php');
 ```
 
 Next let's create a SimplrWP object:
 ```php
   class Sample_Author extends SimplrWP\Core\SObject {
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
 ```
 
 As you can see, when you create a new object you can setup the attributes (fields)
 for the object.  Each field uses a SimplrWP Field type.  SimplrWP includes a set of default
 field types, but you can easily extend the platform to support your own field type.
 
 Currently supported field types include:
 - Checkbox
 - Custom Post Type (in progress)
 - File Upload
 - Radio
 - Repeater
 - Select
 - SimplrWP Object (relational)
 - Text
 - Textarea
 - WPEditor
 - WPMediaUploader
 - Raw
 - Encrypted
 
Now that you have the object, we can create interfaces to the object.
 
 Here's how to create an WP-Admin interface to the object:
 ```php
 add_action( 'init', function() {
	// Simplr Front End
	register_sample_author_front_end();
	
    // Simplr WP Admin
    if(is_admin()) {
    	register_sample_author();
    }
 }, 1 );

 function register_resource_document_admin() {

	$wp_admin = new SimplrWP\WPAdmin\Admin(array(
		'object' => new Sample_Author,
		'icon' => 'dashicons-groups',
		'capability' => 'manage_options',
		'admin_list' => array(
			'primary_field' => 'first_name',
			'fields' => [
				'first_name' => [
					'label' => 'Name',
					'value' => function($field) {
						return $field->render_value();
					}
				]
			]
		),
		
	));

	$wp_admin->register_metabox(array(
		'id' => 'author',
		'label' => 'Author',
		'context' => 'normal',
		'editable_fields' => array('first_name', 'last_name')
	));
	
 }
 ```
 
 Here's how to create routing and set theme templates for the object:
 ```php
 function register_sample_author_front_end() {
	global $author_front_end;
	
	$sample_author_front_end = new SimplrWP\FrontEnd\FrontEnd(array(
			'object' => new Sample_Author,
			'base_slug' => 'authors',
			'list_order_by' => 'last_name',
			'object_slug' => '[id]',
			'single_template_file' => SOME_THEME_PATH . '/simplrwp_object_templates/authors/single.php',
			'list_page_settings' => array(
				'objects_per_page' => 5,
				'template_file' => SOME_THEME_PATH . '/simplrwp_object_templates/authors/list.php'
			)
	));
	
 ```
 
 You can also add multiple subpages to access the objects.  Here's an example of a sub page:
 ```php
	$sample_author_front_end->add_sub_page(array(
			'template_file' => SOME_THEME_PATH . '/simplrwp_object_templates/authors/last_name_starts_with.php',
			'slug_keys' => array('starts_with','page'),
			'prepare_query_callback' => function($query_params) {
				return array(
					'order_by' => 'last_name',
					'where_args' => array(
						array(
							'key' => 'last_name',
							'value' => $query_params['starts_with'],
							'compare' => 'BEGINS WITH'
						)
					)
				);
			}
	));
 ```

 Finally, now that you've created a SimplrWP object, it's easy to use it.
 
 Here's an example of how to use your new object:
 ```php
 $new_author = new Sample_Author();
 ```
 If you're trying to load an instance of the object from the database, you can instantiate
 the object like this:
 ```php
 $current_author = new Sample_Author(1); //pass in the ID of the object
 ```
 
 To access a field from the object:
 ```php
 $author_first_name = $current_author->get_field('first_name');
 ```
 
 This is just a short introduction to SimplrWP.  
 
 **More documentation and examples to come!**
 
