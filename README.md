## SimplrWP
A developer's framework for WordPress.

# Overview
 The core behind SimplrWP is the SimplrWP Object.  This is similar to WordPress's Custom Post Type (CPT), but with more flexibility and control over the database structure, how they work within the WordPress Admin Dashboard, and how the object's data is validated. 
 
 The framework allows you to create your own database tables, provide validation on the data going into the database and utilize WordPress' amazing content management dashboard to manage the content.
 
 This object serves as a base and can be extended to create more complex objects for your project. By extending this class you can create virtually any time of object. While the object can be used directly, it is not recommended.
 
 **Important: This project is still in it's early stages and isn't ready for production sites yet**
  
 # Examples
 
 Here's an example on how you can extend this object to create your own:
 ```php
   class Sample_Author extends SimplrWP\Core\Object {
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
 
 Once you've created your new object class, it's easy to use it.
 
 Here's an example of how to use your new object:
 ```php
 $new_author = new Sample_Author();
 ```
 If you're trying to load an instance of the object from the database, you can instantiate
 the object like this:
 ```php
 $current_author = new SampleAuthor(1); //pass in the ID of the object
 ```
 
