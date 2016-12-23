<?php
require_once("db_common.php");

class MySQLDBWrapper extends DBWrapper{
	function query($sql,$result=false){
		if (is_object($sql))
			$sql=$sql->sql();
			
		$this->logger->log("Exec SQL: ",$sql);
		$res=mysql_query($sql);
		if (!$res){
			$this->logger->log("MySQL error: ",mysql_error());
			return false;
		}
		if ($result===false)
			return $res;
		else return mysql_result($res,$result);
	}	
	function escape($str){
		return mysql_real_escape_string($str);
	}	
	function get_data_array($res){
		return mysql_fetch_array($res,MYSQL_NUM);
	}
	function get_data_named($res){
		return mysql_fetch_assoc($res);
	}
	function get_id(){
		return mysql_insert_id($this->db);
	}
	function count($res){
		return mysql_num_rows($res);	
	}
}


class DBQueryMySQL extends DBQuery{
}	



?>