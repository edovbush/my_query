<?php

class my_query
{
    private $results;
    private $connection;
		private $database;

    function __construct($connect)
    {
        $this->connection = mysqli_connect($connect[0], $connect[1], $connect[2], $connect[3]);
		
		mysqli_query ($this->connection,"set character_set_client='utf8'");
		mysqli_query ($this->connection,"set character_set_results='utf8'");
		mysqli_query ($this->connection,"set collation_connection='utf8_general_ci'");	

		$this->database = $connect[3];
    }

    function Execute($query)
    {
        $this->results = ($this->connection ? mysqli_query($this->connection, $query) : false);
        return $this->results !== false;
    }

	function display(){
		while(($row = mysqli_fetch_row($this->results)))
		print_r($row);
	}
   
	function show_columns($table) {
	   $query = "SHOW COLUMNS FROM $table";$result = $this->Execute($query);$columns = [];
	   if($result) {
			$myrow = mysqli_fetch_array($this->results);$columns = [];do {$columns[] = $myrow[0];}while($myrow = mysqli_fetch_array($this->results));
	   }else {//throw new Exception('Error');
	   }
	   return $columns;
	}
   
	function show_tables() {
		$value = '';
		$database = $this->database;
		$query = "Show tables from $database";
		$result = $this->Execute($query); 
		if($result) {
			$myrow = mysqli_fetch_array($this->results);
			$value = [];
			if($myrow) {
				do {
					$value[] = $myrow["Tables_in_$database"];
				}
				while($myrow = mysqli_fetch_array($this->results));
			}
		}
		return $value;
	}
	
	function nums($table, $cols, $vals) {
		$sel = $this->select($table, 'many', $cols, $vals, $cols[0]);
		$res = 0;
		if($sel) {
			$arr = array_column($sel, $cols[0]);
			$res = count($arr);
		}
		return $res;
	}
	
	function rows($table) {
		$query = "SELECT count(*) FROM `$table`";
		$result = $this->Execute($query);
		$res = '';
		if($result) {
			$myrow = mysqli_fetch_array($this->results);
			if($myrow){
				$res = $myrow[0];
			}
		}
		return $res;
	}
	
	function check_exist($table) {
		$query = "show tables like '$table'";
		$result = $this->Execute($query);
		$res = '';
		if($result) {
			$myrow = mysqli_fetch_array($this->results);
			if($myrow){
				$res = 1;
			}
		}
		return $res;
	}
	
	function create_like($like_table, $new_table) {
		$query = "CREATE TABLE `$new_table` LIKE $like_table";
		$result = $this->Execute($query);
	}
	
	function select_insert($table, $where_cols, $where_vals, $insert_cols, $insert_vals) 
	{
		$sel = $this->select($table, 'one', $where_cols, $where_vals, $where_cols[0], 'limit 1');
		
		if($sel) {
			$query = $this->update($table, $where_cols, $where_vals, $insert_cols, $insert_vals);
		}
		else {
			$query = $this->insert($table, $insert_cols, $insert_vals);
		}
		return $query;
		
	}

