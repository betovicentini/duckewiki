<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"

);
$which_java = array(
);
$title = 'Especialistas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//ADICIONA NOVAS COLUNAS QUE SERAO NECESSARIAS
$qq ="ALTER TABLE `Tax_Generos`  ADD `EspecialistaID` INT(10) NULL DEFAULT NULL AFTER `Valid`";
@mysql_query($qq,$conn);
$qq ="ALTER TABLE `ChangeTax_Generos`  ADD `EspecialistaID` INT(10) NULL DEFAULT NULL AFTER `Valid`";
@mysql_query($qq,$conn);


//CRIA UMA TABELA PARA ESPECIALISTAS
$qq = "CREATE TABLE IF NOT EXISTS Especialistas (
EspecialistaID INT(10) unsigned NOT NULL auto_increment,
Especialista INT(10) COMMENT 'PessoaID',
EspecialistaTXT CHAR(200) COMMENT 'Abreviacao',
FamiliaID INT(10),
Familias CHAR(200),
Generos CHAR(200),
Herbarium CHAR(10),
Email CHAR(100),
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (EspecialistaID)) CHARACTER SET utf8 ENGINE InnoDB";
@mysql_query($qq,$conn);


//ADICIONA A ESSA TABELA AS FAMILIAS QUE EXISTEM NA BASE
$qn = "INSERT INTO Especialistas (FamiliaID, Familias)  SELECT DISTINCT newtb.FamiliaID, fam.Familia FROM ((SELECT DISTINCT idd.FamiliaID FROM Identidade idd JOIN Plantas AS pl ON pl.DetID=idd.DetID AND idd.FamiliaID>0) UNION (SELECT DISTINCT idd.FamiliaID FROM Identidade idd JOIN Especimenes AS pl ON pl.DetID=idd.DetID AND idd.FamiliaID>0)) AS newtb LEFT JOIN Especialistas as espec ON espec.FamiliaID=newtb.FamiliaID LEFT JOIN Tax_Familias as fam ON newtb.FamiliaID=fam.FamiliaID WHERE espec.EspecialistaID IS NULL AND newtb.FamiliaID>0  AND fam.Familia IS NOT NULL AND fam.Familia<>''  ORDER BY fam.Familia";
$res = @mysql_query($qn,$conn);

if ($res) {
echo "
  <form name='myform' action='especialista-gridsave.php' method='post'>";
  foreach ($gget as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
  foreach ($ppost as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";
}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);


?>
