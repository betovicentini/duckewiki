<?php
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders($body);//cadastro da identificacao

if (!empty($filtro)) { 
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$especimenesids= $rr['EspecimenesIDS'];
} else {
	if ($coletorid>0) { 
		$colnums = explode(";",$colnumbers);
		$specids = array();
		foreach ($colnums as $vv) {
			$qq = "SELECT EspecimenID FROM Especimenes WHERE ColetorID='".$coletorid."' AND Number='".trim($vv)."'";
			$res = mysql_query($qq,$conn);
			$nres = mysql_numrows($res);
			if ($nres==1) {
				$rr = mysql_fetch_assoc($res);
				$specids[] = $rr['EspecimenID'];
			}
		}
		$especimenesids = implode(";",$specids);
	}
}
if (!empty($especimenesids) && !empty($detset))  { //se tiver enviado faz o cadastro
$specarray = explode(";",$especimenesids);
$detarray = unserialize($detset);
	$todos=0;
	//insere nova determinacao que ser· usada pelo conjunto de amostras
	if (empty($detchange) || $detchange=='mudou') {			
		$newdetid = InsertIntoTable($detarray,'DetID','Identidade',$conn);
	} 
	if ($newdetid) { //se cadastrou a identificacao corretamente, entao atualiza os registros das coletas com essa nova determinacao
		$nok =0;
		$naomudou=0;
		$falhou=0;
		foreach ($specarray as $specid) {  //for earch specimen
			//pega o valor antigo para compara e ver se precisa mudar
			$qq = "SELECT Identidade.* FROM Especimenes JOIN Identidade USING(DetID) WHERE EspecimenID='$specid'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$olddetid = $row['DetID'];
			$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$detarray,$conn);
			if ($detchanged==0 || empty($detchanged)) { //se for identifico nesse campos nao faz nada
				$detchange = 'naomudou';
				$naomudou++;
			} else {
				CreateorUpdateTableofChanges($specid,'EspecimenID','Especimenes',$conn);
				$arrayofvalues = array('DetID' => $newdetid);
				$newupdate = UpdateTable($specid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
				if (!$newupdate) {
					$falhou++;
				} else {
					$nok++;
				}
			}
		} //end for earch specimen
		
	} else {  //end se identidade for cadastrada corretamente
		$todos++;
	}

} //fecha se ent„o faz o cadastro

if ($nok>0) {
	echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>$nok ".GetLangVar('sucessoregistrosatualizados')."</td></tr>
</table>
<br>";
}

if ($falhou>0) {
		echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$falhou ".GetLangVar('erroregistrofalhou')."</td></tr>
</table>
<br>";
}
if ($naomudou>0) {
 echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$naomudou ".GetLangVar('erroregistronaomudou')."</td></tr>
</table>
<br>";
}

if ($todos>0) {
	echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$falhou ".GetLangVar('erro1')."</td></tr>
</table>
<br>";
}
	
HTMLtrailers();

?>
