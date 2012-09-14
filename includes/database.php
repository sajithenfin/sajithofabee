<?php
/**
* MySQLDatabase
*
* This is small mysql framework class 
* helps to perform basic query operations CRUD
*
* @author 	Sadanandan Sajith 
* @copyright	Enfin Technologies 2012
* @version	1.0
*/
require_once(LIB_PATH.DS."config.php");

class MySQLDatabase {
	
	private $connection;
	public $last_query;
	private $magic_quotes_active;
	private $real_escape_string_exists;
	
  function __construct() {
    $this->open_connection();
		$this->magic_quotes_active = get_magic_quotes_gpc();
		$this->real_escape_string_exists = function_exists( "mysql_real_escape_string" );
  }

	/**
	* open_connection
	*
	* opens a database connection 
	*/
	public function open_connection() {
		$this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
		if (!$this->connection) {
			die("Database connection failed: " . mysql_error());
		} else {
			$db_select = mysql_select_db(DB_NAME, $this->connection);
			if (!$db_select) {
				die("Database selection failed: " . mysql_error());
			}
		}
	}

	/**
	* close_connection
	*
	* close a database connection which is already opened
	*/
	public function close_connection() {
		if(isset($this->connection)) {
			mysql_close($this->connection);
			unset($this->connection);
		}
	}

	/**
	* query
	*
	* executes give sql query
	* @param string $sql
	* @return object $result
	*/
	public function query($sql) {
		$this->last_query = $sql;
		$result = mysql_query($sql);
		$this->confirm_query($result);
		return $result;
	}
	
	/**
	* escape_value
	*
	* escape string form sql injection
	* @param string $value
	* @return string $value
	*/
	public function escape_value( $value ) {
		if( $this->real_escape_string_exists ) { // PHP v4.3.0 or higher
			// undo any magic quote effects so mysql_real_escape_string can do the work
			if( $this->magic_quotes_active ) { $value = stripslashes( $value ); }
			$value = mysql_real_escape_string( $value );
		} else { // before PHP v4.3.0
			// if magic quotes aren't already on then add slashes manually
			if( !$this->magic_quotes_active ) { $value = addslashes( $value ); }
			// if magic quotes are active, then the slashes already exist
		}
		return $value;
	}
	
	/**
	* find_all
	*
	* select all from given table
	* @param string $table_name
	* @return object $result
	*/
	public function find_all($table_name) {
		$result = $this->query("SELECT * FROM ".$table_name);
		return $result;
  	}
	/**
	* find_by_id
	*
	* select a record from given table with given id
	* @param string $table_name
	* @param int $id
	* @return object|bool $result
	*/
	public function find_by_id($table_name,$id=0) {
    	return $this->query("SELECT * FROM ".$table_name." WHERE id=".$id." LIMIT 1");
  	}
	
	/**
	* find_by_condition
	*
	* select a record from given table with given condition
	* @param string $table_name
	* @param string $table_key
	* @param int $value
	* @return object|bool $result
	*/
	public function find_by_condition($table_name,$key,$value="") {
		$query = "SELECT * FROM ".$table_name." WHERE ".$key."='".$value."' LIMIT 1";
    	return $this->query($query);
  	}
	/**
	* create
	*
	* insert datas into given table
	* @param string $table_name
	* @param array $data
	* @return bool true on success, false on fail
	*/
	public function create($table_name,$data) {
	  $sql = "INSERT INTO ".$table_name." (";
		$sql .= join(", ", array_keys($data));
	  $sql .= ") VALUES ('";
		$sql .= join("', '", array_values($data));
		$sql .= "')";
	  if($this->query($sql)) {
	    return true;
	  } else {
	    return false;
	  }
	}
	
	/**
	* update
	*
	* updates datas in the given table with respect to the id
	* @param string $table_name
	* @param int $id
	* @param array $data
	* @return bool true on success, false on fail
	*/
	public function update($table_name,$id,$data) {
		$attribute_pairs = array();
		foreach($data as $key => $value) {
		  $attribute_pairs[] = "{$key}='{$value}'";
		}
		$sql = "UPDATE ".$table_name." SET ";
		$sql .= join(", ", $attribute_pairs);
		$sql .= " WHERE id=". $this->escape_value($id);
	  $this->query($sql);
	  return ($this->affected_rows() == 1) ? true : false;
	}
	
