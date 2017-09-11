<?php
class DB {
	protected $registry;
	private $db;
	private $exist_tables = array();
	
    /**
     * Construct database object. 
	 * Get an the class for database conection.
	 * Connect to database and get an array of all exist tables in database.
     *
     * @param  object  $registry
     * @return object
     */
	public function __construct($registry, $driver, $hostname, $username, $password, $database, $port = NULL) {
		
        $this->registry = $registry;
		$class = 'DB\\' . $driver;

		if (class_exists($class)) {
			$this->db = new $class($hostname, $username, $password, $database, $port);
			
			// Get the list of exist tables in database
			$query = $this->db->query("SHOW TABLES");
			foreach ($query->rows as $tables) {
				$this->exist_tables[] = array_shift($tables);
			}
		} else {
			$log = $this->registry->get('log');
			$log->write('ERROR: Could not load database driver ' . $driver . '!');
			die;
		}
	}

	public function query($sql) {
		return $this->db->query($sql);
	}

	public function escape($value) {
		return $this->db->escape($value);
	}

	public function countAffected() {
		return $this->db->countAffected();
	}

	public function getLastId() {
		return $this->db->getLastId();
	}
	
	public function free() {
		return $this->db->free();
	}
	
	public function use_result() {
		return $this->db->use_result();
	}
	
	public function store_result() {
		return $this->db->store_result();
	}
   
   public function next_result() {
		return $this->db->next_result();
	}
   
   public function multi_query($query) {
		$this->link->multi_query($query);
	}
   
   
    /**
	 * Check is this table name exist in database. If not exist then we add this informin in log file and return false.
     *
     * @param  string
     * @return boolean
     */
	public function checkExist($datatable) {
		if (in_array(DB_PREFIX . $datatable, $this->exist_tables)) {
			return true;			
		} else {
			$log = $this->registry->get('log');
			$log->write('ERROR: Table with name "' . DB_PREFIX . $datatable . '" missing in "' . DB_DATABASE . '" database!');
			return false;
		}
	}
}
