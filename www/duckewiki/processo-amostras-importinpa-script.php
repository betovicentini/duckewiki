<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//CABECALHO
$menu = FALSE;

//IMPORTAR PARA A BASE
$qq = "SELECT * FROM `".$tbname."`";
$rr = mysql_query($qq,$conn);
$nrr = mysql_numrows($rr);
$specsfilter = array();
if ($nrr>0) {
$idx = 1;
$up=0;
$naom = 0;
while ($rwr = mysql_fetch_assoc($rr)) {
		$inpanum = $rwr['INPA']+0;
		//$brahmsid = $rwr['BRAHMS']+0;
		$specid = $rwr['WIKIESPECIMENID']+0;
		if ($inpanum>0 && $specid>0) {
			$arrayofvalues = array( 'INPA_ID' => $inpanum);
			//'INPABRAHMS' => $brahmsid);
			CreateorUpdateTableofChanges($specid,'EspecimenID','Especimenes',$conn);
			//compara, se for diferente atualiza, caso contrário ignora
			$mudou = CompareOldWithNewValues('Especimenes','EspecimenID',$specid,$arrayofvalues,$conn);
			if ($mudou==0 || empty($mudou)) { //se for identifico nesse campos nao faz nada
				$perc =floor(($idx/$nrr)*100);
				$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
				mysql_query($qnu);
				$naom++;
			} else {
				$updatespecid = UpdateTable($specid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
				$qu = "UPDATE processo_".$processoid."  SET ".$herbariumsigla."=".$inpanum."  WHERE EspecimenID='".$specid."'";
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ProcessosLIST  SET ".$herbariumsigla."=".$inpanum."  WHERE EspecimenID='".$specid."'";
				$ruu = mysql_query($qu,$conn);
				if ($ru && $updatespecid>0 && $ruu) {
					$up++;
					$perc =floor(($idx/$nrr)*100);
					$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
					mysql_query($qnu);
				}
			}
		}
		$idx++;
}
	$txt = "Registros ".$up." atualizados e ".$naom." já estavam cadastrados";
	$perc =100;
	$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
	mysql_query($qnu);
	echo $txt;
} else {
	$perc =100;
	$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
	mysql_query($qnu);
	echo "Nada";
}
session_write_close();
?>