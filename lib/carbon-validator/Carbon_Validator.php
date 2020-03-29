<?php
/**
 * Validate array of data against set of rules
 */
class Carbon_Validator {
	/**
	 * Data to be validated. 
	 */
	private $data = array();

	/**
	 * The initial validation rules. 
	 */
	private $rules = array();

	/**
	 * Custom error messages.
	 */
	private $custom_messages = array();

	/**
	 * Normalized validation rules array. 
	 */
	private $parsed_rules = array();

	/**
	 * 2 dimensional array with error messages. 
	 */
	private $error_messages = array();

	/**
	 * Whether the data has been validated already. 
	 */
	private $validation_performed = false;

	/**
	 * Array with mime types for commonly-used formats.
	 *
	 * @see https://codex.wordpress.org/Function_Reference/get_allowed_mime_types
	 */
	private static $mime_types = array();

	/**
	 * Custom validation rules. 
	 */
	public static $custom_rules = array();

	/**
	 * Holds stored instances.
	 */
	public static $store = array();

	/**
	 * Store a validator instance.
	 */
	public static function store($id, Carbon_Validator $validator) {
		self::$store[$id] = $validator;
	}

	/**
	 * Load a validator instance.
	 */
	public static function load($id) {
		return isset(self::$store[$id]) ? self::$store[$id] : null;
	}

	/**
	 * Register a custom validation rule. 
	 */
	public static function extend($rule_name, $callback) {
		if (!is_callable($callback)) {
			throw new Carbon_Validator_InvalidSyntax("Second argument of " .
				"Carbon_Validator::extend() is not callable.");
		}

		self::$custom_rules[$rule_name] = $callback;
	}

	/**
	 * Remove a custom validation rule. 
	 */
	public static function unextend($rule_name) {
		unset(self::$custom_rules[$rule_name]);
	}

	/**
	 * Construct the validator object. 
	 */
	function __construct($data, $rules, $custom_messages=array()) {
		$this->rules = $rules;
		$this->data = $data;
		$this->custom_messages = $custom_messages;

		// Load the mime types just once
		if (empty(self::$mime_types)) {
			self::$mime_types = get_allowed_mime_types();
		}
	}

	/**
	 * Whether the data is valid according to the rules. 
	 */
	public function passes() {
		$this->perform_validation();
		return !$this->has_errors();
	}

	/**
	 * Whether the data is invalid according to the rules. 
	 */
	public function fails() {
		return !$this->passes();
	}

	/**
	 * When $field is provided, all errors for that
	 * particular field are returned. 
	 * 
	 * When $field is not provided, a hash with errors(as values)
	 * and associated fields(as keys) is returned
	 */
	public function get_errors($field=null) {
		if (is_null($field) && !empty($this->error_messages)) {
			$errors = array();

			foreach ($this->error_messages as $field => $errors_stack) {
				$errors[$field] = $errors_stack[0];
			}

			return $errors;
		}

		return isset($this->error_messages[$field]) ? $this->error_messages[$field] : array(); 
	}

	public function get_all_errors() {
		$errors = array();

		foreach ($this->error_messages as $field => $errors_stack) {
			$errors = array_merge($errors, $errors_stack);
		}

		return $errors;
	}

	/**
	 * Return 2 dimensional array where keys are field names and values are array with errors. 
	 */
	public function get_errors_by_field() {
		return $this->error_messages();
	}

	/**
	 * Return array with all fields with error. 
	 */
	public function get_fields_with_error() {
		return array_keys($this->error_messages);
	}

	/**
	 * Return true if the field is invalid. 
	 */
	public function has_errors($field=null) {
		$errors = $this->get_errors($field);
		return count($errors) > 0;
	}

	/**
	 * Return first error for particular field, or for all fields when no first argument is passed. 
	 */
	public function first_error($field=null) {
		$errors = $this->get_all_errors($field);
		if (isset($errors[0])) {
			return $errors[0];
		}
		return null;
	}
	/* Internal methods */

	/**
	 * Goes through the validation rules and builds the array
	 * errors message array.  
	 */
	protected function perform_validation() {
		if ($this->validation_performed) {
			return;
		}
		
		$this->parse_rules();
		foreach ($this->parsed_rules as $field => $rules) {
			foreach ($rules as $rule_name => $rule_params) {
				$callback = $this->get_rule_callback($rule_name);
				$value = $this->get_field_value($field);

				// Perform the validation. For built-in rules, there are no exceptions,
				// but for custom rules it's easier to throw & catch the validation error. 
				try {
					$result = call_user_func($callback, $value, $rule_params);

					if (!$result) {
						$error = $this->get_error_message($field, $rule_name, $value, $rule_params);
						$this->error_messages[$field][] = $error;
					}
					
				} catch(Carbon_Validator_ValidationError $e) {
					// Custom validation rules are throw in exceptions 
					$this->error_messages[$field][] = $e->getMessage();
				}
			}
		}

		// Mark input data as validated(so the validation isn't performed again)
		$this->validation_performed = true;
	}

