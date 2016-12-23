<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

if (empty($filtronome)) {
	$filtronome = "FiltroDoProjeto_".$prjid;
}
$arrayofvals = array('FiltroName' => $filtronome,'Shared' => 0);
$newfiltro = InsertIntoTable($arrayofvals,'FiltroID','Filtros',$conn);
$res = "O filtro ".$filtronome." foi salvo com sucesso";
if ($newfiltro) {
	$qu = "INSERT INTO FiltrosSpecs (EspecimenID,PlantaID,FiltroID) (SELECT DISTINCT EspecimenID,PlantaID,".$newfiltro." FROM projetosespecs WHERE (ProjetoID+0)=".$projetoid.")";
	$ru = mysql_query($qu,$conn);
	if (!$ru) {
		echo $qu;
		$res = "ERRO: não foi possível cadastrar plantas ou amostras no filtro filtro";
	} 
} else {
	$res = "ERRO: não foi possível cadastrar o filtro";
}
echo $res;
?>