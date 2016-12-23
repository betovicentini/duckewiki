<?php
session_start();
include "functions/HeaderFooter.php";
require_once($relativepathtoroot.$databaseconnection_clean);

$uuid = $_POST['uuid'];
$tempids = $_POST['tempids'];
$status = $_POST['status'];
$tbname =  $_POST['table'];


$ids = explode(",",$tempids);
$nt = count($ids);

//echo $nt;
$inserido =0;
if ($nt>0) {
	foreach ($ids as $spid) {
			$rr = mysql_query("UPDATE  `".$tbname."` SET `Marcado`=".$status."  WHERE `TraitID`=".$spid, $res);
			if ($rr) {
			   $inserido++;
			}
		}
}
echo $inserido;
?>