	/**
	 * Determinate the validation rule callback: it might be either
	 * a custom validation rule or built-in validation
	 */
	protected function get_rule_callback($rule_name) {
		if (isset(self::$custom_rules[$rule_name])) {
			return self::$custom_rules[$rule_name];
		}

		$method_name = 'validate_' . $rule_name;
		if (!method_exists($this, $method_name)) {
			throw new Carbon_Validator_InvalidSyntax("Unknown validation rule: $rule_name");
		}

		return array($this, $method_name);
	}

	/**
	 * Get the value of the field under validation. Make sure that 
	 * we're not causing PHP errors if the value is not present in
	 * the input data. 
	 */
	protected function get_field_value($field) {
		// Whenever the field name is passed in format like 
		// user[name][first] we need to find the path in the 
		// array.
		$path = array_filter(preg_split('~\]\[|\[|\]~', $field));

		// Make a local reference to work with the data. 
		$data = $this->data;

		// Go through the array keys deeply
		while (count($path) > 0) {
			$next_key = array_shift($path);

			if (isset($data[$next_key])) {
				$data = $data[$next_key];
			} else {
				// There isn't a field with this name in the input data
				return null;
			}
		}

		return $data;
	}

	/**
	 * Normalize validation rules provided by the user, so the validation
	 * code could work with them in consistent way. 
	 */
	protected function parse_rules() {
		foreach ($this->rules as $field => $rules) {
			if (is_string($rules)) {
				$rules = explode('|', $rules);
			}
			foreach ($rules as $rule) {
				// split by the first colon only. For example in regexes there might be
				// more than one colon, all after the first should be left as part of the regex. 
				$rule_parts = explode(':', $rule, 2);
				$rule_name = $rule_parts[0];

				if (isset($rule_parts[1])) {
					if ($rule_name !== 'regex') {
						$rule_params = explode(',', $rule_parts[1]);
					} else {
						$rule_params = array($rule_parts[1]);
					}

				} else {
					$rule_params = array();
				}

				$this->parsed_rules[$field][$rule_name] = $rule_params;
			} 
		}
	}

	protected function name_to_label($field_name) {
		return ucwords(preg_replace('~[\-_]~', " ", $field_name));
	}

	/**
	 * Returns an error message for particular field and validation rule
	 * @param  string  $fields  the name of the field in the input
	 * @param  string  $rule  	the name of the rule that has failed
	 * @param  string  $value  	the value of the field
	 * @param  string  $value  	additional
	 */
	protected function get_error_message($field, $rule, $value, $params) {
		if (isset($this->custom_messages["$field.$rule"])) {
			return $this->custom_messages["$field.$rule"];
		}

		$field_label = $this->name_to_label($field);
		
		switch ($rule) {
			case "required":
				return sprintf( __("The field %s is required. ", 'crb'), $field_label );

			case "email":
				return sprintf( __("Please enter valid email address in the %s field. ", 'crb'), $field_label );

			case "email_exists":
				return sprintf( __("The email address \"%s\" is already registered. Please enter a different email in the %s field. ", 'crb'), $value, $field_label );

			case "user_exists":
				return sprintf( __("The username \"%s\" is already registered. Please enter a different username in the %s field. ", 'crb'), $value, $field_label );

			case "regex":
				return sprintf( __("The field %s doesn't match the required format.", 'crb'), $field_label );

			case "numeric":
				return sprintf( __("Please enter numeric value in %s field. ", 'crb'), $field_label );

			case "string_min_length":
				return sprintf( __("Please enter at least %d characters in %s field. ", 'crb'), $params[0], $field_label );

			case "string_max_length":
				return sprintf( __("Please enter less than %d characters in %s field. ", 'crb'), $params[0], $field_label );

			case "numeric_min":
				return sprintf( __("Please enter number greater than %d in %s field. ", 'crb'), $params[0], $field_label );

			case "numeric_max":
				return sprintf( __("Please enter number lower than %d in %s field. ", 'crb'), $params[0], $field_label );

			case "url":
				return sprintf( __("Please valid URL in %s field. ", 'crb'), $field_label );

			case "file":
				if (empty($params)) {
					return sprintf( __("Please upload file in %s field. ", 'crb'), $field_label );
				} else {
					$formats = implode(', ', array_map('strtoupper', $params));
					return sprintf( __("Please upload file with %s format in %s field. ", 'crb'), $formats, $field_label );
				}

			case "filesize":
				return sprintf( __("Please upload file smaller than %s in %s field. ", 'crb'), $params[0], $field_label );

			case "confirmed":
				return sprintf( __("Confirmation for %s field isn't correct. ", 'crb'), $field_label );

			default:
				return sprintf( __("Invalid value for %s field. ", 'crb'), $field_label );
		}
	}

