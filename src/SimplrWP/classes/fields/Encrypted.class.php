<?php 
namespace SimplrWP\Fields;
/**
 * ## Overview
 * The Encypted field uses Defuse PHP to encrypt the data before stored in the DB
 * 
 * IMPORTANT: Just because this data is stored at rest, encrypted, it does not mean it is secure!
 * Please be mindful of all in transit, in motion activities around this field's value.
 *
 * ## Examples
 *
 * Here's an example on how you create a Encrypted field:
 * ```php
 *```
 */
class Encrypted extends Field {
	
	protected $key = false;
	
	public function __construct($options =  array()) {
		// let's load the defuse library
		require_once(SIMPLRWP_PATH . '/third_party/defuse-php/defuse-crypto.phar');
		
		$this->key = $this->_get_crypto_key();
		
		parent::__construct($options);
	}
	
	public function wp_admin_render_field() {
		$required = $this->is_required() ? '<span style="color:red"> *</span>' : '';
		echo '<div class="field">';
			echo '<label class="simplrwp--label">' . $this->get_label() . $required . '</label>';
			if($this->settings['read_only']){
				echo '<p>' . $this->render_value() . '</p>';
			}else{
				echo '<textarea class="textarea" name="' . $this->get_name() . '">'. $this->get_value() .'</textarea>';
			}
		echo '</div>';
	}
	
	public function unprepare_db_value($value) {
		$this->settings['value'] = $this->_decrypt_value($value);
	}
	
	public function prepare_db_value($value) {
		return $this->_encrypt_value($value);
	}
	
	private function _encrypt_value($value) {
		return \Defuse\Crypto\Crypto::encrypt( $value, $this->key);
	}
	
	private function _decrypt_value($value) {
		try {
			return \Defuse\Crypto\Crypto::decrypt( $value, $this->key);
		} catch ( WrongKeyOrModifiedCiphertextException $e) {
			return $value;
		}
	}
	
	private function _get_crypto_key() {
		// is a key path defined?
		if($key_path = $this->_get_key_file_path()) {
			// if no directory, create it
			if(!file_exists($key_path)) {
				mkdir($key_path);
				// create htaccess file protecting access
				$htaccess_file = fopen($key_path . '.htaccess', 'w');
				fwrite($htaccess_file, 'Deny from all');
				fclose($htaccess_file);
			}
			
			// let's check if file already exists
			$key_file_path = $key_path . 'key.php';
			if(!file_exists($key_file_path)) {
				// generate the key
				$new_key = \Defuse\Crypto\KeyProtectedByPassword::createRandomPasswordProtectedKey(SECURE_AUTH_KEY);
				
				// save the key in a php file
				$key_file_buffer = fopen($key_file_path, 'w');
				fwrite($key_file_buffer, sprintf('<?php $simplrwp_encrypt_key_string=\'%s\';', $new_key->saveToAsciiSafeString()));
				fclose($key_file_buffer);
				
				// only read and execute by the owner, no other access
				chmod($key_file_path, 0500);
			}
			
			require($key_file_path);
			
			$locked_key = \Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($simplrwp_encrypt_key_string);
			
			return $locked_key->unlockKey(SECURE_AUTH_KEY);
		} 
		
		return false;
	}
		
	private function _get_key_file_path() {
		if(defined('SIMPLRWP_ENCRYPTED_KEY_PATH')) {
			return SIMPLRWP_ENCRYPTED_KEY_PATH;
		}
		return false;
	}
}