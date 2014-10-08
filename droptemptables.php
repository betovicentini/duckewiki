<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include "functions/ImportData.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

echo $dbname."<br />";
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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();
$title = 'Drop temp tables';
$body ='';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($_SESSION);
$dadt = $_SESSION['sessiondate'];
$qq = "SELECT create_time,table_name
FROM INFORMATION_SCHEMA.TABLES
WHERE (table_name LIkE 'temp%' OR table_name LIkE 'Temp%') AND create_time NOT LIKE '".$dadt."%' AND TABLE_SCHEMA LIKE '".$dbname."'";
//echo $qq."<br >";
$res = mysql_query($qq,$conn);
$deleted =0;
while($row = mysql_fetch_assoc($res)) {
	//echopre($row);
	$qu = "DROP TABLE `".$row['table_name']."`";
	$rr = mysql_query($qu,$conn);
	if ($rr) {
		echo $row['table_name']."  deleted OK!.<br />";
		$deleted++;
	} else {
		echo strtoupper($row['table_name'])."  NOT deleted.<br />";
	}
}
echo "<p class='success' style='width: 300px;'>$deleted tabelas mais velhas que hoje apagadas!</p>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>