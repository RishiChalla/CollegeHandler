<?php

/** 
 * Stores a single form input
 */
class FormInput {
	/** Type of input */
	public $type;

	/** The default value of the input */
	public $default;

	/** The icon for the input to use */
	public $icon;

	/** The description/placeholer of the input */
	public $description;

	/** The name of the input */
	public $name;

	/** The data held by the input */
	public $data;

	/** The title of the input */
	public $title;

	/** Whether or not to render the form input */
	public $render = true;

	/** Creates a new Form Input */
	function __construct($title, $default, $icon, $description, $name, $type, $render = true) {
		$this->type = $type;
		$this->default = $default;
		$this->icon = $icon;
		$this->description = $description;
		$this->name = $name;
		$this->title = $title;
		$this->render = $render;
	}

	/** Validates and sets the data */
	function setData($data) {
		if (!isset($data) || empty($data))
			return "Please fill out the ".$this->title;
		$this->data = $data;
		return null;
	}

	/** Returns the data */
	function getValue() {
		return $this->data;
	}
}

class FormTextInput extends FormInput {
	/** the minimum length of the data */
	public $minLength;

	/** the maximum length of the data */
	public $maxLength;

	/** Creates a new Text Form Input */
	function __construct($title, $default, $icon, $description, $name, $minLength = 1, $maxLength = INF, $type = "text") {
		parent::__construct($title, $default, $icon, $description, $name, $type);
		$this->minLength = $minLength;
		$this->maxLength = $maxLength;
	}

	/**
	 * Validates that the data exists and sets the data
	 * Returns an error string or null
	 */
	function setData($data) {
		if (!isset($data) || empty($data))
			return "Please fill out the ".$this->title;
		if (strlen($data) < $this->minLength || strlen($data) > $this->maxLength)
			return "Please ensure your response for the ".$this->title." is between ".$this->minLength." and ".$this->maxLength." characters.";
		$this->data = $data;
		return null;
	}
}

class FormCheckboxInput extends FormInput {
	/** Creates a new Checkbox Form Input */
	function __construct($title, $icon, $description, $name, $type = "checkbox") {
		parent::__construct($title, "", $icon, $description, $name, $type);
	}

	/**
	 * Validates that the data exists and sets the data
	 * Returns an error string or null
	 */
	function setData($data) {
		$bool = 0;
		if (isset($data)) $bool = 1;
		else $bool = 0;
		$this->data = $bool;
		return null;
	}
}

class FormEmailInput extends FormInput {
	/** Creates a new Email Form Input */
	function __construct($title, $icon, $description, $name, $type = "email") {
		parent::__construct($title, "", $icon, $description, $name, $type);
	}

	/**
	 * Validates that the data exists and sets the data
	 * Returns an error string or null
	 */
	function setData($data) {
		if (!isset($data) || empty($data))
			return "Please fill out the ".$this->title;
		if (!filter_var($data, FILTER_VALIDATE_EMAIL))
			return "Please ensure your response for the ".$this->title." a valid email address.";
		$this->data = $data;
		return null;
	}
}

class FormPasswordInput extends FormInput {
	/** Creates a new Email Form Input */
	function __construct($title, $icon, $description, $name, $type = "password") {
		parent::__construct($title, "", $icon, $description, $name, $type);
	}

	/**
	 * Validates that the password is safe and sets it
	 * Returns an error string or null
	 */
	function setData($data) {
		$prefix = "Please ensure your response for the ".$this->title." ";
		if (!isset($data) || empty($data))
			return "Please fill out the ".$this->title;
		if (strlen($data) < 5 || strlen($data) > 150)
			return $prefix."is between 5 and 150 characters";
		$this->data = $data;
		return null;
	}

	/** Returns the data */
	function getValue() {
		return password_hash($this->data, PASSWORD_DEFAULT);
	}
}

class FormNumberInput extends FormInput {
	/** the minimum value */
	public $min;

	/** the maximum value */
	public $max;

	/** Creates a new Text Form Input */
	function __construct($title, $default, $icon, $description, $name, $min = -INF, $max = INF, $type = "number") {
		parent::__construct($title, $default, $icon, $description, $name, $type);
		$this->min = $min;
		$this->max = $max;
	}

	/**
	 * Validates that the data exists and sets the data
	 * Returns an error string or null
	 */
	function setData($data) {
		if (!isset($data) || empty($data))
			return "Please fill out the ".$this->title;
		if ($data < $this->min || $data > $this->max)
			return "Please ensure your response for the ".$this->title." is between ".$this->min." and ".$this->max.".";
		$this->data = $data;
		return null;
	}
}

?>