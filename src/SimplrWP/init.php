<?php
/*
 Name: SimplrW
 Description: This is a PHP framework built on WordPress for more advanced "CPT-type" objects and interfaces.
 Version:     2.1.1
 */

// if already loaded, exit
if(defined('SIMPLRWP_PATH'))
	return;

// initialize for global polling
global $available_simplrwp_objects;
$available_simplrwp_objects = array();

// global path to SimplrWP
define( 'SIMPLRWP_PATH', dirname(__FILE__) . '/' );
// global URL to SimplrWP
define( 'SIMPLRWP_URL', plugins_url( '/', __FILE__) . '/' );

// load core classes
require_once 'classes/core/Object.class.php';
require_once 'classes/core/ObjectQuery.class.php';
require_once 'classes/core/Validator.class.php';

// load fields
require_once 'classes/fields/Field.class.php';

require_once 'classes/fields/Raw.class.php';
require_once 'classes/fields/Encrypted.class.php';
require_once 'classes/fields/Text.class.php';
require_once 'classes/fields/TextArea.class.php';
require_once 'classes/fields/Select.class.php';
require_once 'classes/fields/Radio.class.php';
require_once 'classes/fields/Checkbox.class.php';
require_once 'classes/fields/WPEditor.class.php';
require_once 'classes/fields/WPMediaUploader.class.php';
require_once 'classes/fields/Repeater.class.php';
require_once 'classes/fields/FileUpload.class.php';
require_once 'classes/fields/SimplrWPObject.class.php';

// load third party integrations
require_once 'third_party/acf/simplrwp_object.php';

// only load wp-admin objects if user is an admin
if(is_admin()) {
	require_once 'classes/wp_admin/Admin.class.php';
	require_once 'classes/wp_admin/ObjectList.class.php';
}

// load front end objects
require_once 'classes/front_end/FrontEnd.class.php';
require_once 'classes/front_end/General.class.php';


/****************************************
 * Automatic Documentation Generator
 * ======================================
 * 
 * Documentation can automatically be generated using the APIGen tool.
 * 
 * To regenerate SimplrWP's documentation, open your terminal and
 * navigate to the SimplrWP path.  Next, run the following command in
 * your terminal:
 * 
 * php apigen.phar generate -s ./  -d ./docs --template-theme bootstrap
 * 
 * To view the documentation, navigate to the SimplrWP/documentation in your browser.
 * 
 * When documenting, you can use Markdown for code samples, tables, etc.  Here's a 
 * Markdown cheatsheet: https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet
 * 
 * That's it!
 * 
 ****************************************/
?>