	protected function is_file($value) {
		return $value instanceof Carbon_FileUpload;
	}

	/**
	 * Return the mime type for a particular format (file extension). 
	 */
	protected function get_format_mime_type($format) {
		foreach (self::$mime_types as $extensions => $mime_type) {
			if (in_array($format, explode('|', $extensions))) {
				return $mime_type;
			}
		}

		return false;
	}

	/*************************************************/
	// 				Validation Methods
	/*************************************************/

	function validate_required($value) {
		if (is_null($value)) {
			return false;
		}

		if (is_string($value) && strlen(trim($value)) < 1) {
			return false;
		}

		if(is_array($value) && count($value) < 1) {
			return false;
		}

		return true;
	}

	function validate_email($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	function validate_email_exists($value) {
		return !email_exists($value);
	}

	function validate_user_exists($value) {
		return !username_exists($value);
	}

	function validate_regex($value, $params) {
		return (bool)preg_match($params[0], $value);
	}

	function validate_numeric($value) {
		return is_numeric($value);
	}

	private function _strlen($str) {
		if (function_exists('mb_strlen')) {
			return mb_strlen($str, "UTF-8");
		} else if (function_exists('iconv_strlen')) {
			return iconv_strlen($str, "UTF-8");
		} else {
			return strlen($str);
		}
	}

	function validate_string_min_length($value, $params) {
		return $this->_strlen($value) >= $params[0];
	}

	function validate_string_max_length($value, $params) {
		return $this->_strlen($value) <= $params[0];
	}

	function validate_numeric_min($value, $params) {
		return $value >= $params[0];
	}

	/**
	 * Validate numeric value to be lower than .
	 */
	function validate_numeric_max($value, $params) {
		return $value <= $params[0];
	}

	/**
	 * Validate URL address
	 */
	function validate_url($value, $params) {
		return filter_var($value, FILTER_VALIDATE_URL) !== false;	
	}

	/**
	 * Validate field that needs confirmation, usually password fields. 
	 */
	function validate_confirmed($value, $params) {
		if (!isset($params[0])) {
			throw new Carbon_Validator_InvalidSyntax("You need to provide the name of the confirmation field.");
		}
		$confirmation_field = $params[0];

		if (!isset($this->data[$confirmation_field])) {
			return false;
		}
		$confirmed_value = $this->data[$confirmation_field];

		return $value === $confirmed_value;
	}
	/**
	 * 
	 */
	function validate_file($value, $params) {
		if (!$this->is_file($value) || !$value->is_uploaded()) {
			return false;
		}
		$allowed_formats = $params;

		$allowed_mimes = array();

		foreach ($allowed_formats as $format) {
			$format = strtolower($format);
			$mime_type = $this->get_format_mime_type($format);

			if (!$mime_type) {
				throw new Carbon_Validator_InvalidSyntax("Unknown format $format. Please add the mime type for this format " .
					"using the 'upload_mimes' WordPress filter.");
			}

			$allowed_mimes[] = $mime_type;
		}

		return in_array($value->get_mime_type(), $allowed_mimes);
	}
	/**
	 * This rule will accept 3 formats:
	 *  - integer: assumed size to be in bytes
	 *  - 400K: assumed to be in kilobytes
	 *  - 4M: assumed to be in megabytes
	 */
	function validate_filesize($value, $params) {
		if (!$this->is_file($value)) {
			return false;
		}
		$max_size = $params[0];

		if (preg_match('~^(\d+)([KM]?)$~i', $max_size, $matches)) {
			$size = $matches[1];
			$size_type = strtolower($matches[2]);

			if ($size_type === "k") {
				$max_size = $size * 1024;
			} else if ($size_type === 'm') {
				$max_size = $size * 1024 * 1024;
			} else {
				$max_size = intval($size);
			}
		} else {
			throw new Carbon_Validator_InvalidSyntax("Couldn't understand max upload size: $max_size");
		}
		return $value->getSize() < $max_size;
	}

}

// Thrown when validation declaration isn't correct. 
class Carbon_Validator_InvalidSyntax extends Exception {}

// Just an empty class used for custom validation rules
class Carbon_Validator_ValidationError extends Exception {}
