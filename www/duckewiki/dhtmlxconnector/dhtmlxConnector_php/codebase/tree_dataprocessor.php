<?php

require_once("dataprocessor.php");

class TreeDataProcessor extends DataProcessor{
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
		$this->logger->log("Edit operation started [TREE]",$_POST);
		$results=array();

		
		$ids=explode(",",$_POST["ids"]);
		$rows_data=$this->get_post_values($ids);
		for ($i=0; $i < sizeof($ids); $i++) { 
			$rid = $ids[$i];
			$status = $_POST[$rid."_!nativeeditor_status"];
			$data = $this->get_post_values($rid);
			$this->logger->log("Item data [{$rid}]",$rows_data[$rid]);
			$action=$this->inner_process($status,$rid,$this->name_data($rows_data[$rid]));
			$results[]=$action;
			
			if ($form)
				$sub_res=$form["obj"]->update_external($action);	//send master action to related element
				
			if ($status=="inserted"){
				//change parent id for child elements
				$oid=$action->get_id();
				$nid=$action->get_new_id();
				for ($j=$i+1; $j < sizeof($ids); $j++) { 
					if ($rows_data[$ids[$j]]["tr_pid"]==$oid)
						$rows_data[$ids[$j]]["tr_pid"]=$nid;
				}
			}
				
		}
		
		$this->output_edit($results);
	}
	function name_data($data){
		$cf=$this->sql->config;
		//register parent id field
		$this->sql->add_field($cf["pid"][0],$cf["pid"][1]);
		
		$res=array();
		$res[$cf["field"][0][1]]=$data["tr_text"];
		$res[$cf["pid"][1]]=$data["tr_pid"];
		
		return array("data"=>$res,"original"=>$data);
	}
}
?>