	/**
	* delete_by_id
	*
	* deletes a table row by id
	* @param string $table_name
	* @param int $id
	* @return bool true on success, false on fail
	*/
	public function delete_by_id($table_name,$id) {
	  $sql = "DELETE FROM ".$table_name;
	  $sql .= " WHERE id=". $this->escape_value($id);
	  $sql .= " LIMIT 1";
	  $this->query($sql);
	  return ($this->affected_rows() == 1) ? true : false;
	}
	
	/**
	* join_table
	*
	* performs a join query
	* @param string $table_name
	* @param array $data conditions for joining
	* @return object $result
	*/
	public function join_table($table_name,$data){
		/*follow this format for data array
			$data = array(
				'tables'=>array(
					'ofabee_unique_device'=>array('DID','device_id','name','model','version','key'),
					'ofabee_age'=>array('age_range'),
					'ofabee_gendar'=>array('type'),
					'ofabee_country'=>array('country_name')
				),
				'join'=>array(
					'ofabee_age'=>array('ofabee_unique_device.AGID','ofabee_age.AGID'),
					'ofabee_gendar'=>array('ofabee_unique_device.GID','ofabee_gendar.GID'),
					'ofabee_country'=>array('ofabee_unique_device.COID','ofabee_country.COID')
				)
			);
		*/
		$query = "SELECT ";
		$last = end(end($data['tables']));
		foreach ($data['tables'] as $table=>$columns){
			foreach($columns as $count=>$column){
				$query .= $table.".".$column;
				if($column != $last)
				$query.=",";
			}
		}
		$query.=" FROM ".$table_name;
		foreach($data['join'] as $table=>$condition){
			$query.=" JOIN ".$table." ON ".$condition[0]."=".$condition[1];
		}
		return $this->query($query);
	}
	
	/**
	* join_table_where
	*
	* performs a join query
	* @param string $table_name
	* @param array $data conditions for joining
	* @param string $table_key
	* @param int $value
	* @return object $result
	*/
	public function join_table_where($table_name,$data,$key,$value=""){
		/*follow this format for data array
			$data = array(
				'tables'=>array(
					'ofabee_unique_device'=>array('DID','device_id','name','model','version','key'),
					'ofabee_age'=>array('age_range'),
					'ofabee_gendar'=>array('type'),
					'ofabee_country'=>array('country_name')
				),
				'join'=>array(
					'ofabee_age'=>array('ofabee_unique_device.AGID','ofabee_age.AGID'),
					'ofabee_gendar'=>array('ofabee_unique_device.GID','ofabee_gendar.GID'),
					'ofabee_country'=>array('ofabee_unique_device.COID','ofabee_country.COID')
				)
			);
		*/
		$query = "SELECT ";
		$last = end(end($data['tables']));
		foreach ($data['tables'] as $table=>$columns){
			foreach($columns as $count=>$column){
				$query .= $table.".".$column;
				if($column != $last)
				$query.=",";
			}
		}
		$query.=" FROM ".$table_name;
		foreach($data['join'] as $table=>$condition){
			$query.=" JOIN ".$table." ON ".$condition[0]."=".$condition[1];
		}
		$query.=" WHERE ".$key."=".$value;
		return $this->query($query);
	}

	
	// "database-neutral" methods
  public function fetch_array($result_set) {
    return mysql_fetch_array($result_set);
  }
  
  public function num_rows($result_set) {
   return mysql_num_rows($result_set);
  }
  
  public function insert_id() {
    // get the last id inserted over the current db connection
    return mysql_insert_id($this->connection);
  }
  
  public function affected_rows() {
    return mysql_affected_rows($this->connection);
  }

	private function confirm_query($result) {
		if (!$result) {
	    $output = "Database query failed: " . mysql_error() . "<br /><br />";
	    //$output .= "Last SQL query: " . $this->last_query;
	    die( $output );
		}
	}
	
}

$database = new MySQLDatabase();
$db =& $database;

?>