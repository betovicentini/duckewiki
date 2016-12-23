<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//echopre($gget);
if ($especimenid>0 && $thetraitid>0 && $imgid>0) {
	//checa por link anterior	
	$sql = "SELECT * FROM Traits_variation WHERE EspecimenID=".$especimenid."  AND TraitID=".$thetraitid;
	$ores2 = mysql_query($sql,$conn);
	$nlinks= mysql_num_rows($ores2);
	if ($nlinks==1) {
		$oldrow = mysql_fetch_assoc($ores2);
		$oldimgs = $oldrow["TraitVariation"];
		$oldrowid = $oldrow["TraitVariationID"];
		$oldimgs = explode(";",$oldimgs);
		$oldimgs[] = $imgid;
		$newimgs = array_unique($oldimgs);
		$newimgs = implode(";",$newimgs);
		$newimgs = array("TraitVariation" => $newimgs);
		CreateorUpdateTableofChanges($oldrowid,'TraitVariationID','Traits_variation',$conn);
		UpdateTable($oldrowid,$newimgs,'TraitVariationID','Traits_variation',$conn);
		$resposta = "RELAÇÃO SALVA";
		
		$fieldsaskeyofvaluearray = array("UnLinked" => 0);
		CreateorUpdateTableofChanges($imgid,'ImageID','Imagens',$conn);
		UpdateTable($imgid,$fieldsaskeyofvaluearray,'ImageID','Imagens',$conn);
								
								
	} else {
      $newimgs = array("TraitVariation" => $imgid, "EspecimenID" => $especimenid,"TraitID" => $thetraitid);
		$newimg = InsertIntoTable($newimgs,'TraitVariationID','Traits_variation',$conn);
		if ($newimg) {
			$resposta = "RELAÇÃO SALVA";
			$fieldsaskeyofvaluearray = array("UnLinked" => 0);
			CreateorUpdateTableofChanges($imgid,'ImageID','Imagens',$conn);
			UpdateTable($imgid,$fieldsaskeyofvaluearray,'ImageID','Imagens',$conn);
		} else {
			$resposta = "ERRO: RELAÇÃO NÃO FOI SALVA";
		}
	}	
}		
echo $resposta;
?>