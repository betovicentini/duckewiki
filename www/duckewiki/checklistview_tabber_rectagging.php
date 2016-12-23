<?php
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require_once($relativepathtoroot.$databaseconnection_clean);

//$uuid = cleanQuery($_SESSION['userid'],$conn);


$uuid = $_POST['uuid'];
$tempids = $_POST['tempids'];
$status = $_POST['status'];
$tbname =  $_POST['table'];
$usertbname =  $_POST['usertbname'];
$sesid = substr(session_id(),0,10);

$ids = explode(",",$tempids);

$iddd = $tempids;
$inserido =0;
$atu =0;
if ($tbname=='checklist_all') {
	$field = 'TempID';
}
if ($tbname=='checklist_speclist') {
	$field = 'EspecimenID';
	
}
if ($tbname=='checklist_pllist') {
	$field = 'PlantaID';
}
if ($tbname=='checklist_plots') {
	$field = 'nomeid';
}


$nt = count($ids);
if ($nt>0) {
	$atu=0;
	$inserido=0;
	$nochange = 0;
	$mmm = 0;
	foreach ($ids as $spid) {
		//PEGA O VALOR EXISTENTE, SE HOUVER
		if ($uuid>0) {
			$res =  mysql_query("SELECT * FROM `".$tbname."UserLists` WHERE UserID=".$uuid." AND  ".$field."=".$spid);
		} else {
			$res =  mysql_query("SELECT * FROM `".$tbname."UserLists` WHERE SessionID=".$sesid." AND  ".$field."=".$spid);
		}
		$nres = mysql_numrows($res);
		//SE JA HOUVER UM TAG PARA O USUARIO/SESSAO
		if ($nres>0 && $status==0) {
			//APAGA
			$rw = mysql_fetch_assoc($res);
			mysql_query("DELETE FROM `".$tbname."UserLists` WHERE ListID=".$rw['ListID']);
			$atu++;
		} else {
			if ($nres==0 && $status==1) {
				if ($uuid>0) {
					$qinn = "INSERT INTO  `".$tbname."UserLists` (`Marcado`,`".$field."`,`UserID`) VALUES ('1' ,'".$spid."','".$uuid."')";
				} else {
					$qinn = "INSERT INTO  `".$tbname."UserLists` (`Marcado`,`".$field."`,`SessionID`) VALUES ('1' ,'".$spid."','".$sesid."')";
				}
				$rr = mysql_query($qinn);
				if ($rr) {
					$inserido++;
				} else {
					$mmm++;
				}
			} else {
				$nochange++;
			}
		}
	}
}
$txt = '';
if ($inserido>0) {
$txt .=  $inserido." registros foram MARCADOS";
}
if ($nochange>0) {
$txt .=  $nochange." registros não mudaram";
}
if ($atu>0) {
$txt .=  $atu." registros foram DESMARCADOS";
}
echo $txt;
//echo $ttt;

?>