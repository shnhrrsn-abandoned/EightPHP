<?php
/**
 * Formation library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Core {

	// Template variables
	protected $template = array(
		'title' => '',
		'class' => '',
		'open'  => '',
		'close' => '',
	);

	// Formation attributes
	protected $attr = array();
	protected $formation_id = nil;
	private   $formation_id_element;

	// Formation inputs and hidden inputs
	public $inputs = array();
	public $hidden = array();

	// Error message format, only used with custom templates
	public $error_format = '<p class="error">{message}</p>';
	public $newline_char = "\n";

	/**
	 * Formation constructor. Sets the form action, title, method, and attributes.
	 *
	 * @return  void
	 */
	public function __construct($action = nil, $title = '', $method = nil, $attr = array(), $formation_id=nil) {
		// Set form attributes
		$this->attr['action'] = $action;
		$this->attr['method'] = empty($method) ? 'post' : $method;

		// Set template variables
		$this->template['title'] = $title;

		// Empty attributes sets the class to "form"
		empty($attr) and $attr = array('class' => 'form');

		// String attributes is the class name
		is_string($attr) and $attr = array('class' => $attr);

		// Extend the template with the attributes
		$this->attr += $attr;
		
		// Set form id to handle multiple formations
		if(!is_null($formation_id)) {
			$this->formation_id = $formation_id;
			$this->formation_id_element = $this->hidden('formation_id')->value($form_id);
		}
	}
	
	public function set_unique($formation_id) {
		if(!is_object($this->formation_id_element)) {
			$this->formation_id_element = $this->hidden('formation_id');
		}
		
		$this->formation_id = $formation_id;
		$this->formation_id_element->value($formation_id);
		$this->formation_id_element->__set('id', 'formation_id');
	}

	/**
	 * Magic __get method. Returns the specified form element.
	 *
	 * @param   string   unique input name
	 * @return  object
	 */
	public function __get($key) {
		if (isset($this->inputs[$key])) {
			return $this->inputs[$key];
		} elseif (isset($this->hidden[$key])) {
			return $this->hidden[$key];
		} elseif (isset($this->attr[$key])) {
			return $this->attr[$key];
		}
	}

	/**
	 * Magic __call method. Creates a new formation element object.
	 *
	 * @throws  Eight_Exception
	 * @param   string   input type
	 * @param   string   input name
	 * @return  object
	 */
	public function __call($method, $args) {
		// Class name
		$input = 'Formation_'.ucfirst($method);

		// Create the input
		switch(count($args)) {
			case 1:
				$input = new $input($args[0], $this);
			break;
			case 2:
				$input = new $input($args[0], $args[1], $this);
			break;
			default:
				throw new Eight_Exception('formation.invalid_input', $input);
		}

		if ( ! ($input instanceof Formation_Input) AND ! ($input instanceof Formation))
			throw new Eight_Exception('formation.unknown_input', get_class($input));

		$input->method = $this->attr['method'];

		if ($name = $input->name) {
			// Assign by name
			if ($method == 'hidden') {
				$this->hidden[$name] = $input;
			} elseif($input instanceof Formation_Radio) {
				$this->inputs[] = $input;
			} else {
				$this->inputs[$name] = $input;
			}
		} else {
			// No name, these are unretrievable
			$this->inputs[] = $input;
		}

		return $input;
	}

	/**
	 * Set a form attribute. This method is chainable.
	 *
	 * @param   string|array  attribute name, or an array of attributes
	 * @param   string        attribute value
	 * @return  object
	 */
	public function set_attr($key, $val = nil, $append = FALSE) {
		if (is_array($key)) {
			// Merge the new attributes with the old ones
			$this->attr = array_merge($this->attr, $key);
		} else {
			if($key == 'class' && $append) {
				$this->attr[$key] .= ' '.$val;
			} else {
				// Set the new attribute
				$this->attr[$key] = $val;
			}
		}

		return $this;
	}

	/**
	 * Validates the form by running each inputs validation rules.
	 *
	 * @return  bool
	 */
	public function validate() {
		$status = YES;
		
		$inputs = array_merge($this->hidden, $this->inputs);
		
		foreach ($inputs as $input) {
			if($input->name == 'formation_id') continue;
			if ($input->validate() == NO) {
				$status = NO;
			}
		}

		return $status;
	}
	
	/**
	 * Returns whether or not this is the formation form that was submitted
	 *
	 * @return bool
	 */
	public function submitted() {
		$method = "is_".$this->attr['method'];
		if(is_null($this->formation_id)) {
			return request::$method() ? YES : NO;
		} else {
			return request::$method() && request::$input['formation_id'] == $this->formation_id ? YES : NO;
		}
	}

	/**
	 * Returns the form as an array of input names and values.
	 *
	 * @return  array
	 */
	public function as_array() {
		$this->as_array_data = array();
		
		// This will dig through the Formation classes and return the values
		$this->process_inputs(array_merge($this->hidden, $this->inputs));
		
		foreach(array_keys($this->as_array_data) as $key) {
			if(preg_match_all("#\[([^\]]+)\]#", $key, $matches)) {
				$original_key = $key;
				$keys = $matches[1];
				array_unshift($keys, trim(substr($key, 0, strpos($key, "["))));
				$this->as_array_data = arr::set_key_path($this->as_array_data, $keys, $this->as_array_data[$original_key]);
				unset($this->as_array_data[$original_key]);
			}
		}
		
		return $this->as_array_data;
	}
	
	/**
	 * Processes all of the provided inputs and recursively digs through groups
	 */
	public function process_inputs($inputs) {
		foreach($inputs as $input) {
			if($input->name == 'formation_id') continue;
			if ($input instanceof Formation_Group) { // It's a Formation_Group Object
				$this->process_inputs($input->inputs); // Go deeper
			} else if (is_array($input->inputs)) {
				foreach ($input->inputs as $group_input) {
					if ($name = $group_input->name) {
						$this->as_array_data[$name] = $group_input->value;
					}
				}
			} else if ($name = $input->name) { //  otherwise, it's a modifier 
				// Return only named inputs
				$this->as_array_data[$name] = $input->value;
			}
		}
	}

	/**
	 * Changes the error message format. Your message formatting must
	 * contain a {message} placeholder.
	 *
	 * @throws  Eight_Exception
	 * @param   string   new message format
	 * @return  void
	 */
	public function error_format($string = '') {
		if (strpos((string) $string, '{message}') === NO)
			throw new Eight_Exception('validation.error_format');

		$this->error_format = $string;
	}

	/**
	 * Creates the form HTML
	 *
	 * @param   string   form view template name
	 * @param   boolean  use a custom view
	 * @return  string
	 */
	public function render($template = 'formation/wrapper', $custom = NO) {
		// Load template
		$form = new View($template);

		if ($custom) {
			// Using a custom view
			$data = array();
			foreach (array_merge($this->hidden, $this->inputs) as $input) {
				$data[$input->name] = $input;
				
				// Groups will never have errors, so skip them
				if ($input instanceof Formation_Group)
					continue;

				// Compile the error messages for this input
				$messages = '';
				$errors = $input->error_messages();
				if (is_array($errors) AND ! empty($errors)) {
					foreach($errors as $error) {
						// Replace the message with the error in the html error string
						$messages .= str_replace('{message}', $error, $this->error_format).$this->newline_char;
					}
				}

				$data[$input->name.'_errors'] = $messages;
			}

			$form->set($data);
		} else {
			// Using a template view

			$form->set($this->template);
			$hidden = array();
			if ( ! empty($this->hidden)) {
				foreach($this->hidden as $input) {
					$hidden[$input->name] = $input->value;
				}
			}

			$form_type = 'open';
			// See if we need a multipart form
			foreach($this->inputs as $input) {
				if ($input instanceof Formation_Upload) {
					$form_type = 'open_multipart';
				}
			}

			// Tack on the "formation" class
			$space = str::e($this->attr['class']) ? '' : ' ';
			$this->attr['class'] = 'formation'.$space.$this->attr['class'];
			
			// Set the form open and close
			$form->open  = form::$form_type(arr::remove('action', $this->attr), $this->attr, $hidden);
			$form->close = form::close();

			// Set the inputs
			$form->inputs = $this->inputs;
		}

		return $form;
	}

	/**
	 * Returns the form HTML
	 */
	public function __toString() {
		return (string) $this->render();
	}

} // End Formation