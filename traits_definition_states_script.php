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


//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

$tbname = 'temp_TraitsStates_'.$uuid;
$qq = "DROP TABLE ".$tbname;
$rq = @mysql_query($qq,$conn);
	
$qq = "SELECT 
'edit-icon.png' AS EDIT,
pltb.TraitID,
pltb.ParentID,
pltb.TraitTipo,
pltb.ParentID as VARIAVEL,
pltb.TraitName as ESTADO,
pltb.TraitName_English as ESTADO_ENG,
pltb.TraitDefinicao as DEFINICAO,
pltb.TraitDefinicao_English as DEFINICAO_ENG,
gettraittaxa(pltb.TraitID) AS TAXA,
pltb.TraitIcone as IMAGEM
FROM Traits as pltb
WHERE pltb.TraitTipo LIKE '%Estado%' AND pltb.ParentID='".$traitid."'";

$sql = "CREATE TABLE IF NOT EXISTS ".$tbname." ".$qq;
//echo $qq."<br >";
$rz = mysql_query($sql,$conn);

$qq = "ALTER TABLE `".$tbname."` CHANGE  `IMAGEM`  `IMAGEM` VARCHAR( 300 )";
mysql_query($qq,$conn);

$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(TraitID)";
mysql_query($qq,$conn);
		
$sql = "CREATE INDEX CLASSE ON ".$tbname."  (ESTADO)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX VARIAVEL ON ".$tbname."  (VARIAVEL)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX VARIAVEL_ENG ON ".$tbname."  (ESTADO_ENG)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX VARIAVEL_ENG ON ".$tbname."  (ESTADO_ENG)";
mysql_query($sql,$conn);

header("location: traits_definition_states_save.php?tbname=".$tbname);
?>