<?php

require_once("dataprocessor.php");

class GridDataProcessor extends DataProcessor{
	function get_post_values($ids){
		$data=array(); 
		for ($i=0; $i < sizeof($ids); $i++)
			$data[$i]=array();
		
		foreach ($_POST as $key => $value) {
			$details=explode("_",$key,2);
			if (sizeof($details)==1) continue;
			
			$subdetails=explode("c",$details[1],2);
			if ($subdetails[0]=="" && is_numeric($subdetails[1]))
				$data[$details[0]][$subdetails[1]]=$value;
			else
				$data[$details[0]][$details[1]]=$value;
		}
		
		return $data;
	}
	function process($form=false){
		$this->logger->log("Edit operation started [GRID]",$_POST);
		$results=array();

		
		$ids=explode(",",$_POST["ids"]);
		$rows_data=$this->get_post_values($ids);
		for ($i=0; $i < sizeof($ids); $i++) { 
			$rid = $ids[$i];
			$status = $_POST[$rid."_!nativeeditor_status"];
			$data = $this->get_post_values($rid);
			$this->logger->log("Row data [{$rid}]",$rows_data[$rid]);
			$action=$this->inner_process($status,$rid,$this->name_data($rows_data[$rid]));
			$results[]=$action;
			
			if ($form)
				$sub_res=$form["obj"]->update_external($action);	//send master action to related element
				
		}
		
		$this->output_edit($results);
	}
	
	function name_data($data){
		$cf=$this->sql->config;
		$res=array();
		for ($i=0; $i < sizeof($cf["field"]); $i++)
			$res[$cf["field"][$i][1]]=$data[$i];
		return array("data"=>$res,"original"=>$data);
	}
}
?>