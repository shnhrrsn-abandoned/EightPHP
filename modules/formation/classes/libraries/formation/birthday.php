<?php
/**
 * Formation birthday input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Birthday_Core extends Formation_Dateselect {
	protected $min_age = 0;
	
	public function __construct($name, $formation) {
		parent::__construct($name, $formation);
		
		$this->date_only(YES);
	}
	
	public function min_age($age=nil) {
		if($age != nil && is_numeric($age)) {
			$this->min_age = (int)$age;
		}
		
		return $this;
	}
	
	public function html_element() {
		$this->parts['year'] = array(1950, date('Y')-$this->min_age);
		if(!isset(request::$input[$this->data['name']])) $this->data['value'] = $this->default_time();
		return parent::html_element();
	}
	
	protected function default_time() {
		if($this->min_age == 0) return time();
		
		return time() - ($this->min_age * 31556926);
	}


} // End Formation_Birthday_Core