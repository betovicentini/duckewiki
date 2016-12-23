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


$tbname = 'temp_TraitsEditDefinitions_'.$uuid;

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);

$updatetable=1;
if (($nr==0 || $updatetable>0)) {
	
$perc = 1;
$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
mysql_query($qnu,$conn);
session_write_close();

	
$qq = "DROP TABLE ".$tbname;
$rq = @mysql_query($qq,$conn);
	
	$qq = "SELECT 
'edit-icon.png' AS EDIT,
pltb.TraitID,
pltb.ParentID,
pltb.TraitTipo,
pltb.ParentID as CLASSE,
pltb.TraitName as VARIAVEL,
pltb.TraitName_English as VARIAVEL_ENG,
pltb.TraitDefinicao as DEFINICAO,
pltb.TraitDefinicao_English as DEFINICAO_ENG,
pltb.TraitUnit as UNIDADE,
IF (pltb.TraitTipo='Variavel|Categoria', 'categories.png', '') AS CATEGORIAS,
gettraittaxa(pltb.TraitID) AS TAXA,
pltb.TraitIcone as IMAGEM,
IF(pltb.MultiSelect='Sim',1,0) as MultiSelect
FROM Traits as pltb
LEFT JOIN Traits as par ON par.TraitID=pltb.ParentID WHERE pltb.TraitTipo LIKE '%variav%'";

$sql = "CREATE TABLE IF NOT EXISTS ".$tbname." ".$qq;
$rz = mysql_query($sql,$conn);

$perc = 50;
$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
mysql_query($qnu,$conn);
session_write_close();

$qq = "ALTER TABLE `".$tbname."` ADD  `Marcado` TINYINT( 1 ) NOT NULL FIRST";
mysql_query($qq,$conn);

$qq = "ALTER TABLE `".$tbname."` CHANGE  `MultiSelect`  `MultiSelect` TINYINT( 1 )";
mysql_query($qq,$conn);

$qq = "ALTER TABLE `".$tbname."` CHANGE  `IMAGEM`  `IMAGEM` VARCHAR( 300 )";
mysql_query($qq,$conn);

$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(TraitID)";
mysql_query($qq,$conn);
		
$sql = "CREATE INDEX CLASSE ON ".$tbname."  (CLASSE)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX VARIAVEL ON ".$tbname."  (VARIAVEL)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX VARIAVEL_ENG ON ".$tbname."  (VARIAVEL_ENG)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX VARIAVEL_ENG ON ".$tbname."  (VARIAVEL_ENG)";
mysql_query($sql,$conn);

$perc = 100;
$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
mysql_query($qnu,$conn);
echo "Concluido";
session_write_close();


} 
else {
		$perc = 100;
		$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu,$conn);
		echo "Concluido";
}
session_write_close();
?>