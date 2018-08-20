<?php
!function_exists('checkpathdir') && exit('Forbidden');

/*
by  codydalton168
*/




Class Initialization {
	var $sql = 0;
	var $default_host;
	var $default_user;
	var $default_pwassword;
	var $default_name;
	var $default_charset;
	var $default_pconnect = 0;
	var $query_num = 0;
	var $default_lp;

	function Initialization($default_host,$default_user,$default_pwassword,$default_name,$default_charset,$default_pconnect,$default_lp){
		$this->sqlhost = $default_host;
		$this->sqlport = '3306';
		$this->sqluser = $default_user;
		$this->sqlpw   = $default_pwassword;
		$this->sqlname = $default_name;
		$this->sqlcharset = $default_charset;
		$this->pconnect = $default_pconnect;
		$this->lp = & $default_lp;
		$this->connect();
	}
	function connect(){

		list($sqlhost, $sqlport) = explode(':', $this->sqlhost);

		!$sqlport && $sqlport = 3306;

		$this->sql = mysqli_init();	

		mysqli_real_connect($this->sql,$sqlhost, $this->sqluser, $this->sqlpw, false, $sqlport);

		if(mysqli_errno($this->sql) != 0){
			$this->halt('Connect('.$this->pconnect.') to MySQL failed');
		}

		$serverinfo = mysqli_get_server_info($this->sql);

		if ($serverinfo > '4.1' && $this->sqlcharset) {
			mysqli_query($this->sql, "SET character_set_connection=" . $this->sqlcharset . ",character_set_results=" . $this->sqlcharset . ",character_set_client=binary");
		}

		if ($serverinfo > '5.0') {
			mysqli_query($this->sql, "SET sql_mode=''");
		}

		if ($this->sqlname && !@mysqli_select_db($this->sql, $this->sqlname)) {
			$this->halt('Cannot use database');
		}
	}

	function close($linkid){
		return @mysqli_close($linkid);

	}

	function lock($table_name){
		return $this->query("LOCK TABLES ".$table_name." WRITE");
	}
	
	function unlock($table_name){
		return $this->query("UNLOCK TABLES");
	}

	function select_db($default_name){
		if (!@mysqli_select_db($default_name,$this->sql)) {
			$this->halt('Cannot use database');
		}
	}

	function server_info(){
		return mysqli_get_server_info($this->sql);
	}


	//get_update
	function gtup($SQL_1,$SQL_2,$SQL_3){
		$rt = $this->gs($SQL_1,MYSQL_NUM);
		if (isset($rt[0])) {
			$this->up($SQL_2);
		} else {
			$this->up($SQL_3);
		}
	}


	function insert_id(){
		return $this->gv('SELECT LAST_INSERT_ID()');
	}

	//get_value
	function gv($SQL,$result_type = MYSQL_NUM,$field=0){
		$query = $this->query($SQL);
		$rt =& $this->fetch_array($query,$result_type);
		return isset($rt[$field]) ? $rt[$field] : false;
	}

	//getone
	function gs($SQL,$result_type = MYSQL_ASSOC){
		$query = $this->query($SQL,'Q');
		$rt =& $this->fetch_array($query,$result_type);
		return $rt;
	}



	//update
	function up($SQL,$lp=1){
		if ($this->lp ==1 && $lp) {
			$tmpsql6 = substr($SQL,0,6);
			if (strtoupper($tmpsql6.'E')=='REPLACE') {
				$SQL = 'REPLACE LOW_PRIORITY'.substr($SQL,7);
			} else {
				$SQL = $tmpsql6.' LOW_PRIORITY'.substr($SQL,6);
			}
		}
		return $this->query($SQL,'U');
	}


	function query($SQL,$method = null,$error = true){
		$query = @mysqli_query($this->sql, $SQL, ($method ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT));
		if (in_array(mysqli_errno($this->sql),array(2006, 2013)) && empty($query) && !defined('QUERY')) {
			define('QUERY',true); 
			@mysqli_close($this->sql);
			sleep(2);
			$this->connect();
			$query = $this->query($SQL);
		}
		if ($method != 'U') {
			$this->query_num++;
		}
		if(!$query && $error){
			$this->halt('Query Error: '.$SQL);
		}
		return $query;
	}



	function fetch_array($query, $result_type = MYSQL_ASSOC){
			if($result_type == 'MYSQL_ASSOC'){
	                     		return mysqli_fetch_assoc($query);

			} else if($result_type == 'MYSQLI_NUM'){

				return mysqli_fetch_row($query);

			} else {

				return mysqli_fetch_array($query);
			}
	}
	function affected_rows(){
		return mysqli_affected_rows($this->sql);
	}
	function num_rows($query){
		if (!is_bool($query)) {
			return mysqli_num_rows($query);
		}
		return 0;
	}
	function num_fields($query){
		return @mysqli_num_fields($query);
	}
	function escape_string($str){
		return mysqli_real_escape_string($this->sql, $str);
	}
	function free_result(){
		$void = func_get_args();
		foreach ($void as $query) {
			if ($query instanceof mysqli_result) {
				mysqli_free_result($query);
			}
		}
		unset($void);
	}

	function halt($msg=null){
		require_once(DIR.'require/mysqli_msg.php');
		new DBERROR($msg);
	}
	

}


?>
