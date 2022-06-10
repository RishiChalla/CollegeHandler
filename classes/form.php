<?php

/** 
 * Processes a single form
 */
class Form {
	/** The method of retrieving form data (GET/POST) */
	private $method;

	/** Array of inputs to process */
	public $inputs;

	/** The submition input */
	public $submit;

	/** Creates a new form handler */
	function __construct($method = "GET", $inputs = [], $submit = "") {
		$this->method = $method;
		$this->inputs = $inputs;
		$this->submit = $submit;
	}

	/** 
	 * Creates the form with an HTML template
	 * 
	 * Include a string of HTML and use ${field} to indicate which field to use where
	 */
	function createForm($template) {
		foreach ($this->inputs as $input) {
			if (!$input->render) continue;
			echo preg_replace_callback('/\${(.*?)}/', function($match) use($input) {
				return $input->{$match[1]};
			}, $template);
		}
	}

	/** 
	 * Similar to create form but returns the string
	 */
	function template($template) {
		foreach ($this->inputs as $input) {
			if (!$input->render) continue;
			echo preg_replace_callback('/\${(.*?)}/', function($match) use($input) {
				return $input->{$match[1]};
			}, $template);
		}
	}

	/** Gets an input's value */
	function getRawValue($inputTitle) {
		foreach ($this->inputs as $input) {
			if ($input->name == $inputTitle) {
				return $input->data;
			}
		}
	}

	/** Gets an input's value */
	function getValue($inputTitle) {
		foreach ($this->inputs as $input) {
			if ($input->name == $inputTitle) {
				return $input->getValue();
			}
		}
	}

	/**
	 * Updates the FormInput data IF the form has already been submitted
	 * returns an array of error strings
	 */
	function updateData() {
		if ($this->method == "POST" && !isset($_POST[$this->submit])) return;
		if ($this->method == "GET" && !isset($_GET[$this->submit])) return;

		$errors = [];

		foreach ($this->inputs as $input) {
			if ($this->method == "POST") $data = isset($_POST[$input->name]) ? $_POST[$input->name] : null;
			else $data = isset($_GET[$input->name]) ? $_GET[$input->name] : null;
			if ($response = $input->setData($data)) array_push($errors, $response);
		}

		return $errors;
	}

	/**
	 * Creates an SQL query from a given template
	 * 
	 * Use ${name} inside the sql command to indicate spaces to replace with data
	 * For example:
	 * SELECT * FROM `table` WHERE title LIKE %${title}%
	 */
	function sql($sql) {
		global $db;
		return preg_replace_callback('/\${(.*?)}/', function($match) use($db) {
			$found = false;
			$data = null;
			foreach ($this->inputs as $input) {
				if ($input->name == $match[1]) {
					$found = true;
					$data = $input->getValue();
					break;
				}
			}
			if (!$found) die("Did not find input named ".$match[1]);
			return "'".mysqli_real_escape_string($db, $data)."'";
		}, $sql);
	}

	/**
	 * Creates an SQL query from a given template and executes it returning the mysqli_query() result
	 * 
	 * Use ${name} inside the sql command to indicate spaces to replace with data
	 * For example:
	 * SELECT * FROM `table` WHERE title LIKE %${title}%
	 */
	function process($sql) {
		global $db;
		$query = $this->sql($sql);
		return mysqli_query($db, $query);
	}
}

?>