	function select($table, $function, $where_columns = null, $where_values = null, $select_column = null, $query_end = null) {
		
		$where = '';
		$value = '';
		
		if($where_columns) {
			if(!is_array($where_columns)) {
				$where_columns = explode(', ', $where_columns);
				$where_values = explode(', ', $where_values);
				$where_columns = array_filter($where_columns);
			}
			if(is_array($where_columns)) {	
				$where = 'WHERE ';
				foreach($where_columns as $key=>$column) {
					$where_value = addslashes($where_values[$key]);
					$where .= "$column = '$where_value' AND ";
				}
				$where = rtrim($where, " AND ");
			}
			else {
				$where_values = addslashes($where_values);
				$where .= "WHERE $where_columns = '$where_values'";
			}
		}
		
		$select_column = $select_column ? $select_column : "*";
		
		$query = "SELECT $select_column FROM `$table` $where $query_end;";
		//echo $query . '<br><br>';
		$result = $this->Execute($query); 
		
		if($result) {
			$myrow = mysqli_fetch_array($this->results);
			$value = [];
			if($myrow) {
				if($function == 'one') {
					if(strpos($select_column, ',')) {
						$select_column = explode(', ', $select_column);
						if($select_column) {
							foreach($select_column as $select_col) {
								$value[$select_col] = $myrow[$select_col];
							}	
						}
					}
					else {
						$value = $myrow[$select_column];
					}
				}
				
				if($function == 'many') {
					$select_column = explode(', ', $select_column);
					if($select_column) {
						$u = 0;
						do {
											
							foreach($select_column as $select_col) {
								$val = $myrow[$select_col];
								$value[$u][$select_col] = $val;
							}	
							
							$u++;
						}
						while($myrow = mysqli_fetch_array($this->results));
					}
				}
			}
		}

		return $value;
		
	}

	function truncate($table) {
		$query =  "TRUNCATE TABLE $table;";
		$result = $this->Execute($query);
	}
	
	function remove($table, $where_columns, $where_values, $additional = null) {
		$where = '';
		if($where_columns) {
			if(!is_array($where_columns)) {
				$where_columns = explode(', ', $where_columns);
				$where_values = explode(', ', $where_values);
			}
			$where = 'WHERE ';
			foreach($where_columns as $key=>$column) {
				$where .= "$column = '" . $where_values[$key] . "' AND ";
			}
			$where = rtrim($where, " AND ");
		}
		$query = "DELETE from `$table` $where $additional;"; 
		$result = $this->Execute($query);
	}
	
	function update($table, $where_columns, $where_values, $select_column, $update_value, $additional = null) {
		$funcs = '';
		if(!is_array($where_columns)) {
			$where_columns = explode(', ', $where_columns);
			$where_values = explode(', ', $where_values);
		}
		
		if($where_columns) {
			$where = 'WHERE ';
			if(is_array($where_columns)) {
				foreach($where_columns as $key=>$column) {
					$where .= "$column = '" . $where_values[$key] . "' AND ";
				}
				$where = rtrim($where, " AND ");
			}
			else {
				$where .= "$where_columns = '$where_values'";
			}
		}
		
		if(is_array($select_column)) {
			$funcs = [];
			foreach($select_column as $key=>$selecty) {
				$value = $update_value[$key];
				$value = addslashes($value);
				$query_update = "UPDATE $table SET $selecty = '$value' $where $additional;"; 
				$result = $this->Execute($query_update);
				$funcs[] = $query_update;
			}
			$funcs = implode(', ', $funcs);
		}
		else {
			$update_value = addslashes($update_value);
			$query_update = "UPDATE $table SET $select_column = '$update_value' $where $additional;";
			$result = $this->Execute($query_update);
			$funcs = $query_update;
		}
		return $funcs;
	}
	
	function insert($table, $tds, $vals)
	{
		global $connection;
		$tds_list = ''; $vals_list = '';
		if(!is_array($tds)) {		
			$tds = explode(', ', $tds);
			$vals = explode(', ', $vals);
		}
		foreach ($tds as $value) {
			$value = addslashes($value);
			$tds_list .= "`$value`,";
			//echo $value;
			}
		$tds_list = substr($tds_list, 0, -1); 
		foreach ($vals as $value) {
			$value = addslashes($value);
			$vals_list .= "'$value',";
			//echo $value;
			}
		$vals_list = substr($vals_list, 0, -1);
		
		$query = "INSERT INTO $table ($tds_list) VALUES ($vals_list);";
		mysqli_query($this->connection, $query);
		$new_id = $this->connection->insert_id ?? '';
		return ['query' => $query, 'id' => $new_id];
	}

	function comma($val) {
		$res = '';
		if(is_array($val)) {
			$res = implode(', ', $val);
		}
		return $res;
	}
   
};

?>
