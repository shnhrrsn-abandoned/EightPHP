<?php
/**
 * [Object Relational Mapping][ref-orm] (ORM) is a method of abstracting database
 * access to standard PHP calls. All table rows are represented as model objects,
 * with object properties representing row data. ORM in Eight generally follows
 * the [Active Record][ref-act] pattern.
 *
 * [ref-orm]: http://wikipedia.org/wiki/Object-relational_mapping
 * [ref-act]: http://wikipedia.org/wiki/Active_record
 *
 * @package		Modules
 * @subpackage	ORM
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class ORM_Core {

	// Current relationships
	protected $has_one                 = array();
	protected $belongs_to              = array();
	protected $has_many                = array();
	protected $has_and_belongs_to_many = array();

	// Relationships that should always be joined
	protected $load_with = nil;

	// Current object
	protected $object  = array();
	protected $changed = array();
	protected $loaded  = NO;
	protected $saved   = NO;
	protected $sorting = array('id' => 'asc');

	// Related objects
	protected $related = array();

	// Model table information
	protected $object_name;
	protected $table_name;
	protected $table_columns;
	protected $ignored_columns;

	// Table primary key and value
	protected $primary_key = 'id';
	protected $primary_val = 'name';

	// Array of foreign key name overloads
	protected $foreign_key = array();

	// Model configuration
	protected $table_names_plural = YES;
	protected $reload_on_wakeup   = YES;

	// Database configuration
	protected $db = 'default';
	protected $db_applied = array();

	/**
	 * Creates and returns a new model.
	 *
	 * @chainable
	 * @param   string  model name
	 * @param   mixed   parameter for find()
	 * @return  ORM
	 */
	public static function factory($model, $id = nil) {
		// Set class name
		$model = 'Model_'.ucfirst($model);

		return new $model($id);
	}

	/**
	 * Prepares the model database connection and loads the object.
	 *
	 * @param   mixed  parameter for find or object to load
	 * @return  void
	 */
	public function __construct($id = nil) {
		// Set the object name
		$this->object_name = strtolower(substr(get_class($this), 6));

		// Initialize database
		$this->__initialize();

		if($id === nil OR $id === '') {
			// Clear the object
			$this->clear();
		} elseif(is_object($id)) {
			// Load an object
			$this->load_values((array) $id);
		} else {
			// Find an object
			$this->find($id);
		}
	}

	/**
	 * Prepares the model database connection, determines the table name,
	 * and loads column information.
	 *
	 * @return  void
	 */
	public function __initialize() {
		if(!is_object($this->db)) {
			// Get database instance
			$this->db = Database::instance($this->db);
		}

		if(empty($this->table_name)) {
			// Table name is the same as the object name
			$this->table_name = $this->object_name;

			if($this->table_names_plural === YES) {
				// Make the table name plural
				$this->table_name = inflector::plural($this->table_name);
			}
		}

		if(is_array($this->ignored_columns)) {
			// Make the ignored columns mirrored = mirrored
			$this->ignored_columns = array_combine($this->ignored_columns, $this->ignored_columns);
		}

		// Load column information
		$this->reload_columns();
	}

	/**
	 * Allows serialization of only the object data and state, to prevent
	 * "stale" objects being unserialized, which also requires less memory.
	 *
	 * @return  array
	 */
	public function __sleep() {
		// Store only information about the object
		return array('object_name', 'object', 'changed', 'loaded', 'saved', 'sorting');
	}

	/**
	 * Prepares the database connection and reloads the object.
	 *
	 * @return  void
	 */
	public function __wakeup() {
		// Initialize database
		$this->__initialize();

		if($this->reload_on_wakeup === YES) {
			// Reload the object
			$this->reload();
		}
	}

	/**
	 * Handles pass-through to database methods. Calls to query methods
	 * (query, get, insert, update) are not allowed. Query builder methods
	 * are chainable.
	 *
	 * @param   string  method name
	 * @param   array   method arguments
	 * @return  mixed
	 */
	public function __call($method, array $args) {
		if(method_exists($this->db, $method)) {
			if(in_array($method, array('query', 'get', 'insert', 'update', 'delete')))
				throw new Eight_Exception('orm.query_methods_not_allowed');

			// Method has been applied to the database
			$this->db_applied[$method] = $method;

			// Number of arguments passed
			$num_args = count($args);

			if($method === 'select' and $num_args > 3) {
				// Call select() manually to avoid call_user_func_array
				$this->db->select($args);
			} else {
				// We use switch here to manually call the database methods. This is
				// done for speed: call_user_func_array can take over 300% longer to
				// make calls. Most database methods are 4 arguments or less, so this
				// avoids almost any calls to call_user_func_array.

				switch ($num_args) {
					case 0:
						// Support for things like reset_select, reset_write, list_tables
						return $this->db->$method();
					break;
					case 1:
						$this->db->$method($args[0]);
					break;
					case 2:
						$this->db->$method($args[0], $args[1]);
					break;
					case 3:
						$this->db->$method($args[0], $args[1], $args[2]);
					break;
					case 4:
						$this->db->$method($args[0], $args[1], $args[2], $args[3]);
					break;
					default:
						// Here comes the snail...
						call_user_func_array(array($this->db, $method), $args);
					break;
				}
			}

			return $this;
		} else {
			throw new Eight_Exception('core.invalid_method', $method, get_class($this));
		}
	}

	/**
	 * Handles retrieval of all model values, relationships, and metadata.
	 *
	 * @param   string  column name
	 * @return  mixed
	 */
	public function __get($column) {
		if(isset($this->ignored_columns[$column])) {
			return nil;
		} elseif(array_key_exists($column, $this->object)) {
			return $this->object[$column];
		} elseif(isset($this->related[$column])) {
			return $this->related[$column];
		} elseif($column === 'primary_key_value') {
			return $this->object[$this->primary_key];
		} elseif($model = $this->related_object($column)) {
			// This handles the has_one and belongs_to relationships

			if(isset($this->object[$column.'_'.$model->primary_key])) {
				// Use the FK that exists in this model as the PK
				$where = array($model->table_name.'.'.$model->primary_key => $this->object[$column.'_'.$model->primary_key]);
			} else {
				// Use this model PK as the FK
				$where = array($this->foreign_key() => $this->object[$this->primary_key]);
			}

			// one<>alias:one relationship
			return $this->related[$column] = $model->find($where);
		} elseif(isset($this->has_many[$column])) {
			// Load the "middle" model
			$through = ORM::factory(inflector::singular($this->has_many[$column]));

			// Load the "end" model
			$model = ORM::factory(inflector::singular($column));

			// Load JOIN info
			$join_table = $through->table_name;
			$join_col1  = $model->foreign_key(nil, $join_table);
			$join_col2  = $model->foreign_key(YES);

			// one<>alias:many relationship
			return $this->related[$column] = $model
				->join($join_table, $join_col1, $join_col2)
				->where($this->foreign_key(nil, $join_table), $this->object[$this->primary_key])
				->find_all();
		} elseif(in_array($column, $this->has_many)) {
			// one<>many relationship
			return $this->related[$column] = ORM::factory(inflector::singular($column))
				->where($this->foreign_key($column), $this->object[$this->primary_key])
				->find_all();
		} elseif(in_array($column, $this->has_and_belongs_to_many)) {
			// Load the remote model, always singular
			$model = ORM::factory(inflector::singular($column));

			// Load JOIN info
			$join_table = $model->join_table($this->table_name);
			$join_col1  = $model->foreign_key(nil, $join_table);
			$join_col2  = $model->foreign_key(YES);

			// many<>many relationship
			return $this->related[$column] = $model
				->join($join_table, $join_col1, $join_col2)
				->where($this->foreign_key(nil, $join_table), $this->object[$this->primary_key])
				->find_all();
		} elseif(in_array($column, array
			(
				'object_name', // Object
				'primary_key', 'primary_val', 'table_name', 'table_columns', // Table
				'loaded', 'saved', // Status
				'has_one', 'belongs_to', 'has_many', 'has_and_belongs_to_many', // Relationships
			))) {
			// Model meta information
			return $this->$column;
		} else {
			throw new Eight_Exception('core.invalid_property', $column, get_class($this));
		}
	}

	/**
	 * Handles setting of all model values, and tracks changes between values.
	 *
	 * @param   string  column name
	 * @param   mixed   column value
	 * @return  void
	 */
	public function __set($column, $value) {
		if(isset($this->ignored_columns[$column])) {
			return nil;
		} elseif(isset($this->object[$column]) OR array_key_exists($column, $this->object)) {
			if(isset($this->table_columns[$column])) {
				// Data has changed
				$this->changed[$column] = $column;

				// Object is no longer saved
				$this->saved = NO;
			}

			$this->object[$column] = $this->load_type($column, $value);
		} else {
			throw new Eight_Exception('core.invalid_property', $column, get_class($this));
		}
	}

	/**
	 * Checks if object data is set.
	 *
	 * @param   string  column name
	 * @return  boolean
	 */
	public function __isset($column) {
		return (isset($this->object[$column]) OR isset($this->related[$column]));
	}

	/**
	 * Unsets object data.
	 *
	 * @param   string  column name
	 * @return  void
	 */
	public function __unset($column) {
		unset($this->object[$column], $this->changed[$column], $this->related[$column]);
	}

	/**
	 * Displays the primary key of a model when it is converted to a string.
	 *
	 * @return  string
	 */
	public function __toString() {
		return (string) $this->object[$this->primary_key];
	}

	/**
	 * Returns the values of this object as an array.
	 *
	 * @return  array
	 */
	public function as_array() {
		return $this->object;
	}

	public function with($object) {
		$prefix = $table = $object;

		if($this->table_names_plural) {
			$table = inflector::plural($table);
		}

		if(!($object = $this->related_object($object))) {
			return $this;
		}

		// Use the keys of the empty object to determine the columns
		$select = array_keys($object->as_array());
		foreach($select as $i => $column) {
			// Add the prefix so that load_result can determine the relationship
			$select[$i] = $object->table_name.'.'.$column.' AS '.$prefix.':'.$column;
		}

		// Select all of the prefixed keys in the object
		$this->db->select($select);

		$foreign_key = $prefix.'_'.$object->primary_key;

		if(array_key_exists($foreign_key, $this->object)) {
			$join_col1 = $object->foreign_key(YES);
			$join_col2 = $this->table_name.'.'.$foreign_key;
		} else {
			$join_col1 = $this->foreign_key(nil, $table);
			$join_col2 = $this->foreign_key(YES);
		}

		// Join the related object into the result
		$this->db->join($object->table_name, $join_col1, $join_col2);

		return $this;
	}

	/**
	 * Finds and loads a single database row into the object.
	 *
	 * @chainable
	 * @param   mixed  primary key or an array of clauses
	 * @return  ORM
	 */
	public function find($id = nil) {
		if($id !== nil) {
			if(is_array($id)) {
				// Search for all clauses
				$this->db->where($id);
			} else {
				// Search for a specific column
				$this->db->where($this->table_name.'.'.$this->unique_key($id), $id);
			}
		}

		return $this->load_result();
	}

	/**
	 * Finds multiple database rows and returns an iterator of the rows found.
	 *
	 * @chainable
	 * @param   integer  SQL limit
	 * @param   integer  SQL offset
	 * @return  ORM_Iterator
	 */
	public function find_all($limit = nil, $offset = nil) {
		if($limit !== nil and!isset($this->db_applied['limit'])) {
			// Set limit
			$this->limit($limit);
		}

		if($offset !== nil and!isset($this->db_applied['offset'])) {
			// Set offset
			$this->offset($offset);
		}

		return $this->load_result(YES);
	}

	/**
	 * Creates a key/value array from all of the objects available. Uses find_all
	 * to find the objects.
	 *
	 * @param   string  key column
	 * @param   string  value column
	 * @return  array
	 */
	public function select_list($key, $val) {
		// Return a select list from the results
		return $this->select($key, $val)->find_all()->select_list($key, $val);
	}

	/**
	 * Validates the current object. This method should generally be called
	 * via the model, after the $_POST Validation object has been created.
	 *
	 * @param   object   Validation array
	 * @return  boolean
	 */
	public function validate(Validation $array, $save = NO) {
		if(!$array->submitted()) {
			$safe_array = $array->safe_array();

			foreach($safe_array as $key => $val) {
				// Pre-fill data
				$array[$key] = $this->$key;
			}
		}

		// Validate the array
		if($status = $array->validate()) {
			$safe_array = $array->safe_array();

			foreach($safe_array as $key => $val) {
				// Set new data
				$this->$key = $val;
			}

			if($save === YES OR is_string($save)) {
				// Save this object
				$this->save();

				if(is_string($save)) {
					// Redirect to the saved page
					url::redirect($save);
				}
			}
		}

		// Return validation status
		return $status;
	}

	/**
	 * Saves the current object. If the object is new, it will be reloaded
	 * after being saved.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function save() {
		if(empty($this->changed))
			return $this;

		$data = array();
		foreach($this->changed as $column) {
			// Compile changed data
			$data[$column] = $this->object[$column];
		}

		if($this->loaded === YES) {
			$query = $this->db
				->where($this->primary_key, $this->object[$this->primary_key])
				->update($this->table_name, $data);

			// Object has been saved
			$this->saved = YES;

			// Nothing has been changed
			$this->changed = array();
		} else {
			$query = $this->db
				->insert($this->table_name, $data);

			if($query->count() > 0) {
				if(empty($this->object[$this->primary_key])) {
					// Load the insert id as the primary key
					$this->object[$this->primary_key] = $query->insert_id();
				}

				// Reload the object
				$this->reload();
			}
		}

		return $this;
	}

	/**
	 * Deletes the current object from the database. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function delete($id = nil) {
		if($id === nil and $this->loaded) {
			// Use the the primary key value
			$id = $this->object[$this->primary_key];
		}

		// Delete this object
		$this->db->where($this->primary_key, $id)->delete($this->table_name);

		return $this->clear();
	}

	/**
	 * Delete all objects in the associated table. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @param   array  ids to delete
	 * @return  ORM
	 */
	public function delete_all($ids = nil) {
		if(is_array($ids)) {
			// Delete only given ids
			$this->db->in($this->primary_key, $ids);
		} else {
			// Delete all records
			$this->db->where(YES);
		}

		// Delete all objects
		$this->db->delete($this->table_name);

		return $this->clear();
	}

	/**
	 * Unloads the current object and clears the status.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function clear() {
		// Create an array with all the columns set to nil
		$columns = array_keys($this->table_columns);
		$values  = array_combine($columns, array_fill(0, count($columns), nil));

		// Replace the current object with an empty one
		$this->load_values($values);

		return $this;
	}

	/**
	 * Reloads the current object from the database.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function reload() {
		return $this->find($this->object[$this->primary_key]);
	}

	/**
	 * Reload column definitions.
	 *
	 * @chainable
	 * @param   boolean  force reloading
	 * @return  ORM
	 */
	public function reload_columns($force = NO) {
		if($force === YES OR empty($this->table_columns)) {
			// Load table columns
			$this->table_columns = $this->db->list_columns($this->table_name, YES);
		}

		return $this;
	}

	/**
	 * Tests if this object has a relationship to a different model.
	 *
	 * @param   object   related ORM model
	 * @return  boolean
	 */
	public function has(ORM $model) {
		if(!$this->loaded)
			return NO;

		if(($join_table = array_search(inflector::plural($model->object_name), $this->has_and_belongs_to_many)) === NO)
			return NO;

		if(is_int($join_table)) {
			// No "through" table, load the default JOIN table
			$join_table = $model->join_table($this->table_name);
		}

		if($model->loaded) {
			// Select only objects of a specific id
			$this->db->where($model->foreign_key(nil, $join_table), $model->primary_key_value);
		}

		// Return the number of rows that exist
		return $this->db
			->where($this->foreign_key(nil, $join_table), $this->object[$this->primary_key])
			->count_records($join_table);
	}

	/**
	 * Adds a new relationship to between this model and another.
	 *
	 * @param   object   related ORM model
	 * @return  boolean
	 */
	public function add(ORM $model) {
		if(!$this->loaded)
			return NO;

		if($this->has($model))
			return YES;

		if(($join_table = array_search(inflector::plural($model->object_name), $this->has_and_belongs_to_many)) === NO)
			return NO;

		if(is_int($join_table)) {
			// No "through" table, load the default JOIN table
			$join_table = $model->join_table($this->table_name);
		}

		// Insert the new relationship
		$this->db->insert($join_table, array
		(
			$this->foreign_key(nil, $join_table)  => $this->object[$this->primary_key],
			$model->foreign_key(nil, $join_table) => $model->primary_key_value,
		));

		return YES;
	}

	/**
	 * Adds a new relationship to between this model and another.
	 *
	 * @param   object   related ORM model
	 * @return  boolean
	 */
	public function remove(ORM $model) {
		if(!$this->has($model))
			return NO;

		if(($join_table = array_search(inflector::plural($model->object_name), $this->has_and_belongs_to_many)) === NO)
			return NO;

		if(is_int($join_table)) {
			// No "through" table, load the default JOIN table
			$join_table = $model->join_table($this->table_name);
		}

		if($model->loaded) {
			// Delete only a specific object
			$this->db->where($model->foreign_key(nil, $join_table), $model->primary_key_value);
		}

		// Return the number of rows deleted
		return $this->db
			->where($this->foreign_key(nil, $join_table), $this->object[$this->primary_key])
			->delete($join_table)
			->count();
	}

	/**
	 * Count the number of records in the table.
	 *
	 * @return  integer
	 */
	public function count_all() {
		// Return the total number of records in a table
		return $this->db->count_records($this->table_name);
	}

	/**
	 * Count the number of records in the last query, without LIMIT or OFFSET applied.
	 *
	 * @return  integer
	 */
	public function count_last_query() {
		if($sql = $this->db->last_query()) {
			if(stripos($sql, 'LIMIT') !== NO) {
				// Remove LIMIT from the SQL
				$sql = preg_replace('/\sLIMIT\s+[^a-z]+/i', ' ', $sql);
			}

			if(stripos($sql, 'OFFSET') !== NO) {
				// Remove OFFSET from the SQL
				$sql = preg_replace('/\sOFFSET\s+\d+/i', '', $sql);
			}

			// Get the total rows from the last query executed
			$result = $this->db->query
			(
				'SELECT COUNT(*) AS '.$this->db->escape_column('total_rows').' '.
				'FROM ('.trim($sql).') AS '.$this->db->escape_table('counted_results')
			);

			// Return the total number of rows from the query
			return (int) $result->current()->total_rows;
		}

		return NO;
	}

	/**
	 * Proxy method to Database list_fields.
	 *
	 * @param   string  table name
	 * @return  array
	 */
	public function list_columns($table) {
		// Proxy to database
		return $this->db->list_columns($table);
	}

	/**
	 * Proxy method to Database field_data.
	 *
	 * @param   string  table name
	 * @return  array
	 */
	public function field_data($table) {
		// Proxy to database
		return $this->db->field_data($table);
	}

	/**
	 * Proxy method to Database last_query.
	 *
	 * @return  string
	 */
	public function last_query() {
		// Proxy to database
		return $this->db->last_query();
	}

	/**
	 * Proxy method to Database field_data.
	 *
	 * @chainable
	 * @param   string  SQL query to clear
	 * @return  ORM
	 */
	public function clear_cache($sql = nil) {
		// Proxy to database
		$this->db->clear_cache($sql);

		return $this;
	}

	/**
	 * Returns the unique key for a specific value. This method is expected
	 * to be overloaded in models if the model has other unique columns.
	 *
	 * @param   mixed   unique value
	 * @return  string
	 */
	public function unique_key($id) {
		return $this->primary_key;
	}

	/**
	 * Determines the name of a foreign key for a specific table.
	 *
	 * @param   string  related table name
	 * @param   string  prefix table name (used for JOINs)
	 * @return  string
	 */
	public function foreign_key($table = nil, $prefix_table = nil) {
		if($table === YES) {
			// Return the name of this tables PK
			return $this->table_name.'.'.$this->primary_key;
		}

		if(is_string($prefix_table)) {
			// Add a period for prefix_table.column support
			$prefix_table .= '.';
		}

		if(isset($this->foreign_key[$table])) {
			// Use the defined foreign key name, no magic here!
			$foreign_key = $this->foreign_key[$table];
		} else {
			if(!is_string($table) OR!isset($this->object[$table.'_'.$this->primary_key])) {
				// Use this table
				$table = $this->table_name;

				if($this->table_names_plural === YES) {
					// Make the key name singular
					$table = inflector::singular($table);
				}
			}

			$foreign_key = $table.'_'.$this->primary_key;
		}

		return $prefix_table.$foreign_key;
	}

	/**
	 * This uses alphabetical comparison to choose the name of the table.
	 *
	 * Example: The joining table of users and roles would be roles_users,
	 * because "r" comes before "u". Joining products and categories would
	 * result in categories_prouducts, because "c" comes before "p".
	 *
	 * Example: zoo > zebra > robber > ocean > angel > aardvark
	 *
	 * @param   string  table name
	 * @return  string
	 */
	public function join_table($table) {
		if($this->table_name > $table) {
			$table = $table.'_'.$this->table_name;
		} else {
			$table = $this->table_name.'_'.$table;
		}

		return $table;
	}

	/**
	 * Returns an ORM model for the given object name;
	 *
	 * @param   string  object name
	 * @return  ORM
	 */
	protected function related_object($object) {
		if(isset($this->has_one[$object])) {
			$object = ORM::factory($this->has_one[$object]);
		} elseif(isset($this->belongs_to[$object])) {
			$object = ORM::factory($this->belongs_to[$object]);
		} elseif(in_array($object, $this->has_one) OR in_array($object, $this->belongs_to)) {
			$object = ORM::factory($object);
		} else {
			return NO;
		}

		return $object;
	}

	/**
	 * Loads an array of values into into the current object.
	 *
	 * @chainable
	 * @param   array  values to load
	 * @return  ORM
	 */
	public function load_values(array $values) {
		// Get the table columns
		$columns = array_keys($this->table_columns);

		if(array_key_exists($this->primary_key, $values)) {
			// Replace the object and reset the object status
			$this->object = $this->changed = $this->related = array();

			// Set the loaded and saved object status based on the primary key
			$this->loaded = $this->saved = ($values[$this->primary_key] > 0);
		}

		// Related objects
		$related = array();

		foreach($values as $column => $value) {
			if(strpos($column, ':') === NO) {
				if(isset($columns[$column])) {
					// The type of the value can be determined, convert the value
					$value = $this->load_type($column, $value);
				}

				$this->object[$column] = $value;
			} else {
				list ($prefix, $column) = explode(':', $column, 2);

				$related[$prefix][$column] = $value;
			}
		}

		if(!empty($related)) {
			foreach($related as $object => $values) {
				// Load the related objects with the values in the result
				$this->related[$object] = $this->related_object($object)->load_values($values);
			}
		}

		return $this;
	}

	/**
	 * Loads a value according to the types defined by the column metadata.
	 *
	 * @param   string  column name
	 * @param   mixed   value to load
	 * @return  mixed
	 */
	protected function load_type($column, $value) {
		if(is_object($value) OR is_array($value) OR!isset($this->table_columns[$column]))
			return $value;

		// Load column data
		$column = $this->table_columns[$column];

		if($value === nil and!empty($column['null']))
			return $value;

		if(!empty($column['binary']) and!empty($column['exact']) and (int) $column['length'] === 1) {
			// Use boolean for BINARY(1) fields
			$column['type'] = 'boolean';
		}

		switch ($column['type']) {
			case 'int':
				if($value === '' and!empty($column['null'])) {
					// Forms will only submit strings, so empty integer values must be null
					$value = nil;
				} elseif((float) $value > PHP_INT_MAX) {
					// This number cannot be represented by a PHP integer, so we convert it to a string
					$value = (string) $value;
				} else {
					$value = (int) $value;
				}
			break;
			case 'float':
				$value = (float) $value;
			break;
			case 'boolean':
				$value = (bool) $value;
			break;
			case 'string':
				$value = (string) $value;
			break;
		}

		return $value;
	}

	/**
	 * Loads a database result, either as a new object for this model, or as
	 * an iterator for multiple rows.
	 *
	 * @chainable
	 * @param   boolean       return an iterator or load a single row
	 * @return  ORM           for single rows
	 * @return  ORM_Iterator  for multiple rows
	 */
	protected function load_result($array = NO) {
		if($array === NO) {
			// Only fetch 1 record
			$this->db->limit(1);
		}

		if(!isset($this->db_applied['select'])) {
			// Selete all columns by default
			$this->db->select($this->table_name.'.*');
		}

		if(!empty($this->load_with)) {
			foreach($this->load_with as $object) {
				// Join each object into the results
				$this->with($object);
			}
		}

		if(!isset($this->db_applied['orderby']) and!empty($this->sorting)) {
			$sorting = array();
			foreach($this->sorting as $column => $direction) {
				if(strpos($column, '.') === NO) {
					// Keeps sorting working properly when using JOINs on
					// tables with columns of the same name
					$column = $this->table_name.'.'.$column;
				}

				$sorting[$column] = $direction;
			}

			// Apply the user-defined sorting
			$this->db->orderby($sorting);
		}

		// Load the result
		$result = $this->db->get($this->table_name);

		if($array === YES) {
			// Return an iterated result
			return new ORM_Iterator($this, $result);
		}

		if($result->count() === 1) {
			// Load object values
			$this->load_values($result->result(NO)->current());
		} else {
			// Clear the object, nothing was found
			$this->clear();
		}

		return $this;
	}

} // End ORM