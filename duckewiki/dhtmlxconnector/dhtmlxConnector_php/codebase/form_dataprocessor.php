<?php

require_once("dataprocessor.php");
class FormDataProcessor extends DataProcessor{
	function get_post_values($fields,$id=false){
		if ($id) $prefix=$id."_";
		else $prefix="";
		
		$data=array(); 
		for ($i=0; $i < sizeof($fields); $i++) 
			$data[]=$_POST[$prefix.$fields[$i][1]];
		return $data;
	}

	function process($names,$action=false){
		if (!$action){
			$status="updated";
			$id = $_GET["form_id"];	//separate form
			$data=$this->get_post_values($names);
		} else {
			$status=$action->get_status();
			$id=$action->get_new_id();
			$data=$this->get_post_values($names,$id);
		}
			
		if ($status=="invalid" || $status=="error") return;
		
		$this->logger->log("Edit operation started [FORM:".$id."]",$data);
		$result=$this->inner_process($status,$id,$this->name_data($data),$action);
		
		if ($action)
			return;
			
		$this->output_edit(array($result));
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