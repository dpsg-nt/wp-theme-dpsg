<?php
/**
 * Abstraction on an uploaded file(element of the $_FILES array)
 */
class Carbon_FileUpload {
	/**
	 * The original name of the file on client's machine
	 */
	protected $name;

	/**
	 * Temporary file name
	 */
	protected $tmp_name;

	/**
	 * Size in bytes
	 */
	protected $size;

	/**
	 * Mime Type
	 */
	protected $type;

	/**
	 * Error code, 
	 */
	protected $error;

	/**
	 * Build an object from $_FILES array element
	 */
	static function make($file) {
		return new self(
			$file['name'],
			$file['tmp_name'],
			$file['type'],
			$file['size'],
			$file['error']
		);
	}

	public function __construct($name, $tmp_name, $type, $size, $error) {
		$this->name = $name;
		$this->tmp_name = $tmp_name;
		$this->type = $type;
		$this->size = $size;
		$this->error = $error;
	}

	/**
	 * Try to read the MIME type without relying on the user provided information
	 * and when that's not possible, get whatever is in $_FILES['file']['type']
	 */
	public function get_mime_type() {
		if (function_exists('finfo_open')) {
			$fih = finfo_open(FILEINFO_MIME_TYPE);
			return finfo_file($fih, $this->tmp_name);
		} else if (function_exists('mime_content_type')) {
			return mime_content_type($this->tmp_name);
		} else {
			return $this->type;
		}
	}

	public function is_uploaded() {
		if ($this->tmp_name) {
			return true;
		}
		return false;
	}

	function getSize() {
		return $this->size;
	}
}
